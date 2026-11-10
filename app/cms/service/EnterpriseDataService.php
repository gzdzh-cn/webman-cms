<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Small enterprise modules retain Eyou's actual tables and fields. */
class EnterpriseDataService
{
    public const MODULES = [
        'ad' => '广告轮播', 'ad_position' => '广告位置', 'links' => '友情链接', 'links_group' => '友链分组',
        'config' => '网站设置', 'config_attribute' => '自定义变量', 'nav_list' => '自定义导航', 'nav_position' => '导航位置',
        'form' => '表单管理', 'guestbook_attribute' => '表单字段', 'guestbook' => '留言管理',
        'download_file' => '下载附件', 'media_file' => '视频附件', 'smtp_tpl' => '邮件模板', 'sms_template' => '短信模板', 'sms_log' => '短信记录',
        'tagindex' => 'TAG管理', 'search_word' => '搜索词', 'language' => '语言管理', 'admin_log' => '操作日志',
    ];

    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    public function schema(string $module): array
    {
        if (!isset(self::MODULES[$module])) { throw new InvalidArgumentException('不支持的企业模块'); }
        return $this->repository->columns($module);
    }

    public function list(string $module, array $input): array
    {
        if ($module === 'config_attribute') return (new CustomVariableService($this->repository))->list($input);
        $schema = $this->schema($module); $query = $this->repository->table($module);
        $access=new ContentAccessService($this->repository);
        if(in_array($module,['download_file','media_file'],true))$access->filterArchiveOwnerQuery($query);
        if($module==='nav_list'&&!$access->unrestricted())$query->whereIn('type_id',$access->filterCategoryQuery($this->repository->table('arctype'))->column('id'));
        if(isset($schema['lang']))$query->where('lang',\app\cms\CmsContext::current()->lang);
        if ($module === 'sms_template') $query->whereIn('send_scene', [7, 11]);
        if ($module === 'smtp_tpl') $query->whereIn('send_scene', [1, 40]);
        $editingLanguage=\app\cms\CmsContext::current()->lang;
        if (in_array($module,['download_file','media_file'],true)) {
            $query->whereIn('aid',$this->repository->table('archives')->where('lang',$editingLanguage)->column('aid'));
        }
        if ($module==='nav_list') {
            $query->whereIn('type_id',array_merge([0],$this->repository->table('arctype')->where('lang',$editingLanguage)->column('id')));
        }

        if (isset($schema['is_del'])) { $query->where('is_del', 0); }
        foreach (['lang', 'pid', 'typeid', 'form_type', 'groupid', 'inc_type', 'form_id', 'position_id', 'aid', 'sms_type', 'source', 'send_scene', 'status', 'is_read', 'is_open', 'is_hot', 'examine'] as $key) {
            if (isset($schema[$key]) && isset($input[$key]) && $input[$key] !== '') { $query->where($key, $input[$key]); }
        }
        $search = trim((string)($input['q'] ?? ''));
        if ($search !== '') {
            $fields = array_keys(array_filter($schema, fn($column) => preg_match('/char|text/i', $column['Type'])));
            if ($fields) { $query->whereLike(implode('|', array_slice($fields, 0, 12)), '%' . addcslashes($search, '%_') . '%'); }
        }
        $primary = $this->primary($schema);
        if(isset($input['_ui_record']))$query->where($primary,(int)$input['_ui_record']);
        $count = (clone $query)->count();
        $rows = $query->order(isset($schema['sort_order']) ? 'sort_order asc,' . $primary . ' desc' : $primary . ' desc')->page(max(1, (int)($input['page'] ?? 1)), min(100, max(1, (int)($input['limit'] ?? 20))))->select()->toArray();
        if ($module === 'guestbook' && $rows) {
            foreach ($rows as &$row) {
                $row['fields'] = $this->repository->table('guestbook_attr')->alias('value')->leftJoin('guestbook_attribute attribute', 'value.attr_id=attribute.attr_id')->where('value.aid', $row['aid'])->field('attribute.attr_name,attribute.is_showlist,value.attr_value')->select()->toArray();
            }
            unset($row);
        }
        if(in_array($module,['ad_position','links_group','form','guestbook'],true)) {
            foreach($rows as &$row) {
                $impact=[];
                if($module==='ad_position')$impact['广告']=$this->repository->table('ad')->where('pid',$row['id'])->count();
                if($module==='links_group')$impact['友情链接']=$this->repository->table('links')->where('groupid',$row['id'])->count();
                if($module==='form') {
                    $impact['表单字段']=$this->repository->table('guestbook_attribute')->where(['typeid'=>$row['form_id'],'form_type'=>1])->count();
                    $impact['提交记录']=$this->repository->table('guestbook')->where(['typeid'=>$row['form_id'],'form_type'=>1])->count();
                }
                if($module==='guestbook')$impact['提交字段']=$this->repository->table('guestbook_attr')->where('aid',$row['aid'])->count();
                $row['_ui_delete_impact']=$impact;
            }unset($row);
        }
        if ($module === 'sms_log') {
            foreach ($rows as &$row) { unset($row['code'], $row['msg']); }
            unset($row);
        }
        return ['count' => $count, 'data' => $rows, 'code' => 0, 'msg' => 'ok'];
    }

