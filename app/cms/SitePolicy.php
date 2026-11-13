<?php
declare(strict_types=1);
namespace app\cms;

use support\Response;

/** Public request policies run before either a template or generated HTML is read. */
final class SitePolicy
{
    public static function response(CmsContext $context, ?string $publicRoot = null): ?Response
    {
        $request = $context->request;
        if (!$request || strtolower((string)$request->input('m', $context->module)) !== 'home'
            || ($request->plugin ?? '') === 'admin' || preg_match('#^/web-admin(?:/|$)#', $request->path())) return null;
        $settings = $context->global;
        $base = rtrim((string)($settings['web_cmspath'] ?? ''), '/');
        $path = $request->path();
        if ($base !== '' && str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
        if (preg_match('#^/(?:index\.php/)?api(?:/|$)#i', $path)) return null;
        $generating = !empty($context->memo['cms.static.generating']);
        if (!$generating && !empty($settings['web_is_https']) && !str_starts_with($context->origin(), 'https://')) {
            return self::redirect('https://' . $request->host() . $request->uri(), in_array($request->method(), ['GET', 'HEAD'], true) ? 301 : 307);
        }
        if ((int)($settings['web_status'] ?? 0) === 1) {
            $mode = (int)($settings['web_status_mode'] ?? 0);
            if ($mode === 1) {
                $url = trim((string)($settings['web_status_url'] ?? ''));
                $local = preg_match('#^/(?!/)[^\x00-\x20\x7f\\\\]*$#D', $url);
                $absolute = preg_match('#^https?://[^\s\\\\]+$#iD', $url) && filter_var($url, FILTER_VALIDATE_URL);
                if ($local || $absolute) return self::redirect($url, 302);
                return self::closed($context, '网站暂时关闭，没有找到跳转的URL');
            }
            if ($mode === 2) {
                $html = self::closeTemplate((string)($settings['web_status_tpl'] ?? 'public/close.html'), (string)($settings['web_cmspath'] ?? ''), $publicRoot ?? \app\cms\Paths::publicRoot());
                if ($html !== null) return self::closed($context, $html, true);
            }
            return self::closed($context, (string)($settings['web_status_text'] ?? '网站暂时关闭，维护中……'));
        }
        $force = DeviceSelector::forced($context->input('param.goto'));
        if (!$generating && !ThemeCatalog::isResponsive(ThemeCatalog::configured($settings['web_tpl_theme'] ?? null))
            && $force === null && empty($settings['other_pcwapjs'])
            && in_array($request->method(), ['GET', 'HEAD'], true) && !$request->isAjax()
            && preg_match('/mobile|android|iphone|ipad|ipod|windows phone/i', (string)$request->header('user-agent', ''))) {
            $mobile = self::mobileHost($context);
            if ($mobile !== null && strcasecmp($request->host(), $mobile) !== 0) {
                return self::redirect((str_starts_with($context->origin(), 'https://') ? 'https://' : 'http://') . $mobile . $request->uri(), 302);
            }
        }
        return null;
    }

    /** Independent mobile host names select mobile templates even for a desktop browser. */
    public static function isMobileHost(CmsContext $context): bool
    {
        $mobile = self::mobileHost($context);
        return $mobile !== null && strcasecmp((string)$context->request?->host(), $mobile) === 0;
    }

    private static function mobileHost(CmsContext $context): ?string
    {
        if (empty($context->global['web_mobile_domain_open']) || !$context->request) return null;
        $prefix = (string)($context->global['web_mobile_domain'] ?? '');
        $host = $context->request->host(true);
        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/iD', $prefix) || $host === 'localhost'
            || filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) || !str_contains($host, '.')) return null;
        // Domain routing is case-insensitive; the template compatibility
        // helper itself deliberately preserves upstream input spelling.
        $root = (new compat\Request())->rootDomain(strtolower($host));
        $port = parse_url($context->origin(), PHP_URL_PORT);
        return strtolower($prefix . '.' . $root) . ($port ? ':' . $port : '');
    }

    private static function redirect(string $url, int $status): Response
    {
        return new Response($status, ['Location' => $url, 'Cache-Control' => 'no-store', 'Vary' => 'User-Agent, Cookie'], '');
    }

    private static function closed(CmsContext $context, string $body, bool $raw = false): Response
    {
        if (!$raw) $body = "<div style='text-align:center; font-size:20px; font-weight:bold; margin:50px 0px;'>{$body}</div>";
        return new Response(200, ['Content-Type' => 'text/html; charset=utf-8', 'Cache-Control' => 'no-store', 'X-CMS-Site-Status' => 'closed'], $context->request?->method() === 'HEAD' ? '' : $body);
    }

    /** Read a public HTML page as bytes, never execute or expose private templates. */
    private static function closeTemplate(string $value, string $base, string $publicRoot): ?string
    {
        $value = trim($value);
        if ($value === '') $value = 'public/close.html';
        if (preg_match('#^https?://#i', $value)) $value = (string)parse_url($value, PHP_URL_PATH);
        if ($base !== '' && str_starts_with($value, rtrim($base, '/') . '/')) $value = substr($value, strlen(rtrim($base, '/')));
        $value = ltrim($value, '/');
        if (str_starts_with($value, 'public/')) $value = substr($value, 7);
        if (preg_match('#^template(?:/|$)#i', $value)) return null;
        if (!preg_match('/\.html?$/iD', $value) || preg_match('/[\x00-\x20\x7f\\\\?#]/', $value)) return null;
        $root = realpath($publicRoot);
        if (!$root || is_link($publicRoot)) return null;
        $path = $root;
        foreach (explode('/', $value) as $part) {
            if ($part === '' || $part === '.' || $part === '..' || str_starts_with($part, '.')) return null;
            $path .= '/' . $part;
            if (is_link($path)) return null;
        }
        $actual = realpath($path);
        if (!$actual || !is_file($actual) || !str_starts_with($actual, $root . DIRECTORY_SEPARATOR) || AssetResolver::isTemplateSource($actual)) return null;
        $html = file_get_contents($actual);
        return is_string($html) && $html !== '' ? $html : null;
    }
}
