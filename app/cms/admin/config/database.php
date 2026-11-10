<?php
$dbSettings = \app\cms\database\DatabaseSettings::values();
$connection = [
    'driver' => 'mysql',
    'host' => $dbSettings['DB_HOST'],
    'port' => $dbSettings['DB_PORT'],
    'database' => $dbSettings['DB_NAME'],
    'username' => $dbSettings['DB_USER'],
    'password' => $dbSettings['DB_PASSWORD'],
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_general_ci',
    'strict' => false,
    'engine' => 'InnoDB',
];
return  [
    'default' => 'mysql',
    'connections' => [
        'mysql' => $connection + ['prefix' => \app\cms\database\TableNames::admin()],
        'cms' => $connection + ['prefix' => \app\cms\database\TableNames::cms()],
    ],
];
