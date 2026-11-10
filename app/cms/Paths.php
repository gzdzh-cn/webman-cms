<?php
declare(strict_types=1);
namespace app\cms;

/** Application-owned paths. Public URLs must never be inferred from private paths. */
final class Paths
{
    public const PUBLIC_ASSET_URL = '/admin-assets';
    public const COMMON_ASSET_URL = self::PUBLIC_ASSET_URL . '/common';
    public const PUBLIC_ASSETS = 'public' . self::PUBLIC_ASSET_URL;
    public const COMMON_ASSETS = 'public' . self::COMMON_ASSET_URL;

    public static function assets(string $path = ''): string { return self::root(self::PUBLIC_ASSETS . ($path === '' ? '' : '/' . $path)); }
    public static function commonAssets(string $path = ''): string { return self::root(self::COMMON_ASSETS . ($path === '' ? '' : '/' . $path)); }
    public static function root(string $path = ''): string { return self::join(dirname(__DIR__, 2), $path); }
    public static function cms(string $path = ''): string { return self::join(__DIR__, $path); }
    public static function resources(string $path = ''): string { return self::cms('resources' . ($path === '' ? '' : '/' . $path)); }
    public static function resource(string $path = ''): string { return self::resources($path); }
    public static function legacyData(string $path = ''): string { return self::resources('legacy/data' . ($path === '' ? '' : '/' . $path)); }
    public static function runtime(string $path = ''): string { return self::join(runtime_path(), $path); }
    public static function install(string $path = ''): string { return self::root('install' . ($path === '' ? '' : '/' . $path)); }
    /** Maintenance-only original SQL lookup; never used to reopen the Web installer. */
    public static function installationBaseline(string $filename): string { return (new \app\cms\install\InstallerArchive(self::root()))->baselinePath($filename); }
    public static function publicHtml(string $path = ''): string { return self::join(public_path('html'), $path); }

    public static function ensureDirectory(string $path, int $mode = 0750): void
    {
        if (!str_starts_with($path, '/') || str_contains($path, "\0")) throw new \RuntimeException('Directory must be absolute');
        $current = '';
        foreach (explode('/', trim($path, '/')) as $part) {
            if ($part === '' || $part === '.' || $part === '..') throw new \RuntimeException('Invalid directory');
            $current .= '/' . $part;
            if (is_link($current) || (file_exists($current) && !is_dir($current))) throw new \RuntimeException('Directory contains a link or file');
        }
        // Several workers may create the same cold runtime path together.
        // EEXIST is successful only after checking the resulting real directory.
        if (!is_dir($path) && !@mkdir($path, $mode, true) && !is_dir($path)) throw new \RuntimeException('Unable to create runtime directory');
        clearstatcache(true, $path);
        if (is_link($path) || !is_dir($path)) throw new \RuntimeException('Invalid runtime directory');
    }

    private static function join(string $base, string $path): string
    {
        if (str_contains($path, "\0") || str_contains($path, '\\') || str_starts_with($path, '/') || preg_match('#(?:^|/)\.\.(?:/|$)#', $path)) throw new \InvalidArgumentException('Invalid application-relative path');
        return rtrim($base, '/') . ($path === '' ? '' : '/' . $path);
    }
}
