<?php
declare(strict_types=1);
namespace app\cms\admin\logic;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** EyouCMS Field/arctype_* targets the category table, never a content model. */
class CategoryFieldLogic extends \plugin\admin\app\logic\FieldLogic
{
    public const CHANNEL_ID = -99;

    protected function validFieldName(string $name): bool
    {
        return strlen($name) <= 64 && preg_match('/^\w+$/D', $name) && !preg_match('/^(_+|[0-9]+)$/D', $name);
    }
    protected function forbidTypePrefix(): bool { return false; }
    protected function fieldTitle(string $title): string { return htmlspecialchars($title, ENT_COMPAT, 'UTF-8'); }

    public static function assertTypeTransition(string $old, string $new): void
    {
        // Imported media definitions retain their storage type. Use persisted metadata.
        if (($old === 'media' || $new === 'media') && $old !== $new) {
            throw new InvalidArgumentException('历史媒体栏目字段不能直接转换类型');
        }
        $inline = ['radio', 'select', 'checkbox']; $independent = ['radios', 'selects', 'checkboxs'];
        if ((in_array($old, $inline, true) && in_array($new, $independent, true))
            || (in_array($old, $independent, true) && in_array($new, $inline, true))
            || ($old === 'checkbox' && in_array($new, ['radio', 'select'], true))) {
            throw new InvalidArgumentException('该栏目字段类型不能直接转换，请保留原选项存储方式');
        }
    }

    protected function fieldTargetTable(int $channelId): string
    {
        if ($channelId !== self::CHANNEL_ID) throw new InvalidArgumentException('栏目字段类型无效');
        return (new CmsRepository())->fullName('arctype');
    }

    public function getFieldTypeList($channel_id = 0, $field = '*')
    {
        return array_values(array_filter(parent::getFieldTypeList(self::CHANNEL_ID, $field), static fn($row) => !in_array($row['name'] ?? '', ['file', 'media', 'region'], true)));
    }

    /** Existing imported media fields need a usable edit choice; creation stays filtered. */
    public function getFieldTypeListForEdit(string $storedType, $field = '*'): array
    {
        if ($storedType !== 'media') return $this->getFieldTypeList(self::CHANNEL_ID, $field);
        return array_values(array_filter(parent::getFieldTypeList(self::CHANNEL_ID, $field), static fn($row) => ($row['name'] ?? '') === 'media'));
    }

    public function processFieldData($post, $channel_id, $oldFieldName = null)
    {
        $dtype = trim((string)($post['dtype'] ?? ''));
        if ((int)$channel_id !== self::CHANNEL_ID || in_array($dtype, ['file', 'region'], true)
            || ($dtype === 'media' && $oldFieldName === null)) {
            return ['success' => false, 'error' => '栏目字段类型无效'];
        }
        return parent::processFieldData($post, self::CHANNEL_ID, $oldFieldName);
    }

    public function checkChannelFieldList($table, $fieldname, $channel_id, $excludeFields = [])
    {
        // Upstream also reserves the Single addon namespace when defining category fields.
        return parent::checkChannelFieldList((new CmsRepository())->fullName('single_content'), $fieldname, self::CHANNEL_ID, $excludeFields);
    }

    public static function normalizeTypeids($input): array
    {
        if ($input === null) return [];
        if (!is_array($input)) throw new InvalidArgumentException('栏目绑定格式无效');
        $ids = [];
        foreach ($input as $value) {
            if (!is_scalar($value) || !preg_match('/^\d+$/D', (string)$value)) throw new InvalidArgumentException('栏目绑定格式无效');
            $ids[] = (int)$value;
        }
        $ids = array_values(array_unique($ids));
        // A single zero means every category, including future categories.
        return count($ids) > 1 ? array_values(array_filter($ids)) : $ids;
    }

    protected function fieldTypeids($input, int $channelId): array
    {
        $ids = self::normalizeTypeids($input);
        if (!$ids || $ids === [0]) return $ids;
        $r = new CmsRepository();
        $selected = $r->table('arctype')->whereIn('id', $ids)->where('is_del', 0)->select()->toArray();
        if (count($selected) !== count($ids)) throw new InvalidArgumentException('绑定栏目不存在或已回收');
        // Original arctype_add/edit extends explicit selections by canonical tid names.
        // The backend does not load a home CmsContext. Read the raw counter for
        // each selected category language rather than its uninitialized globals.
        $names = [];
        foreach ($selected as $category) {
            $counter = $r->table('config')->where('name', 'system_langnum')->where('lang', $category['lang'])->value('value');
            if ((int)$counter > 1) $names[] = 'tid' . $category['id'];
        }
        if ($names) {
            $bindings = $r->table('language_attr')->where('attr_group', 'arctype')->whereIn('attr_name', $names)->select()->toArray();
            $peers = array_values(array_unique(array_map('intval', array_column($bindings, 'attr_value'))));
            $targets = $peers ? $r->table('arctype')->whereIn('id', $peers)->where('is_del', 0)->select()->toArray() : [];
            $targets = array_column($targets, null, 'id'); $groups = []; $owners = [];
            foreach ($bindings as $binding) {
                $target = $targets[(int)$binding['attr_value']] ?? null;
                if (!$target || $target['lang'] !== $binding['lang']) throw new InvalidArgumentException('关联语言栏目绑定无效');
                $group = $binding['attr_name'] . ':' . $binding['lang'];
                if ((isset($groups[$group]) && $groups[$group] !== (int)$target['id']) || (isset($owners[$target['id']]) && $owners[$target['id']] !== $binding['attr_name'])) throw new InvalidArgumentException('关联语言栏目绑定冲突');
                $groups[$group] = (int)$target['id']; $owners[$target['id']] = $binding['attr_name'];
            }
            $ids = array_values(array_unique(array_merge($ids, $peers)));
            if ($r->table('arctype')->whereIn('id', $ids)->where('is_del', 0)->count() !== count($ids)) throw new InvalidArgumentException('关联语言栏目不存在或已回收');
        }
        return $ids;
    }
}
