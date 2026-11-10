<?php
declare(strict_types=1);

namespace app\cms\database;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

/** Offline, bounded MyISAM conversion. DDL is never represented as transactional. */
final class EngineMigration
{
    private const VERSION = 1;
    private const MAX_TABLES = 512;
    private const MAX_ROWS = 200000;
    private const MAX_BYTES = 268435456;
    private const RESTORATION_CONTRACT = [
        'sql_mode' => 'NO_ENGINE_SUBSTITUTION,NO_AUTO_VALUE_ON_ZERO',
        'time_zone' => '+00:00',
        'sql_quote_show_create' => '1',
        'character_set_client' => 'utf8mb4',
        'character_set_connection' => 'utf8mb4',
        'character_set_results' => 'utf8mb4',
        'collation_connection' => 'utf8mb4_general_ci',
        'row_encoding' => 'base64_cells_null_distinct',
        'restore_scope' => 'verified_backup_into_reviewed_empty_database_only',
    ];
    private int $bytes = 0;
    private ?array $originalSession = null;

    public function __construct(private PDO $pdo, private string $database, private array $prefixes = ['ey_', 'wa_'])
    {
        self::identifier($database);
        if (!$prefixes || count($prefixes) !== count(array_unique($prefixes))) self::refuse('invalid_prefixes');
        foreach ($prefixes as $prefix) self::identifier($prefix);
        sort($this->prefixes, SORT_STRING);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
        if ($pdo->inTransaction()) self::refuse('active_transaction_not_supported');
        if ((string)$pdo->query('SELECT DATABASE()')->fetchColumn() !== $database) self::refuse('connection_database_mismatch');
        // Session-only canonical reads make raw SHOW CREATE and TIMESTAMP exports restorable.
        // In particular, preserve zero IDs and use backslash escapes, quoted names and UTC.
        $keys = array_slice(array_keys(self::RESTORATION_CONTRACT), 0, 7);
        $this->originalSession = $pdo->query('SELECT ' . implode(', ', array_map(static fn($key) => '@@SESSION.' . $key . ' AS ' . $key, $keys)))->fetch(PDO::FETCH_ASSOC);
        try {
            foreach ($keys as $key) {
                $this->setSessionValue($key, self::RESTORATION_CONTRACT[$key]);
            }
        } catch (Throwable $error) { $this->restoreSession(); throw $error; }
    }

    public function __destruct() { $this->restoreSession(); }

    /** No database writes or filesystem writes. The full bounded row multiset is hashed. */
    public function inspect(): array
    {
        return $this->capture(false);
    }

