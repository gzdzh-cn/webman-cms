<?php
declare(strict_types=1);
/** Fixed-scope child of verify_standalone_deployment.py; never a general DB utility. */
ini_set('zend.exception_ignore_args', '1');
ini_set('display_errors', '0');
ini_set('log_errors', '0');
umask(0077);
set_exception_handler(static function (Throwable $error): void {
    // PDO messages, SQL values and environment contents must never reach a report.
    fwrite(STDERR, 'Standalone child refused: ' . get_class($error) . ':' . $error->getLine() . "\n");
    exit(1);
});

[$script, $action, $rootArgument, $nonce, $role] = $argv + ['', '', '', '', 'primary'];
if (!preg_match('/^[a-f0-9]{12}$/D', $nonce) || !in_array($role, ['primary', 'restore'], true)) {
    throw new RuntimeException('Invalid owned deployment arguments');
}
$root = realpath($rootArgument);
if (!$root || realpath(__FILE__) !== $root . '/app/cms/maintenance/deployment/helper.php') {
    throw new RuntimeException('Child must execute inside the copied application');
}
$journal = dirname($root) . '/ownership.json';
if (!is_file($journal) || is_link($journal) || (fileperms($journal) & 0077) !== 0) {
    throw new RuntimeException('Private ownership journal is required');
}
$state = json_decode((string)file_get_contents($journal), true, 512, JSON_THROW_ON_ERROR);
if (($state['nonce'] ?? '') !== $nonce || ($state['clone_root'] ?? '') !== $root
    || !preg_match('/^[a-f0-9]{48}$/D', $state['ownership_key'] ?? '')
    || basename(dirname($root)) !== $nonce || basename($root) !== 'app'
    || dirname(dirname($root)) !== ($state['control_root'] ?? '')
    || !str_starts_with((string)($state['control_root'] ?? ''), DIRECTORY_SEPARATOR)
    || str_starts_with($state['control_root'] . '/', $state['source_root'] . '/')) {
    throw new RuntimeException('Ownership journal mismatch');
}
foreach (['primary' => '', 'restore' => '_restore'] as $name => $suffix) {
    if (($state['database_names'][$name] ?? '') !== 'wmcms_verify_' . $nonce . $suffix) {
        throw new RuntimeException('Only the registered dedicated database names are allowed');
    }
}
chdir($root);
require $root . '/vendor/autoload.php';
if (base_path() !== $root) throw new RuntimeException('Child base_path points outside the copy');

