<?php
declare(strict_types=1);
namespace app\cms\admin\controller;

use app\cms\service\FaqService;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use support\Request;
use support\Response;

class FaqController extends \plugin\admin\app\controller\Base
{
    protected function roleIds(): array { return (array)(admin()['roles'] ?? []); }
    protected function canWrite(string $action): bool
    {
        if (!in_array($action,['save','delete'],true)) return false;
        $roles=$this->roleIds(); if (!$roles) return false; $ids=[];
        foreach (Role::whereIn('id',$roles)->pluck('rules') as $rules) $ids=array_merge($ids,array_map('trim',explode(',',(string)$rules)));
        if (in_array('*',$ids,true)) return true;
        return Rule::whereIn('id',array_filter($ids,'ctype_digit'))->where('key',\plugin\admin\app\controller\FaqController::class.'@'.$action)->exists();
    }
    public function index(Request $request): Response { return \app\cms\admin\support\AdminView::raw('cms/faq')->withHeaders(['Cache-Control'=>'no-store','X-Content-Type-Options'=>'nosniff']); }
    public function select(Request $request): Response
    {
        if ($request->method()!=='GET') return response('Method Not Allowed',405);
        try { $result=(new FaqService())->list($request->get());$result['permissions']=['save'=>$this->canWrite('save'),'delete'=>$this->canWrite('delete')];return json($result); }
        catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }
    public function detail(Request $request): Response
    {
        if ($request->method()!=='GET') return response('Method Not Allowed',405);
        try {
            $id=$request->get('group_id','');$lang=$request->get('lang','cn');
            if (!is_scalar($id)||!ctype_digit((string)$id)||(int)$id<1||!is_string($lang)) throw new \InvalidArgumentException('分组ID或语言格式错误');
            return $this->json(0,'ok',(new FaqService())->detail((int)$id,$lang));
        } catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }
    private function write(Request $request,string $action,callable $operation): Response
    {
        if ($request->method()!=='POST') return response('Method Not Allowed',405);
        if (!$this->canWrite($action)) return $this->json(2,'无独立的FAQ写入权限')->withStatus(403);
        $origin=rtrim((string)$request->header('origin',''),'/');
        if ($origin===''||strcasecmp($origin,\app\cms\CmsContext::current()->origin())!==0) return $this->json(1,'请从当前后台页面提交操作')->withStatus(403);
        try { return $this->json(0,'操作成功',$operation(new FaqService())); }
        catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }
    public function save(Request $request): Response
    {
        return $this->write($request,'save',function($service)use($request){$input=$request->post();$id=$input['group_id']??0;if(!is_scalar($id)||!ctype_digit((string)$id)||(int)$id>2147483647)throw new \InvalidArgumentException('分组ID格式错误');return ['group_id'=>$service->save($input,(int)$id?:null)];});
    }
    public function delete(Request $request): Response
    {
        return $this->write($request,'delete',function($service)use($request){if((string)$request->post('confirm','')!=='1')throw new \InvalidArgumentException('请确认永久删除分组及全部问答');$lang=$request->post('lang','cn');$ids=$request->post('ids',[]);if(!is_string($lang)||!is_array($ids))throw new \InvalidArgumentException('删除参数格式错误');$service->deleteGroups($ids,$lang);return [];});
    }
}
