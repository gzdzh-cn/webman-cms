<?php
declare(strict_types=1);
namespace app\cms\admin\controller;
use app\cms\service\SitemapService;
use support\Request;
use support\Response;

 class SitemapController extends \plugin\admin\app\controller\GeneratedModuleController
{
    protected $noNeedAuth = ['select', 'save', 'generate', 'clean'];
    public function index(Request $request): Response { if ($request->method() !== 'GET') return response('Method Not Allowed', 405); if (!$this->canRead()) return response('Forbidden', 403); return \app\cms\admin\support\AdminView::raw('cms/sitemap')->withHeaders(['Cache-Control' => 'no-store']); }
    public function select(Request $request): Response { return $this->readAction($request, function () use ($request) { $data = (new SitemapService())->listing($request->get()); $data['permissions'] = ['save' => $this->canWrite('save'), 'generate' => $this->canWrite('generate'), 'clean' => $this->canWrite('clean')]; return $data; }); }
    public function save(Request $request): Response { return $this->writeAction($request, 'save', fn() => (new SitemapService())->save($request->post())); }
    public function generate(Request $request): Response { return $this->writeAction($request, 'generate', fn() => (new SitemapService())->generate($request->post())); }
    public function clean(Request $request): Response { return $this->writeAction($request, 'clean', fn() => (new SitemapService())->clean($request->post())); }
}
