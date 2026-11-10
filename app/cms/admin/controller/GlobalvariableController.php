<?php
namespace app\cms\admin\controller;

use app\cms\CmsContext;
use app\cms\service\CustomVariableGroupService;
use app\cms\service\EnterpriseDataService;
use plugin\admin\api\Auth;
use plugin\admin\api\CmsWritePolicy;
use support\Request;
use support\Response;

class GlobalvariableController extends \plugin\admin\app\controller\CmsDataController
{
    protected string $module = 'config_attribute';
    protected $noNeedAuth=['groups','groupSave','groupDelete'];
    protected function groupsService():CustomVariableGroupService{return new CustomVariableGroupService();}
    protected function variableService():EnterpriseDataService{return new EnterpriseDataService();}
    protected function canReadVariables():bool{return Auth::canAccess(\plugin\admin\app\controller\GlobalvariableController::class,'index');}
    protected function canWriteVariables(string $action):bool{return CmsWritePolicy::allowed(\plugin\admin\app\controller\GlobalvariableController::class,$action,(array)(admin()['roles']??[]));}
    protected function canUploadVariables():bool{return Auth::canAccess(\plugin\admin\app\controller\UploadController::class,'file');}
    public function index(Request $request):Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);if(!$this->canReadVariables())return response('Forbidden',403);return \app\cms\admin\support\AdminView::raw('cms/globalvariable')->withHeaders(['Cache-Control'=>'no-store','X-Content-Type-Options'=>'nosniff']);
    }
    public function groups(Request $request):Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);if(!$this->canReadVariables())return $this->json(2,'无自定义变量读取权限')->withStatus(403);
        try{$data=$this->groupsService()->read($request->get());$data['permissions']=['save'=>$this->canWriteVariables('save'),'delete'=>$this->canWriteVariables('delete'),'upload'=>$this->canUploadVariables()];return $this->json(0,'ok',$data)->withHeaders(['Cache-Control'=>'no-store']);}catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
    }
    public function save(Request $request):Response
    {
        if($response=$this->authorize($request,'save'))return $response;
        try{return $this->json(0,'保存成功',['id'=>$this->variableService()->save('config_attribute',$request->post())]);}catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
    }
    public function delete(Request $request):Response
    {
        if($response=$this->authorize($request,'delete'))return $response;
        try{$this->variableService()->delete('config_attribute',(array)$request->post('ids',[]));return $this->json(0,'回收成功');}catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
    }
    public function groupSave(Request $request):Response
    {
        if($response=$this->authorize($request,'save'))return $response;
        try{return $this->json(0,'分组已保存',['id'=>$this->groupsService()->save($request->post())]);}catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
    }
    public function groupDelete(Request $request):Response
    {
        if($response=$this->authorize($request,'delete'))return $response;
        try{$this->groupsService()->delete($request->post());return $this->json(0,'分组已删除');}catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
    }
    private function authorize(Request $request,string $action):?Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);if(!$this->canReadVariables()||!$this->canWriteVariables($action))return $this->json(2,'无独立的自定义变量操作权限')->withStatus(403);$origin=rtrim((string)$request->header('origin',''),'/');if($origin===''||strcasecmp($origin,CmsContext::current()->origin())!==0)return $this->json(1,'请从当前后台页面提交操作')->withStatus(403);return null;
    }
}
