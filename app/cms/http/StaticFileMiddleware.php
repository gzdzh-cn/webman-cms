<?php
namespace app\cms\http;

use app\cms\FileResponse;
use app\cms\AssetResolver;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

 class StaticFileMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        // Native static callbacks are cached by Webman. Check ownership on every
        // request, including after withdrawal, before that callback can read bytes.
        $path = rawurldecode($request->path());
        if (!$request->plugin && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['html', 'htm'], true)
            && (AssetResolver::isGeneratedHtml(\app\cms\Paths::publicRoot() . '/' . ltrim($path, '/')) || (new \app\cms\service\PublicStaticPublisher())->ownsFile(\app\cms\Paths::publicRoot() . '/' . ltrim($path, '/')))) {
            return (new \app\middleware\CmsContextMiddleware())->process($request, static fn($request) => (new \app\controller\CmsController())->resolve($request));
        }
        $response = $handler($request);
        if (isset($response->file['file'])) {
            // Re-resolve the URL through the same mapped mounts and per-file
            // authorization as CMS routes. A stale file under an old mount
            // must never bypass the system-resource manifest.
            $file = AssetResolver::resolve($request->path());
            if ($file === null) return response('页面不存在', 404);
            return FileResponse::make($request, $file);
        }
        return $response;
    }
}
