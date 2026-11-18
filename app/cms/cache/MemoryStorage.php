<?php
declare(strict_types=1);
namespace app\cms\cache;
/** Owned by one request; never a worker singleton. */
final class MemoryStorage implements StorageInterface
{
    private array $values = [];
    public function contains(string $key): bool { return array_key_exists($key, $this->values); }
    public function read(string $key): ?string
    {
        $entry = $this->values[$key] ?? null;
        return $entry !== null && ($entry['expires'] === 0 || $entry['expires'] > time()) ? $entry['bytes'] : null;
    }
    public function write(string $key, string $bytes, int $ttl = 0): void { $this->values[$key] = ['bytes'=>$bytes,'expires'=>$ttl > 0 ? time()+$ttl : 0]; }
    public function remove(string $key): bool { $this->values[$key] = null; return true; }
}