    /**
     * All application writers/schema changes must stay stopped until completion or recovery.
     * Table locks protect backup capture only; no lock/transaction is claimed across ALTER.
     * A failed return preserves the private backup/journal and never reverses successful DDL.
     */
    public function apply(array $plan, string $expectedFingerprint, string $backupDirectory, bool $maintenanceConfirmed): array
    {
        if (!$maintenanceConfirmed) self::refuse('maintenance_window_confirmation_required');
        self::validatePlan($plan, $expectedFingerprint);
        $this->sameScope($plan);
        if ($this->pdo->inTransaction()) self::refuse('active_transaction_not_supported');
        $lockName = 'cms-engine-' . substr(hash('sha256', json_encode([$plan['server_binding'], $this->database], JSON_THROW_ON_ERROR)), 0, 48);
        $lock = $this->pdo->prepare('SELECT GET_LOCK(?, 0)'); $lock->execute([$lockName]);
        if ((string)$lock->fetchColumn() !== '1') self::refuse('migration_lock_unavailable');
        $journal = null; $phase = 'preflight'; $active = null;
        try {
            if (file_exists($backupDirectory) || is_link($backupDirectory)) {
                $phase = 'verify_existing_backup';
                $verified = self::verifyBackup($backupDirectory);
                if (!hash_equals($expectedFingerprint, $verified['plan_fingerprint'])) self::refuse('backup_plan_mismatch');
                $journal = self::readPrivateJson($backupDirectory . '/journal.json');
                if (($journal['kind'] ?? null) !== 'engine_migration_journal' || ($journal['plan_fingerprint'] ?? null) !== $expectedFingerprint
                    || array_keys($journal['tables'] ?? []) !== array_keys($plan['tables'])) self::refuse('journal_identity_mismatch');
                $this->validateCurrent($plan, $this->capture(false));
            } else {
                $phase = 'fresh_plan_recheck';
                $current = $this->capture(false);
                if (!hash_equals($expectedFingerprint, $current['fingerprint'])) self::refuse('stale_plan');
                self::newPrivateDirectory($backupDirectory);
                self::newPrivateDirectory($backupDirectory . '/tables');
                $journal = ['kind' => 'engine_migration_journal', 'version' => self::VERSION,
                    'plan_fingerprint' => $expectedFingerprint, 'status' => 'backup_in_progress', 'phase' => 'backup',
                    'tables' => array_fill_keys(array_keys($plan['tables']), ['status' => 'not_started'])];
                self::writePrivateJson($backupDirectory . '/journal.json', $journal, true);
                $phase = 'locked_backup';
                $names = array_keys($plan['tables']);
                $this->pdo->exec('LOCK TABLES ' . implode(', ', array_map(fn($name) => $this->qualified($name) . ' WRITE', $names)));
                try {
                    $full = $this->capture(true);
                    $summary = $full; foreach ($summary['tables'] as &$table) unset($table['backup']); unset($table);
                    if (!hash_equals($expectedFingerprint, $summary['fingerprint'])) self::refuse('stale_plan_during_backup');
                    $files = [];
                    foreach ($full['tables'] as $name => $table) {
                        $file = 'tables/' . $name . '.json';
                        self::writePrivateJson($backupDirectory . '/' . $file, $table['backup'], true);
                        $files[$name] = ['file' => $file, 'sha256' => hash_file('sha256', $backupDirectory . '/' . $file)];
                    }
                    self::writePrivateJson($backupDirectory . '/manifest.json', ['kind' => 'engine_migration_backup',
                        'version' => self::VERSION, 'restoration_contract' => self::RESTORATION_CONTRACT,
                        'plan' => $plan, 'tables' => $files], true);
                } finally { $this->pdo->exec('UNLOCK TABLES'); }
                self::verifyBackup($backupDirectory);
                $journal['status'] = 'ready'; $journal['phase'] = 'backup_verified';
                self::writePrivateJson($backupDirectory . '/journal.json', $journal);
            }
            $phase = 'pre_ddl_full_recheck';
            $this->validateCurrent($plan, $this->capture(false));
            foreach ($plan['tables'] as $name => $before) {
                $active = $name; $phase = 'table_precondition';
                $this->bytes = 0;
                $now = $this->table($name, false);
                $converted = $this->equivalent($before, $now, 'InnoDB');
                if ($before['engine'] === 'InnoDB' || $converted) {
                    if (!$this->equivalent($before, $now, 'InnoDB')) self::refuse('completed_table_changed');
                    $journal['tables'][$name] = ['status' => $before['engine'] === 'InnoDB' ? 'preserved_innodb' : 'verified', 'after' => $now];
                    self::writePrivateJson($backupDirectory . '/journal.json', $journal);
                    continue;
                }
                if ($now !== $before) self::refuse('pending_table_changed');
                $journal['status'] = 'in_progress'; $journal['phase'] = 'ddl_started';
                $journal['tables'][$name] = ['status' => 'ddl_started', 'before' => $now];
                self::writePrivateJson($backupDirectory . '/journal.json', $journal);
                $phase = 'alter_table';
                $sql = 'ALTER TABLE ' . $this->qualified($name) . ' ENGINE=InnoDB';
                if ($before['auto_increment'] !== null) $sql .= ', AUTO_INCREMENT=' . $before['auto_increment'];
                $this->pdo->exec($sql);
                $phase = 'post_ddl_verification'; $this->bytes = 0; $after = $this->table($name, false);
                if (!$this->equivalent($before, $after, 'InnoDB')) self::refuse('post_ddl_preservation_mismatch');
                $journal['tables'][$name] = ['status' => 'verified', 'after' => $after];
                self::writePrivateJson($backupDirectory . '/journal.json', $journal);
            }
            $active = null; $phase = 'final_full_verification';
            $after = $this->capture(false); $this->validateCurrent($plan, $after, true);
            $journal['status'] = 'complete'; $journal['phase'] = 'complete'; $journal['after'] = $after;
            self::writePrivateJson($backupDirectory . '/journal.json', $journal);
            return $this->receipt(true, $plan, $journal, $backupDirectory, $phase);
        } catch (Throwable $error) {
            if ($journal !== null) {
                $journal['status'] = 'incomplete'; $journal['phase'] = $phase;
                $journal['failure'] = ['table' => $active, 'type' => get_class($error),
                    'code' => $error instanceof InvalidArgumentException ? $error->getMessage() : 'database_or_filesystem_operation_failed'];
                try { self::writePrivateJson($backupDirectory . '/journal.json', $journal); } catch (Throwable) { /* Original progress remains on disk. */ }
            }
            return $this->receipt(false, $plan, $journal, $backupDirectory, $phase) + [
                'error' => $error instanceof InvalidArgumentException ? $error->getMessage() : 'database_or_filesystem_operation_failed',
                'error_type' => get_class($error), 'active_table' => $active];
        } finally {
            try { $release = $this->pdo->prepare('SELECT RELEASE_LOCK(?)'); $release->execute([$lockName]); } catch (Throwable) {}
        }
    }

