<?php
namespace app\cms\admin\middleware;

use plugin\admin\api\Auth;
use ReflectionException;
use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class AccessControl implements MiddlewareInterface
{
    protected function invalidateCache(): void { (new \app\cms\service\AdminOperationService())->invalidateCache(); }
    protected function canAccess(Request $request, int &$code, string &$msg): bool
    {
        if (Auth::canAccess($request->controller, $request->action, $code, $msg)) return true;
        if ($code === 2 && \app\cms\admin\support\ContentEditorReadAccess::allows($request)) {
            $code = 0; $msg = ''; return true;
        }
        // Existing category roles must be able to load the fields used by their
        // original editor. Keep login and the explicit new field rule intact.
        if ($code !== 2 || $request->controller !== \plugin\admin\app\controller\ChannelfieldController::class
            || $request->action !== 'categoryFields' || $request->method() !== 'GET') return false;
        $typeId = $request->get('typeid', 0);
        if (!is_scalar($typeId) || !preg_match('/^\d+$/D', (string)$typeId)) return false;
        $actions = (int)$typeId > 0 ? ['select', 'update'] : ['select', 'insert'];
        foreach ($actions as $action) {
            $aliasCode = 0; $aliasMessage = '';
            if (Auth::canAccess(\plugin\admin\app\controller\ArctypeController::class, $action, $aliasCode, $aliasMessage)) {
                $code = 0; $msg = ''; return true;
            }
        }
        return false;
    }
    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     * @throws ReflectionException|BusinessException
     */
    public function process(Request $request, callable $handler): Response
    {
        $controller = $request->controller;
        $action = $request->action;

        $code = 0;
        $msg = '';
        if (!$this->canAccess($request, $code, $msg)) {
            if ($request->expectsJson()) {
                $response = json(['code' => $code, 'msg' => $msg, 'data' => []]);
            } else {
                if ($code === 401) {
                  $response = admin_error_401_script();
                } else {
                    $response = \app\cms\admin\support\AdminView::render('common/error/403')->withStatus(403);
                }
            }

        } else {
            try {
                \app\cms\admin\support\LanguageAdminContext::prepare($request);
                \app\cms\admin\support\ContentAdminAccess::prepare($request);
            }
            catch (\InvalidArgumentException $error) { return json(['code'=>2,'msg'=>$error->getMessage(),'data'=>[]])->withStatus(403); }
            $publicationBefore = [];
            if ($request->method() === 'POST' && !$this->isNonPublishingAction($request)) {
                try { $publicationBefore = $this->publicationService()->capture($request); }
                catch (\Throwable $error) { $publicationBefore = ['capture_error' => $error->getMessage()]; }
            }
            $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        }

        // Backup previews include the log table. Their signed private journal is
        // the audit record; a post-preview database write invalidates that plan.
        if ($request->method() === 'POST' && !$this->isNonPublishingAction($request) && function_exists('admin_id') && admin_id()) {
            $result = json_decode($response->rawBody(), true);
            if (is_array($result) && ($result['code'] ?? null) === 0 && !in_array(strtolower((string)$action), ['select', 'login', 'logout'], true)) {
                $filePublished = $controller === \plugin\admin\app\controller\FilemanagerController::class
                    && in_array($action, ['edit', 'newfile', 'replace_img', 'activate'], true)
                    && !empty(\app\cms\CmsContext::current()->memo['cms.template_file_cache_published']);
                if (!$filePublished) {
                    try { $this->invalidateCache(); }
                    catch (\Throwable $error) {
                        $result = $this->warning($result, 'cache_warning', '数据已保存，缓存刷新未完成，请稍后重试或检查缓存目录');
                        try { \support\Log::warning('CMS cache invalidation failed after commit: ' . $error->getMessage()); } catch (\Throwable) {}
                    }
                }
                $published = $this->publishOperations($request, $result, $publicationBefore ?? []);
                if ($published !== json_decode($response->rawBody(), true)) $response->withBody(json_encode($published, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
                $this->writeOperationLog($request);
            }
        }

        return $response;

    }

    /** Session changes and previews may persist their own task, but change no public content. */
    private function isNonPublishingAction(Request $request): bool
    {
        $controller = (string)$request->controller;
        $action = strtolower((string)$request->action);
        if ($controller === \plugin\admin\app\controller\DatabasebackupController::class) return true;
        $actions = [
            \plugin\admin\app\controller\UpdateController::class => ['start'],
            \plugin\admin\app\controller\IndexController::class => ['restartstart'],
            \plugin\admin\app\controller\LanguageController::class => ['switch', 'preview'],
            \plugin\admin\app\controller\ArchiveController::class => ['copyplan'],
            \plugin\admin\app\controller\ArctypeController::class => ['batchpreview'],
            \plugin\admin\app\controller\TagsController::class => ['batchpreview'],
            \plugin\admin\app\controller\SearchwordController::class => ['batchpreview'],
        ];
        return in_array($action, $actions[$controller] ?? [], true);
    }

    /** These callbacks run after the business transaction has committed. */
    protected function publicationService(): \app\cms\service\PublicationService { return new \app\cms\service\PublicationService(); }
    protected function scheduleMaps(Request $request): void { (new \app\cms\service\AdminOperationService())->scheduleMaps($request); }
    protected function writeOperationLog(Request $request): void
    {
        (new \app\cms\service\AdminOperationService())->writeOperationLog($request);
    }

    /** Preserve generic CRUD success payloads that intentionally return scalar data. */
    private function warning(array $result, string $key, string $message): array
    {
        if (!array_key_exists('data', $result) || is_array($result['data'])) $result['data'][$key] = $message;
        else $result[$key] = $message;
        $result['msg'] = (string)($result['msg'] ?? '') . '；' . $message;
        return $result;
    }

    private function publishOperations(Request $request, array $result, array $before): array
    {
        $module = class_basename((string)$request->controller);
        if (!in_array($module, ['ArchiveController', 'ArctypeController', 'RecycleController', 'TagsController', 'SeoController', 'WebsiteconfigController', 'LanguageController', 'FilemanagerController'], true)) return $result;
        try {
            $this->scheduleMaps($request);
            if (isset($before['capture_error'])) throw new \RuntimeException('操作前的发布范围采集失败：' . $before['capture_error']);
            if ($before) {
                $publication = $this->publicationService()->publish($request, $before, $result);
                foreach ($publication['publications'] as $item) {
                    if (!array_key_exists('data', $result) || is_array($result['data'])) $result['data']['publication'][] = $item;
                    else $result['publication'][] = $item;
                }
                foreach ($publication['warnings'] as $warning) $result['msg'] .= '；' . $warning;
            }
        } catch (\Throwable $error) {
            $result = $this->warning($result, 'publication_warning', '数据已保存，自动发布未完成，请检查静态生成及Sitemap任务');
            try { \support\Log::warning('CMS automatic publication could not be scheduled: ' . $error->getMessage()); } catch (\Throwable) {}
        }
        return $result;
    }

}
