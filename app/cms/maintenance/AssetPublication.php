<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Complete preflight, same-directory staging, atomic no-overwrite publication. */
final class AssetPublication
{
    public static function target(string $root, string $relative): string
    {
        AssetArchive::relative($relative);
        return AssetArchive::path($root . '/' . $relative);
    }
    public static function matches(string $path, array $record): bool
    {
        clearstatcache(true, $path); $stat = @lstat($path);
        return $stat && ($stat['mode'] & 0170000) === 0100000 && $stat['nlink'] === 1
            && $stat['size'] === ($record['size'] ?? $record['bytes']) && hash_equals($record['sha256'], (string)hash_file('sha256', $path));
    }
    public static function preflight(string $root, array $records): array
    {
        AssetArchive::path($root); $pending = [];
        foreach ($records as $name => $record) {
            $path = self::target($root, $name); clearstatcache(true, $path);
            if (file_exists($path)) { if (!self::matches($path, $record)) throw new \RuntimeException('已有资源内容或文件身份不同，拒绝覆盖：' . $name); }
            else $pending[] = $name;
        }
        return $pending;
    }
    public static function publish(string $root, array $records, array $staged): int
    {
        $pending = self::preflight($root, $records); $created = [];
        try {
            foreach ($pending as $name) {
                $record = $records[$name]; $source = $staged[$name] ?? throw new \RuntimeException('资源暂存不完整');
                if (!self::matches($source, $record)) throw new \RuntimeException('资源暂存校验失败');
                $path = self::target($root, $name); $parent = dirname($path);
                if (!is_dir($parent) && !mkdir($parent, 0755, true) && !is_dir($parent)) throw new \RuntimeException('无法创建资源目录');
                self::target($root, $name);
                $temporary = $parent . '/.asset-' . bin2hex(random_bytes(12));
                $out = @fopen($temporary, 'xb'); if (!$out) throw new \RuntimeException('无法创建资源暂存文件');
                try {
                    chmod($temporary, $record['mode'] ?? 0644);
                    $in = fopen($source, 'rb'); if (!$in) throw new \RuntimeException('无法读取资源暂存');
                    try { $copied = stream_copy_to_stream($in, $out); } finally { fclose($in); }
                    if ($copied !== ($record['size'] ?? $record['bytes']) || !fflush($out) || !fsync($out)) throw new \RuntimeException('资源写入不完整');
                    fclose($out); $out = null;
                    self::target($root, $name); clearstatcache(true, $path);
                    if (file_exists($path)) { if (!self::matches($path, $record)) throw new \RuntimeException('并发写入了不同资源，拒绝覆盖'); continue; }
                    if (!@link($temporary, $path)) throw new \RuntimeException('资源原子发布失败');
                    $identity = lstat($path); $created[] = ['path'=>$path,'dev'=>$identity['dev'],'ino'=>$identity['ino'],'sha256'=>$record['sha256']];
                } finally { if (is_resource($out)) fclose($out); if (is_file($temporary)) unlink($temporary); }
            }
            return count($created);
        } catch (\Throwable $error) {
            foreach (array_reverse($created) as $entry) {
                clearstatcache(true, $entry['path']); $stat = @lstat($entry['path']);
                if ($stat && $stat['dev'] === $entry['dev'] && $stat['ino'] === $entry['ino'] && ($stat['mode'] & 0170000) === 0100000 && hash_equals($entry['sha256'], (string)hash_file('sha256', $entry['path']))) unlink($entry['path']);
            }
            throw $error;
        }
    }
    public static function temporary(): string
    {
        $path = rtrim((string)realpath(sys_get_temp_dir()), '/') . '/cms-assets-' . bin2hex(random_bytes(16));
        if (!mkdir($path, 0700)) throw new \RuntimeException('无法创建私有资源暂存目录');
        return $path;
    }
    public static function stage(string $root, string $name, string $bytes): string
    {
        $path = self::target($root, $name); $parent = dirname($path);
        if (!is_dir($parent) && !mkdir($parent, 0700, true) && !is_dir($parent)) throw new \RuntimeException('无法创建私有暂存目录');
        $stream = @fopen($path, 'xb'); if (!$stream) throw new \RuntimeException('资源暂存文件已存在');
        try { chmod($path, 0600); if (fwrite($stream, $bytes) !== strlen($bytes)) throw new \RuntimeException('资源暂存写入不完整'); } finally { fclose($stream); }
        return $path;
    }
    public static function remove(string $root): void
    {
        if (is_link($root)) throw new \RuntimeException('私有暂存目录被替换');
        foreach (new \FilesystemIterator($root, \FilesystemIterator::SKIP_DOTS) as $file) {
            if ($file->isDir() && !$file->isLink()) self::remove($file->getPathname()); else unlink($file->getPathname());
        }
        rmdir($root);
    }
}
