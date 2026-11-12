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


// Fill installation defaults and validate the effective database configuration.
$cmsDatabaseSettings = DatabaseSettings::fallbacks();
$database = [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => $cmsDatabaseSettings['DB_HOST'],
            'port'        => $cmsDatabaseSettings['DB_PORT'],
            'database'    => $cmsDatabaseSettings['DB_NAME'],
            'username'    => $cmsDatabaseSettings['DB_USER'],
            'password'    => $cmsDatabaseSettings['DB_PASSWORD'],
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => \app\cms\database\TableNames::cms(),
            'strict'      => true,
            'engine'      => null,
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 设置查询结果默认返回关联数组
            ],
            'pool' => \app\cms\config\DeploymentConfig::group('database')['pool'],
        ],
    ],
];

// Preserve backend connection identities and their original strict/prefix settings.
$admin = require base_path('app/cms/admin/config/database.php');
foreach ($admin['connections'] as $name => $connection) {
    $database['connections']['plugin.admin.' . $name] = $connection;
}
return $database;
DATABASE;
echo "\n";
