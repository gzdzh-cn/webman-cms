<?php
namespace app\cms\http;

use app\cms\compat\exception\HttpException;
use app\cms\compat\exception\HttpResponseException;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

/** Translate the isolated compiler's HTTP exceptions at Webman's catch point. */
 class CmsExceptionHandler extends \support\exception\Handler
{
    public function __construct($logger, $debug, private ?\app\cms\service\ErrorDisplaySettings $errorDisplay = null)
    {
        parent::__construct($logger, $debug);
        $this->errorDisplay ??= new \app\cms\service\ErrorDisplaySettings();
    }

    public function report(Throwable $exception)
    {
        if ($exception instanceof \app\cms\security\SessionUnavailable) return;
        if ($exception instanceof HttpResponseException || ($exception instanceof HttpException && $exception->getStatusCode() >= 400 && $exception->getStatusCode() < 500)) return;
        parent::report($exception);
    }

    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof \app\cms\security\SessionUnavailable) {
            return new Response(503, ['Content-Type'=>'text/plain; charset=utf-8', 'Cache-Control'=>'no-store', 'Retry-After'=>'5'], '会话服务暂时不可用，请稍后重试');
        }
        if (\app\cms\admin\support\AdminRoutes::isBackend($request)) {
            return (new \app\cms\admin\exception\Handler($this->logger, (bool)config('cms_admin.app.debug', false)))->render($request, $exception);
        }
        if ($exception instanceof HttpResponseException) return $exception->getResponse();
        $public = ($request->plugin ?? '') !== 'admin' && !preg_match('#^/app/admin(?:/|$)#', $request->path());
        if ($public && $this->errorDisplay->nativeEnabled()) {
            // Explicit public debugging is independent of APP_DEBUG. The admin
            // plugin retains its own exception handler and response protocol.
            return (new \support\exception\Handler($this->logger, true))->render($request, $exception)->withHeaders([
                'Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, nofollow',
            ]);
        }
        if ($public && !$request->expectsJson()) {
            $status = $exception instanceof HttpException ? (int)$exception->getStatusCode() : 500;
            $missingTemplate = $exception instanceof \RuntimeException && (
                str_starts_with($exception->getMessage(), 'Template does not exist or escapes template root:')
                || str_starts_with($exception->getMessage(), 'CMS template directory is missing')
                || str_starts_with($exception->getMessage(), 'Page template is empty:')
            );
            return \app\cms\ErrorPage::make($status, $missingTemplate, $exception instanceof HttpException ? $exception->getHeaders() : []);
        }
        if ($public && !($exception instanceof HttpException)) {
            // Retain the existing public JSON protocol, without debug traces.
            return new Response(200, ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'no-store'], json_encode([
                'code' => $exception->getCode() ?: 500, 'msg' => '页面暂时无法显示',
            ], JSON_UNESCAPED_UNICODE));
        }
        if ($exception instanceof HttpException) {
            $status = (int)$exception->getStatusCode();
            if ($status < 400 || $status > 599) $status = 500;
            $message = $exception->getMessage() ?: ($status === 404 ? '页面不存在' : '请求失败');
            $body = '<!doctype html><html lang="zh"><meta charset="utf-8"><title>' . $status . '</title><h1>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</h1></html>';
            return new Response($status, $exception->getHeaders() + ['Content-Type' => 'text/html; charset=utf-8'], $body);
        }
        return parent::render($request, $exception);
    }
}
