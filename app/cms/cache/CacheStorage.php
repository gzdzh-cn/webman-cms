<?php
declare(strict_types=1);
namespace app\cms\cache;

use Webman\Context;

/** Logical runtime paths identify signed bytes; Redis never opens those paths. */
final class CacheStorage
{
    private static function identity(string $path): string
    {
        $root = rtrim(runtime_path(), '/');
        if (!str_starts_with($path, $root . '/') || str_contains($path, "\0") || str_contains($path, '\\')
            || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path)) throw new \RuntimeException('缓存逻辑路径无效');
        $policy = CachePolicy::snapshot();
        $identity = [\app\cms\template\TemplateMode::current(), \app\cms\template\TemplateDialect::VERSION, \app\cms\template\TemplateCapabilities::COMPILE_VERSION, \app\cms\template\TemplateMode::siteSignature(), $path];
        $generation = CachePolicy::cacheGeneration();
        return $policy['namespace'] . ':' . $generation . ':' . hash('sha256', serialize($identity));
    }
    private static function memory(): MemoryStorage
    {
        $memory = Context::get('cms.cache.memory');
        if (!$memory instanceof MemoryStorage) Context::set('cms.cache.memory', $memory = new MemoryStorage());
        return $memory;
    }
    private static function store(): StorageInterface { return CachePolicy::isRedis() ? new RedisStorage() : new FileStorage(); }
    private static function failed(): bool { return (bool)Context::get('cms.cache.redis.failed'); }
    private static function failure(bool $mutation): void
    {
        Context::set('cms.cache.redis.failed', true);
        RedisConnection::warning();
        // A failed replacement/deletion must not resurrect an older Redis value.
        if ($mutation && !Context::get('cms.cache.redis.invalidated')) {
            CachePolicy::rotate();
            Context::set('cms.cache.redis.invalidated', true);
        }
    }
    public static function read(string $logicalPath): ?string
    {
        $key = self::identity($logicalPath); $memory = self::memory();
        if ($memory->contains($key)) return $memory->read($key);
        if (CachePolicy::isRedis() && self::failed()) return null;
        try {
            $bytes = self::store()->read($key);
            // Dependency validation and execution must use the same verified
            // snapshot even when Redis disappears between those two steps.
            if ($bytes !== null) $memory->write($key, $bytes);
            else $memory->remove($key);
            return $bytes;
        }
        catch (\Throwable $error) { if (!CachePolicy::isRedis()) throw $error; self::failure(false); return null; }
    }
    public static function write(string $logicalPath, string $bytes, int $ttl = 0): void
    {
        $key = self::identity($logicalPath); self::memory()->write($key, $bytes, $ttl);
        if (CachePolicy::isRedis() && self::failed()) { self::failure(true); return; }
        try { self::store()->write($key, $bytes, $ttl); }
        catch (\Throwable $error) { if (!CachePolicy::isRedis()) throw $error; self::failure(true); }
    }
    public static function remove(string $logicalPath): bool
    {
        $key = self::identity($logicalPath); self::memory()->remove($key);
        if (CachePolicy::isRedis() && self::failed()) { self::failure(true); return true; }
        try { return self::store()->remove($key); }
        catch (\Throwable $error) { if (!CachePolicy::isRedis()) throw $error; self::failure(true); return true; }
    }
    public static function clear(): void
    {
        $epochs = CachePolicy::rotate();
        \Webman\Context::set('cms.cache.generation', $epochs['cache_generation']);
        Context::set('cms.cache.memory', new MemoryStorage());
        if (!CachePolicy::isRedis()) (new FileStorage())->collect();
    }
}
