<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


namespace app\cms\compat;

/** Original input-helper contracts isolated from Webman's global input(). */
final class Input
{
    public static function get($key = '', $default = '', $filter = '')
    {
        if (0 === strpos($key, '?')) {
            $key = substr($key, 1);
            $has = true;
        }
        if ($pos = strpos($key, '.')) {
            // 指定参数来源
            list($method, $key) = explode('.', $key, 2);
            if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'route', 'param', 'request', 'session', 'cookie', 'server', 'env', 'path', 'file'])) {
                $key    = $method . '.' . $key;
                $method = 'param';
            }
        } else {
            // 默认为自动判断
            $method = 'param';
        }
        if (isset($has)) {
            $data = \app\cms\compat\request()->has($key, $method, $default);
        } else {
            $data = \app\cms\compat\request()->$method($key, $default, $filter);
        }
        /*防止shell注入处理*/
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = self::allowed($val) ? $val : '';
            }
        } else if (is_string($data) && stristr($data, ',')) {
            $arr = explode(',', $data);
            foreach ($arr as $key => $val) {
                $arr[$key] = self::allowed($val) ? $val : '';
            }
            $data = implode(',', $arr);
        } else {
            $data = self::allowed($data) ? $data : '';
        }
        /*--end*/

        $city_switch_on =& \app\cms\CmsContext::current()->memo[__FUNCTION__ . ':city_switch_on'];
        if ($city_switch_on === null) { $city_switch_on = null; }
        null === $city_switch_on && $city_switch_on = \app\cms\compat\config('city_switch_on');
        if (!empty($city_switch_on)) {
            if (('site' == $key || preg_match('/^site\//i', $key)) && in_array($method, ['param','get']) && stristr(\app\cms\compat\request()->baseFile(), 'index.php')) {
                $current_site = '';
                /*兼容伪静态多城市切换*/
                $pathinfo = \app\cms\compat\request()->pathinfo();
                if (!empty($pathinfo)) {
                    $s_arr = explode('/', $pathinfo);
                    if ('m' == $s_arr[0]) {
                        $s_arr[0] = $s_arr[1];
                    }
                    $count = \app\cms\compat\Db::name('citysite')->where(['domain'=>$s_arr[0]])->cache(true, EYOUCMS_CACHE_TIME, 'citysite')->count();
                    if (!empty($count)) {
                        $current_site = $s_arr[0];
                    }
                }
                /*--end*/

                /*支持独立域名配置*/
                // if (empty($current_site)) {
                //     $subDomain = request()->subDomain();
                //     if (!empty($subDomain) && 'www' != $subDomain) {
                //         $siteInfo = \app\cms\compat\Db::name('citysite')->where('domain',$subDomain)->cache(true, EYOUCMS_CACHE_TIME, 'citysite')->find();
                //         if (!empty($siteInfo['is_open'])) {
                //             $current_site = $siteInfo['domain'];
                //         }
                //     }
                // }
                /*--end*/

                if (isset($data['site'])) {
                    $site = trim($data['site'], '/');
                    $site = trim($site);
                    empty($site) && $data['site'] = $current_site;
                } else if (is_string($data)) {
                    $site = trim($data, '/');
                    $site = trim($site);
                    empty($site) && $data = $current_site;
                }
            }
        }

        return $data;
    }

    private static function allowed($value): bool
    {
        return $value === null || !function_exists('eyPreventShell') || \eyPreventShell($value);
    }
}
