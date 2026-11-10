<?php
/** Framework-safe global entry points; upstream helpers are loaded afterwards. */

use app\cms\CmsContext;
use app\cms\UrlGenerator;
use app\cms\compat\Runtime;
use support\think\Db;

function tpCache($key, $data = [], $lang = '', $options = null)
{
    $context = CmsContext::current();
    $lang = $lang ?: $context->lang;
    [$group, $name] = array_pad(explode('.', (string)$key, 2), 2, null);
    if ($data) {
        Db::transaction(function () use ($group, $lang, $data) {
            foreach ($data as $name => $value) {
                $where = ['inc_type' => $group, 'lang' => $lang, 'name' => $name];
                $value = is_array($value) ? serialize($value) : (string)$value;
                if (Db::name('config')->where($where)->find()) Db::name('config')->where($where)->update(['value' => $value, 'update_time' => time()]);
                else Db::name('config')->insert($where + ['value' => $value, 'update_time' => time()]);
            }
        });
        $context->load();
        \app\cms\compat\Cache::clear();
    }
    if (!$context->global) $context->load();
    if ($group === 'global' && $name === null) return $context->global;
    if ($name !== null && array_key_exists($name, $context->global)) return $context->global[$name];
    $values = $context->getConfig('groups.' . $group, []);
    if (array_key_exists('web_users_switch', $values)) $values['web_users_switch'] = 0;
    if ($group === 'shop') $values['shop_open'] = 0;
    return $name === null ? $values : ($values[$name] ?? '');
}

function get_main_lang($force = false)
{
    $context = CmsContext::current();
    $main = $context->getConfig('main_lang');
    if ($force || $main === null) {
        $main = (string)(Db::name('language')->order('id asc')->value('mark') ?? '');
        $context->config['main_lang'] = $main;
    }
    return (string)$main;
}
function get_default_lang($force = false)
{
    $context = CmsContext::current();
    return $context->module === 'admin' ? get_admin_lang($force) : $context->getConfig('ey_config.system_home_default_lang');
}
function get_home_lang($force = false) { return CmsContext::current()->lang; }
function get_current_lang() { return CmsContext::current()->lang; }
function get_admin_lang() { return CmsContext::current()->lang; }
function get_home_site() { return CmsContext::current()->site; }
function isMobile() { return CmsContext::current()->device === 'mobile'; }
function is_language()
{
    $policy = new \app\cms\service\LanguagePolicy();
    $number = count($policy->all());
    return $policy->enabled() && $number > 1 ? $number : false;
}
function M($name = '', ...$arguments) { return \app\cms\compat\Db::name($name); }
function D($name = '', ...$arguments) { return Runtime::model($name); }
function model($name = '', ...$arguments) { return Runtime::model($name); }
function I($name = '', $default = null, $filter = null) { return CmsContext::current()->input($name, $default, $filter); }
function url($route = '', $parameters = [], $suffix = true, $domain = false, $mode = null, $format = null, $inlet = null) { return UrlGenerator::build($route, $parameters, $suffix, $domain, $mode, $format, $inlet); }

function safeRequest($default = null) { return CmsContext::current()->request ?? $default; }
function safeGet($name, $default = null) { return CmsContext::current()->input('param.' . $name, $default); }
function safeHeader($name, $default = null) { return safeRequest()?->header($name, $default) ?? $default; }
function safePath($default = '') { return safeRequest()?->path() ?? $default; }
function safeAll($default = []) { return safeRequest()?->all() ?? $default; }

// All upstream calls to framework helpers are rewritten to the compatibility
// namespace; Webman's global request/config/session functions stay untouched.
require_once __DIR__ . '/upstream/function.php';
require_once __DIR__ . '/upstream/common.php';
require_once __DIR__ . '/upstream/helper.php';
require_once __DIR__ . '/upstream/core.php';
require_once __DIR__ . '/upstream/home.php';
require_once __DIR__ . '/upstream/custom.php';
