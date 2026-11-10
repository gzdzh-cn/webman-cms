<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use app\cms\template\TemplateMode;
use InvalidArgumentException;

/** Previewed category-only creation. The receipt and all created rows commit together. */
final class CategoryBatchService
{
    private const VERSION = 1;
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    /** Rows refer only to an earlier row; identifiers never come from a browser-generated ID. */
    public static function normalize(array $input): array
    {
        $items=$input['items']??null;
        if(is_string($items)) {
            if(strlen($items)>131072)throw new InvalidArgumentException('栏目清单过大');
            try{$items=json_decode($items,true,32,JSON_THROW_ON_ERROR);}catch(\JsonException){throw new InvalidArgumentException('栏目清单格式错误');}
        }
        if(!is_array($items)||!array_is_list($items)||!$items||count($items)>100)throw new InvalidArgumentException('每批请输入1至100个栏目');
        $parent=self::number($input['parent_id']??0,true);$channel=self::number($input['current_channel']??0);
        $result=[];
        foreach($items as$i=>$item) {
            if(!is_array($item))throw new InvalidArgumentException('第'.($i+1).'行栏目格式错误');
            $title=$item['typename']??'';$directory=$item['dirname']??'';
            if(!is_string($title)||!is_string($directory)||trim($title)===''||mb_strlen(trim($title))>50)throw new InvalidArgumentException('第'.($i+1).'行栏目名称需为1至50个字符');
            if($directory!==''&&(!preg_match('/^[A-Za-z0-9_-]{1,80}$/D',$directory)))throw new InvalidArgumentException('第'.($i+1).'行目录只允许字母、数字、下划线或连字符，最多80位');
            $parentRow=self::number($item['parent_row']??0,true);
            if($parentRow>$i)throw new InvalidArgumentException('第'.($i+1).'行上级行号必须指向前面的栏目');
            $result[]=['row'=>$i+1,'typename'=>trim($title),'dirname'=>$directory,'parent_row'=>$parentRow,'current_channel'=>self::number($item['current_channel']??$channel)];
        }
        return ['parent_id'=>$parent,'current_channel'=>$channel,'items'=>$result];
    }

    private static function number(mixed $value,bool $zero=false): int
    {
        if((!is_string($value)&&!is_int($value))||!preg_match('/^\d{1,10}$/D',(string)$value)||(int)$value<($zero?0:1)||(int)$value>2147483647)throw new InvalidArgumentException('栏目或模型编号格式错误');
        return (int)$value;
    }

    public function preview(array $input,string $language): array
    {
        $input=self::normalize($input);$this->assertEngines();
        return (new LanguageStateStore('category.batch',$this->repository))->locked(function($store)use($input,$language){
            return $this->repository->transaction(function()use($store,$input,$language){
                $plan=$this->plan($input,$language);$token=bin2hex(random_bytes(24));
                $receipt=$plan+['version'=>self::VERSION,'token'=>$token,'status'=>'preview','owner'=>$this->actor(),'created_at'=>time()];
                $store->write($token,$receipt);
                return $this->publicReceipt($receipt);
            });
        });
    }

