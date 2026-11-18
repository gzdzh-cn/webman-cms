<?php
declare(strict_types=1);
namespace app\cms\service;

use plugin\admin\app\common\CmsUtil;
use plugin\admin\app\logic\FieldLogic;
use plugin\admin\app\model\{Channelfield, ChannelfieldBind, FieldCustomParam, Channeltype};
use InvalidArgumentException;

/** Schema changes never fall back to deleting a populated column. */
class FieldService
{
    public function save(array $input, int $channelId, ?int $id = null): int
    {
        $old = $id ? Channelfield::find($id) : null;
        if ($id && !$old) { throw new InvalidArgumentException('字段不存在'); }
        if ($old) { $channelId = (int)$old->channel_id; }
        if (!$channelId) { throw new InvalidArgumentException('请选择所属模型'); }
        if ($old && count(array_diff(array_keys($input), ['id', 'ifeditable', 'sort_order', 'ifrequire'])) === 0) {
            $old->fill(array_intersect_key($input, array_flip(['ifeditable', 'sort_order', 'ifrequire'])));
            $old->update_time = time(); $old->save(); \app\cms\compat\Cache::clear(); return (int)$old->id;
        }
        if ($old) {
            $input += $old->toArray();
            $input['typeids'] ??= ChannelfieldBind::where('field_id', $id)->pluck('typeid')->toArray();
        }
        if ($old && $old->ifsystem) {
            if ($input['name'] !== $old->name || $input['dtype'] !== $old->dtype) { throw new InvalidArgumentException('系统字段不能修改名称或类型'); }
            $old->fill(array_intersect_key($input, array_flip(['title','remark','sort_order','ifrequire','ifeditable','is_release'])));
            $old->update_time = time(); $old->save(); \app\cms\compat\Cache::clear(); return (int)$old->id;
        }
        $oldValues = $old?->toArray();
        $logic = $this->fieldLogic();
        $result = $logic->processFieldData($input, $channelId, $old?->name);
        if (!$result['success']) { throw new InvalidArgumentException($result['error']); }
        $data = $result['data']; $post = $data['post']; $table = $data['table'];
        $definition = $data['ntabsql']; $changed = !$old || $old->name !== $post['name'] || $old->dtype !== $post['dtype'] || $old->dfvalue !== $data['dfvalue'];
        $snapshot = null;
        if ($changed) {
            if ($old) {
                $snapshot = $this->backup($table, $old->name, $old->toArray());
                $this->strictDatabase(fn() => $logic->changeFieldInTable($table, $old->name, $definition));
            } else { $this->strictDatabase(fn() => $logic->addFieldToTable($table, $definition)); }
        }
        try {
            $savedId = $this->strictDatabase(fn() => CmsUtil::db()->transaction(function () use ($old, $oldValues, $post, $data, $channelId, $id) {
                $field = $old ?: new Channelfield();
                $values = array_intersect_key($post, array_flip(['title','name','dtype','remark','dfvalue_unit','typeid','set_type','is_release','is_screening','sort_order','ifrequire','ifeditable']));
                $values += ['sort_order' => 100, 'ifrequire' => 0, 'ifeditable' => 1];
                $values += ['dfvalue' => $data['dfvalue'], 'maxlength' => $data['maxlength'], 'define' => $data['buideType'], 'channel_id' => $channelId, 'update_time' => time()];
                if (!$old) { $values += ['ifcontrol' => 0, 'ifsystem' => 0, 'ifmain' => $this->newIfMain($channelId), 'status' => 1, 'add_time' => time()]; }
                $field->fill($values); $field->save(); $id = (int)$field->id;
                ChannelfieldBind::where('field_id', $id)->delete();
                foreach ($data['typeids'] as $typeId) { ChannelfieldBind::insert(['field_id' => $id, 'typeid' => $typeId, 'add_time' => time(), 'update_time' => time()]); }
                $this->writeOptions($post, $id, $channelId, $oldValues);
                return $id;
            }));
        } catch (\Throwable $error) {
            if ($changed) {
                if ($snapshot) {
                    $this->restore($table, $post['name'], $snapshot);
                    // CHANGE may shorten index prefixes when the new varchar
                    // is narrower. Restoring the column does not widen them.
                    $this->restoreIndexes($table, $snapshot['indexes'] ?? []);
                }
                else { CmsUtil::db()->statement('ALTER TABLE `' . $table . '` DROP COLUMN `' . $post['name'] . '`'); }
            }
            if ($changed) { $this->refreshSchema($table); }
            throw $error;
        }
        $this->refreshSchema($table);
        return $savedId;
    }

