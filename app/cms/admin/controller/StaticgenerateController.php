<?php
declare(strict_types=1);
namespace app\cms\admin\controller;
use app\cms\service\StaticGenerationService;
use support\Request;
use support\Response;

 class StaticgenerateController extends \plugin\admin\app\controller\GeneratedModuleController
{
    protected $noNeedAuth = ['select', 'start', 'step', 'status', 'clean'];
    public function index(Request $request): Response { if ($request->method() !== 'GET') return response('Method Not Allowed', 405); if (!$this->canRead()) return response('Forbidden', 403); return \app\cms\admin\support\AdminView::raw('cms/staticgenerate')->withHeaders(['Cache-Control' => 'no-store']); }
    public function select(Request $request): Response { return $this->readAction($request, function () use ($request) { $data = (new StaticGenerationService())->listing($request->get()); $data['permissions'] = ['start' => $this->canWrite('start'), 'step' => $this->canWrite('step'), 'clean' => $this->canWrite('clean')]; return $data; }); }
    public function start(Request $request): Response { return $this->writeAction($request, 'start', fn() => (new StaticGenerationService())->start($request->post(), (int)admin_id())); }
    public function step(Request $request): Response { return $this->writeAction($request, 'step', fn() => (new StaticGenerationService())->status($request->post())); }
    public function status(Request $request): Response { return $this->readAction($request, fn() => (new StaticGenerationService())->status($request->get())); }
    public function clean(Request $request): Response { return $this->writeAction($request, 'clean', fn() => (new StaticGenerationService())->clean($request->post())); }
}