    /** Verify private raw schema/rows without opening a database; never return their values. */
    public static function verifyBackup(string $directory): array
    {
        self::privateDirectory($directory); self::privateDirectory($directory . '/tables');
        $manifest = self::readPrivateJson($directory . '/manifest.json');
        if (($manifest['kind'] ?? null) !== 'engine_migration_backup' || ($manifest['version'] ?? null) !== self::VERSION
            || ($manifest['restoration_contract'] ?? null) !== self::RESTORATION_CONTRACT) self::refuse('invalid_backup_manifest_or_restoration_contract');
        $plan = $manifest['plan'] ?? []; self::validatePlan($plan, $plan['fingerprint'] ?? '');
        if (array_keys($manifest['tables'] ?? []) !== array_keys($plan['tables'])) self::refuse('backup_table_scope_mismatch');
        $tables = []; $total = 0;
        foreach ($plan['tables'] as $name => $summary) {
            self::identifier($name); $entry = $manifest['tables'][$name];
            if (($entry['file'] ?? '') !== 'tables/' . $name . '.json') self::refuse('invalid_backup_file');
            $file = $directory . '/' . $entry['file']; $backup = self::readPrivateJson($file);
            if (!is_string($entry['sha256'] ?? null) || !hash_equals($entry['sha256'], hash_file('sha256', $file))) self::refuse('backup_file_hash_mismatch');
            if (($backup['kind'] ?? null) !== 'engine_migration_table' || ($backup['name'] ?? null) !== $name
                || ($backup['summary'] ?? null) !== $summary || !is_string($backup['create'] ?? null)
                || !hash_equals($summary['schema_sha256'], hash('sha256', $backup['create']))
                || !str_starts_with($backup['create'], 'CREATE TABLE `' . $name . '` (')
                || ($backup['columns'] ?? null) !== array_column($summary['columns'], 'Field') || !is_array($backup['rows'] ?? null)) self::refuse('invalid_table_backup');
            $encoded = [];
            foreach ($backup['rows'] as $row) {
                if (!is_array($row) || count($row) !== count($backup['columns'])) self::refuse('backup_row_width_mismatch');
                foreach ($row as $cell) if ($cell !== null && (!is_string($cell) || base64_decode($cell, true) === false)) self::refuse('invalid_backup_binary_encoding');
                $bytes = self::json($row); $encoded[] = $bytes; $total += strlen($bytes);
                if ($total > self::MAX_BYTES || count($encoded) > self::MAX_ROWS) self::refuse('backup_size_limit');
            }
            sort($encoded, SORT_STRING);
            if (count($encoded) !== $summary['row_count'] || !hash_equals($summary['data_sha256'], hash('sha256', self::json($encoded)))) self::refuse('backup_data_digest_mismatch');
            $tables[$name] = ['row_count' => count($encoded), 'sha256' => $entry['sha256']];
        }
        return ['pass' => true, 'kind' => 'engine_migration_backup_verification', 'version' => self::VERSION,
            'plan_fingerprint' => $plan['fingerprint'], 'manifest_sha256' => hash_file('sha256', $directory . '/manifest.json'),
            'database' => $plan['database'], 'table_count' => count($tables), 'tables' => $tables,
            'restoration_contract' => self::RESTORATION_CONTRACT,
            'private_permissions_verified' => true, 'database_accessed' => false];
    }

