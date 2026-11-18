<?php
declare(strict_types=1);
namespace app\cms\admin\controller;

use app\cms\CmsContext;
use plugin\admin\api\Auth;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use support\Request;
use support\Response;

/** Exact write permissions; an index/controller-only grant cannot mutate generated files. */
abstract class GeneratedModuleController extends \plugin\admin\app\controller\Base
{
    protected function canRead(): bool { return Auth::canAccess(static::class, 'index'); }
    protected function canWrite(string $action): bool
    {
        if (!in_array($action, ['start', 'step', 'clean', 'save', 'generate'], true)) return false;
        $roles = (array)(admin()['roles'] ?? []); if (!$roles) return false;
        $ids = []; foreach (Role::whereIn('id', $roles)->pluck('rules') as $rules) $ids = array_merge($ids, array_map('trim', explode(',', (string)$rules)));
        if (in_array('*', $ids, true)) return true;
        return Rule::whereIn('id', array_filter($ids, 'ctype_digit'))->where('key', static::class . '@' . $action)->exists();
    }
    protected function result(int $code, string $message, array $data = []): Response { return $this->json($code, $message, $data)->withHeaders(['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']); }
    protected function readAction(Request $request, callable $operation): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        if (!$this->canRead()) return $this->result(2, '没有当前模块读取权限')->withStatus(403);
        try { return $this->result(0, 'ok', $operation()); }
        catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }
    protected function writeAction(Request $request, string $action, callable $operation): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed', 405);
        if (!$this->canRead() || !$this->canWrite($action)) return $this->result(2, '没有独立的当前操作权限')->withStatus(403);
        $origin = rtrim((string)$request->header('origin', ''), '/');
        if ($origin === '' || strcasecmp($origin, CmsContext::current()->origin()) !== 0) return $this->result(2, '请从当前后台页面提交操作')->withStatus(403);
        try { return $this->result(0, '操作完成', $operation()); }
        catch (\InvalidArgumentException|\RuntimeException $error) { return $this->result(1, $error->getMessage()); }
    }
}
