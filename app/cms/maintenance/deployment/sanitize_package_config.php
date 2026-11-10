<?php
declare(strict_types=1);

// Print a fixed, credential-free database.php template. This command does not
// inspect or modify the running site's configuration or any target path.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if ($argc !== 1) {
    fwrite(STDERR, "Usage: php sanitize_package_config.php > /private/package-copy/config/database.php\n");
    exit(2);
}
echo <<<'DATABASE'
<?php
use app\cms\config\Environment;
use app\cms\database\DatabaseSettings;

// BEGIN CMS DATABASE SETTINGS
$cmsDatabaseSettings = [];
// END CMS DATABASE SETTINGS
// Fill installation defaults and validate the effective database configuration.
$cmsDatabaseSettings = DatabaseSettings::fallbacks();
$database = [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => Environment::value('DB_HOST', $cmsDatabaseSettings['DB_HOST']),
            'port'        => Environment::value('DB_PORT', $cmsDatabaseSettings['DB_PORT']),
            'database'    => Environment::value('DB_NAME', $cmsDatabaseSettings['DB_NAME']),
            'username'    => Environment::value('DB_USER', $cmsDatabaseSettings['DB_USER']),
            'password'    => Environment::value('DB_PASSWORD', $cmsDatabaseSettings['DB_PASSWORD']),
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => \app\cms\database\TableNames::cms(),
            'strict'      => true,
            'engine'      => null,
            'options'    => [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ],
    ],
];
$admin = require base_path('app/cms/admin/config/database.php');
foreach ($admin['connections'] as $name => $connection) {
    $database['connections']['plugin.admin.' . $name] = $connection;
}
return $database;
DATABASE;
echo "\n";