    public function delete(array $ids): void
    {
        $fields = Channelfield::whereIn('id', $ids)->get();
        foreach ($fields as $field) { $this->assertDeletable($field); }
        $snapshots = [];
        foreach ($fields as $field) {
            $table = $this->tableForField($field);
            $snapshots[] = ['table' => $table, 'name' => $field->name, 'snapshot' => $this->backup($table, $field->name, $field->toArray())];
        }
        $dropped = [];
        try {
            foreach ($snapshots as $change) {
                CmsUtil::db()->statement('ALTER TABLE `' . $change['table'] . '` DROP COLUMN `' . $change['name'] . '`');
                $dropped[] = $change;
            }
            CmsUtil::db()->transaction(function () use ($ids) {
                ChannelfieldBind::whereIn('field_id', $ids)->delete();
                $this->deleteOptions($ids);
                Channelfield::whereIn('id', $ids)->delete();
            });
        } catch (\Throwable $error) {
            // All removed columns must exist before composite indexes can be rebuilt.
            foreach (array_reverse($dropped) as $change) { $this->restore($change['table'], null, $change['snapshot']); }
            foreach ($dropped as $change) { $this->restoreIndexes($change['table'], $change['snapshot']['indexes'] ?? []); }
            foreach (array_unique(array_column($dropped, 'table')) as $table) { $this->refreshSchema($table); }
            throw $error;
        }
        foreach (array_unique(array_column($snapshots, 'table')) as $table) { $this->refreshSchema($table); }
    }

    protected function fieldLogic(): FieldLogic { return new FieldLogic(); }

    protected function newIfMain(int $channelId): int { return 0; }

    protected function assertDeletable(Channelfield $field): void
    {
        if ($field->ifsystem || $field->ifmain) { throw new InvalidArgumentException('系统字段不能删除'); }
    }

    protected function tableForField(Channelfield $field): string
    {
        $model = Channeltype::find($field->channel_id);
        if (!$model) { throw new InvalidArgumentException('字段所属模型不存在'); }
        return $this->fieldLogic()->resolveChannelTable($model);
    }

    protected function writeOptions(array $post, int $id, int $channelId, ?array $old): void
    {
        FieldCustomParam::where('field_id', $id)->delete();
        if (in_array($post['dtype'], ['checkboxs','radios','selects'], true)) {
            foreach ($post['mcheck'] as $option) {
                FieldCustomParam::insert(['field_id' => $id, 'name' => $post['name'], 'dfvalue' => trim((string)($option['dfvalue'] ?? '')), 'sort_order' => (int)($option['sort_order'] ?? 0), 'dtype' => $post['dtype'], 'channel_id' => $channelId, 'add_time' => time(), 'update_time' => time()]);
            }
        }
    }

    protected function deleteOptions(array $ids): void { FieldCustomParam::whereIn('field_id', $ids)->delete(); }

    protected function optionsForField(array $metadata): array { return FieldCustomParam::where('field_id', $metadata['id'])->get()->toArray(); }

