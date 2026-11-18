<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use app\cms\repository\CmsRepository;
use app\cms\service\ContentAccessService;
use app\cms\service\LanguagePolicy;
use app\cms\service\LanguageStateStore;
use InvalidArgumentException;
use support\Request;

/** Check real owners before dispatch; list queries use ContentAccessService separately. */
final class ContentAdminAccess
{
    public static function prepare(Request $request): void
    {
        $r=new CmsRepository();$access=new ContentAccessService($r);if($access->unrestricted())return;
        $module=substr(basename(str_replace('\\','/',(string)$request->controller)),0,-10);
        $action=strtolower((string)$request->action);$input=$request->all();$post=$request->method()==='POST';
        if($module==='Archive') {
            $input=ArchiveListInput::forAccess($request,\app\cms\service\ContentService::normalizeInput($input));
            if($action==='singlepage'){
                foreach(self::ids($input['id']??[])as$id){$access->assertCategory($id);foreach($r->table('archives')->where(['typeid'=>$id,'is_del'=>0])->select()->toArray()as$row)$access->assertArchive($row);}
            }else foreach(['aid','aids','ids','id']as$key)foreach(self::ids($input[$key]??[])as$id)$access->assertArchive($id);
            foreach(['typeid','typeid2','stypeid']as$key)foreach(self::ids($input[$key]??[])as$id)$access->assertCategory($id);
            foreach(self::ids($input['joinaid']??[])as$id)$access->assertArchive($id);
        }
        if($module==='Arctype') {
            foreach(self::ids($input['parent_id']??[])as$id)$access->assertCategory($id);
            $ids=array_values(array_unique(array_merge(self::ids($input['id']??[]),self::ids($input['ids']??[]))));
            foreach($ids as$id)$access->assertCategory($id);
            if($action==='insert')$access->assertCreateCategory();
            if($post&&in_array($action,['update','delete'],true))$access->assertCategoryMutation($ids);
        }
        if($module==='Recycle') {
            $ids=self::ids($input['ids']??[]);$type=$input['type']??'archives';
            if($type==='archives')foreach($ids as$id)$access->assertArchive($id);
            if($type==='arctype'){
                foreach($ids as$id)$access->assertCategory($id);
                if($post)$access->assertCategoryMutation($ids,$action==='restore');
            }
        }
        if(in_array($module,['Downloadfile','Mediafile'],true)) {
            foreach(self::ids($input['aid']??[])as$id)$access->assertArchive($id);
            $table=$module==='Downloadfile'?'download_file':'media_file';
            foreach(array_unique(array_merge(self::ids($input['file_id']??[]),self::ids($input['record']??[]),self::ids($input['ids']??[])))as$id){
                $row=$r->table($table)->where('file_id',$id)->find();if(!$row)throw new InvalidArgumentException('附件不存在');$access->assertArchive((int)$row['aid']);
            }
        }
        if($module==='Productparameter') {
            foreach(self::ids($input['aid']??[])as$id)$access->assertArchive($id);
            foreach(self::ids($input['typeid']??[])as$id)$access->assertCategory($id);
            if($post&&in_array($action,['recycle','restore','purge'],true)&&!$access->unrestricted())throw new InvalidArgumentException('参数定义操作会影响多个文档，请使用具有全部内容范围的账号');
        }
        if($module==='Specialnode') {
            foreach(self::ids($input['aid']??[])as$id)$access->assertArchive($id);
            foreach(self::ids($input['ids']??[])as$id){$row=$r->table('special_node')->where('node_id',$id)->find();if(!$row)throw new InvalidArgumentException('专题节点不存在');$access->assertArchive((int)$row['aid']);}
            $nodes=$input['nodes']??[];if(is_string($nodes)){$nodes=json_decode($nodes,true);if(!is_array($nodes))throw new InvalidArgumentException('专题节点格式错误');}
            if(is_array($nodes))foreach($nodes as$node){if(!is_array($node))throw new InvalidArgumentException('专题节点格式错误');foreach(self::ids($node['typeid']??[])as$id)$access->assertCategory($id);foreach(self::ids($node['aidlist']??[])as$id)$access->assertArchive($id);}
        }
        if($module==='Channelfield') {
            foreach(self::ids($input['typeid']??[])as$id)$access->assertCategory($id);
            if($post&&in_array($action,['insert','update','delete'],true))throw new InvalidArgumentException('字段结构会影响多个栏目，请使用具有全部内容范围的账号');
        }
        if($module==='Language')self::language($request,$r,$access,$input,$action,$post);
    }

    private static function language(Request $request,CmsRepository $r,ContentAccessService $access,array $input,string $action,bool $post): void
    {
        if($post&&$action==='save'&&(int)($input['id']??0)===0)$access->assertCreateCategory();
        if($post&&in_array($action,['preview','sync','reset','restore','delete'],true)){
            $marks=[(new LanguagePolicy($r))->main()];
            foreach(array_merge(self::ids($input['id']??[]),self::ids($input['ids']??[]))as$id){$mark=$r->table('language')->where('id',$id)->value('mark');if(!$mark)throw new InvalidArgumentException('语言不存在');$marks[]=$mark;}
            if(isset($input['plan_token'])){
                $token=$input['plan_token'];if(!is_string($token)||!preg_match('/^[a-f0-9]{48}$/D',$token))throw new InvalidArgumentException('同步凭据无效');
                $receipt=(new LanguageStateStore('language.sync',$r))->read($token);
                if(!$receipt)throw new InvalidArgumentException('同步任务不存在');
                foreach(['source','target']as$key)if(!empty($receipt[$key]))$marks[]=$receipt[$key];
            }
            $access->assertLanguageContents(array_values(array_unique($marks)));
        }
        if($post&&$action==='associate'){
            $source=self::ids($input['source_id']??[]);$target=self::ids($input['target_id']??[]);
            foreach(array_merge($source,$target)as$id)$access->assertCategory($id);
            foreach($source as$id){
                $names=$r->table('language_attr')->where(['attr_group'=>'arctype','attr_value'=>(string)$id])->column('attr_name');
                if($names)foreach($r->table('language_attr')->where('attr_group','arctype')->whereIn('attr_name',$names)->select()->toArray()as$link)$access->assertCategory((int)$link['attr_value']);
            }
        }
    }

    private static function ids(mixed $value): array
    {
        if($value===null||$value==='')return [];
        if(is_string($value))$value=explode(',',$value);elseif(!is_array($value))$value=[$value];
        $ids=[];foreach($value as$id){if((!is_int($id)&&!is_string($id))||!preg_match('/^\d{1,10}$/D',(string)$id)||(int)$id>4294967295)throw new InvalidArgumentException('记录编号格式错误');if((int)$id>0)$ids[]=(int)$id;}
        return array_values(array_unique($ids));
    }
}