    /** Exact editor read with the same list/owner restrictions and current language. */
    public function record(string $module,int $id): ?array
    {
        if($id<1)return null;
        $result=$this->list($module,['_ui_record'=>$id,'lang'=>\app\cms\CmsContext::current()->lang,'limit'=>1]);
        return $result['data'][0]??null;
    }

    public function save(string $module, array $input): int
    {
        $schema = $this->schema($module);
        if ($module === 'tagindex') { $this->tagEngines(); }
        if ($module === 'language') return (new LanguageService($this->repository))->save($input);
        if (in_array($module, ['admin_log', 'sms_log'], true)) { throw new InvalidArgumentException('日志只读'); }
        $primary = $this->primary($schema); $id = (int)($input[$primary] ?? 0);
        return $this->repository->transaction(function (CmsRepository $repository) use ($module, $schema, $primary, $id, $input) {
            $sync = EnterpriseLanguageSyncService::supports($module) ? new EnterpriseLanguageSyncService($repository) : null;
            if ($sync) $sync->lock();
            $fieldSync = $module === 'guestbook_attribute' ? new FormFieldLanguageSyncService($repository) : null;
            if ($fieldSync) $fieldSync->lock();
            $tagLanguage = $module === 'tagindex' ? $this->lockTagLanguage($input, $id) : null;
            $old = $id ? $repository->table($module)->where($primary, $id)->lock(true)->find() : null;
            if ($id && !$old) { throw new InvalidArgumentException('记录不存在'); }
            $this->assertScope($module,$input+($old??[]));
            if($old)$this->assertScope($module,$old);
            if ($old && $module === 'tagindex' && $old['lang'] !== $tagLanguage) { throw new InvalidArgumentException('TAG语言归属已改变，请重新读取'); }
            if ($id && $module === 'config_attribute') (new CustomVariableService($repository))->assertEditable($id);
            if ($old && $module === 'config' && !empty($old['is_del'])) throw new InvalidArgumentException('请先从回收站恢复配置变量');
            $data = $repository->filter($module, $input);
            unset($data['is_del']);
            if ($sync) $data = $sync->prepare($module, $data, $old ?: null);
            $data = $this->prepare($module, $data, $old, $id, $input);
            if ($module === 'guestbook') {
                $data = array_intersect_key($data, array_flip(['is_read', 'is_star', 'examine', 'reply']));
                if (array_key_exists('reply', $data)) { $data['reply_time'] = time(); $data['admin_id'] = (int)(function_exists('admin_id') ? admin_id() : 0); }
                if (!$id) { throw new InvalidArgumentException('留言通过网站表单提交'); }
            }
            if (isset($schema['update_time'])) { $data['update_time'] = time(); }
            if ($id) { $repository->table($module)->where($primary, $id)->update($data); $this->afterSave($module, $id, $data + $old, $old, $input); return $id; }
            if (isset($schema['add_time'])) { $data['add_time'] = time(); }
            if (isset($schema['lang'])) { $data += ['lang' => \app\cms\CmsContext::current()->lang]; }
            foreach ($schema as $key => $column) {
                if ($key === $primary || array_key_exists($key, $data) || $column['Null'] === 'YES' || $column['Default'] !== null) { continue; }
                $data[$key] = preg_match('/int|decimal|float|double/i', $column['Type']) ? 0 : '';
            }
            $newId = (int)$repository->table($module)->insertGetId($data);
            $this->afterSave($module, $newId, $data, null, $input);
            if ($sync) $sync->created($module, $newId);
            if ($fieldSync) $fieldSync->created($newId);
            return $newId;
        });
    }

