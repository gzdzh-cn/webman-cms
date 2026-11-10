<?php
declare(strict_types=1);
namespace app\cms\template;

/** Conservative single-table, literal-only SELECT subset for the legacy SQL tag. */
final class StaticSelectPolicy
{
    public static function validate(string $sql, string $prefix): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]*$/D', $prefix)) throw new \InvalidArgumentException('数据库表前缀无效');
        // Backslashes, comments and statement delimiters have mode-dependent meanings.
        if (strlen($sql) > 4096 || preg_match('~[;\\\\#]|--|/\*|\*/|[\x00-\x1f]~', $sql)) throw new \InvalidArgumentException('SQL标签只允许单条静态查询');
        $id = '`?[a-zA-Z_][a-zA-Z0-9_]*`?';
        $column = '(?:'.$id.'\.)?'.$id;
        $literal = "(?:-?[0-9]+(?:\\.[0-9]+)?|'(?:[^'\\\\]|'')*'|NULL)";
        $field = '(?:\*|'.$column.')(?:\s+AS\s+'.$id.')?';
        $predicate = $column.'\s*(?:=|<>|!=|<=|>=|<|>|LIKE)\s*'.$literal;
        $pattern = '~^\s*SELECT\s+'.$field.'(?:\s*,\s*'.$field.')*\s+FROM\s+('.$id.')'
            .'(?:\s+WHERE\s+'.$predicate.'(?:\s+AND\s+'.$predicate.')*)?'
            .'(?:\s+ORDER\s+BY\s+'.$column.'(?:\s+(?:ASC|DESC))?(?:\s*,\s*'.$column.'(?:\s+(?:ASC|DESC))?)*)?'
            .'(?:\s+LIMIT\s+[0-9]+(?:\s*,\s*[0-9]+)?)?\s*$~iD';
        if (!preg_match($pattern, $sql, $matches)) throw new \InvalidArgumentException('SQL标签仅支持简单静态SELECT');
        $table = strtolower(trim($matches[1], '`'));
        $allowed = ['archives', 'arctype', 'article_content', 'product_content', 'images_content', 'download_content', 'ad', 'ad_position', 'links', 'archives_flag', 'tagindex'];
        if (!str_starts_with($table, strtolower($prefix)) || !in_array(substr($table, strlen($prefix)), $allowed, true)) {
            throw new \InvalidArgumentException('SQL标签不能查询该数据表');
        }
    }
}
