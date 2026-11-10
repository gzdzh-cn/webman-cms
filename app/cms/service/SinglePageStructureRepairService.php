<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Explicit maintenance only: insert absent single-page structure, never update translations. */
class SinglePageStructureRepairService
{
    private const TABLES = [
        'language', 'arctype', 'archives', 'single_content', 'channeltype',
        'language_attr', 'language_attribute', 'language_archives_copy_log',
        'channelfield', 'channelfield_bind', 'field_custom_param', 'setting',
        'tagindex', 'taglist',
    ];

    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    /** Read-only, including cache state. Core/reference registries are locked for a coherent maintenance plan. */
    public function inspect(array $categoryIds, ?string $currentLanguage = null): array
    {
        $ids = $this->ids($categoryIds);
        return $this->repository->connection()->transaction(function () use ($ids, $currentLanguage) {
            return $this->plan($ids, $currentLanguage)['public'];
        });
    }

    /** The fingerprint is an optimistic precondition, rechecked under the same ordered locks. */
    public function repair(array $categoryIds, string $expectedFingerprint, ?string $currentLanguage = null): array
    {
        $ids = $this->ids($categoryIds);
        if (!preg_match('/^[a-f0-9]{64}$/D', $expectedFingerprint)) throw new InvalidArgumentException('请提供检查结果中的有效指纹');
        $connection = $this->repository->connection();
        // getPdo() returns false on a fresh CLI connection; explicitly open its master first.
        $connection->query('SELECT 1 AS repair_connection_ready', [], true);
        if ($connection->getPdo()->inTransaction()) throw new InvalidArgumentException('显式修复必须独立提交，请勿在已有事务内调用');
        $result = $connection->transaction(function () use ($ids, $expectedFingerprint, $currentLanguage) {
            $state = $this->plan($ids, $currentLanguage); $result = $state['public'];
            if (!hash_equals($expectedFingerprint, $result['fingerprint'])) throw new InvalidArgumentException('检查后数据、配置或表结构已改变，请重新检查');
            if (!$result['repairable']) throw new InvalidArgumentException('存在结构或历史关系冲突，整批未修复；请先查看检查结果');
            $applied = []; $now = time();
            foreach ($result['plan'] as $item) {
                if ($item['action'] === 'none') continue;
                $aid = $item['aid'];
                if ($item['action'] === 'create_archive_and_body') {
                    $category = $state['categories'][$item['typeid']]; $editor = $state['editor'];
                    $aid = (int)$this->repository->table('archives')->insertGetId([
                        'title' => $category['typename'], 'typeid' => $item['typeid'], 'channel' => 6,
                        'lang' => $category['lang'], 'seo_description' => '', 'reason' => '', 'sort_order' => 100,
                        'editor_remote_img_local' => $editor['editor_remote_img_local'] ?? 1,
                        'editor_img_clear_link' => $editor['editor_img_clear_link'] ?? 1, 'add_time' => $now,
                    ]);
                    $this->assertNewAidUnreferenced($aid, $state['rows']);
                }
                // Deliberately sparse: mobile and every custom field retain SQL defaults/NULL.
                // A STRICT-mode NOT NULL/no-default error must roll back the complete batch.
                $this->repository->table('single_content')->insert([
                    'aid' => $aid, 'typeid' => $item['typeid'], 'content' => '', 'add_time' => $now, 'update_time' => $now,
                ]);
                $applied[] = ['typeid' => $item['typeid'], 'action' => $item['action'], 'aid' => $aid];
            }
            $result['committed'] = true; $result['applied'] = $applied;
            return $result;
        });
        if ($result['applied']) {
            try { \app\cms\compat\Cache::clear(); }
            catch (\Throwable $error) { $result['cache_warning'] = '数据已提交，缓存清理失败，请手动清理缓存'; }
        }
        return $result;
    }

    private function ids(array $values): array
    {
        if (!$values || count($values) > 100) throw new InvalidArgumentException('请显式选择1至100个单页栏目ID');
        $ids = [];
        foreach ($values as $value) {
            if ((!is_int($value) && (!is_string($value) || !ctype_digit($value))) || filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) throw new InvalidArgumentException('栏目ID必须是正整数');
            $ids[] = (int)$value;
        }
        $ids = array_values(array_unique($ids)); sort($ids, SORT_NUMERIC);
        return $ids;
    }

