<?php
declare(strict_types=1);
namespace app\cms;

/** Named themes with filesystem aliases for unchanged EyouCMS templates. */
final class ThemeCatalog
{
    public static function canonical(string $name): string
    {
        $name = trim($name, '/');
        if ($name === '' || $name === 'official') return 'default';
        if (!preg_match('#^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$#D', $name)) throw new \InvalidArgumentException('模板目录格式无效');
        if (in_array(strtolower(explode('/', $name)[0]), ['pc', 'mobile', 'plugins', '_shared'], true)) throw new \InvalidArgumentException('该目录不是网站主题');
        return $name;
    }

    public static function configured(?string $record): string
    {
        return self::canonical($record ?? (string)\app\cms\config\SiteSettings::get('theme'));
    }

    public static function root(string $theme, ?string $device = null): string
    {
        $theme = self::canonical($theme);
        if ($device !== null && !in_array($device, ['pc', 'mobile'], true)) throw new \InvalidArgumentException('模板终端无效');
        if ($device !== null && self::isResponsive($theme)) $device = 'pc';
        $base = \app\cms\support\EmbeddedResources::realpath(\app\cms\Paths::templates(''));
        if (!$base || is_link(\app\cms\Paths::templates(''))) throw new \InvalidArgumentException('模板根目录无效');
        $path = $base;
        foreach (explode('/', $theme . ($device === null ? '' : '/' . $device)) as $part) {
            $path .= '/' . $part;
            if (is_link($path)) throw new \InvalidArgumentException('模板目录不能是符号链接');
        }
        $actual = \app\cms\support\EmbeddedResources::realpath($path);
        if (!$actual || !is_dir($actual) || !str_starts_with($actual, $base . DIRECTORY_SEPARATOR)) throw new \InvalidArgumentException('模板目录不存在');
        return $actual;
    }

    /** An explicit boolean opt-in; existing split-device themes retain their paths. */
    public static function metadata(string $theme): array
    {
        $theme = self::canonical($theme);
        try { $file = self::root($theme) . '/theme.json'; }
        catch (\InvalidArgumentException $error) { return []; }
        if (!is_file($file) || is_link($file) || filesize($file) >= 65536) return [];
        $value = json_decode((string)file_get_contents($file), true);
        return is_array($value) ? $value : [];
    }

    public static function isResponsive(string $theme): bool
    {
        return (self::metadata($theme)['responsive'] ?? false) === true;
    }

    public static function all(): array
    {
        $items = [];
        foreach (\app\cms\support\EmbeddedResources::glob(\app\cms\Paths::templates('*'), GLOB_ONLYDIR) ?: [] as $path) {
            $key = basename($path);
            if (in_array(strtolower($key), ['pc', 'mobile', 'plugins', 'official', '_shared'], true) || is_link($path)) continue;
            try { $root = self::root($key); } catch (\InvalidArgumentException $error) { continue; }
            $meta = [];
            $manifest = $root . '/theme.json';
            if (is_file($manifest) && !is_link($manifest) && filesize($manifest) < 65536) { $decoded = json_decode((string)file_get_contents($manifest), true); if (is_array($decoded)) $meta = $decoded; }
            $devices = [];
            foreach (['pc', 'mobile'] as $device) {
                try { $deviceRoot = self::root($key, $device); $index = \app\cms\support\EmbeddedResources::realpath($deviceRoot . '/index.htm'); $devices[$device] = $index && str_starts_with($index, $deviceRoot . DIRECTORY_SEPARATOR) && is_file($index); }
                catch (\InvalidArgumentException $error) { $devices[$device] = false; }
            }
            if (!$devices['pc'] && !$devices['mobile']) continue;
            $name = $key === 'default' ? 'EyouCMS 默认模板' : (is_string($meta['name'] ?? null) ? $meta['name'] : $key);
            $description = is_string($meta['description'] ?? null) ? $meta['description'] : '';
            $inspection = \app\cms\secure\ThemeInspection::inspect($root, 'template/' . $key);
            $items[] = ['key' => $key, 'name' => $name, 'description' => $description, 'path' => '/template/' . $key, 'supported' => !$inspection['blocking_issues'], 'issues' => $inspection['issues'], 'blocking_issues' => $inspection['blocking_issues'],
                'thumbnail' => self::thumbnail($root, $key, $meta), 'devices' => $devices];
        }
        usort($items, static fn(array $a, array $b): int => (($b['key'] === 'default') <=> ($a['key'] === 'default')) ?: strcmp($a['key'], $b['key']));
        return $items;
    }

