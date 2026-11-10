<?php
declare(strict_types=1);
namespace app\cms\repository;

use support\think\Db;
use InvalidArgumentException;

/** Shared data access for the public runtime and CMS administration. */
class CmsRepository
{
    public function connection()
    {
        return Db::connect('mysql');
    }

    public function table(string $name)
    {
        $this->identifier($name);
        return $this->connection()->name($name);
    }

    public function fullName(string $name): string
    {
        $this->identifier($name);
        $prefix = (string)config('think-orm.connections.mysql.prefix', \app\cms\database\TableNames::cms());
        $this->identifier($prefix . $name);
        return $prefix . $name;
    }

    public function columns(string $table): array
    {
        // ThinkORM retains schema within a long-running worker. Custom-field DDL
        // may have been committed by another worker since its last query.
        $this->connection()->getSchemaInfo($this->fullName($table), true);
        $rows = $this->connection()->query('SHOW FULL COLUMNS FROM `' . $this->fullName($table) . '`');
        return array_column($rows, null, 'Field');
    }

    public function filter(string $table, array $data, bool $includePrimary = false): array
    {
        $columns = $this->columns($table); $filtered = [];
        foreach ($data as $name => $value) {
            $column = $columns[$name] ?? null;
            if (!$column || (!$includePrimary && $column['Key'] === 'PRI')) { continue; }
            if (is_array($value)) { $value = implode(',', array_filter($value, 'is_scalar')); }
            if ($value === '' && preg_match('/^(?:tinyint|smallint|mediumint|int|bigint|decimal|float|double)/i', $column['Type'])) {
                $value = $column['Default'] ?? ($column['Null'] === 'YES' ? null : 0);
            }
            $filtered[$name] = $value;
        }
        return $filtered;
    }

    public function transaction(callable $operation)
    {
        $result = $this->connection()->transaction(fn() => $operation($this));
        \app\cms\compat\Cache::clear();
        return $result;
    }

    public function identifier(string $name): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) { throw new InvalidArgumentException('Invalid CMS table/field identifier'); }
    }
}
