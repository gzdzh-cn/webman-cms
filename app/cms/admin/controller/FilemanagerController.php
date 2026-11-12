<?php
namespace app\cms\admin\controller;

use app\cms\service\TemplateFileService;
use app\cms\service\ThemeService;
use app\cms\CmsContext;
use app\cms\ThemeCatalog;
use plugin\admin\api\Auth;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use support\Request;
use support\Response;

class FilemanagerController extends \plugin\admin\app\controller\Base
{
    protected $noNeedAuth = ['themes', 'activate', 'preview'];
    protected function roleIds(): array { return (array)(admin()['roles'] ?? []); }
    public function index(Request $request): Response
    {
        $files = $request->get('section') === 'files' || $request->get('path') !== null || $request->get('activepath') !== null;
        return \app\cms\admin\support\AdminView::raw($files ? 'cms/filemanager' : 'cms/themes')->withHeaders(['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    /** Theme actions reuse the existing module grant; no new rule IDs are needed. */
    protected function canReadThemes(): bool { return Auth::canAccess(\plugin\admin\app\controller\FilemanagerController::class, 'index'); }

    public function themes(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        if (!$this->canReadThemes()) return $this->result(2, '无模板管理读取权限')->withStatus(403);
        try {
            $data = (new ThemeService())->listing($request->get());
            $data['permissions'] = ['activate' => $this->canWrite('edit')];
            return $this->result(0, 'ok', $data);
        } catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }

    public function activate(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed', 405);
        if (!$this->canReadThemes() || !$this->canWrite('edit')) return $this->result(2, '无独立的模板文件写入权限')->withStatus(403);
        $origin = rtrim((string)$request->header('origin', ''), '/');
        if ($origin === '' || strcasecmp($origin, CmsContext::current()->origin()) !== 0) return $this->result(1, '请从当前后台页面提交操作')->withStatus(403);
        try { return $this->result(0, '模板已启用', (new ThemeService())->activate($request->post())); }
        catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }

    /** Authenticated homepage preview never persists the selected theme. */
    public function preview(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        if (!$this->canReadThemes()) return response('Forbidden', 403);
        $previous = CmsContext::current();
        try {
            $input = $request->get();
            foreach (['theme', 'lang', 'device'] as $name) if (!isset($input[$name]) || !is_string($input[$name])) throw new \InvalidArgumentException('预览参数无效');
            if (!in_array($input['device'], ['pc', 'mobile'], true)) throw new \InvalidArgumentException('预览终端无效');
            if (!preg_match('/^[A-Za-z0-9_-]{2,50}$/D', $input['lang'])) throw new \InvalidArgumentException('预览语言无效');
            $theme = ThemeCatalog::canonical($input['theme']);
            ThemeCatalog::root($theme, $input['device']);
            $context = new CmsContext($request);
            $context->parameters['lang'] = $input['lang'];
            $context->parameters['goto'] = $input['device'];
            CmsContext::set($context);
            $context->load();
            if ($context->lang !== $input['lang']) throw new \InvalidArgumentException('预览语言不存在或不可用');
            $context->useTheme($theme, $input['device']);
            return (new \app\controller\CmsController())->index($request)->withHeaders([
                'Cache-Control' => 'private, no-store', 'X-Robots-Tag' => 'noindex, nofollow', 'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\InvalidArgumentException|\RuntimeException $error) {
            return response('无法预览：' . htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), 400)->withHeaders(['Cache-Control' => 'no-store', 'Content-Type' => 'text/plain; charset=utf-8']);
        } finally { CmsContext::set($previous); }
    }

    /** Bare controller/menu access must never authorize arbitrary JS/template writes. */
    protected function canWrite(string $action): bool
    {
        if (!in_array($action, ['edit', 'newfile', 'replace_img'], true)) return false;
        $roles = $this->roleIds(); if (!$roles) return false;
        $ids = [];
        foreach (Role::whereIn('id', $roles)->pluck('rules') as $rules) $ids = array_merge($ids, array_map('trim', explode(',', (string)$rules)));
        if (in_array('*', $ids, true)) return true;
        return Rule::whereIn('id', array_filter($ids, 'ctype_digit'))->where('key', \plugin\admin\app\controller\FilemanagerController::class . '@' . $action)->exists();
    }

    private function result(int $code, string $message, mixed $data = []): Response
    {
        return $this->json($code, $message, $data)->withHeaders(['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function select(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        try {
            $data = (new TemplateFileService())->listing($request->get());
            $data['permissions'] = ['edit'=>empty($data['readonly'])&&$this->canWrite('edit'), 'newfile'=>empty($data['readonly'])&&$this->canWrite('newfile'), 'replace_img'=>false];
            return $this->result(0, 'ok', $data);
        } catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }

    public function read(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        try { return $this->result(0, 'ok', (new TemplateFileService())->read($request->get())); }
        catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }

    private function write(Request $request, string $action, callable $operation): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed', 405);
        if (!$this->canWrite($action)) return $this->result(2, '无独立的模板文件写入权限')->withStatus(403);
        $origin = rtrim((string)$request->header('origin', ''), '/');
        if ($origin === '' || strcasecmp($origin, \app\cms\CmsContext::current()->origin()) !== 0) return $this->result(1, '请从当前后台页面提交操作')->withStatus(403);
        try { return $this->result(0, '操作成功', $operation(new TemplateFileService())); }
        catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }

    public function edit(Request $request): Response { return $this->write($request, 'edit', fn($service) => $service->save($request->post())); }
    public function newfile(Request $request): Response { return $this->write($request, 'newfile', fn($service) => $service->save($request->post(), true)); }
    public function replace_img(Request $request): Response
    {
        return $this->result(2, '模板目录不支持图片上传或替换')->withStatus(403);
    }
}
