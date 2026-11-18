<?php
namespace app\cms\admin\controller;

use app\cms\service\EnterpriseDataService;
use support\Request;
use support\Response;

abstract class CmsDataController extends \plugin\admin\app\controller\Base
{
    protected string $module;

    public function index(Request $request): Response
    {
        $service = new EnterpriseDataService();
        $lookup = [];
        if (in_array($this->module, ['download_file', 'media_file'], true)) {
            $repository = new \app\cms\repository\CmsRepository();
            $channel = $repository->table('channeltype')->where('nid', $this->module === 'download_file' ? 'download' : 'media')->value('id');
            $lookup['aid'] = (new \app\cms\service\ContentAccessService($repository))->filterArchiveQuery($repository->table('archives'))->where(['channel' => (int)$channel, 'is_del' => 0, 'lang' => \app\cms\CmsContext::current()->lang])->field('aid value,title label')->order('aid desc')->limit(1000)->select()->toArray();
        }
        $schema = $service->schema($this->module);
        $ui = \app\cms\admin\support\UiDefinition::forModule($this->module, $schema);
        $repository = new \app\cms\repository\CmsRepository();
        $language = \app\cms\CmsContext::current()->lang;
        foreach ([
            'ad'=>['pid','ad_position','id','title'], 'links'=>['groupid','links_group','id','group_name'],
            'nav_list'=>['parent_id','nav_list','nav_id','nav_name'], 'tagindex'=>['typeid','arctype','id','typename'],
        ] as $owner=>$definition) {
            if($this->module!==$owner)continue;
            [$key,$table,$pk,$label]=$definition;
            $query=$repository->table($table);$columns=$repository->columns($table);
            if($table==='arctype')(new \app\cms\service\ContentAccessService($repository))->filterCategoryQuery($query);
            if(isset($columns['lang']))$query->where('lang',$language);
            if(isset($columns['is_del']))$query->where('is_del',0);
            $lookup[$key]=$query->field($pk.' value,'.$label.' label')->order($pk)->select()->toArray();
            if(in_array($key,['parent_id','typeid'],true))array_unshift($lookup[$key],['value'=>0,'label'=>$key==='parent_id'?'顶级导航':'不指定栏目']);
        }
        if($this->module==='guestbook_attribute') {
            $lookup['typeid']=[];
            foreach([0=>['arctype','id','typename'],1=>['form','form_id','form_name']] as $kind=>[$table,$pk,$name]) {
                foreach($repository->table($table)->where('lang',$language)->field($pk.' value,'.$name.' label')->select()->toArray() as $row) {$row['kind']=$kind;$lookup['typeid'][]=$row;}
            }
        }
        if($this->module==='guestbook') {
            $lookup['typeid']=[];
            foreach([0=>['arctype','id','typename'],1=>['form','form_id','form_name']] as $kind=>[$table,$pk,$name]) {
                foreach($repository->table($table)->where('lang',$language)->field($pk.' value,'.$name.' label')->select()->toArray() as $row) {$row['kind']=$kind;$lookup['typeid'][]=$row;}
            }
        }
        if($this->module==='admin_log') {
            $lookup['admin_id']=\plugin\admin\app\model\Admin::query()->get(['id','username'])->map(static fn($row)=>['value'=>$row->id,'label'=>$row->username])->all();
        }
        $permissions=[];
        foreach(['select','save','delete'] as $action) $permissions[$action]=\plugin\admin\api\Auth::canAccess($request->controller,$action);
        $view=(string)$request->get('view','');$record=null;
        if($view!=='' && !in_array($view,['create','edit','detail'],true))return response('界面类型无效',400);
        if($view!=='') {
            if (!$permissions['select'] || ($view !== 'detail' && !$ui['readonly'] && !$permissions['save'])) {
                return \app\cms\admin\support\AdminView::raw('common/error/403')->withStatus(403);
            }
            if($view==='create'&&($ui['readonly']||$this->module==='guestbook'))return response('此模块不支持新增',403);
            if($view!=='create') {
                $value=$request->get('record');
                if(!is_scalar($value)||!preg_match('/^[1-9][0-9]{0,9}$/D',(string)$value))return response('记录ID格式错误',400);
                $record=$service->record($this->module,(int)$value);
                if(!$record)return response('记录不存在或不属于当前编辑语言',404);
            }
        }
        if ($this->module === 'sms_log') { unset($schema['code'], $schema['msg']); }
        if (in_array($this->module, ['sms_template', 'smtp_tpl'], true)) {
            $lookup['send_scene'] = $this->module === 'sms_template' ? [['value' => '7', 'label' => '留言手机验证'], ['value' => '11', 'label' => '留言通知']] : [['value' => '1', 'label' => '留言通知'], ['value' => '40', 'label' => '留言邮箱验证']];
            $lookup['is_open'] = [['value' => '1', 'label' => '启用'], ['value' => '0', 'label' => '停用']];
            if ($this->module === 'sms_template') $lookup['sms_type'] = [['value' => '1', 'label' => '阿里云'], ['value' => '2', 'label' => '腾讯云（原版接口）']];
        }
        if ($this->module === 'nav_list') {
            $repository = new \app\cms\repository\CmsRepository();
            $lookup['type_id'] = array_map(static fn($row) => ['value' => $row['id'], 'label' => '[' . $row['lang'] . '] ' . $row['typename']], (new \app\cms\service\ContentAccessService($repository))->filterCategoryQuery($repository->table('arctype'))->where(['is_del' => 0, 'status' => 1, 'lang' => \app\cms\CmsContext::current()->lang])->field('id,typename,lang')->order('lang,id')->select()->toArray());
            $lookup['position_id'] = $repository->table('nav_position')->where('is_del', 0)->field('position_id value,position_name label')->order('sort_order,position_id')->select()->toArray();
        }
        return \app\cms\admin\support\AdminView::raw('cms/data', ['ui'=>$ui,'permissions'=>$permissions,'editorView'=>$view,'editorRecord'=>$record,'module' => $this->module, 'title' => EnterpriseDataService::MODULES[$this->module], 'schema' => $schema, 'primary' => $service->primary($schema), 'lookup' => $lookup, 'initial' => array_intersect_key($request->get(), $service->schema($this->module)), 'endpoint' => preg_replace('#/index/?$#', '', $request->path())]);
    }

