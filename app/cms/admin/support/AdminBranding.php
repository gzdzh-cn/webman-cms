<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use app\cms\CmsContext;
use app\cms\service\LanguagePolicy;
use support\think\Db;

/** Website settings own the backend name; appearance settings only own its image. */
final class AdminBranding
{
    public static function name(): string
    {
        $context = CmsContext::current();
        if (array_key_exists('web_name', $context->global)) {
            $name = $context->global['web_name'];
        } else {
            // Login has no editor context yet, so use the system's main language.
            $name = Db::name('config')->where([
                'name' => 'web_name', 'lang' => (new LanguagePolicy())->main(), 'is_del' => 0,
            ])->value('value');
        }
        return is_string($name) && trim($name) !== '' ? trim($name) : '网站后台';
    }

    public static function configuration(array $config): array
    {
        // Pear renders this field as HTML; encode once at that output boundary.
        $config['logo']['title'] = htmlspecialchars(self::name(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return $config;
    }
}