function output(array $value): never
{
    echo json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    exit;
}
function q(string $identifier): string
{
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $identifier)) throw new RuntimeException('Invalid identifier');
    return chr(96) . $identifier . chr(96);
}
function privateFile(string $path, string $bytes, bool $exclusive = true): void
{
    $handle = fopen($path, $exclusive ? 'x' : 'w');
    if (!$handle) throw new RuntimeException('Cannot write private owned file');
    if (fwrite($handle, $bytes) !== strlen($bytes)) throw new RuntimeException('Incomplete owned file');
    fclose($handle);
    chmod($path, 0600);
}
function saveState(): void
{
    global $state, $journal;
    $temporary = $journal . '.' . bin2hex(random_bytes(5));
    privateFile($temporary, json_encode($state, JSON_THROW_ON_ERROR));
    if (!rename($temporary, $journal)) throw new RuntimeException('Cannot publish ownership state');
}
function envValue(string $value): string
{
    if (str_contains($value, "\0")) throw new RuntimeException('NUL in environment value');
    return '"' . str_replace(
        ["\\", '"', '$', "\n", "\r", "\t"],
        ["\\\\", '\\"', '\\$', '\\n', '\\r', '\\t'], $value) . '"';
}
if ($action === 'configure') {
    if (file_exists($root . '/.env') || is_link($root . '/.env')) throw new RuntimeException('Fresh environment required');
    // Resolve the source through the same configuration stack as the application.
    // The configure child receives the caller environment; later actions are scrubbed.
    $source = \app\cms\database\DatabaseSettings::values($state['source_root']);
    if ($source['DB_USER'] === '') throw new RuntimeException('Required connection setting absent');
    $values = array_intersect_key($source, array_flip(['DB_HOST', 'DB_PORT', 'DB_USER', 'DB_PASSWORD']));
    $values += [
        'DB_NAME' => $state['database_names']['primary'], 'CMS_DB_PREFIX' => 'ey_', 'DB_PREFIX' => 'ey_',
        'ADMIN_DB_PREFIX' => 'wa_', 'DB_TABLE_LAYOUT' => 'legacy', 'WEBMAN_LISTEN' => 'http://127.0.0.1:8793',
        'WEBMAN_WORKERS' => '2', 'CMS_THEME' => 'default', 'CMS_BASE_PATH' => '',
        'APP_ENV' => 'test', 'APP_DEBUG' => 'false', 'TPL_CACHE' => 'false',
        'LOG_ENABLE' => 'false', 'LOG_PRINT_TO_CONSOLE' => 'false', 'LOG_PRINT_TO_FILE' => 'false',
        'SQL_LOG_ENABLE' => 'false', 'SQL_LOG_PRINT_TO_CONSOLE' => 'false', 'SQL_LOG_PRINT_TO_FILE' => 'false',
    ];
    $bytes = '';
    foreach ($values as $name => $value) $bytes .= $name . '=' . envValue((string)$value) . "\n";
    if (Dotenv\Dotenv::parse($bytes) !== $values) throw new RuntimeException('Environment round-trip failed');
    privateFile($root . '/.env', $bytes);
    // This fresh, ownership-checked copy targets a new database, not the source installation.
    // Keep the archive inventory, which describes copied files rather than database identity.
    foreach (['cms-installed.json', 'cms-installing.json', 'cms-installing.lock'] as $name) {
        $copied = $root . '/config/' . $name;
        if (is_link($copied)) throw new RuntimeException('Copied installation state cannot be a symlink');
        if (is_file($copied) && !unlink($copied)) throw new RuntimeException('Cannot reset copied installation identity');
    }
    output(['mode' => 'connection_allowlist_only', 'database' => $values['DB_NAME'],
        'listen' => $values['WEBMAN_LISTEN'], 'workers' => 2, 'mode_0600' => true]);
}

