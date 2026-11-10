<?php
declare(strict_types=1);
namespace app\cms\cache;

/** Reuse worker connections only while their request-pinned settings match. */
final class RedisConnection
{
    private static ?\Redis $client = null;
    private static ?array $connectedSettings = null;
    private static int $lastWarning = 0;

    public static function settings(): array
    {
        $pinned = \Webman\Context::get('cms.redis.settings');
        if (is_array($pinned)) return $pinned;
        // Webman's config() snapshot belongs to worker startup. Require only
        // this private config again so both PHP edits and .env keys work online.
        $path = \app\cms\Paths::root('config/cms_cache.php');
        clearstatcache(true, $path);
        $opcacheEnabled = filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)
            && (!in_array(PHP_SAPI, ['cli', 'phpdbg'], true) || filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN));
        // Even validate_timestamps=0 must not reuse old connection parameters.
        // Fail the probe if the active opcode cache cannot be refreshed.
        if ($opcacheEnabled && (!function_exists('opcache_invalidate') || !@opcache_invalidate($path, true))) {
            throw new \RuntimeException('Redis PHP 配置刷新失败，请检查 OPcache 配置刷新权限');
        }
        $settings = require $path;
        foreach (['port', 'database'] as $name) {
            $value = $settings[$name] ?? null;
            if (!(is_int($value) || (is_string($value) && preg_match('/^(?:0|[1-9][0-9]*)$/D', $value)))
                || filter_var($value, FILTER_VALIDATE_INT) === false) throw new \RuntimeException('Redis 数值配置无效');
            $settings[$name] = (int)$value;
        }
        if (!is_string($settings['host'] ?? null) || $settings['host'] === '' || str_contains($settings['host'], "\0")
            || !is_int($settings['port'] ?? null) || $settings['port'] < 1 || $settings['port'] > 65535
            || !is_int($settings['database'] ?? null) || $settings['database'] < 0
            || !is_string($settings['username'] ?? null) || !is_string($settings['password'] ?? null)
            || !is_string($settings['prefix'] ?? null) || !preg_match('/^[A-Za-z0-9:_-]{1,100}$/D', $settings['prefix'])) {
            throw new \RuntimeException('Redis 配置无效');
        }
        foreach (['timeout', 'read_timeout'] as $name) {
            if (!is_numeric($settings[$name] ?? null) || !is_finite((float)$settings[$name]) || $settings[$name] <= 0 || $settings[$name] > 30) {
                throw new \RuntimeException('Redis 超时配置须大于0且不超过30秒');
            }
        }
        \Webman\Context::set('cms.redis.settings', $settings);
        return $settings;
    }

    public static function client(): \Redis
    {
        $settings = self::settings();
        if (self::$client !== null && self::$connectedSettings === $settings) return self::$client;
        self::disconnect();
        if (!extension_loaded('redis')) throw new \RuntimeException('Redis 模式需要 PHP Redis 扩展');
        try {
            $client = new \Redis();
            if (!@$client->connect($settings['host'], $settings['port'], (float)$settings['timeout'], null, 0, (float)$settings['read_timeout'])) throw new \RuntimeException();
            $client->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
            if ($settings['username'] !== '' || $settings['password'] !== '') {
                $auth = $settings['username'] !== '' ? [$settings['username'], $settings['password']] : $settings['password'];
                if (!$client->auth($auth)) throw new \RuntimeException();
            }
            if (!$client->select($settings['database'])) throw new \RuntimeException();
            self::$connectedSettings = $settings;
            return self::$client = $client;
        } catch (\Throwable) {
            self::disconnect();
            throw new \RuntimeException('Redis 连接或认证失败，请检查私有配置及服务状态');
        }
    }

    public static function key(string $suffix): string { return self::settings()['prefix'] . $suffix; }

    public static function disconnect(): void
    {
        try { self::$client?->close(); } catch (\Throwable) {}
        self::$client = null;
        self::$connectedSettings = null;
    }

    public static function warning(string $category = 'cache'): void
    {
        self::disconnect();
        if (time() - self::$lastWarning < 60) return;
        self::$lastWarning = time();
        try { \support\Log::warning($category === 'session' ? 'CMS Redis session unavailable; returning service unavailable' : 'CMS Redis cache unavailable; using request memory without file cache'); } catch (\Throwable) {}
    }

    /** An expiring random key never touches another application's data. */
    public static function probe(): void
    {
        $key = null;
        try {
            $client = self::client();
            if ($client->ping() === false) throw new \RuntimeException();
            $key = self::key('probe:' . hash('sha256', \app\cms\Paths::root()) . ':' . bin2hex(random_bytes(16)));
            $value = bin2hex(random_bytes(16));
            if (!$client->setex($key, 5, $value) || $client->get($key) !== $value || $client->ttl($key) < 1
                || $client->del($key) !== 1 || $client->exists($key) !== 0) throw new \RuntimeException();
        } catch (\Throwable) {
            self::disconnect();
            throw new \RuntimeException('Redis 配置验证失败，未切换缓存模式。请检查 .env 的 CMS_REDIS_* 或 config/cms_cache.php 配置，以及扩展、连接、认证、读写/过期/删除权限和 PHP 配置刷新权限；修正参数后可直接重新保存');
        } finally {
            if ($key !== null && self::$client !== null) { try { self::$client->del($key); } catch (\Throwable) {} }
        }
    }
}
