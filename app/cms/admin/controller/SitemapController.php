<?php
declare(strict_types=1);
namespace app\cms\admin\controller;
use app\cms\service\SitemapService;
use support\Request;
use support\Response;

 class SitemapController extends \plugin\admin\app\controller\GeneratedModuleController
{
    protected $noNeedAuth = ['select', 'save', 'generate', 'clean', 'baiduPreview', 'baiduSave', 'baiduPush', 'baiduSchedule'];
    public function index(Request $request): Response { if ($request->method() !== 'GET') return response('Method Not Allowed', 405); if (!$this->canRead()) return response('Forbidden', 403); return \app\cms\admin\support\AdminView::raw('cms/sitemap')->withHeaders(['Cache-Control' => 'no-store']); }
    public function select(Request $request): Response { return $this->readAction($request, function () use ($request) { $data = (new SitemapService())->listing($request->get()); $data['permissions'] = ['save' => $this->canWrite('save'), 'generate' => $this->canWrite('generate'), 'clean' => $this->canWrite('clean'), 'baiduSave' => $this->canWrite('baiduSave'), 'baiduPush' => $this->canWrite('baiduPush'), 'baiduSchedule' => $this->canWrite('baiduSchedule')]; $data['baidu'] = (new \app\cms\service\BaiduPushService())->status($request->get()); $data['baidu_schedule'] = (new \app\cms\service\BaiduPushScheduleService())->status($request->get()); return $data; }); }
    protected function canWrite(string $action): bool
    {
        if (in_array($action, ['baiduSave','baiduPush','baiduSchedule'], true)) return \plugin\admin\api\CmsWritePolicy::allowed(static::class, $action, (array)(admin()['roles'] ?? []));
        return parent::canWrite($action);
    }
    public function baiduSchedule(Request $request): Response { return $this->writeAction($request, 'baiduSchedule', fn() => (new \app\cms\service\BaiduPushScheduleService())->save($request->post())); }
    public function baiduPreview(Request $request): Response { return $this->readAction($request, fn() => (new \app\cms\service\BaiduPushService())->preview($request->get())); }
    public function baiduSave(Request $request): Response { return $this->writeAction($request, 'baiduSave', fn() => (new \app\cms\service\BaiduPushService())->savePush($request->post())); }
    public function baiduPush(Request $request): Response { return $this->writeAction($request, 'baiduPush', fn() => (new \app\cms\service\BaiduPushService())->push($request->post())); }
    public function save(Request $request): Response { return $this->writeAction($request, 'save', fn() => (new SitemapService())->save($request->post())); }
    public function generate(Request $request): Response { return $this->writeAction($request, 'generate', fn() => (new SitemapService())->generate($request->post())); }
    public function clean(Request $request): Response { return $this->writeAction($request, 'clean', fn() => (new SitemapService())->clean($request->post())); }
}
