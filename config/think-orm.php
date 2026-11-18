<?php
$dbSettings = \app\cms\database\DatabaseSettings::values();
$database = [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type' => 'mysql',
            'hostname' => $dbSettings['DB_HOST'],
            'hostport' => $dbSettings['DB_PORT'],
            'database' => $dbSettings['DB_NAME'],
            'username' => $dbSettings['DB_USER'],
            'password' => $dbSettings['DB_PASSWORD'],
            'charset' => 'utf8mb4',
            'prefix' => \app\cms\database\TableNames::cms(),
            'break_reconnect' => true,
            'trigger_sql' => false,
            'params' => [PDO::ATTR_TIMEOUT => 5],
        ],
    ],
];

$admin = require base_path('app/cms/admin/config/thinkorm.php');
foreach ($admin['connections'] as $name => $connection) {
    $database['connections']['plugin.admin.' . $name] = $connection;
}
return $database;