    /** Discover raster previews without exposing a source file or another theme. */
    private static function thumbnail(string $root, string $key, array $meta): ?string
    {
        $candidates = is_string($meta['thumbnail'] ?? null) ? [$meta['thumbnail']] : [];
        $candidates = array_merge($candidates, ['thumbnail.png', 'thumbnail.jpg', 'thumbnail.webp',
            'preview.png', 'preview.jpg', 'litpic.png', 'litpic.jpg', 'theme.jpg', 'theme.png']);
        $types = ['png' => IMAGETYPE_PNG, 'jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG,
            'webp' => IMAGETYPE_WEBP, 'gif' => IMAGETYPE_GIF, 'avif' => IMAGETYPE_AVIF,
            'bmp' => IMAGETYPE_BMP, 'ico' => IMAGETYPE_ICO];
        foreach (array_unique($candidates) as $relative) {
            // A lexical check precedes every filesystem operation. No URL,
            // absolute path, hidden segment, traversal or encoded separator.
            if (strlen($relative) > 512 || !preg_match('#^[A-Za-z0-9_-][A-Za-z0-9_.-]*(?:/[A-Za-z0-9_-][A-Za-z0-9_.-]*)*$#D', $relative)
                || str_contains($relative, '..')) continue;
            $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
            if (!isset($types[$extension])) continue;
            clearstatcache(true);
            $path = $root;
            foreach (explode('/', $relative) as $part) {
                $path .= '/' . $part;
                if (is_link($path)) continue 2;
            }
            $actual = \app\cms\support\EmbeddedResources::realpath($path);
            if (!$actual || !str_starts_with($actual, $root . DIRECTORY_SEPARATOR) || !is_file($actual) || !is_readable($actual)) continue;
            $bytes = filesize($actual);
            if ($bytes === false || $bytes > 16 * 1024 * 1024) continue;
            $image = @getimagesize($actual);
            if ($image === false || ($image[2] ?? null) !== $types[$extension]) continue;
            $hash = hash_file('sha256', $actual);
            if ($hash === false) continue;
            return '/template/' . $key . '/' . $relative . '?v=' . substr($hash, 0, 16);
        }
        return null;
    }

    public static function physical($path)
    {
        return self::mapping($path)['physical'] ?? $path;
    }

    public static function isTemplatePath($path): bool
    {
        return self::mapping($path) !== null;
    }

    public static function glob(string $pattern, int $flags = 0): array|false
    {
        $map = self::mapping($pattern);
        if ($map === null) return \glob($pattern, $flags);
        $matches = \app\cms\support\EmbeddedResources::glob($map['physical'], $flags);
        if ($matches === false) return false;
        $result = [];
        foreach ($matches as $match) {
            try { self::mapping($match); } catch (\InvalidArgumentException $error) { continue; }
            $result[] = $map['virtual_root'] . substr($match, strlen($map['physical_root']));
        }
        return $result;
    }

    private static function mapping($path): ?array
    {
        if (!is_string($path) || $path === '' || (str_contains($path, '://') && !str_starts_with($path, Paths::templates().'/'))) return null;
        foreach (array_unique([Paths::templates(),Paths::root('template'),base_path('template'),'./template','template','/template']) as $virtual) {
            if ($path !== $virtual && !str_starts_with($path, $virtual . '/')) continue;
            $relative = substr($path, strlen($virtual));
            if (str_contains($relative, "\0") || str_contains($relative, '\\') || in_array('..', explode('/', $relative), true)) throw new \InvalidArgumentException('模板资源路径无效');
            $physicalRoot = \app\cms\Paths::templates('');
            foreach (['official' => 'default', 'pc' => 'default/pc', 'mobile' => 'default/mobile'] as $old => $new) {
                if ($relative === '/' . $old || str_starts_with($relative, '/' . $old . '/')) {
                    $virtual .= '/' . $old;
                    $physicalRoot .= '/' . $new;
                    $relative = substr($relative, strlen($old) + 1);
                    break;
                }
            }
            $physical = $physicalRoot . $relative;
            $root = \app\cms\support\EmbeddedResources::realpath(\app\cms\Paths::templates(''));
            if ($root) {
                $ancestor = $physical;
                while (!file_exists($ancestor) && !is_link($ancestor) && dirname($ancestor) !== $ancestor) $ancestor = dirname($ancestor);
                $actual = \app\cms\support\EmbeddedResources::realpath($ancestor);
                if (!$actual || ($actual !== $root && !str_starts_with($actual, $root . DIRECTORY_SEPARATOR))) throw new \InvalidArgumentException('模板资源路径超出目录范围');
            }
            return ['physical' => $physical, 'physical_root' => $physicalRoot, 'virtual_root' => $virtual];
        }
        return null;
    }
}
