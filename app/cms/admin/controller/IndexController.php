<?php

namespace app\cms\admin\controller;

use plugin\admin\app\common\Util;
use plugin\admin\app\model\Option;
use plugin\admin\app\model\User;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use think\db\Where;
use Throwable;
use Workerman\Worker;
 


class IndexController
{

    /**
     * 无需登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['index'];

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['dashboard', 'featuremap', 'restartInfo', 'restartStart', 'restartStatus'];

    /**
     * 后台主页
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function index(Request $request): Response
    {
        clearstatcache();
        if (!(new \app\cms\install\InstallerService())->status()['installed']) {
            return redirect('/install');
        }
        $admin = admin();
        if (!$admin) {
            return \app\cms\admin\support\AdminView::raw('account/login');
        }
        return \app\cms\admin\support\AdminView::raw('index/index', ['title' => \app\cms\admin\support\AdminBranding::name()]);
    }

    /**
     * 仪表板
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function dashboard(Request $request): Response
    {
        $repository = new \app\cms\repository\CmsRepository();
        return \app\cms\admin\support\AdminView::raw('cms/dashboard', [
            'categories' => $repository->table('arctype')->where(['is_del' => 0, 'lang' => \app\cms\CmsContext::current()->lang])->count(),
            'documents' => $repository->table('archives')->where(['is_del' => 0, 'lang' => \app\cms\CmsContext::current()->lang])->count(),
            'today' => $repository->table('archives')->where(['is_del' => 0, 'lang' => \app\cms\CmsContext::current()->lang])->where('add_time', '>=', strtotime('today'))->count(),
            'messages' => $repository->table('guestbook')->where(['is_read' => 0, 'lang' => \app\cms\CmsContext::current()->lang])->count(),
            'dashboardInfo' => (new \app\cms\service\AdminDashboardInfo())->collect($request, $repository),
        ]);
    }

    /** A login-protected directory; each destination keeps its own authorization. */
    public function featuremap(Request $request): Response
    {
        return \app\cms\admin\support\AdminView::raw('cms/featuremap');
    }

    /** Restart is intentionally restricted to super administrators. */
    public function restartInfo(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        $allowed = \app\cms\admin\support\Auth::isSuperAdmin();
        return json(['code'=>0, 'msg'=>'ok', 'data'=>['allowed'=>$allowed, 'csrf'=>$allowed ? \app\cms\admin\support\LanguageAdminSecurity::token($request) : null, 'wait_ms'=>(\app\cms\service\AdminRestartService::restartTimeout()+65)*1000]])->withHeader('Cache-Control', 'no-store');
    }

    public function restartStart(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed', 405);
        if (!\app\cms\admin\support\Auth::isSuperAdmin()) return json(['code'=>2,'msg'=>'仅超级管理员可重启程序','data'=>[]])->withStatus(403);
        try {
            \app\cms\admin\support\LanguageAdminSecurity::assertWrite($request);
            $result=(new \app\cms\service\AdminRestartService())->start((int)admin_id());
            try { \support\Log::info('Admin restart requested by administrator '.(int)admin_id().'; task '.$result['id']); } catch (\Throwable) {}
            return json(['code'=>0,'msg'=>'已安排重启','data'=>$result])->withHeader('Cache-Control','no-store');
        } catch (\InvalidArgumentException|\RuntimeException $error) {
            return json(['code'=>1,'msg'=>$error->getMessage(),'data'=>[]])->withHeader('Cache-Control','no-store');
        } catch (\Throwable $error) {
            try { \support\Log::error('Admin restart failed: '.$error->getMessage()); } catch (\Throwable) {}
            return json(['code'=>1,'msg'=>'无法安排重启，请检查服务日志','data'=>[]])->withHeader('Cache-Control','no-store');
        }
    }

    public function restartStatus(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        if (!\app\cms\admin\support\Auth::isSuperAdmin()) return json(['code'=>2,'msg'=>'无权查看重启任务','data'=>[]])->withStatus(403);
        try {
            $id=$request->get('id','');
            if (!is_string($id)) throw new \RuntimeException('重启任务无效');
            $result=(new \app\cms\service\AdminRestartService())->status($id, (int)admin_id());
            return json(['code'=>0,'msg'=>'ok','data'=>$result])->withHeader('Cache-Control','no-store');
        } catch (\Throwable $error) {
            return json(['code'=>1,'msg'=>'重启任务不存在或状态不可用','data'=>[]])->withHeader('Cache-Control','no-store');
        }
    }

}
