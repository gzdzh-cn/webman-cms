<?php
namespace app\cms\admin\controller;

use app\cms\service\LanguagePackService;
use app\cms\admin\support\LanguageAdminSecurity;
use support\Request;
use support\Response;

class LanguagepackController extends \plugin\admin\app\controller\Base
{
    public function index(Request $request): Response
    {
        try {
            $target=$request->get('target', \app\cms\CmsContext::current()->lang);
            if (!is_string($target) || $target==='') throw new \InvalidArgumentException('请选择语言包目标语言');
            (new \app\cms\service\LanguageAccessService())->assertLanguages([$target]);
            return \app\cms\admin\support\AdminView::raw('cms/languagepack', ['languageMark'=>$target]);
        } catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }
    private function target(Request $request): string
    {
        $target=$request->get('target',$request->get('lang',\app\cms\CmsContext::current()->lang));
        if (!is_string($target) || $target==='') throw new \InvalidArgumentException('请选择语言包目标语言');
        (new \app\cms\service\LanguageAccessService())->assertLanguages([$target]);
        return $target;
    }
    public function select(Request $request): Response
    {
        try {
            $input=$request->get();
            if (array_key_exists('target',$input)) $input['lang']=$this->target($request);
            return $this->json(0,'ok',(new LanguagePackService())->listing($input));
        }
        catch (\Throwable $error) { return $this->failure($error); }
    }
    private function write(Request $request,callable $operation): Response
    {
        if ($request->method()!=='POST') return response('Method Not Allowed',405);
        try { LanguageAdminSecurity::assertWrite($request); return $this->json(0,'操作完成',$operation(new LanguagePackService())); }
        catch (\Throwable $error) { return $this->failure($error); }
    }
    private function failure(\Throwable $error): Response
    {
        if($error instanceof \InvalidArgumentException)return $this->json(1,$error->getMessage());
        try{\support\Log::error('Local language package operation failed: '.$error->getMessage());}catch(\Throwable){}
        return $this->json(1,'语言包操作失败，请检查服务日志后重试；已有译文不会被导入覆盖');
    }
    public function save(Request $request): Response { return $this->write($request,fn($service)=>['id'=>$service->save($request->post())]); }
    public function batch(Request $request): Response { return $this->write($request,fn($service)=>$service->batch((string)$request->post('content',''))); }
    public function delete(Request $request): Response
    {
        return $this->write($request,function($service)use($request){
            if((string)$request->post('confirm','')!=='1')throw new \InvalidArgumentException('请确认删除该变量的全部语言值');
            $service->delete((array)$request->post('ids',[]),(string)$request->post('lang',\app\cms\CmsContext::current()->lang));return [];
        });
    }
    public function remoteCheck(Request $request): Response { return $this->write($request,fn($service)=>$service->check()); }
    public function remoteSingle(Request $request): Response { return $this->remoteCheck($request); }
    public function remoteList(Request $request): Response { return $this->remoteCheck($request); }
    public function preview(Request $request): Response
    {
        return $this->write($request,fn($service)=>$service->preview((string)$request->post('package',''),(string)$request->post('lang',\app\cms\CmsContext::current()->lang)));
    }
    public function import(Request $request): Response
    {
        return $this->write($request,function($service)use($request){
            if((string)$request->post('confirm','')!=='1')throw new \InvalidArgumentException('请先审阅语言包差异再确认导入');
            return $service->commit((string)$request->post('plan_token',''),(string)$request->post('package',''),(string)$request->post('lang',\app\cms\CmsContext::current()->lang));
        });
    }
    public function export(Request $request): Response
    {
        try { return $this->json(0,'ok',(new LanguagePackService())->export($this->target($request))); }
        catch (\Throwable $error) { return $this->failure($error); }
    }
    public function supplement(Request $request): Response { return $this->write($request,fn($service)=>$service->supplement((string)$request->post('lang',\app\cms\CmsContext::current()->lang))); }
    public function rebuild(Request $request): Response { return $this->write($request,fn($service)=>$service->rebuild((string)$request->post('lang',\app\cms\CmsContext::current()->lang))); }
}