    public function delete(string $module, array $ids): void
    {
        $schema = $this->schema($module);
        if ($module === 'tagindex') { $this->tagEngines(); }
        if ($module === 'config_attribute') { (new CustomVariableService($this->repository))->recycle($ids); return; }
        if ($module === 'guestbook_attribute') { (new FormFieldService($this->repository))->recycle($ids); return; }
        if (in_array($module, ['admin_log', 'sms_log', 'language'], true)) { throw new InvalidArgumentException('此模块不允许直接删除'); }
        $ids = array_filter(array_unique(array_map('intval', $ids)));
        if (!$ids) { throw new InvalidArgumentException('请选择记录'); }
        $this->repository->transaction(function (CmsRepository $repository) use ($module, $schema, $ids) {
            $sync = EnterpriseLanguageSyncService::supports($module) ? new EnterpriseLanguageSyncService($repository) : null;
            if ($sync) { $sync->lock(); $ids = $sync->expand($module, $ids); }
            if ($module === 'tagindex') {
                $owners = $repository->table('tagindex')->whereIn('id', $ids)->column('lang', 'id');
                $languages = array_values(array_unique($owners)); sort($languages, SORT_STRING);
                foreach ($languages as $lang) { $repository->table('language')->where('mark', $lang)->lock(true)->find(); }
                $ordered = $ids; sort($ordered, SORT_NUMERIC);
                foreach ($ordered as $id) {
                    $current = $repository->table('tagindex')->where('id', $id)->lock(true)->find();
                    if ((bool)$current !== array_key_exists($id, $owners) || ($current && $current['lang'] !== $owners[$id])) { throw new InvalidArgumentException('TAG记录或语言归属已改变，请重新读取'); }
                }
            }
            foreach($repository->table($module)->whereIn($this->primary($schema),$ids)->lock(true)->select()->toArray()as$row)$this->assertScope($module,$row);
            if($module==='tagindex'&&!(new ContentAccessService($repository))->unrestricted()){foreach($repository->table('taglist')->whereIn('tid',$ids)->column('aid')as$aid)(new ContentAccessService($repository))->assertArchive((int)$aid);}
            if ($module === 'config' && $repository->table('config')->alias('c')->join($repository->fullName('config_attribute') . ' a', 'a.attr_var_name=c.name AND a.lang=c.lang')->whereIn('c.id', $ids)->find()) {
                throw new InvalidArgumentException('请通过自定义变量管理回收变量，保留原值以便恢复');
            }
            foreach (['nav_position' => ['nav_list', 'position_id']] as $owner => [$child, $foreign]) {
                if ($module === $owner && $repository->table($child)->whereIn($foreign, $ids)->where($child === 'guestbook' ? ['form_type' => 1] : (isset($repository->columns($child)['is_del']) ? ['is_del' => 0] : []))->find()) { throw new InvalidArgumentException('请先处理关联记录'); }
            }
            if ($module === 'nav_list' && $repository->table('nav_list')->whereIn('parent_id', $ids)->where('is_del', 0)->whereNotIn('nav_id', $ids)->find()) { throw new InvalidArgumentException('请先删除下级导航'); }
            if ($module === 'ad_position') {
                $children = $repository->table('ad')->whereIn('pid', $ids)->column('id');
                $sync->removeBindings('ad', $children);
                $repository->table('ad')->whereIn('id', $children)->delete();
            }
            if ($module === 'links_group') {
                foreach ($repository->table('links_group')->whereIn('id', $ids)->column('lang') as $lang) {
                    if (!$repository->table('links_group')->where('lang', $lang)->whereNotIn('id', $ids)->find()) throw new InvalidArgumentException('至少保留一个友情链接分组');
                }
                $children = $repository->table('links')->whereIn('groupid', $ids)->column('id');
                $sync->removeBindings('links', $children);
                $repository->table('links')->whereIn('id', $children)->delete();
            }
            if ($module === 'form') {
                $submissions = $repository->table('guestbook')->whereIn('typeid', $ids)->where('form_type', 1)->column('aid');
                $attributes = $repository->table('guestbook_attribute')->whereIn('typeid', $ids)->where('form_type', 1)->column('attr_id');
                $repository->table('guestbook_attr')->whereIn('aid', $submissions)->delete();
                $repository->table('guestbook_attr')->whereIn('attr_id', $attributes)->delete();
                $repository->table('guestbook')->whereIn('aid', $submissions)->delete();
                $sync->removeBindings('form_attribute', $attributes);
                $repository->table('guestbook_attribute')->whereIn('attr_id', $attributes)->delete();
            }
            if ($module === 'guestbook') { $repository->table('guestbook_attr')->whereIn('aid', $ids)->delete(); }
            if ($module === 'tagindex') { $repository->table('taglist')->whereIn('tid', $ids)->delete(); }
            $attachmentAids = in_array($module, ['download_file', 'media_file'], true) ? $repository->table($module)->whereIn('file_id', $ids)->column('aid') : [];
            $query = $repository->table($module)->whereIn($this->primary($schema), $ids);
            if ($sync) $sync->removeBindings($module, $ids);
            if (isset($schema['is_del']) && $module !== 'config' && !$sync) { $query->update(['is_del' => 1]); } else { $query->delete(); }
            if ($attachmentAids) { (new AttachmentService($repository))->refresh($module, $attachmentAids); }
        });
    }

