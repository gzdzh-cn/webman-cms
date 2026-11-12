<?php
declare(strict_types=1);
namespace app\cms;

/** Immutable, file-exact resource registry; no directory-wide public grants. */
final class AssetManifest
{
    private static ?array $data = null;
    private static array $urls = [];
    private static array $targets = [];
    private static array $reads = [];

    private static function load(): void
    {
        if (self::$data !== null) return;
        $path = Paths::resource('asset-manifest.json');
        $data = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (($data['format'] ?? null) !== 2) throw new \RuntimeException('Unsupported resource manifest');
        self::$data = $data;
        foreach ($data['files'] as $entry) {
            self::$targets[$entry['target']] = $entry;
            foreach ($entry['aliases'] as $url) self::$urls[$url] = $entry;
            foreach ($entry['read_aliases'] ?? $entry['aliases'] as $url) self::$reads[$url] = $entry;
            if ($entry['url']) self::$urls[$entry['url']] = $entry;
        }
    }

    private static function clean(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) return null;
        $path = rawurldecode($path);
        if (str_contains($path, "\0") || str_contains($path, '\\') || preg_match('#(^|/)\.[^/]*(/|$)#', $path)) return null;
        $base = rtrim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        if ($base !== '' && str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
        return '/' . ltrim($path, '/');
    }

    public static function entry(string $url): ?array
    {
        self::load();
        $path = self::clean($url);
        return $path === null ? null : (self::$urls[$path] ?? null);
    }

    private static function physical(array $entry): ?string
    {
        $relative = $entry['target'];
        if (str_starts_with($relative, '/') || str_contains($relative, '\\') || preg_match('#(^|/)\.[^/]*(/|$)#', $relative)) return null;
        $path = Paths::logical($relative);
        if (str_starts_with($path, 'phar://')) return \app\cms\support\EmbeddedResources::realpath($path) && is_file($path) ? $path : null;
        \app\cms\config\Entry::assertPath($path);
        $actual = \app\cms\support\EmbeddedResources::realpath($path);
        return $actual && is_file($actual) && $actual === $path ? $actual : null;
    }

    public static function resolve(string $url): ?string
    {
        $entry = self::entry($url);
        if (!$entry || !$entry['public']) return null;
        if ($entry['source'] === 'public/static/common/js/lang/foreign_global.js') return \app\cms\service\LanguageAssetService::generated();
        $file = self::physical($entry);
        if ($file && $entry['source'] === 'public/static/common/js/ey_global.js') {
            return \app\cms\service\LanguageAssetService::resolve($file);
        }
        return $file;
    }

    /** Resolve only registered legacy reads, never destination/write paths. */
    public static function readPath($path): ?string
    {
        if (!is_string($path) || str_contains($path, '://')) return null;
        $root = rtrim(base_path(), '/') . '/';
        if (str_starts_with($path, $root)) $path = substr($path, strlen($root));
        elseif (str_starts_with($path, './')) $path = substr($path, 2);
        self::load();
        $key = '/' . ltrim($path, '/');
        if (str_contains($key, '\\') || preg_match('#(^|/)\.[^/]*(/|$)#', $key) || str_contains($key, "\0")) return null;
        $entry = $key === null ? null : (self::$reads[$key] ?? self::$urls[$key] ?? null);
        if ($entry && $entry['source'] === 'public/static/common/js/lang/foreign_global.js') return \app\cms\service\LanguageAssetService::generated();
        return $entry ? self::physical($entry) : null;
    }

    /** Also accepts directory bases required by legacy dynamic loaders. */
    public static function url(string $oldUrl): string
    {
        self::load();
        $parts = parse_url($oldUrl);
        if (!is_array($parts) || isset($parts['host']) || isset($parts['scheme'])) return $oldUrl;
        $path = self::clean($oldUrl);
        if ($path === null) return $oldUrl;
        $entry = self::$urls[$path] ?? null;
        $url = $entry && $entry['public'] ? $entry['url'] : null;
        if (!$url && str_ends_with($path, '/')) {
            foreach (self::$data['directories'] as $old => $new) {
                if (str_starts_with($path, $old)) {
                    $known = false;
                    foreach (self::$urls as $registered => $item) if (str_starts_with($registered, $path)) { $known = true; break; }
                    if ($known) $url = $new . substr($path, strlen($old));
                    break;
                }
            }
        }
        if (!$url) return $oldUrl;
        $query = [];
        foreach (explode('&', $parts['query'] ?? '') as $part) {
            if ($part !== '' && rawurldecode(explode('=', $part, 2)[0]) !== 'v') $query[] = $part;
        }
        if ($entry) {
            $hash = $entry['sha256'];
            if (($entry['source'] === 'public/static/common/js/ey_global.js' || !empty($entry['mutable'])) && ($file = self::resolve($oldUrl))) {
                $hash = str_starts_with($file, Paths::runtime('cache/assets/'))
                    ? hash('sha256', \app\cms\service\LanguageAssetService::readDerived($file))
                    : hash_file('sha256', $file);
            }
            $query[] = 'v=' . substr($hash, 0, 16);
        }
        $base = rtrim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        return $base . $url . ($query ? '?' . implode('&', $query) : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    public static function isPublicTarget(string $file, bool $htmlOnly = false): bool
    {
        self::load();$relative=Paths::relative($file);$entry=$relative===null?null:(self::$targets[$relative]??null);
        return $entry && $entry['public'] && (!$htmlOnly || $entry['html_allowed']) && self::physical($entry) === \app\cms\support\EmbeddedResources::realpath($file);
    }
    public static function isManagedFile(string $file): bool
    {
        self::load();$relative=Paths::relative($file);return $relative!==null&&isset(self::$targets[$relative]);
    }
}
