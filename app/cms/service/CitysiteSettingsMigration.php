<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Explicit, fingerprinted migration of legacy cn city settings; reads never migrate. */
final class CitysiteSettingsMigration
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function preview(): array
    {
        $main = (new LanguagePolicy($this->repository))->main();
        $rows = $this->repository->table('config')->whereIn('lang', array_values(array_unique([$main,'cn'])))->whereIn('name',CitysiteService::SETTINGS)->order('lang,name,id')->select()->toArray();
        return self::plan($main, $rows);
    }

    /** Pure plan calculation is shared by read previews and the locked commit. */
    public static function plan(string $main, array $rows): array
    {
        usort($rows, static fn($a,$b) => [$a['lang'],$a['name'],(int)$a['id']] <=> [$b['lang'],$b['name'],(int)$b['id']]);
        $entries=[];$copies=[];$conflicts=[];
        foreach (CitysiteService::SETTINGS as $name) {
            $source=array_values(array_filter($rows,static fn($r)=>$r['lang']==='cn'&&$r['name']===$name&&(int)$r['is_del']===0));
            $target=array_values(array_filter($rows,static fn($r)=>$r['lang']===$main&&$r['name']===$name));
            $active=array_values(array_filter($target,static fn($r)=>(int)$r['is_del']===0));
            $status='absent';$reason='没有旧配置，无需迁移';
            if (count($source)>1||count($active)>1) { $status='conflict';$reason='存在重复配置，需人工核对'; }
            elseif ($main==='cn') { $status='current';$reason='系统主语言已是 cn，无需迁移'; }
            elseif (!$source) { $status='retained';$reason='保留主语言当前配置'; }
            elseif (!$target) { $status='copy';$reason='补齐主语言缺失配置';$copies[]=$source[0]; }
            elseif (!$active) { $status='conflict';$reason='主语言有已删除配置，不能自动恢复或覆盖'; }
            elseif ((string)$active[0]['value']===(string)$source[0]['value']) { $status='same';$reason='值一致，保留现有主语言记录'; }
            else { $status='conflict';$reason='旧 cn 与主语言值不同，不能自动覆盖'; }
            if ($status==='conflict') $conflicts[]=$name;
            $entries[]=['name'=>$name,'status'=>$status,'reason'=>$reason,'source_value'=>$source[0]['value']??null,'target_value'=>$active[0]['value']??null];
        }
        $fingerprint=hash('sha256',json_encode(['version'=>1,'main'=>$main,'rows'=>$rows],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
        return ['version'=>1,'main_language'=>$main,'fingerprint'=>$fingerprint,'entries'=>$entries,'copy_count'=>count($copies),'conflicts'=>$conflicts,'can_apply'=>$main!=='cn'&&!$conflicts&&count($copies)>0,'copies'=>$copies];
    }

    public function apply(string $fingerprint): array
    {
        (new LanguageAccessService($this->repository))->assertLanguages(array_column((new LanguagePolicy($this->repository))->all(),'mark'));
        return (new SiteModulePolicy($this->repository))->locked(function()use($fingerprint){
            foreach (['language','config','cms_state_records'] as $table) {
                $engine=$this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);
                if (strtolower((string)($engine[0]['ENGINE']??''))!=='innodb') throw new InvalidArgumentException('城市配置迁移要求相关表使用InnoDB：'.$table);
            }
            return $this->repository->transaction(function($r)use($fingerprint){
                $r->table('language')->order('id')->lock(true)->select();
                $r->table('config')->whereIn('name',CitysiteService::SETTINGS)->order('id')->lock(true)->select();
                $plan=$this->preview();
                if (!preg_match('/^[a-f0-9]{64}$/D',$fingerprint)||!hash_equals($plan['fingerprint'],$fingerprint)) throw new InvalidArgumentException('城市配置已变化，请重新预览迁移差异');
                if ($plan['conflicts']) throw new InvalidArgumentException('存在配置冲突，未迁移任何配置：'.implode('、',$plan['conflicts']));
                if (!$plan['can_apply']) return ['copied'=>0,'main_language'=>$plan['main_language']];
                foreach ($plan['copies'] as $row) {
                    if ($row['name']==='web_citysite_open') (new SiteModulePolicy($r))->assertEnable('city',!empty($row['value']));
                    unset($row['id']);$row['lang']=$plan['main_language'];$row['update_time']=time();
                    $r->table('config')->insert($r->filter('config',$row));
                }
                (new LanguageStateStore('city-settings-migration',$r))->write($fingerprint,['plan'=>$plan,'copied'=>count($plan['copies']),'completed_at'=>time()]);
                return ['copied'=>count($plan['copies']),'main_language'=>$plan['main_language']];
            });
        });
    }
}
