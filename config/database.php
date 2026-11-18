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
