<?php
declare(strict_types=1);
namespace app\cms\install;

use support\Request;
use support\Response;

final class InstallerController
{
    private const BLOCKED_REASONS = ['configured_database_unavailable', 'invalid_configuration', 'invalid_installation_lock'];

    private function blocked(array $state): bool
    {
        return !empty($state['blocked']) || in_array($state['reason'] ?? '', self::BLOCKED_REASONS, true);
    }

    private function configurationError(bool $json = false, array $state = []): Response
    {
        $message = ($state['reason'] ?? '') === 'existing_database'
            ? '目标数据库已有数据且未识别为 EyouCMS，安装入口保持关闭。请核对数据库与表前缀，已有站点请使用迁移流程。'
            : '网站配置异常或数据库暂不可用，安装入口保持关闭。请检查配置与数据库服务。';
        $response = $json
            ? json(['code' => 1, 'msg' => $message])->withStatus(503)
            : response('<!doctype html><meta charset="utf-8"><title>网站配置异常</title><p>' . $message . '</p>', 503)->header('Content-Type', 'text/html; charset=utf-8');
        return $response->header('Cache-Control', 'no-store');
    }

    public function index(Request $request): Response
    {
        $service = new InstallerService();
        $state = $service->status(true);
        if ($this->blocked($state)) return $this->configurationError(false, $state);
        if ($state['installed'] && $this->ownedHandoff($request) === null) return response('<!doctype html><meta charset="utf-8"><title>网站已安装</title><p>网站已安装，安装入口已关闭。</p><a href="/app/admin">进入后台</a>', 403)
            ->header('Content-Type', 'text/html; charset=utf-8')->header('Cache-Control', 'no-store');
        $file = base_path('app/cms/install/view/index.html');
        if (!is_file($file)) $file = base_path('install/index.html');
        if (!is_file($file)) return response('安装资源不存在', 404)->header('Cache-Control', 'no-store');
        if (!$state['installed']) $this->prepareInstallerTransport($request);
        $token = (string)$request->session()->get('cms_install_csrf', '') ?: bin2hex(random_bytes(24));
        $request->session()->set('cms_install_csrf', $token);
        if (!$request->session()->get('cms_install_handoff_owner')) $request->session()->set('cms_install_handoff_owner', bin2hex(random_bytes(32)));
        if (!$request->session()->get('cms_install_upload_owner')) $request->session()->set('cms_install_upload_owner', bin2hex(random_bytes(32)));
        return response(str_replace('{{INSTALL_CSRF}}', $token, (string)file_get_contents($file)))
            ->header('Content-Type', 'text/html; charset=utf-8')->header('Cache-Control', 'no-store');
    }

    public function status(Request $request): Response
    {
        $service = new InstallerService();
        $state = $service->status(true);
        if ($this->blocked($state)) return $this->configurationError(true, $state);
        // Public status never reveals a private archive name or scans installed-site assets.
        $data = array_intersect_key($state, array_flip(['installed', 'reason', 'resume_version']));
        if (!$state['installed']) {
            $data += $service->environment();
            $data['eyou_themes'] = array_map(static fn(array $theme): array => array_intersect_key($theme,
                array_flip(['key', 'name', 'path', 'devices'])), (new InstallerThemes(base_path()))->choices());
        }
        if ($handoff = $this->ownedHandoff($request)) $data['handoff'] = $handoff;
        return json(['code' => 0, 'data' => $data])->header('Cache-Control', 'no-store');
    }

