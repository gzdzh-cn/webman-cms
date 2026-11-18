<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/**
 * Transaction-local enterprise structure synchronization.
 * Based on EyouCMS 1.7.7 ArctypeLogic, AdPosition, LinksGroup and FormLogic.
 * 易优CMS 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * https://www.eyoucms.com — retain the upstream license for commercial use.
 * Call lock() before locking business rows; the caller owns the transaction.
 */
class EnterpriseLanguageSyncService
{
    private const ENTITIES = [
        'arctype' => ['id', 'tid', 'typename'],
        'ad_position' => ['id', 'adp', 'title'],
        'ad' => ['id', 'ad', 'title'],
        'links_group' => ['id', 'linksgroup', 'group_name'],
        'form' => ['form_id', 'form', 'form_name'],
    ];
    private array $languages = [];
    private array $policies = [];

    public function __construct(private CmsRepository $repository) {}

    public static function supports(string $table): bool { return isset(self::ENTITIES[$table]); }

    public function lock(): void
    {
        $this->languages = $this->repository->table('language')->order('id asc')->lock(true)->select()->toArray();
        if (!$this->languages) throw new InvalidArgumentException('请先配置网站语言');
        $this->policies = [];
    }

    public function policy(string $lang): array
    {
        if (!$this->languages) throw new \LogicException('语言同步必须先锁定语言列表');
        if (!in_array($lang, array_column($this->languages, 'mark'), true)) throw new InvalidArgumentException('语言不存在');
        if (isset($this->policies[$lang])) return $this->policies[$lang];
        $split = (new LanguagePolicy($this->repository))->settings()['language_split'] ?? 0;
        // Shared structure follows the one site-wide policy, never a stale per-language counter.
        return $this->policies[$lang] = ['main' => $this->languages[0]['mark'], 'shared' => (new LanguagePolicy($this->repository))->enabled() && count($this->languages) > 1 && empty($split)];
    }

    public function prepare(string $table, array $data, ?array $old): array
    {
        $lang = $data['lang'] ?? $old['lang'] ?? (new LanguagePolicy($this->repository))->main();
        if (!is_string($lang) || ($old && $lang !== $old['lang'])) throw new InvalidArgumentException('不能修改记录所属语言');
        $this->policy($lang);
        (new LanguageAccessService($this->repository))->assertLanguages([$lang]);
        $languagePolicy = new LanguagePolicy($this->repository);
        if (!$languagePolicy->enabled() && $lang !== $languagePolicy->main()) throw new InvalidArgumentException('多语言已关闭，当前只能编辑系统主语言');
        if ($old && !empty($old['is_del'])) throw new InvalidArgumentException('请先恢复回收站内的记录');
        if ($table === 'ad') {
            $pid = (int)($data['pid'] ?? $old['pid'] ?? 0);
            $owner = $this->repository->table('ad_position')->where('id', $pid)->lock(true)->find();
            if (!$owner || $owner['lang'] !== $lang || !empty($owner['is_del'])) throw new InvalidArgumentException('广告位置不存在或语言不一致');
        }
        $data['lang'] = $lang;
        return $data;
    }

