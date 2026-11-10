<?php
declare(strict_types=1);
namespace app\cms\template;

use app\cms\compat\Db;
use think\db\Raw;

/** Preserve parameter bindings when legacy tag conditions are combined. */
final class SqlConditions
{
    public static function combine(array $conditions): Raw
    {
        $parts = [];
        $bind = [];
        foreach ($conditions as $condition) {
            if ($condition instanceof Raw) {
                $parts[] = $condition->getValue();
                foreach ($condition->getBind() as $key => $value) {
                    if (!is_int($key)) throw new \InvalidArgumentException('条件必须使用位置参数');
                    $bind[] = $value;
                }
            } else {
                $parts[] = (string)$condition;
            }
        }
        return Db::raw(implode(' AND ', $parts), $bind);
    }

    public static function order(string $value): string
    {
        $value = trim($value);
        if (strtolower($value) === 'rand()' || preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)?(?:\s+(?:asc|desc))?(?:\s*,\s*[a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)?(?:\s+(?:asc|desc))?)*$/iD', $value)) return $value;
        throw new \InvalidArgumentException('排序表达式格式无效');
    }

    public static function ids(string $value): array
    {
        $value = str_replace('，', ',', trim($value));
        if (!preg_match('/^\d+(?:\s*,\s*\d+)*$/D', $value)) throw new \InvalidArgumentException('栏目或文档ID列表格式无效');
        return array_map('intval', explode(',', $value));
    }

    public static function idCondition(string $column, string $value, bool $exclude = false): Raw
    {
        $ids = self::ids($value);
        return Db::raw($column.($exclude ? ' NOT IN (' : ' IN (').implode(',', array_fill(0, count($ids), '?')).')', $ids);
    }
}
