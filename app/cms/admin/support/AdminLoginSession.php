<?php
declare(strict_types=1);

namespace app\cms\admin\support;

use plugin\admin\app\model\Admin;
use plugin\admin\app\model\AdminRole;
use RuntimeException;
use support\exception\BusinessException;
use support\Request;
use Workerman\Protocols\Http\Session;

/** Establish a session only after the caller has verified its authentication proof. */
final class AdminLoginSession
{
    public static function establish(Request $request, Admin $admin): array
    {
        if ((int)$admin->id < 1 || (int)$admin->status !== 0) {
            throw new BusinessException('当前账户暂时无法登录');
        }

        $admin->login_at = date('Y-m-d H:i:s');
        if (!$admin->save()) throw new RuntimeException('登录状态保存失败，请重试');
        $identity = $admin->toArray();
        $identity['password'] = md5((string)$admin->password);
        $identity['roles'] = AdminRole::where('admin_id', $admin->id)->pluck('role_id')->toArray();
        $identity['session_last_update_time'] = time();
        $identity['session_nonce'] = bin2hex(random_bytes(32));

        $oldSession = $request->session();
        $newId = Request::createSessionId();
        $newSession = new Session($newId);
        $data = $oldSession->all();
        unset($data['admin'], $data['captcha-login']);
        $registry = new \app\cms\security\AdminSessionRegistry();
        $registered = false;
        try {
            // Registration must precede publication of an authenticated session.
            $registry->register($newId, (int)$admin->id, $identity['session_nonce']);
            $registered = true;
            $newSession->put($data);
            $newSession->set('admin', $identity);
            $newSession->save();

            // Session::save() does not propagate handler write failures.
            $saved = (new Session($newId))->get('admin');
            if (!is_array($saved) || $saved !== $identity) {
                throw new RuntimeException('登录会话保存失败，请检查会话目录后重试');
            }

            if (!empty($oldSession->get('admin'))) $registry->revoke($oldSession->getId());
            $oldSession->flush();
            $oldSession->save();
            if ((new Session($oldSession->getId()))->all() !== []) {
                throw new RuntimeException('旧登录会话清理失败，请检查会话存储后重试');
            }
        } catch (\Throwable $error) {
            if ($registered) $registry->revoke($newId);
            $newSession->flush();
            $newSession->save();
            throw $error;
        }

        // The upstream regenerate helper updates only the cookie. Replace both
        // request context values so this request and the next use the same ID.
        $request->sessionId($newId);
        $request->context['session'] = $newSession;

        return ['nickname' => (string)($identity['nickname'] ?? ''), 'token' => $newId];
    }
}
