<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Enterprise document review and copy. Identities, source bytes and files stay intact. */
final class ArchiveWorkflowService
{
    private const ATTRIBUTES = ['is_head','is_special','is_top','is_recom','is_litpic','is_roll','is_slide','is_diyattr','is_b'];
    private const RELATED = ['images_upload','product_img','download_file','media_file','product_attr','shop_product_attr','product_custom_param','special_node','taglist'];
    // These are source history/shared asset catalogues, not owned content to clone.
    private const HISTORY = ['uploads','download_log','media_log','media_play_record','users_collection','users_footprint','users_forward','users_like','users_notice_tpl_content'];
    private CmsRepository $repository;

    public function __construct(?CmsRepository $repository = null) { $this->repository = $repository ?? new CmsRepository(); }

    private function ids(array $ids, int $maximum = 100): array
    {
        if (!$ids || count($ids)>$maximum || !array_is_list($ids)) throw new InvalidArgumentException('请选择1至'.$maximum.'篇文档');
        foreach ($ids as &$id) {
            if ((!is_string($id)&&!is_int($id)) || !preg_match('/^[1-9][0-9]{0,9}$/D',(string)$id) || (int)$id>2147483647) throw new InvalidArgumentException('文档ID格式错误');
            $id=(int)$id;
        }
        unset($id); $ids=array_values(array_unique($ids)); sort($ids); return $ids;
    }

