<?php
declare(strict_types=1);
namespace app\cms\cache;
use app\cms\security\RuntimeFiles;
final class FileStorage implements StorageInterface
{
    private function path(string $key): string { return \app\cms\Paths::runtime('cms/cache-store/' . hash('sha256', $key) . '.cache'); }
    public function read(string $key): ?string
    {
        $raw = RuntimeFiles::read($this->path($key));
        if ($raw === null || !str_contains($raw, "\n")) return null;
        [$expires, $bytes] = explode("\n", $raw, 2);
        if (!ctype_digit($expires) || ((int)$expires > 0 && (int)$expires <= time())) { $this->remove($key); return null; }
        return $bytes;
    }
    public function write(string $key, string $bytes, int $ttl = 0): void
    {
        RuntimeFiles::write($this->path($key), (time() + ($ttl > 0 ? $ttl : 86400)) . "\n" . $bytes);
        if (random_int(1, 100) === 1) $this->collect();
    }
    public function remove(string $key): bool { return RuntimeFiles::remove($this->path($key)); }
    /** Reclaim abandoned generations without deleting another request's fresh cache. */
    public function collect(): void
    {
        $directory = \app\cms\Paths::runtime('cms/cache-store');
        if (!is_dir($directory) || is_link($directory)) return;
        foreach (new \DirectoryIterator($directory) as $file) {
            if ($file->isLink() || !$file->isFile() || !preg_match('/^[a-f0-9]{64}\.cache$/D', $file->getFilename())) continue;
            if ($file->getMTime() >= time() - 86400) continue;
            $raw = RuntimeFiles::read($file->getPathname());
            if ($raw === null) continue;
            $expires = strstr($raw, "\n", true);
            if ($expires !== false && ctype_digit($expires) && ((int)$expires === 0 || (int)$expires <= time())) RuntimeFiles::remove($file->getPathname());
        }
    }
}
