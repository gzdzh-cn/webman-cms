<?php
declare(strict_types=1);
namespace app\cms\admin\controller;

use app\cms\service\ProductParameterService;
use plugin\admin\api\CmsWritePolicy;
use support\Request;
use support\Response;

class ProductparameterController extends \plugin\admin\app\controller\Base
{
    private const WRITES=['saveDefinition','saveGroup','saveProduct','recycle','restore','purge'];
    protected function roleIds():array {return (array)(admin()['roles']??[]);}
    protected function canWrite(string $action):bool
    {
        return in_array($action,self::WRITES,true)&&CmsWritePolicy::allowed(\plugin\admin\app\controller\ProductparameterController::class,$action,$this->roleIds());
    }
    private function language(array $input):string {$lang=$input['lang']??'cn';if(!is_string($lang))throw new \InvalidArgumentException('语言格式错误');return $lang;}
    private function id(mixed $value,bool $zero=false):int {if((!is_int($value)&&!is_string($value))||!ctype_digit((string)$value)||(int)$value<($zero?0:1)||(int)$value>2147483647)throw new \InvalidArgumentException('参数ID格式错误');return (int)$value;}
    public function index(Request $request):Response {return \app\cms\admin\support\AdminView::raw('cms/productparameter')->withHeaders(['Cache-Control'=>'no-store','X-Content-Type-Options'=>'nosniff']);}
    public function metadata(Request $request):Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);try{$input=$request->get();$service=new ProductParameterService();if(isset($input['impact_action'])){$action=$input['impact_action'];$kind=$input['kind']??'';$ids=$input['ids']??[];if(!is_string($action)||!in_array($action,['recycle','restore','purge'],true)||!is_string($kind)||!is_array($ids))throw new \InvalidArgumentException('参数影响预览格式错误');if(!$this->canWrite($action))return $this->json(2,'无此参数操作权限')->withStatus(403);return $this->json(0,'ok',$service->removalPlan($kind,$ids,$this->language($input),$action));}$data=$service->metadata($this->language($input));$data['permissions']=[];foreach(self::WRITES as $action)$data['permissions'][$action]=$this->canWrite($action);return $this->json(0,'ok',$data);}catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function product(Request $request):Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);try{return $this->json(0,'ok',(new ProductParameterService())->product($this->id($request->get('aid','')),$this->language($request->get())));}catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    private function write(Request $request,string $action,callable $operation):Response
    {
        if($denied=CmsWritePolicy::authorize($request,\plugin\admin\app\controller\ProductparameterController::class,$action,$this->roleIds()))return $denied;try{return $this->json(0,'操作成功',$operation(new ProductParameterService(),$request->post()));}catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function saveDefinition(Request $request):Response {return $this->write($request,'saveDefinition',function($s,$input){$kind=$input['kind']??'';if(!is_string($kind))throw new \InvalidArgumentException('参数类型错误');$id=$this->id($input['attr_id']??0,true);return ['attr_id'=>$s->saveDefinition($kind,$input,$id?:null)];});}
    public function saveGroup(Request $request):Response {return $this->write($request,'saveGroup',function($s,$input){if(array_key_exists('definitions',$input)){$definitions=is_string($input['definitions'])?json_decode($input['definitions'],true):$input['definitions'];if(!is_array($definitions))throw new \InvalidArgumentException('初始参数定义格式错误');if($definitions&&!$this->canWrite('saveDefinition'))throw new \InvalidArgumentException('无独立的参数定义保存权限');}$id=$this->id($input['list_id']??0,true);return ['list_id'=>$s->saveGroup($input,$id?:null)];});}
    public function saveProduct(Request $request):Response {return $this->write($request,'saveProduct',fn($s,$input)=>$s->saveProduct($this->id($input['aid']??''),$input,$this->language($input)));}
    private function removal(Request $request,string $action):Response
    {
        return $this->write($request,$action,function($s,$input)use($action){$kind=$input['kind']??'';$ids=$input['ids']??[];if(!is_string($kind)||!is_array($ids))throw new \InvalidArgumentException('参数操作格式错误');$lang=$this->language($input);if($action==='purge'&&(string)($input['confirm']??'')!=='1')throw new \InvalidArgumentException('请确认永久删除参数及其值');if(array_key_exists('plan_fingerprint',$input)){if(!is_string($input['plan_fingerprint']))throw new \InvalidArgumentException('参数影响预览指纹格式错误');$s->executeRemovalPlan($kind,$ids,$lang,$action,$input['plan_fingerprint']);}elseif($action==='purge')$s->purge($kind,$ids,$lang);else$s->recycle($kind,$ids,$lang,$action==='restore');return [];});
    }
    public function recycle(Request $request):Response {return $this->removal($request,'recycle');}
    public function restore(Request $request):Response {return $this->removal($request,'restore');}
    public function purge(Request $request):Response {return $this->removal($request,'purge');}
}
