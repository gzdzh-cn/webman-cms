<?php
namespace app\cms;

/** Keep stored upload URLs independent of their public filesystem location. */
final class UploadPaths
{
    /** Create an upload bucket without following links inside the public tree. */
    public static function ensureDirectory(string $relative): string
    {
        if (!preg_match('#^uploads(?:/[A-Za-z0-9_-]+)*$#D', $relative)) {
            throw new \InvalidArgumentException('上传目录无效');
        }
        $public = realpath(public_path());
        if ($public === false || !is_dir($public)) throw new \RuntimeException('公共目录不可用');
        $path = $public;
        foreach (explode('/', $relative) as $part) {
            $path .= DIRECTORY_SEPARATOR . $part;
            clearstatcache(true, $path);
            if (is_link($path)) throw new \InvalidArgumentException('上传目录不能包含符号链接');
            if (!is_dir($path) && !@mkdir($path, 0755) && !is_dir($path)) throw new \RuntimeException('无法创建上传目录');
            clearstatcache(true, $path);
            if (is_link($path) || realpath($path) !== $path) throw new \InvalidArgumentException('上传目录无效');
        }
        return $path;
    }

    /** Non-upload paths and stream URLs retain their native filesystem meaning. */
    public static function physical($path)
    {
        $mapping = self::mapping($path);
        return $mapping === null ? $path : $mapping['physical'];
    }

    /** Preserve glob's caller-visible relative/absolute path shape. */
    public static function glob(string $pattern, int $flags = 0): array|false
    {
        $mapping = self::mapping($pattern);
        if ($mapping === null) return \glob($pattern, $flags);
        $matches = \glob($mapping['physical'], $flags);
        if ($matches === false) return false;
        $result = [];
        foreach ($matches as $match) {
            // A wildcard can traverse a symlink not present in the pattern's
            // existing ancestor. Check each result against its upload bucket.
            try { self::mapping($match); }
            catch (\InvalidArgumentException $error) { continue; }
            // GLOB_NOCHECK returns the input pattern unchanged when absent.
            $result[] = $mapping['virtual_root'] . substr($match, strlen($mapping['physical_root']));
        }
        return $result;
    }

    private static function mapping($path): ?array
    {
        if (!is_string($path) || $path === '' || str_contains($path, '://')) return null;
        $project = rtrim(base_path(), '/');
        $prefixes = [$project . '/public/', $project . '/', './public/', 'public/', '/public/', './', '', '/'];
        $base = trim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        if ($base !== '') $prefixes[] = '/' . $base . '/';
        foreach ($prefixes as $prefix) {
            foreach (['uploads', 'upload'] as $folder) {
                $virtual = $prefix . $folder;
                if ($path !== $virtual && !str_starts_with($path, $virtual . '/')) continue;
                $relative = substr($path, strlen($virtual));
                if (str_contains($relative, "\0") || str_contains($relative, '\\') || in_array('..', explode('/', $relative), true)) {
                    throw new \InvalidArgumentException('上传资源路径无效');
                }
                // Both historical URL spellings share one physical store.
                $physicalRoot = rtrim(public_path('uploads'), '/');
                $physical = $physicalRoot . $relative;
                // Check the nearest existing ancestor for both reads and writes.
                // A symlink inside an upload bucket must not escape that bucket.
                $root = \realpath($physicalRoot);
                if ($root !== false) {
                    $ancestor = $physical;
                    while (!\file_exists($ancestor) && !\is_link($ancestor) && dirname($ancestor) !== $ancestor) $ancestor = dirname($ancestor);
                    $actual = \realpath($ancestor);
                    if ($actual === false || ($actual !== $root && !str_starts_with($actual, $root . DIRECTORY_SEPARATOR))) {
                        throw new \InvalidArgumentException('上传资源路径超出目录范围');
                    }
                }
                return ['physical' => $physical, 'physical_root' => $physicalRoot, 'virtual_root' => $virtual];
            }
        }
        return null;
    }
}
