<?php
/** Private Redis settings; PHP parameters and local .env reload per request. */
use app\cms\config\Environment;
$cmsRedisEnvironment = Environment::overrides();
return [
    'host' => (string)($cmsRedisEnvironment['CMS_REDIS_HOST'] ?? '127.0.0.1'),
    'port' => $cmsRedisEnvironment['CMS_REDIS_PORT'] ?? 6379,
    'username' => (string)($cmsRedisEnvironment['CMS_REDIS_USERNAME'] ?? ''),
    'password' => (string)($cmsRedisEnvironment['CMS_REDIS_PASSWORD'] ?? ''),
    // Redis 数据库编号：0 为第一个库，1 为第二个库；.env 同名配置优先。
    'database' => $cmsRedisEnvironment['CMS_REDIS_DATABASE'] ?? 0,
    'timeout' => $cmsRedisEnvironment['CMS_REDIS_TIMEOUT'] ?? '0.5',
    'read_timeout' => $cmsRedisEnvironment['CMS_REDIS_READ_TIMEOUT'] ?? '0.5',
    'prefix' => (string)($cmsRedisEnvironment['CMS_REDIS_PREFIX'] ?? 'cms:'),
];
