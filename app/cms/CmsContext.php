<?php

namespace app\cms;

use support\Request;
use support\think\Db;
use Webman\Context;

/** Request-scoped EyouCMS state; never store this object in a worker singleton. */
final class CmsContext
{
    public int $tid = 0;
    public int $aid = 0;
    public string $lang = 'cn';
    public string $device = 'pc';
    public string $theme = 'default/pc';
    public string $module = 'home';
    public string $controller = 'Index';
    public string $action = 'index';
    public string $site = '';
    public array $siteInfo = [];
    public array $global = [];
    public array $field = [];
    public array $pagination = [];
    public array $config = [];
    public array $memo = [];
    public array $parameters = [];

    public function __construct(public ?Request $request = null)
    {
        $this->parameters = $request?->all() ?? [];
        $this->config = [
            'app_debug' => (bool)config('app.debug', false),
            'pathinfo_depr' => '/', 'url_html_suffix' => 'html',
            'url_common_param' => false, 'url_param_type' => 0, 'url_convert' => false,
            'url_domain_root' => '', 'app_host' => '',
            'default_lang' => 'cn', 'main_lang' => null, 'lang_switch_on' => false,
            'city_switch_on' => false, 'language_split' => false,
            'template' => ['view_suffix' => 'htm', 'tpl_cache' => false],
            'paginate' => ['type' => 'eyou', 'var_page' => 'page', 'list_rows' => 15],
            'database' => ['prefix' => \app\cms\database\TableNames::cms()],
            'captcha' => ['default' => ['length' => 4, 'imageW' => 0, 'imageH' => 0], 'guestbook' => ['is_on' => 0, 'config' => []]],
            'global' => [
                'arctype_channel_id' => -1, 'arctype_max_level' => 10,
                'citysite_max_level' => 3, 'url_screen_var' => 'ZXljbXM',
                'allow_release_channel' => [1, 2, 3, 4, 5, 7, 9],
                'channeltype_list' => [], 'parse_url_param' => ['lang', 'site', 'goto'],
                'field_region_type' => [], 'validate_type_list' => [],
                'tpl_deny_func_list' => 'phpinfo,eval,exit,exec,passthru,shell_exec,system,proc_open,popen',
            ],
        ];
    }

    public static function current(): self
    {
        $context = Context::get('cms.context');
        if (!$context instanceof self) {
            $context = new self(function_exists('request') ? request() : null);
            self::set($context);
        }
        return $context;
    }

    public static function set(self $context): void
    {
        Context::set('cms.context', $context);
    }

    /** Site-relative PATH_INFO shared by selection and controller routing. */
    public function routingPath(): string
    {
        $path = trim(rawurldecode((string)$this->request?->path()), '/');
        $base = trim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        if ($base !== '') {
            if ($path === $base) $path = '';
            elseif (str_starts_with($path, $base . '/')) $path = substr($path, strlen($base) + 1);
        }
        return $path === 'index.php' ? '' : preg_replace('#^index\.php/#', '', $path);
    }

