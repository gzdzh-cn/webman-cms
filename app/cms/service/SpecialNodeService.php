<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** EyouCMS SpecialNode field contract; atomic ownership checks repair legacy corruption. */
final class SpecialNodeService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }
    private function number(mixed $value, bool $zero = false, bool $signed = false): int
    {
        if ((!is_string($value) && !is_int($value)) || !preg_match($signed ? '/^-?\d{1,10}$/D' : '/^\d{1,10}$/D',(string)$value) || (int)$value>2147483647 || (int)$value<($signed ? -2147483648 : ($zero ? 0 : 1))) throw new InvalidArgumentException('节点数字参数格式错误');
        return (int)$value;
    }
    private function text(mixed $value, int $limit): string
    {
        if (!is_string($value) || mb_strlen($value)>$limit) throw new InvalidArgumentException('节点文字格式错误或超过长度限制');
        return trim($value);
    }
    private function language(string $lang): void
    {
        if (!$this->repository->table('language')->where('mark',$lang)->find()) throw new InvalidArgumentException('语言不存在');
        (new LanguageAccessService($this->repository))->assertLanguages([$lang]);
    }
    private function enterprise(int $channel): bool
    {
        $model=$this->repository->table('channeltype')->where('id',$channel)->find();
        return $model && !in_array($model['nid'],['single','special','guestbook','ask','shop','shop_product','users','member'],true) && !in_array($channel,[6,7,8,51],true);
    }
    private function owner(int $aid,string $lang,bool $lock=false): array
    {
        $this->number($aid);$this->language($lang);
        $query=$this->repository->table('archives')->where(['aid'=>$aid,'lang'=>$lang,'is_del'=>0]);
        $owner=($lock ? $query->lock(true) : $query)->find();
        if (!$owner) throw new InvalidArgumentException('专题不存在、已被回收或不属于当前语言');
        (new ContentAccessService($this->repository))->assertArchive($owner);
        $model=$this->repository->table('channeltype')->where('id',$owner['channel'])->find();
        if (!$model || $model['nid']!=='special') throw new InvalidArgumentException('只能管理专题模型的节点');
        if (!$this->repository->table('arctype')->where(['id'=>$owner['typeid'],'lang'=>$lang,'is_del'=>0])->find()) throw new InvalidArgumentException('专题栏目不存在或已被回收');
        return $owner;
    }
    private function engines(): void
    {
        foreach(['archives','special_node','arctype'] as $table){
            $rows=$this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);
            if(strtolower((string)($rows[0]['ENGINE']??''))!=='innodb') throw new InvalidArgumentException('专题、栏目与节点表必须使用InnoDB事务引擎');
        }
    }
    private function nodeRows(int $aid,string $lang): array
    {
        return $this->repository->table('special_node')->where(['aid'=>$aid,'lang'=>$lang])->order('sort_order asc,node_id desc')->select()->toArray();
    }
    public function list(int $aid,string $lang='cn'): array { return ['owner'=>$this->owner($aid,$lang),'nodes'=>$this->nodeRows($aid,$lang)]; }
    public function topics(string $lang='cn'): array
    {
        $this->language($lang);$models=$this->repository->table('channeltype')->where('nid','special')->column('id');
        $types=(new ContentAccessService($this->repository))->filterCategoryQuery($this->repository->table('arctype'))->where(['lang'=>$lang,'is_del'=>0])->whereIn('current_channel',$models)->column('id');
        return (new ContentAccessService($this->repository))->filterArchiveQuery($this->repository->table('archives'))->where(['lang'=>$lang,'is_del'=>0])->whereIn('channel',$models)->whereIn('typeid',$types)->field('aid,title,typeid,status,lang')->order('aid desc')->select()->toArray();
    }
    public function choices(int $aid,string $lang='cn',string $search=''): array
    {
        $this->owner($aid,$lang);$search=$this->text($search,100);
        $models=$this->repository->table('channeltype')->select()->toArray();$channels=[];
        foreach($models as $model)if($this->enterprise((int)$model['id']))$channels[]=(int)$model['id'];
        $categories=(new ContentAccessService($this->repository))->filterCategoryQuery($this->repository->table('arctype'))->where(['lang'=>$lang,'is_del'=>0])->whereIn('current_channel',$channels)->field('id,typename,parent_id,current_channel')->order('sort_order asc,id asc')->select()->toArray();
        $query=(new ContentAccessService($this->repository))->filterArchiveQuery($this->repository->table('archives'))->where(['lang'=>$lang,'is_del'=>0])->where('aid','<>',$aid)->whereIn('channel',$channels)->whereIn('typeid',array_column($categories,'id'));
        if($search!=='')$query->whereLike('title','%'.addcslashes($search,'%_').'%');
        return ['categories'=>$categories,'archives'=>$query->field('aid,title,typeid,channel,status,arcrank')->order('aid desc')->limit(100)->select()->toArray()];
    }
    private function csv(mixed $input): array
    {
        if(is_string($input))$input=trim($input)===''?[]:explode(',',str_replace('，',',',$input));
        if(!is_array($input)||!array_is_list($input)||count($input)>1000)throw new InvalidArgumentException('文档及排序列表需为最多1000条的数组或逗号分隔值');
        return $input;
    }
    public function save(int $aid,array $input,string $lang='cn'): array
    {
        $this->engines();
        return $this->repository->transaction(function(CmsRepository $r)use($aid,$input,$lang):array{
            $owner=$this->owner($aid,$lang,true);
            $existing=$r->table('special_node')->where('aid',$aid)->lock(true)->select()->toArray();$byId=array_column($existing,null,'node_id');
            foreach($existing as $old)if($old['lang']!==$lang)throw new InvalidArgumentException('专题存在语言归属异常的节点');
            if(!array_key_exists('nodes',$input))return ['owner'=>$owner,'nodes'=>$this->nodeRows($aid,$lang)];
            $nodes=$input['nodes'];if(is_string($nodes)){try{$nodes=json_decode($nodes,true,32,JSON_THROW_ON_ERROR);}catch(\JsonException){throw new InvalidArgumentException('节点JSON格式错误');}}
            if(!is_array($nodes)||!array_is_list($nodes)||count($nodes)>200)throw new InvalidArgumentException('节点列表需为最多200条的数组');
            // Preserve upstream reverse-count suffixes for ordinary duplicate raw values.
            // EyouCMS copyright 2016-2028 海南赞赞网络科技有限公司 (SpecialNode contract).
            foreach(['title','code'] as $field){
                $raw=[];foreach($nodes as $node){if(!is_array($node))throw new InvalidArgumentException('节点格式错误');$raw[]=$node[$field]??'';}
                foreach($nodes as $i=>&$node){$value=$raw[$i];$count=0;foreach(array_slice($raw,$i) as $other)if($value===$other)$count++;if($value!==''&&$count>1){if(!is_string($value))throw new InvalidArgumentException('节点名称格式错误');$node[$field]=$value.'_'.$count;}}unset($node);
            }
            $normalized=[];$seen=[];$codes=[];$titles=[];$now=time();
            foreach($nodes as $position=>$node){
                $id=$this->number($node['node_id']??0,true);
                if($id&&(!isset($byId[$id])||isset($seen[$id])))throw new InvalidArgumentException('节点ID不存在、重复或不属于当前专题及语言');
                if($id)$seen[$id]=true;
                foreach(['aid','lang'] as $field)if(array_key_exists($field,$node)&&(string)$node[$field]!==($field==='aid'?(string)$aid:$lang))throw new InvalidArgumentException('不能修改节点所属专题或语言');
                $title=$this->text($node['title']??'',50);$title=$title===''?'默认节点':$title;
                $code=$this->text($node['code']??'',50);$code=$code===''?'default':$code;
                if(isset($codes[$code])||isset($titles[$title]))throw new InvalidArgumentException('规范化后的节点标识或名称重复，请使用不同值');$codes[$code]=true;$titles[$title]=true;
                $isauto=$this->number($node['isauto']??0,true);$status=$this->number($node['status']??$byId[$id]['status']??1,true);$isDel=$this->number($node['is_del']??$byId[$id]['is_del']??0,true);
                if($isauto>1||$status>1||$isDel>1)throw new InvalidArgumentException('节点开关必须为0或1');
                $typeid=$this->number($node['typeid']??0,true);
                if($typeid){$category=$r->table('arctype')->where(['id'=>$typeid,'lang'=>$lang,'is_del'=>0])->lock(true)->find();if(!$category||!$this->enterprise((int)$category['current_channel']))throw new InvalidArgumentException('自动栏目不存在、语言不符或不是企业内容模型');(new ContentAccessService($r))->assertCategory($category);}
                $ids=array_map(fn($v)=>$this->number(is_string($v)?trim($v):$v),$this->csv($node['aidlist']??''));
                if(count(array_unique($ids))!==count($ids)||in_array($aid,$ids,true))throw new InvalidArgumentException('手选文档重复或引用了专题自身');
                $orders=$this->csv($node['allsorts']??'');if(!$orders)$orders=range(1,count($ids));if(!$ids)$orders=[];
                if(count($ids)!==count($orders))throw new InvalidArgumentException('文档与排序数量不一致');
                $orders=array_map(fn($v)=>$this->number(is_string($v)?trim($v):$v,true,true),$orders);$pairs=[];
                foreach($ids as $i=>$docId){$doc=$r->table('archives')->where(['aid'=>$docId,'lang'=>$lang,'is_del'=>0])->lock(true)->find();if(!$doc||!$this->enterprise((int)$doc['channel'])||!$r->table('arctype')->where(['id'=>$doc['typeid'],'lang'=>$lang,'is_del'=>0])->find())throw new InvalidArgumentException('手选文档不存在、已回收、语言不符或不属于企业模型');(new ContentAccessService($r))->assertArchive($doc);$pairs[]=['aid'=>$docId,'sort'=>$orders[$i],'position'=>$i];}
                usort($pairs,fn($a,$b)=>($a['sort']<=>$b['sort'])?:($a['position']<=>$b['position']));
                $keywords=$this->text($node['keywords']??'',200);$keywords=implode(',',array_filter(array_map('trim',explode(',',str_replace('，',',',$keywords)))));
                $row=$this->number($node['row']??10,true);if(!$row)$row=10;
                $normalized[]=['node_id'=>$id,'title'=>$title,'code'=>$code,'isauto'=>$isauto,'typeid'=>$typeid,'keywords'=>$keywords,'aidlist'=>implode(',',array_column($pairs,'aid')),'allsorts'=>implode(',',array_column($pairs,'sort')),'row'=>$row,'sort_order'=>$this->number($node['sort_order']??($position+1),true,true),'status'=>$status,'is_del'=>$isDel,'update_time'=>$now];
            }
            $delete=array_values(array_diff(array_keys($byId),array_keys($seen)));if($delete)$r->table('special_node')->where(['aid'=>$aid,'lang'=>$lang])->whereIn('node_id',$delete)->delete();
            foreach($normalized as $node){$id=$node['node_id'];unset($node['node_id']);if($id)$r->table('special_node')->where(['node_id'=>$id,'aid'=>$aid,'lang'=>$lang])->update($node);else$r->table('special_node')->insert($node+['aid'=>$aid,'lang'=>$lang,'add_time'=>$now]);}
            return ['owner'=>$owner,'nodes'=>$this->nodeRows($aid,$lang)];
        });
    }
    public function delete(int $aid,array $ids,string $lang='cn'): void
    {
        $ids=array_values(array_unique(array_map(fn($id)=>$this->number($id),$ids)));if(!$ids||count($ids)>200)throw new InvalidArgumentException('请选择最多200个节点');$this->engines();
        $this->repository->transaction(function(CmsRepository $r)use($aid,$ids,$lang):void{$this->owner($aid,$lang,true);$found=$r->table('special_node')->where(['aid'=>$aid,'lang'=>$lang])->whereIn('node_id',$ids)->lock(true)->count();if($found!==count($ids))throw new InvalidArgumentException('节点不存在或不属于当前专题及语言');$r->table('special_node')->where(['aid'=>$aid,'lang'=>$lang])->whereIn('node_id',$ids)->delete();});
    }
}
