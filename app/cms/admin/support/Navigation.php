<?php
declare(strict_types=1);

namespace app\cms\admin\support;

/** A request-local display projection; this class never grants or persists permissions. */
class Navigation
{
    public const VERSION = '20261106-basic-settings-v1';

    public static function specification(): array
    {
        return require \app\cms\Paths::cms('admin/config/navigation.php');
    }

    /** Preserve every rule identity/action subtree when preparing a fresh menu import. */
    public static function menuDefaults(array $legacy): array
    {
        $byKey = [];
        $collect = static function (array $items) use (&$collect, &$byKey): void {
            foreach ($items as $item) {
                $byKey[$item['key']] = $item;
                $collect($item['children'] ?? []);
            }
        };
        $collect($legacy);
        $result = [];
        foreach (self::specification() as $index => $group) {
            $children = [];
            foreach ($group['modules'] as $module) {
                foreach ($module['pages'] as $page) {
                    // A display alias reuses an existing permission and has no database rule.
                    if (isset($page['permission_key'])) continue;
                    if (!isset($byKey[$page['key']])) {
                        throw new \LogicException('Missing original navigation rule: ' . $page['key']);
                    }
                    $item = $byKey[$page['key']];
                    $item['weight'] = 1000 - count($children);
                    $children[] = $item;
                }
            }
            $result[] = ['title' => $group['title'], 'key' => $group['key'], 'icon' => $group['icon'],
                'type' => 0, 'pid' => 0, 'weight' => 1100 - $index * 100, 'children' => $children];
        }
        $known = [];
        foreach (self::specification() as $group) foreach ($group['modules'] as $module) foreach ($module['pages'] as $page) $known[$page['key']] = true;
        $remaining = static function (array $items) use (&$remaining, $known): array {
            $result = [];
            foreach ($items as $item) {
                if (isset($known[$item['key']])) continue; // Its complete subtree moved above.
                if (isset($item['children'])) $item['children'] = $remaining($item['children']);
                if ((int)$item['type'] === 0 && empty($item['children'])) continue;
                $result[] = $item;
            }
            return $result;
        };
        foreach ($remaining($legacy) as $extra) {
            foreach ($result as $index => $group) {
                if ($group['key'] === $extra['key'] && (int)$extra['type'] === 0) {
                    array_push($result[$index]['children'], ...($extra['children'] ?? []));
                    continue 2;
                }
            }
            $result[] = $extra;
        }
        return $result;
    }

    /** Effective menu visibility includes the full parent chain, with cycle protection. */
    public static function visibleIds(array $rows): array
    {
        $byId = array_column($rows, null, 'id');
        $result = [];
        foreach ($rows as $row) {
            $current = (int)$row['id'];
            $seen = [];
            while ($current !== 0) {
                if (isset($seen[$current]) || !isset($byId[$current]) || (int)($byId[$current]['status'] ?? 0) !== 1) {
                    continue 2;
                }
                $seen[$current] = true;
                $current = (int)($byId[$current]['pid'] ?? 0);
            }
            $result[(int)$row['id']] = true;
        }
        return $result;
    }

    /** Match Auth::canAccess(index): a controller or one of its exact action grants. */
    public static function accessibleController(string $controller, array $grantedKeys, bool $super): bool
    {
        if ($super) return true;
        $controller = strtolower($controller);
        foreach ($grantedKeys as $key) {
            $key = strtolower((string)$key);
            if ($key === $controller || str_starts_with($key, $controller . '@')) return true;
        }
        return false;
    }

