<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

class CategoryService
{
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    public function save(array $input, int $adminId, ?int $id = null): int
    {
        return $this->repository->transaction(function (CmsRepository $repository) use ($input, $adminId, $id) {
            $sync = new EnterpriseLanguageSyncService($repository); $sync->lock();
            $old = $id ? $repository->table('arctype')->where('id', $id)->lock(true)->find() : null;
            if ($id && !$old) { throw new InvalidArgumentException('栏目不存在'); }
            $contentAccess=new ContentAccessService($repository);
            if($old)$contentAccess->assertCategoryMutation([(int)$id]);else$contentAccess->assertCreateCategory();
            $input = (new CategoryFieldService())->categoryInput($input, (int)$id);
            $data = $repository->filter('arctype', $input);
            unset($data['admin_id'], $data['is_del'], $data['del_method'], $data['total_arc']);
            if (!$old && !isset($data['lang']) && !empty($data['parent_id'])) $data['lang'] = $repository->table('arctype')->where('id', (int)$data['parent_id'])->value('lang') ?? (new LanguagePolicy($this->repository))->main();
            $data = $sync->prepare('arctype', $data, $old ?: null);
            $values = array_replace($old ?: [], $data);
            if (trim((string)($values['typename'] ?? '')) === '') { throw new InvalidArgumentException('栏目名称不能为空'); }
            $parentId = (int)($values['parent_id'] ?? 0);
            $model = $repository->table('channeltype')->where('id', (int)($values['current_channel'] ?? 0))->find();
            if (!$model || !empty($model['is_del']) || (int)$model['id'] === 51) { throw new InvalidArgumentException('请选择企业官网内容模型'); }
            if ($old && (int)$old['current_channel'] !== (int)$model['id'] && $repository->table('archives')->where('typeid', $id)->find()) { throw new InvalidArgumentException('栏目已有文档，不能更换内容模型'); }
            $parent = $parentId ? $repository->table('arctype')->where(['id' => $parentId, 'is_del' => 0])->find() : null;
            if ($parentId && !$parent) { throw new InvalidArgumentException('上级栏目不存在'); }
            if($parent)$contentAccess->assertCategory($parent);
            if ($id && ($parentId === $id || in_array($parentId, $this->descendants($id), true))) { throw new InvalidArgumentException('不能将栏目移动到自身或子栏目'); }
            $language = $values['lang'] ?? $parent['lang'] ?? (new LanguagePolicy($this->repository))->main();
            if ($parent && $language !== $parent['lang']) { throw new InvalidArgumentException('父子栏目必须使用相同语言'); }
            $dirname = (new CategoryDirectoryService($repository))->resolve((string)$values['typename'], (string)($values['dirname'] ?? ''), $language, (int)$id);
            $paths = $this->paths($parent, $dirname, (string)($values['diy_dirpath'] ?? ''), $language, (bool)$old);
            $data = array_replace($data, [
                'dirname' => $dirname, 'dirpath' => $paths['dirpath'], 'diy_dirpath' => $paths['diy_dirpath'], 'parent_id' => $parentId,
                'topid' => $parent ? ((int)$parent['topid'] ?: $parentId) : 0,
                'grade' => $parent ? (int)$parent['grade'] + 1 : 0,
                // Original edit keeps the previous top-model group when promoted to a root.
                'channeltype' => $parent['channeltype'] ?? ($old ? (int)$old['channeltype'] : (int)$model['id']),
                'current_channel' => (int)$model['id'], 'lang' => $language, 'update_time' => time(),
            ]);
            $provenance=new HtmltextProvenanceService($repository);$sources=[];
            $htmlFields=$repository->table('channelfield')->where(['channel_id'=>-99,'dtype'=>'htmltext'])->column('name');
            $metadata=HtmltextProvenanceService::inputMetadata($input);
            foreach($htmlFields as$field)if(array_key_exists($field,$data)) {
                $after=(string)$data[$field];
                $sources[$field]=$old?$provenance->sourceForWrite('arctype','id',(int)$id,$field,(string)($old[$field]??''),$after,is_array($metadata[$field]??null)?$metadata[$field]:null):(($input['__htmltext_format']??'')==='raw-v1'?'raw':'unknown');
            }
            $creating = !$id;
            if ($creating) {
                $data += ['admin_id' => $adminId, 'status' => 1, 'add_time' => time(), 'sort_order' => 100, 'templist' => 'lists_' . $model['nid'] . '.htm', 'tempview' => 'view_' . $model['nid'] . '.htm'];
                $id = (int)$repository->table('arctype')->insertGetId($data);
            } else { $repository->table('arctype')->where('id', $id)->update($data); }
            foreach($sources as$field=>$source)$provenance->markTrusted('arctype','id',$id,$field,(string)$data[$field],$source);
            if ($creating) {
                $sync->ensureSingle($id);$peers=$sync->created('arctype',$id);
                foreach($peers as$peer)if((int)$peer!==$id)$provenance->copyRecord('arctype',$id,'arctype',(int)$peer,$htmlFields,'id');
            }
            else { $sync->categoryEdited($old, $data + $old); }
            $inheritance = $old && !empty($input['inherit_option']) ? [
                'source_channel' => (int)$old['current_channel'],
                'source_empty_logic' => $old['empty_logic'],
            ] : null;
            $this->rebuildChildren($id, $inheritance);
            (new NavigationService($repository))->refreshCategory($id);
            if ($old && array_key_exists('typename', $input) && (string)$input['typename'] !== (string)$old['typename']) {
                (new LanguageStateStore('language.pending', $repository))->remove('arctype.'.$id);
            }
            return $id;
        });
    }

