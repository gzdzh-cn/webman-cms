<?php
declare(strict_types=1);
namespace app\cms\admin\support;

/** Backend identity is independent of the public website name. */
final class AdminBranding
{
    public static function name(): string
    {
        return '后台管理';
    }

    public static function configuration(array $config): array
    {
        // Pear renders this field as HTML; encode once at that output boundary.
        $config['logo']['title'] = htmlspecialchars(self::name(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return $config;
    }
}