    public static function metadata(array $rows, array $grants, array $legacyMenus = []): array
    {
        $rows = AdminRoutes::projectUrls($rows);
        $legacyMenus = AdminRoutes::projectUrls($legacyMenus);
        $byKey = [];
        $grantedKeys = [];
        $grantSet = [];
        $super = in_array('*', $grants);
        foreach ($rows as $row) {
            $byKey[strtolower((string)$row['key'])] = $row;
            // Keep the original menu's numeric CSV matching (including leading zero IDs).
            if (in_array($row['id'], $grants)) {
                $grantSet[(string)$row['id']] = true;
                $grantedKeys[] = $row['key'];
            }
        }
        $visible = self::visibleIds($rows);
        // Preserve the old menu tree's effective visibility as well as API access.
        // A hidden action can still authorize an API, but must not unhide its menu.
        $byId = array_column($rows, null, 'id');
        $reachable = [];
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            if (!isset($visible[$id]) || (!$super && !isset($grantSet[(string)$id]))) continue;
            while ($id !== 0 && !isset($reachable[$id])) {
                $reachable[$id] = true;
                $id = (int)$byId[$id]['pid'];
            }
        }
        $groups = $modules = $pages = $menus = $sidebarDefaults = [];
        $knownKeys = [];
        foreach (self::specification() as $groupIndex => $group) {
            $groupModules = $groupMenus = [];
            foreach ($group['modules'] as $moduleIndex => $module) {
                $moduleId = 'cms-nav-' . $module['id'];
                $modulePages = [];
                foreach ($module['pages'] as $pageSpec) {
                    $knownKeys[strtolower($pageSpec['key'])] = true;
                    $permissionKey = $pageSpec['permission_key'] ?? $pageSpec['key'];
                    $row = $byKey[strtolower($permissionKey)] ?? null;
                    if (!$row || (int)$row['type'] !== 1 || !isset($visible[(int)$row['id']], $reachable[(int)$row['id']]) ||
                        !self::accessibleController($permissionKey, $grantedKeys, $super)) continue;
                    $href = (string)($pageSpec['href'] ?? $row['href'] ?? '');
                    if ($href === '') continue;
                    $page = ['id' => $pageSpec['id'] ?? (int)$row['id'], 'key' => $pageSpec['key'], 'title' => $pageSpec['title'],
                        'href' => $href, 'permission' => $row['key'], 'module' => $moduleId, 'group' => $group['id'],
                        'auxiliary' => (bool)($pageSpec['auxiliary'] ?? false), 'return_key' => $pageSpec['return_key'] ?? null,
                        'sections' => $pageSpec['sections'] ?? []];
                    $modulePages[] = $page;
                    $pages[] = $page;
                }
                if (!$modulePages) continue;
                $sidebar = $module['sidebar'] ?? true;
                $shortcut = (bool)$sidebar && (bool)($module['shortcut'] ?? false);
                $entry = $modulePages[0];
                $modules[] = ['id' => $moduleId, 'title' => $module['title'], 'group' => $group['id'], 'weight' => 1000 - $moduleIndex,
                    'icon' => $module['icon'], 'sidebar' => $sidebar, 'shortcut' => $shortcut, 'entry' => $entry, 'pages' => $modulePages];
                if ($shortcut) $sidebarDefaults[] = $moduleId;
                $groupModules[] = $moduleId;
                if ($sidebar) {
                    $groupMenus[] = ['id' => $entry['id'], 'pid' => 'cms-nav-group-' . $group['id'],
                        'key' => $entry['key'], 'title' => $module['title'], 'name' => $module['title'],
                        'value' => $entry['id'], 'href' => $entry['href'], 'type' => 1,
                        'icon' => 'layui-icon ' . $module['icon'], 'module' => $moduleId, 'weight' => 1000 - $moduleIndex];
                }
            }
            if ($groupModules) $groups[] = ['id' => $group['id'], 'key' => $group['key'], 'title' => $group['title'],
                'icon' => $group['icon'], 'modules' => $groupModules, 'weight' => 1100 - $groupIndex * 100];
            if ($groupMenus) $menus[] = ['id' => 'cms-nav-group-' . $group['id'], 'pid' => 0, 'key' => $group['key'],
                'title' => $group['title'], 'name' => $group['title'], 'value' => 'cms-nav-group-' . $group['id'],
                'type' => 0, 'icon' => 'layui-icon ' . $group['icon'], 'weight' => 1100 - $groupIndex * 100, 'children' => $groupMenus];
        }
        // Keep authorized custom rules and their original hierarchy. Known leaf descendants
        // are promoted only when their standard parent becomes a module display entry.
        $custom = static function (array $items) use (&$custom, $knownKeys): array {
            $result = [];
            foreach ($items as $item) {
                $key = strtolower((string)($item['key'] ?? ''));
                $href = strtolower((string)($item['href'] ?? ''));
                if (preg_match('~(?:uisetcontroller|plugincontroller|devcontroller)(?:@|$)~', $key) || preg_match('~^/web-admin/(?:uiset|plugin|dev)(?:/|$)|^/web-admin/table/crud(?:/|$)~', $href)) continue;
                $children = $custom($item['children'] ?? []);
                if (isset($knownKeys[strtolower((string)($item['key'] ?? ''))])) {
                    array_push($result, ...$children);
                    continue;
                }
                if ((int)($item['type'] ?? 0) === 0 && !$children) continue;
                if (isset($item['children'])) $item['children'] = $children;
                $result[] = $item;
            }
            return $result;
        };
        array_push($menus, ...$custom($legacyMenus));
        $sort = static function (array &$items) use (&$sort): void {
            usort($items, static function (array $a, array $b): int {
                $weight = (int)($b['weight'] ?? 0) <=> (int)($a['weight'] ?? 0);
                if ($weight) return $weight;
                $aId = (string)($a['id'] ?? ''); $bId = (string)($b['id'] ?? '');
                return ctype_digit($aId) && ctype_digit($bId) ? (int)$aId <=> (int)$bId : strcmp($aId, $bId);
            });
            foreach ($items as &$item) if (isset($item['children'])) $sort($item['children']);
            unset($item);
        };
        $sort($menus);
        // A display-only destination for an authenticated admin, never a persisted rule
        // or an additional page/module grant. The controller retains its login check.
        $map = ['id' => 'cms-feature-map', 'key' => 'cms-feature-map', 'title' => '功能地图',
            'href' => '/web-admin/index/featuremap', 'permission' => '', 'module' => 'cms-feature-map', 'group' => ''];
        return ['version' => self::VERSION, 'groups' => $groups, 'modules' => $modules, 'pages' => $pages,
            'menus' => $menus, 'map' => $map, 'sidebar_defaults' => $sidebarDefaults];
    }
}