    public function descendants(int $id): array
    {
        $all = []; $pending = [$id]; $seen = [$id => true];
        while ($pending) {
            $next = $this->repository->table('arctype')->whereIn('parent_id', $pending)->column('id'); $pending = [];
            foreach ($next as $child) {
                $child = (int)$child;
                if (isset($seen[$child])) { throw new InvalidArgumentException('栏目树存在循环，请先修复数据'); }
                $seen[$child] = true; $all[] = $child; $pending[] = $child;
            }
        }
        return $all;
    }

    public function recycle(array $ids, bool $restore = false): void
    {
        $this->repository->transaction(function (CmsRepository $repository) use ($ids, $restore) {
            $sync = new EnterpriseLanguageSyncService($repository); $sync->lock();
            $selected = array_values(array_unique(array_map('intval', $ids)));
            (new ContentAccessService($repository))->assertCategoryMutation($selected,$restore);
            if ($restore) {
                // Original RecycleBin::arctype_recovery selects is_del=1 before
                // restoring ancestors or passive document deletions. Validate
                // every explicit selection before a batch can change anything.
                if (!$selected) throw new InvalidArgumentException('请选择回收站中待恢复的栏目');
                foreach ($selected as $selectedId) {
                    $selectedRow = $repository->table('arctype')->where('id', $selectedId)->lock(true)->find();
                    if (!$selectedRow || empty($selectedRow['is_del'])) throw new InvalidArgumentException('栏目不存在或不在回收站，不能恢复');
                }
            }
            $roots = $sync->expand('arctype', $selected);
            $changed = [];
            if (!$restore) {
                // Expand only the selected source-language trees. A translated
                // peer can have independent local descendants, which upstream
                // does not include in this operation.
                $changed = $this->languageSubtree($sync, $selected);
                $repository->table('arctype')->whereIn('id', $changed)->where('is_del', 0)->update(['is_del' => 1, 'del_method' => 2, 'update_time' => time()]);
                $repository->table('archives')->whereIn('typeid', $changed)->where('is_del', 0)->update(['is_del' => 1, 'del_method' => 2, 'update_time' => time()]);
                if ($roots) $repository->table('arctype')->whereIn('id', $roots)->update(['is_del' => 1, 'del_method' => 1, 'update_time' => time()]);
                foreach ($changed as $typeid) (new ContentService($repository))->refreshCount((int)$typeid);
                $sync->recycled('arctype', $changed, false);
                return;
            }
            foreach ($roots as $id) {
                $category = $repository->table('arctype')->where('id', $id)->find();
                if (!$category) { continue; }
                if ($restore) {
                    // Restore ancestors as the original recycle-bin entry does,
                    // without restoring their independently deleted documents.
                    $parent = (int)$category['parent_id']; $seen = [$id => true];
                    while ($parent) {
                        if (isset($seen[$parent])) throw new InvalidArgumentException('栏目树存在循环');
                        $seen[$parent] = true;
                        $row = $repository->table('arctype')->where('id', $parent)->find();
                        if (!$row || $row['lang'] !== $category['lang']) throw new InvalidArgumentException('上级栏目不存在或语言不一致');
                        $repository->table('arctype')->where('id', $parent)->update(['is_del' => 0, 'del_method' => 0, 'update_time' => time()]);
                        $changed[] = $parent; $parent = (int)$row['parent_id'];
                    }
                    $active = []; $pending = [$id];
                    while ($pending) {
                        $current = array_shift($pending);
                        if (in_array($current, $active, true)) throw new InvalidArgumentException('栏目树存在循环');
                        $repository->table('arctype')->where('id', $current)->update(['is_del' => 0, 'del_method' => 0, 'update_time' => time()]);
                        $active[] = $current;
                        foreach ($repository->table('arctype')->where('parent_id', $current)->select()->toArray() as $child) {
                            if (empty($child['is_del']) || (int)$child['del_method'] === 2) $pending[] = (int)$child['id'];
                        }
                    }
                    $subtree = $active;
                    $repository->table('archives')->whereIn('typeid', $active)->where('del_method', 2)->update(['is_del' => 0, 'del_method' => 0, 'update_time' => time()]);
                }
                $changed = array_merge($changed, $subtree);
                foreach ($subtree as $typeid) { (new ContentService($repository))->refreshCount((int)$typeid); }
            }
            $sync->recycled('arctype', array_unique($changed), $restore);
        });
    }

