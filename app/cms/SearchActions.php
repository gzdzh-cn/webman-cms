<?php
/**
 * 易优CMS
 * ============================================================================
 * 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.eyoucms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 * Adapted from application/home/controller/Search.php for request-scoped Webman.
 */
namespace app\cms;

use app\cms\compat\Db;
use app\cms\compat\Request;
use app\cms\compat\Runtime;
use app\cms\compat\Session;
use function app\cms\compat\input;

/** Original enterprise search entry, including ordinary search-form accounting. */
final class SearchActions
{
    public static function prepare(CmsContext $context, string $action = 'lists'): array
    {
        $parameters = input('param.');
        // Legacy tag helpers expose these resolved IDs even without route input.
        // Search fields contain only parameters actually supplied by that route.
        $supplied = array_replace($context->request?->all() ?? [], $context->parameters);
        foreach (['tid', 'aid'] as $key) {
            if (!array_key_exists($key, $supplied)) {
                unset($parameters[$key]);
                continue;
            }
            // Resolved tag IDs must not replace explicitly supplied search fields.
            $filtered = Request::instance()->input([$key => $supplied[$key]], '', '', '');
            $value = $filtered[$key];
            $parameters[$key] = $value === null || !function_exists('eyPreventShell') || eyPreventShell($value) ? $value : '';
        }
        if ($action === 'index') {
            $template = self::indexTemplate($context);
            if ($template !== null) {
                $parameters['pageurl'] = Request::instance()->url(true);
                $parameters['pageurl_m'] = pc_to_mobile_url($parameters['pageurl']);
                $parameters['mobile_domain'] = '';
                if (!empty($context->global['web_mobile_domain_open']) && !empty($context->global['web_mobile_domain'])) {
                    $parameters['mobile_domain'] = $context->global['web_mobile_domain'] . '.' . Request::instance()->rootDomain();
                }
                if (!isset($parameters['keywords'])) $parameters['keywords'] = '';
                return ['kind' => 'render', 'field' => $parameters, 'template' => $template];
            }
        }
        if (!array_key_exists('keywords', $parameters)) return ['kind' => 'abort', 'status' => 404];
        // input() additionally rejects shell prefixes; the original controller
        // deliberately reads Request again for validation and recording.
        $word = Request::instance()->param('keywords');
        if (empty($word)) return self::error(foreign_lang('system15', $context->lang));
        $page = Request::instance()->param('page');
        if (Runtime::looseCompare(2, $page, '>')) {
            $decoded = strtolower(htmlspecialchars_decode($word, ENT_COMPAT));
            $configuration = tpCache('search');
            if (empty($configuration) || !array_key_exists('search_tabu_words', $configuration)) {
                $configuration['search_tabu_words'] = ['<', '>', '"', ';', ',', '@', '&', '#', '\\', '*', 'eval', 'call_user_func_array', 'file_put_contents', 'phpinfo', 'shell_exec'];
            } else {
                $configuration['search_tabu_words'] = explode(PHP_EOL, strtolower($configuration['search_tabu_words']));
            }
            foreach ($configuration['search_tabu_words'] as $tabu) {
                // PHP7 strstr('', '') warns and returns false. Its diagnostic
                // is not reproduced, but an empty configured item never blocks.
                if ($tabu !== '' && strstr($decoded, $tabu)) {
                    return self::error(sprintf(foreign_lang('system13', $context->lang), $tabu));
                }
            }
            $word = addslashes($word);
            if (!empty(input('param.method/d'))) {
                $users = Session::get('users', []) ?: [];
                $userId = !empty($users['users_id']) ? intval($users['users_id']) : 0;
                $adminId = !empty($users['admin_id']) ? intval($users['admin_id']) : 0;
                $now = getTime();
                // isset, rather than key existence, is the original default rule.
                foreach (['search_second' => 60, 'search_maxnum' => 5, 'search_locking' => 120] as $key => $default) {
                    if (!isset($configuration[$key])) $configuration[$key] = $default;
                }
                $searchFields = array_flip(Db::name('search_word')->getTableFields());
                $hasIdentity = isset($searchFields['users_id'], $searchFields['ip']);
                if (!$hasIdentity && empty($adminId) && Runtime::looseCompare(0, $configuration['search_second'], '<')) {
                    $surplus = self::legacySearchWait($configuration, $now, $userId);
                    if ($surplus > 0) return self::error(sprintf(foreign_lang('system14', $context->lang), ceil($surplus / 60)), [], $surplus);
                }
                if ($hasIdentity && empty($adminId) && Runtime::looseCompare(0, $configuration['search_second'], '<')) {
                    $where = !empty($userId) ? ['users_id' => $userId] : ['ip' => clientIP()];
                    $total = Db::name('search_word')->where($where)->where([
                        'update_time' => ['gt', $now - self::number($configuration['search_second'])],
                    ])->count();
                    $locking = Db::name('search_locking')->where($where)->find();
                    if ($total >= intval($configuration['search_maxnum'])) {
                        if (empty($locking)) {
                            $locking = ['users_id' => $userId, 'ip' => clientIP(), 'locking_time' => $now, 'add_time' => $now, 'update_time' => $now];
                            $locking['id'] = Db::name('search_locking')->insertGetId($locking);
                        } elseif (self::number($locking['locking_time']) + self::number($configuration['search_locking']) < $now) {
                            Db::name('search_locking')->where(['id' => $locking['id']])->update(['locking_time' => $now, 'update_time' => $now]);
                            $locking['locking_time'] = $now;
                        }
                    }
                    if (!empty($locking)) {
                        $lockingTime = !empty($locking['locking_time']) ? $locking['locking_time'] : 0;
                        $surplus = self::number($lockingTime) + self::number($configuration['search_locking']) - $now;
                        if ($surplus > 0) return self::error(sprintf(foreign_lang('system14', $context->lang), ceil($surplus / 60)), [], $surplus);
                    }
                }
                $existing = Db::name('search_word')->field('id')->where(['word' => $word, 'lang' => $context->lang])->find();
                if (empty($existing)) {
                    Db::name('search_word')->insert(array_intersect_key(['word' => $word, 'sort_order' => 100, 'users_id' => $userId,
                        'ip' => clientIP(), 'lang' => $context->lang, 'add_time' => $now, 'update_time' => $now], $searchFields));
                } else {
                    Db::name('search_word')->where(['id' => $existing['id']])->update(array_intersect_key(['searchNum' => Db::raw('searchNum+1'),
                        'users_id' => $userId, 'ip' => clientIP(), 'update_time' => $now], $searchFields));
                }
            }
        }
        if (!isset($parameters['keywords'])) $parameters['keywords'] = '';
        return ['kind' => 'render', 'field' => $parameters, 'template' => self::listsTemplate($context)];
    }

