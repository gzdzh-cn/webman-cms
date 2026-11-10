<?php
declare(strict_types=1);
namespace app\cms\admin\controller;

use app\cms\service\SpecialNodeService;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use support\Request;
use support\Response;

class SpecialnodeController extends \plugin\admin\app\controller\Base
{
    protected function roleIds(): array { return (array)(admin()['roles']??[]); }
    protected function canWrite(string $action): bool
    {
        if(!in_array($action,['save','delete'],true))return false;$roles=$this->roleIds();if(!$roles)return false;$ids=[];
        foreach(Role::whereIn('id',$roles)->pluck('rules') as $rules)$ids=array_merge($ids,array_map('trim',explode(',',(string)$rules)));
        if(in_array('*',$ids,true))return true;
        return Rule::whereIn('id',array_filter($ids,'ctype_digit'))->where('key',\plugin\admin\app\controller\SpecialnodeController::class.'@'.$action)->exists();
    }
    private function parameters(Request $request,bool $post=false): array
    {
        $input=$post?$request->post():$request->get();$lang=$input['lang']??'cn';$aid=$input['aid']??'';
        if(!is_string($lang)||(!is_int($aid)&&!is_string($aid))||!ctype_digit((string)$aid)||(int)$aid<1||(int)$aid>2147483647)throw new \InvalidArgumentException('专题ID或语言格式错误');
        return [(int)$aid,$lang];
    }
    public function index(Request $request): Response { return \app\cms\admin\support\AdminView::raw('cms/specialnode')->withHeaders(['Cache-Control'=>'no-store','X-Content-Type-Options'=>'nosniff']); }
    public function topics(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        try{$lang=$request->get('lang','cn');if(!is_string($lang))throw new \InvalidArgumentException('语言格式错误');$r=new \app\cms\repository\CmsRepository();return $this->json(0,'ok',['topics'=>(new SpecialNodeService($r))->topics($lang),'languages'=>$r->table('language')->field('mark,title')->order('id')->select()->toArray()]);}catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function select(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        try{[$aid,$lang]=$this->parameters($request);$data=(new SpecialNodeService())->list($aid,$lang);$data['permissions']=['save'=>$this->canWrite('save'),'delete'=>$this->canWrite('delete')];return $this->json(0,'ok',$data);}catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function choices(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        try{[$aid,$lang]=$this->parameters($request);$q=$request->get('q','');if(!is_string($q))throw new \InvalidArgumentException('搜索词格式错误');return $this->json(0,'ok',(new SpecialNodeService())->choices($aid,$lang,$q));}catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    private function write(Request $request,string $action,callable $operation): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        if(!$this->canWrite($action))return $this->json(2,'无独立的专题节点写入权限')->withStatus(403);
        $origin=rtrim((string)$request->header('origin',''),'/');if($origin===''||strcasecmp($origin,\app\cms\CmsContext::current()->origin())!==0)return $this->json(1,'请从当前后台页面提交操作')->withStatus(403);
        try{[$aid,$lang]=$this->parameters($request,true);return $this->json(0,'操作成功',$operation(new SpecialNodeService(),$aid,$lang));}catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function save(Request $request): Response { return $this->write($request,'save',fn($service,$aid,$lang)=>$service->save($aid,$request->post(),$lang)); }
    public function delete(Request $request): Response
    {
        return $this->write($request,'delete',function($service,$aid,$lang)use($request){$ids=$request->post('ids',[]);if((string)$request->post('confirm','')!=='1'||!is_array($ids))throw new \InvalidArgumentException('请确认永久删除所选节点');$service->delete($aid,$ids,$lang);return [];});
    }
}