    public function install(Request $request): Response
    {
        try {
            $service = new InstallerService();
            $state = $service->status(true);
            if ($this->blocked($state)) return $this->configurationError(true, $state);
            if (!$state['installed']) $this->prepareInstallerTransport($request);
            $token = (string)$request->session()->get('cms_install_csrf', '');
            if ($token === '' || !hash_equals($token, (string)$request->post('_csrf', ''))) {
                return json(['code' => 1, 'msg' => '安装会话已失效，请刷新安装页'])->withStatus(403)->header('Cache-Control', 'no-store');
            }
            $data = $request->post();
            unset($data['sql_file'], $data['sql_sha256'], $data['source_prefix']);
            $action = $request->post('action', 'install');
            if (in_array($action, ['upload_start', 'upload_chunk', 'upload_finish'], true)) {
                if (!filter_var($data['compatibility'] ?? false, FILTER_VALIDATE_BOOLEAN) || !filter_var($data['import_sql'] ?? false, FILTER_VALIDATE_BOOLEAN)) throw new InstallerValidationException(['compatibility'=>'仅兼容 EyouCMS 模式允许上传 SQL']);
                if (!empty($state['installed'])) throw new \RuntimeException('安装入口已关闭');
                $owner = (string)$request->session()->get('cms_install_upload_owner', '');
                if ($owner === '') throw new \RuntimeException('安装会话已失效，请刷新');
                $upload = new InstallerUpload(base_path(), $owner);
                if ($action === 'upload_start') $result = $upload->start($data['name'] ?? null, $data['size'] ?? null);
                elseif ($action === 'upload_chunk') {
                    $chunk = $request->file('chunk');
                    if (!$chunk || !$chunk->isValid()) throw new \InvalidArgumentException('上传分片无效');
                    $result = $upload->chunk((string)($data['upload_id'] ?? ''), $data['index'] ?? null, $chunk->getPathname());
                } else $result = $upload->finish((string)($data['upload_id'] ?? ''));
                return json(['code'=>0, 'data'=>$result])->header('Cache-Control','no-store');
            }
            if (!in_array($action, ['check','preflight','install','step'], true)) throw new \InvalidArgumentException('未知安装操作');
            $compatible = filter_var($data['compatibility'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($action !== 'check' && $compatible && ($state['resume_version'] ?? null) !== 2
                && (!filter_var($data['import_sql'] ?? false, FILTER_VALIDATE_BOOLEAN) || empty($data['upload_token']))) {
                throw new InstallerValidationException(['upload_token'=>'EyouCMS 模式必须选择 SQL 文件并完整上传后才能继续。']);
            }
            if ($action !== 'check' && filter_var($data['import_sql'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                if (!$compatible) throw new InstallerValidationException(['compatibility'=>'仅兼容 EyouCMS 模式允许导入 SQL']);
                $owner=(string)$request->session()->get('cms_install_upload_owner','');
                $data += (new InstallerUpload(base_path(),$owner))->resolve((string)($data['upload_token']??''));
            }
            $result = match ($action) {
                'check' => $service->inspect($data),
                'preflight' => $service->preflight($data),
                default => $service->step($data, 3, (string)$request->session()->get('cms_install_handoff_owner', '')),
            };
            if (!empty($result['installed'])) {
                $result = array_intersect_key($result, array_flip(['installed', 'tables', 'demo', 'login_url', 'restart_required', 'archive_pending', 'archive_status', 'template_mode', 'finish_status', 'handoff']));
                $message = $result['handoff']['message'] ?? '数据库安装完成，请检查安装收尾状态';
            } else {
                $message = $action === 'check' ? '数据库检查通过' : ($action === 'preflight' ? '安装预检通过，请核对目标表清单' : '正在安装，请勿关闭页面');
            }
            return json(['code' => 0, 'data' => $result, 'msg' => $message])->header('Cache-Control', 'no-store');
        } catch (\InvalidArgumentException | \RuntimeException $error) {
            $message = $error instanceof \PDOException ? '数据库操作失败，请检查连接权限及服务状态' : $error->getMessage();
            return json(['code' => 1, 'msg' => $message, 'data'=>['field_errors'=>$this->fieldErrors($error)]])->withStatus(400)->header('Cache-Control', 'no-store');
        } catch (\Throwable $error) {
            return json(['code' => 1, 'msg' => '安装未完成，请检查环境后使用相同设置重试'])->withStatus(500)->header('Cache-Control', 'no-store');
        }
    }
    /** Separate installer transport from pre-existing login/captcha state. */
    private function prepareInstallerTransport(Request $request): void
    {
        $session = $request->session();
        $allowed = ['cms_install_csrf' => 48, 'cms_install_handoff_owner' => 64, 'cms_install_upload_owner' => 64];
        $kept = [];
        foreach ($session->all() as $name => $value) {
            if (isset($allowed[$name]) && is_string($value) && preg_match('/^[a-f0-9]{' . $allowed[$name] . '}$/D', $value)) $kept[$name] = $value;
        }
        if ($kept !== $session->all()) {
            $session->flush();
            $session->save();
            // Upstream regenerateId changes only the cookie, so explicitly
            // replace both request context values as in normal admin login.
            $id = Request::createSessionId();
            $transport = new \Workerman\Protocols\Http\Session($id);
            $transport->put($kept);
            $transport->save();
            $request->sessionId($id);
            $request->context['session'] = $transport;
        }
    }
    private function ownedHandoff(Request $request): ?array
    {
        $owner=(string)$request->session()->get('cms_install_handoff_owner','');
        if($owner==='')return null;
        try{return $this->sessionHandoff($request,(new InstallerHandoff())->status($owner));}catch(\Throwable $error){return null;}
    }

    private function sessionHandoff(Request $request,array $handoff): array
    {
        // A lost login response or a refreshed completion page must still offer
        // the explicit button. The POST independently rechecks the real account.
        if(($handoff['restart_status']??'')==='ready'
            &&$request->session()->get('cms_install_authenticated_task')===($handoff['task_id']??null)
            &&!empty($request->session()->get('admin')['id'])){
            $handoff['login_available']=true;$handoff['message']='已登录，点击“进入后台”打开工作台。';
        }
        return $handoff;
    }

    public function handoffStatus(Request $request): Response
    {
        try{
            $service=new InstallerHandoff();
            $probe=(string)$request->header('x-cms-install-probe','');
            if($probe!==''&&in_array($request->getRemoteIp(),['127.0.0.1','::1'],true))$handoff=$service->probe($probe);
            else $handoff=$this->sessionHandoff($request,$service->status((string)$request->session()->get('cms_install_handoff_owner','')));
            return json(['code'=>0,'data'=>['installed'=>true,'handoff'=>$handoff]])->header('Cache-Control','no-store');
        }catch(\Throwable $error){return json(['code'=>1,'msg'=>'安装交接会话不存在或已失效，请使用正常登录入口'])->withStatus(403)->header('Cache-Control','no-store');}
    }

    public function handoffRetry(Request $request): Response {return $this->handoffAction($request,false);}
    public function handoffLogin(Request $request): Response {return $this->handoffAction($request,true);}
    private function handoffAction(Request $request,bool $login): Response
    {
        try{
            $csrf=(string)$request->session()->get('cms_install_csrf','');
            if($csrf===''||!hash_equals($csrf,(string)$request->post('_csrf','')))throw new \RuntimeException('安装交接会话已失效，请使用正常登录入口');
            $owner=(string)$request->session()->get('cms_install_handoff_owner','');$service=new InstallerHandoff();
            $data=$login?$service->login($request,$owner):['installed'=>true,'handoff'=>$service->retry($owner)];
            return json(['code'=>0,'data'=>$data])->header('Cache-Control','no-store');
        }catch(\Throwable $error){
            $message=$error instanceof \PDOException?'数据库暂不可用，请检查服务后重试':($error instanceof \RuntimeException?$error->getMessage():'操作未完成，请重新检查服务状态或使用正常登录入口');
            return json(['code'=>1,'msg'=>$message])->withStatus(400)->header('Cache-Control','no-store');
        }
    }
    private function fieldErrors(\Throwable $error): array
    {
        if ($error instanceof InstallerValidationException) return $error->errors;
        if ($error instanceof \PDOException) {
            $number=(int)($error->errorInfo[1]??0);
            if (in_array($number,[2002,2003,2005,2006],true)) return ['host'=>'无法连接数据库，请核对主机、端口及服务状态','port'=>'请核对端口'];
            if ($number===1045) return ['user'=>'账号、密码或访问授权不正确','password'=>'请核对数据库凭据'];
            return ['database'=>'数据库操作失败，请检查权限及服务状态'];
        }
        if (preg_match('/SQL|上传|分片|前缀识别/', $error->getMessage())) return ['upload_token'=>$error->getMessage()];
        return [];
    }
}
