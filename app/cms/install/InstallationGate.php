<?php
declare(strict_types=1);
namespace app\cms\install;

use Webman\Http\Request;
use Webman\Http\Response;

/** Run before backend authorization or frontend database queries. */
final class InstallationGate
{
    private const INSTALL_ROUTES = ['/install','/install/','/install/index.php','/install/status','/install/handoff/status','/install/handoff/retry','/install/handoff/login'];
    public function __construct(private ?InstallerService $service = null) {}

    public function response(Request $request): ?Response
    {
        $path = rawurldecode($request->path());
        // Installation material never becomes a new URL after a filesystem rename.
        $checkPath = $path;
        $base = rtrim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        if ($base !== '' && str_starts_with($checkPath, $base . '/')) $checkPath = substr($checkPath, strlen($base));
        if (\app\cms\support\EmbeddedResources::binary()) {
            if(preg_match('#^/(?:public/)?install(?:_|/|$)#i',$checkPath)) return response('Not Found',404);
            return null; // Installed database was checked before starting workers.
        }
        if (preg_match('#^/(?:public/)?install(?:_[^/]+)?(?:/|$)#i', $checkPath)
            && !in_array($path, self::INSTALL_ROUTES, true)) {
            return response('Not Found', 404)->header('Cache-Control', 'no-store');
        }
        if (in_array($path, self::INSTALL_ROUTES, true)) return null;
        if (preg_match('#^/(?:app/cms|config|vendor|support|docs|\.git)(?:/|$)|^/\.env(?:$|\.)#i', $checkPath)) {
            return response('Not Found', 404)->header('Cache-Control', 'no-store');
        }
        $state = ($this->service ?? new InstallerService())->status();
        $configurationError = !empty($state['blocked']) || in_array($state['reason'], ['invalid_configuration', 'invalid_installation_lock', 'configured_database_unavailable'], true);
        $json = !in_array($request->method(), ['GET', 'HEAD'], true)
            || str_contains(strtolower((string)$request->header('accept')), 'json')
            || strtolower((string)$request->header('x-requested-with')) === 'xmlhttprequest'
            || $this->isReadApi($request, $checkPath);
        if ($configurationError) {
            $message = ($state['reason'] ?? '') === 'existing_database'
                ? '目标数据库已有数据且未识别为 EyouCMS，安装入口保持关闭；请核对数据库与表前缀，已有站点请使用迁移流程。'
                : '网站配置或数据库连接异常，安装入口保持关闭，请检查配置与数据库服务。';
            return $json
                ? json(['code'=>503,'msg'=>$message,'reason'=>'installation_configuration_error'])->withStatus(503)->header('Cache-Control','no-store')
                : response('<!doctype html><meta charset="utf-8"><title>网站配置异常</title><p>'.$message.'</p>',503)->header('Content-Type','text/html; charset=utf-8')->header('Cache-Control','no-store');
        }
        if ($state['installed']) return null;
        if ($json) return json(['code'=>503,'msg'=>'网站尚未安装，请先完成安装','reason'=>'installation_required','install_url'=>'/install/index.php'])->withStatus(503)->header('Cache-Control','no-store');
        return redirect('/install/index.php')->header('Cache-Control','no-store');
    }
    /** Read APIs remain JSON even when clients omit Accept/XHR headers. */
    private function isReadApi(Request $request, string $path): bool
    {
        $module = $request->get('m', 'home');
        $controller = $request->get('c', '');
        $module = is_string($module) ? strtolower($module) : '';
        $controller = is_string($controller) ? strtolower($controller) : '';
        if (in_array($path, ['/', '/index.php'], true)
            && ($module === 'api' || ($module === 'home' && $controller === 'ajax'))) return true;
        if (preg_match('#^/(?:index\.php/)?(?:api/|home/ajax/)#i', $path)) return true;
        if (!str_starts_with($path, '/web-admin/')) return false;
        $path = \app\cms\admin\support\AdminRoutes::normalizePath($path);
        $readActions = [
            'account'=>['info','login','logout'], 'config'=>['get'], 'dict'=>['get'],
            'rule'=>['get','permission'], 'role'=>['rules'],
            'archive'=>['getcontent','getcommontags'], 'arctype'=>['gettemplates'],
            'channelfield'=>['categoryfields','getarctypelist','getfieldtypelist','ajaxgetregiondata'],
            'citysite'=>['settings'], 'websiteconfig'=>['settings'], 'faq'=>['detail'],
            'filemanager'=>['themes','read'], 'globalvariable'=>['groups'],
            'productparameter'=>['metadata','product'], 'specialnode'=>['topics','choices'],
            'staticgenerate'=>['status'], 'table'=>['show','schema','types'],
            'plugin'=>['list','getinstalledplugins'],
        ];
        foreach (\app\cms\admin\support\AdminRoutes::definitions() as $definition) {
            $prefix = $definition['path'] . '/';
            if (!str_starts_with($path, $prefix)) continue;
            $tail = substr($path, strlen($prefix));
            $action = explode('/', $tail)[0];
            $module = basename($definition['path']);
            if (str_contains($tail, '/')
                && !($module === 'dict' && $action === 'get' && preg_match('#^get/[^/]+$#D', $tail))) return false;
            if (!in_array($action, array_map('strtolower', $definition['actions']), true)) return false;
            return $action === 'select' || in_array($action, $readActions[$module] ?? [], true);
        }
        return false;
    }

}
