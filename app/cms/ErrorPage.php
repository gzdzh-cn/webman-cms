<?php
declare(strict_types=1);
namespace app\cms;

use support\Response;

/** Public errors never depend on a working theme, database or template compiler. */
final class ErrorPage
{
    public static function make(int $status = 500, bool $templateMissing = false, array $headers = []): Response
    {
        if ($status < 400 || $status > 599) $status = 500;
        $title = $status === 404 ? '页面不存在' : ($status === 403 ? '暂时无法访问' : '页面暂时无法显示');
        $message = $status === 404 ? '您访问的页面不存在，或已被移走。' : ($templateMissing
            ? '当前页面的模板尚未配置，请联系网站管理员。'
            : '当前页面暂时无法访问，请稍后重试或联系网站管理员。');
        $base = '/' . trim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        $home = rtrim($base, '/') . '/';
        $values = ['{{status}}' => (string)$status, '{{title}}' => $title, '{{message}}' => $message, '{{home}}' => $home];
        $file = base_path('app/view/cms/error.html');
        $html = is_file($file) && is_readable($file) ? file_get_contents($file) : false;
        if ($html === false) $html = '<!doctype html><html lang="zh-CN"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{title}}</title><h1>{{title}}</h1><p>{{message}}</p><a href="{{home}}">返回首页</a></html>';
        foreach ($values as &$value) $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        unset($value);
        return new Response($status, array_replace($headers, [
            'Content-Type' => 'text/html; charset=utf-8', 'Cache-Control' => 'no-store',
            'X-Robots-Tag' => 'noindex, nofollow', 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; frame-ancestors 'self'",
        ]), strtr($html, $values));
    }
}