    private function languageSubtree(EnterpriseLanguageSyncService $sync, array $ids): array
    {
        $all = array_values(array_unique(array_map('intval', $ids)));
        foreach ($all as $id) $all = array_merge($all, $this->descendants($id));
        return $sync->expand('arctype', array_values(array_unique($all)));
    }

    /** Original controller add/edit have deliberately different DIY behavior. */
    private function paths(?array $parent, string $dirname, string $diy, string $lang, bool $editing): array
    {
        $settings = [];
        foreach (['seo_pseudo' => 1, 'seo_html_listname' => 2, 'seo_html_pagename' => 2] as $name => $default) {
            $rows = $this->repository->table('config')->where(['lang' => $lang, 'inc_type' => 'seo', 'name' => $name, 'is_del' => 0])->lock(true)->select()->toArray();
            if (count($rows) > 1) throw new InvalidArgumentException('语言 SEO 配置重复');
            $value = $rows[0]['value'] ?? $default;
            if (is_string($value) && preg_match('/^(a|s|i|b|d|O):|^N;$/', $value)) {
                $decoded = @unserialize($value, ['allowed_classes' => false]);
                if ($decoded !== false || $value === 'b:0;') $value = $decoded;
            }
            $settings[$name] = !empty($value) ? (int)$value : $default;
        }
        $static = $settings['seo_pseudo'] === 2;
        $custom = $static && ($settings['seo_html_listname'] === 4 || $settings['seo_html_pagename'] === 4);
        $base = $parent ? ($static && $settings['seo_html_listname'] === 4 ? $parent['diy_dirpath'] : $parent['dirpath']) : '';
        $path = rtrim((string)$base, '/') . '/' . $dirname;
        $diy = !empty($diy) ? '/' . trim($diy, '/') : '';
        if (!$editing && !$custom) $diy = $path;
        if ($editing && $custom && $diy !== '') $path = $diy;
        return ['dirpath' => $path, 'diy_dirpath' => $diy];
    }