    private function capture(bool $withRows): array
    {
        $this->bytes = 0;
        $server = $this->pdo->query('SELECT @@server_id AS server_id, @@hostname AS hostname, @@port AS port, @@version AS version')->fetch(PDO::FETCH_ASSOC);
        $statement = $this->pdo->prepare('SELECT TABLE_NAME,TABLE_TYPE,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=? ORDER BY TABLE_NAME');
        $statement->execute([$this->database]); $tables = []; $excluded = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $table) {
            self::identifier($table['TABLE_NAME']);
            if ($table['TABLE_TYPE'] !== 'BASE TABLE') self::refuse('views_or_other_table_objects_not_supported');
            if (!$this->selected($table['TABLE_NAME'])) { $excluded[] = $table['TABLE_NAME']; continue; }
            if (!in_array($table['ENGINE'], ['MyISAM', 'InnoDB'], true)) self::refuse('unsupported_table_engine');
            $tables[$table['TABLE_NAME']] = null;
        }
        if (!$tables || count($tables) > self::MAX_TABLES) self::refuse('table_scope_empty_or_too_large');
        $this->assertObjects(array_keys($tables));
        foreach ($tables as $name => $_) $tables[$name] = $this->table($name, $withRows);
        $out = ['kind' => 'engine_migration_plan', 'version' => self::VERSION, 'database' => $this->database,
            'server_binding' => $server, 'prefixes' => $this->prefixes, 'excluded_base_tables' => $excluded,
            'restoration_contract' => self::RESTORATION_CONTRACT,
            'limits' => ['tables' => self::MAX_TABLES, 'rows_per_table' => self::MAX_ROWS, 'encoded_row_bytes_total' => self::MAX_BYTES],
            'tables' => $tables];
        $summary = $out; foreach ($summary['tables'] as &$table) unset($table['backup']); unset($table);
        $out['fingerprint'] = hash('sha256', self::json($summary));
        return $out;
    }

    private function table(string $name, bool $withRows): array
    {
        self::identifier($name); $q = $this->qualified($name);
        $metadata = $this->pdo->prepare('SELECT ENGINE,AUTO_INCREMENT,TABLE_COLLATION,TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND TABLE_TYPE=\'BASE TABLE\'');
        $metadata->execute([$this->database, $name]); $meta = $metadata->fetch(PDO::FETCH_ASSOC);
        if (!$meta || !in_array($meta['ENGINE'], ['MyISAM', 'InnoDB'], true)) self::refuse('table_missing_or_engine_unsupported');
        $auto = $meta['AUTO_INCREMENT'];
        if ($auto !== null && !preg_match('/^[0-9]+$/D', (string)$auto)) self::refuse('invalid_auto_increment_metadata');
        $columns = $this->pdo->query('SHOW FULL COLUMNS FROM ' . $q)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as &$column) {
            unset($column['Privileges']); self::identifier($column['Field']);
            if (preg_match('/GENERATED|INVISIBLE/i', $column['Extra'])) self::refuse('generated_or_invisible_column_not_supported');
        } unset($column);
        $indexes = $this->pdo->query('SHOW INDEX FROM ' . $q)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($indexes as &$index) {
            if ($index['Index_type'] !== 'BTREE' || empty($index['Column_name'])
                || (isset($index['Visible']) && $index['Visible'] !== 'YES') || !empty($index['Expression'])
                || str_contains(strtolower($index['Comment'] ?? ''), 'disabled')) self::refuse('unsupported_or_disabled_index_type');
            unset($index['Table'], $index['Cardinality'], $index['Packed']);
            ksort($index, SORT_STRING);
        } unset($index);
        foreach ($columns as $column) if (str_contains(strtolower($column['Extra']), 'auto_increment')) {
            $leading = false; $grouped = false;
            foreach ($indexes as $index) if ($index['Column_name'] === $column['Field']) {
                if ((int)$index['Seq_in_index'] === 1 && $index['Sub_part'] === null) $leading = true;
                elseif ((int)$index['Seq_in_index'] > 1) $grouped = true;
            }
            // MyISAM uses a global sequence when any index starts with the AI column,
            // even if that column also occurs later in another composite index.
            // MySQL 8.4 manual, "Using AUTO_INCREMENT", MyISAM Notes (multiple indexes).
            if (!$leading) self::refuse($grouped ? 'grouped_composite_auto_increment_not_supported' : 'auto_increment_leading_index_required');
        }
        usort($indexes, static fn($a, $b) => [$a['Key_name'], (int)$a['Seq_in_index']] <=> [$b['Key_name'], (int)$b['Seq_in_index']]);
        $create = array_values($this->pdo->query('SHOW CREATE TABLE ' . $q)->fetch(PDO::FETCH_NUM))[1];
        $suffix = strrpos($create, "\n) ENGINE=");
        if ($suffix === false) self::refuse('unsupported_create_table_format');
        $options = substr($create, $suffix);
        if (preg_match('/PARTITION|TABLESPACE|DATA DIRECTORY|INDEX DIRECTORY|COMPRESSION|ENCRYPTION|PACK_KEYS|CHECKSUM|DELAY_KEY_WRITE|CONNECTION=/i', $options)
            || preg_match('/ROW_FORMAT=(?!DYNAMIC\b)\w+/i', $options)) self::refuse('unsupported_table_options');
        $normalized = substr($create, 0, $suffix) . preg_replace(['/ENGINE=(?:MyISAM|InnoDB)\b/', '/ ROW_FORMAT=DYNAMIC\b/'], ['ENGINE=__TARGET__', ''], $options);
        $rows = []; $statement = $this->pdo->query('SELECT * FROM ' . $q);
        while (($row = $statement->fetch(PDO::FETCH_NUM)) !== false) {
            $row = array_map(static fn($value) => $value === null ? null : base64_encode((string)$value), $row);
            $encoded = self::json($row); $this->bytes += strlen($encoded);
            if ($this->bytes > self::MAX_BYTES || count($rows) >= self::MAX_ROWS) self::refuse('snapshot_size_limit');
            $rows[] = $encoded;
        }
        sort($rows, SORT_STRING);
        $out = ['engine' => $meta['ENGINE'], 'action' => $meta['ENGINE'] === 'MyISAM' ? 'convert_to_innodb' : 'preserve_innodb',
            'schema_sha256' => hash('sha256', $create), 'structural_sha256' => hash('sha256', $normalized),
            'columns' => $columns, 'indexes' => $indexes, 'auto_increment' => $auto,
            'collation' => $meta['TABLE_COLLATION'], 'comment' => $meta['TABLE_COMMENT'],
            'row_count' => count($rows), 'data_sha256' => hash('sha256', self::json($rows))];
        if ($withRows) $out['backup'] = ['kind' => 'engine_migration_table', 'version' => self::VERSION, 'name' => $name,
            'summary' => $out, 'create' => $create, 'columns' => array_column($columns, 'Field'),
            'rows' => array_map(static fn($row) => json_decode($row, true, 512, JSON_THROW_ON_ERROR), $rows)];
        return $out;
    }

    private function assertObjects(array $tables): void
    {
        foreach (['TRIGGERS' => 'TRIGGER_SCHEMA', 'ROUTINES' => 'ROUTINE_SCHEMA', 'EVENTS' => 'EVENT_SCHEMA'] as $kind => $schema) {
            $query = $this->pdo->prepare('SELECT COUNT(*) FROM information_schema.' . $kind . ' WHERE ' . $schema . '=?'); $query->execute([$this->database]);
            if ((int)$query->fetchColumn() !== 0) self::refuse('triggers_routines_or_events_not_supported');
        }
        $query = $this->pdo->prepare('SELECT TABLE_SCHEMA,TABLE_NAME,REFERENCED_TABLE_SCHEMA,REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME IS NOT NULL AND (TABLE_SCHEMA=? OR REFERENCED_TABLE_SCHEMA=?)');
        $query->execute([$this->database, $this->database]);
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fk) if (($fk['TABLE_SCHEMA'] === $this->database && in_array($fk['TABLE_NAME'], $tables, true))
            || ($fk['REFERENCED_TABLE_SCHEMA'] === $this->database && in_array($fk['REFERENCED_TABLE_NAME'], $tables, true))) self::refuse('foreign_keys_not_supported');
        $query = $this->pdo->prepare('SELECT TABLE_NAME FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA=? AND PARTITION_NAME IS NOT NULL'); $query->execute([$this->database]);
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) if (in_array($row['TABLE_NAME'], $tables, true)) self::refuse('partitioned_tables_not_supported');
    }

    private function sameScope(array $plan): void
    {
        if ($plan['database'] !== $this->database || $plan['prefixes'] !== $this->prefixes) self::refuse('plan_scope_mismatch');
    }

    private function validateCurrent(array $before, array $now, bool $complete = false): void
    {
        $left = $before; $right = $now; unset($left['tables'], $left['fingerprint'], $right['tables'], $right['fingerprint']);
        if ($left !== $right || array_keys($before['tables']) !== array_keys($now['tables'])) self::refuse('database_scope_or_server_changed');
        foreach ($before['tables'] as $name => $table) {
            $current = $now['tables'][$name];
            if (!$complete && $current === $table) continue;
            if (!$this->equivalent($table, $current, 'InnoDB')) self::refuse('table_data_schema_or_engine_changed');
        }
    }

    private function equivalent(array $before, array $now, string $engine): bool
    {
        if ($now['engine'] !== $engine) return false;
        foreach (['engine', 'action', 'schema_sha256'] as $field) unset($before[$field], $now[$field]);
        return $before === $now;
    }

    private function receipt(bool $pass, array $plan, ?array $journal, string $directory, string $phase): array
    {
        $statuses = []; foreach (($journal['tables'] ?? []) as $name => $table) $statuses[$name] = $table['status'];
        return ['pass' => $pass, 'kind' => 'engine_migration_receipt', 'version' => self::VERSION,
            'database' => $this->database, 'plan_fingerprint' => $plan['fingerprint'], 'phase' => $phase,
            'status' => $pass ? 'complete' : 'incomplete_or_refused', 'table_statuses' => $statuses,
            'before' => $plan, 'after' => $journal['after'] ?? null,
            'backup_manifest_sha256' => is_file($directory . '/manifest.json') ? hash_file('sha256', $directory . '/manifest.json') : null,
            'journal_sha256' => is_file($directory . '/journal.json') ? hash_file('sha256', $directory . '/journal.json') : null,
            'atomic_transaction' => false, 'successful_ddl_automatically_reverted' => false,
            'recovery' => 'Keep maintenance active. Verify the backup; rerun the same plan/fingerprint/backup directory. Completed tables are reverified. Changed or unsupported states refuse. Restore raw backup only into a separately reviewed empty database; no automatic destructive restore.'];
    }

    private static function validatePlan(array $plan, string $fingerprint): void
    {
        if (($plan['kind'] ?? null) !== 'engine_migration_plan' || ($plan['version'] ?? null) !== self::VERSION
            || ($plan['restoration_contract'] ?? null) !== self::RESTORATION_CONTRACT
            || !is_array($plan['tables'] ?? null) || !$plan['tables'] || count($plan['tables']) > self::MAX_TABLES
            || !preg_match('/^[a-f0-9]{64}$/D', $fingerprint) || ($plan['fingerprint'] ?? null) !== $fingerprint) self::refuse('invalid_plan');
        $copy = $plan; unset($copy['fingerprint']);
        if (!hash_equals($fingerprint, hash('sha256', self::json($copy)))) self::refuse('plan_fingerprint_mismatch');
        foreach ($plan['tables'] as $name => $table) { self::identifier($name); if (!in_array($table['engine'] ?? null, ['MyISAM', 'InnoDB'], true)) self::refuse('invalid_plan_engine'); }
    }

    private function selected(string $table): bool
    {
        foreach ($this->prefixes as $prefix) if (str_starts_with($table, $prefix)) return true;
        return false;
    }

    private function qualified(string $table): string { return '`' . $this->database . '`.`' . self::identifier($table) . '`'; }
    private static function identifier(string $value): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]{1,64}$/D', $value)) self::refuse('unsupported_identifier');
        return $value;
    }
    private static function refuse(string $code): never { throw new InvalidArgumentException($code); }
    private static function json(array $value): string { return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); }

    private static function newPrivateDirectory(string $path): void
    {
        if (!str_starts_with($path, '/') || str_contains($path, '/..') || file_exists($path) || is_link($path)
            || !is_dir(dirname($path)) || realpath(dirname($path)) !== dirname($path)) self::refuse('new_absolute_private_directory_required');
        if (!mkdir($path, 0700)) self::refuse('private_directory_creation_failed');
        chmod($path, 0700); self::privateDirectory($path);
    }
    private static function privateDirectory(string $path): void
    {
        if (!str_starts_with($path, '/') || is_link($path) || !is_dir($path) || realpath($path) !== $path
            || (fileperms($path) & 0077) !== 0 || fileowner($path) !== self::currentUid()) self::refuse('private_directory_required');
    }
    private static function readPrivateJson(string $path): array
    {
        if (is_link($path) || !is_file($path) || (fileperms($path) & 0077) !== 0 || fileowner($path) !== self::currentUid()
            || filesize($path) > self::MAX_BYTES * 2) self::refuse('private_bounded_file_required');
        $result = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($result)) self::refuse('invalid_json_document');
        return $result;
    }
    private static function currentUid(): int
    {
        if (!function_exists('posix_geteuid')) self::refuse('posix_effective_uid_required');
        return posix_geteuid();
    }
    private function restoreSession(): void
    {
        if ($this->originalSession === null) return;
        foreach ($this->originalSession as $key => $value) {
            try { $this->setSessionValue($key, $value); } catch (Throwable) { /* Never hide the migration outcome. */ }
        }
        $this->originalSession = null;
    }
    private function setSessionValue(string $key, mixed $value): void
    {
        $set = $this->pdo->prepare('SET SESSION ' . $key . '=?');
        if ($key === 'sql_quote_show_create') $set->bindValue(1, (int)$value, PDO::PARAM_INT);
        else $set->bindValue(1, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $set->execute();
    }
    private static function writePrivateJson(string $path, array $value, bool $exclusive = false): void
    {
        self::privateDirectory(dirname($path));
        if (is_link($path) || ($exclusive && file_exists($path))) self::refuse('private_file_must_not_exist');
        $temp = dirname($path) . '/.' . basename($path) . '.' . bin2hex(random_bytes(8)) . '.tmp';
        $handle = fopen($temp, 'x+b');
        if (!$handle) self::refuse('private_file_creation_failed');
        try {
            chmod($temp, 0600); $bytes = self::json($value) . "\n"; $offset = 0;
            while ($offset < strlen($bytes)) { $count = fwrite($handle, substr($bytes, $offset)); if (!$count) self::refuse('private_file_write_failed'); $offset += $count; }
            if (!fflush($handle) || !fsync($handle)) self::refuse('private_file_flush_failed');
        } finally { fclose($handle); }
        if (!rename($temp, $path)) { @unlink($temp); self::refuse('private_file_publish_failed'); }
    }
}
