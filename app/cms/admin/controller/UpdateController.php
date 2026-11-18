<?php
declare(strict_types=1);
namespace app\cms\admin\controller;
use app\cms\admin\support\{Auth,LanguageAdminSecurity};
use app\cms\service\AdminUpdateService;
use support\{Request,Response};
class UpdateController
{
    protected $noNeedAuth=['check','start','status'];
    public function check(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        try {
            $force=$request->get('force','0');if(!is_string($force)||!in_array($force,['0','1'],true))throw new \InvalidArgumentException('重新检查参数无效');
            $info=(new AdminUpdateService())->cachedCheck($force==='1');
            $allowed=Auth::isSuperAdmin();
            $info['allowed']=$allowed;$info['csrf']=$allowed?LanguageAdminSecurity::token($request):null;
            // Nonce is unique per successful login, independent of page refresh.
            $info['login_key']=hash('sha256',(string)(admin()['session_nonce']??$request->session()->getId()));
            return json(['code'=>0,'data'=>$info])->withHeader('Cache-Control','no-store');
        }catch(\Throwable){return json(['code'=>1,'msg'=>'检查更新暂时不可用，请稍后重试','data'=>[]])->withHeader('Cache-Control','no-store');}
    }
    public function start(Request $request): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        if(!Auth::isSuperAdmin())return json(['code'=>2,'msg'=>'仅超级管理员可升级','data'=>[]])->withStatus(403);
        try{
            LanguageAdminSecurity::assertWrite($request);$id=$request->post('release_id','');
            if(!is_string($id)||!preg_match('/^[0-9]+:[a-f0-9]{64}$/D',$id))throw new \InvalidArgumentException('正式发布身份无效，请重新检查');
            return json(['code'=>0,'data'=>(new AdminUpdateService())->start($id,(int)admin_id())])->withHeader('Cache-Control','no-store');
        }catch(\InvalidArgumentException|\RuntimeException $e){return json(['code'=>1,'msg'=>$e->getMessage(),'data'=>[]])->withHeader('Cache-Control','no-store');}
        catch(\Throwable){return json(['code'=>1,'msg'=>'启动升级失败，请检查维护日志','data'=>[]])->withHeader('Cache-Control','no-store');}
    }
    public function status(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        try{$id=$request->get('id','');if(!is_string($id))throw new \InvalidArgumentException('任务无效');return json(['code'=>0,'data'=>(new AdminUpdateService())->status($id)])->withHeader('Cache-Control','no-store');}
        catch(\Throwable){return json(['code'=>1,'msg'=>'升级任务状态暂时不可用','data'=>[]])->withHeader('Cache-Control','no-store');}
    }
}
