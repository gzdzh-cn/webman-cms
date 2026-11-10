<?php
namespace app\cms\admin\controller;

use app\cms\CmsContext;
use app\cms\service\BasicSettingsService;
use plugin\admin\api\Auth;
use plugin\admin\api\CmsWritePolicy;
use support\Request;
use support\Response;

class WebsiteconfigController extends \plugin\admin\app\controller\CmsDataController
{
    protected string $module = 'config';
    protected $noNeedAuth = ['settings','configure'];
    protected function settingsService(): BasicSettingsService { return new BasicSettingsService(); }
    protected function canReadSettings(): bool { return Auth::canAccess(\plugin\admin\app\controller\WebsiteconfigController::class,'index'); }
    protected function canConfigure(): bool { return CmsWritePolicy::allowed(\plugin\admin\app\controller\WebsiteconfigController::class,'save',(array)(admin()['roles']??[])); }

    public function index(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        if(!$this->canReadSettings())return response('Forbidden',403);
        return \app\cms\admin\support\AdminView::raw('cms/websiteconfig')->withHeaders(['Cache-Control'=>'no-store','X-Content-Type-Options'=>'nosniff']);
    }
    public function settings(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        if(!$this->canReadSettings())return $this->settingsResult(2,'无网站设置读取权限')->withStatus(403);
        try{return $this->settingsResult(0,'ok',$this->settingsPresentation($this->settingsService()->read($request->get())));}
        catch(\InvalidArgumentException|\RuntimeException $error){return $this->settingsResult(1,$error->getMessage());}
    }
    public function configure(Request $request): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        if(!$this->canReadSettings()||!$this->canConfigure())return $this->settingsResult(2,'无独立的网站设置保存权限')->withStatus(403);
        $origin=rtrim((string)$request->header('origin',''),'/');if($origin===''||strcasecmp($origin,CmsContext::current()->origin())!==0)return $this->settingsResult(1,'请从当前后台页面提交操作')->withStatus(403);
        try{return $this->settingsResult(0,'网站设置已保存',$this->settingsPresentation($this->settingsService()->save($request->post())));}
        catch(\InvalidArgumentException|\RuntimeException $error){return $this->settingsResult(1,$error->getMessage());}
    }
    protected function settingsLinks(): array
    {
        $links=[];foreach([[\plugin\admin\app\controller\FilemanagerController::class,'模板管理','/app/admin/filemanager/index'],[\plugin\admin\app\controller\SmsconfigController::class,'短信配置','/app/admin/smsconfig/index'],[\plugin\admin\app\controller\EmailtemplateController::class,'邮件模板','/app/admin/emailtemplate/index'],[\plugin\admin\app\controller\SmstemplateController::class,'短信模板','/app/admin/smstemplate/index']] as[$class,$title,$href])if(Auth::canAccess($class,'index'))$links[]=['title'=>$title,'href'=>$href];return $links;
    }
    protected function canUploadSettings():bool { return Auth::canAccess(\plugin\admin\app\controller\UploadController::class,'file'); }
    private function settingsPresentation(array $data):array { $data['permissions']=['save'=>$this->canConfigure(),'upload'=>$this->canUploadSettings()];$data['links']=$this->settingsLinks();return $data; }
    private function settingsResult(int $code,string $message,array $data=[]):Response { return $this->json($code,$message,$data)->withHeaders(['Cache-Control'=>'no-store','X-Content-Type-Options'=>'nosniff']); }
}
