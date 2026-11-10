<?php
declare(strict_types=1);
namespace app\cms\admin\controller;
use app\cms\maintenance\DatabaseBackup;
use app\cms\service\ContentAccessService;
use app\cms\service\LanguageAccessService;
use app\cms\service\LanguagePolicy;
use support\Request;
use support\Response;
class DatabasebackupController extends \plugin\admin\app\controller\Base
{
    protected $noNeedAuth=['index','select','create','preview','restore','status','tasks','cleanupPreview','cleanup'];
    private function guard(Request $request,bool $write=false):?Response
    {
        $roles=(array)(admin()['roles']??[]);$super=false;foreach(\plugin\admin\app\model\Role::whereIn('id',$roles)->pluck('rules')as$rules)if(in_array('*',explode(',',(string)$rules),true))$super=true;
        if(!$super||!(new ContentAccessService())->unrestricted())return response('数据库备份还原仅限拥有全部数据范围的超级管理员',403);
        (new LanguageAccessService())->assertLanguages(array_column((new LanguagePolicy())->all(),'mark'));
        if($request->method()!==($write?'POST':'GET'))return response('Method Not Allowed',405);
        if($write){try{\app\cms\admin\support\LanguageAdminSecurity::assertWrite($request);}catch(\InvalidArgumentException $e){return $this->json(2,$e->getMessage())->withStatus(403);}$origin=rtrim((string)$request->header('origin',''),'/');if($origin===''||strcasecmp($origin,\app\cms\CmsContext::current()->origin())!==0)return response('请从当前后台页面提交操作',403);}return null;
    }
    public function index(Request $request):Response {if($e=$this->guard($request))return$e;return \app\cms\admin\support\AdminView::raw('cms/database-backup');}
    public function select(Request $request):Response {if($e=$this->guard($request))return$e;return$this->invoke(fn()=>(new DatabaseBackup())->backups());}
    public function create(Request $request):Response {if($e=$this->guard($request,true))return$e;return$this->invoke(fn()=>(new DatabaseBackup())->create());}
    public function preview(Request $request):Response {if($e=$this->guard($request,true))return$e;return$this->invoke(fn()=>(new DatabaseBackup())->preview((string)$request->post('id','')));}
    public function restore(Request $request):Response {if($e=$this->guard($request,true))return$e;if($request->post('confirm')!=='restore')return$this->json(1,'请确认预检中列出的全部目标表');return$this->invoke(fn()=>(new DatabaseBackup())->restore((string)$request->post('token','')));}
    public function status(Request $request):Response {if($e=$this->guard($request))return$e;return$this->invoke(fn()=>(new DatabaseBackup())->status((string)$request->get('token','')));}
    public function tasks(Request $request):Response {if($e=$this->guard($request))return$e;return$this->invoke(fn()=>(new DatabaseBackup())->tasks());}
    public function cleanupPreview(Request $request):Response {if($e=$this->guard($request,true))return$e;return$this->invoke(fn()=>(new DatabaseBackup())->cleanupPreview((string)$request->post('token','')));}
    public function cleanup(Request $request):Response {if($e=$this->guard($request,true))return$e;if($request->post('confirm')!=='cleanup')return$this->json(1,'请确认所列恢复对象将被永久清理，原任务将不能回退');return$this->invoke(fn()=>(new DatabaseBackup())->cleanup((string)$request->post('token',''),(string)$request->post('confirmation','')));}
    private function invoke(callable $operation):Response {try{return$this->json(0,'处理完成',$operation())->withHeader('Cache-Control','no-store');}catch(\InvalidArgumentException|\RuntimeException$e){return$this->json(1,$e->getMessage())->withHeader('Cache-Control','no-store');}}
}
