<?php
declare(strict_types=1);
namespace app\cms\config;

/** Bootstrap paths must not depend on either Webman config or the working directory. */
final class Entry
{
    public static function directory(): string
    {
        return \Phar::running(false) ? dirname(\Phar::running(false)) : dirname(__DIR__, 3);
    }
    public static function absolute(string $path, ?string $base = null): string
    {
        if ($path === '' || str_contains($path, "\0") || str_contains($path, '\\') || str_contains($path, '://')) throw new \RuntimeException('配置路径无效');
        if ($path[0] !== '/') $path = ($base ?? self::directory()).'/'.$path;
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') continue;
            if ($part === '..') throw new \RuntimeException('配置路径不能包含上级目录');
            $parts[] = $part;
        }
        return '/'.implode('/', $parts);
    }
    public static function assertPath(string $path): void
    {
        $current = '';
        foreach (explode('/', ltrim($path, '/')) as $part) {
            $current .= '/'.$part;
            clearstatcache(true, $current);
            if (is_link($current)) throw new \RuntimeException('配置或数据路径不能包含符号链接');
        }
    }
}