    /** New records only. Existing bindings are reused without replacing translations. */
    public function created(string $table, int $id): array
    {
        [$primary, $prefix, $title] = self::ENTITIES[$table];
        $row = $this->repository->table($table)->where($primary, $id)->lock(true)->find();
        if (!$row) throw new InvalidArgumentException('新增记录不存在');
        $policy = $this->policy($row['lang']);
        if (!$policy['shared'] || $row['lang'] !== $policy['main']) return [$row['lang'] => $id];
        (new LanguageAccessService($this->repository))->assertLanguages(array_column($this->languages,'mark'));
        $name = $this->canonical($table, $row['lang'], $id) ?? $prefix . $id;
        if ($table === 'arctype') $this->assertCategoryTargetsAvailable($row, $name);
        $this->bind($table, $name, $row['lang'], $id);
        $attribute = $this->repository->table('language_attribute')->where(['attr_group' => $table, 'attr_name' => $name])->lock(true)->find();
        if (!$attribute) $this->repository->table('language_attribute')->insert(['attr_title' => $row[$title], 'attr_name' => $name, 'attr_group' => $table, 'add_time' => time(), 'update_time' => time()]);
        elseif (!empty($attribute['is_del'])) throw new InvalidArgumentException('语言变量已回收，请先恢复原记录');
        $result = [$row['lang'] => $id];
        foreach ($this->languages as $language) {
            $lang = $language['mark'];
            if ($lang === $row['lang']) continue;
            $target = $this->target($table, $name, $lang);
            if ($target) { $result[$lang] = (int)$target[$primary]; continue; }
            $copy = $row; unset($copy[$primary]);
            $copy['lang'] = $lang; $copy[$title] = $row[$title];
            if ($table === 'arctype') {
                $parent = $this->mappedOwner('arctype', (int)$row['parent_id'], $row['lang'], $lang);
                $copy['parent_id'] = (int)($parent['id'] ?? 0);
                $copy['topid'] = $parent ? ((int)$parent['topid'] ?: (int)$parent['id']) : 0;
            } elseif ($table === 'ad') {
                $owner = $this->mappedOwner('ad_position', (int)$row['pid'], $row['lang'], $lang);
                // Upstream writes a null pid when the mapping is missing. Reject
                // that corrupt state atomically instead of leaving an orphan ad.
                if (!$owner) throw new InvalidArgumentException('目标语言广告位置缺少有效绑定，请先同步广告位置');
                $copy['pid'] = (int)$owner['id'];
            }
            try { $new = (int)$this->repository->table($table)->insertGetId($copy); }
            catch (\think\db\exception\DuplicateException $error) {
                // The unique index remains authoritative if a writer outside
                // this service inserts after the preflight locking read.
                if ($table !== 'arctype' || !$this->categoryDirectoryTaken($copy['dirname'], $lang)) throw $error;
                throw new InvalidArgumentException($this->categoryDirectoryConflict($copy['dirname'], $lang), 0, $error);
            }
            $this->bind($table, $name, $lang, $new); $result[$lang] = $new;
            (new LanguageStateStore('language.pending',$this->repository))->write($table.'.'.$new,['lang'=>$lang,'source'=>$row['lang'],'state'=>'pending','source_id'=>$id,'created_at'=>time()]);
            if ($table === 'arctype') $this->ensureSingle($new);
        }
        return $result;
    }

    /** Preserve upstream clone directories and IDs; reject unrelated owners. */
    private function assertCategoryTargetsAvailable(array $source, string $name): void
    {
        foreach ($this->languages as $language) {
            $lang = $language['mark'];
            if ($lang === $source['lang'] || $this->target('arctype', $name, $lang)) continue;
            // The original unique(dirname,lang) includes recycled categories.
            // Inspect every target before creating metadata or an earlier peer.
            if ($this->categoryDirectoryTaken($source['dirname'], $lang)) {
                throw new InvalidArgumentException($this->categoryDirectoryConflict($source['dirname'], $lang));
            }
        }
    }

    private function categoryDirectoryTaken(string $dirname, string $lang): bool
    {
        return (bool)$this->repository->table('arctype')->where(['dirname' => $dirname, 'lang' => $lang])->lock(true)->find();
    }

    private function categoryDirectoryConflict(string $dirname, string $lang): string
    {
        return '目标语言【' . $lang . '】的栏目目录【' . $dirname . '】已被占用（含回收站），请更改目录后重试';
    }

