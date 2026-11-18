<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Explicit canonical category relationships; titles are never used as identity. */
final class LanguageAssociationService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function listing(string $target): array
    {
        $r=$this->repository; $policy=new LanguagePolicy($r); $source=$policy->main();
        (new LanguageAccessService($r))->assertLanguages([$source,$target]);
        if ($target===$source || !in_array($target,array_column($policy->all(),'mark'),true)) throw new InvalidArgumentException('请选择其他目标语言');
        $rows=[];
        foreach ((new ContentAccessService($r))->filterCategoryQuery($r->table('arctype'))->where(['lang'=>$source,'is_del'=>0])->order('sort_order asc,id asc')->select()->toArray() as $category) {
            $name=$this->canonical((int)$category['id'],$source) ?? 'tid'.$category['id'];
            $bindings=$r->table('language_attr')->where(['attr_group'=>'arctype','attr_name'=>$name,'lang'=>$target])->select()->toArray();
            $peer=count($bindings)===1?$r->table('arctype')->where(['id'=>(int)$bindings[0]['attr_value'],'lang'=>$target,'is_del'=>0])->find():null;
            if ($peer && !(new ContentAccessService($r))->canCategory((int)$peer['id'])) continue;
            $rows[]=['source_id'=>(int)$category['id'],'source_title'=>$category['typename'],'channel'=>(int)$category['current_channel'],'target_id'=>$peer?(int)$peer['id']:0,'target_title'=>$peer['typename']??'','name'=>$name,'conflict'=>count($bindings)>1 || (bool)($bindings && !$peer)];
        }
        return ['source'=>$source,'target'=>$target,'targets'=>array_values(array_filter($policy->all(),fn($v)=>$v['mark']!==$source && in_array($v['mark'],(new LanguageAccessService($r))->allowedLanguages(),true))),'rows'=>$rows,'target_categories'=>(new ContentAccessService($r))->filterCategoryQuery($r->table('arctype'))->where(['lang'=>$target,'is_del'=>0])->order('sort_order asc,id asc')->field('id,typename,current_channel')->select()->toArray()];
    }

    public function save(int $sourceId,int $targetId,string $target): array
    {
        return $this->repository->transaction(function()use($sourceId,$targetId,$target){
            $r=$this->repository; $source=(new LanguagePolicy($r))->main();
            $r->table('language')->order('id asc')->lock(true)->select();
            (new LanguageAccessService($r))->assertLanguages([$source,$target]);
            if ($source===$target) throw new InvalidArgumentException('源目标语言必须不同');
            $a=$r->table('arctype')->where(['id'=>$sourceId,'lang'=>$source,'is_del'=>0])->lock(true)->find();
            $b=$r->table('arctype')->where(['id'=>$targetId,'lang'=>$target,'is_del'=>0])->lock(true)->find();
            if (!$a || !$b || (int)$a['current_channel']!==(int)$b['current_channel']) throw new InvalidArgumentException('栏目不存在或模型不一致');
            $access=new ContentAccessService($r);$access->assertCategory($a);$access->assertCategory($b);
            $name=$this->canonical($sourceId,$source) ?? 'tid'.$sourceId;
            $peerName=$this->canonical($targetId,$target);
            if ($peerName!==null && $peerName!==$name) throw new InvalidArgumentException('目标栏目已属于其他语言关联，请先解除原关联');
            $old=$r->table('language_attr')->where(['attr_group'=>'arctype','attr_name'=>$name,'lang'=>$target])->lock(true)->select()->toArray();
            if (count($old)>1) throw new InvalidArgumentException('栏目关联重复，请先核对');
            if ($old && (int)$old[0]['attr_value']!==$targetId) throw new InvalidArgumentException('源栏目已有其他目标，请先解除原关联');
            $this->bind($name,$source,$sourceId);$this->bind($name,$target,$targetId);
            if (!$r->table('language_attribute')->where(['attr_group'=>'arctype','attr_name'=>$name])->find()) $r->table('language_attribute')->insert(['attr_group'=>'arctype','attr_name'=>$name,'attr_title'=>$a['typename'],'is_del'=>0,'add_time'=>time(),'update_time'=>time()]);
            return ['source_id'=>$sourceId,'target_id'=>$targetId,'source'=>$source,'target'=>$target,'name'=>$name];
        });
    }

    public function remove(int $sourceId,string $target): void
    {
        $this->repository->transaction(function()use($sourceId,$target){
            $r=$this->repository; $source=(new LanguagePolicy($r))->main();
            $r->table('language')->order('id asc')->lock(true)->select();
            (new LanguageAccessService($r))->assertLanguages([$source,$target]);
            if ($target===$source) throw new InvalidArgumentException('不能解除主语言身份');
            $access=new ContentAccessService($r);$access->assertCategory($sourceId);
            $name=$this->canonical($sourceId,$source) ?? 'tid'.$sourceId;
            foreach($r->table('language_attr')->where(['attr_group'=>'arctype','attr_name'=>$name,'lang'=>$target])->lock(true)->select()->toArray()as$link)$access->assertCategory((int)$link['attr_value']);
            // Only metadata changes; content and old variable names remain untouched.
            $r->table('language_attr')->where(['attr_group'=>'arctype','attr_name'=>$name,'lang'=>$target])->delete();
        });
    }

    /** Re-enabling shared structure is allowed only for complete, consistent groups. */
    public function validateSharedStructure(): void
    {
        $r=$this->repository;$all=(new LanguagePolicy($r))->all();$main=(new LanguagePolicy($r))->main();
        (new LanguageAccessService($r))->assertLanguages(array_column($all,'mark'));
        foreach (['arctype'=>'id','ad_position'=>'id','form'=>'form_id','links_group'=>'id'] as $table=>$primary) {
            $base=$r->table($table); if (isset($r->columns($table)['is_del'])) $base->where('is_del',0);
            foreach ((clone $base)->where('lang',$main)->select()->toArray() as $row) {
                $group=$r->table('language_attr')->where(['attr_group'=>$table,'lang'=>$main,'attr_value'=>(string)$row[$primary]])->select()->toArray();
                if (count($group)!==1) throw new InvalidArgumentException('结构关联不完整：'.$table.'#'.$row[$primary]);
                foreach ($all as $language) {
                    $links=$r->table('language_attr')->where(['attr_group'=>$table,'attr_name'=>$group[0]['attr_name'],'lang'=>$language['mark']])->select()->toArray();
                    $peer=count($links)===1?$r->table($table)->where([$primary=>(int)$links[0]['attr_value'],'lang'=>$language['mark']])->find():null;
                    if (!$peer || !empty($peer['is_del']) || ($table==='arctype' && (int)$peer['current_channel']!==(int)$row['current_channel'])) throw new InvalidArgumentException('语言【'.$language['mark'].'】结构缺失或模型不一致，请先修复关联');
                }
            }
            foreach ((clone $base)->where('lang','<>',$main)->select()->toArray() as $row) {
                $links=$r->table('language_attr')->where(['attr_group'=>$table,'lang'=>$row['lang'],'attr_value'=>(string)$row[$primary]])->select()->toArray();
                if (count($links)!==1 || !$r->table('language_attr')->where(['attr_group'=>$table,'lang'=>$main,'attr_name'=>$links[0]['attr_name']])->find()) throw new InvalidArgumentException('非主语言存在独立结构，不能直接关闭语言分离');
            }
        }
    }

    private function canonical(int $id,string $lang): ?string
    {
        $rows=$this->repository->table('language_attr')->where(['attr_group'=>'arctype','lang'=>$lang,'attr_value'=>(string)$id])->select()->toArray();
        if (count($rows)>1) throw new InvalidArgumentException('栏目存在重复语言绑定');
        return $rows[0]['attr_name']??null;
    }
    private function bind(string $name,string $lang,int $id): void
    {
        $where=['attr_group'=>'arctype','attr_name'=>$name,'lang'=>$lang];
        if (!$this->repository->table('language_attr')->where($where)->find()) $this->repository->table('language_attr')->insert($where+['attr_value'=>(string)$id,'add_time'=>time(),'update_time'=>time()]);
    }
}
