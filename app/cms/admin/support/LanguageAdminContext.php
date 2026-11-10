<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use app\cms\CmsContext;
use app\cms\repository\CmsRepository;
use app\cms\service\LanguageAccessService;
use app\cms\service\LanguagePolicy;
use support\Request;

final class LanguageAdminContext
{
    public static function state(Request $request): array
    {
        $policy=new LanguagePolicy();$access=new LanguageAccessService();$allowed=$access->allowedLanguages();$main=$policy->main();
        $valid=$policy->enabled()?$allowed:array_values(array_intersect($allowed,[$main]));
        $session=$request->session();$state=$session->get('cms_edit_language',[]);$fingerprint=$policy->fingerprint();
        if(!is_array($state)||!in_array($state['lang']??'',$valid,true)||($state['policy']??'')!==$fingerprint){
            $selected=in_array($state['lang']??'',$valid,true)?$state['lang']:(in_array($main,$valid,true)?$main:($valid[0]??''));
            $state=['lang'=>$selected,'revision'=>bin2hex(random_bytes(16)),'policy'=>$fingerprint];$session->set('cms_edit_language',$state);
        }
        return $state+['edit'=>$state['lang'],'main'=>$main,'enabled'=>$policy->enabled(),'languages'=>array_values(array_filter($policy->all(),static fn($l)=>in_array($l['mark'],$allowed,true)))];
    }
    public static function switchTo(Request $request,string $lang): array
    {
        $state=self::state($request);(new LanguageAccessService())->assertLanguages([$lang]);
        if(!$state['enabled']&&$lang!==$state['main'])throw new \InvalidArgumentException('多语言已关闭');
        if((string)$request->post('revision','')!==$state['revision'])throw new \InvalidArgumentException('语言已在其他窗口切换，请刷新');
        $request->session()->set('cms_edit_language',['lang'=>$lang,'revision'=>bin2hex(random_bytes(16)),'policy'=>$state['policy']]);
        return self::state($request);
    }
    public static function prepare(Request $request): void
    {
        if(!function_exists('admin')||!admin())return;
        $state=self::state($request);$controller=basename(str_replace('\\','/',$request->controller));
        $context=CmsContext::current();$context->module='admin';$context->lang=$state['lang']?:$state['main'];$context->memo['cms.admin.language']=$state;
        $context->parameters['lang']=$context->lang;
        // Constants must exist before the upstream registry is read on a cold worker.
        \app\cms\compat\Runtime::boot($context);
        $context->load();
        // These controllers select operation targets, not an alternative editor language.
        $management=in_array($controller,['LanguageController','LanguagepackController','RoleController','AccountController','IndexController','ConfigController','RuleController','AdminController','DictController','PluginController','TableController','DevController','OperationlogController','UserController'],true);
        if($management){
            if($controller==='IndexController'&&strtolower((string)$request->action)==='dashboard'&&$state['lang']==='')throw new \InvalidArgumentException('没有可用的编辑语言，请联系管理员');
            return;
        }
        if($state['lang']==='')throw new \InvalidArgumentException('没有可用的编辑语言，请联系管理员');
        $revision=(string)$request->header('x-cms-language-revision','');
        if($request->method()==='POST'&&($revision===''||!hash_equals($state['revision'],$revision)))throw new \InvalidArgumentException('编辑语言上下文已失效，请刷新后再提交');
        $provided=$request->input('lang');
        if($provided!==null&&$provided!==''&&(!is_scalar($provided)||(string)$provided!==$state['lang']))throw new \InvalidArgumentException('请使用右上角统一切换编辑语言');
        $header=(string)$request->header('x-cms-edit-language','');
        if($header!==''&&$header!==$state['lang'])throw new \InvalidArgumentException('编辑语言已改变，请刷新页面');
        $request->setGet('lang',$state['lang']);if($request->method()==='POST')$request->setPost('lang',$state['lang']);
        $context->parameters=array_replace($request->all(),['lang'=>$state['lang']]);
        self::records($request,$controller,$state['lang']);
    }
    private static function records(Request $request,string $controller,string $lang,?CmsRepository $repository=null): void
    {
        $r=$repository??new CmsRepository();$module=substr($controller,0,-10);$action=strtolower((string)$request->action);
        $input=$request->all();
        if($module==='Archive')$input=ArchiveListInput::forAccess($request,\app\cms\service\ContentService::normalizeInput($input));
        $tables=[
            'Archive'=>['archives','aid'],'Arctype'=>['arctype','id'],'Ad'=>['ad','id'],'Adposition'=>['ad_position','id'],
            'Links'=>['links','id'],'Linksgroup'=>['links_group','id'],'Navigation'=>['nav_list','nav_id'],
            'Navigationposition'=>['nav_position','position_id'],'Form'=>['form','form_id'],
            'Formfield'=>['guestbook_attribute','attr_id'],'Guestbook'=>['guestbook','aid'],'Tags'=>['tagindex','id'],
            'Faq'=>['faq_group','group_id'],'Globalvariable'=>['config_attribute','attr_id'],
            'Specialnode'=>['special_node','node_id'],'Searchword'=>['search_word','id'],
            'Emailtemplate'=>['smtp_tpl','tpl_id'],'Smstemplate'=>['sms_template','tpl_id'],
            'Downloadfile'=>['download_file','file_id'],'Mediafile'=>['media_file','file_id'],
        ];
        $keys=null;
        // The single-page endpoint uses a category ID, never an archive ID.
        if($module==='Archive'&&$action==='singlepage'){$tables[$module]=['arctype','id'];$keys=['id'];}
        if($module==='Globalvariable'&&in_array($action,['groups','groupsave','groupdelete'],true)){
            $tables[$module]=['config_type','id'];
        }
        if($module==='Recycle'){
            $types=['archives'=>['archives','aid'],'arctype'=>['arctype','id'],'customvar'=>['config','id'],'gbookattr'=>['guestbook_attribute','attr_id']];
            $type=$input['type']??'archives';
            if(is_string($type)&&isset($types[$type]))$tables[$module]=$types[$type];
        }
        if($module==='Productparameter'){
            if($action==='savegroup')$tables[$module]=['shop_product_attrlist','list_id'];
            elseif(in_array($action,['savedefinition','metadata','recycle','restore','purge'],true)){
                $kind=$input['kind']??'';
                if($kind==='legacy')$tables[$module]=['product_attribute','attr_id'];
                elseif($kind==='modern')$tables[$module]=['shop_product_attribute','attr_id'];
                elseif($kind==='group')$tables[$module]=['shop_product_attrlist','list_id'];
            }
        }
        $selected=[];
        if(isset($tables[$module])){
            [$table,$pk]=$tables[$module];$keys??=[$pk,'ids'];
            if($module==='Archive'&&$table==='archives')$keys[]='aids';
            $ids=[];foreach(array_unique($keys)as$key)if(array_key_exists($key,$input))$ids=array_merge($ids,self::recordIds($input[$key]));
            $selected=self::assertRecords($r,$table,$pk,array_values(array_unique($ids)),$lang);
            // Tables without their own language carry it through their owner.
            foreach($selected as$row){
                if(in_array($table,['download_file','media_file'],true))self::assertRecords($r,'archives','aid',self::recordIds($row['aid']??0),$lang);
                if($table==='nav_list')self::assertRecords($r,'arctype','id',self::recordIds($row['type_id']??0),$lang);
            }
        }
        if ($request->method()==='POST' && ($module==='Navigationposition' || ($module==='Navigation' && (
            (array_key_exists('type_id',$input) && (int)$input['type_id']===0)
            || (!$selected && !array_key_exists('type_id',$input))
            || array_filter($selected,static fn($row)=>(int)($row['type_id']??0)===0)
        )))) (new LanguageAccessService($r))->assertLanguages(array_column((new LanguagePolicy($r))->all(),'mark'));
        $references=[];
        if(in_array($module,['Archive','Arctype','Specialnode','Productparameter','Downloadfile','Mediafile'],true)){
            $references['typeid']=['arctype','id'];
            if(!($module==='Archive'&&$action==='singlepage'))$references['aid']=['archives','aid'];
        }
        if($module==='Archive'){
            $references['stypeid']=['arctype','id'];$references['typeid2']=['arctype','id'];$references['joinaid']=['archives','aid'];
            $references['attrlist_id']=['shop_product_attrlist','list_id'];
            $parameters=$input['product_parameters']??null;
            if(is_string($parameters))$parameters=json_decode($parameters,true);
            if(is_array($parameters)&&array_key_exists('attrlist_id',$parameters))self::assertRecords($r,'shop_product_attrlist','list_id',self::recordIds($parameters['attrlist_id']),$lang);
        }
        if($module==='Arctype')$references['parent_id']=['arctype','id'];
        if($module==='Ad')$references['pid']=['ad_position','id'];
        if($module==='Links')$references['groupid']=['links_group','id'];
        if($module==='Navigation'){
            $references['type_id']=['arctype','id'];$references['parent_id']=['nav_list','nav_id'];$references['position_id']=['nav_position','position_id'];
        }
        if($module==='Globalvariable'&&!in_array($action,['groups','groupsave','groupdelete'],true))$references['type_id']=['config_type','id'];
        if(in_array($module,['Guestbook','Formfield'],true)){
            $formType=$input['form_type']??($selected[0]['form_type']??0);
            if(!in_array($formType,[0,1,'0','1'],true))throw new \InvalidArgumentException('表单类型无效');
            $references['typeid']=(int)$formType===1?['form','form_id']:['arctype','id'];
        }
        if($module==='Formfield')$references['form_id']=['form','form_id'];
        if($module==='Productparameter'){
            $references['list_id']=['shop_product_attrlist','list_id'];$references['attrlist_id']=['shop_product_attrlist','list_id'];
        }
        foreach($references as$key=>[$table,$pk])if(array_key_exists($key,$input))self::assertRecords($r,$table,$pk,self::recordIds($input[$key]),$lang);
    }

