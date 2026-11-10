<?php
namespace app\cms;

use app\cms\compat\Cookie;
use app\cms\compat\exception\HttpException;
use app\cms\compat\exception\HttpResponseException;
use app\cms\compat\Request;
use support\think\Db;

/** Original enterprise city selection and its request-local template variables. */
final class CityContext
{
    public static function load(CmsContext $context): void
    {
        $context->site = '';
        $context->siteInfo = [];
        $enabled = array_key_exists('web_citysite_open', $context->global)
            ? !empty($context->global['web_citysite_open'])
            : ((int)(new \app\cms\persistence\LegacyMarkerStore())->read('citysite.txt', '0') > 0);
        $multilingual = !empty($context->global['cms_multilingual_enabled']);
        $context->memo['site.modules.conflict'] = $multilingual && $enabled;
        // Read-only conflict guard: preserve both stored choices for an explicit fix.
        $context->setConfig('city_switch_on', !$multilingual && $enabled);
        Cookie::delete('site_info');
        if (!$context->getConfig('city_switch_on')) return;

        // switch_citysite runs before Route::check. Never publish its path
        // default into the original query used to build the route registry.
        $selection = $context->memo['city.request_site'] ??= [
            'explicit' => (bool)$context->input('param.?site'),
            'value' => (string)$context->input('param.site/s', ''),
        ];
        if (empty($context->memo['city.switched'])) {
            $site = trim($selection['value'], '/');
            if (!empty($site) && !preg_match('/^[A-Za-z0-9_-]+$/D', $site)) throw new HttpException(404, '页面不存在');
            if (!$selection['explicit']) {
                $segments = explode('/', $context->routingPath());
                $pathSite = $segments[0] === 'm' ? ($segments[1] ?? '') : $segments[0];
                if ($pathSite !== '') $site = (string)(Db::name('citysite')->where('domain', $pathSite)->value('domain') ?: '');
            }
            if (!empty($site)) {
                // Original switch checks existence and default redirect before
                // Common checks status; slash trimming applies only here.
                $record = Db::name('citysite')->where('domain', $site)->find();
                if (!$record || empty($record['domain'])) throw new HttpException(404, '页面不存在');
                if ($record['id'] == ($context->global['site_default_home'] ?? 0)) {
                    throw new HttpResponseException(redirect(($context->global['web_cmspath'] ?? '') . '/'));
                }
            } else {
                // Upstream Request::subDomain caches its first result. The
                // second call with web_basehost returns that same value, so
                // switch_citysite's Host exclusion always takes this branch.
                // get_home_site below still resolves the actual Host normally.
                $site = 'www';
            }
            $context->memo['city.switch_site'] = $site;
            $context->memo['city.switched'] = true;
        }
        if (empty($context->memo['city.defer_common'])) self::select($context);
    }

    /** Common runs after route parameters override the original request. */
    public static function select(CmsContext $context): void
    {
        if (!$context->getConfig('city_switch_on')) return;
        $explicit = (bool)$context->input('param.?site');
        $requested = (string)$context->input('param.site/s', '');
        $homeSite = $requested;
        if (empty($homeSite)) {
            $subdomain = Request::instance()->subDomain();
            if (!empty($subdomain) && $subdomain !== 'www') {
                $hostSite = Db::name('citysite')->where('domain', $subdomain)->find();
                if (!empty($hostSite['is_open'])) $homeSite = (string)$hostSite['domain'];
            }
            if (empty($homeSite)) {
                // Keep PHP7's original observable get_default_site() value 0.
                $default = $context->global['site_default_home'] ?? 0;
                $homeSite = !empty($default) ? (string)(Db::name('citysite')->where('id', $default)->value('domain') ?? '') : (string)$default;
            }
        }
        $context->site = preg_replace('/([^\w\-\_])/i', '', $homeSite);
        $context->siteInfo = [];
        Cookie::delete('site_info');
        // Explicit empty/zero inputs suppress the Host for eyou.site, although
        // get_home_site (and city template selection) can still use that Host.
        $site = $explicit ? $requested : $context->site;
        $where = !empty($site) ? ['domain' => $site] : (!empty($context->global['site_default_home']) ? ['id' => $context->global['site_default_home']] : []);
        if (!$where) return;
        $record = Db::name('citysite')->where($where)->find();
        if (!$record || empty($record['status'])) throw new HttpException(404, '页面不存在');
        unset($record['add_time'], $record['update_time'], $record['sort_order']);
        if (!empty($record['seoset'])) {
            foreach (['seo_title', 'seo_keywords', 'seo_description'] as $key) $record[$key] = str_replace(['{region}', '{区域}'], (string)$record['name'], (string)($record[$key] ?? ''));
        } elseif (!empty($context->global['site_seoset'])) {
            foreach (['seo_title', 'seo_keywords', 'seo_description'] as $key) $record[$key] = site_seo_handle((string)($context->global['site_' . $key] ?? ''), $record);
        }
        $context->siteInfo = $record;
        Cookie::set('site_info', json_encode($record, JSON_UNESCAPED_UNICODE));
    }

    public static function template(CmsContext $context, string $template): string
    {
        if (!$context->getConfig('city_switch_on') || empty($context->site) || empty($context->global['site_template'])) return $template;
        foreach (['city/' . $context->site, 'city/default', $context->site] as $directory) {
            if (is_dir($context->templateRoot() . $directory)) return $directory . '/' . $template;
        }
        return $template;
    }
}