    private function plan(array $ids, ?string $currentLanguage): array
    {
        $r = $this->repository; $db = $r->connection(); $schema = []; $engines = []; $rows = []; $issues = [];
        $identity = $db->query('SELECT DATABASE() AS database_name, @@SESSION.sql_mode AS sql_mode');
        if (empty($identity[0]['database_name'])) throw new InvalidArgumentException('无法确认数据库身份');
        foreach (self::TABLES as $table) {
            $name = $r->fullName($table);
            $engine = $db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$name]);
            $engines[$table] = strtolower((string)($engine[0]['ENGINE'] ?? ''));
            if ($engines[$table] !== 'innodb') $issues[] = ['typeid' => 0, 'code' => 'non_transactional_table', 'table' => $table];
            $columns = $r->columns($table);
            $indexes = $db->query('SHOW INDEX FROM `' . $name . '`');
            foreach ($indexes as &$index) unset($index['Cardinality']);
            unset($index);
            usort($indexes, static fn(array $left, array $right): int => [$left['Key_name'], (int)$left['Seq_in_index']] <=> [$right['Key_name'], (int)$right['Seq_in_index']]);
            $schema[$table] = ['name' => $name, 'columns' => $columns, 'indexes' => $indexes];
        }
        // Whole core/reference registries are deliberately locked in maintenance mode.
        // This also sees orphan rows and aliases that have no database foreign keys.
        foreach (self::TABLES as $table) {
            $primary = array_keys(array_filter($schema[$table]['columns'], static fn(array $column): bool => $column['Key'] === 'PRI'));
            if (!$primary) throw new InvalidArgumentException('维护检查要求相关表存在主键');
            $rows[$table] = $r->table($table)->order(implode(',', array_map(static fn(string $name): string => $name . ' asc', $primary)))->lock(true)->select()->toArray();
        }
        $languages = array_column($rows['language'], null, 'mark');
        $currentLanguage ??= $rows['language'][0]['mark'] ?? null;
        if (!$currentLanguage || !isset($languages[$currentLanguage])) $issues[] = ['typeid' => 0, 'code' => 'editor_language_missing'];
        $editor = [];
        foreach ($rows['setting'] as $setting) if ($setting['lang'] === $currentLanguage && $setting['inc_type'] === 'editor') $editor[$setting['name']] = $setting['value'];
        $categories = array_column($rows['arctype'], null, 'id'); $archives = array_column($rows['archives'], null, 'aid');
        $models = array_column($rows['channeltype'], null, 'id');
        if (!isset($models[6]) || !empty($models[6]['is_del']) || ($models[6]['table'] ?? '') !== 'single') $issues[] = ['typeid' => 0, 'code' => 'single_model_conflict'];
        $plan = [];
        foreach ($ids as $id) {
            $category = $categories[$id] ?? null;
            if (!$category || !empty($category['is_del']) || (int)$category['current_channel'] !== 6 || !isset($languages[$category['lang']])) {
                $issues[] = ['typeid' => $id, 'code' => 'category_owner_language_model_conflict'];
                continue;
            }
            $owned = array_values(array_filter($rows['archives'], static fn(array $row): bool => (int)$row['typeid'] === $id));
            $bodies = array_values(array_filter($rows['single_content'], static fn(array $row): bool => (int)$row['typeid'] === $id));
            if (count($owned) > 1) { $issues[] = ['typeid' => $id, 'code' => 'multiple_archives']; continue; }
            $archive = $owned[0] ?? null; $aid = $archive ? (int)$archive['aid'] : null;
            if ($archive && (!empty($archive['is_del']) || (int)$archive['channel'] !== 6 || $archive['lang'] !== $category['lang'] || !empty($archive['users_id']))) {
                $issues[] = ['typeid' => $id, 'code' => 'archive_recycled_owner_language_model_conflict']; continue;
            }
            $matching = $aid === null ? [] : array_values(array_filter($rows['single_content'], static fn(array $row): bool => (int)$row['aid'] === $aid));
            if (count($matching) > 1) { $issues[] = ['typeid' => $id, 'code' => 'multiple_bodies']; continue; }
            if (($matching && (int)$matching[0]['typeid'] !== $id) || count($bodies) !== count($matching)) {
                $issues[] = ['typeid' => $id, 'code' => 'orphan_or_conflicting_body']; continue;
            }
            $this->relations($id, $aid, $rows, $categories, $archives, $issues);
            $plan[] = ['typeid' => $id, 'action' => !$archive ? 'create_archive_and_body' : (!$matching ? 'create_body' : 'none'), 'aid' => $aid];
        }
        // Content remains internal. The public manifest exposes only IDs, actions and a digest.
        $fingerprint = hash('sha256', serialize([
            'version' => 1, 'service_sha256' => hash_file('sha256', __FILE__), 'category_ids' => $ids,
            'current_language' => $currentLanguage, 'identity' => $identity, 'engines' => $engines,
            'schema' => $schema, 'rows' => $rows,
        ]));
        return ['public' => [
            'version' => 1, 'category_ids' => $ids, 'current_language' => $currentLanguage,
            'fingerprint' => $fingerprint, 'repairable' => !$issues, 'plan' => $plan, 'issues' => $issues,
            'counts' => array_count_values(array_column($plan, 'action')),
        ], 'categories' => $categories, 'editor' => $editor, 'rows' => $rows];
    }

    /** Reject accidental resurrection when an imported AUTO_INCREMENT counter reuses a dangling ID. */
    private function assertNewAidUnreferenced(int $aid, array $rows): void
    {
        $collision = false;
        foreach ($rows['single_content'] as $row) if ((int)$row['aid'] === $aid) $collision = true;
        foreach ($rows['language_attr'] as $row) if ($row['attr_group'] === 'archives' && ctype_digit(trim((string)$row['attr_value'])) && (int)$row['attr_value'] === $aid) $collision = true;
        foreach ($rows['language_archives_copy_log'] as $row) if ((int)$row['oldid'] === $aid || (int)$row['newid'] === $aid) $collision = true;
        foreach ($rows['taglist'] as $row) if ((int)$row['aid'] === $aid) $collision = true;
        foreach ($rows['archives'] as $row) if ((int)$row['joinaid'] === $aid) $collision = true;
        if ($collision) throw new InvalidArgumentException('新文档ID仍被历史记录引用，整批已回滚，请先检查历史数据');
    }

    /** Validate relevant legacy relations without deleting, rewriting or inventing them. */
    private function relations(int $typeid, ?int $aid, array $rows, array $categories, array $archives, array &$issues): void
    {
        $fail = static function (string $code) use ($typeid, &$issues): void { $issues[] = ['typeid' => $typeid, 'code' => $code]; };
        $fields = array_column($rows['channelfield'], null, 'id');
        foreach ($rows['channelfield_bind'] as $binding) if ((int)$binding['typeid'] === $typeid) {
            $field = $fields[(int)$binding['field_id']] ?? null;
            if (!$field || (int)$field['channel_id'] !== 6) $fail('invalid_custom_field_binding');
        }
        $tags = array_column($rows['tagindex'], null, 'id');
        foreach ($rows['taglist'] as $tag) {
            if ((int)$tag['typeid'] !== $typeid && ($aid === null || (int)$tag['aid'] !== $aid)) continue;
            $index = $tags[(int)$tag['tid']] ?? null; $category = $categories[$typeid];
            if ($aid === null || (int)$tag['aid'] !== $aid || (int)$tag['typeid'] !== $typeid || $tag['lang'] !== $category['lang'] || !$index || $index['lang'] !== $category['lang']) $fail('invalid_historical_tag_owner');
        }
        $names = []; $peerCategories = [$typeid => true];
        foreach ($rows['language_attr'] as $binding) {
            $group = $binding['attr_group'];
            $raw = trim((string)$binding['attr_value']);
            if (($group === 'arctype' && ctype_digit($raw) && (int)$raw === $typeid) || ($group === 'archives' && $aid !== null && ctype_digit($raw) && (int)$raw === $aid)) $names[$group . ':' . $binding['attr_name']] = [$group, $binding['attr_name']];
        }
        foreach ($rows['language_attr'] as $binding) if ($binding['attr_group'] === 'arctype' && isset($names['arctype:' . $binding['attr_name']])) {
            $raw = trim((string)$binding['attr_value']);
            if (ctype_digit($raw)) $peerCategories[(int)$raw] = true;
        }
        // A missing target archive may still have an old archive alias discovered
        // through its category's canonical peer and that peer's source archive.
        foreach ($rows['language_attr'] as $binding) if ($binding['attr_group'] === 'archives') {
            $raw = trim((string)$binding['attr_value']); $owner = ctype_digit($raw) ? ($archives[(int)$raw] ?? null) : null;
            if ($owner && isset($peerCategories[(int)$owner['typeid']])) $names['archives:' . $binding['attr_name']] = ['archives', $binding['attr_name']];
        }
        foreach ($names as [$group, $name]) {
            $definitions = array_values(array_filter($rows['language_attribute'], static fn(array $row): bool => $row['attr_group'] === $group && $row['attr_name'] === $name));
            if (count($definitions) !== 1 || !empty($definitions[0]['is_del'])) { $fail('invalid_historical_definition'); continue; }
            $bindings = array_values(array_filter($rows['language_attr'], static fn(array $row): bool => $row['attr_group'] === $group && $row['attr_name'] === $name));
            $seen = [];
            foreach ($bindings as $binding) {
                if (isset($seen[$binding['lang']])) $fail('duplicate_historical_binding');
                $seen[$binding['lang']] = true;
                $raw = (string)$binding['attr_value'];
                if (!ctype_digit($raw) || (string)(int)$raw !== $raw) { $fail('noncanonical_historical_binding_value'); continue; }
                $owner = $group === 'arctype' ? ($categories[(int)$raw] ?? null) : ($archives[(int)$raw] ?? null);
                if (!$owner || !empty($owner['is_del']) || $owner['lang'] !== $binding['lang']) { $fail('invalid_historical_binding_owner'); continue; }
                if (!in_array($owner['lang'], array_column($rows['language'], 'mark'), true)) $fail('invalid_historical_binding_language');
                $aliases = array_filter($rows['language_attr'], static fn(array $row): bool => $row['attr_group'] === $group && $row['lang'] === $binding['lang'] && $row['attr_value'] === $raw);
                if (count($aliases) !== 1) $fail('conflicting_historical_canonical_names');
                if ($group === 'arctype' && (int)$owner['current_channel'] !== 6) $fail('invalid_historical_category_model');
                if ($group === 'archives') {
                    $category = $categories[(int)$owner['typeid']] ?? null;
                    if ((int)$owner['channel'] !== 6 || !empty($owner['users_id']) || !$category || !empty($category['is_del']) || (int)$category['current_channel'] !== 6 || $category['lang'] !== $owner['lang']) $fail('invalid_historical_archive_category');
                    if (count($peerCategories) > 1 && !isset($peerCategories[(int)$owner['typeid']])) $fail('historical_archive_category_mapping_conflict');
                }
            }
        }
        $seenLogs = [];
        foreach ($rows['language_archives_copy_log'] as $log) {
            if ((int)$log['typeid'] !== $typeid && (int)$log['new_typeid'] !== $typeid && ($aid === null || ((int)$log['oldid'] !== $aid && (int)$log['newid'] !== $aid))) continue;
            $key = serialize([(int)$log['channel'], (int)$log['oldid'], $log['lang']]);
            if (isset($seenLogs[$key])) $fail('duplicate_historical_copy_log');
            $seenLogs[$key] = true;
            $source = $archives[(int)$log['oldid']] ?? null; $target = $archives[(int)$log['newid']] ?? null;
            $sourceCategory = $categories[(int)$log['typeid']] ?? null; $targetCategory = $categories[(int)$log['new_typeid']] ?? null;
            if (!$source || !$target || !$sourceCategory || !$targetCategory || !empty($source['is_del']) || !empty($target['is_del']) || !empty($sourceCategory['is_del']) || !empty($targetCategory['is_del']) ||
                (int)$log['channel'] !== 6 || (int)$source['channel'] !== 6 || (int)$target['channel'] !== 6 || (int)$sourceCategory['current_channel'] !== 6 || (int)$targetCategory['current_channel'] !== 6 ||
                !empty($source['users_id']) || !empty($target['users_id']) || $source['lang'] === $target['lang'] || !in_array($source['lang'], array_column($rows['language'], 'mark'), true) || !in_array($target['lang'], array_column($rows['language'], 'mark'), true) ||
                (int)$source['typeid'] !== (int)$log['typeid'] || (int)$target['typeid'] !== (int)$log['new_typeid'] || $target['lang'] !== $log['lang'] || $source['lang'] !== $sourceCategory['lang'] || $target['lang'] !== $targetCategory['lang']) $fail('invalid_historical_copy_log');
        }
    }
}
