<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use app\cms\Paths;
use InvalidArgumentException;
use RuntimeException;
use support\Response;
use think\Template;

/** Backend rendering is independent of Webman's physical plugin discovery. */
final class AdminView
{
    public static function raw(string $template, array $vars = []): Response
    {
        $file = self::file($template);
        $vars = self::variables($vars);
        ob_start();
        try {
            (static function (string $__cms_view_file, array $__cms_view_vars): void {
                extract($__cms_view_vars, EXTR_SKIP);
                include $__cms_view_file;
            })($file, $vars);
            return new Response(200, [], self::languageBootstrap((string)ob_get_clean()));
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }

    public static function render(string $template, array $vars = []): Response
    {
        self::file($template);
        $cache = Paths::runtime('cms/admin/views');
        if (!\app\cms\cache\CachePolicy::isRedis()) Paths::ensureDirectory($cache);
        $options = array_replace((array)config('cms_admin.view.options', []), [
            'view_path' => Paths::cms('admin/view/'),
            'cache_path' => $cache . '/',
            'view_suffix' => 'html',
            // Vendor checkCache() otherwise deserializes an untrusted disk header
            // before our storage boundary. Always compile trusted application sources.
            'tpl_cache' => false,
            'display_cache' => false,
            'compile_type' => \app\cms\security\AdminTemplateStorage::class,
        ]);
        $views = new Template($options);
        ob_start();
        try {
            $views->fetch($template, self::variables($vars));
            return new Response(200, [], self::languageBootstrap((string)ob_get_clean()));
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }

    private static function languageBootstrap(string $html): string
    {
        if (!str_contains(strtolower($html), '<head')) return $html;
        // Login also uses the shared feedback API, before any authenticated
        // language state exists. Load its assets independently of that state.
        $uiCss=htmlspecialchars(\app\cms\AssetManifest::url('/admin-assets/css/cms-ui.css'),ENT_QUOTES,'UTF-8');
        $uiJs=htmlspecialchars(\app\cms\AssetManifest::url('/admin-assets/js/cms-ui.js'),ENT_QUOTES,'UTF-8');
        $ui='<link rel="stylesheet" href="'.$uiCss.'"><script defer src="'.$uiJs.'"></script>';
        $html = preg_replace('/<head\b[^>]*>/i', '$0'.$ui, $html, 1);
        $request = request();
        if (!$request || !function_exists('admin') || !admin()) return $html;
        $state = LanguageAdminContext::state($request);
        $state['edit'] = $state['lang'];
        $state['csrf'] = LanguageAdminSecurity::token($request);
        $json = json_encode($state, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR);
        $url = htmlspecialchars(\app\cms\AssetManifest::url('/admin-assets/js/cms-language-context.js'), ENT_QUOTES, 'UTF-8');
        // Establish embedded-page presentation before parsing any body content.
        // Applying it on the iframe load event exposed retired headings for a frame.
        $layoutCss = htmlspecialchars(\app\cms\AssetManifest::url('/admin-assets/css/cms-page-layout.css'), ENT_QUOTES, 'UTF-8');
        $presentation = '<script>try{if(parent!==window&&parent.CmsNavigation&&parent.CmsNavigation.resolve(location.href)&&/\/(index|dashboard|featuremap)\/?$/.test(location.pathname)){document.documentElement.classList.add("cms-workspace-document");}}catch(_){}</script>'
            . '<style>html.cms-workspace-document body>h1,html.cms-workspace-document body>main>h1,html.cms-workspace-document body>main>header>h1,html.cms-workspace-document .cms-module-navigation>.cms-module-heading,html.cms-workspace-document #management>.page-head h1{display:none!important}</style>'
            . '<link rel="stylesheet" data-cms-page-layout="1" href="'.$layoutCss.'">';
        return preg_replace('/<head\b[^>]*>/i', '$0'.$presentation.'<script>window.CmsLanguageInitial='.$json.';</script><script src="'.$url.'"></script>', $html, 1);
    }

    private static function variables(array $vars): array
    {
        $request = request();
        return array_replace((array)($request->_view_vars ?? []), $vars);
    }

    private static function file(string $template): string
    {
        if (!preg_match('#^(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+$#D', $template)) {
            throw new InvalidArgumentException('后台模板名称无效');
        }
        $root = Paths::cms('admin/view');
        $file = $root;
        foreach (explode('/', $template . '.html') as $segment) {
            $file .= '/' . $segment;
            if (is_link($file)) throw new RuntimeException('后台模板不能使用符号链接');
        }
        if ($packed = \app\cms\support\PackagedFiles::template($file, $root)) return $packed;
        $actual = realpath($file);
        if (!$actual || !is_file($actual) || !str_starts_with($actual, (string)realpath($root) . '/')) {
            throw new RuntimeException('后台模板不存在：' . $template);
        }
        return $actual;
    }
}