    public function load(): void
    {
        $this->global = [];
        $this->config['groups'] = [];
        $allLanguages = Db::name('language')->order('id asc')->select()->toArray();
        $policy = new \app\cms\service\LanguagePolicy();
        $main = $policy->main();
        $this->config['main_lang'] = $main;
        $enabled = $policy->enabled();
        $backend = $this->module === 'admin' || ($this->request && preg_match('#^/web-admin(?:/|$)#', $this->request->path()));
        $languages = array_values(array_filter($allLanguages, static fn($row) => (int)$row['status'] === 1));
        $public = $policy->publicLanguages();
        $available = array_column($backend ? $allLanguages : $public, null, 'mark');
        $default = $main;
        if ($enabled) foreach ($languages as $language) if ((int)$language['is_home_default'] === 1) { $default = (string)$language['mark']; break; }
        if ($backend) $default = (string)($this->memo['cms.admin.language']['lang'] ?? $main) ?: $main;
        $requested = (string)$this->input('param.lang/s', '');
        if ($requested !== '' && !preg_match('/^[a-z][a-z0-9_-]{1,49}$/iD', trim($requested, '/'))) throw new compat\exception\HttpException(404, '页面不存在');
        $explicit = (bool)$this->input('param.?lang');
        $pathLanguage = '';
        if (!$backend && $this->request) {
            $segments = explode('/', $this->routingPath());
            $mark = $segments[0] === 'm' ? ($segments[1] ?? '') : $segments[0];
            foreach ($allLanguages as $language) if (strcasecmp($language['mark'], $mark) === 0) { $pathLanguage = $language['mark']; break; }
            if ($pathLanguage !== '' && !isset($available[$pathLanguage])) throw new compat\exception\HttpException(404, '页面不存在');
        }
        if (!$explicit) {
            if ($pathLanguage !== '') $requested = $pathLanguage;
            elseif (!$backend && $this->isApiRequest()) {
                $cookie = (string)$this->input('cookie.home_lang/s', $default);
                $requested = isset($available[$cookie]) ? $cookie : $default;
            } else $requested = $default;
        }
        $requested = trim($requested, '/');$selected = '';
        foreach ($available as $mark => $language) if (strcasecmp($mark, $requested) === 0) { $selected = $mark; break; }
        if ($selected === '') throw new compat\exception\HttpException(404, '页面不存在');
        $this->lang = $selected;
        if (!$backend) {
            $this->memo['response_cookies']['home_lang'] = $this->lang;
            $this->memo['response_cookie_options']['home_lang'] = ['path' => $this->cookiePath(), 'samesite' => 'Lax'];
        }
        $values = Db::name('config')->where(['lang' => $this->lang, 'is_del' => 0])->select()->toArray();
        foreach ($values as $row) {
            $value = $row['value'];
            if (is_string($value) && preg_match('/^(a|s|i|b|d|O):|^N;$/', $value)) {
                $decoded = @unserialize($value, ['allowed_classes' => false]);
                if ($decoded !== false || $value === 'b:0;') {
                    $value = $decoded;
                }
            }
            $this->global[$row['name']] = $value;
            $this->config['groups'][$row['inc_type']][$row['name']] = $value;
        }
        // City settings are site-wide and belong to the system main language.
        $citySettings = (new \app\cms\service\CitysiteService())->settings();
        foreach ($citySettings as $name => $value) {
            $this->global[$name] = $value;
            $this->config['groups'][$name === 'web_citysite_open' ? 'web' : 'site'][$name] = $value;
        }
        // The user explicitly excludes membership and commerce workflows.
        $this->global['web_users_switch'] = 0;
        $this->global['shop_open'] = 0;
        $this->global['web_cmspath'] = rtrim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        $configuredHost = $this->global['web_basehost'] ?? '';
        $this->memo['cms.configured_basehost'] = $configuredHost;
        $this->global['web_basehost'] = is_string($configuredHost) && trim($configuredHost) !== '' ? rtrim(trim($configuredHost), '/') : $this->origin();
        $this->device = $this->detectDevice();
        $this->global['lang'] = $this->lang;
        // Original switch_language() only publishes lang_info for a multilingual
        // site. Preserve its homepage/domain/SEO rules and language flag path.
        if ($enabled && count($languages) > 1) {
            $language = $available[$this->lang];
            $root = $this->global['web_cmspath'];
            $url = (string)($language['url'] ?? '');
            if (empty($url)) {
                if ((int)($language['is_home_default'] ?? 0) === 1) {
                    $url = $root . '/';
                } else {
                    $inlet = (int)($this->global['seo_inlet'] ?? 0) === 1 ? '' : '/index.php';
                    $seo = $this->config['groups']['seo']['seo_pseudo'] ?? '';
                    $seo = !empty($seo) ? $seo : ($this->global['seo_pseudo'] ?? 1);
                    $url = (int)$seo === 1
                        ? $this->origin() . $root . $inlet . ($inlet === '' ? '/?' : '?') . http_build_query(['lang' => $this->lang])
                        : $root . $inlet . '/' . $this->lang;
                }
            }
            $this->global['lang_title'] = $language['title'];
            $this->global['lang_url'] = $url;
            $this->global['lang_logo'] = \app\cms\AssetManifest::url($root . '/public/static/common/images/language/' . $this->lang . '.gif');
        }
        // Eyou's source of truth is web_tpl_theme; templets_* are derived URLs,
        // which can be absolute and must never be used as filesystem paths.
        $this->useTheme(ThemeCatalog::configured(isset($this->global['web_tpl_theme']) ? (string)$this->global['web_tpl_theme'] : null));
        $defaults = __DIR__ . '/routing/defaults.php';
        $languageSettings = $policy->settings();
        $this->global['cms_multilingual_enabled'] = $enabled ? 1 : 0;
        $this->global['web_language_switch'] = $languageSettings['web_language_switch'];
        $this->global['language_split'] = $languageSettings['language_split'];
        $this->config['ey_config'] = array_replace(is_file($defaults) ? require $defaults : [], $this->global);
        $this->config['tpcache'] = $this->global;
        $this->config['lang_switch_on'] = $enabled && count($languages) > 1;
        $this->config['language_split'] = (bool)$languageSettings['language_split'];
        $this->config['default_lang'] = $default;
        $captcha = json_decode((string)(Db::name('setting')->where(['name' => 'system_vertify', 'inc_type' => 'system', 'lang' => $main])->value('value') ?: ''), true);
        if (is_array($captcha['captcha'] ?? null)) $this->config['captcha'] = array_replace_recursive($this->config['captcha'], $captcha['captcha']);
        // The adapted upstream registry defines field types, validation rules,
        // URL propagation and query limits used by business tags.
        $registry = __DIR__ . '/support/upstream/global.php';
        if (is_file($registry)) $this->config['global'] = array_replace($this->config['global'], require $registry);
        $this->config['global']['channeltype_list'] = Db::name('channeltype')->column('id', 'nid');
        $this->config['global']['allow_release_channel'] = array_values(Db::name('channeltype')
            ->whereNotIn('nid', ['single', 'guestbook', 'ask'])->column('id'));
        CityContext::load($this);
    }

    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null || $name === '') {
            return $this->config;
        }
        $value = $this->config;
        foreach (explode('.', $name) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }

    public function setConfig(string|array $name, mixed $value = null): void
    {
        if (is_array($name)) {
            $this->config = array_replace_recursive($this->config, $name);
            return;
        }
        $target =& $this->config;
        foreach (explode('.', $name) as $part) {
            if (!isset($target[$part]) || !is_array($target[$part])) {
                $target[$part] = [];
            }
            $target =& $target[$part];
        }
        $target = $value;
    }

    public function input(string $name = '', mixed $default = null, mixed $filter = null): mixed
    {
        [$path, $cast] = array_pad(explode('/', $name, 2), 2, '');
        $parts = explode('.', $path);
        $source = in_array($parts[0], ['param', 'get', 'post', 'server', 'cookie', 'session']) ? array_shift($parts) : 'param';
        $data = match ($source) {
            'get' => $this->request?->get() ?? $this->parameters,
            'post' => $this->request?->post() ?? [],
            'server' => $this->request?->server() ?? $_SERVER,
            'cookie' => $this->request?->cookie() ?? [],
            'session' => $this->request ? $this->request->session()->all() : [],
            default => array_replace($this->request?->all() ?? [], $this->parameters),
        };
        $exists = str_starts_with(implode('.', $parts), '?');
        if ($exists) {
            $parts[0] = ltrim($parts[0], '?');
        }
        foreach (array_filter($parts, static fn($v) => $v !== '') as $part) {
            if (!is_array($data) || !array_key_exists($part, $data)) {
                return $exists ? false : $default;
            }
            $data = $data[$part];
        }
        if ($exists) {
            return true;
        }
        return match (strtolower($cast)) {
            'd', 'i' => (int)$data, 'f' => (float)$data, 'b' => (bool)$data,
            'a' => (array)$data, 's' => is_scalar($data) ? (string)$data : (string)$default,
            default => $data ?? $default,
        };
    }

    public function templateRoot(): string
    {
        return ThemeCatalog::physical(\app\cms\Paths::templates($this->theme)) . '/';
    }

    /** Request-local selection, also used by authenticated theme previews. */
    public function useTheme(string $theme, ?string $device = null): void
    {
        $theme = ThemeCatalog::canonical($theme);
        $device ??= $this->device;
        if (!in_array($device, ['pc', 'mobile'], true)) throw new \InvalidArgumentException('模板终端无效');
        $responsive = ThemeCatalog::isResponsive($theme);
        if ($responsive) $device = 'pc';
        $this->device = $device;
        $this->theme = $theme . '/' . $device;
        $directory = '/template/' . $theme;
        $mainSite = (int)($this->global['web_multi_site'] ?? 0) === 1 ? $this->origin() . ($this->global['web_cmspath'] ?? '') : '';
        $this->global['web_tpl_theme'] = $theme;
        $this->global['web_cmsurl'] = $mainSite;
        $this->global['web_templets_dir'] = $directory;
        $this->global['web_templeturl'] = $mainSite . $directory;
        $this->global['web_templets_pc'] = $mainSite . $directory . '/pc';
        $this->global['web_templets_m'] = $mainSite . $directory . ($responsive ? '/pc' : '/mobile');
        $this->config['groups']['web']['web_tpl_theme'] = $theme;
        $this->config['ey_config'] = array_replace($this->config['ey_config'] ?? [], $this->global);
        $this->config['tpcache'] = $this->global;
    }

    public function variables(): array
    {
        return [
            'eyou' => ['global' => $this->global, 'field' => $this->field, 'site' => $this->siteInfo, 'type' => [], 'users' => []],
            'tid' => $this->tid, 'aid' => $this->aid, 'RootDir' => $this->global['web_cmspath'] ?? '',
            'Request' => ['param' => array_replace($this->parameters, ['m' => $this->module, 'c' => $this->controller, 'a' => $this->action])],
            'seo_title' => $this->field['seo_title'] ?? $this->global['web_title'] ?? '',
            'seo_keywords' => $this->field['seo_keywords'] ?? $this->global['web_keywords'] ?? '',
            'seo_description' => $this->field['seo_description'] ?? $this->global['web_description'] ?? '',
        ];
    }

    public function origin(): string
    {
        if ($this->request) {
            $forwarded = strtolower(trim((string)$this->request->header('x-forwarded-proto', '')));
            // BaoTa's local reverse proxy sends X-Scheme instead. Accept that
            // fallback only from loopback, never from an external HTTP client.
            if ($forwarded === '' && in_array($this->request->getRemoteIp(), ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
                $forwarded = strtolower(trim((string)$this->request->header('x-scheme', '')));
            }
            $scheme = $forwarded === 'https' || $this->request->connection?->transport === 'ssl' ? 'https' : 'http';
            return $scheme . '://' . $this->request->host();
        }
        return \app\cms\config\SiteSettings::origin($this->memo['cms.configured_basehost'] ?? $this->global['web_basehost'] ?? '');
    }

    private function detectDevice(): string
    {
        $force = DeviceSelector::forced($this->input('param.goto'));
        if ($force !== null) return $force;
        if (SitePolicy::isMobileHost($this)) return 'mobile';
        return preg_match('/mobile|android|iphone|ipad|ipod|windows phone/i', (string)$this->request?->header('user-agent', '')) ? 'mobile' : 'pc';
    }

    private function isApiRequest(): bool
    {
        return strtolower((string)$this->input('param.m/s', 'home')) === 'api'
            || strtolower((string)$this->input('param.c/s', '')) === 'ajax'
            || (bool)preg_match('#/(?:index\.php/)?api/#i', (string)$this->request?->path())
            || (bool)preg_match('#/(?:index\.php/)?home/ajax(?:/|$)#i', (string)$this->request?->path())
            || strtolower((string)$this->request?->header('x-requested-with', '')) === 'xmlhttprequest';
    }

    private function cookiePath(): string
    {
        $base = trim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        return $base === '' ? '/' : '/' . $base . '/';
    }
}