    public function execute(string $token,string $language): array
    {
        $this->token($token);$this->assertEngines();
        $result=(new LanguageStateStore('category.batch',$this->repository))->locked(function($store)use($token,$language){
            return $this->repository->transaction(function(CmsRepository $r)use($store,$token,$language){
                // Match the lock order used by CategoryService and structural language operations.
                (new EnterpriseLanguageSyncService($r))->lock();
                $receipt=$store->read($token);$this->assertReceipt($receipt,$language);
                if($receipt['status']==='complete')return $this->publicReceipt($receipt);
                if(time()-(int)$receipt['created_at']>1800)throw new InvalidArgumentException('批建预检已过期，请重新预检');
                $plan=$this->plan($receipt['input'],$language,true);
                if(!hash_equals((string)$receipt['fingerprint'],$plan['fingerprint']))throw new InvalidArgumentException('栏目、模型、语言配置或权限已变化，请重新预检');
                $created=[];$before=array_map('intval',$r->table('arctype')->column('id'));
                foreach($receipt['items']as$item) {
                    $parent=$item['parent_row']?$created[$item['parent_row']-1]['id']:$receipt['input']['parent_id'];
                    $id=(new CategoryService($r))->save(['typename'=>$item['typename'],'dirname'=>$item['dirname'],'parent_id'=>$parent,'current_channel'=>$item['current_channel'],'lang'=>$language],$this->actor());
                    $created[]=['row'=>$item['row'],'id'=>$id,'typename'=>$item['typename'],'dirname'=>$item['dirname'],'parent_id'=>$parent];
                }
                $all=$r->table('arctype')->whereNotIn('id',$before?:[0])->field('id,typename,dirname,parent_id,current_channel,lang')->order('id')->select()->toArray();
                $receipt['status']='complete';$receipt['completed_at']=time();$receipt['created']=$created;$receipt['all_created']=$all;
                $receipt['counts']=['requested'=>count($created),'created'=>count($all),'failed'=>0,'skipped'=>0];
                $store->write($token,$receipt);
                return $this->publicReceipt($receipt);
            });
        });
        \app\cms\compat\Cache::clear();
        return $result;
    }

    public function status(string $token,string $language): array
    {
        $this->token($token);$receipt=(new LanguageStateStore('category.batch',$this->repository))->read($token);
        $this->assertReceipt($receipt,$language);return $this->publicReceipt($receipt);
    }

