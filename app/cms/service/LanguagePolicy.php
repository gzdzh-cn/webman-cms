<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use plugin\admin\app\model\Option;

/** One policy for public routing, editing, tags and background generation. */
final class LanguagePolicy
{
    private CmsRepository $repository;
    public function __construct(?CmsRepository $repository = null) { $this->repository=$repository??new CmsRepository(); }
    public function all(): array { return $this->repository->table('language')->order('id asc')->select()->toArray(); }
    public function main(): string
    {
        $main=(string)($this->repository->table('language')->order('id asc')->value('mark')??'');
        if ($main==='') throw new \RuntimeException('未配置系统主语言');
        return $main;
    }
    public function enabled(): bool
    {
        $rows=Option::where('name','cms_multilingual_enabled')->limit(2)->pluck('value')->all();
        if(count($rows)>1) throw new \RuntimeException('多语言总开关配置重复');
        // Until the explicit upgrade, preserve the former effective behaviour.
        return $rows ? (string)$rows[0]==='1' : count(array_filter($this->all(),static fn($l)=>(int)$l['status']===1))>1;
    }
    public function publicLanguages(): array
    {
        $main=$this->main();$enabled=$this->enabled();
        return array_values(array_filter($this->all(),static fn($l)=>(int)$l['status']===1&&($enabled||$l['mark']===$main)));
    }
    public function settings(): array
    {
        $settings=['language_split'=>0,'web_language_switch'=>0];
        foreach($this->repository->table('config')->where(['lang'=>$this->main(),'is_del'=>0])->whereIn('name',array_keys($settings))->select()->toArray() as $row) $settings[$row['name']]=(int)$row['value'];
        return $settings;
    }
    public function fingerprint(): string { return hash('sha256',json_encode([$this->enabled(),$this->all(),$this->settings()],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)); }
    public function migrate(bool $apply=false): array
    {
        $existing=Option::where('name','cms_multilingual_enabled')->exists();$enabled=$this->enabled();
        if($apply&&!$existing) Option::insert(['name'=>'cms_multilingual_enabled','value'=>$enabled?'1':'0']);
        return ['existing'=>$existing,'enabled'=>$enabled,'main'=>$this->main(),'applied'=>$apply&&!$existing];
    }
    public function setEnabled(bool $enabled): void
    {
        (new LanguageAccessService($this->repository))->assertLanguages(array_column($this->all(),'mark'));
        (new SiteModulePolicy($this->repository))->locked(function()use($enabled){
            (new SiteModulePolicy($this->repository))->assertEnable('multilingual',$enabled);
            Option::query()->updateOrInsert(['name'=>'cms_multilingual_enabled'],['value'=>$enabled?'1':'0']);
            \app\cms\compat\Cache::clear();
        });
    }
    public function saveSettings(array $input): array
    {
        (new LanguageAccessService($this->repository))->assertLanguages(array_column($this->all(),'mark'));
        $before=$this->settings();$next=$before;
        foreach($next as $key=>$value) if(array_key_exists($key,$input)){if(!in_array((string)$input[$key],['0','1'],true))throw new \InvalidArgumentException('开关值无效');$next[$key]=(int)$input[$key];}
        if($before['language_split']&&!$next['language_split']) (new LanguageAssociationService($this->repository))->validateSharedStructure();
        $this->repository->transaction(function()use($next){foreach($this->all() as $language)foreach($next as $name=>$value){
            $query=$this->repository->table('config')->where(['name'=>$name,'lang'=>$language['mark'],'is_del'=>0]);$rows=$query->lock(true)->select()->toArray();
            if(count($rows)>1)throw new \InvalidArgumentException('语言配置重复，请先修复');
            if($rows)$this->repository->table('config')->where('id',$rows[0]['id'])->update(['value'=>(string)$value,'update_time'=>time()]);
            else $this->repository->table('config')->insert(['name'=>$name,'lang'=>$language['mark'],'value'=>(string)$value,'inc_type'=>$name==='language_split'?'language':'web','is_del'=>0,'update_time'=>time()]);
        }});
        return $next;
    }
}
