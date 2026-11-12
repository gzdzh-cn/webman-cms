<?php
declare(strict_types=1);

// Defaults to a SELECT-only dry run. --apply requires the exact reviewed fingerprint.
require_once dirname(__DIR__, 4) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/database/NavigationMigration.php';

use app\cms\database\NavigationMigration;

\app\cms\config\Environment::initialize();
$apply = in_array('--apply', $argv, true);
if ($apply && in_array('--dry-run', $argv, true)) { fwrite(STDERR, "Choose either --apply or --dry-run\n"); exit(1); }
$expected = '';
$rollback = '';
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--expect=')) $expected = substr($argument, 9);
    elseif (str_starts_with($argument, '--rollback=')) $rollback = substr($argument, 11);
    elseif (!in_array($argument, ['--dry-run', '--apply'], true)) {
        fwrite(STDERR, "Unknown argument\n"); exit(1);
    }
}
$pdo = null;
$locked = false;
try {
    $database = \app\cms\database\DatabaseSettings::values()['DB_NAME'];
    $prefix = \app\cms\database\TableNames::admin();
    if (!preg_match('/^[a-zA-Z0-9_]+$/D', $database) || !preg_match('/^[a-zA-Z0-9_]+$/D', $prefix)) throw new RuntimeException('Invalid database identifier');
    if ($apply && !preg_match('/^[a-f0-9]{64}$/D', $expected)) throw new RuntimeException('--apply requires --expect=<reviewed dry-run fingerprint>');
    if ($rollback !== '' && !preg_match('/^[a-f0-9]{64}$/D', $rollback)) throw new RuntimeException('Invalid rollback receipt fingerprint');
    $pdo = \app\cms\database\Connection::environment();
    $read = static function (string $suffix, bool $forUpdate = false) use ($pdo, $prefix): array {
        $where = $suffix === 'options' ? " WHERE name='system_config'" : '';
        return $pdo->query('SELECT * FROM `' . $prefix . $suffix . '`' . $where . ' ORDER BY id' . ($forUpdate ? ' FOR UPDATE' : ''))->fetchAll(PDO::FETCH_ASSOC);
    };
    if ($apply) {
        $query = $pdo->prepare('SELECT GET_LOCK(?, 10)');
        $query->execute(['cms-navigation-' . $database . '-' . $prefix]);
        if ((int)$query->fetchColumn() !== 1) throw new RuntimeException('Navigation migration is already running');
        $locked = true;
        $engines = $pdo->prepare('SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME IN (?,?,?,?)');
        $engines->execute([$database, $prefix . 'rules', $prefix . 'roles', $prefix . 'admin_roles', $prefix . 'options']);
        $tables = $engines->fetchAll(PDO::FETCH_KEY_PAIR);
        if (count($tables) !== 4 || array_filter($tables, static fn($engine) => strtoupper((string)$engine) !== 'INNODB')) throw new RuntimeException('Transactional InnoDB authorization/configuration tables are required');
        $pdo->beginTransaction();
    } else {
        $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY');
    }
    $before = $read('rules', $apply); $roles = $read('roles', $apply); $adminRoles = $read('admin_roles', $apply);
    $options = $read('options', $apply);
    $fallback = json_decode(file_get_contents(\app\cms\Paths::resource('defaults/pear.config.json')), true, 512, JSON_THROW_ON_ERROR);
    $cmsPrefix=\app\cms\database\TableNames::cms();
    $backupStore=new \app\cms\persistence\StateStore('backups:navigation',null,$cmsPrefix);
    $directory='database://backups:navigation';
    $sourceReceipt = null;
    if ($rollback !== '') {
        $path = $directory . '/navigation-' . $rollback . '.json.receipt.json';
        $sourceReceipt=$backupStore->read(basename($path));
        if(!$sourceReceipt)throw new RuntimeException('Owned navigation apply receipt is missing');
        if (($sourceReceipt['database'] ?? '') !== $database || ($sourceReceipt['admin_prefix'] ?? '') !== $prefix || ($sourceReceipt['fingerprint'] ?? '') !== $rollback) throw new RuntimeException('Navigation receipt does not own this database/prefix');
        $plan = NavigationMigration::rollbackPlan($before, $roles, $adminRoles, $options, $sourceReceipt);
    } else {
        $plan = NavigationMigration::plan($before, $roles, $adminRoles, $options, $fallback);
    }
    if ($apply && !hash_equals($expected, $plan['fingerprint'])) throw new RuntimeException('The reviewed state changed; generate a new dry run');
    $backupPath = null;
    $receiptPath = null;
    $writePrivate = static function (string $path, array $value) use ($backupStore): void {
        $name=basename($path);$old=$backupStore->read($name);
        if($old && $old!==$value)throw new RuntimeException('Existing migration backup differs');
        $backupStore->write($name,$value);
    };
    if ($apply && ($plan['operations'] || $plan['option_operation'])) {
        $backupPath = $directory . '/navigation-' . ($rollback !== '' ? 'rollback-' : '') . $plan['fingerprint'] . '.json';
        $backup = ['version' => $plan['version'], 'fingerprint' => $plan['fingerprint'], 'database' => $database, 'admin_prefix' => $prefix,
            'rules' => $before, 'roles' => $roles, 'admin_roles' => $adminRoles, 'options' => $options,
            'operations' => $plan['operations'], 'option_operation' => $plan['option_operation']];
        $writePrivate($backupPath, $backup);
        foreach ($plan['operations'] as $operation) {
            $values = $operation['operation'] === 'insert' ? $operation['after'] + ['created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')] : ($operation['changes'] ?? []);
            $columns = array_keys($values);
            if ($operation['operation'] === 'insert') {
                $sql = 'INSERT INTO `' . $prefix . 'rules` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
                $parameters = array_values($values);
            } elseif ($operation['operation'] === 'delete') {
                $sql = 'DELETE FROM `' . $prefix . 'rules` WHERE id=?';
                $parameters = [$operation['id']];
            } else {
                $sql = 'UPDATE `' . $prefix . 'rules` SET ' . implode(',', array_map(static fn($column) => '`' . $column . '`=?', $columns)) . ' WHERE id=?';
                $parameters = [...array_values($values), $operation['id']];
            }
            $query = $pdo->prepare($sql); $query->execute($parameters);
            if ($query->rowCount() !== 1) throw new RuntimeException('A migration operation did not affect exactly one rule');
        }
        $optionOperation = $plan['option_operation'];
        if ($optionOperation) {
            if ($optionOperation['operation'] === 'insert') {
                $query = $pdo->prepare('INSERT INTO `' . $prefix . 'options` (`name`,`value`) VALUES (?,?)');
                $query->execute(['system_config', $optionOperation['after_value']]);
                $backup['option_operation']['after_id'] = (int)$pdo->lastInsertId();
            } elseif ($optionOperation['operation'] === 'delete') {
                $query = $pdo->prepare('DELETE FROM `' . $prefix . 'options` WHERE id=? AND name=?');
                $query->execute([$optionOperation['before']['id'], 'system_config']);
            } else {
                $query = $pdo->prepare('UPDATE `' . $prefix . 'options` SET value=? WHERE id=? AND name=?');
                $query->execute([$optionOperation['after_value'], $optionOperation['before']['id'], 'system_config']);
            }
            if ($query->rowCount() !== 1) throw new RuntimeException('Configuration migration did not affect exactly one option');
        }
        $current = $read('rules');
        $currentById = array_column($current, null, 'id');
        foreach ($plan['after'] as $row) {
            $actual = $currentById[$row['id']] ?? [];
            foreach ($row as $key => $value) if (($actual[$key] ?? null) !== $value && (string)($actual[$key] ?? '') !== (string)$value) throw new RuntimeException('Postmigration rule mismatch');
        }
        if (count($current) !== $plan['rules_after'] || $roles !== $read('roles') || $adminRoles !== $read('admin_roles')) throw new RuntimeException('Postmigration authorization invariant failed');
        $currentOptions = $read('options');
        if ($optionOperation) {
            if ($optionOperation['operation'] === 'delete') {
                if ($currentOptions) throw new RuntimeException('Configuration rollback did not remove its owned row');
            } elseif (count($currentOptions) !== 1 || (string)$currentOptions[0]['value'] !== $optionOperation['after_value']) {
                throw new RuntimeException('Configuration migration verification failed');
            }
        } elseif ($currentOptions !== $options) throw new RuntimeException('Unplanned configuration change');
        if ($rollback === '') {
            $again = NavigationMigration::plan($current, $roles, $adminRoles, $currentOptions, $fallback);
            if ($again['operations'] || $again['option_operation']) throw new RuntimeException('Migration is not repeatable');
        }
        $receiptPath = $backupPath . '.receipt.json';
        $writePrivate($receiptPath, $backup);
    }
    $pdo->commit();
    unset($plan['after']);
    echo json_encode(['mode' => $apply ? 'apply' : 'dry-run', 'direction' => $rollback !== '' ? 'rollback' : 'upgrade', 'database' => $database, 'admin_prefix' => $prefix,
        'backup' => $backupPath, 'receipt' => $receiptPath, 'plan' => $plan], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), PHP_EOL;
} catch (Throwable $error) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    $message = $error instanceof PDOException ? 'Database operation failed (SQLSTATE ' . $error->getCode() . ')' : $error->getMessage();
    fwrite(STDERR, $message . "\n"); exit(1);
} finally {
    if ($locked && $pdo) { $query = $pdo->prepare('SELECT RELEASE_LOCK(?)'); $query->execute(['cms-navigation-' . $database . '-' . $prefix]); }
}
