<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;
use plugin\admin\app\common\CmsUtil;
use plugin\admin\app\logic\{CategoryFieldLogic, FieldLogic};
use plugin\admin\app\model\{Channelfield, ChannelfieldBind, FieldCustomParam};

/** Category field contract adapted from EyouCMS Field/arctype_* and FieldLogic.
 * 易优CMS 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 */
class CategoryFieldService extends FieldService
{
    public const CHANNEL_ID = -99;

    public function save(array $input, int $channelId = self::CHANNEL_ID, ?int $id = null): int
    {
        if ($channelId !== self::CHANNEL_ID) throw new InvalidArgumentException('栏目字段类型无效');
        $old = $id ? Channelfield::find($id) : null;
        if ($id && (!$old || (int)$old->channel_id !== self::CHANNEL_ID)) throw new InvalidArgumentException('栏目字段不存在');
        if ($old && $old->ifsystem) throw new InvalidArgumentException('系统栏目字段不能修改');
        if (isset($input['channel_id']) && (int)$input['channel_id'] !== self::CHANNEL_ID) throw new InvalidArgumentException('不能更改字段所属类型');
        if ($old) CategoryFieldLogic::assertTypeTransition((string)$old->dtype, trim((string)($input['dtype'] ?? $old->dtype)));
        $quick = $old && count(array_diff(array_keys($input), ['id', 'ifeditable', 'sort_order', 'ifrequire'])) === 0;
        if (!$quick && in_array($input['dtype'] ?? $old?->dtype, ['checkboxs', 'radios', 'selects'], true)) {
            $existing = $old ? $this->optionsForField($old->toArray()) : [];
            $input['mcheck'] ??= $existing;
            self::optionPlan($existing, $input['mcheck']);
        }
        return $this->schemaLock(function () use ($input, $id, $old) {
            $name = trim((string)($input['name'] ?? $old?->name ?? ''));
            if (!$id || $name !== $old->name) $this->assertNewOptionNamespace($name);
            return parent::save($input, self::CHANNEL_ID, $id);
        });
    }

