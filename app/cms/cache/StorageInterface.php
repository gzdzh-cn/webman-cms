<?php
declare(strict_types=1);
namespace app\cms\cache;
interface StorageInterface
{
    public function read(string $key): ?string;
    public function write(string $key, string $bytes, int $ttl = 0): void;
    public function remove(string $key): bool;
}