    public function tools(Request $request): Response
    {
        if (!in_array($this->module, ['tagindex','search_word','guestbook'], true)) return response('工具不可用',404);
        $permissions=[];foreach(['select','save'] as $action)$permissions[$action]=\plugin\admin\api\Auth::canAccess($request->controller,$action);
        return \app\cms\admin\support\AdminView::raw('cms/enterprise-tools',['module'=>$this->module,'permissions'=>$permissions,'endpoint'=>preg_replace('#/tools/?$#','',$request->path())]);
    }
    private function toolWrite(Request $request): ?Response
    {
        try { \app\cms\admin\support\LanguageAdminSecurity::assertWrite($request); } catch(\InvalidArgumentException $e) { return $this->json(2,$e->getMessage())->withStatus(403); }
        $origin=rtrim((string)$request->header('origin',''),'/');
        if($origin===''||strcasecmp($origin,\app\cms\CmsContext::current()->origin())!==0)return json(['code'=>2,'msg'=>'请从当前后台页面提交操作','data'=>[]])->withStatus(403);
        return null;
    }
    public function batchPreview(Request $request): Response
    {
        if($denied=$this->toolWrite($request))return $denied;
        try{return $this->json(0,'预览完成',(new \app\cms\service\EnterpriseToolsService())->preview($this->module,$request->post()));}
        catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function batchRun(Request $request): Response
    {
        if($denied=$this->toolWrite($request))return $denied;
        try{$r=(new \app\cms\service\EnterpriseToolsService())->run($this->module,(string)$request->post('token',''));return $this->json(0,$r['failed']?'部分项目未完成，可重试失败项目':'处理完成',$r);}
        catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function searchSettings(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        if($this->module!=='search_word')return response('Not Found',404);
        return $this->json(0,'ok',(new \app\cms\service\EnterpriseToolsService())->searchSettings());
    }
    public function saveSearchSettings(Request $request): Response
    {
        if($denied=$this->toolWrite($request))return $denied;
        if($this->module!=='search_word')return response('Not Found',404);
        try{return $this->json(0,'保存成功',(new \app\cms\service\EnterpriseToolsService())->saveSearchSettings($request->post()));}
        catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function relations(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        if($this->module!=='tagindex')return response('Not Found',404);
        try{return $this->json(0,'ok',(new \app\cms\service\EnterpriseToolsService())->relations((int)$request->get('tag_id',0),(string)$request->get('q','')));}
        catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function saveRelations(Request $request): Response
    {
        if($denied=$this->toolWrite($request))return $denied;
        if($this->module!=='tagindex')return response('Not Found',404);
        try{return $this->json(0,'保存成功',(new \app\cms\service\EnterpriseToolsService())->saveRelations((int)$request->post('tag_id',0),(array)$request->post('aids',[]),(string)$request->post('fingerprint',''),(array)$request->post('visible_ids',[])));}
        catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }
    public function export(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        try{$bytes=(new \app\cms\service\EnterpriseToolsService())->csv($this->module,$request->get());return response($bytes,200,['Content-Type'=>'text/csv; charset=utf-8','Content-Disposition'=>'attachment; filename="'.$this->module.'-'.date('Ymd-His').'.csv"','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);}
        catch(\InvalidArgumentException $e){return $this->json(1,$e->getMessage());}
    }

    public function select(Request $request): Response
    {
        return json((new EnterpriseDataService())->list($this->module, $request->get()));
    }

    public function save(Request $request): Response
    {
        if ($request->method() !== 'POST') { return response('Method Not Allowed', 405); }
        try { return $this->json(0, '保存成功', ['id' => (new EnterpriseDataService())->save($this->module, $request->post())]); }
        catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
    }

    public function delete(Request $request): Response
    {
        if ($request->method() !== 'POST') { return response('Method Not Allowed', 405); }
        try { (new EnterpriseDataService())->delete($this->module, (array)$request->post('ids', []), \app\cms\service\RecyclePolicyService::expected($request->post('recycle_enabled'))); return $this->json(0, '删除成功'); }
        catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
    }
}
