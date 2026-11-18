<?php

namespace app\cms\http;

use app\cms\CmsContext;
use Webman\MiddlewareInterface;
use Webman\Http\Request;
use Webman\Http\Response;

 class CmsContextMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (preg_match('#^/web-admin/update/(check|start|status|source)/?$#D', $request->path())) return $this->processInternal($request,$handler);
        return \app\cms\maintenance\MaintenanceGate::read(fn()=> $this->processInternal($request,$handler),fn()=>response('程序正在升级，请稍后重试。',503,['Retry-After'=>'5','Cache-Control'=>'no-store']));
    }

    private function processInternal(Request $request, callable $handler): Response
    {
        if (\app\cms\maintenance\DatabaseBackup::maintenance() && !preg_match('#^/web-admin/databasebackup(?:/(?:index|select|status|restore|tasks|cleanup))?/?$#D',$request->path())) return response('网站正在恢复数据库，请稍后重试。',503,['Retry-After'=>'10','Cache-Control'=>'no-store']);
        if ($response = (new \app\cms\install\InstallationGate())->response($request)) return $response;
        // A new object also resets CLI/non-coroutine workers between requests.
        $context = new CmsContext($request);
        CmsContext::set($context);
        $recovery=preg_match('#^/web-admin/databasebackup(?:/(?:index|select|status|restore|tasks|cleanup))?/?$#D',$request->path());
        $response=$recovery?$handler($request):\app\cms\maintenance\DatabaseTraffic::read(fn()=>$handler($request),fn()=>response('网站正在恢复数据库，请稍后重试。',503,['Retry-After'=>'10','Cache-Control'=>'no-store']));
        foreach ($context->memo['response_cookies'] ?? [] as $name => $value) {
            $options = $context->memo['response_cookie_options'][$name] ?? [];
            $options = is_array($options) ? $options : ['expire' => (int)$options];
            $response->cookie((string)$name, $value === null ? '' : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value), $value === null ? -1 : ($options['expire'] ?? null), $options['path'] ?? '/', $options['domain'] ?? '', (bool)($options['secure'] ?? false), (bool)($options['httponly'] ?? false), $options['samesite'] ?? '');
        }
        return $response;
    }
}
