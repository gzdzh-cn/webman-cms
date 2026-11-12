<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Local language variables only. Import never replaces an existing translation. */
class LanguagePackService
{
    private CmsRepository $repository;
    public function __construct(?CmsRepository $repository = null) { $this->repository = $repository ?? new CmsRepository(); }
    private function policy(): LanguagePolicy { return new LanguagePolicy($this->repository); }
    private function state(string $scope): LanguageStateStore { return new LanguageStateStore($scope, $this->repository); }
    private function access(array $marks): void { (new LanguageAccessService($this->repository))->assertLanguages($marks); }
    private function language(string $mark): string
    {
        if (!in_array($mark, array_column($this->policy()->all(),'mark'),true)) throw new InvalidArgumentException('语言不存在');
        $this->access([$mark]); return $mark;
    }
    private function name(string $name, bool $system = false): string
    {
        $name = strtolower(trim($name));
        if (!preg_match('/^[a-z0-9_-]{1,50}$/D',$name)) throw new InvalidArgumentException('变量名仅支持1至50位字母、数字、下划线和连接符');
        if (!$system && preg_match('/^sys\d+$/D',$name)) throw new InvalidArgumentException('sys加数字为基础变量保留名称');
        return $name;
    }
    private function engines(): void
    {
        foreach (['language','language_pack','cms_state_records'] as $table) {
            $rows = $this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);
            if (strtolower((string)($rows[0]['ENGINE']??'')) !== 'innodb') throw new InvalidArgumentException('语言及持久化表必须使用InnoDB事务引擎');
        }
    }
    private function dictionary(string $language): array { return (new LanguageDictionary($this->repository))->resolve($language); }
    private function definition(string $name, string $source): void
    {
        $store = $this->state('language-definitions');
        $catalog = $store->read('catalog',['entries'=>[]]);
        if (!isset($catalog['entries'][$name])) {
            $catalog['entries'][$name] = ['source'=>$source,'created_at'=>time()];
            ksort($catalog['entries']); $store->write('catalog',$catalog);
        }
    }
    public function listing(array $input): array
    {
        $mark = $this->language((string)($input['lang']??\app\cms\CmsContext::current()->lang ?: $this->policy()->main()));
        $dictionary = $this->dictionary($mark); $rows = [];
        $search = trim((string)($input['q']??''));
        foreach ($dictionary['entries'] as $row) {
            if (!preg_match('/^[a-z0-9_-]{1,50}$/D',$row['name'])) continue;
            if (isset($input['is_syn']) && $input['is_syn'] !== '' && $row['is_syn'] !== (int)(bool)$input['is_syn']) continue;
            if (($input['status']??'') === 'pending' && !$row['pending']) continue;
            if ($search !== '' && stripos($row['name'],$search) === false && mb_stripos($row['value'],$search) === false) continue;
            $row['lang'] = $mark; $rows[] = $row;
        }
        usort($rows, static fn($a,$b) => ($b['pending'] <=> $a['pending']) ?: strcmp($a['name'],$b['name']));
        $count = count($rows); $limit = min(100,max(1,(int)($input['limit']??20))); $page = max(1,(int)($input['page']??1));
        $allowed = (new LanguageAccessService($this->repository))->allowedLanguages();
        $languages = array_values(array_filter($this->policy()->all(), static fn($row)=>in_array($row['mark'],$allowed,true)));
        return ['count'=>$count,'data'=>array_slice($rows,($page-1)*$limit,$limit),'languages'=>$languages,'batch_languages'=>array_column($languages,'mark'),'main'=>$dictionary['main'],'lang'=>$mark,'pending'=>count(array_filter($dictionary['entries'],static fn($row)=>$row['pending'] && preg_match('/^[a-z0-9_-]{1,50}$/D',$row['name']))),'provider'=>'local'];
    }
    public function save(array $input): int
    {
        return $this->repository->transaction(function($r) use ($input) {
            $this->engines();
            $r->table('language')->order('id asc')->lock(true)->select();
            $id = (int)($input['id']??0); $old = $id ? $r->table('language_pack')->where('id',$id)->lock(true)->find() : null;
            if ($id && !$old) throw new InvalidArgumentException('语言变量不存在');
            if ($old) $this->language((string)$old['lang']);
            $rawName = (string)($input['name']??$old['name']??'');
            $known = array_key_exists(strtolower($rawName),LocalLanguageCatalog::variables($this->policy()->main())) || ($old && !empty($old['is_syn']));
            $name = $this->name($rawName,$known);
            $values = $input['values']??$input['value']??[];
            if (!is_array($values) || !$values) throw new InvalidArgumentException('请提供要保存语言的实际译文');
            foreach ($values as $mark=>$value) { $this->language((string)$mark); if (!is_string($value) || strlen($value)>65535) throw new InvalidArgumentException('变量值必须是文本且不超过65535字节'); }
            $previousName = (string)($old['name']??$name);
            $group = $r->table('language_pack')->where('name',$previousName)->lock(true)->select()->toArray(); $byMark = [];
            foreach ($group as $row) { if (isset($byMark[$row['lang']])) throw new InvalidArgumentException('存在重复变量，请先核对'); $byMark[$row['lang']] = $row; }
            if ($name !== $previousName) {
                if ($known || array_filter($group,static fn($row)=>!empty($row['is_syn']))) throw new InvalidArgumentException('基础变量不能更名');
                $this->access(array_column($this->policy()->all(),'mark'));
                if ($r->table('language_pack')->where('name',$name)->find()) throw new InvalidArgumentException('变量名已存在');
                $r->table('language_pack')->where('name',$previousName)->update(['name'=>$name,'update_time'=>time()]);
                $catalogStore=$this->state('language-definitions');$catalog=$catalogStore->read('catalog',['entries'=>[]]);
                if(isset($catalog['entries'][$previousName])){$catalog['entries'][$name]=$catalog['entries'][$previousName];unset($catalog['entries'][$previousName]);$catalogStore->write('catalog',$catalog);}
                foreach($this->policy()->all()as$language){$translationStore=$this->state('language-translations');$oldMeta=$translationStore->read($language['mark'].':'.$previousName);if($oldMeta){$translationStore->write($language['mark'].':'.$name,$oldMeta);$translationStore->remove($language['mark'].':'.$previousName);}}
            }
            $first = $id;
            foreach ($values as $mark=>$value) {
                $data = ['name'=>$name,'value'=>$value,'update_time'=>time()];
                if (isset($byMark[$mark])) $r->table('language_pack')->where('id',$byMark[$mark]['id'])->update($data);
                else { $newId = (int)$r->table('language_pack')->insertGetId($data+['lang'=>$mark,'is_syn'=>$known?1:0,'sort_order'=>100,'add_time'=>time()]); $first = $first ?: $newId; }
                // A value is actual even when an editor intentionally saves an empty string.
                $this->state('language-translations')->write($mark.':'.$name,['status'=>'translated','source'=>'manual','sha256'=>hash('sha256',$value),'updated_at'=>time()]);
            }
            $this->definition($name,'manual');
            return $first;
        });
    }
    public function batch(string $content): array
    {
        $lines = array_values(array_filter(preg_split('/\r\n|\r|\n/',$content),static fn($line)=>trim($line)!==''));
        if (!$lines || count($lines)>200) throw new InvalidArgumentException('批量内容需为1至200行');
        return $this->repository->transaction(function($r) use ($lines) {
            $this->engines(); $r->table('language')->order('id asc')->lock(true)->select(); $marks = array_column($this->policy()->all(),'mark'); $this->access($marks); $main=$this->policy()->main();
            $marks=array_values(array_unique(array_merge([$main],$marks))); $names=[];
            foreach ($lines as $line) {
                $values=explode('=',str_replace('＝','=',$line));
                foreach($values as$value) if(strlen($value)>65535) throw new InvalidArgumentException('变量值过长');
                $id=(int)$r->table('language_pack')->insertGetId(['name'=>'','value'=>$values[0]??'','lang'=>$main,'sort_order'=>100,'add_time'=>time(),'update_time'=>time()]);
                $name='lang'.$id;
                if($r->table('language_pack')->where('name',$name)->find())throw new InvalidArgumentException('自动变量名已存在，批量新增已回滚');
                $r->table('language_pack')->where('id',$id)->update(['name'=>$name]);
                foreach(array_slice($marks,1)as$i=>$mark) if(array_key_exists($i+1,$values))$r->table('language_pack')->insert(['name'=>$name,'value'=>$values[$i+1],'lang'=>$mark,'sort_order'=>100,'add_time'=>time(),'update_time'=>time()]);
                $this->definition($name,'manual'); $names[]=$name;
            }
            return ['names'=>$names,'languages'=>$marks];
        });
    }
    public function delete(array $ids,string $lang): void
    {
        $lang=$this->language($lang);$ids=array_values(array_unique(array_filter(array_map('intval',$ids))));
        if(!$ids)throw new InvalidArgumentException('请选择语言变量');
        $this->repository->transaction(function($r)use($ids,$lang){
            $this->engines();$r->table('language')->order('id asc')->lock(true)->select();$this->access(array_column($this->policy()->all(),'mark'));
            $rows=$r->table('language_pack')->whereIn('id',$ids)->where('lang',$lang)->lock(true)->select()->toArray();
            if(count($rows)!==count($ids))throw new InvalidArgumentException('变量不存在或不属于当前语言');
            $names=array_column($rows,'name');
            if($r->table('language_pack')->whereIn('name',$names)->where('is_syn',1)->find())throw new InvalidArgumentException('基础语言变量不能删除');
            $r->table('language_pack')->whereIn('name',$names)->delete();
            $store=$this->state('language-definitions');$catalog=$store->read('catalog',['entries'=>[]]);
            foreach($names as$name){unset($catalog['entries'][$name]);foreach($this->policy()->all()as$language)$this->state('language-translations')->remove($language['mark'].':'.$name);}
            $store->write('catalog',$catalog);
        });
    }
    public function check(): array { throw new InvalidArgumentException('官方语言包联网功能已停用，请使用本地JSON语言包管理'); }
    public function synchronize(string $mode,string $lang='',string $copyLang='',bool $confirmed=false): array { return $this->check(); }

    public function parse(string $raw,string $language): array
    {
        $pack=LanguageJson::decode($raw);
        if(($pack['format']??null)!==1||($pack['language']??null)!==$language||!is_array($pack['entries']??null)||!array_is_list($pack['entries']))throw new InvalidArgumentException('需要format=1、language与目标一致、entries为数组的本地语言包');
        $max=(int)config('cms_language_pack.max_entries',5000);if(count($pack['entries'])>$max)throw new InvalidArgumentException('语言包条目超过5000项');$entries=[];
        foreach($pack['entries']as$row){
            if(!is_array($row)||!is_string($row['name']??null)||!is_string($row['value']??null)||strlen($row['value'])>65535)throw new InvalidArgumentException('语言包条目必须包含name和文本value');
            if(!empty($row['pending']))throw new InvalidArgumentException('待翻译回退值不能作为实际译文导入');
            $name=$this->name($row['name'],true);if(array_key_exists($name,$entries))throw new InvalidArgumentException('语言包含重复变量名：'.$name);$entries[$name]=$row['value'];
        }
        ksort($entries);return $entries;
    }
    private function actor(): int { if(in_array(PHP_SAPI,['cli','micro'],true)&&!\app\cms\CmsContext::current()->request)return 0; return function_exists('admin_id') ? (int)admin_id() : 0; }
    private function fingerprint(string $lang): string { return hash('sha256',serialize([$this->dictionary($lang)['revision'],\app\cms\template\TemplateMode::current(),\app\cms\template\TemplateMode::siteSignature()])); }
    public function preview(string $raw,string $lang): array
    {
        $lang=$this->language($lang);$entries=$this->parse($raw,$lang);$actual=(new LanguageDictionary($this->repository))->actual($lang);$diff=[];$counts=['inserted'=>0,'same'=>0,'conflicts'=>0,'retained'=>0];
        foreach($entries as$name=>$value){$status=!isset($actual[$name])?'inserted':($actual[$name]['value']===$value?'same':'conflicts');$counts[$status]++;if($status!=='inserted')$counts['retained']++;$diff[]=['name'=>$name,'status'=>$status,'current'=>$actual[$name]['value']??null,'incoming'=>$value];}
        $token=bin2hex(random_bytes(24));$record=['version'=>1,'language'=>$lang,'sha256'=>hash('sha256',$raw),'entries'=>$entries,'fingerprint'=>$this->fingerprint($lang),'expires_at'=>time()+900,'actor'=>$this->actor()];
        $this->state('language-package-preview')->locked(function($store)use($token,$record){$active=0;foreach($store->names()as$name){$old=$store->read($name);if(($old['expires_at']??0)<time())$store->remove($name);else $active++;}if($active>=100)throw new InvalidArgumentException('待确认语言包过多，请稍后重试');$store->write($token,$record);});
        return $counts+['token'=>$token,'language'=>$lang,'sha256'=>$record['sha256'],'diff'=>$diff,'pending'=>count(array_filter($this->dictionary($lang)['entries'],static fn($row)=>$row['pending']&&preg_match('/^[a-z0-9_-]{1,50}$/D',$row['name']))),'expires_at'=>$record['expires_at']];
    }
    public function commit(string $token,string $raw,string $lang): array
    {
        $lang=$this->language($lang);if(!preg_match('/^[a-f0-9]{48}$/D',$token))throw new InvalidArgumentException('请先预览语言包差异');
        return $this->state('language-package-preview')->locked(function()use($token,$raw,$lang){return $this->repository->transaction(function($r)use($token,$raw,$lang){
            $this->engines();$r->table('language')->order('id')->lock(true)->select();
            $store=$this->state('language-package-preview');$preview=$store->read($token);
            if(!$preview||($preview['expires_at']??0)<time()||($preview['language']??'')!==$lang||($preview['actor']??0)!==$this->actor()||!hash_equals((string)$preview['sha256'],hash('sha256',$raw)))throw new InvalidArgumentException('语言包预检已失效，请重新预览');
            $entries=$this->parse($raw,$lang);
            if(isset($preview['result']))return $preview['result'];
            if(!hash_equals((string)$preview['fingerprint'],$this->fingerprint($lang)))throw new InvalidArgumentException('语言变量已变化，请重新预览差异');
            $actual=(new LanguageDictionary($r))->actual($lang);$inserted=0;$retained=0;
            foreach($entries as$name=>$value){if(array_key_exists($name,$actual)){$retained++;continue;}$r->table('language_pack')->insert(['name'=>$name,'value'=>$value,'lang'=>$lang,'is_syn'=>preg_match('/^sys\d+$/D',$name)?1:0,'sort_order'=>100,'add_time'=>time(),'update_time'=>time()]);$this->definition($name,'json');$this->state('language-translations')->write($lang.':'.$name,['status'=>'translated','source'=>'json','sha256'=>hash('sha256',$value),'updated_at'=>time()]);$inserted++;}
            $result=['inserted'=>$inserted,'retained'=>$retained,'language'=>$lang,'complete'=>true];$preview['result']=$result;$preview['fingerprint']=$this->fingerprint($lang);$store->write($token,$preview);return $result;
        });});
    }
    /** Old imports cannot bypass the mandatory difference-preview token. */
    public function import(string $mode,mixed $response,string $lang='',string $copyLang='',bool $confirmed=false): array { throw new InvalidArgumentException('导入协议已改为本地JSON，请先预览差异再确认导入'); }
    public function export(string $lang): array
    {
        $lang=$this->language($lang);$dictionary=$this->dictionary($lang);$entries=[];$pending=[];
        foreach($dictionary['entries']as$row){if(!preg_match('/^[a-z0-9_-]{1,50}$/D',$row['name']))continue;if($row['pending'])$pending[]=['name'=>$row['name'],'status'=>'pending','fallback'=>$row['value']];else$entries[]=['name'=>$row['name'],'value'=>$row['value']];}
        if(count($entries)>5000)throw new InvalidArgumentException('实际变量超过5000项，请分批维护后导出');
        $package=['format'=>1,'language'=>$lang,'main_language'=>$dictionary['main'],'entries'=>$entries,'pending'=>$pending];
        if(strlen(json_encode($package,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR))>(int)config('cms_language_pack.max_bytes',2097152))throw new InvalidArgumentException('语言包超过2 MiB导入上限，请先拆分变量范围后导出');
        return $package;
    }
    public function supplement(string $lang): array
    {
        $lang=$this->language($lang);
        return $this->repository->transaction(function($r)use($lang){$this->engines();$r->table('language')->order('id')->lock(true)->select();$dictionary=$this->dictionary($lang);$count=0;
            foreach($dictionary['entries']as$name=>$row){if(!preg_match('/^[a-z0-9_-]{1,50}$/D',$name))continue;$this->definition($name,'baseline');if($row['pending']){$this->state('language-translations')->write($lang.':'.$name,['status'=>'pending','source'=>'main_fallback','updated_at'=>time()]);$count++;}}
            return ['language'=>$lang,'pending'=>$count,'inserted_translations'=>0,'message'=>'缺失变量已补齐，主语言回退值未写成实际译文'];
        });
    }
    public function rebuild(string $lang): array
    {
        $lang=$this->language($lang);$dictionary=(new LanguageDictionary($this->repository))->cached($lang,true);
        return ['language'=>$lang,'entries'=>count($dictionary['entries']),'revision'=>$dictionary['revision'],'complete'=>true];
    }
}