    /** Original addData creates an empty archive/addon for single-page columns. */
    public function ensureSingle(int $typeid, bool $refreshTitle = false): void
    {
        $r = $this->repository; $row = $r->table('arctype')->where('id', $typeid)->find();
        if (!$row || (int)$row['current_channel'] !== 6) return;
        $archives = $r->table('archives')->where(['typeid' => $typeid, 'is_del' => 0])->lock(true)->select()->toArray();
        if (count($archives) > 1) throw new InvalidArgumentException('单页栏目存在多条有效文档，请先修复数据');
        if ($archives) {
            $archive = $archives[0];
            if ($archive['lang'] !== $row['lang'] || (int)$archive['channel'] !== 6) throw new InvalidArgumentException('单页文档与栏目模型或语言不一致');
            $aid = (int)$archive['aid'];
            if ($refreshTitle) $r->table('archives')->where('aid', $aid)->update(['title' => $row['typename'], 'typeid' => $typeid, 'channel' => 6, 'sort_order' => 100, 'update_time' => time()]);
        } else {
            $main = $this->defaults('archives', ['title' => $row['typename'], 'typeid' => $typeid, 'channel' => 6, 'sort_order' => 100, 'lang' => $row['lang'], 'add_time' => time(), 'update_time' => time(), 'admin_id' => (int)($row['admin_id'] ?? 0)]);
            $aid = (int)$r->table('archives')->insertGetId($main);
        }
        $addon = $r->table('single_content')->where('aid', $aid)->lock(true)->find();
        if ($addon && (int)$addon['typeid'] !== $typeid) throw new InvalidArgumentException('单页附加内容与栏目不一致');
        if (!$addon) {
            $r->table('single_content')->insert($this->defaults('single_content', ['aid' => $aid, 'typeid' => $typeid, 'content' => '', 'content_ey_m' => '', 'add_time' => time(), 'update_time' => time()]));
            $provenance=new HtmltextProvenanceService($r);
            $created=$r->table('single_content')->where('aid',$aid)->find();
            foreach(['content','content_ey_m']as$field)if(array_key_exists($field,$created)&&(string)$created[$field]==='')$provenance->markTrusted('single_content','aid',$aid,$field,'','raw');
        }
    }

    /** Only the model ID is shared on edit; names, paths and text stay local. */
    public function categoryEdited(array $old, array $row): void
    {
        // Original updateData refreshes only this language's single-page title
        // on every category edit, including when the model remains unchanged.
        $this->ensureSingle((int)$row['id'], true);
        if ((int)$old['current_channel'] === (int)$row['current_channel'] || !$this->policy($row['lang'])['shared']) return;
        $ids = $this->expand('arctype', [(int)$row['id']]);
        $this->repository->table('arctype')->whereIn('id', $ids)->where('current_channel', $old['current_channel'])->update(['current_channel' => $row['current_channel'], 'update_time' => time()]);
        if ((int)$row['current_channel'] === 6) foreach ($ids as $id) $this->ensureSingle($id);
    }

    public function expand(string $table, array $ids): array
    {
        [$primary] = self::ENTITIES[$table]; $all = array_map('intval', $ids);
        foreach ($this->repository->table($table)->whereIn($primary, $ids)->lock(true)->select()->toArray() as $row) {
            if (!$this->policy($row['lang'])['shared']) continue;
            $name = $this->canonical($table, $row['lang'], (int)$row[$primary]);
            if (!$name) continue;
            foreach ($this->repository->table('language_attr')->where(['attr_group' => $table, 'attr_name' => $name])->lock(true)->select()->toArray() as $binding) {
                $target = $this->target($table, $name, $binding['lang'], true);
                $all[] = (int)$target[$primary];
            }
        }
        $all = array_values(array_unique($all));
        if ($all) (new LanguageAccessService($this->repository))->assertLanguages(array_values(array_unique($this->repository->table($table)->whereIn($primary,$all)->column('lang'))));
        return $all;
    }

    /** Metadata stays bound throughout recycle/restore. */
    public function recycled(string $table, array $ids, bool $restore): void
    {
        [$primary] = self::ENTITIES[$table]; $names = [];
        foreach ($this->repository->table($table)->whereIn($primary, $ids)->select()->toArray() as $row) {
            $policy = $this->policy($row['lang']);
            if ($row['lang'] !== $policy['main']) continue;
            $name = $this->canonical($table, $row['lang'], (int)$row[$primary]);
            if ($name) $names[] = $name;
        }
        if ($names) $this->repository->table('language_attribute')->where('attr_group', $table)->whereIn('attr_name', array_unique($names))->update(['is_del' => $restore ? 0 : 1, 'update_time' => time()]);
    }

