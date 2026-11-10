<?php
declare(strict_types=1);
namespace app\cms\install;

use InvalidArgumentException;
use PDO;

/**
 * Read-only identification of the local EyouCMS 1.7.7 core schema and seed markers.
 * Recognition protects an existing database from installation; it is not a migration
 * or a promise that every schema revision/template is supported by this application.
 */
final class LegacyInstallationInspector
{
    private const INTEGER_TYPES = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'];
    private const TEXT_TYPES = ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext'];

    /** Required stable core fields; extra columns, sizes and storage engines may differ. */
    private const CORE = [
        'config' => ['primary' => 'id', 'columns' => [
            'id' => 'integer', 'name' => 'text', 'value' => 'text', 'inc_type' => 'text',
            'lang' => 'text', 'is_del' => 'integer',
        ]],
        'arctype' => ['primary' => 'id', 'columns' => [
            'id' => 'integer', 'channeltype' => 'integer', 'current_channel' => 'integer',
            'parent_id' => 'integer', 'typename' => 'text', 'dirname' => 'text',
            'templist' => 'text', 'tempview' => 'text', 'lang' => 'text',
            'status' => 'integer', 'is_del' => 'integer',
        ]],
        'archives' => ['primary' => 'aid', 'columns' => [
            'aid' => 'integer', 'typeid' => 'integer', 'channel' => 'integer',
            'title' => 'text', 'tempview' => 'text', 'lang' => 'text',
            'status' => 'integer', 'is_del' => 'integer',
        ]],
        'channeltype' => ['primary' => 'id', 'columns' => [
            'id' => 'integer', 'nid' => 'text', 'title' => 'text', 'table' => 'text',
            'ctl_name' => 'text', 'ifsystem' => 'integer', 'status' => 'integer', 'is_del' => 'integer',
        ]],
        'language' => ['primary' => 'id', 'columns' => [
            'id' => 'integer', 'title' => 'text', 'mark' => 'text',
            'is_home_default' => 'integer', 'is_admin_default' => 'integer', 'status' => 'integer',
        ]],
    ];

    /**
     * Uses the PDO connection's currently selected database. All SQL is SELECT.
     * missing contains table or table.column identifiers only. A seed discriminator
     * absent from an otherwise valid table is reported using its column identifier.
     * Connection/permission errors propagate so callers can keep installation closed.
     *
     * @return array{recognized:bool,version:?string,missing:list<string>}
     */
    public function inspect(PDO $pdo, string $prefix): array
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,31}$/D', $prefix)) {
            throw new InvalidArgumentException('CMS 数据表前缀无效');
        }
        // MySQL may store identifiers in lowercase even when the configured prefix
        // uses capitals. Respect the server's identifier rules, not metadata collation.
        $caseInsensitive = (int)$pdo->query('SELECT @@lower_case_table_names')->fetchColumn() !== 0;
        $tableKey = static fn(string $name): string => $caseInsensitive ? strtolower($name) : $name;
        $tables = array_map(static fn(string $name): string => $prefix . $name, array_keys(self::CORE));
        $placeholders = implode(',', array_fill(0, count($tables), '?'));
        $query = $pdo->prepare('SELECT TABLE_NAME AS table_name, TABLE_TYPE AS table_type FROM information_schema.TABLES'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (' . $placeholders . ')');
        $query->execute($tables);
        $present = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) $present[$tableKey($row['table_name'])] = $row['table_type'];

        $query = $pdo->prepare('SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name, DATA_TYPE AS data_type, COLUMN_KEY AS column_key'
            . ' FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (' . $placeholders . ')');
        $query->execute($tables);
        $columns = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) $columns[$tableKey($row['table_name'])][$row['column_name']] = $row;

        $missing = [];
        foreach (self::CORE as $suffix => $definition) {
            $table = $prefix . $suffix;
            if (($present[$tableKey($table)] ?? null) !== 'BASE TABLE') {
                $missing[] = $table;
                continue;
            }
            $tableColumns = $columns[$tableKey($table)] ?? [];
            foreach ($definition['columns'] as $name => $kind) {
                $types = $kind === 'integer' ? self::INTEGER_TYPES : self::TEXT_TYPES;
                if (!isset($tableColumns[$name]) || !in_array(strtolower((string)$tableColumns[$name]['data_type']), $types, true)) {
                    $missing[] = $table . '.' . $name;
                }
            }
            $primary = array_keys(array_filter($tableColumns, static fn(array $column): bool => $column['column_key'] === 'PRI'));
            if ($primary !== [$definition['primary']]) $missing[] = $table . '.' . $definition['primary'];
        }
        if ($missing) return $this->result($missing);

        // Empty article/category tables are valid. Only installation seed markers
        // discriminate a real Eyou database from unrelated same-name empty tables.
        $query = $pdo->prepare('SELECT DISTINCT `name` FROM `' . $prefix . 'config` WHERE `name` IN (?, ?)');
        $query->execute(['web_name', 'web_basehost']);
        if (count($query->fetchAll(PDO::FETCH_COLUMN)) !== 2) $missing[] = $prefix . 'config.name';
        $query = $pdo->prepare('SELECT 1 FROM `' . $prefix . 'channeltype` WHERE `nid` = ? AND `table` = ? AND `ctl_name` = ? LIMIT 1');
        $query->execute(['article', 'article', 'Article']);
        if ($query->fetchColumn() === false) $missing[] = $prefix . 'channeltype.nid';
        $query = $pdo->query('SELECT 1 FROM `' . $prefix . 'language` WHERE `mark` <> \'\' LIMIT 1');
        if ($query->fetchColumn() === false) $missing[] = $prefix . 'language.mark';
        if ($missing) return $this->result($missing);

        $query = $pdo->prepare('SELECT LEFT(`value`, 65) FROM `' . $prefix . 'config`'
            . ' WHERE `name` = ? AND `inc_type` = ? ORDER BY (`lang` = ?) DESC, `id` ASC LIMIT 1');
        $query->execute(['system_version', 'system', 'cn']);
        $version = $query->fetchColumn();
        // Do not expose arbitrary configuration contents in a version field.
        $version = is_string($version) && strlen($version) <= 64
            && preg_match('/^v?\d+\.\d+(?:\.\d+)?(?:[-_.][A-Za-z0-9]+)*$/D', $version)
            ? $version : null;
        return ['recognized' => true, 'version' => $version, 'missing' => []];
    }

    private function result(array $missing): array
    {
        return ['recognized' => false, 'version' => null, 'missing' => array_values(array_unique($missing))];
    }
}