    private function engines(array $tables): void
    {
        foreach (array_unique($tables) as $table) {
            $rows=$this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);
            if (strtolower((string)($rows[0]['ENGINE']??''))!=='innodb') throw new InvalidArgumentException('操作涉及的表必须使用InnoDB事务引擎：'.$table);
        }
    }

    private function documents(array $ids): array
    {
        $rows=$this->repository->table('archives')->whereIn('aid',$ids)->order('aid asc')->lock(true)->select()->toArray();
        if (count($rows)!==count($ids)) throw new InvalidArgumentException('部分文档不存在，请刷新列表');
        $access=new ContentAccessService($this->repository);
        foreach ($rows as $row) {
            $access->assertArchive($row);
            $category=$this->repository->table('arctype')->where('id',$row['typeid'])->lock(true)->find();
            if (!$category || !empty($category['is_del']) || !empty($row['is_del']) || $category['lang']!==$row['lang'] || (int)$category['current_channel']!==(int)$row['channel']) throw new InvalidArgumentException('文档或所属栏目已被回收，或归属异常');
            $access->assertCategory($category);
        }
        (new LanguageAccessService($this->repository))->assertLanguages(array_values(array_unique(array_column($rows,'lang'))));
        return $rows;
    }

    /** Every selected record is checked before the first update, then committed together. */
    public function batch(array $ids, string $operation, array $attributes = [], string $reason = ''): array
    {
        $ids=$this->ids($ids);
        if (!in_array($operation,['audit','pending','attributes','return','resubmit'],true)) throw new InvalidArgumentException('不支持的文档操作');
        $changes=[];
        if ($operation==='attributes') {
            if (!$attributes || array_diff(array_keys($attributes),self::ATTRIBUTES)) throw new InvalidArgumentException('请选择支持的文档属性');
            foreach ($attributes as $field=>$value) {
                if (!in_array($value,[0,1,'0','1'],true)) throw new InvalidArgumentException('文档属性只能为0或1');
                $changes[$field]=(int)$value;
            }
        } else {
            $changes['arcrank']=['audit'=>0,'pending'=>-1,'return'=>-2,'resubmit'=>-1][$operation];
            if ($operation==='return') {
                $reason=trim($reason);
                if ($reason==='' || mb_strlen($reason)>2000) throw new InvalidArgumentException('请填写不超过2000字的退回原因');
                $changes['reason']=$reason;
            } elseif ($operation==='audit') $changes['reason']='';
        }
        $this->engines(['archives','arctype','taglist']);
        return $this->repository->transaction(function(CmsRepository $r) use($ids,$operation,$changes):array {
            $rows=$this->documents($ids);
            foreach ($rows as $row) if ($operation==='resubmit' && (int)$row['arcrank']!==-2) throw new InvalidArgumentException('只有已退回的文档可以重新提交');
            $r->table('archives')->whereIn('aid',$ids)->update($changes+['update_time'=>time()]);
            if (array_key_exists('arcrank',$changes)) $r->table('taglist')->whereIn('aid',$ids)->update(['arcrank'=>$changes['arcrank'],'update_time'=>time()]);
            foreach (array_unique(array_column($rows,'typeid')) as $typeid) (new ContentService($r))->refreshCount((int)$typeid);
            return ['count'=>count($rows),'aids'=>$ids,'changes'=>$changes];
        });
    }

    /** Retains the legacy single-cell protocol while moving its write behind the same ACL. */
    public function updateStatus(int $aid, string $field, mixed $value): array
    {
        $ids=$this->ids([$aid]);
        if (!in_array($field,[...self::ATTRIBUTES,'is_jump','arcrank','status','sort_order'],true)) throw new InvalidArgumentException('不允许更新该字段');
        if ($field!=='arcrank' && ((!is_int($value)&&!is_string($value)) || !preg_match($field==='sort_order'?'/^-?[0-9]{1,10}$/D':'/^[01]$/D',(string)$value) || (int)$value>2147483647 || (int)$value < -2147483648)) throw new InvalidArgumentException('状态或排序格式错误');
        $this->engines(['archives','arctype','taglist']);
        return $this->repository->transaction(function(CmsRepository $r) use($ids,$field,$value):array {
            $row=$this->documents($ids)[0];
            if ($field==='arcrank' && (int)$row['arcrank']===-2) throw new InvalidArgumentException('文档已退回，请先重新提交后审核');
            $next=$field==='arcrank'?((int)$row['arcrank']===0?-1:0):(int)$value;
            if ($field==='is_jump' && $next===1 && trim((string)($row['jumplinks']??''))==='') throw new InvalidArgumentException('请先在文档编辑页填写跳转地址');
            $r->table('archives')->where('aid',$row['aid'])->update([$field=>$next,'update_time'=>time()]);
            if ($field==='arcrank') $r->table('taglist')->where('aid',$row['aid'])->update(['arcrank'=>$next,'update_time'=>time()]);
            (new ContentService($r))->refreshCount((int)$row['typeid']);
            return ['new_value'=>$next,'aids'=>$ids];
        });
    }

    /** A preview is an actor/site-bound persistent object, never a client-supplied write plan. */
    public function copyPlan(array $ids, int $typeid, int $adminId): array
    {
        $ids=$this->ids($ids);
        if ($typeid<1 || $adminId<1) throw new InvalidArgumentException('请选择目标栏目');
        $store=new LanguageStateStore('archive-copy-v1',$this->repository);
        return $store->locked(fn()=> $this->repository->transaction(function() use($ids,$typeid,$adminId,$store):array {
            $snapshot=$this->copySnapshot($ids,$typeid);
            $token=bin2hex(random_bytes(24));
            $record=['version'=>1,'owner'=>$adminId,'site'=>\app\cms\template\TemplateMode::siteSignature(),'mode'=>\app\cms\template\TemplateMode::current(),'ids'=>$ids,'typeid'=>$typeid,'fingerprint'=>$this->fingerprint($snapshot),'status'=>'planned','created_at'=>time(),'expires_at'=>time()+900];
            $store->write($token,$record);
            return ['token'=>$token,'count'=>count($ids),'target'=>['id'=>$typeid,'name'=>$snapshot['target']['typename']],'documents'=>array_map(fn($row)=>['aid'=>(int)$row['aid'],'title'=>$row['title']],$snapshot['documents']),'related_counts'=>array_map('count',$snapshot['related']),'history_counts'=>$snapshot['history'],'notice'=>'每篇复制一份；文件实体共享，附件关联和自定义参数独立；访问历史及原语言关联不复制。'];
        }));
    }

    public function executeCopy(string $token, int $adminId): array
    {
        if (!preg_match('/^[a-f0-9]{48}$/D',$token) || $adminId<1) throw new InvalidArgumentException('复制预检凭据无效，请重新预检');
        $store=new LanguageStateStore('archive-copy-v1',$this->repository);
        return $store->locked(fn()=> $this->repository->transaction(function(CmsRepository $r) use($token,$adminId,$store):array {
            $record=$store->read($token);
            if (!$record || ($record['version']??0)!==1 || (int)$record['owner']!==$adminId || $record['site']!==\app\cms\template\TemplateMode::siteSignature() || $record['mode']!==\app\cms\template\TemplateMode::current()) throw new InvalidArgumentException('复制任务不属于当前账号或站点');
            // Recheck current source/target permissions even for an idempotent retry.
            $snapshot=$this->copySnapshot($record['ids'],(int)$record['typeid']);
            if ($record['status']==='complete') return array_replace($record['result'],['replayed'=>true]);
            if ($record['status']!=='planned' || (int)$record['expires_at']<time()) throw new InvalidArgumentException('复制预检已过期，请重新预检');
            if (!hash_equals($record['fingerprint'],$this->fingerprint($snapshot))) throw new InvalidArgumentException('源文档、关联或目标栏目已变化，请重新预检');
            $mapping=[]; $now=time(); $target=$snapshot['target']; $addon=$snapshot['addon_table'];
            foreach ($snapshot['documents'] as $source) {
                $new=$this->cloneRow('archives',$source,['aid']);
                $new['title']=$this->copyTitle((string)$source['title'],(int)$source['channel'],$snapshot['model']);
                $new=array_replace($new,['typeid'=>(int)$target['id'],'admin_id'=>$adminId,'users_id'=>0,'is_del'=>0,'del_method'=>0,'joinaid'=>0,'htmlfilename'=>'','ismake'=>0,'add_time'=>$now,'update_time'=>$now]);
                $new=$r->filter('archives',$new);
                $newId=(int)$r->table('archives')->insertGetId($new); $mapping[(int)$source['aid']]=$newId;
                $body=$snapshot['bodies'][(int)$source['aid']];
                $body=$this->cloneRow($addon,$body,['aid']);
                $body['aid']=$newId;
                if (isset($snapshot['schemas'][$addon]['typeid'])) $body['typeid']=(int)$target['id'];
                if (isset($snapshot['schemas'][$addon]['add_time'])) $body['add_time']=$now;
                if (isset($snapshot['schemas'][$addon]['update_time'])) $body['update_time']=$now;
                $r->table($addon)->insert($body);
                $provenance=new HtmltextProvenanceService($r);
                $provenance->copyRecord('archives',(int)$source['aid'],'archives',$newId,$snapshot['html_main']);
                $provenance->copyRecord($addon,(int)$source['aid'],$addon,$newId,$snapshot['html_addon']);
            }
            $custom=[]; $tags=[];
            foreach ($snapshot['related'] as $table=>$rows) foreach ($rows as $row) {
                $sourceId=(int)$row['aid']; $newId=$mapping[$sourceId];
                $new=$this->cloneRow($table,$row,$table==='taglist'?['aid','tid']:['aid']);
                $new['aid']=$newId;
                if (isset($snapshot['schemas'][$table]['typeid']) && $table!=='special_node') $new['typeid']=(int)$target['id'];
                if ($table==='taglist') { $new['tid']=$row['tid']; $tags[]=(int)$row['tid']; }
                if ($table==='shop_product_attr' && !empty($snapshot['definitions']['modern'][(int)$row['attr_id']]['is_custom'])) {
                    $key=$sourceId.':'.$row['attr_id'];
                    if (!isset($custom[$key])) {
                        $definition=$this->cloneRow('shop_product_attribute',$snapshot['definitions']['modern'][(int)$row['attr_id']],['attr_id']);
                        $custom[$key]=(int)$r->table('shop_product_attribute')->insertGetId(array_replace($definition,['add_time'=>$now,'update_time'=>$now]));
                    }
                    $new['attr_id']=$custom[$key];
                }
                foreach (['add_time','update_time'] as $field) if (isset($snapshot['schemas'][$table][$field])) $new[$field]=$now;
                $r->table($table)->insert($new);
            }
            foreach (array_unique($tags) as $tid) $r->table('tagindex')->where('id',$tid)->update(['total'=>$r->table('taglist')->where('tid',$tid)->count(),'update_time'=>$now]);
            (new ContentService($r))->refreshCount((int)$target['id']);
            $result=['count'=>count($mapping),'mapping'=>$mapping,'aids'=>array_values($mapping),'replayed'=>false];
            $record['status']='complete'; $record['completed_at']=$now; $record['result']=$result;
            $store->write($token,$record);
            return $result;
        }));
    }

    private function copySnapshot(array $ids, int $typeid): array
    {
        $r=$this->repository;
        $target=$r->table('arctype')->where('id',$typeid)->lock(true)->find();
        if (!$target || !empty($target['is_del'])) throw new InvalidArgumentException('目标栏目不存在或已回收');
        (new LanguageAccessService($r))->assertLanguages([$target['lang']]);
        $access=new ContentAccessService($r); $access->assertCategory($target);
        $documents=$this->documents($ids);
        $model=$r->table('channeltype')->where('id',$target['current_channel'])->lock(true)->find();
        if (!$model || !in_array($model['nid'],['article','product','images','download','media','special','recruit'],true)) throw new InvalidArgumentException('当前仅支持企业文章、展示产品、图集、下载、视频、专题及招聘模型复制');
        foreach ($documents as $row) {
            if ((int)$row['channel']!==(int)$target['current_channel'] || $row['lang']!==$target['lang']) throw new InvalidArgumentException('只能复制到相同模型、相同语言的栏目');
            if (!empty($row['users_id']) || !empty($row['merchant_id']) || !empty($row['restric_type']) || !empty($row['arc_level_id'])) throw new InvalidArgumentException('会员稿、多商家或受限付费内容不支持企业文档复制');
        }
        $addon=$model['table'].'_content'; $r->identifier($addon);
        $related=[]; $history=[]; $schemas=[];
        $allowed=match($model['nid']) {
            'images'=>['images_upload'], 'product'=>['product_img','product_attr','shop_product_attr','product_custom_param'],
            'download'=>['download_file'], 'media'=>['media_file'], 'special'=>['special_node'], default=>[],
        };
        $allowed[]='taglist';
        // Discover extension tables too: an unrecognised owned row must never disappear silently.
        $prefix=substr($r->fullName('archives'),0,-strlen('archives'));
        $aidTables=$r->connection()->query("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME='aid' ORDER BY TABLE_NAME");
        foreach ($aidTables as $entry) {
            $full=(string)$entry['TABLE_NAME'];
            if (!str_starts_with($full,$prefix)) continue;
            $table=substr($full,strlen($prefix)); $r->identifier($table);
            if (in_array($table,['archives',$addon],true)) continue;
            $rows=$r->table($table)->whereIn('aid',$ids)->lock(true)->select()->toArray();
            if (!$rows) continue;
            if (in_array($table,self::HISTORY,true)) { $history[$table]=count($rows); continue; }
            if (!in_array($table,$allowed,true)) throw new InvalidArgumentException('存在不支持或归属异常的关联表，复制已停止：'.$table);
            foreach ($rows as $row) if (isset($row['lang']) && $row['lang']!==$target['lang']) throw new InvalidArgumentException('关联记录语言不符：'.$table);
            $related[$table]=$this->stableRows($rows); $schemas[$table]=$r->columns($table);
        }
        $bodies=$r->table($addon)->whereIn('aid',$ids)->order('aid asc')->lock(true)->select()->toArray();
        if (count($bodies)!==count($ids) || count(array_unique(array_column($bodies,'aid')))!==count($ids)) throw new InvalidArgumentException('文档附表缺失或重复，请先修复');
        $schemas['archives']=$r->columns('archives'); $schemas[$addon]=$r->columns($addon);
        $definitions=['legacy'=>[],'modern'=>[]]; $references=[]; $byId=array_column($documents,null,'aid');
        foreach (['legacy'=>['product_attr','product_attribute'],'modern'=>['shop_product_attr','shop_product_attribute']] as $kind=>[$valuesTable,$definitionTable]) foreach ($related[$valuesTable]??[] as $value) {
            $attr=(int)$value['attr_id'];
            $definition=$r->table($definitionTable)->where('attr_id',$attr)->lock(true)->find();
            if (!$definition || $definition['lang']!==$target['lang']) throw new InvalidArgumentException('产品参数定义缺失或语言不符');
            if ($kind==='legacy' && (int)$definition['typeid']!==(int)$target['id']) throw new InvalidArgumentException('旧产品参数不适用于目标栏目，请选择原栏目或先处理参数');
            if ($kind==='modern' && (bool)($value['is_custom']??0)!==(bool)$definition['is_custom']) throw new InvalidArgumentException('产品参数值与定义的自定义类型不一致');
            if ($kind==='modern' && empty($definition['is_custom']) && !in_array((int)$definition['list_id'],[0,(int)($byId[(int)$value['aid']]['attrlist_id']??0)],true)) throw new InvalidArgumentException('产品参数定义不属于原产品参数组');
            if ($kind==='modern' && !empty($definition['is_custom'])) {
                $owners=array_unique(array_map('intval',$r->table('shop_product_attr')->where('attr_id',$attr)->lock(true)->column('aid')));
                if ($owners!==[(int)$value['aid']]) throw new InvalidArgumentException('产品自定义参数不是当前产品独占，无法安全复制');
                $schemas[$definitionTable]=$r->columns($definitionTable);
            }
            $definitions[$kind][$attr]=$definition;
        }
        foreach ($documents as $doc) if (!empty($doc['attrlist_id'])) {
            $group=$r->table('shop_product_attrlist')->where('list_id',$doc['attrlist_id'])->lock(true)->find();
            if (!$group || $group['lang']!==$target['lang'] || !empty($group['is_del'])) throw new InvalidArgumentException('产品参数组缺失、已回收或语言不符');
            $references['groups'][(int)$group['list_id']]=$group;
        }
        foreach ($related['taglist']??[] as $row) {
            $tag=$r->table('tagindex')->where('id',$row['tid'])->lock(true)->find();
            if (!$tag || $tag['lang']!==$target['lang'] || $row['lang']!==$target['lang']) throw new InvalidArgumentException('TAG关联缺失或语言不符');
            $references['tags'][(int)$tag['id']]=$tag;
        }
        foreach ($related['special_node']??[] as $node) {
            if ($node['lang']!==$target['lang']) throw new InvalidArgumentException('专题节点语言不符');
            if (!empty($node['typeid'])) {
                $category=$r->table('arctype')->where('id',$node['typeid'])->lock(true)->find();
                if (!$category || $category['lang']!==$target['lang'] || !empty($category['is_del'])) throw new InvalidArgumentException('专题节点引用的栏目无效');
                $nodeModel=$r->table('channeltype')->where('id',$category['current_channel'])->find();
                if (!$nodeModel || !in_array($nodeModel['nid'],['article','product','images','download','media','recruit'],true)) throw new InvalidArgumentException('专题节点引用的栏目模型不受支持');
                $access->assertCategory($category); $references['categories'][(int)$category['id']]=$category;
            }
            $nodeIds=trim((string)$node['aidlist'])===''?[]:explode(',',(string)$node['aidlist']);
            if (count(array_unique($nodeIds))!==count($nodeIds)) throw new InvalidArgumentException('专题节点存在重复文档');
            $orders=trim((string)$node['allsorts'])===''?[]:explode(',',(string)$node['allsorts']);
            if (count($orders)!==count($nodeIds)) throw new InvalidArgumentException('专题节点文档与排序数量不一致');
            foreach ($orders as $sort) if (!preg_match('/^-?[0-9]{1,10}$/D',$sort) || (int)$sort>2147483647 || (int)$sort< -2147483648) throw new InvalidArgumentException('专题节点排序格式无效');
            if ($nodeIds) foreach ($this->ids($nodeIds,1000) as $id) {
                $doc=$this->documents([$id])[0];
                $nodeModel=$r->table('channeltype')->where('id',$doc['channel'])->find();
                if ($doc['lang']!==$target['lang'] || !$nodeModel || !in_array($nodeModel['nid'],['article','product','images','download','media','recruit'],true) || !empty($doc['merchant_id'])) throw new InvalidArgumentException('专题节点引用了不支持的文档');
                $references['archives'][$id]=$doc;
            }
        }
        $htmlMain=[]; $htmlAddon=[];
        foreach ($r->table('channelfield')->where(['channel_id'=>$model['id'],'dtype'=>'htmltext'])->order('id asc')->lock(true)->select()->toArray() as $field) {
            if (!empty($field['ifmain'])) $htmlMain[]=$field['name']; else $htmlAddon[]=$field['name'];
        }
        foreach (['content','content_ey_m'] as $field) if (isset($schemas[$addon][$field])) $htmlAddon[]=$field;
        $htmlMain=array_values(array_unique($htmlMain)); $htmlAddon=array_values(array_unique($htmlAddon));
        $provenance=new HtmltextProvenanceService($r); $formats=[];
        foreach ($documents as $doc) $formats['archives'][(int)$doc['aid']]=$this->sourceFingerprint($provenance,'archives',$doc,$htmlMain);
        foreach ($bodies as $body) $formats[$addon][(int)$body['aid']]=$this->sourceFingerprint($provenance,$addon,$body,$htmlAddon);
        $this->engines(['archives','arctype','cms_state_records',$addon,'taglist','tagindex',...array_keys($related),...array_keys($schemas)]);
        ksort($related); ksort($history); ksort($schemas);
        return ['target'=>$target,'documents'=>$documents,'model'=>$model,'addon_table'=>$addon,'bodies'=>array_column($bodies,null,'aid'),'related'=>$related,'history'=>$history,'schemas'=>$schemas,'definitions'=>$definitions,'references'=>$references,'formats'=>$formats,'html_main'=>$htmlMain,'html_addon'=>$htmlAddon];
    }

    private function sourceFingerprint(HtmltextProvenanceService $service,string $table,array $row,array $fields): array
    {
        $result=[];
        foreach ($fields as $field) if (array_key_exists($field,$row)) $result[$field]=array_intersect_key($service->describe($table,'aid',(int)$row['aid'],$field,(string)$row[$field]),array_flip(['source','sha256','revision']));
        return $result;
    }

    private function stableRows(array $rows): array
    {
        usort($rows,static fn($a,$b)=>strcmp(json_encode($a,JSON_THROW_ON_ERROR),json_encode($b,JSON_THROW_ON_ERROR))); return $rows;
    }
    private function fingerprint(array $snapshot): string { return hash('sha256',json_encode($snapshot,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)); }

    private function cloneRow(string $table,array $row,array $identities): array
    {
        foreach ($this->repository->columns($table) as $name=>$column) if (str_contains(strtolower((string)($column['Extra']??'')),'auto_increment')) unset($row[$name]);
        foreach ($identities as $field) unset($row[$field]);
        return $row;
    }

    private function copyTitle(string $title,int $channel,array $model): string
    {
        $type=$this->repository->columns('archives')['title']['Type']??'varchar(500)';
        preg_match('/\((\d+)\)/',$type,$match); $max=(int)($match[1]??500);
        for ($i=1;$i<=10000;$i++) {
            $suffix=$i===1?' - 副本':' - 副本 '.$i;
            $candidate=mb_substr($title,0,max(0,$max-mb_strlen($suffix))).$suffix;
            if (!empty($model['is_repeat_title']) || !$this->repository->table('archives')->where(['title'=>$candidate,'channel'=>$channel,'is_del'=>0])->lock(true)->find()) return $candidate;
        }
        throw new InvalidArgumentException('副本标题冲突过多，请调整源文档标题');
    }
}
