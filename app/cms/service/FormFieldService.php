<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Guestbook/free-form field lifecycle; historical submissions survive recycling. */
final class FormFieldService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function list(array $input): array
    {
        $query = $this->repository->table('guestbook_attribute')->where('is_del', 1);
        foreach (['lang', 'typeid', 'form_type'] as $key) if (isset($input[$key]) && $input[$key] !== '') {
            if ($key === 'form_type') $this->formType($input[$key]);
            $query->where($key, $input[$key]);
        }
        $search = trim((string)($input['q'] ?? ''));
        if ($search !== '') $query->whereLike('attr_name', '%' . addcslashes($search, '%_') . '%');
        $count = (clone $query)->count();
        $rows = $query->order('update_time desc,attr_id desc')->page(max(1, (int)($input['page'] ?? 1)), min(100, max(1, (int)($input['limit'] ?? 20))))->select()->toArray();
        return ['code' => 0, 'msg' => 'ok', 'count' => $count, 'data' => $rows];
    }

    public function prepare(array $data, ?array $old): array
    {
        if ($old && !empty($old['is_del'])) throw new InvalidArgumentException('请先从回收站恢复表单字段');
        $field = $data + ($old ?? []) + ['lang' => (new LanguagePolicy($this->repository))->main(), 'form_type' => 0];
        $this->formType($field['form_type']);
        if ($old) foreach (['typeid', 'form_type', 'lang'] as $key) {
            if ((string)$field[$key] !== (string)$old[$key] && ($this->repository->table('guestbook_attr')->where('attr_id', $old['attr_id'])->find()
                || $this->repository->table('language_attr')->whereIn('attr_group', ['guestbook_attribute', 'form_attribute'])->where('attr_value', (string)$old['attr_id'])->find())) {
                throw new InvalidArgumentException('已有历史值或语言绑定的字段不能更换所属表单、模型或语言');
            }
        }
        $this->owner($field, false);
        return $data;
    }

    public function recycle(array $ids, ?int $formType = null): void { $this->change($ids, 'recycle', $formType); }
    public function restore(array $ids, ?int $formType = null): void { $this->change($ids, 'restore', $formType); }
    public function purge(array $ids, ?int $formType = null): void { $this->change($ids, 'purge', $formType); }

    private function change(array $values, string $action, ?int $formType): void
    {
        if ($formType !== null) $this->formType($formType);
        $this->repository->transaction(function (CmsRepository $r) use ($values, $action, $formType) {
            $sync = new EnterpriseLanguageSyncService($r); $sync->lock();
            $ids = $this->ids($values);
            $selected = $r->table('guestbook_attribute')->whereIn('attr_id', $ids)->order('attr_id')->lock(true)->select()->toArray();
            if (count($selected) !== count($ids)) throw new InvalidArgumentException('表单字段不存在');
            $targets = []; $bindings = []; $groups = [];
            foreach ($selected as $field) {
                $type = $this->formType($field['form_type']);
                if ($formType !== null && $type !== $formType) throw new InvalidArgumentException('字段不属于当前表单类型');
                if ((bool)$field['is_del'] !== ($action !== 'recycle')) throw new InvalidArgumentException($action === 'recycle' ? '字段已在回收站中' : '只能操作回收站内的表单字段');
                [$expanded, $fieldBindings, $canonical] = $this->expand($field, $sync);
                foreach ($expanded as $row) {
                    if ($action === 'purge' && empty($row['is_del'])) throw new InvalidArgumentException('同组仍有启用字段，请先回收整个字段组');
                    $this->owner($row, $action === 'purge');
                    $targets[(int)$row['attr_id']] = $row;
                }
                foreach ($fieldBindings as $row) $bindings[(int)$row['id']] = $row;
                if ($canonical !== null) $groups[$canonical[0] . "\0" . $canonical[1]] = $canonical;
            }
            $targetIds = array_keys($targets);
            if ($action === 'purge') {
                $r->table('guestbook_attr')->whereIn('attr_id', $targetIds)->delete();
                $r->table('guestbook_attribute')->whereIn('attr_id', $targetIds)->delete();
                if ($bindings) $r->table('language_attr')->whereIn('id', array_keys($bindings))->delete();
                foreach ($groups as [$group, $name]) {
                    if (!$r->table('language_attr')->where(['attr_group' => $group, 'attr_name' => $name])->find()) {
                        $r->table('language_attribute')->where(['attr_group' => $group, 'attr_name' => $name])->delete();
                    }
                }
            } else {
                $data = ['is_del' => $action === 'restore' ? 0 : 1, 'update_time' => time()];
                if ($action === 'restore') $data['is_showlist'] = 0;
                $r->table('guestbook_attribute')->whereIn('attr_id', $targetIds)->update($data);
            }
        });
    }

    /** Resolve the selected field's actual canonical name, including translated IDs. */
    private function expand(array $field, EnterpriseLanguageSyncService $sync): array
    {
        $r = $this->repository; $type = $this->formType($field['form_type']);
        $group = $type === 1 ? 'form_attribute' : 'guestbook_attribute';
        $source = $r->table('language_attr')->whereIn('attr_group', ['guestbook_attribute', 'form_attribute'])->where('attr_value', (string)$field['attr_id'])->lock(true)->select()->toArray();
        if (!$source) return [[$field], [], null]; // Legitimate independent field.
        if (count($source) !== 1 || $source[0]['attr_group'] !== $group || $source[0]['lang'] !== $field['lang']) throw new InvalidArgumentException('字段语言绑定冲突，请先修复映射');
        $name = (string)$source[0]['attr_name'];
        $definitions = $r->table('language_attribute')->where(['attr_group' => $group, 'attr_name' => $name])->lock(true)->select()->toArray();
        if (count($definitions) !== 1 || !empty($definitions[0]['is_del'])) throw new InvalidArgumentException('字段语言定义缺失或重复');
        $all = $r->table('language_attr')->where(['attr_group' => $group, 'attr_name' => $name])->order('id')->lock(true)->select()->toArray();
        $rows = []; $seen = [];
        foreach ($all as $binding) {
            if (!ctype_digit((string)$binding['attr_value']) || (int)$binding['attr_value'] < 1 || isset($seen[$binding['lang']])) throw new InvalidArgumentException('字段语言组存在重复或无效映射');
            $seen[$binding['lang']] = true;
            $row = $r->table('guestbook_attribute')->where('attr_id', (int)$binding['attr_value'])->lock(true)->find();
            if (!$row || $this->formType($row['form_type']) !== $type || $row['lang'] !== $binding['lang']) throw new InvalidArgumentException('字段语言组跨模型或语言冲突');
            $aliases = $r->table('language_attr')->whereIn('attr_group', ['guestbook_attribute', 'form_attribute'])->where('attr_value', (string)$row['attr_id'])->lock(true)->select()->toArray();
            if (count($aliases) !== 1) throw new InvalidArgumentException('字段绑定到多个语言组');
            $rows[] = $row;
        }
        if (count($all) > 1) $this->validateOwnerGroup($field, $rows);
        $shared = $sync->policy((string)$field['lang'])['shared'];
        if ($shared) foreach ($rows as $row) if ($this->split((string)$row['lang'])) throw new InvalidArgumentException('字段语言组的独立编辑配置不一致');
        return $shared ? [$rows, $all, [$group, $name]] : [[$field], $source, [$group, $name]];
    }

    private function validateOwnerGroup(array $source, array $fields): void
    {
        $r = $this->repository; $group = (int)$source['form_type'] === 1 ? 'form' : 'arctype';
        $bindings = $r->table('language_attr')->where(['attr_group' => $group, 'attr_value' => (string)$source['typeid']])->lock(true)->select()->toArray();
        if (count($bindings) !== 1 || $bindings[0]['lang'] !== $source['lang']) throw new InvalidArgumentException('所属表单语言绑定缺失或重复');
        $definition = $r->table('language_attribute')->where(['attr_group' => $group, 'attr_name' => $bindings[0]['attr_name']])->lock(true)->select()->toArray();
        if (count($definition) !== 1 || !empty($definition[0]['is_del'])) throw new InvalidArgumentException('所属表单语言定义缺失或重复');
        $owners = $r->table('language_attr')->where(['attr_group' => $group, 'attr_name' => $bindings[0]['attr_name']])->lock(true)->select()->toArray();
        $mapped = [];
        foreach ($owners as $binding) {
            if (isset($mapped[$binding['lang']]) || !ctype_digit((string)$binding['attr_value']) || (int)$binding['attr_value'] < 1) throw new InvalidArgumentException('所属表单语言组冲突');
            $mapped[$binding['lang']] = (int)$binding['attr_value'];
        }
        foreach ($fields as $field) {
            if (($mapped[$field['lang']] ?? 0) !== (int)$field['typeid']) throw new InvalidArgumentException('字段与所属表单的语言组不一致');
            $aliases = $r->table('language_attr')->where(['attr_group' => $group, 'attr_value' => (string)$field['typeid']])->lock(true)->select()->toArray();
            if (count($aliases) !== 1 || $aliases[0]['attr_name'] !== $bindings[0]['attr_name'] || $aliases[0]['lang'] !== $field['lang']) throw new InvalidArgumentException('所属表单绑定到多个语言组');
        }
    }

    private function owner(array $field, bool $allowMissing): void
    {
        $type = $this->formType($field['form_type']);
        $owner = $this->repository->table($type === 1 ? 'form' : 'arctype')->where($type === 1 ? 'form_id' : 'id', (int)($field['typeid'] ?? 0))->lock(true)->find();
        if (!$owner) {
            if ($allowMissing) return;
            throw new InvalidArgumentException('所属表单不存在，请先恢复关联栏目或表单');
        }
        if ($owner['lang'] !== $field['lang'] || ($type === 0 && (int)$owner['current_channel'] !== 8)) throw new InvalidArgumentException('所属表单模型或语言不匹配');
        if (!$allowMissing && !empty($owner['is_del'])) throw new InvalidArgumentException('请先恢复关联栏目');
    }

    private function split(string $lang): bool
    {
        $rows = $this->repository->table('config')->where(['name' => 'language_split', 'inc_type' => 'language', 'lang' => $lang, 'is_del' => 0])->lock(true)->select()->toArray();
        if (count($rows) > 1) throw new InvalidArgumentException('语言独立编辑配置重复');
        $value = $rows[0]['value'] ?? '0';
        if (is_string($value) && preg_match('/^(a|s|i|b|d|O):|^N;$/', $value)) {
            $decoded = @unserialize($value, ['allowed_classes' => false]);
            if ($decoded !== false || $value === 'b:0;') $value = $decoded;
        }
        return !empty($value);
    }

    private function formType(mixed $type): int
    {
        if (!in_array($type, [0, 1, '0', '1'], true)) throw new InvalidArgumentException('表单类型无效');
        return (int)$type;
    }

    private function ids(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            if ((!is_int($value) && (!is_string($value) || !ctype_digit($value))) || (int)$value < 1) throw new InvalidArgumentException('字段ID无效');
            $ids[(int)$value] = (int)$value;
        }
        if (!$ids) throw new InvalidArgumentException('请选择表单字段');
        return array_values($ids);
    }
}
