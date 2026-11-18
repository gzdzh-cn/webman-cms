<?php
namespace app\cms\admin\controller;

use app\cms\CmsContext;
use app\cms\service\SeoService;
use plugin\admin\api\Auth;
use plugin\admin\api\CmsWritePolicy;
use support\Request;
use support\Response;

class SeoController extends \plugin\admin\app\controller\Base
{
    // Logged-in actions use explicit read and write boundaries below. A bare
    // module grant must never imply the independent save permission.
    protected $noNeedAuth = ['select', 'save'];
    protected function service(): SeoService { return new SeoService(); }
    protected function canRead(): bool { return Auth::canAccess(\plugin\admin\app\controller\SeoController::class, 'index'); }
    protected function canSave(): bool { return CmsWritePolicy::allowed(\plugin\admin\app\controller\SeoController::class, 'save', (array)(admin()['roles'] ?? [])); }
    protected function tools(): array
    {
        $result = [];
        foreach (['static_generate' => \plugin\admin\app\controller\StaticgenerateController::class, 'sitemap' => \plugin\admin\app\controller\SitemapController::class] as $key => $controller) {
            $result[$key] = class_exists($controller) && Auth::canAccess($controller, 'index');
        }
        return $result;
    }

    public function index(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        if (!$this->canRead()) return response('Forbidden', 403);
        return \app\cms\admin\support\AdminView::raw('cms/seo')->withHeaders(['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function select(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        if (!$this->canRead()) return $this->result(2, '无SEO设置读取权限')->withStatus(403);
        try {
            $data = $this->service()->read($this->input($request, $request->get()));
            $data['permissions'] = ['save' => $this->canSave()] + $this->tools();
            return $this->result(0, 'ok', $data);
        } catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }

    public function save(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed', 405);
        if (!$this->canRead() || !$this->canSave()) return $this->result(2, '无独立的SEO保存权限')->withStatus(403);
        $origin = rtrim((string)$request->header('origin', ''), '/');
        if ($origin === '' || strcasecmp($origin, CmsContext::current()->origin()) !== 0) return $this->result(1, '请从当前后台页面提交操作')->withStatus(403);
        try { return $this->result(0, 'SEO设置已保存', $this->service()->save($this->input($request, $request->post()))); }
        catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }

    /** The workspace owns editor language; legacy cookies cannot override it. */
    private function input(Request $request, array $input): array
    {
        $input['lang'] = CmsContext::current()->lang;
        return $input;
    }

    private function result(int $code, string $message, array $data = []): Response
    {
        return $this->json($code, $message, $data)->withHeaders(['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