    /** Old imports lack visitor columns and search_locking; use the installed state store. */
    private static function legacySearchWait(array $configuration, int $now, int $userId): int|float
    {
        $store = new \app\cms\persistence\StateStore('search:limits');
        $key = hash('sha256', $userId > 0 ? 'user:'.$userId : 'ip:'.clientIP());
        return $store->locked(function () use ($store, $key, $configuration, $now): int|float {
            $state = $store->read($key);
            $surplus = ($state['locked_until'] ?? 0) - $now;
            if ($surplus > 0) return $surplus;
            $since = $now - self::number($configuration['search_second']);
            $hits = array_values(array_filter($state['hits'] ?? [], static fn($hit) => $hit > $since));
            if (count($hits) >= intval($configuration['search_maxnum'])) {
                $wait = max(0, self::number($configuration['search_locking']));
                $store->write($key, ['hits'=>$hits, 'locked_until'=>$now + $wait]);
                if ($wait > 0) return $wait;
            }
            $hits[] = $now;
            $store->write($key, ['hits'=>$hits, 'locked_until'=>0]);
            return 0;
        });
    }

    private static function error(string $message, mixed $data = '', int|float $wait = 5): array
    {
        return ['kind' => 'error', 'result' => ['code' => 0, 'msg' => $message, 'data' => $data, 'wait' => $wait, 'target' => '_self']];
    }

    /** PHP7 accepts a numeric prefix in arithmetic on stored string settings. */
    private static function number(mixed $value): int|float
    {
        if (!is_string($value)) return is_int($value) || is_float($value) ? $value : (int)$value;
        return preg_match('/^[\x09-\x0d ]*[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?/', $value, $match) ? $match[0] + 0 : 0;
    }

    private static function indexTemplate(CmsContext $context): ?string
    {
        $template = 'index_search';
        if (file_exists($context->templateRoot() . $template . '.htm')) return $template;
        if ($context->getConfig('city_switch_on') && !empty($context->site)) {
            return self::cityTemplate($context, $template);
        }
        if ($context->getConfig('lang_switch_on') && !empty($context->lang)
            && file_exists($context->templateRoot() . $template . '_' . $context->lang . '.htm')) return $template . '_' . $context->lang;
        return null;
    }

    private static function listsTemplate(CmsContext $context): string
    {
        $template = 'lists_search';
        $channel = input('param.channelid/d');
        if (!empty($channel) && file_exists($context->templateRoot() . $template . '_' . $channel . '.htm')) $template .= '_' . $channel;
        if ($context->getConfig('city_switch_on') && !empty($context->site)) return self::cityTemplate($context, $template) ?? $template;
        if ($context->getConfig('lang_switch_on') && !empty($context->lang)
            && file_exists($context->templateRoot() . $template . '_' . $context->lang . '.htm')) $template .= '_' . $context->lang;
        return $template;
    }

    private static function cityTemplate(CmsContext $context, string $template): ?string
    {
        if (!empty($context->global['site_template'])) {
            foreach (['city/' . $context->site, 'city/default', $context->site] as $directory) {
                if (file_exists($context->templateRoot() . $directory)) return $directory . '/' . $template;
            }
        }
        return null;
    }
}