    private function plan(array $input,string $language,bool $resolved=false): array
    {
        $r=$this->repository;$sync=new EnterpriseLanguageSyncService($r);$sync->lock();$policy=new LanguagePolicy($r);
        $access=new ContentAccessService($r);$access->assertCreateCategory();
        (new LanguageAccessService($r))->assertLanguages([$language]);
        if(!$policy->enabled()&&$language!==$policy->main())throw new InvalidArgumentException('多语言已关闭，请使用系统主语言');
        $settings=$sync->policy($language);
        if($settings['shared']&&$language!==$settings['main'])throw new InvalidArgumentException('共享栏目结构只能从系统主语言批建');
        $languages=$settings['shared']?array_column($policy->all(),'mark'):[$language];
        (new LanguageAccessService($r))->assertLanguages($languages);
        $parent=$input['parent_id']?$r->table('arctype')->where(['id'=>$input['parent_id'],'lang'=>$language,'is_del'=>0])->lock(true)->find():null;
        if($input['parent_id']&&!$parent)throw new InvalidArgumentException('上级栏目不存在、语言不符或已回收');
        if($parent)$access->assertCategory($parent);
        if($parent&&$settings['shared']) {
            $bindings=$r->table('language_attr')->where(['attr_group'=>'arctype','lang'=>$language,'attr_value'=>(string)$parent['id']])->select()->toArray();
            if(count($bindings)!==1)throw new InvalidArgumentException('上级栏目语言关联不完整，请先修复');
            foreach($languages as$mark) {
                $links=$r->table('language_attr')->where(['attr_group'=>'arctype','lang'=>$mark,'attr_name'=>$bindings[0]['attr_name']])->select()->toArray();
                $peer=count($links)===1?$r->table('arctype')->where(['id'=>(int)$links[0]['attr_value'],'lang'=>$mark,'is_del'=>0])->find():null;
                if(!$peer||(int)$peer['current_channel']!==(int)$parent['current_channel'])throw new InvalidArgumentException('上级栏目在语言 '.$mark.' 缺少有效关联');
                $access->assertCategory($peer);
            }
        }
        $models=array_column($r->table('channeltype')->where('is_del',0)->select()->toArray(),null,'id');
        $reserved=[];$items=[];
        foreach($input['items']as$item) {
            $model=$models[$item['current_channel']]??null;
            if(!$model||empty($model['status'])||in_array((int)$model['id'],[51],true)||in_array($model['nid'],['ask','shop','shop_product','users','member'],true))throw new InvalidArgumentException('第'.$item['row'].'行需选择已启用的企业官网内容模型');
            $name=$resolved?$item['dirname']:(new CategoryDirectoryService($r))->resolve($item['typename'],$item['dirname'],$language);
            if(isset($reserved[strtolower($name)]))throw new InvalidArgumentException('第'.$item['row'].'行目录与本批其他栏目重复，请填写不同目录');
            $reserved[strtolower($name)]=true;
            foreach($languages as$mark)if($r->table('arctype')->where(['lang'=>$mark,'dirname'=>$name])->find())throw new InvalidArgumentException('第'.$item['row'].'行目录在语言 '.$mark.' 已存在（含回收站）');
            $item['dirname']=$name;$item['model_title']=$model['title'];$item['parent_title']=$item['parent_row']?$items[$item['parent_row']-1]['typename']:($parent['typename']??'顶级栏目');$items[]=$item;
        }
        $input['items']=array_map(static fn($item)=>array_intersect_key($item,array_flip(['row','typename','dirname','parent_row','current_channel'])),$items);
        $snapshot=[];
        foreach(['arctype'=>'id','channeltype'=>'id','language'=>'id','language_attr'=>'id','config'=>'id','channelfield'=>'id','channelfield_bind'=>'id']as$table=>$pk)$snapshot[$table]=$r->table($table)->order($pk.' asc')->lock(true)->select()->toArray();
        $snapshot['scope']=$access->grants();$snapshot['language_scope']=(new LanguageAccessService($r))->allowedLanguages();
        $snapshot['affected_languages']=$languages;$snapshot['multilingual_enabled']=$policy->enabled();$snapshot['settings']=$policy->settings();
        $snapshot['input']=$input;$snapshot['language']=$language;$snapshot['template_mode']=TemplateMode::current();$snapshot['site_signature']=TemplateMode::siteSignature();
        return ['input'=>$input,'items'=>$items,'language'=>$language,'affected_languages'=>$languages,'template_mode'=>$snapshot['template_mode'],'site_signature'=>$snapshot['site_signature'],'fingerprint'=>hash('sha256',json_encode($snapshot,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)),'counts'=>['requested'=>count($items),'create'=>count($items)*count($languages),'single_pages'=>count(array_filter($items,static fn($item)=>(int)$item['current_channel']===6))*count($languages)]];
    }

    private function assertReceipt(array $receipt,string $language): void
    {
        if(!$receipt||($receipt['version']??null)!==self::VERSION||(int)($receipt['owner']??-1)!==$this->actor())throw new InvalidArgumentException('批建任务不存在或不属于当前账号');
        if(($receipt['language']??'')!==$language||($receipt['site_signature']??'')!==TemplateMode::siteSignature()||($receipt['template_mode']??'')!==TemplateMode::current())throw new InvalidArgumentException('编辑语言、站点或模板模式已变化，请重新预检');
        (new ContentAccessService($this->repository))->assertCreateCategory();
        (new LanguageAccessService($this->repository))->assertLanguages($receipt['affected_languages']);
    }

    private function assertEngines(): void
    {
        foreach(['language','arctype','archives','single_content','language_attr','language_attribute','cms_state_records','nav_list']as$table) {
            $rows=$this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);
            if(strtolower((string)($rows[0]['ENGINE']??''))!=='innodb')throw new InvalidArgumentException('栏目批建要求相关表使用InnoDB：'.$table);
        }
    }
    private function actor(): int { return function_exists('admin_id')?(int)admin_id():0; }
    private function token(string $token): void { if(!preg_match('/^[a-f0-9]{48}$/D',$token))throw new InvalidArgumentException('批建凭据无效'); }
    private function publicReceipt(array $receipt): array { return array_diff_key($receipt,array_flip(['fingerprint','owner','site_signature','template_mode','input'])); }
}
