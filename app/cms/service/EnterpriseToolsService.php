<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\repository\CmsRepository;
use InvalidArgumentException;
use app\cms\template\TemplateMode;

/** Local business tools; tasks and row writes share the CMS transaction. */
final class EnterpriseToolsService
{
    private LanguageStateStore $state;
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
        $this->state = new LanguageStateStore('enterprise-tools-v1', $this->repository);
    }
    private function language(): string { return CmsContext::current()->lang; }
    private function actor(): int { return function_exists('admin_id') && request() ? (int)admin_id() : 0; }
    public function preview(string $module, array $input): array
    {
        if (!in_array($module, ['tagindex','search_word'], true)) throw new InvalidArgumentException('此模块不支持批量新增');
        $text=$input['values']??'';
        if(!is_string($text)||strlen($text)>65536)throw new InvalidArgumentException('批量内容需为64KiB以内文本');
        $items=array_values(array_unique(array_filter(array_map('trim',preg_split('/\R/u',$text)?:[]),static fn($v)=>$v!=='')));
        if(!$items||count($items)>500)throw new InvalidArgumentException('请输入1至500项，每行一项');
        $field=$module==='tagindex'?'tag':'word';$max=$module==='tagindex'?50:255;$plan=[];
        foreach($items as $value){if(mb_strlen($value)>$max||preg_match('/[\x00-\x1f\x7f]/u',$value))throw new InvalidArgumentException('内容过长或含控制字符：'.mb_substr($value,0,30));
            $existing=$this->repository->table($module)->where(['lang'=>$this->language(),$field=>$value])->order('id')->select()->toArray();
            $plan[]=['value'=>$value,'existing'=>array_column($existing,'id'),'fingerprint'=>hash('sha256',json_encode($existing,JSON_THROW_ON_ERROR))];
        }
        $token=bin2hex(random_bytes(24));$record=['site'=>TemplateMode::siteSignature(),'mode'=>TemplateMode::current(),'module'=>$module,'field'=>$field,'lang'=>$this->language(),'actor'=>$this->actor(),'created_at'=>time(),'items'=>$plan,'results'=>[]];
        $this->state->write($token,$record);
        return ['token'=>$token,'items'=>$plan,'create'=>count(array_filter($plan,static fn($v)=>!$v['existing'])),'skip'=>count(array_filter($plan,static fn($v)=>(bool)$v['existing']))];
    }
    public function run(string $module,string $token): array
    {
        if(!preg_match('/^[a-f0-9]{48}$/D',$token))throw new InvalidArgumentException('请先预览批量内容');
        $this->transactional([$module,'cms_state_records']);
        return $this->state->locked(function()use($module,$token){
            $task=$this->state->read($token);
            if(!$task||($task['site']??'')!==TemplateMode::siteSignature()||($task['mode']??'')!==TemplateMode::current()||$task['module']!==$module||$task['lang']!==$this->language()||$task['actor']!==$this->actor()||time()-$task['created_at']>1800)throw new InvalidArgumentException('批量预览已失效，请重新预览');
            foreach($task['items'] as $index=>$item){
                if(isset($task['results'][$index])&&$task['results'][$index]['state']!=='failed')continue;
                try{$this->repository->transaction(function()use(&$task,$index,$item,$token,$module){
                    $current=$this->repository->table($module)->where(['lang'=>$task['lang'],$task['field']=>$item['value']])->lock(true)->order('id')->select()->toArray();
                    if(!hash_equals($item['fingerprint'],hash('sha256',json_encode($current,JSON_THROW_ON_ERROR))))throw new InvalidArgumentException('记录已变化，请重新预览');
                    if($item['existing'])$result=['state'=>'skipped','value'=>$item['value'],'reason'=>'已存在，保留原值'];
                    else{$id=(new EnterpriseDataService($this->repository))->save($module,[$task['field']=>$item['value'],'lang'=>$task['lang']]);$result=['state'=>'success','value'=>$item['value'],'id'=>$id];}
                    $next=$task;$next['results'][$index]=$result;$this->state->write($token,$next);$task=$next;
                });}catch(\Throwable $error){$task['results'][$index]=['state'=>'failed','value'=>$item['value'],'reason'=>$error instanceof InvalidArgumentException?$error->getMessage():'保存失败，可重试未完成项目'];$this->state->write($token,$task);}
            }
            $counts=['success'=>0,'failed'=>0,'skipped'=>0];foreach($task['results'] as$result)$counts[$result['state']]++;
            return $counts+['token'=>$token,'results'=>array_values($task['results'])];
        });
    }
    private function tag(int $id): array
    {
        $row=$this->repository->table('tagindex')->where(['id'=>$id,'lang'=>$this->language()])->find();
        if(!$row)throw new InvalidArgumentException('TAG不存在或不属于当前语言');
        return $row;
    }
    public function relations(int $id,string $q=''): array
    {
        $tag=$this->tag($id);$query=$this->repository->table('archives')->where(['lang'=>$this->language(),'is_del'=>0])->where('channel','<>',6);
        if(class_exists(ContentAccessService::class))(new ContentAccessService($this->repository))->filterArchiveQuery($query);
        if($q!=='')$query->whereLike('title','%'.addcslashes($q,'%_').'%');
        $rows=$query->field('aid,title,typeid,arcrank')->order('aid desc')->limit(200)->select()->toArray();
        $selected=$this->repository->table('taglist')->where(['tid'=>$id,'lang'=>$this->language()])->column('aid');
        return ['tag'=>$tag,'documents'=>$rows,'selected'=>array_values(array_intersect(array_map('intval',$selected),array_map('intval',array_column($rows,'aid')))),'fingerprint'=>$this->relationFingerprint($id),'limited'=>count($rows)===200];
    }
    private function relationFingerprint(int $id): string
    {
        return hash('sha256',json_encode([$this->tag($id),$this->repository->table('taglist')->where('tid',$id)->order('aid')->select()->toArray()],JSON_THROW_ON_ERROR));
    }
    public function saveRelations(int $id,array $ids,string $fingerprint,array $visibleIds): array
    {
        $this->transactional(['language','tagindex','taglist','archives']);
        $ids=$this->ids($ids);$visibleIds=$this->ids($visibleIds);
        if(array_diff($ids,$visibleIds))throw new InvalidArgumentException('提交了本次候选范围外的文档');
        return $this->repository->transaction(function()use($id,$ids,$fingerprint,$visibleIds){
            $tag=$this->tag($id);$this->repository->table('language')->where('mark',$tag['lang'])->lock(true)->find();$this->repository->table('tagindex')->where('id',$id)->lock(true)->find();
            if(!hash_equals($this->relationFingerprint($id),$fingerprint))throw new InvalidArgumentException('TAG关联已变化，请重新读取');
            $access=class_exists(ContentAccessService::class)?new ContentAccessService($this->repository):null;
            foreach($visibleIds as$aid){$row=$this->repository->table('archives')->where(['aid'=>$aid,'lang'=>$this->language(),'is_del'=>0])->lock(true)->find();if(!$row||(int)$row['channel']===6)throw new InvalidArgumentException('候选文档已变化，请重新读取');if($access)$access->assertArchive($aid);}
            $old=$this->repository->table('taglist')->where(['tid'=>$id,'lang'=>$this->language()])->column('aid');
            $remove=array_values(array_diff(array_intersect(array_map('intval',$old),$visibleIds),$ids));
            if($remove)$this->repository->table('taglist')->where('tid',$id)->whereIn('aid',$remove)->delete();
            foreach(array_diff($ids,array_map('intval',$old))as$aid){$row=$this->repository->table('archives')->where('aid',$aid)->find();$this->repository->table('taglist')->insert(['tid'=>$id,'aid'=>$aid,'typeid'=>$row['typeid'],'tag'=>$tag['tag'],'arcrank'=>$row['arcrank'],'lang'=>$tag['lang'],'add_time'=>time(),'update_time'=>time()]);}
            foreach(array_unique(array_merge($ids,$remove))as$aid){$this->repository->table('archives')->where('aid',$aid)->update(['update_time'=>time()]);}
            $count=$this->repository->table('taglist')->where('tid',$id)->count();$this->repository->table('tagindex')->where('id',$id)->update(['total'=>$count,'update_time'=>time()]);
            return ['added'=>count(array_diff($ids,array_map('intval',$old))),'removed'=>count($remove),'total'=>$count];
        });
    }
    private function ids(array $ids): array
    {
        if(count($ids)>500)throw new InvalidArgumentException('一次最多处理500条');$out=[];foreach($ids as$id){if(!is_scalar($id)||!preg_match('/^[1-9][0-9]{0,9}$/D',(string)$id))throw new InvalidArgumentException('记录ID无效');$out[]=(int)$id;}return array_values(array_unique($out));
    }
    public function searchSettings(): array
    {
        $values=['search_model'=>'default','search_second'=>'60','search_maxnum'=>'5','search_locking'=>'120','title_word_model'=>'0','intellect_arr'=>'["title","seo_keywords"]','search_tabu_words'=>implode("\n",['<','>','"',';',',','@','&','#','\\','*','eval','call_user_func_array','file_put_contents','phpinfo','shell_exec'])];
        foreach($this->repository->table('config')->where(['lang'=>$this->language(),'inc_type'=>'search','is_del'=>0])->select()->toArray()as$row)if(array_key_exists($row['name'],$values))$values[$row['name']]=$row['value'];
        return $values+['fingerprint'=>hash('sha256',json_encode($values,JSON_THROW_ON_ERROR))];
    }
    public function saveSearchSettings(array $input): array
    {
        $old=$this->searchSettings();if(!is_string($input['fingerprint']??null)||!hash_equals($old['fingerprint'],$input['fingerprint']))throw new InvalidArgumentException('搜索设置已变化，请重新读取');
        $next=[];if(!in_array($input['search_model']??null,['default','accurate','intellect'],true))throw new InvalidArgumentException('搜索模式无效');$next['search_model']=$input['search_model'];
        foreach(['search_second','search_maxnum','search_locking']as$key){if(!is_scalar($input[$key]??null)||!preg_match('/^[0-9]{1,6}$/D',(string)$input[$key]))throw new InvalidArgumentException('搜索时间和次数必须为0至999999的整数');$next[$key]=(string)(int)$input[$key];}
        if(!in_array((string)($input['title_word_model']??''),['0','1'],true))throw new InvalidArgumentException('分词匹配选项无效');$next['title_word_model']=(string)$input['title_word_model'];
        $fields=$input['intellect_arr']??[];if(!is_array($fields)||array_diff($fields,['title','seo_keywords','seo_description','content']))throw new InvalidArgumentException('智能搜索字段无效');$next['intellect_arr']=json_encode(array_values(array_unique($fields)),JSON_THROW_ON_ERROR);
        if(!is_string($input['search_tabu_words']??null)||strlen($input['search_tabu_words'])>8192)throw new InvalidArgumentException('搜索禁词最长8KiB');$words=array_values(array_unique(array_filter(array_map('trim',preg_split('/\R/u',mb_strtolower($input['search_tabu_words']))?:[]),static fn($s)=>$s!=='')));$next['search_tabu_words']=implode(PHP_EOL,$words);
        return (new LanguageStateStore('search-settings',$this->repository))->locked(function()use($input,$next){return $this->repository->transaction(function()use($input,$next){if(!hash_equals($this->searchSettings()['fingerprint'],$input['fingerprint']))throw new InvalidArgumentException('搜索设置已变化');foreach($next as$name=>$value){$rows=$this->repository->table('config')->where(['lang'=>$this->language(),'name'=>$name,'is_del'=>0])->lock(true)->select()->toArray();if(count($rows)>1)throw new InvalidArgumentException('搜索配置存在重复，请先修复');if($rows)$this->repository->table('config')->where('id',$rows[0]['id'])->update(['value'=>$value,'update_time'=>time()]);else$this->repository->table('config')->insert(['name'=>$name,'value'=>$value,'lang'=>$this->language(),'inc_type'=>'search','is_del'=>0,'update_time'=>time()]);}return $this->searchSettings();});});
    }
    public function csv(string $module,array $input): string
    {
        if(!in_array($module,['guestbook','search_word'],true))throw new InvalidArgumentException('此模块不提供CSV导出');
        $columns=$module==='search_word'?['id'=>'ID','word'=>'搜索词','searchNum'=>'搜索次数','resultNum'=>'结果数','is_hot'=>'热门','add_time'=>'创建时间']:['aid'=>'ID','typeid'=>'来源ID','is_read'=>'已读','examine'=>'审核','reply'=>'回复','add_time'=>'提交时间'];
        $custom=[];if($module==='guestbook'){foreach($this->repository->table('guestbook_attribute')->where(['lang'=>$this->language(),'is_showlist'=>1,'is_del'=>0])->order('sort_order,attr_id')->select()->toArray()as$field)$custom[(int)$field['attr_id']]=$field['attr_name'];}
        $stream=fopen('php://temp','w+');fwrite($stream,"\xEF\xBB\xBF");fputcsv($stream,array_merge(array_values($columns),array_values($custom)),',','"','');$page=1;$total=0;
        do{$result=(new EnterpriseDataService($this->repository))->list($module,array_replace($input,['lang'=>$this->language(),'page'=>$page,'limit'=>100]));if($result['count']>10000)throw new InvalidArgumentException('超过10000条，请先缩小筛选范围');foreach($result['data']as$row){$values=[];foreach($columns as$key=>$label){$value=$row[$key]??'';if($key==='add_time')$value=$value?date('Y-m-d H:i:s',(int)$value):'';$values[]=$this->csvCell((string)$value);}if($custom){$fields=$this->repository->table('guestbook_attr')->where('aid',$row['aid'])->column('attr_value','attr_id');foreach($custom as$id=>$name)$values[]=$this->csvCell((string)($fields[$id]??''));}fputcsv($stream,$values,',','"','');$total++;}$page++;}while($total<$result['count']&&$result['data']);rewind($stream);$bytes=stream_get_contents($stream);fclose($stream);return $bytes;
    }
    private function transactional(array $tables): void
    {
        foreach($tables as $table){$rows=$this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);if(strtolower((string)($rows[0]['ENGINE']??''))!=='innodb')throw new InvalidArgumentException('此操作要求事务引擎 InnoDB：'.$table);}
    }
    private function csvCell(string $value): string { return preg_match('/^[\s]*[=+@\-]/u',$value)?"'".$value:$value; }
}
