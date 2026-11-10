<?php
declare(strict_types=1);
namespace app\cms\security;

use app\cms\Paths;
use RuntimeException;

/** Bounded regular-file IO, scoped to this deployment's disposable runtime. */
final class RuntimeFiles
{
    public static function path(string $path): void
    {
        $root = rtrim(runtime_path(), '/');
        if (!str_starts_with($path, $root . '/') || str_contains($path, "\0") || str_contains($path, '\\')
            || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path)) throw new RuntimeException('缓存路径超出本项目runtime');
        Paths::ensureDirectory(dirname($path));
        clearstatcache(true, $path);
        if (is_link($path) || (file_exists($path) && !is_file($path))) throw new RuntimeException('缓存路径不能是链接或特殊文件');
    }

    public static function read(string $path): ?string
    {
        self::path($path);
        if (!is_file($path)) return null;
        $handle = @fopen($path, 'rb');
        if ($handle === false) return null;
        try {
            $stat = fstat($handle);
            if (!$stat || ($stat['mode'] & 0170000) !== 0100000 || $stat['nlink'] !== 1
                || $stat['size'] > SignedEnvelope::MAX_BYTES + 4096) return null;
            $raw = stream_get_contents($handle, SignedEnvelope::MAX_BYTES + 4097);
            return is_string($raw) && strlen($raw) <= SignedEnvelope::MAX_BYTES + 4096 ? $raw : null;
        } finally { fclose($handle); }
    }

    public static function write(string $path, string $bytes): void
    {
        self::path($path);
        $temporary = dirname($path) . '/.secure-' . bin2hex(random_bytes(24));
        $handle = @fopen($temporary, 'xb');
        if (!$handle) throw new RuntimeException('无法创建私有缓存暂存文件');
        try {
            chmod($temporary, 0600);
            $offset = 0;
            while ($offset < strlen($bytes)) {
                $written = fwrite($handle, substr($bytes, $offset));
                if ($written === false || $written === 0) throw new RuntimeException('缓存写入失败');
                $offset += $written;
            }
            if (!fflush($handle)) throw new RuntimeException('缓存写入失败');
            fclose($handle); $handle = null;
            self::path($path);
            if (!rename($temporary, $path)) throw new RuntimeException('缓存发布失败');
        } finally {
            if (is_resource($handle)) fclose($handle);
            if (is_file($temporary) && !is_link($temporary)) @unlink($temporary);
        }
    }

    public static function remove(string $path): bool
    {
        self::path($path);
        return !is_file($path) || @unlink($path);
    }
}
