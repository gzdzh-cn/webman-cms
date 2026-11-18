<?php
declare(strict_types=1);

namespace app\cms;

/** Keep the generated-file directory separate from its public URL prefix. */
final class StaticUrl
{
    public static function directory(array $settings): string
    {
        $value = $settings['seo_html_arcdir'] ?? '';
        if (!is_scalar($value)) throw new \InvalidArgumentException('页面保存目录格式错误');
        $directory = trim(trim((string)$value), '/');
        if ($directory === '') return '/html';
        if (!preg_match('#^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$#D', $directory)) {
            throw new \InvalidArgumentException('页面保存目录格式错误');
        }
        return '/' . $directory;
    }

    public static function visibleDirectory(array $settings): string
    {
        return in_array($settings['seo_html_showdir'] ?? '1', ['0', 0, false], true)
            ? '' : self::directory($settings);
    }
}
