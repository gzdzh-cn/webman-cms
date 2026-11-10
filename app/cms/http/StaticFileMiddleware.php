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
            && (AssetResolver::isGeneratedHtml(public_path() . '/' . ltrim($path, '/')) || (new \app\cms\service\PublicStaticPublisher())->ownsFile(public_path() . '/' . ltrim($path, '/')))) {
            return (new \app\middleware\CmsContextMiddleware())->process($request, static fn($request) => (new \app\controller\CmsController())->resolve($request));
        }
        $response = $handler($request);
        if (isset($response->file['file'])) {
            if (!AssetResolver::isPublicFile($response->file['file'], public_path('', $request->plugin ?: null))) return response('页面不存在', 404);
            return FileResponse::make($request, $response->file['file'])->withHeaders($response->getHeaders());
        }
        return $response;
    }
}