    protected function validBackupFieldName(string $name): bool { return (bool)preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $name); }

    protected function refreshSchema(string $table): void
    {
        (new \app\cms\repository\CmsRepository())->connection()->getSchemaInfo($table, true);
        \app\cms\compat\Cache::clear();
    }

    private function strictDatabase(callable $action): mixed
    {
        $db = CmsUtil::db(); $mode = (array)$db->selectOne('SELECT @@SESSION.sql_mode AS mode');
        $original = $mode['mode'];
        try {
            $db->statement('SET SESSION sql_mode=?', [implode(',', array_unique(array_filter(array_merge(explode(',', $original), ['STRICT_ALL_TABLES']))))]);
            return $action();
        } finally { $db->statement('SET SESSION sql_mode=?', [$original]); }
    }

    /** Store schema, metadata and complete column values before irreversible MySQL DDL. */
    private function backup(string $table, string $name, array $metadata): array
    {
        if (!$this->validBackupFieldName($name)) { throw new InvalidArgumentException('字段名称无效'); }
        $db = CmsUtil::db(); $columns = $db->select('SHOW FULL COLUMNS FROM `' . $table . '`');
        $column = null; $primary = null; $preceding = [];
        foreach ($columns as $row) {
            $row = (array)$row;
            if ($row['Field'] === $name) { $column = $row; }
            elseif ($column === null) { $preceding[] = $row['Field']; }
            if ($row['Key'] === 'PRI') { $primary = $row['Field']; }
        }
        if (!$column || !$primary) { throw new InvalidArgumentException('字段或主键不存在，无法安全修改'); }
        $allIndexes = array_map(static fn($row) => (array)$row, $db->select('SHOW INDEX FROM `' . $table . '`'));
        $keys = array_column(array_filter($allIndexes, static fn($row) => $row['Column_name'] === $name), 'Key_name');
        $indexes = array_values(array_filter($allIndexes, static fn($row) => in_array($row['Key_name'], $keys, true)));
        foreach ($indexes as $index) { if (empty($index['Column_name'])) { throw new InvalidArgumentException('此字段包含表达式索引，暂不支持自动修改或删除'); } }
        $snapshot = ['preceding' => $preceding, 'indexes' => $indexes, 'table' => $table, 'column' => $column, 'primary' => $primary, 'metadata' => $metadata, 'binds' => ChannelfieldBind::where('field_id', $metadata['id'])->get()->toArray(), 'options' => $this->optionsForField($metadata), 'rows' => $db->table($db->raw('`' . $table . '`'))->select([$primary, $name])->get()->map(fn($row) => (array)$row)->all()];
        $name = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.json';
        (new \app\cms\persistence\StateStore('backups:fields'))->write($name, $snapshot);
        return $snapshot;
    }

    /** Restore the full index, including columns not being deleted and prefix lengths. */
    private function restoreIndexes(string $table, array $rows): void
    {
        if (!$rows) { return; }
        $db = CmsUtil::db(); $groups = [];
        foreach ($rows as $row) { $groups[$row['Key_name']][] = $row; }
        $existing = array_column(array_map(static fn($row) => (array)$row, $db->select('SHOW INDEX FROM `' . $table . '`')), 'Key_name');
        $quote = static fn(string $value): string => '`' . str_replace('`', '``', $value) . '`';
        foreach ($groups as $name => $parts) {
            usort($parts, static fn($left, $right) => (int)$left['Seq_in_index'] <=> (int)$right['Seq_in_index']);
            $first = $parts[0]; $columns = [];
            foreach ($parts as $part) {
                if (empty($part['Column_name'])) { throw new \RuntimeException('无法自动恢复表达式索引，请使用字段备份'); }
                $columns[] = $quote($part['Column_name']) . ($part['Sub_part'] !== null ? '(' . (int)$part['Sub_part'] . ')' : '') . (($part['Collation'] ?? '') === 'D' ? ' DESC' : '');
            }
            $kind = strtoupper((string)$first['Index_type']);
            $definition = $name === 'PRIMARY' ? 'PRIMARY KEY' : (in_array($kind, ['FULLTEXT', 'SPATIAL'], true) ? $kind . ' INDEX ' : (!$first['Non_unique'] ? 'UNIQUE INDEX ' : 'INDEX ')) . $quote($name);
            $definition .= ' (' . implode(',', $columns) . ')';
            if (!empty($first['Index_comment'])) { $definition .= ' COMMENT ' . $db->getPdo()->quote($first['Index_comment']); }
            if (($first['Visible'] ?? '') === 'NO') { $definition .= ' INVISIBLE'; }
            if (($first['Ignored'] ?? '') === 'YES') { $definition .= ' IGNORED'; }
            $drop = in_array($name, $existing, true) ? ($name === 'PRIMARY' ? 'DROP PRIMARY KEY, ' : 'DROP INDEX ' . $quote($name) . ', ') : '';
            $db->statement('ALTER TABLE `' . $table . '` ' . $drop . 'ADD ' . $definition);
        }
    }

    private function restore(string $table, ?string $currentName, array $snapshot): void
    {
        $db = CmsUtil::db(); $column = $snapshot['column']; $name = $column['Field'];
        $definition = '`' . $name . '` ' . $column['Type'];
        if ($column['Collation']) { $definition .= ' COLLATE ' . $column['Collation']; }
        $definition .= $column['Null'] === 'YES' ? ' NULL' : ' NOT NULL';
        if ($column['Default'] !== null) { $definition .= ' DEFAULT ' . $db->getPdo()->quote($column['Default']); }
        $definition .= ' COMMENT ' . $db->getPdo()->quote($column['Comment']);
        if (array_key_exists('preceding', $snapshot)) {
            $present = array_column(array_map(static fn($row) => (array)$row, $db->select('SHOW COLUMNS FROM `' . $table . '`')), 'Field');
            $previous = null;
            foreach ($snapshot['preceding'] as $candidate) { if (in_array($candidate, $present, true)) { $previous = $candidate; } }
            $definition .= $previous === null ? ' FIRST' : ' AFTER `' . str_replace('`', '``', $previous) . '`';
        }
        $db->statement('ALTER TABLE `' . $table . '` ' . ($currentName ? 'CHANGE COLUMN `' . $currentName . '` ' : 'ADD ') . $definition);
        $db->transaction(function () use ($db, $table, $snapshot, $name) {
            foreach ($snapshot['rows'] as $row) { $db->table($db->raw('`' . $table . '`'))->where($snapshot['primary'], $row[$snapshot['primary']])->update([$name => $row[$name]]); }
        });
    }
}
