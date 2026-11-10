<?php
declare(strict_types=1);
namespace app\cms\config;

/** Also usable before Webman configuration is loaded. */
final class SiteSettings
{
    public static function get(string $key): string
    {
        $settings = require dirname(__DIR__, 3) . '/config/cms_site.php';
        if (!array_key_exists($key, $settings)) throw new \InvalidArgumentException('Unknown site configuration key');
        return $settings[$key];
    }
}
