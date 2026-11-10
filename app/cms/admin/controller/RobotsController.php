<?php
declare(strict_types=1);
namespace app\cms\admin\controller;

use app\cms\admin\support\LanguageAdminSecurity;
use app\cms\service\RobotsService;
use plugin\admin\api\Auth;
use plugin\admin\api\CmsWritePolicy;
use support\Request;
use support\Response;

class RobotsController extends \plugin\admin\app\controller\Base
{
    protected $noNeedAuth=['index','select','save','revisions','restore'];
    private function canRead(): bool {return Auth::canAccess(\plugin\admin\app\controller\SeoController::class,'index');}
    private function canSave(): bool
    {
        if(!CmsWritePolicy::allowed(\plugin\admin\app\controller\SeoController::class,'save',(array)(admin()['roles']??[])))return false;
        $all=array_column((new \app\cms\service\LanguagePolicy())->all(),'mark');
        return !array_diff($all,(new \app\cms\service\LanguageAccessService())->allowedLanguages());
    }
    public function index(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        if(!$this->canRead())return response('Forbidden',403);
        return \app\cms\admin\support\AdminView::raw('cms/robots')->withHeaders(['Cache-Control'=>'no-store']);
    }
    public function select(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        if(!$this->canRead())return $this->json(2,'无SEO设置读取权限')->withStatus(403);
        try{return $this->json(0,'ok',(new RobotsService())->read()+['can_save'=>$this->canSave(),'csrf'=>LanguageAdminSecurity::token($request)]);}
        catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
    public function save(Request $request): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        if(!$this->canRead()||!$this->canSave())return $this->json(2,'无独立的SEO设置保存权限')->withStatus(403);
        try{LanguageAdminSecurity::assertWrite($request);return $this->json(0,'robots.txt 已保存',(new RobotsService())->save($request->post()));}
        catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
    public function revisions(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        if(!$this->canRead())return $this->json(2,'无SEO设置读取权限')->withStatus(403);
        try{return $this->json(0,'ok',(new RobotsService())->revisions());}
        catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
    public function restore(Request $request): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        if(!$this->canRead()||!$this->canSave())return $this->json(2,'无独立的SEO设置保存权限或完整语言范围')->withStatus(403);
        try{LanguageAdminSecurity::assertWrite($request);return $this->json(0,'已恢复前版本',(new RobotsService())->restore($request->post()));}
        catch(\InvalidArgumentException|\RuntimeException $error){return $this->json(1,$error->getMessage());}
    }
}