$env = $root . '/.env';
if (!is_file($env) || is_link($env) || (fileperms($env) & 0077) !== 0) {
    throw new RuntimeException('Private copied environment missing');
}
// Use the same effective values as the application instead of an independent
// dotenv/getenv interpretation. Validate the complete owned destination before PDO.
\app\cms\config\Environment::initialize($root);
$settings = \app\cms\database\DatabaseSettings::values($root);
if ($settings['DB_NAME'] !== $state['database_names']['primary']
    || $settings['CMS_DB_PREFIX'] !== 'ey_' || $settings['DB_PREFIX'] !== 'ey_'
    || $settings['ADMIN_DB_PREFIX'] !== 'wa_' || $settings['DB_TABLE_LAYOUT'] !== 'legacy'
    || \app\cms\config\Environment::value('WEBMAN_LISTEN', '', $root) !== 'http://127.0.0.1:8793'
    || \app\cms\config\Environment::value('WEBMAN_WORKERS', '', $root) !== '2'
    || $settings['DB_USER'] === '') {
    throw new RuntimeException('Unsafe copied environment');
}
if ($action === 'inspect-env') {
    output(['database' => $settings['DB_NAME'], 'listen' => 'http://127.0.0.1:8793', 'workers' => 2]);
}
function connection(bool $select = true): PDO
{
    global $state, $role, $settings;
    $pdo = new PDO('mysql:host=' . $settings['DB_HOST'] . ';port=' . $settings['DB_PORT'] . ';charset=utf8mb4',
        $settings['DB_USER'], $settings['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_STRINGIFY_FETCHES => true,
        ]);
    $pdo->exec("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION'");
    if ($select) $pdo->exec('USE ' . q($state['database_names'][$role]));
    return $pdo;
}
function existsDatabase(PDO $pdo, string $name): bool
{
    $query = $pdo->prepare('SELECT 1 FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=?');
    $query->execute([$name]);
    return (bool)$query->fetchColumn();
}
function assertOwned(PDO $pdo): void
{
    global $state, $role, $nonce;
    if (!in_array($state['database_names'][$role], $state['created_databases'], true)) {
        throw new RuntimeException('Database was not created and registered by this rehearsal');
    }
    $query = $pdo->query('SELECT nonce, ownership_key FROM ' . q('__wmcms_deployment_owner'));
    $rows = $query->fetchAll();
    if (count($rows) !== 1 || $rows[0]['nonce'] !== $nonce
        || !hash_equals($state['ownership_key'], $rows[0]['ownership_key'])) {
        throw new RuntimeException('Database ownership marker mismatch');
    }
}
if ($action === 'claim') {
    $pdo = connection(false);
    $name = $state['database_names'][$role];
    if (existsDatabase($pdo, $name) || in_array($name, $state['created_databases'], true)) {
        throw new RuntimeException('Refusing an existing database');
    }
    // No IF NOT EXISTS: a racing creator must fail rather than become owned.
    $pdo->exec('CREATE DATABASE ' . q($name) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $state['created_databases'][] = $name;
    saveState();
    $pdo->exec('USE ' . q($name));
    $pdo->exec('CREATE TABLE ' . q('__wmcms_deployment_owner') . ' (nonce VARCHAR(12) NOT NULL PRIMARY KEY, ownership_key VARCHAR(48) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin');
    $pdo->prepare('INSERT INTO ' . q('__wmcms_deployment_owner') . ' VALUES (?,?)')
        ->execute([$nonce, $state['ownership_key']]);
    output(['created' => $name, 'ownership_marker' => true]);
}
if ($action === 'drop') {
    $server = connection(false);
    $name = $state['database_names'][$role];
    if (!in_array($name, $state['created_databases'], true)) throw new RuntimeException('Unregistered cleanup');
    if (existsDatabase($server, $name)) {
        $server->exec('USE ' . q($name));
        assertOwned($server);
        $server->exec('DROP DATABASE ' . q($name));
    }
    $state['created_databases'] = array_values(array_diff($state['created_databases'], [$name]));
    saveState();
    output(['removed' => $name]);
}

$pdo = connection();
assertOwned($pdo);
function tableNames(PDO $pdo): array
{
    $rows = $pdo->query('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE=\'BASE TABLE\' ORDER BY TABLE_NAME')->fetchAll();
    foreach ($rows as $row) {
        q($row['TABLE_NAME']);
        if (!preg_match('/^(?:ey_|wa_|__wmcms_deployment_owner$)/D', $row['TABLE_NAME'])
            || strcasecmp((string)$row['ENGINE'], 'InnoDB') !== 0) {
            throw new RuntimeException('Only owned baseline InnoDB tables are supported');
        }
    }
    return array_column($rows, 'TABLE_NAME');
}
function objects(PDO $pdo): array
{
    $out = [];
    foreach (['VIEWS' => 'TABLE_SCHEMA', 'TRIGGERS' => 'TRIGGER_SCHEMA',
              'ROUTINES' => 'ROUTINE_SCHEMA', 'EVENTS' => 'EVENT_SCHEMA'] as $table => $column) {
        $out[$table] = (int)$pdo->query('SELECT COUNT(*) FROM information_schema.' . $table . ' WHERE ' . $column . '=DATABASE()')->fetchColumn();
    }
    $out['FOREIGN_KEYS'] = (int)$pdo->query('SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL')->fetchColumn();
    return $out;
}
function capture(PDO $pdo, bool $withRows): array
{
    $tables = [];
    $total = 0;
    foreach (tableNames($pdo) as $name) {
        $columns = $pdo->query('SHOW FULL COLUMNS FROM ' . q($name))->fetchAll();
        foreach ($columns as $column) {
            if (str_contains(strtoupper($column['Extra']), 'GENERATED')) throw new RuntimeException('Generated columns are outside the owned restore contract');
        }
        $create = array_values($pdo->query('SHOW CREATE TABLE ' . q($name))->fetch())[1];
        $rows = [];
        $statement = $pdo->query('SELECT * FROM ' . q($name));
        while ($row = $statement->fetch(PDO::FETCH_NUM)) {
            $encoded = array_map(static fn($cell) => $cell === null ? null : base64_encode((string)$cell), $row);
            $bytes = json_encode($encoded, JSON_THROW_ON_ERROR);
            $total += strlen($bytes);
            if ($total > 100 * 1024 * 1024 || count($rows) >= 100000) throw new RuntimeException('Owned rehearsal backup size bound exceeded');
            $rows[] = $bytes;
        }
        sort($rows, SORT_STRING);
        $record = ['schema_sha256' => hash('sha256', $create),
            'data_sha256' => hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR)),
            'row_count' => count($rows)];
        if ($withRows) $record += ['create' => $create, 'columns' => array_column($columns, 'Field'), 'rows' => $rows];
        $tables[$name] = $record;
    }
    return ['tables' => $tables, 'non_table_objects' => objects($pdo)];
}
if ($action === 'snapshot') output(capture($pdo, false));
if ($action === 'backup') {
    if ($role !== 'primary') throw new RuntimeException('Only the owned primary is backed up');
    if (array_sum(objects($pdo)) !== 0) throw new RuntimeException('Unsupported schema objects; backup remains pending');
    $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    $pdo->exec('SET TRANSACTION READ ONLY');
    $pdo->beginTransaction();
    $snapshot = capture($pdo, true);
    $pdo->rollBack();
    $backup = ['kind' => 'owned_standalone_table_backup', 'nonce' => $nonce,
        'database' => $state['database_names']['primary'], 'snapshot' => $snapshot];
    $path = $root . '/.verify/backup.json';
    privateFile($path, json_encode($backup, JSON_THROW_ON_ERROR));
    $state['backup_sha256'] = hash_file('sha256', $path);
    saveState();
    output(['mode' => 'owned_PDO_consistent_table_snapshot', 'tables' => count($snapshot['tables']),
        'non_table_objects' => $snapshot['non_table_objects'], 'private_mode_0600' => true]);
}
if ($action === 'restore') {
    if ($role !== 'restore' || tableNames($pdo) !== ['__wmcms_deployment_owner']) {
        throw new RuntimeException('Restore requires the newly claimed empty restore database');
    }
    $path = $root . '/.verify/backup.json';
    if (!is_file($path) || is_link($path) || (fileperms($path) & 0077) !== 0
        || !hash_equals($state['backup_sha256'] ?? '', hash_file('sha256', $path))) {
        throw new RuntimeException('Private backup identity mismatch');
    }
    $backup = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if ($backup['kind'] !== 'owned_standalone_table_backup' || $backup['nonce'] !== $nonce
        || $backup['database'] !== $state['database_names']['primary']
        || array_sum($backup['snapshot']['non_table_objects']) !== 0) throw new RuntimeException('Backup scope mismatch');
    foreach ($backup['snapshot']['tables'] as $name => $table) {
        q($name);
        if ($name === '__wmcms_deployment_owner') continue; // Same nonce/key/schema in both owned databases.
        if (!preg_match('/^(?:ey_|wa_)/D', $name)
            || !str_starts_with($table['create'], 'CREATE TABLE ' . q($name) . ' (')
            || hash('sha256', $table['create']) !== $table['schema_sha256']
            || hash('sha256', json_encode($table['rows'], JSON_THROW_ON_ERROR)) !== $table['data_sha256']) {
            throw new RuntimeException('Invalid owned table backup');
        }
        $pdo->exec($table['create']);
        $insert = $pdo->prepare('INSERT INTO ' . q($name) . ' (' . implode(',', array_map('q', $table['columns']))
            . ') VALUES (' . implode(',', array_fill(0, count($table['columns']), '?')) . ')');
        $pdo->beginTransaction();
        foreach ($table['rows'] as $bytes) {
            $encoded = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
            if (count($encoded) !== count($table['columns'])) throw new RuntimeException('Owned row width mismatch');
            $values = [];
            foreach ($encoded as $cell) {
                if ($cell === null) { $values[] = null; continue; }
                $decoded = base64_decode($cell, true);
                if ($decoded === false) throw new RuntimeException('Invalid binary field encoding');
                $values[] = $decoded;
            }
            $insert->execute($values);
        }
        $pdo->commit();
    }
    $expected = $backup['snapshot'];
    foreach ($expected['tables'] as &$table) unset($table['create'], $table['columns'], $table['rows']);
    unset($table);
    if (capture($pdo, false) !== $expected) throw new RuntimeException('Full restored schema/data mismatch');
    output(['restored_tables' => count($expected['tables']), 'full_schema_and_data_hash_equal' => true]);
}