    private function rebuildChildren(int $parentId, ?array $inheritance = null): void
    {
        $parent = $this->repository->table('arctype')->where('id', $parentId)->find();
        if (!$parent) return;
        // getHasChildren() builds a filtered forest from parent_id=0. An
        // excluded ancestor also excludes every otherwise active descendant.
        $rows = $this->repository->table('arctype')->where(['lang' => $parent['lang'], 'status' => 1, 'is_del' => 0])
            ->order('parent_id asc, sort_order asc, id asc')->select()->toArray();
        $eligible = $children = [];
        foreach ($rows as $row) {
            $eligible[(int)$row['id']] = $row;
            $children[(int)$row['parent_id']][] = (int)$row['id'];
        }
        $ancestor = $parentId; $seen = [];
        while ($ancestor !== 0) {
            if (isset($seen[$ancestor])) throw new InvalidArgumentException('栏目树存在循环，请先修复数据');
            $seen[$ancestor] = true;
            if (!isset($eligible[$ancestor])) return;
            $ancestor = (int)$eligible[$ancestor]['parent_id'];
        }
        $pending = array_reverse($children[$parentId] ?? []); $seen = [$parentId => true];
        while ($pending) {
            $id = array_pop($pending);
            if (isset($seen[$id])) throw new InvalidArgumentException('栏目树存在循环，请先修复数据');
            $seen[$id] = true;
            $child = $eligible[$id]; $parent = $eligible[(int)$child['parent_id']];
            $path = rtrim($parent['dirpath'], '/') . '/' . $child['dirname'];
            $update = ['dirpath' => $path, 'topid' => (int)$parent['topid'] ?: (int)$parent['id'], 'grade' => (int)$parent['grade'] + 1, 'channeltype' => $parent['channeltype'], 'update_time' => time()];
            if ($inheritance !== null) {
                // Original updateData inherits through the same filtered tree.
                // Read persisted values after schema normalization, including
                // checkbox arrays converted to comma-separated page_limit.
                $root = $eligible[$parentId];
                $update += array_intersect_key($root, array_flip(['typearcrank', 'page_limit']));
                if ((int)$child['current_channel'] === $inheritance['source_channel']) {
                    $update += array_intersect_key($root, array_flip(['templist', 'tempview', 'rulelist', 'ruleview']));
                    if ((int)$child['current_channel'] === 6) $update['empty_logic'] = $inheritance['source_empty_logic'];
                }
            }
            $this->repository->table('arctype')->where('id', $child['id'])->update($update);
            $eligible[$id] = array_replace($child, $update);
            foreach (array_reverse($children[$id] ?? []) as $next) $pending[] = $next;
        }
    }

    public function purge(array $ids): void
    {
        $this->repository->transaction(function (CmsRepository $repository) use ($ids) {
            $sync = new EnterpriseLanguageSyncService($repository); $sync->lock();
            $all = $this->languageSubtree($sync, $ids);
            (new ContentAccessService($repository))->assertCategoryMutation($all);
            if ($repository->table('arctype')->whereIn('id', $all)->where('is_del', 0)->find()) { throw new InvalidArgumentException('只能彻底删除回收站内的栏目'); }
            // Upstream can orphan target-local children here. Reject the whole
            // transaction until they are moved or explicitly included instead.
            if ($repository->table('arctype')->whereIn('parent_id', $all)->whereNotIn('id', $all)->find()) throw new InvalidArgumentException('目标语言栏目下仍有独立子栏目，请先移动或一并选择');
            $documents = $repository->table('archives')->whereIn('typeid', $all)->column('aid');
            if ($documents) {
                (new ContentService($repository))->purge($documents);
                $sync->removeBindings('archives', $documents);
                $repository->table('language_archives_copy_log')->whereIn('oldid', $documents)->delete();
                $repository->table('language_archives_copy_log')->whereIn('newid', $documents)->delete();
            }
            $repository->table('channelfield_bind')->whereIn('typeid', $all)->delete();
            $attributes = $repository->table('guestbook_attribute')->where(['form_type' => 0])->whereIn('typeid', $all)->column('attr_id');
            if ($attributes) {
                $sync->removeBindings('guestbook_attribute', $attributes);
                $repository->table('guestbook_attr')->whereIn('attr_id', $attributes)->delete();
                $repository->table('guestbook_attribute')->whereIn('attr_id', $attributes)->delete();
            }
            $submissions = $repository->table('guestbook')->where('form_type', 0)->whereIn('typeid', $all)->column('aid');
            $repository->table('guestbook_attr')->whereIn('aid', $submissions)->delete();
            $repository->table('guestbook')->whereIn('aid', $submissions)->delete();
            $sync->removeBindings('arctype', $all);
            $repository->table('arctype')->whereIn('id', $all)->delete();
        });
    }
}
