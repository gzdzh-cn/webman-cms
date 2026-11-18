<?php
namespace app\cms\admin\controller;

use app\cms\admin\support\LanguageAdminContext;
use app\cms\admin\support\LanguageAdminSecurity;
use app\cms\service\LanguagePolicy;
use app\cms\service\LanguageService;
use support\Request;
use support\Response;

class LanguageController extends \plugin\admin\app\controller\CmsDataController
{
    protected string $module='language';
    protected $noNeedAuth=['state','switch'];
    public function index(Request $request): Response { return \app\cms\admin\support\AdminView::raw('cms/language'); }
    public function select(Request $request): Response
    {
        $allowed=(new \app\cms\service\LanguageAccessService())->allowedLanguages();
        $query=(new \app\cms\repository\CmsRepository())->table('language')->whereIn('mark',$allowed);
        $search=trim((string)$request->get('q',''));
        if($search!=='')$query->whereLike('title|mark','%'.addcslashes($search,'%_').'%');
        $count=(clone $query)->count();
        return json(['code'=>0,'msg'=>'ok','count'=>$count,'data'=>$query->order('sort_order asc,id asc')->page(max(1,(int)$request->get('page',1)),min(100,max(1,(int)$request->get('limit',20))))->select()->toArray()]);
    }
    public function state(Request $request): Response
    {
        try {
            $state=LanguageAdminContext::state($request);$permissions=[];
            foreach(['save','delete','sync','reset','associate','settings'] as $action){$code=0;$msg='';$permissions[$action]=\plugin\admin\api\Auth::canAccess(\plugin\admin\app\controller\LanguageController::class,$action,$code,$msg);}
            $packCode=0;$packMessage='';$permissions['pack']=\plugin\admin\api\Auth::canAccess(\plugin\admin\app\controller\LanguagepackController::class,'index',$packCode,$packMessage);
            $policy=new LanguagePolicy();
            $allLanguages=array_column($policy->all(),'mark');
            $hasGlobalScope=!array_diff($allLanguages,array_column($state['languages'],'mark'));
            $permissions['settings']=$permissions['settings']&&$hasGlobalScope;
            return $this->json(0,'ok',['enabled'=>$state['enabled'],'main'=>$state['main'],'edit'=>$state['lang'],'revision'=>$state['revision'],'languages'=>$state['languages'],'can_manage'=>$permissions['save']&&$hasGlobalScope,'permissions'=>$permissions,'settings'=>$policy->settings(),'csrf'=>LanguageAdminSecurity::token($request)]);
        }catch(\Throwable $error){return $this->failure($error);}
    }
    private function write(Request $request,callable $operation): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        try{LanguageAdminSecurity::assertWrite($request);$result=$operation();return $this->json(0,'操作完成',is_array($result)?$result:[]);}
        catch(\Throwable $error){return $this->failure($error);}
    }
    private function failure(\Throwable $error): Response
    {
        if($error instanceof \InvalidArgumentException)return $this->json(1,$error->getMessage());
        try{\support\Log::error('Language operation failed: '.$error->getMessage());}catch(\Throwable){}
        return $this->json(1,'操作失败，请查看任务记录或服务日志后重试');
    }
    public function toggle(Request $request):Response {return $this->write($request,function()use($request){$value=$request->post('enabled');if(!in_array((string)$value,['0','1'],true))throw new \InvalidArgumentException('开关值无效');(new LanguagePolicy())->setEnabled((string)$value==='1');return ['enabled'=>(new LanguagePolicy())->enabled()];});}
    public function switch(Request $request):Response {return $this->write($request,fn()=>LanguageAdminContext::switchTo($request,(string)$request->post('lang','')));}
    public function settings(Request $request):Response {return $request->method()==='GET'?$this->json(0,'ok',(new LanguagePolicy())->settings()):$this->write($request,fn()=>(new LanguagePolicy())->saveSettings($request->post()));}
    public function save(Request $request):Response {return $this->write($request,fn()=>['id'=>(new LanguageService(null,\app\cms\CmsContext::current()->lang))->save($request->post())]);}
    public function delete(Request $request):Response {return $this->write($request,function()use($request){$roles=admin()['roles']??[];if(!\plugin\admin\api\CmsWritePolicy::allowed(\plugin\admin\app\controller\LanguageController::class,'delete',$roles))throw new \InvalidArgumentException('无独立的删除语言权限');return (new \app\cms\service\LanguageDeletionService())->delete((array)$request->post('ids',[]),(string)$request->post('confirm_delete','')==='1');});}
    public function preview(Request $request):Response {return $this->write($request,function()use($request){$mode=(string)$request->post('mode','new');$action=$mode==='all'?'reset':'sync';$code=0;$msg='';if(!\plugin\admin\api\Auth::canAccess(\plugin\admin\app\controller\LanguageController::class,$action,$code,$msg))throw new \InvalidArgumentException('无此同步权限');return (new LanguageService())->preview((int)$request->post('id',0),$mode);});}
    public function sync(Request $request):Response {return $this->write($request,fn()=>(new LanguageService())->execute((string)$request->post('plan_token',''),'new',(int)$request->post('id',0)));}
    public function reset(Request $request):Response {return $this->write($request,function()use($request){if((string)$request->post('confirm','')!=='1')throw new \InvalidArgumentException('请核对完整清单并确认全部同步');return (new LanguageService())->execute((string)$request->post('plan_token',''),'all',(int)$request->post('id',0));});}
    public function jobs(Request $request):Response
    {
        try{$token=(string)$request->get('token','');return $this->json(0,'ok',$token?(new LanguageService())->task($token):['jobs'=>(new LanguageService())->history()]);}catch(\Throwable $error){return $this->failure($error);}
    }
    public function associations(Request $request):Response
    {
        try{return $this->json(0,'ok',(new \app\cms\service\LanguageAssociationService())->listing((string)$request->get('target','')));}catch(\Throwable $error){return $this->failure($error);}
    }
    public function associate(Request $request):Response {return $this->write($request,function()use($request){$service=new \app\cms\service\LanguageAssociationService();$source=(int)$request->post('source_id',0);$target=(string)$request->post('target','');if($request->post('remove')){$service->remove($source,$target);return []; }return $service->save($source,(int)$request->post('target_id',0),$target);});}
    public function restore(Request $request):Response {return $this->write($request,function()use($request){if((string)$request->post('confirm','')!=='1')throw new \InvalidArgumentException('请确认按任务前镜像恢复目标内容');return (new LanguageService())->restore((string)$request->post('plan_token',''));});}
}