    private function prepare(string $module, array $data, ?array $old, int $id, array $input = []): array
    {
        if (in_array($module, ['download_file', 'media_file'], true)) { return (new AttachmentService($this->repository))->prepare($module, $data, $old); }
        $repository = $this->repository;
        if ($module === 'nav_list') $data = (new NavigationService($repository))->prepare($data, $old, isset($input['lang']) ? (string)$input['lang'] : null);
        $merged = $data + ($old ?? []);
        if ($module === 'tagindex') {
            $tag = array_key_exists('tag', $input) ? $input['tag'] : ($old['tag'] ?? '');
            $lang = array_key_exists('lang', $input) ? $input['lang'] : ($old['lang'] ?? \app\cms\CmsContext::current()->lang);
            if (!is_string($tag) || trim($tag) === '' || mb_strlen(trim($tag)) > 50) { throw new InvalidArgumentException('TAG名称需为1至50个字符'); }
            if (!is_string($lang) || ($old && $lang !== $old['lang'])) { throw new InvalidArgumentException('不能修改TAG所属语言'); }
            if (!$repository->table('language')->where('mark', $lang)->find()) { throw new InvalidArgumentException('TAG语言不存在'); }
            $data['tag'] = trim($tag); $data['lang'] = $lang;
            $duplicate = $repository->table('tagindex')->where(['tag' => $data['tag'], 'lang' => $lang]);
            if ($id) { $duplicate->where('id', '<>', $id); }
            if ($duplicate->lock(true)->find()) { throw new InvalidArgumentException('该语言的TAG名称已存在'); }
            if ($id) {
                foreach ($repository->table('taglist')->where('tid', $id)->lock(true)->select()->toArray() as $association) {
                    if ($association['lang'] !== $lang) { throw new InvalidArgumentException('TAG关联存在语言归属异常，已取消保存'); }
                }
            }
        }
        if (in_array($module, ['sms_template', 'smtp_tpl'], true)) {
            $allowed = $module === 'sms_template' ? [7, 11] : [1, 40];
            if (!in_array((int)($merged['send_scene'] ?? 0), $allowed, true) || ($old && !in_array((int)$old['send_scene'], $allowed, true))) throw new InvalidArgumentException('仅支持企业表单场景');
            if ($module === 'sms_template' && !in_array((int)($merged['sms_type'] ?? 1), [1, 2], true)) throw new InvalidArgumentException('不支持的短信平台');
            if (trim((string)($merged['tpl_title'] ?? '')) === '' || trim((string)($merged['tpl_content'] ?? '')) === '') throw new InvalidArgumentException('模板标题及内容不能为空');
            $duplicates = $repository->table($module)->where(['send_scene' => (int)$merged['send_scene'], 'lang' => $merged['lang'] ?? \app\cms\CmsContext::current()->lang]);
            if ($module === 'sms_template') $duplicates->where('sms_type', (int)($merged['sms_type'] ?? 1));
            if ($id) $duplicates->where('tpl_id', '<>', $id);
            if ($duplicates->find()) throw new InvalidArgumentException('该语言和服务商的场景模板已存在');
        }
        $required = ['ad_position' => 'title', 'links' => 'title', 'links_group' => 'group_name', 'nav_list' => 'nav_name', 'nav_position' => 'position_name', 'form' => 'form_name', 'guestbook_attribute' => 'attr_name', 'tagindex' => 'tag', 'config_attribute' => 'attr_name', 'config' => 'name'];
        if (isset($required[$module]) && trim((string)($merged[$required[$module]] ?? '')) === '') { throw new InvalidArgumentException('名称不能为空'); }
        if ($module === 'config') {
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,99}$/', (string)$merged['name'])) { throw new InvalidArgumentException('变量名称格式错误'); }
            $duplicate = $repository->table('config')->where(['name' => $merged['name'], 'lang' => $merged['lang'] ?? \app\cms\CmsContext::current()->lang]);
            if ($id) { $duplicate->where('id', '<>', $id); }
            if ($duplicate->find()) { throw new InvalidArgumentException('变量名称已存在'); }
        }
        if ($module === 'config_attribute') {
            $data = (new CustomVariableService($repository))->prepare($data, $old, $input);
            $merged = $data + ($old ?? []);
            $name = trim((string)($merged['attr_var_name'] ?? ''));
            if ($name !== '' && !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,49}$/', $name)) { throw new InvalidArgumentException('变量名称格式错误'); }
            if ($name !== '') {
                $duplicate = $repository->table('config_attribute')->where(['attr_var_name' => $name, 'lang' => $merged['lang'] ?? \app\cms\CmsContext::current()->lang]);
                if ($id) { $duplicate->where('attr_id', '<>', $id); }
                if ($duplicate->find()) { throw new InvalidArgumentException('变量名称已存在'); }
                if ((!$old || $old['attr_var_name'] !== $name) && $repository->table('config')->where(['name' => $name, 'lang' => $merged['lang'] ?? \app\cms\CmsContext::current()->lang])->find()) { throw new InvalidArgumentException('不能覆盖已有网站变量'); }
            }
            $data['inc_type'] = ($merged['inc_type'] ?? '') ?: 'web';
        }
        if ($module === 'guestbook_attribute') {
            $data = (new FormFieldService($repository))->prepare($data, $old);
            if ((int)($merged['attr_input_type'] ?? 0) < 0 || (int)($merged['attr_input_type'] ?? 0) > 11) { throw new InvalidArgumentException('表单字段类型不支持'); }
        }
        if ($module === 'nav_list') {
            $position = (int)($merged['position_id'] ?? 0);
            if (!$repository->table('nav_position')->where(['position_id' => $position, 'is_del' => 0])->find()) { throw new InvalidArgumentException('导航位置不存在'); }
            $parentId = (int)($merged['parent_id'] ?? 0); $visited = [$id]; $ancestor = $parentId; $top = 0;
            while ($ancestor) {
                if (in_array($ancestor, $visited, true)) { throw new InvalidArgumentException('导航不能移动到自身或下级导航'); }
                $visited[] = $ancestor;
                $parent = $repository->table('nav_list')->where(['nav_id' => $ancestor, 'is_del' => 0])->find();
                if (!$parent || (int)$parent['position_id'] !== $position) { throw new InvalidArgumentException('上级导航不存在或位置不一致'); }
                $top = (int)$parent['nav_id']; $ancestor = (int)$parent['parent_id'];
            }
            $data['topid'] = $top;
        }
        return $data;
    }

    private function afterSave(string $module, int $id, array $data, ?array $old, array $input = []): void
    {
        $repository = $this->repository;
        // Eyou Tags::edit propagates a renamed tag by tid. Keep both writes
        // in this transaction and never replace another tag with the same name.
        if ($module === 'tagindex' && $old && $data['tag'] !== $old['tag']) {
            $repository->table('taglist')->where(['tid' => $id, 'lang' => $old['lang']])->update(['tag' => $data['tag'], 'update_time' => time()]);
        }
        if (in_array($module, ['download_file', 'media_file'], true)) { (new AttachmentService($repository))->refresh($module, [(int)$data['aid']]); }
        if ($module === 'config_attribute') {
            $name = trim((string)($data['attr_var_name'] ?? '')) ?: 'web_attr_' . $id;
            $repository->table('config_attribute')->where('attr_id', $id)->update(['attr_var_name' => $name]);
            $lang = $data['lang'] ?? \app\cms\CmsContext::current()->lang; $group = $data['inc_type'] ?: 'web';
            $existing = $old ? $repository->table('config')->where(['name' => $old['attr_var_name'], 'lang' => $old['lang']])->find() : null;
            $owners = $repository->table('config')->where(['name' => $name, 'lang' => $lang])->lock(true)->select()->toArray();
            if (count($owners) > 1 || ($owners && (!$existing || (int)$owners[0]['id'] !== (int)$existing['id']))) throw new InvalidArgumentException('不能覆盖已有网站变量');
            $payload = ['name' => $name, 'inc_type' => $group, 'lang' => $lang, 'update_time' => time()];
            if (array_key_exists('attr_value', $input) || array_key_exists('value', $input)) $payload['value'] = (new CustomVariableService($repository))->value((int)$data['attr_input_type'], array_key_exists('attr_value', $input) ? $input['attr_value'] : $input['value']);
            if ($existing) { $repository->table('config')->where('id', $existing['id'])->update($payload); }
            else { $repository->table('config')->insert($repository->filter('config', $payload + ['value' => '', 'add_time' => time()])); }
        }
        if ($module === 'nav_list') {
            $queue = [$id]; $seen = [];
            while ($queue) {
                $parent = array_shift($queue);
                if (isset($seen[$parent])) { throw new InvalidArgumentException('导航层级存在循环'); }
                $seen[$parent] = true;
                $current = $repository->table('nav_list')->where('nav_id', $parent)->find();
                $children = $repository->table('nav_list')->where('parent_id', $parent)->where('is_del', 0)->column('nav_id');
                foreach ($children as $child) { $repository->table('nav_list')->where('nav_id', $child)->update(['topid' => $current['topid'] ?: $current['nav_id'], 'position_id' => $current['position_id'], 'update_time' => time()]); $queue[] = $child; }
            }
        }
    }

    public function primary(array $schema): string
    {
        foreach ($schema as $name => $column) { if ($column['Key'] === 'PRI') { return $name; } }
        throw new InvalidArgumentException('数据表缺少主键');
    }

    private function assertScope(string $module,array $row): void
    {
        $access=new ContentAccessService($this->repository);
        if(in_array($module,['download_file','media_file'],true))$access->assertArchive((int)($row['aid']??0));
        if($module==='nav_list'&&(int)($row['type_id']??0)>0)$access->assertCategory((int)$row['type_id']);
    }

    private function tagEngines(): void
    {
        foreach (['language', 'tagindex', 'taglist'] as $table) {
            $rows = $this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$this->repository->fullName($table)]);
            if (strtolower((string)($rows[0]['ENGINE'] ?? '')) !== 'innodb') { throw new InvalidArgumentException('TAG与语言表必须使用InnoDB事务引擎'); }
        }
    }

    /** Locate first without a row lock, then serialize every TAG writer by language. */
    private function lockTagLanguage(array $input, int $id): string
    {
        $owner = $id ? $this->repository->table('tagindex')->where('id', $id)->find() : null;
        if ($id && !$owner) { throw new InvalidArgumentException('记录不存在'); }
        $lang = array_key_exists('lang', $input) ? $input['lang'] : ($owner['lang'] ?? \app\cms\CmsContext::current()->lang);
        if (!is_string($lang) || ($owner && $lang !== $owner['lang'])) { throw new InvalidArgumentException('不能修改TAG所属语言'); }
        if (!$this->repository->table('language')->where('mark', $lang)->lock(true)->find()) { throw new InvalidArgumentException('TAG语言不存在'); }
        return $lang;
    }
}
