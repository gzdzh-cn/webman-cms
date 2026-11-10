<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Enterprise language copies use original language_attr names and new IDs.
 * Incremental sync retains translations. Explicit document replacement follows
 * original is_frm=1 scope and preserves site structure and recycled documents.
 * Source semantics: Eyou common/model/Language and admin/logic/ArchivesLogic.
 */
class LanguageService
{
    private array $schemas = [];
    private array $maps = [];
    private array $created = [];
    private string $source;
    private string $target;
    private bool $resettingDocuments = false;
    private ?array $eligibleAids = null;
    /** Source category => target archive initialized during this copy only. */
    private array $newSinglePages = [];

    public function __construct(private ?CmsRepository $repository = null, private ?string $currentLanguage = null) { $this->repository ??= new CmsRepository(); }

    public function save(array $input): int
    {
        return $this->repository->transaction(function ($r) use ($input) {
            // Serialize language creation and copying, including missing mark checks.
            $languages = $r->table('language')->order('id asc')->lock(true)->select()->toArray();
            $id = (int)($input['id'] ?? 0); $old = null;
            foreach ($languages as $language) if ((int)$language['id'] === $id) $old = $language;
            if ($id && !$old) throw new InvalidArgumentException('语言不存在');
            $mark = strtolower(trim((string)($input['mark'] ?? $old['mark'] ?? '')));
            if (!preg_match('/^[a-z][a-z0-9_-]{1,9}$/D', $mark)) throw new InvalidArgumentException('语言标识需为2至10位字母、数字、下划线或连字符');
            if ($old && $old['mark'] !== $mark) throw new InvalidArgumentException('语言创建后不能更改标识');
            foreach ($languages as $language) if ($language['mark'] === $mark && (int)$language['id'] !== $id) {
                $receipt = (new LanguageStateStore('language.create', $r))->read($mark);
                if (!$id && isset($receipt['input_sha']) && hash_equals($receipt['input_sha'], $this->creationHash($input))) return (int)$language['id'];
                throw new InvalidArgumentException('该语言已存在');
            }
            foreach (['status','is_home_default','target'] as $flag) {
                if (array_key_exists($flag,$input) && (!is_scalar($input[$flag]) || !in_array((string)$input[$flag],['0','1'],true))) throw new InvalidArgumentException('语言开关参数无效：'.$flag);
            }
            $data = $r->filter('language', $input); unset($data['is_admin_default']); $data['mark'] = $mark; $data['update_time'] = time();
            $title = trim((string)($data['title'] ?? $old['title'] ?? ''));
            if ($title === '' || mb_strlen($title) > 100) throw new InvalidArgumentException('语言名称不能为空或超过100个字符');
            $data['title'] = $title;
            $url = trim((string)($data['url'] ?? $old['url'] ?? ''));
            if ($url !== '' && !preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
            if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || mb_strlen($url) > 200)) throw new InvalidArgumentException('语言域名格式错误');
            $data['url'] = $url;
            if (!empty($data['is_home_default'])) (new LanguageAccessService($r))->assertLanguages(array_column($languages,'mark'));
            if ($old) {
                (new LanguageAccessService($r))->assertLanguages([$mark]);
                if ((int)$old['id'] === (int)$languages[0]['id'] && isset($data['status']) && !(int)$data['status']) throw new InvalidArgumentException('系统主语言不能停用');
                if (!empty($old['is_home_default']) && array_key_exists('is_home_default',$data) && !(int)$data['is_home_default']) throw new InvalidArgumentException('请先将其他已启用语言设为前台默认语言');
                if (!empty($old['is_home_default']) && isset($data['status']) && !$data['status']) throw new InvalidArgumentException('不能关闭默认前台语言');
                $r->table('language')->where('id', $id)->update($data);
            } else {
                $source = (new LanguagePolicy($r))->main();
                (new ContentAccessService($r))->assertCreateCategory();
                (new LanguageAccessService($r))->assertLanguages(array_column($languages, 'mark'));
                if ($source === $mark || !$r->table('language')->where('mark', $source)->find()) throw new InvalidArgumentException('复制源语言不存在');
                // Never erase abandoned or unrelated data for a new mark.
                foreach (['config','arctype','archives','language_attr'] as $table) if ($r->table($table)->where('lang', $mark)->find()) throw new InvalidArgumentException('目标语言已有数据，不能覆盖；请先检查数据归属');
                $data['status'] = 0; $data['is_home_default'] = 0; $data['is_admin_default'] = 0;
                $id = (int)$r->table('language')->insertGetId($data + ['add_time' => time()]);
                $this->copyData($source, $mark, false, true);
                (new LanguageStateStore('language.create', $r))->write($mark, ['id'=>$id,'input_sha'=>$this->creationHash($input),'source'=>$source,'created_at'=>time()]);
            }
            if (!empty($data['is_home_default'])) {
                if (!(int)($data['status'] ?? $old['status'] ?? 1)) throw new InvalidArgumentException('默认前台语言必须开启');
                $r->table('language')->where('id', '<>', $id)->update(['is_home_default' => 0, 'update_time' => time()]);
                foreach ($r->table('language')->column('mark') as $lang) $this->setConfig($lang, 'system_home_default_lang', $mark, 'system');
            }
            // Only creation changes the count; a local title/status edit must
            // not write configuration for languages outside the editor's scope.
            if (!$old) {
                $marks = $r->table('language')->column('mark');
                foreach ($marks as $lang) $this->setConfig($lang, 'system_langnum', (string)count($marks), 'system');
            }
            // Selector visibility is an independent setting and is never forced here.
            return $id;
        });
    }

    public function synchronize(int $id, string $source = '', bool $documents = true, string $planToken = ''): array
    {
        if ($planToken === '') throw new InvalidArgumentException('请先预览同步清单');
        return $this->execute($planToken, 'new', $id);
    }

    /** Original Language/sync is_frm=1: documents only, never a language reset. */
    private function replaceDocuments(int $id, string $source, bool $confirmOverwrite = false): array
    {
        if (!$confirmOverwrite) throw new InvalidArgumentException('请明确确认覆盖目标语言非回收站文档并更新单页正文');
        return $this->repository->transaction(function ($r) use ($id, $source) {
            $languages = $r->table('language')->order('id asc')->lock(true)->select()->toArray();
            $target = $origin = null;
            foreach ($languages as $row) { if ((int)$row['id'] === $id) $target = $row; if ($row['mark'] === $source) $origin = $row; }
            if (!$target || !$origin || $target['mark'] === $source) throw new InvalidArgumentException('复制源与目标语言必须存在且不同');
            if ($target['mark'] === (new LanguagePolicy($r))->main()) throw new InvalidArgumentException('不能覆盖系统主语言');
            $this->source = $source; $this->target = $target['mark']; $this->maps = []; $this->created = [];
            $models = $r->table('channeltype')->where('is_del', 0)->select()->toArray();
            $models = array_column(array_filter($models, fn($model) => $this->enterpriseModel($model)), null, 'id');
            $writeTables = ['archives','arctype','language_attr','language_attribute','language_archives_copy_log','tagindex','taglist','images_upload','product_img','download_file','media_file','product_attr','shop_product_attr','product_custom_param','special_node','single_content','channelfield_bind'];
            foreach ($models as $model) $writeTables[] = $model['table'].'_content';
            foreach (array_unique($writeTables) as $table) {
                $engine = $r->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$r->fullName($table)]);
                if (strtolower((string)($engine[0]['ENGINE']??'')) !== 'innodb') throw new InvalidArgumentException('覆盖要求全部相关表使用InnoDB事务引擎');
            }
            // Use existing canonical bindings; reset never creates site structure.
            foreach (['arctype'=>['arctype','tid'], 'shop_product_attrlist'=>['shop_product_attrlist','attrlist_'], 'product_attribute'=>['product_attribute','attr_'], 'shop_product_attribute'=>['shop_product_attribute','attribute_']] as $table => [$group,$prefix]) {
                foreach ($this->rows($table) as $row) {
                    $old = (int)$row[$this->primary($table)];
                    $binding = $r->table('language_attr')->where(['lang'=>$source,'attr_group'=>$group,'attr_value'=>(string)$old])->order('id asc')->find();
                    $name = $binding['attr_name'] ?? $prefix.$old;
                    $mapped = $r->table('language_attr')->where(['lang'=>$this->target,'attr_group'=>$group,'attr_name'=>$name])->order('id desc')->find();
                    if (!$mapped) continue;
                    $copy = $r->table($table)->where([$this->primary($table)=>(int)$mapped['attr_value'],'lang'=>$this->target])->find();
                    if (!$copy || (isset($copy['is_del']) && $copy['is_del'])) throw new InvalidArgumentException('目标语言结构映射失效，覆盖已取消');
                    if ($table === 'arctype' && (int)$row['current_channel'] !== (int)$copy['current_channel']) throw new InvalidArgumentException('源目标栏目模型不一致，覆盖已取消');
                    $this->maps[$group][$old] = (int)$mapped['attr_value'];
                }
            }
            $items = $r->table('archives')->where(['lang'=>$this->target,'is_del'=>0,'users_id'=>0])->whereIn('channel',array_keys($models))->whereIn('typeid',array_values($this->maps['arctype'] ?? []))->where('channel','<>',6)->lock(true)->select()->toArray();
            $aids = array_map('intval',array_column($items,'aid'));
            // Business references use both aid and product_id in the original schema.
            // guestbook.aid / guestbook_attr.aid identify independent submissions.
            $references = [
                'aid'=>['article_pay','download_log','media_log','media_play_record','product_netdisk','product_spec_data','product_spec_value','product_spec_data_handle','product_spec_value_handle','product_users_discount','shop_goods_label_bind','discount_active_goods','discount_goods','sharp_active_goods','sharp_goods','users_collection','users_footprint','users_forward','users_like','uploads'],
                'product_id'=>['article_order','download_order','media_order','shop_cart','shop_order_comment','shop_order_details','shop_order_service'],
            ];
            if ($aids) {
                foreach ($references as $column=>$tables) foreach ($tables as $table) {
                    if ($r->table($table)->whereIn($column,$aids)->lock(true)->find()) throw new InvalidArgumentException('目标文档关联商城或会员业务数据，覆盖已取消');
                }
                foreach ($aids as $aid) if ($r->table('shop_coupon')->whereRaw('FIND_IN_SET(?,product_id)',[$aid])->lock(true)->find()) throw new InvalidArgumentException('目标文档关联商城优惠券，覆盖已取消');
                if ($r->table('archives')->whereNotIn('aid',$aids)->whereIn('joinaid',$aids)->lock(true)->find()) throw new InvalidArgumentException('保留文档仍关联待覆盖文档，覆盖已取消');
                foreach ($aids as $aid) if ($r->table('special_node')->whereNotIn('aid',$aids)->whereRaw('FIND_IN_SET(?,aidlist)',[$aid])->lock(true)->find()) throw new InvalidArgumentException('保留专题仍引用待覆盖文档，覆盖已取消');
            }
            $singleUpdates = [];
            foreach ($this->rows('archives') as $row) {
                if (($this->eligibleAids !== null && !in_array((int)$row['aid'],$this->eligibleAids,true)) || (int)$row['channel'] !== 6 || (int)$row['users_id'] > 0 || !isset($this->maps['arctype'][(int)$row['typeid']])) continue;
                $type = $this->maps['arctype'][(int)$row['typeid']];
                $copies = $r->table('archives')->where(['lang'=>$this->target,'is_del'=>0,'channel'=>6,'typeid'=>$type,'users_id'=>0])->lock(true)->select()->toArray();
                if (count($copies)>1) throw new InvalidArgumentException('目标栏目存在多个单页文档，覆盖已取消');
                if (!$copies) continue;
                $body = $r->table('single_content')->where('aid',$row['aid'])->find();
                if (!$body || !$r->table('single_content')->where('aid',$copies[0]['aid'])->find()) throw new InvalidArgumentException('单页附表缺失，覆盖已取消');
                $singleUpdates[] = [(int)$row['aid'],(int)$copies[0]['aid'],array_intersect_key($body,array_flip(['content','content_ey_m']))];
                $this->maps['archives'][(int)$row['aid']] = (int)$copies[0]['aid'];
            }
            // Reject partial reference rewrites before deleting any target row.
            $sourceItems = array_filter($this->rows('archives'), fn($row) => isset($models[$row['channel']]) && (int)$row['channel'] !== 6 && (int)$row['users_id'] === 0 && ($this->eligibleAids === null || in_array((int)$row['aid'], $this->eligibleAids, true)));
            $sourceAids = array_merge(array_map('intval',array_column($sourceItems,'aid')),array_keys($this->maps['archives'] ?? []));
            foreach ($sourceItems as $row) {
                if ((int)$row['attrlist_id'] && !isset($this->maps['shop_product_attrlist'][(int)$row['attrlist_id']])) throw new InvalidArgumentException('源产品参数组缺少有效目标绑定，覆盖已取消');
                if ((int)$row['joinaid'] && !in_array((int)$row['joinaid'],$sourceAids,true)) throw new InvalidArgumentException('源文档关联本次范围外文档，覆盖已取消');
                foreach (array_filter(array_map('intval',explode(',',(string)$row['stypeid']))) as $type) if (!isset($this->maps['arctype'][$type])) throw new InvalidArgumentException('源文档副栏目映射缺失，覆盖已取消');
                foreach (['product_attr'=>'product_attribute','shop_product_attr'=>'shop_product_attribute'] as $table=>$group) foreach ($r->table($table)->where('aid',$row['aid'])->select()->toArray() as $parameter) if (!isset($this->maps[$group][(int)$parameter['attr_id']])) throw new InvalidArgumentException('源产品参数缺少有效目标绑定，覆盖已取消');
                foreach ($r->table('special_node')->where('aid',$row['aid'])->select()->toArray() as $node) {
                    foreach (array_filter(array_map('intval',explode(',',(string)$node['aidlist']))) as $aid) if (!in_array($aid,$sourceAids,true)) throw new InvalidArgumentException('源专题关联本次范围外文档，覆盖已取消');
                    foreach (array_filter(array_map('intval',explode(',',(string)$node['typeid']))) as $type) if (!isset($this->maps['arctype'][$type])) throw new InvalidArgumentException('源专题栏目映射缺失，覆盖已取消');
                }
            }
            $tagIds = $aids ? $r->table('taglist')->whereIn('aid',$aids)->column('tid') : [];
            foreach ($items as $item) {
                $table = $models[$item['channel']]['table'].'_content'; $this->schema($table);
                $provenance=new HtmltextProvenanceService($r);
                $provenance->forgetRecord($table,'aid',(int)$item['aid'],$this->htmltextFields((int)$item['channel'],$table));
                $provenance->forgetRecord('archives','aid',(int)$item['aid'],$this->htmltextFields((int)$item['channel'],'archives'));
                $r->table($table)->where('aid',$item['aid'])->delete();
            }
            if ($aids) {
                foreach (['taglist','images_upload','product_img','download_file','media_file','product_attr','shop_product_attr','product_custom_param','special_node'] as $table) $r->table($table)->whereIn('aid',$aids)->delete();
                $r->table('language_archives_copy_log')->where('lang',$this->target)->whereIn('newid',$aids)->delete();
                $r->table('language_attr')->where(['lang'=>$this->target,'attr_group'=>'archives'])->whereIn('attr_value',array_map('strval',$aids))->delete();
                $r->table('archives')->whereIn('aid',$aids)->delete();
            }
            $this->resettingDocuments = true;
            try { $this->documents(true); } finally { $this->resettingDocuments = false; }
            foreach ($singleUpdates as [$sourceAid,$aid,$body]) {
                $r->table('single_content')->where('aid',$aid)->update($body);
                (new HtmltextProvenanceService($r))->copyRecord('single_content',$sourceAid,'single_content',$aid,array_keys($body));
            }
            foreach ($this->maps['arctype'] ?? [] as $sourceType=>$targetType) foreach ($r->table('channelfield_bind')->where('typeid',$sourceType)->select()->toArray() as $binding) if (!$r->table('channelfield_bind')->where(['typeid'=>$targetType,'field_id'=>$binding['field_id']])->find()) $this->insert('channelfield_bind',$binding,['typeid'=>$targetType]);
            foreach (array_unique($tagIds) as $tid) $r->table('tagindex')->where('id',$tid)->update(['total'=>$r->table('taglist')->where('tid',$tid)->count()]);
            foreach ($r->table('arctype')->where('lang',$this->target)->column('id') as $type) (new ContentService($r))->refreshCount((int)$type);
            return ['source'=>$source,'target'=>$this->target,'deleted'=>count($aids),'single_updated'=>count($singleUpdates),'created'=>$this->created,'maps'=>$this->maps];
        });
    }

    /** Read-only data preview; only the approval receipt itself is persisted. */
    public function preview(int $id, string $mode = 'new'): array
    {
        if (!in_array($mode, ['new','all'], true)) throw new InvalidArgumentException('同步方式无效');
        return $this->repository->transaction(function () use ($id,$mode) {
            $plan = $this->buildPlan($id,$mode);
            $this->assertTransactional($plan);
            $token = bin2hex(random_bytes(24));
            $receipt = $plan + ['token'=>$token,'plan_token'=>$token,'owner'=>$this->actor(),'expires_at'=>time()+900,'status'=>'preview','created_at'=>time()];
            (new LanguageStateStore('language.sync', $this->repository))->write($token,$receipt);
            return $this->publicPlan($receipt);
        });
    }

    public function resetDocuments(int $id, string $source = '', bool $confirmOverwrite = false, string $planToken = ''): array
    {
        if (!$confirmOverwrite) throw new InvalidArgumentException('请确认完整覆盖清单');
        if ($planToken === '') throw new InvalidArgumentException('请先预览全部同步清单');
        return $this->execute($planToken,'all',$id);
    }

    /** Completed tokens are receipts; retry never reapplies a destructive copy. */
    public function execute(string $token, ?string $expectedMode = null, ?int $expectedId = null): array
    {
        if (!preg_match('/^[a-f0-9]{48}$/D',$token)) throw new InvalidArgumentException('同步预检凭据无效');
        $store = new LanguageStateStore('language.sync',$this->repository);
        return $store->locked(function () use ($token,$store,$expectedMode,$expectedId) {
            $receipt = $store->read($token);
            $this->assertReceipt($receipt,$expectedMode,$expectedId);
            if (($receipt['status'] ?? '') === 'complete') return $receipt['result'];
            if (($receipt['expires_at'] ?? 0) < time()) throw new InvalidArgumentException('预检已过期，请重新预览');
            try {
                return $this->repository->transaction(function () use ($receipt,$token,$store) {
                    $plan = $this->buildPlan((int)$receipt['target_id'],$receipt['mode']);
                    if (!hash_equals($receipt['fingerprint'],$plan['fingerprint'])) throw new InvalidArgumentException('源数据、目标内容或栏目关联已变化，请重新预览');
                    $this->eligibleAids = array_values(array_unique(array_merge($plan['eligible_aids'],$plan['existing_aids'] ?? [])));
                    $this->assertTransactional($plan);
                    $before = $this->captureTarget($plan['target']);
                    if ($plan['mode'] === 'all') $result = $this->replaceDocuments((int)$plan['target_id'],$plan['source'],true);
                    else {
                        $this->created = []; $this->newSinglePages = [];
                        $this->documents();
                        $result = ['source'=>$this->source,'target'=>$this->target,'created'=>$this->created,'maps'=>$this->maps,'deleted'=>0,'single_updated'=>0];
                    }
                    $success = count($plan['create']) + count($plan['single_update']);
                    $result = array_replace($result,['status'=>'complete','state'=>'complete','plan_token'=>$token,'success'=>$success,'failed'=>0,'skipped'=>count($plan['skipped']),'details'=>$plan['skipped'],'counts'=>['success'=>$success,'failed'=>0,'skip'=>count($plan['skipped']),'delete'=>count($plan['delete']),'single_update'=>count($plan['single_update'])]]);
                    $after = $this->captureTarget($plan['target']);
                    $store->write($token,array_replace($receipt,['status'=>'complete','completed_at'=>time(),'before'=>$before,'after_sha'=>$this->hash($after),'result'=>$result]));
                    return $result;
                });
            } catch (\Throwable $error) {
                // Business mutations and their checkpoint rolled back together.
                $store->write($token,array_replace($receipt,['status'=>'failed','last_error'=>$error->getMessage(),'public_error'=>$error instanceof InvalidArgumentException?$error->getMessage():'同步未完成，内容已回滚；请检查服务日志后重试','failed_at'=>time()]));
                throw $error;
            } finally { $this->eligibleAids = null; }
        });
    }

    /** Explicit rollback refuses to overwrite edits made after this task. */
    public function restore(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{48}$/D',$token)) throw new InvalidArgumentException('任务编号无效');
        $store=new LanguageStateStore('language.sync',$this->repository);
        return $store->locked(function()use($token,$store){
            return $this->repository->transaction(function()use($token,$store){
                $receipt=$store->read($token);$this->assertReceipt($receipt);
                if (($receipt['status']??'')==='restored') return ['status'=>'restored','plan_token'=>$token];
                if (($receipt['status']??'')!=='complete' || !isset($receipt['before'],$receipt['after_sha'])) throw new InvalidArgumentException('任务未完成，不能恢复');
                $r=$this->repository;$r->table('language')->order('id asc')->lock(true)->select();
                (new ContentAccessService($r))->assertLanguageContents([$receipt['source'],$receipt['target']]);
                // A restore also reinserts previously deleted documents from its snapshot.
                $contentAccess=new ContentAccessService($r);
                foreach($receipt['before']['archives']??[] as$archive)$contentAccess->assertArchive($archive);
                $current=$this->captureTarget($receipt['target'],array_key_exists('__htmltext',$receipt['before']));
                if (!hash_equals($receipt['after_sha'],$this->hash($current))) throw new InvalidArgumentException('目标内容在同步后已修改，恢复已停止，请人工核对前镜像');
                $deletion = array_merge(array_diff_key($current,['archives'=>1,'tagindex'=>1,'__pending'=>1,'__htmltext'=>1]), array_intersect_key($current,['archives'=>1,'tagindex'=>1]));
                foreach ($deletion as $table=>$rows) {
                    $primary=$this->primary($table);$ids=array_column($rows,$primary);
                    if ($ids) $r->table($table)->whereIn($primary,$ids)->delete();
                }
                $insertion = array_merge(array_intersect_key($receipt['before'],['archives'=>1,'tagindex'=>1]),array_diff_key($receipt['before'],['archives'=>1,'tagindex'=>1,'__pending'=>1,'__htmltext'=>1]));
                foreach ($insertion as $table=>$rows) {
                    $primary=$this->primary($table);
                    foreach ($rows as $row) {
                        if ($r->table($table)->where($primary,$row[$primary])->find()) throw new InvalidArgumentException('恢复ID已被其他记录占用，已回滚');
                        $r->table($table)->insert($row);
                    }
                }
                $pending = new LanguageStateStore('language.pending',$r);
                foreach (array_keys($current['__pending'] ?? []) as $key) $pending->remove($key);
                foreach ($receipt['before']['__pending'] ?? [] as $key=>$record) $pending->write($key,$record);
                if(array_key_exists('__htmltext',$receipt['before'])) {
                    $provenance=new LanguageStateStore(HtmltextProvenanceService::SCOPE,$r);
                    foreach(array_keys($current['__htmltext']??[])as$key)$provenance->remove($key);
                    foreach($receipt['before']['__htmltext']as$key=>$record)$provenance->write($key,$record);
                }
                foreach ($r->table('arctype')->where('lang',$receipt['target'])->column('id') as $id) (new ContentService($r))->refreshCount((int)$id);
                $store->write($token,array_replace($receipt,['status'=>'restored','restored_at'=>time()]));
                return ['status'=>'restored','plan_token'=>$token,'message'=>'已恢复任务前目标内容；原始快照保留'];
            });
        });
    }

    public function task(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{48}$/D',$token)) throw new InvalidArgumentException('任务编号无效');
        $receipt = (new LanguageStateStore('language.sync',$this->repository))->read($token);
        $this->assertReceipt($receipt,null,null,false);
        return $this->publicPlan($receipt);
    }

    /** Keep previous receipts visible while multilingual editing is paused. */
    public function history(int $limit = 20): array
    {
        $limit = max(1,min(100,$limit));
        $store = new LanguageStateStore('language.sync',$this->repository);
        $owner = $this->actor();
        $allowed = (new LanguageAccessService($this->repository))->allowedLanguages();
        $site = \app\cms\template\TemplateMode::siteSignature();
        $mode = \app\cms\template\TemplateMode::current();
        $receipts = [];
        foreach ($store->names() as $token) {
            $receipt = $store->read((string)$token);
            if ((int)($receipt['owner'] ?? -1) !== $owner
                || ($receipt['site_signature'] ?? '') !== $site
                || ($receipt['template_mode'] ?? '') !== $mode
                || !in_array($receipt['source'] ?? '',$allowed,true)
                || !in_array($receipt['target'] ?? '',$allowed,true)) continue;
            $receipts[] = $this->publicPlan($receipt);
        }
        usort($receipts,static fn(array $a,array $b): int => ((int)($b['created_at'] ?? 0) <=> (int)($a['created_at'] ?? 0)) ?: strcmp((string)($b['token'] ?? ''),(string)($a['token'] ?? '')));
        return array_slice($receipts,0,$limit);
    }

    private function assertReceipt(array $receipt, ?string $mode = null, ?int $id = null, bool $requireEnabled = true): void
    {
        if (!$receipt || (int)($receipt['owner'] ?? -1) !== $this->actor()) throw new InvalidArgumentException('同步任务不存在或不属于当前账号');
        if (($mode !== null && $receipt['mode'] !== $mode) || ($id !== null && (int)$receipt['target_id'] !== $id)) throw new InvalidArgumentException('同步预检范围不匹配');
        if ($requireEnabled && !(new LanguagePolicy($this->repository))->enabled()) throw new InvalidArgumentException('请先在功能地图启用多语言');
        (new LanguageAccessService($this->repository))->assertLanguages([$receipt['source'],$receipt['target']]);
        if (($receipt['site_signature'] ?? '') !== \app\cms\template\TemplateMode::siteSignature() || ($receipt['template_mode'] ?? '') !== \app\cms\template\TemplateMode::current()) throw new InvalidArgumentException('站点或模板模式已变化，请重新预览');
    }

    private function buildPlan(int $id, string $mode): array
    {
        $r = $this->repository; $policy = new LanguagePolicy($r);
        if (!$policy->enabled()) throw new InvalidArgumentException('请先在功能地图启用多语言');
        $languages = $r->table('language')->order('id asc')->lock(true)->select()->toArray();
        $target = null; foreach ($languages as $row) if ((int)$row['id'] === $id) $target = $row;
        $this->source = $policy->main();
        if (!$target || $target['mark'] === $this->source) throw new InvalidArgumentException('目标必须是系统主语言以外的语言');
        $this->target = (string)$target['mark'];
        (new LanguageAccessService($r))->assertLanguages([$this->source,$this->target]);
        (new ContentAccessService($r))->assertLanguageContents([$this->source,$this->target]);
        $this->maps = []; $this->created = []; $this->newSinglePages = [];
        $this->loadMaps();
        $models = array_column(array_filter($r->table('channeltype')->where('is_del',0)->select()->toArray(),fn($v)=>$this->enterpriseModel($v)),null,'id');
        $source = $this->rows('archives'); $eligible = []; $skipped = []; $single = []; $existing = [];
        foreach ($source as $row) {
            $aid = (int)$row['aid']; $reason = '';
            if (!isset($models[$row['channel']]) || !empty($row['users_id']) || !empty($row['merchant_id'])) $reason = '不支持的模型、会员投稿或交易内容';
            elseif (!isset($this->maps['arctype'][(int)$row['typeid']])) $reason = '未建立有效栏目关联';
            elseif ((int)$row['channel'] === 6) {
                $peers = $r->table('archives')->where(['lang'=>$this->target,'typeid'=>$this->maps['arctype'][(int)$row['typeid']],'channel'=>6,'is_del'=>0,'users_id'=>0])->select()->toArray();
                if (count($peers)>1) throw new InvalidArgumentException('目标单页栏目存在多条文档');
                if (!$peers) $reason = '目标单页结构缺失，请先补齐栏目结构';
                else {
                    $this->maps['archives'][$aid] = (int)$peers[0]['aid'];
                    if ($mode === 'all') { $single[] = $this->item($row)+['target_id'=>(int)$peers[0]['aid']]; $eligible[$aid]=$row; }
                    else $reason = '已有单页正文保留';
                }
            } elseif ($mode === 'new' && ($peer=$this->existingDocument($row))) {
                $this->maps['archives'][$aid] = (int)$peer['aid']; $existing[$aid]=$peer;
                $reason = !empty($peer['is_del']) ? '目标已回收，保留原状态' : '已有目标文档，保留翻译';
            } else $eligible[$aid] = $row;
            if ($reason !== '') $skipped[$aid] = $this->item($row)+['reason'=>$reason];
        }
        // Each dependent record is either complete or explicitly skipped; no silent zero IDs.
        do {
            $changed = false;
            foreach ($eligible as $aid=>$row) {
                $reason = $this->dependencyError($row,$eligible);
                if ($reason === '') continue;
                unset($eligible[$aid]); $changed=true;
                $skipped[$aid] = $this->item($row)+['reason'=>$reason];
            }
        } while ($changed);
        $single = array_values(array_filter($single,fn($row)=>isset($eligible[$row['id']])));
        $create = array_values(array_map(fn($row)=>$this->item($row),array_filter($eligible,fn($row)=>(int)$row['channel']!==6)));
        $delete = [];
        if ($mode === 'all' && !empty($this->maps['arctype'])) {
            foreach ($r->table('archives')->where(['lang'=>$this->target,'is_del'=>0,'users_id'=>0])->whereIn('channel',array_keys($models))->whereIn('typeid',array_values($this->maps['arctype']))->where('channel','<>',6)->order('aid asc')->select()->toArray() as $row) { if (!empty($row['merchant_id'])) throw new InvalidArgumentException('目标含商户内容，不能自动覆盖'); $delete[]=$this->item($row)+['manual_or_translated'=>true]; }
        }
        if ($mode === 'all') {
            // A broken dependency must not cause deletion of the target's corresponding translation.
            foreach ($skipped as $row) if ($row['reason'] !== '不支持的模型、会员投稿或交易内容' && isset($this->maps['arctype'][$row['typeid']]) && $row['channel'] !== 6) throw new InvalidArgumentException('全部同步存在不完整关联：'.$row['title'].'；'.$row['reason'].'。未删除任何目标内容');
        }
        return ['mode'=>$mode,'source'=>$this->source,'target'=>$this->target,'target_id'=>$id,'counts'=>['create'=>count($create),'delete'=>count($delete),'single_update'=>count($single),'skip'=>count($skipped)],'create'=>$create,'delete'=>$delete,'single_update'=>$single,'skipped'=>array_values($skipped),'eligible_aids'=>array_map('intval',array_keys($eligible)),'existing_aids'=>array_map('intval',array_keys($existing)),'fingerprint'=>$this->fingerprint(),'site_signature'=>\app\cms\template\TemplateMode::siteSignature(),'template_mode'=>\app\cms\template\TemplateMode::current()];
    }

    private function loadMaps(): void
    {
        foreach (['arctype'=>['arctype','tid'],'shop_product_attrlist'=>['shop_product_attrlist','attrlist_'],'product_attribute'=>['product_attribute','attr_'],'shop_product_attribute'=>['shop_product_attribute','attribute_']] as $table=>[$group,$prefix]) foreach ($this->rows($table) as $row) {
            $id=(int)$row[$this->primary($table)];
            $bindings=$this->repository->table('language_attr')->where(['lang'=>$this->source,'attr_group'=>$group,'attr_value'=>(string)$id])->select()->toArray();
            if (count($bindings)>1) throw new InvalidArgumentException('源语言关联重复，请先修复');
            $name=$bindings[0]['attr_name']??$prefix.$id;
            $targets=$this->repository->table('language_attr')->where(['lang'=>$this->target,'attr_group'=>$group,'attr_name'=>$name])->select()->toArray();
            if (count($targets)>1) throw new InvalidArgumentException('目标语言关联重复，请先修复');
            if (!$targets) continue;
            $peer=$this->repository->table($table)->where([$this->primary($table)=>(int)$targets[0]['attr_value'],'lang'=>$this->target])->find();
            if (!$peer || !empty($peer['is_del'])) continue;
            if ($table==='arctype' && (int)$peer['current_channel']!==(int)$row['current_channel']) throw new InvalidArgumentException('栏目关联模型不一致');
            $this->maps[$group][$id]=(int)$peer[$this->primary($table)];
        }
    }

    private function existingDocument(array $row): ?array
    {
        $r=$this->repository; $aid=(int)$row['aid']; $peers=[]; $logs=$r->table('language_archives_copy_log')->where(['oldid'=>$aid,'lang'=>$this->target])->order('id desc')->select()->toArray();
        foreach ($logs as $log) {
            $peer=$r->table('archives')->where(['aid'=>(int)$log['newid'],'lang'=>$this->target])->find();
            if ($peer && (int)$peer['channel']===(int)$row['channel'] && (int)$peer['typeid']===$this->maps['arctype'][(int)$row['typeid']]) $peers[(int)$peer['aid']]=$peer;
        }
        $binding=$r->table('language_attr')->where(['lang'=>$this->source,'attr_group'=>'archives','attr_value'=>(string)$aid])->select()->toArray();
        if (count($binding)>1) throw new InvalidArgumentException('源文档绑定重复');
        $name=$binding[0]['attr_name']??'aid'.$aid;
        foreach ($r->table('language_attr')->where(['lang'=>$this->target,'attr_group'=>'archives','attr_name'=>$name])->select()->toArray() as $b) {
            $peer=$r->table('archives')->where(['aid'=>(int)$b['attr_value'],'lang'=>$this->target])->find();
            if (!$peer || (int)$peer['channel']!==(int)$row['channel'] || (int)$peer['typeid']!==$this->maps['arctype'][(int)$row['typeid']]) throw new InvalidArgumentException('目标文档绑定失效，请先修复关联');
            $peers[(int)$peer['aid']]=$peer;
        }
        $active=array_filter($peers,fn($p)=>empty($p['is_del']));
        if (count($active)>1) throw new InvalidArgumentException('存在多个有效目标文档，不能猜测映射');
        if ($active) return array_values($active)[0];
        if ($peers) return array_values($peers)[0];
        if ($logs) throw new InvalidArgumentException('同步日志已失效且无可靠文档关联，请先修复');
        return null;
    }

    private function dependencyError(array $row,array $eligible): string
    {
        $r=$this->repository;
        $model=$r->table('channeltype')->where('id',$row['channel'])->find();
        if (!$r->table($model['table'].'_content')->where('aid',$row['aid'])->find()) return '源文档附表缺失';
        foreach (array_filter(array_map('intval',explode(',',(string)$row['stypeid']))) as $id) if (!isset($this->maps['arctype'][$id])) return '副栏目缺少关联';
        if (!empty($row['attrlist_id']) && !isset($this->maps['shop_product_attrlist'][(int)$row['attrlist_id']])) return '产品参数组缺少关联';
        if (!empty($row['joinaid']) && !isset($eligible[(int)$row['joinaid']],$this->maps['archives'][(int)$row['joinaid']])) {
            if (!isset($eligible[(int)$row['joinaid']]) && !isset($this->maps['archives'][(int)$row['joinaid']])) return '关联文档不在可同步范围';
        }
        foreach (['product_attr'=>'product_attribute','shop_product_attr'=>'shop_product_attribute'] as $table=>$group) foreach ($r->table($table)->where('aid',$row['aid'])->select()->toArray() as $v) if (!isset($this->maps[$group][(int)$v['attr_id']])) return '产品参数字段缺少关联';
        foreach ($r->table('taglist')->where('aid',$row['aid'])->select()->toArray() as $tag) {
            $index=$r->table('tagindex')->where('id',$tag['tid'])->find();
            if (!$index) return 'TAG记录失效';
            if (!empty($index['typeid']) && !isset($this->maps['arctype'][(int)$index['typeid']])) return 'TAG栏目缺少关联';
        }
        foreach ($r->table('special_node')->where('aid',$row['aid'])->select()->toArray() as $v) {
            foreach (array_filter(array_map('intval',explode(',',(string)$v['typeid']))) as $id) if (!isset($this->maps['arctype'][$id])) return '专题栏目缺少关联';
            foreach (array_filter(array_map('intval',explode(',',(string)$v['aidlist']))) as $id) if (!isset($eligible[$id]) && !isset($this->maps['archives'][$id])) return '专题文档缺少关联';
        }
        return '';
    }

    private function fingerprint(): string
    {
        $r=$this->repository; $parts=[];
        foreach (['language','channeltype','arctype','archives','language_attr','language_archives_copy_log','product_attribute','shop_product_attribute','shop_product_attrlist','special_node','product_attr','shop_product_attr','channelfield_bind','tagindex','taglist','images_upload','product_img','download_file','media_file','product_custom_param'] as $table) $parts[$table]=$r->table($table)->order($this->primary($table).' asc')->lock(true)->select()->toArray();
        foreach ($r->table('channeltype')->select()->toArray() as $model) if ($this->enterpriseModel($model)) $parts[$model['table'].'_content']=$r->table($model['table'].'_content')->order('aid asc')->lock(true)->select()->toArray();
        $parts['htmltext_provenance']=$r->table('cms_state_records')->where('scope',HtmltextProvenanceService::SCOPE)->order('name')->lock(true)->select()->toArray();
        return $this->hash($parts);
    }

    private function assertTransactional(array $plan): void
    {
        $tables=['language','language_attr','language_attribute','language_archives_copy_log','cms_state_records','archives','arctype','tagindex','taglist','images_upload','product_img','download_file','media_file','product_attr','shop_product_attr','product_custom_param','special_node','single_content','channelfield_bind'];
        foreach ($this->repository->table('channeltype')->select()->toArray() as $model) if ($this->enterpriseModel($model)) $tables[]=$model['table'].'_content';
        foreach (array_unique($tables) as $table) {
            $rows=$this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);
            if (strtolower((string)($rows[0]['ENGINE']??''))!=='innodb') throw new InvalidArgumentException('同步要求相关表存在且使用InnoDB：'.$table);
        }
    }

    private function captureTarget(string $lang,bool $withProvenance=true): array
    {
        $r=$this->repository;$result=[];
        $result['archives']=$r->table('archives')->where('lang',$lang)->order('aid asc')->select()->toArray();
        $aids=array_map('intval',array_column($result['archives'],'aid'));
        $tables=['taglist','images_upload','product_img','download_file','media_file','product_attr','shop_product_attr','product_custom_param','special_node'];
        foreach ($r->table('channeltype')->select()->toArray() as $model) if ($this->enterpriseModel($model)) $tables[]=$model['table'].'_content';
        foreach (array_unique($tables) as $table) $result[$table]=$aids?$r->table($table)->whereIn('aid',$aids)->order($this->primary($table).' asc')->select()->toArray():[];
        $result['language_archives_copy_log']=$r->table('language_archives_copy_log')->where('lang',$lang)->order('id asc')->select()->toArray();
        $result['language_attr']=$r->table('language_attr')->where('lang',$lang)->whereIn('attr_group',['archives','tagindex'])->order('id asc')->select()->toArray();
        $result['tagindex']=$r->table('tagindex')->where('lang',$lang)->order('id asc')->select()->toArray();
        $pending=new LanguageStateStore('language.pending',$r); $result['__pending']=[];
        foreach ($pending->names() as $key) { $record=$pending->read($key); if (($record['lang']??'')===$lang) $result['__pending'][$key]=$record; }
        ksort($result['__pending']);
        if($withProvenance) {
            $result['__htmltext']=[];$provenance=new LanguageStateStore(HtmltextProvenanceService::SCOPE,$r);
            $tables=array_merge(['archives'],array_filter($tables,static fn($table)=>str_ends_with($table,'_content')));
            $tables=array_map(fn($table)=>$r->fullName($table),array_unique($tables));
            foreach($provenance->names()as$key) {
                $record=$provenance->read($key);
                if(in_array($record['table']??'', $tables,true)&&($record['primary_key']??'')==='aid'&&in_array((int)($record['id']??0),$aids,true))$result['__htmltext'][$key]=$record;
            }
            ksort($result['__htmltext']);
        }
        return $result;
    }

    private function actor(): int { if (PHP_SAPI==='cli' && !\app\cms\CmsContext::current()->request) return 0; return function_exists('admin_id') ? (int)admin_id() : 0; }
    private function item(array $row): array { return ['id'=>(int)$row['aid'],'title'=>(string)$row['title'],'typeid'=>(int)$row['typeid'],'channel'=>(int)$row['channel']]; }
    private function hash(array $value): string { return hash('sha256',json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)); }
    private function publicPlan(array $receipt): array
    {
        $public=array_diff_key($receipt,array_flip(['fingerprint','eligible_aids','existing_aids','before','after_sha','owner','site_signature','template_mode','last_error','public_error']));
        if(($receipt['status']??'')==='failed')$public['last_error']=$receipt['public_error']??'同步未完成，请检查服务日志后重试';
        return $public;
    }

    private function enterpriseModel(array $model): bool
    {
        return !in_array((int)$model['id'], [8,51], true) && (in_array((string)$model['nid'], ['article','product','images','download','media','single','special','recruit'], true) || (empty($model['ifsystem']) && !in_array((string)$model['nid'], ['ask','guestbook'], true)));
    }

    private function copyData(string $source, string $target, bool $documents, bool $newLanguage = false): array
    {
        $this->source = $source; $this->target = $target; $this->maps = []; $this->created = []; $this->newSinglePages = [];
        if ($newLanguage) $this->copyNewLanguageConfig();
        foreach (['config_attribute','arcrank'] as $table) {
            if ($table === 'config' && $newLanguage) continue;
            foreach ($this->rows($table) as $row) {
                // Configuration and pack variables have stable natural names.
                $key = match ($table) { 'config' => ['name' => $row['name']], 'config_attribute' => ['attr_var_name' => $row['attr_var_name']], 'language_pack' => ['name' => $row['name']], 'smtp_tpl','sms_template' => ['send_scene' => $row['send_scene']], 'arcrank' => ['rank' => $row['rank']], };
                if ($this->repository->table($table)->where($key + ['lang' => $target])->find()) continue;
                $this->insert($table, $row);
            }
        }
        $this->categories($newLanguage);
        foreach (['form' => ['form','form_name'], 'ad_position' => ['adp','title'], 'links_group' => ['linksgroup','group_name'], 'shop_product_attrlist' => ['attrlist_','list_name']] as $table => [$prefix,$title]) {
            foreach ($this->rows($table) as $row) {
                // Original syn_form/ad_position/links_group prefix display names
                // on language creation; syn_shop_product_attrlist does not.
                $changes = [];
                $this->mapped($table, $row, $table, $prefix, $changes, (string)$row[$title]);
            }
        }
        foreach ($this->rows('guestbook_attribute') as $row) {
            $owner = (int)$row['typeid']; $group = (int)$row['form_type'] === 1 ? 'form' : 'arctype';
            if (!isset($this->maps[$group][$owner])) continue;
            $changes = ['typeid' => $this->maps[$group][$owner]];

            $this->mapped('guestbook_attribute', $row, $group === 'form' ? 'form_attribute' : 'guestbook_attribute', 'attr_', $changes, $row['attr_name']);
        }
        foreach (['product_attribute' => ['arctype','typeid','product_attribute','attr_'], 'shop_product_attribute' => ['shop_product_attrlist','list_id','shop_product_attribute','attribute_']] as $table => [$owner,$foreign,$group,$prefix]) {
            foreach ($this->rows($table) as $row) {
                $old = (int)$row[$foreign];
                if ($old && !isset($this->maps[$owner][$old])) continue;
                $changes = [$foreign => $old ? $this->maps[$owner][$old] : 0];
                // Original Language::syn_arctype prefixes category-owned legacy
                // definitions only when creating a language. Modern definitions
                // and later incremental additions retain their source labels.


                $this->mapped($table, $row, $group, $prefix, $changes);
            }
        }
        if ($documents) $this->documents();
        return ['source' => $source, 'target' => $target, 'created' => $this->created, 'maps' => $this->maps];
    }

    private function categories(bool $newLanguage): void
    {
        $rows = $this->repository->table('arctype')->where('lang', $this->source)->order('id asc')->select()->toArray();
        $excluded = [];
        foreach ($rows as $row) if (!empty($row['is_del'])) $excluded[(int)$row['id']] = true;
        // Original arctype_options only reaches children of eligible parents.
        // Keep recycle protection and prune its descendants too; an actually
        // missing parent or cycle still fails the transaction below.
        do {
            $changed = false;
            foreach ($rows as $row) if (!isset($excluded[(int)$row['id']]) && isset($excluded[(int)$row['parent_id']])) {
                $excluded[(int)$row['id']] = true; $changed = true;
            }
        } while ($changed);
        $pending = array_filter($rows, fn($row) => !isset($excluded[(int)$row['id']]));
        while ($pending) {
            $progress = false;
            foreach ($pending as $index => $row) {
                $parent = (int)$row['parent_id'];
                if ($parent && !isset($this->maps['arctype'][$parent])) continue;
                $changes = ['parent_id' => $parent ? $this->maps['arctype'][$parent] : 0, 'topid' => $row['topid'] ? ($this->maps['arctype'][(int)$row['topid']] ?? 0) : 0, 'total_arc' => 0];
                if ($newLanguage) $changes['typename'] = $row['typename'];
                $createdBefore = $this->created['arctype'] ?? 0;
                $id = $this->mapped('arctype', $row, 'arctype', 'tid', $changes, $row['typename']);
                if(($this->created['arctype']??0)>$createdBefore)(new HtmltextProvenanceService($this->repository))->copyRecord('arctype',(int)$row['id'],'arctype',$id,$this->htmltextFields(-99,'arctype'),'id');
                if ((int)$row['current_channel'] === 6 && ($this->created['arctype'] ?? 0) > $createdBefore) {
                    $this->newSinglePages[(int)$row['id']] = $this->createSinglePage($id, (string)($changes['typename'] ?? $row['typename']));
                }
                foreach ($this->repository->table('channelfield_bind')->where('typeid', $row['id'])->select()->toArray() as $bind) {
                    if (!$this->repository->table('channelfield_bind')->where(['typeid' => $id, 'field_id' => $bind['field_id']])->find()) $this->insert('channelfield_bind', $bind, ['typeid' => $id]);
                }
                unset($pending[$index]); $progress = true;
            }
            if (!$progress) throw new InvalidArgumentException('源语言栏目存在孤儿或循环，复制已回滚');
        }
    }

    /** Original Arctype.addData initializes a single page as column structure. */
    private function createSinglePage(int $typeid, string $title): int
    {
        // tpSetting('editor') uses the administrator's current language, not the
        // copied language. Read explicitly to avoid request/process cache state.
        $current = $this->currentLanguage ?? (string)$this->repository->table('language')->order('id asc')->value('mark');
        $editor = [];
        foreach ($this->repository->table('setting')->where(['lang' => $current, 'inc_type' => 'editor'])->order('id asc')->select()->toArray() as $setting) $editor[$setting['name']] = $setting['value'];
        $now = time();
        $aid = $this->insert('archives', [
            'title' => $title, 'typeid' => $typeid, 'channel' => 6,
            'seo_description' => '', 'reason' => '', 'sort_order' => 100,
            'editor_remote_img_local' => $editor['editor_remote_img_local'] ?? 1,
            'editor_img_clear_link' => $editor['editor_img_clear_link'] ?? 1,
            'add_time' => $now,
        ]);
        // Omitted mobile body and main update_time retain their original SQL
        // defaults (NULL and 0). Document copying changes only the two bodies.
        $this->insert('single_content', ['aid' => $aid, 'typeid' => $typeid, 'content' => '', 'add_time' => $now, 'update_time' => $now]);
        $provenance=new HtmltextProvenanceService($this->repository);$body=$this->repository->table('single_content')->where('aid',$aid)->find();
        foreach($this->htmltextFields(6,'single_content')as$field)if(array_key_exists($field,$body)&&(string)$body[$field]==='')$provenance->markTrusted('single_content','aid',$aid,$field,'','raw');
        return $aid;
    }

    private function documents(bool $reset = false): void
    {
        $models = $this->repository->table('channeltype')->where('is_del', 0)->select()->toArray(); $models = array_column($models, null, 'id');
        $new = []; $singleSources = [];
        foreach ($this->rows('archives') as $row) {
            if ($this->eligibleAids !== null && !in_array((int)$row['aid'],$this->eligibleAids,true)) continue;
            $model = $models[$row['channel']] ?? null; $oldType = (int)$row['typeid'];
            if (!$model || !$this->enterpriseModel($model) || (int)$row['users_id'] > 0 || !isset($this->maps['arctype'][$oldType])) continue;
            if ((int)$row['channel'] === 6) {
                // Single pages belong to their column. Existing translations,
                // including earlier candidate copy logs/bindings, stay intact.
                if ($reset) continue;
                // ContentService locks a single page's category before its
                // archive. Keep that order before refreshCount updates it.
                $category = $this->repository->table('arctype')->where('id', $this->maps['arctype'][$oldType])->lock(true)->find();
                if (!$category || $category['lang'] !== $this->target || !empty($category['is_del']) || (int)$category['current_channel'] !== 6) throw new InvalidArgumentException('目标单页栏目归属或模型不一致，复制已回滚');
                $copies = $this->repository->table('archives')->where(['typeid' => $this->maps['arctype'][$oldType], 'lang' => $this->target, 'channel' => 6, 'is_del' => 0, 'users_id' => 0])->lock(true)->select()->toArray();
                if (count($copies) > 1) throw new InvalidArgumentException('目标栏目存在多个单页文档，复制已回滚');
                // Preserve the candidate's cross-language relation remapping
                // without creating archive bindings or ordinary copy logs.
                if ($copies) $this->maps['archives'][(int)$row['aid']] = (int)$copies[0]['aid'];
                if (!isset($this->newSinglePages[$oldType])) continue;
                if (isset($singleSources[$oldType])) throw new InvalidArgumentException('源语言单页栏目存在多条有效文档，复制已回滚');
                $singleSources[$oldType] = true;
                $body = $this->repository->table('single_content')->where('aid', $row['aid'])->find();
                if (!$body) continue;
                if ((int)$body['typeid'] !== $oldType) throw new InvalidArgumentException('源语言单页附表归属不一致，复制已回滚');
                $copied=array_intersect_key($body,array_flip(['content','content_ey_m']));
                $this->repository->table('single_content')->where('aid', $this->newSinglePages[$oldType])->update($copied);
                (new HtmltextProvenanceService($this->repository))->copyRecord('single_content',(int)$row['aid'],'single_content',$this->newSinglePages[$oldType],array_keys($copied));
                continue;
            }
            $oldAid = (int)$row['aid'];
            if (!$reset && isset($this->maps['archives'][$oldAid])) {
                $peer = $this->repository->table('archives')->where(['aid'=>$this->maps['archives'][$oldAid],'lang'=>$this->target])->find();
                if (!$peer) throw new InvalidArgumentException('已核验的目标文档在同步时失效');
                $where = ['oldid'=>$oldAid,'newid'=>(int)$peer['aid'],'lang'=>$this->target];
                if (!$this->repository->table('language_archives_copy_log')->where($where)->find()) $this->repository->table('language_archives_copy_log')->insert($where+['channel'=>$row['channel'],'typeid'=>$row['typeid'],'new_typeid'=>$peer['typeid'],'add_time'=>time()]);
                continue;
            }
            $existing = null;
            if (!$reset) {
                // A reset retains history for recycled documents and adds a new
                // active copy. Prefer that latest copy during later additions.
                $logs = $this->repository->table('language_archives_copy_log')->where(['oldid' => $oldAid, 'lang' => $this->target])->order('id desc')->select()->toArray();
                foreach ($logs as $log) {
                    $copy = $this->repository->table('archives')->where(['aid'=>$log['newid'],'lang'=>$this->target])->find();
                    if ($copy && (!$existing || empty($copy['is_del']))) $existing = $log;
                    if ($copy && empty($copy['is_del'])) break;
                }
                if ($logs && !$existing) throw new InvalidArgumentException('目标语言文档映射失效，请先检查复制记录');
            }
            if ($existing) {
                $valid = $this->repository->table('archives')->where(['aid' => $existing['newid'], 'lang' => $this->target])->find();
                if (!$valid) throw new InvalidArgumentException('目标语言文档映射失效，请先检查复制记录');
                $this->maps['archives'][$oldAid] = (int)$existing['newid']; continue;
            }
            $type = $this->maps['arctype'][$oldType];
            $createdBefore = $this->created['archives'] ?? 0;
            $id = $this->mapped('archives', $row, 'archives', 'aid', ['typeid' => $type, 'stypeid' => $this->remapList((string)$row['stypeid'], 'arctype'), 'attrlist_id' => $this->maps['shop_product_attrlist'][(int)$row['attrlist_id']] ?? 0], '', $reset);
            $this->repository->table('language_archives_copy_log')->insert(['channel' => $row['channel'], 'typeid' => $oldType, 'new_typeid' => $type, 'oldid' => $oldAid, 'newid' => $id, 'lang' => $this->target, 'add_time' => time()]);
            // A canonical binding can survive a source-language replacement.
            // Repair its copy log while retaining the target translation.
            if (!$reset && ($this->created['archives'] ?? 0) === $createdBefore) continue;
            $table = $model['table'] . '_content';
            $addonRows = $this->repository->table($table)->where('aid', $oldAid)->select()->toArray();
            if ($reset && !$addonRows) throw new InvalidArgumentException('源语言文档附表缺失，覆盖已回滚');
            foreach ($addonRows as $addon) $this->insert($table, $addon, ['aid' => $id] + (isset($this->schema($table)['typeid']) ? ['typeid' => $type] : []));
            $provenance=new HtmltextProvenanceService($this->repository);
            $provenance->copyRecord('archives',$oldAid,'archives',$id,$this->htmltextFields((int)$row['channel'],'archives'));
            if($addonRows)$provenance->copyRecord($table,$oldAid,$table,$id,$this->htmltextFields((int)$row['channel'],$table));
            $new[$oldAid] = [$id,$row];
        }
        foreach ($new as $oldAid => [$id,$row]) {
            foreach (['images_upload','product_img','download_file','media_file','product_attr','shop_product_attr','product_custom_param','special_node'] as $table) {
                foreach ($this->repository->table($table)->where('aid', $oldAid)->select()->toArray() as $child) {
                    $changes = ['aid' => $id];
                    if ($table === 'product_attr' || $table === 'shop_product_attr') {
                        $group = $table === 'product_attr' ? 'product_attribute' : 'shop_product_attribute';
                        if (!isset($this->maps[$group][(int)$child['attr_id']])) throw new InvalidArgumentException('产品参数映射缺失，复制已回滚');
                        $changes['attr_id'] = $this->maps[$group][(int)$child['attr_id']];
                    }
                    if ($table === 'special_node') { $changes['typeid'] = $this->remapList((string)$child['typeid'], 'arctype'); $changes['aidlist'] = $this->remapList((string)$child['aidlist'], 'archives'); }
                    $this->insert($table, $child, $changes);
                }
            }
            $this->repository->table('archives')->where('aid', $id)->update(['joinaid' => $this->maps['archives'][(int)$row['joinaid']] ?? 0]);
            foreach ($this->repository->table('taglist')->where('aid', $oldAid)->select()->toArray() as $tag) {
                $index = $this->repository->table('tagindex')->where('id', $tag['tid'])->find();
                if (!$index) continue;
                $tagId = $this->mapped('tagindex', $index, 'tagindex', 'tag', ['typeid' => $this->maps['arctype'][(int)$index['typeid']] ?? 0]);
                $tag['tid'] = $tagId; $tag['aid'] = $id; $tag['typeid'] = $this->maps['arctype'][(int)$row['typeid']]; $tag['lang'] = $this->target;
                if (!$this->repository->table('taglist')->where(['tid' => $tagId, 'aid' => $id])->find()) $this->repository->table('taglist')->insert($tag);
            }
        }
        foreach ($this->maps['tagindex'] ?? [] as $tag) $this->repository->table('tagindex')->where('id', $tag)->update(['total' => $this->repository->table('taglist')->where('tid', $tag)->count()]);
        foreach ($this->maps['arctype'] ?? [] as $type) (new ContentService($this->repository))->refreshCount((int)$type);
    }

    private function remapList(string $value, string $group): string
    {
        $ids = array_filter(array_map('intval', explode(',', $value))); $result = [];
        foreach ($ids as $id) { if (!isset($this->maps[$group][$id])) throw new InvalidArgumentException('关联映射缺失，复制已回滚'); $result[] = $this->maps[$group][$id]; }
        return implode(',', array_unique($result));
    }

    private function mapped(string $table, array $row, string $group, string $prefix, array $changes = [], string $title = '', bool $fresh = false): int
    {
        $primary = $this->primary($table); $oldId = (int)$row[$primary];
        if (isset($this->maps[$group][$oldId])) return $this->maps[$group][$oldId];
        $sourceBind = $this->repository->table('language_attr')->where(['lang' => $this->source, 'attr_group' => $group, 'attr_value' => (string)$oldId])->order('id asc')->find();
        $name = $sourceBind['attr_name'] ?? $prefix . $oldId;
        if (!$sourceBind && !$this->resettingDocuments) $this->bind($this->source, $group, $name, $oldId);
        if ($fresh && $group === 'archives') {
            // Keep historical recycled ownership without letting an old
            // canonical binding shadow the current active database binding.
            $history = $this->repository->table('language_attr')->where(['lang'=>$this->target,'attr_group'=>$group,'attr_name'=>$name])->select()->toArray();
            foreach ($history as $binding) {
                $document = $this->repository->table('archives')->where(['aid'=>$binding['attr_value'],'lang'=>$this->target])->find();
                if (!$document || empty($document['is_del'])) throw new InvalidArgumentException('目标文档绑定仍被占用，覆盖已回滚');
                $this->repository->table('language_attr')->where('id',$binding['id'])->update(['attr_name'=>'history_archive_'.$binding['id'].'_'.substr(hash('sha256',$name),0,12)]);
            }
        }
        $binding = $fresh ? null : $this->repository->table('language_attr')->where(['lang' => $this->target, 'attr_group' => $group, 'attr_name' => $name])->order($group === 'archives' ? 'id desc' : 'id asc')->find();
        if ($binding) {
            $existing = $this->repository->table($table)->where([$primary => $binding['attr_value'], 'lang' => $this->target])->find();
            if (!$existing || (isset($existing['is_del']) && $existing['is_del'])) throw new InvalidArgumentException('目标语言映射失效，不能覆盖已有翻译');
            return $this->maps[$group][$oldId] = (int)$binding['attr_value'];
        }
        $id = $this->insert($table, $row, $changes); $this->maps[$group][$oldId] = $id;
        $this->bind($this->target, $group, $name, $id);
        (new LanguageStateStore('language.pending', $this->repository))->write($group.'.'.$id, ['lang'=>$this->target,'source'=>$this->source,'state'=>'pending','source_id'=>$oldId,'created_at'=>time()]);
        if (!$this->repository->table('language_attribute')->where(['attr_name' => $name, 'attr_group' => $group])->find()) $this->repository->table('language_attribute')->insert(['attr_title' => $title ?: $name, 'attr_name' => $name, 'attr_group' => $group, 'add_time' => time(), 'update_time' => time()]);
        return $id;
    }

    private function bind(string $lang, string $group, string $name, int $value): void
    {
        $this->repository->table('language_attr')->insert(['lang' => $lang, 'attr_group' => $group, 'attr_name' => $name, 'attr_value' => (string)$value, 'add_time' => time(), 'update_time' => time()]);
    }

    /** Original Language::afterAdd config branch, separate from incremental sync. */
    private function copyNewLanguageConfig(): void
    {
        $allowed = ['web_name','web_title','web_keywords','web_description','web_logo','web_ico','web_basehost','web_tpl_theme','web_templets_pc','web_templets_m','web_copyright','web_recordnum','web_status','web_close_reason','web_mobile_domain_open','web_mobile_domain','web_is_mobile','response_type','seo_pseudo','seo_inlet','seo_dynamic_format','seo_rewrite_format','seo_html_position','seo_html_arcdir','seo_html_listname','seo_html_viewname','list_symbol','web_language_switch','system_langnum','system_home_default_lang','language_split'];
        foreach ($this->repository->table('config')->where(['lang'=>$this->source,'is_del'=>0])->order('id asc')->select()->toArray() as $row) {
            if (!in_array($row['name'], $allowed, true)) continue;
            $this->insert('config', $row);
        }
    }

    private function creationHash(array $input): string
    {
        $canonical = array_intersect_key($input,array_flip(['title','mark','url','target','sort_order'])); ksort($canonical);
        return hash('sha256', json_encode($canonical,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
    }

    private function configValue(string $language, string $name): mixed
    {
        $value = null;
        // Explicit reads avoid request-context tpCache cross-language state.
        foreach ($this->repository->table('config')->where(['lang'=>$language,'inc_type'=>'seo','is_del'=>0,'name'=>$name])->order('id asc')->select()->toArray() as $row) $value = $row['value'];
        return $value;
    }
    private function rows(string $table): array
    {
        $query = $this->repository->table($table)->where('lang', $this->source);
        if (isset($this->schema($table)['is_del'])) $query->where('is_del', 0);
        return $query->order($this->primary($table) . ' asc')->select()->toArray();
    }
    private function insert(string $table, array $row, array $changes = []): int
    {
        foreach ($this->schema($table) as $key => $column) if (str_contains($column['Extra'], 'auto_increment')) unset($row[$key]);
        if (isset($this->schema($table)['lang'])) $row['lang'] = $this->target;
        $data = array_replace($row, $changes);
        // Omit generated/computed columns and fill only required columns lacking defaults.
        foreach ($this->schema($table) as $key=>$column) if (!array_key_exists($key,$data) && $column['Null'] !== 'YES' && $column['Default'] === null && !str_contains($column['Extra'],'auto_increment')) $data[$key] = preg_match('/int|decimal|float|double/i',$column['Type']) ? 0 : '';
        $id = (int)$this->repository->table($table)->insertGetId($data); $this->created[$table] = ($this->created[$table] ?? 0) + 1;
        return $id;
    }
    /** Same field definitions drive both template reads and synchronized writer metadata. */
    private function htmltextFields(int $channel,string $table): array
    {
        $names=[];
        $query=$this->repository->table('channelfield')->where(['channel_id'=>$channel,'dtype'=>'htmltext']);
        if($channel!==-99)$query->where('ifmain',$table==='archives'?1:0);
        foreach($query->column('name')as$name)if(isset($this->schema($table)[$name]))$names[]=$name;
        if($table!=='archives'&&$table!=='arctype')foreach(['content','content_ey_m']as$name)if(isset($this->schema($table)[$name]))$names[]=$name;
        return array_values(array_unique($names));
    }
    private function schema(string $table): array { return $this->schemas[$table] ??= $this->repository->columns($table); }
    private function primary(string $table): string { return (new EnterpriseDataService($this->repository))->primary($this->schema($table)); }
    private function setConfig(string $lang, string $name, string $value, string $group): void
    {
        $old = $this->repository->table('config')->where(['lang' => $lang, 'name' => $name])->find();
        if ($old) $this->repository->table('config')->where('id', $old['id'])->update(['value' => $value, 'is_del' => 0, 'update_time' => time()]);
        else $this->repository->table('config')->insert(['lang' => $lang, 'name' => $name, 'value' => $value, 'inc_type' => $group, 'update_time' => time()]);
    }
}
