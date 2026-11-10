<?php
declare(strict_types=1);
namespace app\cms\cache;
final class RedisStorage implements StorageInterface
{
    public function read(string $key): ?string
    {
        $client = RedisConnection::client();
        $client->clearLastError();
        $bytes = $client->get(RedisConnection::key('cache:' . $key));
        if ($bytes === false && $client->getLastError() !== null) throw new \RuntimeException('Redis 缓存读取失败');
        if ($bytes === false) return null;
        if (!is_string($bytes)) throw new \RuntimeException('Redis 缓存读取失败');
        return $bytes;
    }
    public function write(string $key, string $bytes, int $ttl = 0): void
    {
        // Expiry also bounds abandoned generations after online invalidation.
        if (!RedisConnection::client()->setex(RedisConnection::key('cache:' . $key), $ttl > 0 ? $ttl : 86400, $bytes)) throw new \RuntimeException('Redis 缓存写入失败');
    }
    public function remove(string $key): bool
    {
        $deleted = RedisConnection::client()->del(RedisConnection::key('cache:' . $key));
        if (!is_int($deleted)) throw new \RuntimeException('Redis 缓存删除失败');
        return true;
    }
}
