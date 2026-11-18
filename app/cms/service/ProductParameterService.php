<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Display-product parameters, using original legacy and newattr tables and IDs. */
final class ProductParameterService
{
    private const KINDS=['legacy'=>['product_attribute','attr_id','attr_'],'modern'=>['shop_product_attribute','attr_id','attribute_'],'group'=>['shop_product_attrlist','list_id','attrlist_']];
    public function __construct(private ?CmsRepository $repository=null){$this->repository??=new CmsRepository();}
    private function editingLanguage():string
    {
        $policy=new LanguagePolicy($this->repository);$context=\app\cms\CmsContext::current();
        return $policy->enabled()&&$context->request&&$context->lang!==''?$context->lang:$policy->main();
    }
    private function number(mixed $value,bool $zero=false):int
    {
        if((!is_string($value)&&!is_int($value))||!preg_match('/^\d{1,10}$/D',(string)$value)||(int)$value>2147483647||(int)$value<($zero?0:1))throw new InvalidArgumentException('产品参数数字格式错误');return (int)$value;
    }
    private function text(mixed $value,int $max,bool $bytes=false):string
    {
        if(!is_string($value)||($bytes?strlen($value):mb_strlen($value))>$max)throw new InvalidArgumentException('产品参数文字格式错误或超过长度限制');return trim($value);
    }
    private function kind(string $kind):array {return self::KINDS[$kind]??throw new InvalidArgumentException('参数类型错误');}
    private function language(string $lang,bool $lock=false):void
    {
        $q=$this->repository->table('language')->where('mark',$lang);if(!($lock?$q->lock(true):$q)->find())throw new InvalidArgumentException('语言不存在');
    }
    private function engines():void
    {
        foreach(['language','config','archives','arctype','product_attribute','product_attr','shop_product_attrlist','shop_product_attribute','shop_product_attr','language_attr','language_attribute'] as $table){$rows=$this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);if(strtolower((string)($rows[0]['ENGINE']??''))!=='innodb')throw new InvalidArgumentException('产品参数及关联表必须使用InnoDB事务引擎');}
    }
    private function productOwner(int $aid,string $lang,bool $lock=false):array
    {
        (new LanguageAccessService($this->repository))->assertLanguages([$lang]);
        $this->number($aid);$this->language($lang);$q=$this->repository->table('archives')->where(['aid'=>$aid,'lang'=>$lang,'is_del'=>0]);$row=($lock?$q->lock(true):$q)->find();
        if(!$row||!$this->isProduct((int)$row['channel'])||!empty($row['merchant_id']))throw new InvalidArgumentException('展示产品不存在、已回收、属于多商家或语言不符');$this->category((int)$row['typeid'],$lang);return $row;
    }
    private function isProduct(int $channel):bool {return (bool)$this->repository->table('channeltype')->where(['id'=>$channel,'nid'=>'product'])->find();}
    private function category(int $id,string $lang,bool $allowRecycled=false):array
    {
        $q=$this->repository->table('arctype')->where(['id'=>$id,'lang'=>$lang]);if(!$allowRecycled)$q->where('is_del',0);$row=$q->find();if(!$row||!$this->isProduct((int)$row['current_channel']))throw new InvalidArgumentException('产品栏目不存在、已回收或语言不符');return $row;
    }
    private function group(int $id,string $lang,bool $active=true):array
    {
        $row=$this->repository->table('shop_product_attrlist')->where(['list_id'=>$id,'lang'=>$lang])->find();if(!$row||($active&&!empty($row['is_del'])))throw new InvalidArgumentException('新参数组不存在、已回收或语言不符');return $row;
    }
    private function languagePolicy(string $lang, bool $lock = true): array
    {
        $q = $this->repository->table('language')->order('id asc');
        $rows = ($lock ? $q->lock(true) : $q)->select()->toArray();
        if (!in_array($lang, array_column($rows, 'mark'), true)) throw new InvalidArgumentException('语言不存在');
        (new LanguageAccessService($this->repository))->assertLanguages([$lang]);
        $policy = new LanguagePolicy($this->repository);
        if ($lock) $this->repository->table('config')->where(['name'=>'language_split','lang'=>$policy->main(),'is_del'=>0])->lock(true)->select();
        return ['languages'=>$rows,'main_lang'=>$policy->main(),'multilingual'=>$policy->enabled(),'language_split'=>(bool)$policy->settings()['language_split'],'lang'=>$lang];
    }
    public function metadata(string $lang=''):array
    {
        $lang=$lang?:$this->editingLanguage();
        $policy=$this->languagePolicy($lang,false);$r=$this->repository;$models=$r->table('channeltype')->where('nid','product')->column('id');$categories=$r->table('arctype')->where(['lang'=>$lang,'is_del'=>0])->whereIn('current_channel',$models)->field('id,typename,parent_id')->order('sort_order asc,id asc')->select()->toArray();
        $data=['languages'=>array_map(fn($v)=>array_intersect_key($v,array_flip(['mark','title'])),$policy['languages']),'policy'=>array_intersect_key($policy,array_flip(['main_lang','multilingual','language_split'])),'categories'=>$categories,'products'=>$r->table('archives')->where(['lang'=>$lang,'is_del'=>0,'merchant_id'=>0])->whereIn('channel',$models)->whereIn('typeid',array_column($categories,'id'))->field('aid,title,typeid,attrlist_id')->order('aid desc')->select()->toArray()];
        foreach(self::KINDS as $kind=>[$table,$pk,$prefix]){$rows=$r->table($table)->where('lang',$lang)->order('sort_order asc,'.$pk.' asc')->select()->toArray();if($kind==='modern')$rows=array_values(array_filter($rows,fn($row)=>empty($row['is_custom'])));foreach($rows as &$row){$binding=$r->table('language_attr')->where(['lang'=>$lang,'attr_group'=>$table,'attr_value'=>(string)$row[$pk]])->order('id asc')->find();$row['canonical_name']=$binding['attr_name']??$prefix.$row[$pk];}unset($row);$data[$kind]=$rows;}
        return $data;
    }
    /** Resolve exact ownership, rejecting aliases before any family mutation. */
    private function canonicalName(string $table,string $pk,int $id,string $lang,string $prefix):string
    {
        $r=$this->repository;$this->language($lang);$row=$r->table($table)->where([$pk=>$id,'lang'=>$lang])->lock(true)->find();
        if(!$row)throw new InvalidArgumentException('语言关联目标不存在或语言不符');
        $bindings=$r->table('language_attr')->where(['attr_group'=>$table,'attr_value'=>(string)$id])->order('id asc')->lock(true)->select()->toArray();
        if(!$bindings)return $prefix.$id;
        if(count($bindings)!==1||$bindings[0]['lang']!==$lang)throw new InvalidArgumentException('语言绑定重复或归属异常');
        $same=$r->table('language_attr')->where(['attr_group'=>$table,'attr_name'=>$bindings[0]['attr_name'],'lang'=>$lang])->lock(true)->select()->toArray();
        if(count($same)!==1||(int)$same[0]['attr_value']!==$id)throw new InvalidArgumentException('语言组存在重复目标');
        return (string)$bindings[0]['attr_name'];
    }
    private function ownerFamily(string $kind,array $row,bool $allowRecycled=false):array
    {
        $r=$this->repository;
        if($kind==='legacy'){$table='arctype';$pk='id';$owner=(int)$row['typeid'];$prefix='tid';$this->category($owner,$row['lang'],$allowRecycled);}
        elseif($kind==='modern'){$table='shop_product_attrlist';$pk='list_id';$owner=(int)$row['list_id'];$prefix='attrlist_';if(!$owner)return ['name'=>'global','members'=>[]];$this->group($owner,$row['lang']);}
        else return ['name'=>'group','members'=>[]];
        $name=$this->canonicalName($table,$pk,$owner,$row['lang'],$prefix);
        $bindings=$r->table('language_attr')->where(['attr_group'=>$table,'attr_name'=>$name])->order('lang asc,id asc')->lock(true)->select()->toArray();$members=[];
        if(!$bindings)$members[$row['lang']]=$owner;
        foreach($bindings as $binding){$language=(string)$binding['lang'];$id=$this->number($binding['attr_value']);if(isset($members[$language]))throw new InvalidArgumentException('所属栏目或组的语言绑定重复');if($kind==='legacy')$this->category($id,$language,$allowRecycled);else$this->group($id,$language);if($this->canonicalName($table,$pk,$id,$language,$prefix)!==$name)throw new InvalidArgumentException('所属栏目或组的语言绑定冲突');$members[$language]=$id;}
        if(($members[$row['lang']]??0)!==$owner)throw new InvalidArgumentException('所属栏目或组缺少当前语言绑定');
        return ['name'=>$name,'members'=>$members];
    }
    private function canonicalFamily(string $kind,array $row,bool $allowRecycled=false,bool $matchOwners=false):array
    {
        [$table,$pk,$prefix]=$this->kind($kind);$r=$this->repository;$name=$this->canonicalName($table,$pk,(int)$row[$pk],$row['lang'],$prefix);$owner=$kind==='group'?null:$this->ownerFamily($kind,$row,$allowRecycled);
        $bindings=$r->table('language_attr')->where(['attr_group'=>$table,'attr_name'=>$name])->order('lang asc,id asc')->lock(true)->select()->toArray();$members=[];
        if(!$bindings)$members[$row['lang']]=$row;
        foreach($bindings as $binding){$language=(string)$binding['lang'];$id=$this->number($binding['attr_value']);if(isset($members[$language]))throw new InvalidArgumentException('参数语言组重复');$target=$r->table($table)->where([$pk=>$id,'lang'=>$language])->lock(true)->find();if(!$target||!empty($target['is_custom'])||$this->canonicalName($table,$pk,$id,$language,$prefix)!==$name)throw new InvalidArgumentException('参数语言绑定失效或归属异常');if($owner){$targetOwner=$this->ownerFamily($kind,$target,$allowRecycled);if($matchOwners&&$targetOwner['name']!==$owner['name'])throw new InvalidArgumentException('参数语言组的所属栏目或组不一致');}$members[$language]=$target;}
        if(!isset($members[$row['lang']])||(int)$members[$row['lang']][$pk] !== (int)$row[$pk])throw new InvalidArgumentException('参数语言组缺少当前目标');
        return ['name'=>$name,'members'=>$members];
    }
    private function bindCanonical(string $kind,int $id,string $lang,string $title,int $source=0):string
    {
        [$table,$pk,$prefix]=$this->kind($kind);$r=$this->repository;$row=$r->table($table)->where([$pk=>$id,'lang'=>$lang])->lock(true)->find();
        $bindings=$r->table('language_attr')->where(['attr_group'=>$table,'attr_value'=>(string)$id])->order('id asc')->lock(true)->select()->toArray();
        if($bindings){if($source)throw new InvalidArgumentException('已有参数不能重新绑定，请保留现有语言关联');$family=$this->canonicalFamily($kind,$row);$name=$family['name'];}
        else{
            $name=$prefix.$id;
            if($source){$origin=$r->table($table)->where($pk,$source)->lock(true)->find();if(!$origin||$origin['lang']===$lang||!empty($origin['is_del'])||!empty($origin['is_custom']))throw new InvalidArgumentException('源参数不存在、已回收或不属于另一语言');(new LanguageAccessService($r))->assertLanguages([$lang,(string)$origin['lang']]);$family=$this->canonicalFamily($kind,$origin);if($kind!=='group'&&$this->ownerFamily($kind,$row)['name']!==$this->ownerFamily($kind,$origin)['name'])throw new InvalidArgumentException('源参数与目标的所属栏目或组语言绑定不一致');$name=$family['name'];$originBindings=$r->table('language_attr')->where(['attr_group'=>$table,'attr_value'=>(string)$source])->lock(true)->select()->toArray();if(!$originBindings)$this->insertBinding($table,$name,$source,$origin['lang']);}
            if($r->table('language_attr')->where(['attr_group'=>$table,'attr_name'=>$name,'lang'=>$lang])->lock(true)->find())throw new InvalidArgumentException('当前语言已有此参数绑定');
            $this->insertBinding($table,$name,$id,$lang);
        }
        $definition=$r->table('language_attribute')->where(['attr_group'=>$table,'attr_name'=>$name])->lock(true)->select()->toArray();if(count($definition)>1)throw new InvalidArgumentException('参数语言变量重复');
        if(!$definition)$r->table('language_attribute')->insert(['attr_group'=>$table,'attr_name'=>$name,'attr_title'=>$title,'add_time'=>time(),'update_time'=>time()]);
        return $name;
    }
    private function insertBinding(string $table,string $name,int $id,string $lang):void
    {
        $this->repository->table('language_attr')->insert(['attr_group'=>$table,'attr_name'=>$name,'attr_value'=>(string)$id,'lang'=>$lang,'add_time'=>time(),'update_time'=>time()]);
    }
    private function cloneCreatedFamily(string $kind,int $id,array $policy):void
    {
        (new LanguageAccessService($this->repository))->assertLanguages(array_column($policy['languages'],'mark'));
        [$table,$pk]=$this->kind($kind);$r=$this->repository;$row=$r->table($table)->where($pk,$id)->lock(true)->find();$family=$this->canonicalFamily($kind,$row,false,true);$owner=$kind==='group'?null:$this->ownerFamily($kind,$row);
        foreach($policy['languages'] as $language){$lang=$language['mark'];if($lang===$row['lang'])continue;if(isset($family['members'][$lang]))throw new InvalidArgumentException('目标语言已有此参数，不能覆盖翻译');$copy=$row;unset($copy[$pk]);$copy['lang']=$lang;
            if($owner&&$owner['name']!=='global'){$foreign=$kind==='legacy'?'typeid':'list_id';if(!isset($owner['members'][$lang]))throw new InvalidArgumentException('目标语言缺少所属栏目或参数组的准确绑定');$copy[$foreign]=$owner['members'][$lang];}
            if($kind==='group')$copy['list_name']=$this->text($lang.$row['list_name'],60);elseif($kind==='modern')$copy['attr_name']=$this->text($lang.$row['attr_name'],60);
            $new=(int)$r->table($table)->insertGetId($copy);$this->insertBinding($table,$family['name'],$new,$lang);if($kind==='modern')$this->counts([(int)$copy['list_id']],$lang);
        }
    }
    private function counts(array $ids,string $lang):void
    {
        foreach(array_unique(array_filter($ids)) as $id){$count=$this->repository->table('shop_product_attribute')->where(['list_id'=>$id,'lang'=>$lang,'is_del'=>0,'is_custom'=>0])->count();$this->repository->table('shop_product_attrlist')->where(['list_id'=>$id,'lang'=>$lang])->update(['attr_count'=>$count]);}
    }
    public function saveGroup(array $input,?int $id=null):int
    {
        $this->engines();return $this->repository->transaction(function($r)use($input,$id){$lang=$input['lang']??$this->editingLanguage();if(!is_string($lang))throw new InvalidArgumentException('语言格式错误');$policy=$this->languagePolicy($lang);$creating=!$id;$source=$this->number($input['source_id']??0,true);
            $definitions=array_key_exists('definitions',$input)?$this->arrayInput($input['definitions']):[];if($definitions&&(!$creating||$source))throw new InvalidArgumentException('初始参数定义仅支持新建独立参数组');
            $old=$id?$this->group($this->number($id),$lang):null;$name=$this->text($input['list_name']??$old['list_name']??'',60);if($name==='')throw new InvalidArgumentException('请填写新参数组名称');$status=$this->number($input['status']??$old['status']??1,true);if($status>1)throw new InvalidArgumentException('组状态必须为0或1');$data=['list_name'=>$name,'desc'=>$this->text($input['desc']??$old['desc']??'',65535,true),'status'=>$status,'sort_order'=>$this->number($input['sort_order']??$old['sort_order']??100,true),'update_time'=>time()];if($id)$r->table('shop_product_attrlist')->where(['list_id'=>$id,'lang'=>$lang])->update($data);else$id=(int)$r->table('shop_product_attrlist')->insertGetId($data+['lang'=>$lang,'add_time'=>time()]);$this->bindCanonical('group',$id,$lang,$name,$source);
            $clone=$creating&&!$source&&$policy['multilingual']&&!$policy['language_split']&&$lang===$policy['main_lang'];if($clone)$this->cloneCreatedFamily('group',$id,$policy);
            foreach($definitions as $definition){if(!is_array($definition)||array_intersect(array_keys($definition),['attr_id','source_id','lang','list_id']))throw new InvalidArgumentException('初始参数定义不得指定ID、源ID、语言或组');$definition['lang']=$lang;$definition['list_id']=$id;$attr=$this->saveDefinition('modern',$definition);if($clone)$this->cloneCreatedFamily('modern',$attr,$policy);}
            return $id;});
    }
    public function saveDefinition(string $kind,array $input,?int $id=null):int
    {
        if(!in_array($kind,['legacy','modern'],true))throw new InvalidArgumentException('参数定义类型错误');[$table,$pk]=$this->kind($kind);$this->engines();
        return $this->repository->transaction(function($r)use($kind,$table,$pk,$input,$id){$lang=$input['lang']??$this->editingLanguage();if(!is_string($lang))throw new InvalidArgumentException('语言格式错误');$policy=$this->languagePolicy($lang);$creating=!$id;$source=$this->number($input['source_id']??0,true);$old=$id?$r->table($table)->where([$pk=>$this->number($id),'lang'=>$lang])->lock(true)->find():null;if($id&&(!$old||!empty($old['is_del'])||!empty($old['is_custom'])))throw new InvalidArgumentException('定义不存在、已回收、属于产品自定义或语言不符');$name=$this->text($input['attr_name']??$old['attr_name']??'',60);if($name==='')throw new InvalidArgumentException('请填写参数名称');$type=$this->number($input['attr_input_type']??$old['attr_input_type']??0,true);if($type>3)throw new InvalidArgumentException('参数输入类型必须为0至3');$options=$this->text($input['attr_values']??$old['attr_values']??'',65535,true);$options=trim(str_replace(['_','@'],'',$options));if($type===1&&$options==='')throw new InvalidArgumentException('下拉参数必须填写可选值');$data=['attr_name'=>$name,'attr_input_type'=>$type,'attr_values'=>$options,'attr_index'=>$this->number($input['attr_index']??$old['attr_index']??0,true),'sort_order'=>$this->number($input['sort_order']??$old['sort_order']??100,true),'update_time'=>time()];if($data['attr_index']>2)throw new InvalidArgumentException('检索类型必须为0至2');if($kind==='legacy'){$data['typeid']=$this->number($input['typeid']??$old['typeid']??0);$this->category($data['typeid'],$lang);}else{$data['list_id']=$this->number($input['list_id']??$old['list_id']??0,true);if($data['list_id'])$this->group($data['list_id'],$lang);$data['status']=$this->number($input['status']??$old['status']??1,true);if($data['status']>1)throw new InvalidArgumentException('参数状态必须为0或1');}if($id)$r->table($table)->where([$pk=>$id,'lang'=>$lang])->update($data);else$id=(int)$r->table($table)->insertGetId($data+['lang'=>$lang,'add_time'=>time()]);$this->bindCanonical($kind,$id,$lang,$name,$source);if($kind==='modern')$this->counts([$old['list_id']??0,$data['list_id']],$lang);
            if($creating&&$kind==='legacy'&&!$source&&$policy['multilingual']&&!$policy['language_split']&&$lang===$policy['main_lang'])$this->cloneCreatedFamily($kind,$id,$policy);
            return $id;});
    }
    private function legacyRemovalTargets(array $ids,string $lang,string $action,array $policy):array
    {
        if($action!=='recycle'&&$policy['multilingual']&&!$policy['language_split']&&$lang!==$policy['main_lang'])throw new InvalidArgumentException('当前语言不能恢复或彻底删除旧参数，请切换到主体语言');$r=$this->repository;$rows=$r->table('product_attribute')->whereIn('attr_id',$ids)->where('lang',$lang)->order('attr_id asc')->lock(true)->select()->toArray();if(count($rows)!==count($ids))throw new InvalidArgumentException('定义不存在或语言不符');$targets=[];
        $expand=$policy['multilingual']&&!$policy['language_split']&&$lang===$policy['main_lang'];
        foreach($rows as $row){$family=$this->canonicalFamily('legacy',$row,true);foreach($expand?$family['members']:[$row] as $target)$targets[(int)$target['attr_id']]=$target;}
        (new LanguageAccessService($this->repository))->assertLanguages(array_values(array_unique(array_column($targets,'lang'))));
        ksort($targets,SORT_NUMERIC);return array_values($targets);
    }
    public function removalPlan(string $kind,array $ids,string $lang='',string $action='purge'):array
    {
        $lang=$lang?:$this->editingLanguage();
        if(!in_array($action,['recycle','restore','purge'],true)||($kind!=='legacy'&&$action!=='purge'))throw new InvalidArgumentException('参数操作类型错误');[$table,$pk]=$this->kind($kind);$ids=$this->ids($ids);
        return $this->repository->transaction(function($r)use($kind,$table,$pk,$ids,$lang,$action){
            // Every metadata writer takes the language locks before group/definition locks.
            $policy=$this->languagePolicy($lang);$rows=$kind==='legacy'?$this->legacyRemovalTargets($ids,$lang,$action,$policy):$r->table($table)->whereIn($pk,$ids)->where('lang',$lang)->order($pk.' asc')->lock(true)->select()->toArray();
            if($kind!=='legacy'&&count($rows)!==count($ids))throw new InvalidArgumentException('参数不存在或语言不符');$affected=[];
            foreach($rows as $row){
                if($kind==='group'){
                    $affected[]=['kind'=>'group','id'=>(int)$row[$pk],'lang'=>$row['lang'],'name'=>$row['list_name']];
                    $definitions=$r->table('shop_product_attribute')->where('list_id',$row[$pk])->order('attr_id asc')->lock(true)->select()->toArray();
                    foreach($definitions as $definition){if($definition['lang']!==$row['lang']||!empty($definition['is_custom']))throw new InvalidArgumentException('组内定义语言或归属异常');$affected[]=$this->removalDefinitionEntry('modern',$definition,$action);}
                }else $affected[]=$this->removalDefinitionEntry($kind,$row,$action);
            }
            $plan=['kind'=>$kind,'action'=>$action,'affected'=>$affected];$plan['fingerprint']=hash('sha256',json_encode($plan,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));return $plan;
        });
    }
    private function removalDefinitionEntry(string $kind,array $row,string $action):array
    {
        if(!empty($row['is_custom']))throw new InvalidArgumentException('请在所属产品编辑自定义参数');
        $entry=['kind'=>$kind,'id'=>(int)$row['attr_id'],'lang'=>$row['lang'],'name'=>$row['attr_name']];
        if($action==='purge'){
            $table=$kind==='legacy'?'product_attr':'shop_product_attr';
            $values=$this->repository->table($table)->where('attr_id',$row['attr_id'])->field('product_attr_id,aid')->order('product_attr_id asc')->lock(true)->select()->toArray();
            $entry['product_value_ids']=array_map('intval',array_column($values,'product_attr_id'));
            $entry['product_ids']=array_map('intval',array_column($values,'aid'));
            $entry['product_value_count']=count($values);
        }
        return $entry;
    }
    /** The UI submits the preview fingerprint; locks remain held through mutation. */
    public function executeRemovalPlan(string $kind,array $ids,string $lang,string $action,string $fingerprint):void
    {
        if(!preg_match('/^[a-f0-9]{64}$/D',$fingerprint))throw new InvalidArgumentException('参数影响预览指纹无效');
        $this->engines();$this->repository->transaction(function()use($kind,$ids,$lang,$action,$fingerprint){$plan=$this->removalPlan($kind,$ids,$lang,$action);if(!hash_equals($plan['fingerprint'],$fingerprint))throw new InvalidArgumentException('参数影响范围已变化，请重新读取并确认');if($action==='purge')$this->purge($kind,$ids,$lang);else$this->recycle($kind,$ids,$lang,$action==='restore');});
    }
    public function recycle(string $kind,array $ids,string $lang='',bool $restore=false):void
    {
        $lang=$lang?:$this->editingLanguage();
        if($kind!=='legacy')throw new InvalidArgumentException('旧参数定义支持回收与恢复，新参数请使用停用或永久删除');$ids=$this->ids($ids);$this->engines();$this->repository->transaction(function($r)use($ids,$lang,$restore){$policy=$this->languagePolicy($lang);$rows=$this->legacyRemovalTargets($ids,$lang,$restore?'restore':'recycle',$policy);foreach($rows as $row)if($restore)$this->category((int)$row['typeid'],$row['lang']);$r->table('product_attribute')->whereIn('attr_id',array_column($rows,'attr_id'))->update(['is_del'=>$restore?0:1,'update_time'=>time()]);});
    }
    private function ids(array $ids):array {$ids=array_values(array_unique(array_map(fn($v)=>$this->number($v),$ids)));if(!$ids||count($ids)>500)throw new InvalidArgumentException('请选择最多500条参数');return $ids;}
    private function unbind(string $kind,int $id,string $lang):void
    {
        [$table]=$this->kind($kind);$r=$this->repository;$bindings=$r->table('language_attr')->where(['lang'=>$lang,'attr_group'=>$table,'attr_value'=>(string)$id])->lock(true)->select()->toArray();$r->table('language_attr')->where(['lang'=>$lang,'attr_group'=>$table,'attr_value'=>(string)$id])->delete();foreach($bindings as $binding)if(!$r->table('language_attr')->where(['attr_group'=>$table,'attr_name'=>$binding['attr_name']])->find())$r->table('language_attribute')->where(['attr_group'=>$table,'attr_name'=>$binding['attr_name']])->delete();
    }
    public function purge(string $kind,array $ids,string $lang=''):void
    {
        $lang=$lang?:$this->editingLanguage();
        [$table,$pk]=$this->kind($kind);$ids=$this->ids($ids);$this->engines();$this->repository->transaction(function($r)use($kind,$table,$pk,$ids,$lang){$policy=$this->languagePolicy($lang);$rows=$kind==='legacy'?$this->legacyRemovalTargets($ids,$lang,'purge',$policy):$r->table($table)->whereIn($pk,$ids)->where('lang',$lang)->order($pk.' asc')->lock(true)->select()->toArray();if($kind!=='legacy'&&count($rows)!==count($ids))throw new InvalidArgumentException('参数不存在或语言不符');$countGroups=[];foreach($rows as $row){$ownerLang=$row['lang'];if($kind==='modern')$countGroups[]=(int)$row['list_id'];if($kind==='legacy'&&empty($row['is_del']))throw new InvalidArgumentException('请先回收所有关联语言旧参数再彻底删除');if(!empty($row['is_custom']))throw new InvalidArgumentException('请在所属产品编辑自定义参数');if($kind==='group'){if($r->table('archives')->where('attrlist_id',$row[$pk])->lock(true)->find())throw new InvalidArgumentException('参数组仍被产品引用，请先解除产品的参数组');$definitions=$r->table('shop_product_attribute')->where('list_id',$row[$pk])->order('attr_id asc')->lock(true)->select()->toArray();foreach($definitions as $definition){if($definition['lang']!==$ownerLang||!empty($definition['is_custom']))throw new InvalidArgumentException('组内定义语言或归属异常');$this->purgeDefinition('modern',$definition,$ownerLang);}}else$this->purgeDefinition($kind,$row,$ownerLang);$r->table($table)->where([$pk=>$row[$pk],'lang'=>$ownerLang])->delete();$this->unbind($kind,(int)$row[$pk],$ownerLang);}$this->counts($countGroups,$lang);});
    }
    private function purgeDefinition(string $kind,array $definition,string $lang):void
    {
        $r=$this->repository;[$table,$pk]=$this->kind($kind);$values=$kind==='legacy'?'product_attr':'shop_product_attr';foreach($r->table($values)->where('attr_id',$definition['attr_id'])->lock(true)->select()->toArray() as $value){$owner=$r->table('archives')->where('aid',$value['aid'])->find();if(!$owner||$owner['lang']!==$lang||!$this->isProduct((int)$owner['channel'])||!empty($owner['merchant_id']))throw new InvalidArgumentException('存在语言或产品归属异常的参数值，已取消删除');}$r->table($values)->where('attr_id',$definition['attr_id'])->delete();$r->table($table)->where([$pk=>$definition[$pk],'lang'=>$lang])->delete();$this->unbind($kind,(int)$definition[$pk],$lang);
    }
    public function product(int $aid,string $lang=''):array
    {
        $lang=$lang?:$this->editingLanguage();
        $owner=$this->productOwner($aid,$lang);$r=$this->repository;$legacy=$r->table('product_attr')->where('aid',$aid)->order('product_attr_id asc')->select()->toArray();$modern=$r->table('shop_product_attr')->where('aid',$aid)->order('sort_order asc,product_attr_id asc')->select()->toArray();$custom=[];$shared=[];foreach($modern as $value){$definition=$r->table('shop_product_attribute')->where('attr_id',$value['attr_id'])->find();$value['definition']=$definition;if($definition&&!empty($definition['is_custom'])){$value['attr_name']=$definition['attr_name'];$custom[]=$value;}else$shared[]=$value;}foreach($legacy as &$value)$value['definition']=$r->table('product_attribute')->where('attr_id',$value['attr_id'])->find();unset($value);return ['owner'=>$owner,'legacy'=>$legacy,'modern'=>$shared,'custom'=>$custom,'metadata'=>$this->metadata($lang)];
    }
    private function arrayInput(mixed $value):array
    {
        if(is_string($value)){try{$value=json_decode($value,true,32,JSON_THROW_ON_ERROR);}catch(\JsonException){throw new InvalidArgumentException('参数列表JSON格式错误');}}if(!is_array($value)||!array_is_list($value)||count($value)>500)throw new InvalidArgumentException('参数列表需为最多500条的数组');return $value;
    }
    public function saveProduct(int $aid,array $input,string $lang=''):array
    {
        $lang=$lang?:$this->editingLanguage();
        $this->engines();return $this->repository->transaction(function($r)use($aid,$input,$lang){$owner=$this->productOwner($aid,$lang,true);$group=array_key_exists('attrlist_id',$input)?$this->number($input['attrlist_id'],true):(int)$owner['attrlist_id'];if($group)$this->group($group,$lang);
            foreach(['legacy','modern','custom'] as $kind)if(array_key_exists($kind,$input))$this->replaceValues($aid,$lang,$kind,$this->arrayInput($input[$kind]),(int)$owner['typeid'],$group,(string)($input['confirm_custom_delete']??'')==='1');
            if(array_key_exists('attrlist_id',$input))$r->table('archives')->where(['aid'=>$aid,'lang'=>$lang])->update(['attrlist_id'=>$group,'update_time'=>time()]);return $this->product($aid,$lang);});
    }
    private function customOwned(int $attrId,int $aid,string $lang):array
    {
        $r=$this->repository;$definition=$r->table('shop_product_attribute')->where(['attr_id'=>$attrId,'lang'=>$lang,'is_custom'=>1])->lock(true)->find();$owners=array_map('intval',$r->table('shop_product_attr')->where('attr_id',$attrId)->lock(true)->column('aid'));if(!$definition||!$owners||array_diff($owners,[$aid]))throw new InvalidArgumentException('自定义参数不唯一属于当前产品或语言');return $definition;
    }
    private function replaceValues(int $aid,string $lang,string $kind,array $input,int $typeid,int $group,bool $confirmCustomDelete=false):void
    {
        $r=$this->repository;$table=$kind==='legacy'?'product_attr':'shop_product_attr';$definitionTable=$kind==='legacy'?'product_attribute':'shop_product_attribute';$all=$r->table($table)->where('aid',$aid)->lock(true)->select()->toArray();$existing=[];
        foreach($all as $row){$definition=$r->table($definitionTable)->where('attr_id',$row['attr_id'])->lock(true)->find();if(!$definition||$definition['lang']!==$lang)throw new InvalidArgumentException('已有参数存在定义或语言归属异常');$isCustom=!empty($definition['is_custom']);if($kind==='legacy'||($kind==='custom')===$isCustom)$existing[(int)$row['product_attr_id']]=$row;}
        $keep=[];$used=[];$customIds=[];$now=time();
        foreach($input as $position=>$value){if(!is_array($value))throw new InvalidArgumentException('参数值格式错误');$id=$this->number($value['product_attr_id']??0,true);$attr=$this->number($value['attr_id']??0,$kind==='custom');if($id&&(!isset($existing[$id])||isset($used[$id])||(int)$existing[$id]['attr_id']!==$attr))throw new InvalidArgumentException('参数值ID不存在、重复或不属于当前产品');if($id)$used[$id]=true;$text=$this->text($value['attr_value']??'',65535,true);$sort=$this->number($value['sort_order']??$existing[$id]['sort_order']??($position+1),true);
            if($kind==='custom'){$definition=$attr?$this->customOwned($attr,$aid,$lang):null;$name=$this->text($value['attr_name']??$definition['attr_name']??'',60);if($text===''||$text==='0')continue;if($name==='')throw new InvalidArgumentException('请填写自定义参数名称');if($attr){if(isset($customIds[$attr])&&$customIds[$attr]!==$name)throw new InvalidArgumentException('同一自定义参数名称冲突');$r->table('shop_product_attribute')->where(['attr_id'=>$attr,'lang'=>$lang])->update(['attr_name'=>$name,'update_time'=>$now]);}else$attr=(int)$r->table('shop_product_attribute')->insertGetId(['attr_name'=>$name,'list_id'=>0,'attr_input_type'=>0,'attr_values'=>'','status'=>1,'is_custom'=>1,'is_del'=>0,'sort_order'=>100,'lang'=>$lang,'add_time'=>$now,'update_time'=>$now]);$customIds[$attr]=$name;
            }else{$definition=$r->table($definitionTable)->where(['attr_id'=>$attr,'lang'=>$lang])->lock(true)->find();if(!$definition||!empty($definition['is_custom']))throw new InvalidArgumentException('参数定义不存在或语言不符');$unchanged=$id&&$existing[$id]['attr_value']===$text&&($kind==='legacy'||(int)$existing[$id]['sort_order']===$sort);$eligible=empty($definition['is_del'])&&($kind==='legacy'?(int)$definition['typeid']===$typeid:((int)$definition['status']===1&&in_array((int)$definition['list_id'],[0,$group],true)));if(!$eligible&&!$unchanged)throw new InvalidArgumentException('参数定义已回收、停用或不属于当前栏目/组');if($text===''||$text==='0')continue;}
            if(!$id)foreach($existing as $oldId=>$old)if(!isset($keep[$oldId])&&(int)$old['attr_id']===$attr&&$old['attr_value']===$text&&($kind==='legacy'||(int)$old['sort_order']===$sort)){$id=$oldId;break;}
            $data=['attr_id'=>$attr,'attr_value'=>$text,'update_time'=>$now];if($kind!=='legacy')$data+=['sort_order'=>$sort,'is_custom'=>$kind==='custom'?1:0];if($id){if(isset($keep[$id]))throw new InvalidArgumentException('参数值ID重复');$r->table($table)->where(['product_attr_id'=>$id,'aid'=>$aid])->update($data);}else$id=(int)$r->table($table)->insertGetId($data+['aid'=>$aid,'add_time'=>$now]);$keep[$id]=true;
        }
        $delete=array_values(array_diff(array_keys($existing),array_keys($keep)));if($kind==='custom'&&$delete&&!$confirmCustomDelete)throw new InvalidArgumentException('清空或移除产品自定义参数会永久删除其独占定义，请明确确认');if($kind==='custom')foreach($delete as $id)$this->customOwned((int)$existing[$id]['attr_id'],$aid,$lang);if($delete)$r->table($table)->where('aid',$aid)->whereIn('product_attr_id',$delete)->delete();if($kind==='custom')foreach(array_unique(array_column($existing,'attr_id')) as $attr)if(!$r->table('shop_product_attr')->where('attr_id',$attr)->find())$r->table('shop_product_attribute')->where(['attr_id'=>$attr,'lang'=>$lang,'is_custom'=>1])->delete();
    }
    /** Called by ContentService within its purge transaction, before deleting archives. */
    public function purgeProduct(int $aid,string $lang):void
    {
        (new LanguageAccessService($this->repository))->assertLanguages([$lang]);
        $this->language($lang);$this->engines();$r=$this->repository;
        $owner=$r->table('archives')->where('aid',$aid)->lock(true)->find();
        if(!$owner||$owner['lang']!==$lang)throw new InvalidArgumentException('产品参数清理语言与文档实际归属不符');
        $custom=[];foreach($r->table('shop_product_attr')->where('aid',$aid)->lock(true)->select()->toArray() as $value){$definition=$r->table('shop_product_attribute')->where('attr_id',$value['attr_id'])->find();if($definition&&!empty($definition['is_custom'])){$this->customOwned((int)$definition['attr_id'],$aid,$lang);$custom[]=(int)$definition['attr_id'];}}
        $r->table('product_attr')->where('aid',$aid)->delete();$r->table('shop_product_attr')->where('aid',$aid)->delete();if($custom)$r->table('shop_product_attribute')->whereIn('attr_id',array_unique($custom))->where(['lang'=>$lang,'is_custom'=>1])->delete();
    }
}
