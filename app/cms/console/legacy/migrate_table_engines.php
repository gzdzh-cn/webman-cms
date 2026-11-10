#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Offline engine maintenance. Default inspection never changes database or files. */
ini_set('zend.exception_ignore_args', '1');
ini_set('display_errors', '0');
ini_set('log_errors', '0');
$usage = <<<'HELP'
Usage:
  php webman cms:migrate-engines [--prefixes=ey_,wa_] [--plan=/absolute/new-plan.json]
  php webman cms:migrate-engines --apply --plan=/absolute/plan.json --fingerprint=SHA256 --backup-dir=/absolute/new-backup --maintenance-confirmed
  php webman cms:migrate-engines --verify-backup --backup-dir=/absolute/backup

Default: read-only bounded inspection of all matching tables, including existing InnoDB.
Other base-table prefixes are excluded and retained; unsupported schema objects refuse.
Plan files and backup directories must be private and must not be publicly served.
Stop ALL application writers and schema changes before --apply, including background jobs.
--maintenance-confirmed asserts that this offline window remains active through recovery.
DDL is NOT atomic: prior successful conversions remain after failure. Keep the backup/journal.
Resume with the same plan/fingerprint/directory; changed table data or structures refuse.
There is no automatic destructive restore. Verify raw backup before restoring to an empty DB.
HELP;
$options = [];
foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') { echo $usage, "\n"; exit(0); }
    if (in_array($argument, ['--apply', '--verify-backup', '--maintenance-confirmed'], true)) { $key = substr($argument, 2); $value = true; }
    elseif (preg_match('/^--(prefixes|plan|fingerprint|backup-dir)=(.+)$/D', $argument, $match)) { [, $key, $value] = $match; }
    else { fwrite(STDERR, "Invalid argument; use --help.\n"); exit(2); }
    if (isset($options[$key])) { fwrite(STDERR, "Duplicate argument.\n"); exit(2); }
    $options[$key] = $value;
}
try {
    require dirname(__DIR__, 2) . '/database/EngineMigration.php';
    $apply = $options['apply'] ?? false; $verify = $options['verify-backup'] ?? false;
    $root = dirname(__DIR__, 4);
    foreach (['plan', 'backup-dir'] as $key) if (isset($options[$key])) {
        $path = $options[$key];
        if (!str_starts_with($path, '/') || str_contains($path, '/..')) throw new InvalidArgumentException('absolute_private_artifact_path_required');
        foreach (['public', 'template', 'upload', 'uploads', 'static'] as $publicRoot) {
            if ($path === $root . '/' . $publicRoot || str_starts_with($path, $root . '/' . $publicRoot . '/')) throw new InvalidArgumentException('artifact_path_must_not_be_public');
        }
    }
    if ($verify) {
        if ($apply || !isset($options['backup-dir']) || count($options) !== 2) throw new InvalidArgumentException('verify_backup_arguments_invalid');
        $result = \app\cms\database\EngineMigration::verifyBackup($options['backup-dir']);
    } else {
        if ($apply && (!isset($options['plan'], $options['fingerprint'], $options['backup-dir'], $options['maintenance-confirmed']) || isset($options['prefixes']))) throw new InvalidArgumentException('apply_requires_plan_fingerprint_backup_and_maintenance');
        if (!$apply && array_intersect(['fingerprint', 'backup-dir', 'maintenance-confirmed'], array_keys($options))) throw new InvalidArgumentException('inspect_arguments_invalid');
        require $root . '/vendor/autoload.php';
        \app\cms\config\Environment::initialize();
        $database = \app\cms\database\DatabaseSettings::values()['DB_NAME'];
        if (!preg_match('/^[a-zA-Z0-9_]{1,64}$/D', $database)) throw new InvalidArgumentException('invalid_database_identifier');
        $plan = null;
        if ($apply) {
            $path = $options['plan'];
            if (!function_exists('posix_geteuid')) throw new InvalidArgumentException('posix_effective_uid_required');
            $uid = posix_geteuid();
            if (!str_starts_with($path, '/') || realpath($path) !== $path || is_link($path) || !is_file($path) || (fileperms($path) & 0077) !== 0 || fileowner($path) !== $uid || filesize($path) > 16 * 1024 * 1024) throw new InvalidArgumentException('private_plan_file_required');
            $plan = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            $prefixes = $plan['prefixes'] ?? [];
        } else $prefixes = isset($options['prefixes']) ? explode(',', $options['prefixes']) : [\app\cms\database\TableNames::cms(), \app\cms\database\TableNames::admin()];
        $pdo = \app\cms\database\Connection::environment();
        $migration = new \app\cms\database\EngineMigration($pdo, $database, $prefixes);
        $result = $apply ? $migration->apply($plan, $options['fingerprint'], $options['backup-dir'], true) : $migration->inspect();
        if (!$apply && isset($options['plan'])) {
            $path = $options['plan'];
            if (!str_starts_with($path, '/') || realpath(dirname($path)) !== dirname($path) || is_link($path)) throw new InvalidArgumentException('new_absolute_plan_path_required');
            $oldMask = umask(0077);
            try {
                $file = fopen($path, 'x+b'); if (!$file) throw new InvalidArgumentException('plan_file_must_not_exist');
                try { $bytes = json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
                    if (fwrite($file, $bytes) !== strlen($bytes) || !fflush($file) || !fsync($file)) throw new RuntimeException('plan_write_failed');
                } finally { fclose($file); }
            } finally { umask($oldMask); }
        }
    }
    echo json_encode(['mode' => $verify ? 'verify-backup' : ($apply ? 'apply' : 'inspect'), 'result' => $result], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
    exit(($result['pass'] ?? true) ? 0 : 1);
} catch (Throwable $error) {
    // Driver messages and traces may include SQL, values or connection credentials.
    fwrite(STDERR, json_encode(['error' => $error instanceof InvalidArgumentException ? $error->getMessage() : 'engine_migration_failed', 'type' => get_class($error)], JSON_UNESCAPED_SLASHES) . "\n");
    exit(1);
}
