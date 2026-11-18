<?php
namespace app\cms\admin\controller;

use app\cms\service\CitysiteService;
use support\Request;
use support\Response;

class CitysiteController extends \plugin\admin\app\controller\Base
{
    protected $noNeedAuth = ['migrationPreview', 'migrateSettings'];
    public function index(Request $request): Response
    {
        $permissions=[];
        foreach(['select','settings','save','delete','saveSettings'] as $action){
            $code=0;$message='';
            $permissions[$action]=\plugin\admin\api\Auth::canAccess(\plugin\admin\app\controller\CitysiteController::class,$action,$code,$message);
        }
        $view=$request->get('view','');
        if(!is_scalar($view))return response('界面类型无效',400);
        $view=(string)$view;
        if($view!==''&&!in_array($view,['create','edit'],true))return response('界面类型无效',400);
        if($view!==''&&(!$permissions['select']||!$permissions['save'])){
            return \app\cms\admin\support\AdminView::raw('common/error/403')->withStatus(403);
        }
        if($view==='edit'){
            $record=$request->get('record');
            if(!is_scalar($record)||!preg_match('/^[1-9][0-9]{0,9}$/D',(string)$record))return response('记录ID格式错误',400);
        }
        return \app\cms\admin\support\AdminView::raw('cms/citysite',['cityPermissions'=>$permissions]);
    }
    public function select(Request $request): Response { return json((new CitysiteService())->listing(trim((string)$request->get('q','')))); }
    public function settings(Request $request): Response
    {
        $data=(new CitysiteService())->settings();
        $plan=(new \app\cms\service\CitysiteSettingsMigration())->preview();
        $data['_policy']=(new \app\cms\service\SiteModulePolicy())->status()+['migration_pending'=>$plan['copy_count']>0||count($plan['conflicts'])>0];
        $data['_csrf']=\app\cms\admin\support\LanguageAdminSecurity::token($request);
        return $this->json(0,'ok',$data);
    }
    public function migrationPreview(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        if(!$this->configurationPermission('settings'))return $this->json(2,'无城市设置读取权限')->withStatus(403);
        try{$plan=(new \app\cms\service\CitysiteSettingsMigration())->preview();unset($plan['copies']);return $this->json(0,'ok',$plan);}
        catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
    public function migrateSettings(Request $request): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        if(!$this->configurationPermission('settings')||!\plugin\admin\api\CmsWritePolicy::allowed(\plugin\admin\app\controller\CitysiteController::class,'saveSettings',(array)(admin()['roles']??[])))return $this->json(2,'无独立的城市设置保存权限')->withStatus(403);
        try{\app\cms\admin\support\LanguageAdminSecurity::assertWrite($request);return $this->json(0,'城市配置迁移完成',(new \app\cms\service\CitysiteSettingsMigration())->apply((string)$request->post('fingerprint','')));}
        catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
    private function configurationPermission(string $action): bool
    {
        return \plugin\admin\api\Auth::canAccess(\plugin\admin\app\controller\CitysiteController::class,$action);
    }
    public function save(Request $request): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        try{return $this->json(0,'保存成功',['id'=>(new CitysiteService())->save($request->post())]);}catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
    public function saveSettings(Request $request): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        try{(new CitysiteService())->saveSettings($request->post());return $this->json(0,'保存成功');}catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
    public function delete(Request $request): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        try{(new CitysiteService())->delete((array)$request->post('ids',[]));return $this->json(0,'删除成功');}catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
}