if (!in_array($action, ['fixture', 'verify-fixture'], true) || $role !== 'primary') {
    throw new RuntimeException('Unsupported fixed-scope child action');
}
\app\cms\secure\App::loadAllConfig(['route']);
Webman\ThinkOrm\ThinkOrm::start(null);
if (config('think-orm.connections.mysql.database') !== $state['database_names']['primary']) {
    throw new RuntimeException('Service connection does not point to the dedicated database');
}
$repository = new app\cms\repository\CmsRepository();
$fixturePath = $root . '/.verify/fixture.json';
if ($action === 'fixture') {
    if (file_exists($fixturePath)) throw new RuntimeException('Fixture already exists');
    $marker = 'standalone_' . $nonce;
    $categories = new app\cms\service\CategoryService($repository);
    $content = new app\cms\service\ContentService($repository);
    $ownedAssets = [];
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aJXcAAAAASUVORK5CYII=', true);
    $directory = $root . '/public/uploads/' . $marker;
    if (file_exists($directory)) throw new RuntimeException('Owned asset collision');
    mkdir($directory, 0755, true);
    file_put_contents($directory . '/image.png', $png);
    foreach (['uploads', 'upload'] as $prefix) {
        $ownedAssets['/' . $prefix . '/' . $marker . '/image.png'] = hash('sha256', $png);
    }
    $repository->transaction(function () use ($repository, $marker): void {
        foreach (['web_tpl_theme' => 'default', 'web_name' => $marker, 'web_title' => $marker,
                  'web_language_switch' => '0', 'web_citysite_open' => '0',
                  'seo_pseudo' => '1', 'seo_dynamic_format' => '1', 'seo_rewrite_format' => '1',
                  'system_langnum' => '1'] as $name => $value) {
            $query = $repository->table('config')->where(['name' => $name, 'lang' => 'cn']);
            if ($query->find()) $query->update(['value' => $value]);
            else $repository->table('config')->insert(['name' => $name, 'value' => $value,
                'inc_type' => str_starts_with($name, 'seo_') ? 'seo' : (str_starts_with($name, 'system_') ? 'system' : 'web'), 'lang' => 'cn']);
        }
    });
    $fixture = ['marker' => $marker, 'owned_assets' => $ownedAssets, 'types' => [], 'aids' => [], 'pages' => []];
    foreach (['article' => 1, 'product' => 2, 'single' => 6] as $kind => $model) {
        $fixture['types'][$kind] = $categories->save(['typename' => $marker . '_' . $kind,
            'dirname' => $marker . '_' . $kind, 'current_channel' => $model, 'lang' => 'cn',
            'templist' => 'lists_' . $kind . '.htm', 'tempview' => 'view_' . $kind . '.htm'], 1);
        $input = ['title' => $marker . '_' . $kind, 'typeid' => $fixture['types'][$kind],
            'content' => '<p>' . $marker . '_' . $kind . '_PC</p>',
            'content_ey_m' => '<p>' . $marker . '_' . $kind . '_MOBILE</p>',
            'litpic' => '/uploads/' . $marker . '/image.png', 'status' => 1, 'arcrank' => 0];
        $fixture['aids'][$kind] = $kind === 'single'
            ? $content->saveSingle($fixture['types'][$kind], $input, 1) : $content->save($input, 1);
        foreach (['pc' => 'PC', 'mobile' => 'MOBILE'] as $device => $bodyMarker) {
            $params = ['m' => 'home', 'c' => $kind === 'single' ? 'Lists' : 'View',
                'a' => 'index', $kind === 'single' ? 'tid' : 'aid' => $kind === 'single'
                    ? $fixture['types'][$kind] : $fixture['aids'][$kind], 'lang' => 'cn', 'goto' => $device];
            $fixture['pages'][] = ['name' => $kind . '_' . $device,
                'path' => '/index.php?' . http_build_query($params), 'contains' => $marker . '_' . $kind . '_' . $bodyMarker];
        }
    }
    foreach (['pc', 'mobile'] as $device) {
        $fixture['pages'][] = ['name' => 'home_' . $device, 'path' => '/?lang=cn&goto=' . $device, 'contains' => $marker];
        $fixture['pages'][] = ['name' => 'article_list_' . $device,
            'path' => '/index.php?' . http_build_query(['m' => 'home', 'c' => 'Lists', 'a' => 'index',
                'tid' => $fixture['types']['article'], 'lang' => 'cn', 'goto' => $device]),
            'contains' => $marker . '_article'];
        $fixture['pages'][] = ['name' => 'search_' . $device,
            'path' => '/search?' . http_build_query(['keywords' => $marker, 'lang' => 'cn', 'goto' => $device]),
            'contains' => $marker . '_article'];
    }
    // A separate owned template identifies actual worker PIDs; original templates are untouched.
    $probe = 'lists_standalone_' . $nonce . '.htm';
    $probeType = $categories->save(['typename' => $marker . '_probe', 'dirname' => $marker . '_probe',
        'current_channel' => 6, 'lang' => 'cn', 'templist' => $probe], 1);
    foreach (['pc', 'mobile'] as $device) {
        privateFile($root . '/template/default/' . $device . '/' . $probe,
            "{eyou:php}echo 'STANDALONE_" . $nonce . ":' . getmypid();{/eyou:php}");
    }
    $fixture['worker_probe'] = '/index.php?' . http_build_query([
        'm' => 'home', 'c' => 'Lists', 'a' => 'index', 'tid' => $probeType, 'lang' => 'cn', 'goto' => 'pc']);
    privateFile($fixturePath, json_encode($fixture, JSON_THROW_ON_ERROR));
    app\cms\compat\Cache::clear();
    output($fixture);
}

$fixture = json_decode((string)file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);
$checks = [];
foreach ($fixture['aids'] as $kind => $aid) {
    $row = $repository->table($kind . '_content')->where('aid', $aid)->find();
    $checks[$kind] = $row && $row['content'] === '<p>' . $fixture['marker'] . '_' . $kind . '_PC</p>'
        && $row['content_ey_m'] === '<p>' . $fixture['marker'] . '_' . $kind . '_MOBILE</p>';
}
$adminCount = (int)$pdo->query('SELECT COUNT(*) FROM wa_admins')->fetchColumn();
$menuCount = (int)$pdo->query('SELECT COUNT(*) FROM wa_rules')->fetchColumn();
if (in_array(false, $checks, true) || !$adminCount || !$menuCount) throw new RuntimeException('Restart fixture/admin/menu data missing');
output(['persisted_content' => $checks, 'admin_count' => $adminCount, 'menu_count' => $menuCount]);
