<?php
declare(strict_types=1);
namespace app\cms\database;

use plugin\admin\app\common\Navigation;

/** Pure, guarded plan construction. Persistence is deliberately separate. */
final class NavigationMigration
{
    public static function fingerprint(array $rows, array $roles, array $adminRoles, array $options = []): string
    {
        $sources = [hash_file('sha256', __FILE__), hash_file('sha256', dirname(__DIR__) . '/console/legacy/migrate_admin_navigation.php'),
            hash_file('sha256', \app\cms\Paths::cms('admin/support/Navigation.php'))];
        return hash('sha256', json_encode([$rows, $roles, $adminRoles, $options, Navigation::specification(), $sources], JSON_THROW_ON_ERROR));
    }

    public static function plan(array $rows, array $roles = [], array $adminRoles = [], array $options = [], array $fallback = []): array
    {
        $byId = $byKey = [];
        $maxId = 0;
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $key = strtolower((string)$row['key']);
            if (isset($byId[$id]) || isset($byKey[$key])) throw new \RuntimeException('Duplicate rule ID or key; migration refused');
            if (!array_key_exists('status', $row)) throw new \RuntimeException('Apply the existing menu status migration first');
            $byId[$id] = $row;
            $byKey[$key] = $id;
            $maxId = max($maxId, $id);
        }
        foreach ($rows as $row) {
            $seen = [];
            $id = (int)$row['id'];
            while ($id !== 0) {
                if (isset($seen[$id]) || !isset($byId[$id])) throw new \RuntimeException('Cyclic or orphaned rule hierarchy; migration refused');
                $seen[$id] = true;
                $id = (int)$byId[$id]['pid'];
            }
        }
        foreach ($roles as $role) {
            foreach (explode(',', (string)($role['rules'] ?? '')) as $id) {
                if ($id !== '' && $id !== '*' && (!ctype_digit($id) || !isset($byId[(int)$id]))) {
                    throw new \RuntimeException('A role references an unknown rule; migration refused');
                }
            }
        }
        $beforeVisible = Navigation::visibleIds($rows);
        $operations = [];
        foreach (Navigation::specification() as $index => $group) {
            $groupKey = strtolower($group['key']);
            if (isset($byKey[$groupKey])) {
                $groupId = $byKey[$groupKey];
                $old = $byId[$groupId];
                if ((int)$old['type'] !== 0 || (string)($old['href'] ?? '') !== '') {
                    throw new \RuntimeException('Group key is occupied by a non-container rule; migration refused');
                }
                $changes = ['title' => $group['title'], 'icon' => $group['icon'], 'weight' => 1100 - $index * 100, 'pid' => 0];
                self::update($byId, $groupId, $changes, $operations);
            } else {
                $groupId = ++$maxId;
                $row = ['id' => $groupId, 'title' => $group['title'], 'key' => $group['key'], 'icon' => $group['icon'],
                    'type' => 0, 'pid' => 0, 'status' => 1, 'href' => '', 'weight' => 1100 - $index * 100];
                $byId[$groupId] = $row;
                $byKey[$groupKey] = $groupId;
                $operations[] = ['operation' => 'insert', 'id' => $groupId, 'after' => $row];
            }
            $weight = 1000;
            foreach ($group['modules'] as $module) {
                foreach ($module['pages'] as $page) {
                    $key = strtolower($page['key']);
                    if (!isset($byKey[$key])) throw new \RuntimeException('An original enterprise page is missing; run missing-only initialization first');
                    $pageId = $byKey[$key];
                    if ((int)$byId[$pageId]['type'] !== 1) throw new \RuntimeException('Enterprise page key has an unexpected rule type');
                    self::update($byId, $pageId, ['pid' => $groupId, 'weight' => $weight--], $operations);
                }
            }
        }
        $after = array_values($byId);
        usort($after, static fn(array $a, array $b): int => (int)$a['id'] <=> (int)$b['id']);
        $afterVisible = Navigation::visibleIds($after);
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            if (isset($beforeVisible[$id]) !== isset($afterVisible[$id])) {
                throw new \RuntimeException('Regrouping would change an existing rule effective visibility; migration refused');
            }
        }
        $optionOperation = self::optionOperation($options, $fallback);
        return ['version' => Navigation::VERSION, 'fingerprint' => hash('sha256', self::fingerprint($rows, $roles, $adminRoles, $options) . json_encode($optionOperation, JSON_THROW_ON_ERROR)),
            'operations' => $operations, 'rules_before' => count($rows), 'rules_after' => count($after),
            'roles_unchanged' => true, 'rule_ids_keys_preserved' => true, 'effective_visibility_preserved' => true,
            'after' => $after, 'option_operation' => $optionOperation];
    }

    private static function optionOperation(array $options, array $fallback): ?array
    {
        if (count($options) > 1 || ($options && ($options[0]['name'] ?? '') !== 'system_config')) throw new \RuntimeException('Ambiguous system_config option');
        $before = $options[0] ?? null;
        if ($before && (string)$before['value'] !== '') {
            $configuration = json_decode((string)$before['value'], true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($configuration) || !str_starts_with(ltrim((string)$before['value']), '{')) throw new \RuntimeException('system_config must contain a JSON object');
        } else {
            if (!$fallback) throw new \RuntimeException('Missing default configuration for system_config initialization');
            $configuration = $fallback;
        }
        $patch = [
            'menu.control' => false, 'menu.accordion' => true,
            'tab.enable' => true, 'tab.keepState' => true, 'tab.session' => true,
            'tab.index.id' => '0', 'tab.index.href' => '/app/admin/index/dashboard', 'tab.index.title' => '工作台',
            'theme.defaultMenu' => 'light-theme', 'theme.defaultHeader' => 'light-theme', 'theme.defaultColor' => '2', 'theme.banner' => false,
            'other.autoHead' => false, 'other.footer' => false, 'layout.version' => Navigation::VERSION,
        ];
        $changes = [];
        foreach ($patch as $path => $value) {
            $parts = explode('.', $path);
            $target = &$configuration;
            foreach (array_slice($parts, 0, -1) as $part) {
                if (array_key_exists($part, $target) && !is_array($target[$part])) throw new \RuntimeException('Invalid system_config section: ' . $part);
                if (!array_key_exists($part, $target)) $target[$part] = [];
                $target = &$target[$part];
            }
            $key = end($parts);
            $exists = array_key_exists($key, $target);
            if (!$exists || $target[$key] !== $value) {
                $changes[$path] = ['existed' => $exists, 'before' => $target[$key] ?? null, 'after' => $value];
                $target[$key] = $value;
            }
            unset($target);
        }
        if ($before && !$changes && (string)$before['value'] !== '') return null;
        return ['operation' => $before ? 'update' : 'insert', 'before' => $before, 'changes' => $changes,
            'after_value' => json_encode($configuration, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)];
    }

    /** Reverse only this receipt's fields; concurrent unrelated edits are preserved. */
    public static function rollbackPlan(array $rows, array $roles, array $adminRoles, array $options, array $receipt): array
    {
        if (($receipt['version'] ?? '') !== Navigation::VERSION || !isset($receipt['operations']) || !array_key_exists('option_operation', $receipt)) throw new \RuntimeException('Invalid navigation migration receipt');
        $byId = array_column($rows, null, 'id');
        $operations = [];
        foreach (array_reverse($receipt['operations']) as $operation) {
            $id = (int)$operation['id'];
            $current = $byId[$id] ?? null;
            $expected = $operation['operation'] === 'insert' ? $operation['after'] : array_replace($operation['before'], $operation['changes']);
            if (!$current || (string)$current['key'] !== (string)$expected['key'] || (int)$current['type'] !== (int)$expected['type']) throw new \RuntimeException('An owned rule identity changed; rollback refused');
            $check = $operation['operation'] === 'insert' ? $expected : $operation['changes'];
            foreach ($check as $key => $value) if ((string)($current[$key] ?? '') !== (string)$value) throw new \RuntimeException('An owned navigation field changed; rollback refused');
            if ($operation['operation'] === 'insert') {
                foreach ($byId as $row) if ((int)$row['pid'] === $id) throw new \RuntimeException('A new group has surviving children; rollback refused');
                foreach ($roles as $role) if (in_array((string)$id, explode(',', (string)$role['rules']), true)) throw new \RuntimeException('A new group is now assigned to a role; rollback refused');
                unset($byId[$id]);
                $operations[] = ['operation' => 'delete', 'id' => $id, 'before' => $current];
            } else {
                $changes = [];
                foreach ($operation['changes'] as $key => $_) $changes[$key] = $operation['before'][$key];
                $byId[$id] = array_replace($current, $changes);
                $operations[] = ['operation' => 'update', 'id' => $id, 'before' => $current, 'changes' => $changes];
            }
        }
        $option = $receipt['option_operation'];
        $optionOperation = null;
        if ($option) {
            if (count($options) !== 1) throw new \RuntimeException('Owned configuration is missing or ambiguous');
            $current = $options[0];
            if (($current['name'] ?? '') !== 'system_config') throw new \RuntimeException('Owned configuration identity changed');
            if ($option['operation'] === 'insert') {
                if (isset($option['after_id']) && (int)$current['id'] !== (int)$option['after_id']) throw new \RuntimeException('Initialized configuration identity changed; rollback refused');
                if ((string)$current['value'] !== $option['after_value']) throw new \RuntimeException('Initialized configuration has subsequent edits; rollback refused');
                $optionOperation = ['operation' => 'delete', 'before' => $current];
            } else {
                if ((int)$current['id'] !== (int)$option['before']['id']) throw new \RuntimeException('Owned configuration identity changed');
                $configuration = json_decode((string)$current['value'], true, 512, JSON_THROW_ON_ERROR);
                foreach ($option['changes'] as $path => $change) {
                    $parts = explode('.', $path); $target = &$configuration;
                    foreach (array_slice($parts, 0, -1) as $part) {
                        if (!isset($target[$part]) || !is_array($target[$part])) throw new \RuntimeException('Owned configuration structure changed');
                        $target = &$target[$part];
                    }
                    $key = end($parts);
                    if (!array_key_exists($key, $target) || $target[$key] !== $change['after']) throw new \RuntimeException('An owned configuration field changed; rollback refused');
                    if ($change['existed']) $target[$key] = $change['before']; else unset($target[$key]);
                    unset($target);
                }
                $original = (string)$option['before']['value'];
                $originalDecoded = $original !== '' ? json_decode($original, true, 512, JSON_THROW_ON_ERROR) : [];
                // Only ancestors of fields that this migration wrote are owned.
                // Never traverse/prune unrelated arrays added by a later editor.
                $containers = [];
                foreach (array_keys($option['changes']) as $path) {
                    $parts = explode('.', $path); array_pop($parts);
                    while ($parts) { $containers[implode('.', $parts)] = $parts; array_pop($parts); }
                }
                uasort($containers, static fn(array $a, array $b): int => count($b) <=> count($a));
                foreach ($containers as $parts) {
                    $originalNode = $originalDecoded; $existed = true;
                    foreach ($parts as $part) {
                        if (!is_array($originalNode) || !array_key_exists($part, $originalNode)) { $existed = false; break; }
                        $originalNode = $originalNode[$part];
                    }
                    if ($existed) continue;
                    $target = &$configuration;
                    foreach (array_slice($parts, 0, -1) as $part) {
                        if (!isset($target[$part]) || !is_array($target[$part])) { unset($target); continue 2; }
                        $target = &$target[$part];
                    }
                    $key = end($parts);
                    if (array_key_exists($key, $target) && $target[$key] === []) unset($target[$key]);
                    unset($target);
                }
                $value = (string)$current['value'] === $option['after_value'] ? $original : json_encode($configuration, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                $optionOperation = ['operation' => 'update', 'before' => $current, 'after_value' => $value];
            }
        }
        return ['version' => Navigation::VERSION, 'fingerprint' => self::fingerprint($rows, $roles, $adminRoles, $options), 'operations' => $operations,
            'option_operation' => $optionOperation, 'rules_before' => count($rows), 'rules_after' => count($byId), 'after' => array_values($byId), 'roles_unchanged' => true];
    }

    private static function update(array &$byId, int $id, array $changes, array &$operations): void
    {
        $before = $byId[$id];
        $changed = [];
        foreach ($changes as $key => $value) {
            if ((string)($before[$key] ?? '') !== (string)$value) $changed[$key] = $value;
        }
        if (!$changed) return;
        $byId[$id] = array_replace($before, $changed);
        $operations[] = ['operation' => 'update', 'id' => $id, 'before' => $before, 'changes' => $changed];
    }
}