    /** Call before deleting rows; remove metadata only after its last binding. */
    public function removeBindings(string $table, array $ids): void
    {
        $bindings = $this->repository->table('language_attr')->where('attr_group', $table)->whereIn('attr_value', array_map('strval', $ids))->lock(true)->select()->toArray();
        if (!$bindings) return;
        $this->repository->table('language_attr')->whereIn('id', array_column($bindings, 'id'))->delete();
        foreach (array_unique(array_column($bindings, 'attr_name')) as $name) {
            if (!$this->repository->table('language_attr')->where(['attr_group' => $table, 'attr_name' => $name])->find()) $this->repository->table('language_attribute')->where(['attr_group' => $table, 'attr_name' => $name])->delete();
        }
    }

    private function canonical(string $group, string $lang, int $id): ?string
    {
        $rows = $this->repository->table('language_attr')->where(['attr_group' => $group, 'lang' => $lang, 'attr_value' => (string)$id])->lock(true)->select()->toArray();
        if (count($rows) > 1) throw new InvalidArgumentException('记录绑定到多个语言变量，请先修复绑定');
        return $rows[0]['attr_name'] ?? null;
    }

    private function target(string $table, string $name, string $lang, bool $recycled = false): ?array
    {
        $bindings = $this->repository->table('language_attr')->where(['attr_group' => $table, 'attr_name' => $name, 'lang' => $lang])->lock(true)->select()->toArray();
        if (count($bindings) > 1) throw new InvalidArgumentException('目标语言变量绑定重复');
        if (!$bindings) return null;
        [$primary] = self::ENTITIES[$table];
        $row = $this->repository->table($table)->where([$primary => (int)$bindings[0]['attr_value'], 'lang' => $lang])->lock(true)->find();
        if (!$row || (!$recycled && !empty($row['is_del']))) throw new InvalidArgumentException('目标语言映射失效，不能覆盖已有翻译');
        if ($this->canonical($table, $lang, (int)$row[$primary]) !== $name) throw new InvalidArgumentException('目标记录语言绑定不一致');
        return $row;
    }

    private function mappedOwner(string $table, int $id, string $source, string $target): ?array
    {
        if (!$id) return null;
        $name = $this->canonical($table, $source, $id) ?? self::ENTITIES[$table][1] . $id;
        return $this->target($table, $name, $target);
    }

    private function bind(string $group, string $name, string $lang, int $id): void
    {
        $old = $this->canonical($group, $lang, $id);
        if ($old !== null && $old !== $name) throw new InvalidArgumentException('记录已属于其他语言变量');
        $target = $this->target($group, $name, $lang);
        if ($target) {
            if ((int)$target[self::ENTITIES[$group][0]] !== $id) throw new InvalidArgumentException('语言变量已被其他记录占用');
            return;
        }
        $this->repository->table('language_attr')->insert(['lang' => $lang, 'attr_group' => $group, 'attr_name' => $name, 'attr_value' => (string)$id, 'add_time' => time(), 'update_time' => time()]);
    }

    private function setting(string $lang, string $group, string $name, mixed $default): mixed
    {
        $rows = $this->repository->table('config')->where(['lang' => $lang, 'inc_type' => $group, 'name' => $name, 'is_del' => 0])->lock(true)->select()->toArray();
        if (count($rows) > 1) throw new InvalidArgumentException('语言配置重复');
        $value = $rows[0]['value'] ?? $default;
        if (is_string($value) && preg_match('/^(a|s|i|b|d|O):|^N;$/', $value)) {
            $decoded = @unserialize($value, ['allowed_classes' => false]);
            if ($decoded !== false || $value === 'b:0;') $value = $decoded;
        }
        return $value;
    }

    private function defaults(string $table, array $input): array
    {
        $data = $this->repository->filter($table, $input);
        foreach ($this->repository->columns($table) as $name => $column) {
            if ($column['Key'] === 'PRI' || array_key_exists($name, $data) || $column['Null'] === 'YES' || $column['Default'] !== null) continue;
            $data[$name] = preg_match('/int|decimal|float|double/i', $column['Type']) ? 0 : '';
        }
        return $data;
    }
}