    /** Do not silently cast malformed or missing batch IDs into another record. */
    private static function recordIds(mixed $value): array
    {
        if($value===null||$value==='')return [];
        if(is_string($value))$value=explode(',',$value);
        elseif(!is_array($value))$value=[$value];
        $ids=[];
        foreach($value as$id){
            if((!is_string($id)&&!is_int($id))||!preg_match('/^\d{1,10}$/D',(string)$id)||(int)$id>4294967295)throw new \InvalidArgumentException('记录ID格式错误');
            if((int)$id>0)$ids[]=(int)$id;
        }
        return array_values(array_unique($ids));
    }

    private static function assertRecords(CmsRepository $r,string $table,string $pk,array $ids,string $lang): array
    {
        if(!$ids)return [];
        $rows=$r->table($table)->whereIn($pk,$ids)->select()->toArray();
        if(count($rows)!==count($ids))throw new \InvalidArgumentException('记录不存在或已移除，请刷新页面');
        $columns=$r->columns($table);
        foreach($rows as$row)if(isset($columns['lang'])&&($row['lang']??'')!==$lang)throw new \InvalidArgumentException('记录不属于当前编辑语言');
        if($table==='nav_list')foreach($rows as$row)self::assertRecords($r,'arctype','id',self::recordIds($row['type_id']??0),$lang);
        return $rows;
    }
}