    public function delete(array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids || Channelfield::whereIn('id', $ids)->where('channel_id', self::CHANNEL_ID)->count() !== count($ids)) throw new InvalidArgumentException('栏目字段不存在');
        $this->schemaLock(function () use ($ids) { parent::delete($ids); });
    }

    protected function fieldLogic(): FieldLogic { return new CategoryFieldLogic(); }
    protected function newIfMain(int $channelId): int { return 1; }
    protected function validBackupFieldName(string $name): bool { return (bool)preg_match('/^[a-zA-Z0-9_]{1,64}$/', $name); }
    protected function assertNewOptionNamespace(string $name): void
    {
        if ($this->optionsForField(['name' => $name])) throw new InvalidArgumentException('同名历史栏目选项尚未归属字段，请先核对历史数据');
    }
    protected function assertDeletable(Channelfield $field): void
    {
        if ((int)$field->channel_id !== self::CHANNEL_ID || $field->ifsystem) throw new InvalidArgumentException('系统栏目字段不能删除');
    }
    protected function tableForField(Channelfield $field): string
    {
        if ((int)$field->channel_id !== self::CHANNEL_ID) throw new InvalidArgumentException('栏目字段类型无效');
        return (new CmsRepository())->fullName('arctype');
    }
    protected function optionsForField(array $metadata): array
    {
        // Official imports may have field_id=0: channel/name is their original identity.
        $rows = FieldCustomParam::where('channel_id', self::CHANNEL_ID)->where('name', $metadata['name'])->orderBy('sort_order')->orderBy('id')->get()->toArray();
        foreach ($rows as $row) if (!empty($row['field_id']) && (int)$row['field_id'] !== (int)($metadata['id'] ?? 0)) throw new InvalidArgumentException('字段选项归属冲突');
        return $rows;
    }

    public static function optionPlan(array $existing, array $submitted): array
    {
        $known = array_column($existing, null, 'id'); $seen = []; $rows = [];
        foreach ($submitted as $option) {
            if (!is_array($option) || !is_scalar($option['dfvalue'] ?? null)) throw new InvalidArgumentException('字段选项格式无效');
            $rawId = $option['id'] ?? 0;
            if (!is_scalar($rawId) || !preg_match('/^\d+$/D', (string)$rawId)) throw new InvalidArgumentException('选项编号无效');
            $id = (int)$rawId;
            if ($id && (!isset($known[$id]) || isset($seen[$id]))) throw new InvalidArgumentException('选项不属于当前字段或编号重复');
            if ($id) $seen[$id] = true;
            $rows[] = ['id' => $id, 'dfvalue' => trim((string)$option['dfvalue']), 'sort_order' => (int)($option['sort_order'] ?? 0)];
        }
        return ['rows' => $rows, 'delete' => array_values(array_diff(array_keys($known), array_keys($seen)))];
    }

    protected function writeOptions(array $post, int $id, int $channelId, ?array $old): void
    {
        if (!$old) $this->assertNewOptionNamespace($post['name']);
        $existing = $this->optionsForField($old ?? ['name' => $post['name']]);
        $options = in_array($post['dtype'], ['checkboxs', 'radios', 'selects'], true) ? $post['mcheck'] : [];
        $plan = self::optionPlan($existing, $options);
        foreach ($plan['rows'] as $option) {
            $optionId = $option['id']; unset($option['id']);
            $values = $option + ['field_id' => $id, 'name' => $post['name'], 'dtype' => $post['dtype'], 'channel_id' => self::CHANNEL_ID, 'update_time' => time()];
            if ($optionId) FieldCustomParam::where('id', $optionId)->update($values);
            else FieldCustomParam::insert($values + ['add_time' => time()]);
        }
        if ($plan['delete']) FieldCustomParam::whereIn('id', $plan['delete'])->delete();
    }

    protected function deleteOptions(array $ids): void
    {
        foreach (Channelfield::whereIn('id', $ids)->get() as $field) FieldCustomParam::where('channel_id', self::CHANNEL_ID)->where('name', $field->name)->delete();
    }

    private function schemaLock(callable $operation): mixed
    {
        $db = CmsUtil::db(); $key = 'cms_category_fields_' . substr(hash('sha256', (string)$db->getDatabaseName() . ':' . (new CmsRepository())->fullName('arctype')), 0, 32);
        $row = (array)$db->selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$key]);
        if ((int)($row['acquired'] ?? 0) !== 1) throw new InvalidArgumentException('栏目字段正在修改，请稍后重试');
        try { return $operation(); }
        finally {
            try { $db->selectOne('SELECT RELEASE_LOCK(?) AS released', [$key]); }
            catch (\Throwable $error) {
                try { \support\Log::warning('Category field lock release failed', ['error_type' => get_class($error)]); }
                catch (\Throwable) { /* A logger cannot replace the operation result or original error. */ }
            }
        }
    }

    /** Definitions and persisted raw values for the category editor. */
    public function editorFields(int $typeId = 0): array
    {
        $r = new CmsRepository(); $category = $typeId ? $r->table('arctype')->where('id', $typeId)->find() : [];
        $provenance = new HtmltextProvenanceService($r);
        if ($typeId && !$category) throw new InvalidArgumentException('栏目不存在');
        $ids = $r->table('channelfield_bind')->whereIn('typeid', [0, $typeId])->column('field_id');
        if (!$ids) return [];
        $fields = $r->table('channelfield')->where('channel_id', self::CHANNEL_ID)->where('ifsystem', 0)->where('ifeditable', 1)->whereIn('id', $ids)->order('sort_order asc,id asc')->select()->toArray();
        foreach ($fields as &$field) {
            $field['value'] = $category[$field['name']] ?? (in_array($field['dtype'], ['checkbox', 'checkboxs', 'radios', 'selects'], true) ? '' : $field['dfvalue']);
            if ($field['dtype'] === 'htmltext') {
                $bytes = (string)$field['value'];
                $field['__htmltext'] = $typeId
                    ? $provenance->describe('arctype', 'id', $typeId, (string)$field['name'], $bytes)
                    : ['source' => 'new', 'editor_value' => $bytes];
            }
            $field['options'] = in_array($field['dtype'], ['checkboxs', 'radios', 'selects'], true)
                ? array_map(static fn($row) => ['value' => (string)$row['id'], 'text' => (string)$row['dfvalue']], $this->optionsForField($field))
                : array_map(static fn($value) => ['value' => $value, 'text' => $value], array_values(array_filter(explode(',', (string)$field['dfvalue']), static fn($value) => $value !== '')));
            if ($field['dtype'] === 'imgs' && is_string($field['value'])) {
                $decoded = @unserialize($field['value'], ['allowed_classes' => false]);
                $field['value'] = is_array($decoded) ? $decoded : array_map(static fn($path) => ['image_url' => $path, 'intro' => ''], array_filter(explode(',', $field['value'])));
            }
        }
        unset($field); return $fields;
    }

    /** Apply only submitted, bound custom fields; omitted fields retain their stored values. */
    public function categoryInput(array $input, int $typeId = 0): array
    {
        $r = new CmsRepository(); $definitions = $r->table('channelfield')->where('channel_id', self::CHANNEL_ID)->where('ifsystem', 0)->select()->toArray();
        $nested = $input['addonField'] ?? [];
        if (!is_array($nested)) throw new InvalidArgumentException('栏目字段数据格式无效');
        $values = []; $known = array_column($definitions, null, 'name');
        foreach ($definitions as $field) if (array_key_exists($field['name'], $input)) { $values[$field['name']] = $input[$field['name']]; unset($input[$field['name']]); }
        foreach ($nested as $name => $value) {
            $base = isset($known[$name]) ? $name : preg_replace('/(_eyou_is_remote|_eyou_remote|_eyou_local|_eyou_intro)$/', '', (string)$name);
            if (!isset($known[$base])) throw new InvalidArgumentException('栏目字段不存在');
            $values[$name] = $value;
        }
        unset($input['addonField']);
        $boundIds = $r->table('channelfield_bind')->whereIn('typeid', [0, $typeId])->column('field_id');
        foreach ($values as $name => $value) {
            $base = isset($known[$name]) ? $name : preg_replace('/(_eyou_is_remote|_eyou_remote|_eyou_local|_eyou_intro)$/', '', (string)$name); $field = $known[$base];
            if (empty($field['ifeditable']) || !in_array($field['id'], $boundIds)) throw new InvalidArgumentException('栏目字段不可编辑或未绑定到当前栏目');
        }
        $normalized = self::normalizeValues($known, $values);
        foreach ($normalized as $name => $value) {
            if (!in_array($known[$name]['dtype'], ['checkboxs', 'radios', 'selects'], true) || $value === '' || (string)$value === '0') continue;
            $options = array_map('strval', array_column($this->optionsForField($known[$name]), 'id'));
            $selected = $known[$name]['dtype'] === 'checkboxs' ? explode(',', (string)$value) : [(string)$value];
            if (array_diff($selected, $options)) throw new InvalidArgumentException('选项不属于当前栏目字段');
        }
        return array_replace($input, $normalized);
    }

    public static function normalizeValues(array $definitions, array $values, ?int $now = null): array
    {
        $out = []; $now ??= time();
        foreach ($definitions as $name => $field) {
            $helpers = array_intersect_key($values, array_flip([$name . '_eyou_is_remote', $name . '_eyou_remote', $name . '_eyou_local']));
            if (!array_key_exists($name, $values) && !$helpers) continue;
            $value = $values[$name] ?? '';
            if (!is_scalar($value) && !is_array($value)) throw new InvalidArgumentException('栏目字段值格式无效');
            if (in_array($field['dtype'], ['switch', 'int', 'datetime', 'decimal'], true) && !is_scalar($value)) throw new InvalidArgumentException('栏目字段值格式无效');
            foreach ($helpers as $helper) if ($helper !== null && !is_scalar($helper)) throw new InvalidArgumentException('栏目图片字段格式无效');
            switch ($field['dtype']) {
                case 'checkbox': case 'checkboxs':
                    if (is_array($value)) { foreach ($value as $v) if (!is_scalar($v)) throw new InvalidArgumentException('栏目字段选项格式无效'); $value = implode(',', $value); }
                    break;
                // Rich-text bytes are provenance-bound: trimming can corrupt a title-only edit.
                case 'htmltext':
                    if (!is_scalar($value)) throw new InvalidArgumentException('栏目富文本字段格式无效');
                    $value = (string)$value;
                    break;
                case 'switch': case 'int': $value = (int)$value; break;
                case 'datetime': $value = empty($value) ? $now : (is_numeric($value) ? (int)$value : (strtotime((string)$value) ?: 0)); break;
                case 'img':
                    if ($helpers) $value = !empty($values[$name . '_eyou_is_remote']) ? ($values[$name . '_eyou_remote'] ?? '') : ($values[$name . '_eyou_local'] ?? '');
                    break;
                case 'imgs':
                    if ($value === '') $value = [];
                    if (is_array($value)) {
                        $images = []; $intros = $values[$name . '_eyou_intro'] ?? [];
                        if (!is_array($intros)) throw new InvalidArgumentException('图片说明格式无效');
                        foreach ($value as $i => $image) {
                            if ((!is_scalar($image) && !is_array($image)) || (is_array($image) && (!is_scalar($image['image_url'] ?? '') || !is_scalar($image['intro'] ?? ''))) || !is_scalar($intros[$i] ?? '')) throw new InvalidArgumentException('图片数据格式无效');
                            $path = trim((string)(is_array($image) ? ($image['image_url'] ?? '') : $image));
                            if ($path !== '') $images[] = ['image_url' => $path, 'intro' => (string)(is_array($image) ? ($image['intro'] ?? '') : (!empty($intros[$i]) ? $intros[$i] : ''))];
                        }
                        $value = serialize($images);
                    }
                    break;
                case 'files':
                    if (is_array($value)) { foreach ($value as $v) if (!is_scalar($v)) throw new InvalidArgumentException('文件数据格式无效'); $value = implode(',', array_map('trim', array_filter($value))); }
                    break;
                case 'decimal':
                    $parts = explode('.', (string)$value); $value = (int)($parts[0] ?? 0) . '.' . (!empty($parts[1]) ? (int)substr($parts[1], 0, 2) : '00'); break;
                default:
                    if (is_array($value)) { foreach ($value as $v) if (!is_scalar($v)) throw new InvalidArgumentException('栏目字段值格式无效'); $value = implode(',', array_values(array_filter(array_map('trim', $value)))); }
                    else $value = trim((string)$value);
            }
            if (!is_scalar($value) && $value !== null) throw new InvalidArgumentException('栏目字段值格式无效');
            $out[$name] = $value;
        }
        return $out;
    }
}
