<?php
namespace app\cms;

use app\cms\compat\Cache;
use support\think\Db;

/** Reverse custom static naming rules through the same upstream URL helpers. */
final class StaticRoutes
{
    public static function match(string $path, CmsContext $context): ?array
    {
        if ((int)$context->getConfig('ey_config.seo_pseudo') !== 2) return null;
        $index = Cache::get('cms-static-route-index-v3');
        if (!is_array($index)) {
            $index = ['exact' => [], 'pages' => []];
            $types = Db::name('arctype')->where(['is_del' => 0, 'status' => 1, 'lang' => $context->lang])->select()->toArray();
            $types = array_column($types, null, 'id');
            foreach ($types as $type) {
                if ((int)$type['is_part'] === 1) continue;
                $url = typeurl('home/Lists/index', $type);
                $key = self::key($url, $context);
                $target = ['m' => 'home', 'c' => 'Lists', 'a' => 'index', 'tid' => $type['id']];
                $index['exact'][$key] = $target;
                // Web servers often append index.html for a directory URL.
                $index['exact'][rtrim($key, '/') . '/index.html'] = $target;
                $pageUrl = $url;
                get_list_only_pageurl($pageUrl, $type['id'], $type['rulelist'], 987654321);
                $pageKey = self::key($pageUrl, $context);
                if (str_contains($pageKey, '987654321')) {
                    $index['pages'][] = ['pattern' => '#^' . str_replace('987654321', '(?P<page>[1-9][0-9]{0,8})', preg_quote($pageKey, '#')) . '$#D', 'target' => $target];
                }
            }
            if ($types) {
                $models = Db::name('channeltype')->column('ctl_name', 'id');
                foreach (Db::name('archives')->where(['is_del' => 0, 'status' => 1, 'lang' => $context->lang])->where('arcrank', '>', -1)->whereIn('typeid', array_keys($types))->whereIn('channel', $context->getConfig('global.allow_release_channel', []))->select()->toArray() as $archive) {
                    if (!empty($archive['is_jump'])) continue;
                    $row = array_replace($types[$archive['typeid']], $archive);
                    $url = arcurl('home/' . ($models[$archive['channel']] ?? 'View') . '/view', $row);
                    $index['exact'][self::key($url, $context)] = ['m' => 'home', 'c' => 'View', 'a' => 'index', 'aid' => $archive['aid']];
                }
            }
            Cache::set('cms-static-route-index-v3', $index, 3600);
        }
        $path = trim($path, '/');
        if (isset($index['exact'][$path])) return $index['exact'][$path];
        foreach ($index['pages'] as $rule) {
            if (preg_match($rule['pattern'], $path, $values)) return $rule['target'] + ['page' => (int)$values['page']];
        }
        return null;
    }

    private static function key(string $url, CmsContext $context): string
    {
        $path = rawurldecode((string)parse_url($url, PHP_URL_PATH));
        $base = (string)($context->global['web_cmspath'] ?? '');
        if ($base !== '' && str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
        return trim($path, '/');
    }
}
