<?php
use app\cms\config\Environment;
use app\cms\database\DatabaseSettings;

// BEGIN CMS DATABASE SETTINGS
$cmsDatabaseSettings = [
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '13307',
    'DB_NAME' => 'webman_cms',
    'DB_USER' => 'root',
    'DB_PASSWORD' => 'dzh123456',
    'DB_PREFIX' => 'wa_',
    'DB_TABLE_LAYOUT' => 'unified-backend-v1',
    'CMS_DB_PREFIX' => 'wa_',
    'ADMIN_DB_PREFIX' => 'wa_backend_',
];
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
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 设置查询结果默认返回关联数组
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

// Preserve backend connection identities and their original strict/prefix settings.
$admin = require base_path('app/cms/admin/config/database.php');
foreach ($admin['connections'] as $name => $connection) {
    $database['connections']['plugin.admin.' . $name] = $connection;
}
return $database;
