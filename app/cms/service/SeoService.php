<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/**
 * EyouCMS enterprise URL/SEO settings, stored under their original config keys.
 * Adapted semantics: admin/Seo and common/logic/BuildhtmlLogic::seo_handle.
 * 易优CMS 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * https://www.eyoucms.com — retain the upstream license for commercial use.
 */
class SeoService
{
    public const DEFAULTS = [
        'seo_pseudo' => '1', 'seo_dynamic_format' => '1', 'seo_rewrite_format' => '1',
        'seo_rewrite_view_format' => '1', 'seo_inlet' => '0', 'seo_force_inlet' => '0',
        'seo_html_arcdir' => '', 'seo_html_showdir' => '1', 'seo_html_listname' => '2', 'seo_html_pagename' => '2',
        'seo_html_templet' => 'index.htm', 'seo_html_position' => 'index.html', 'seo_showmod' => '0',
        'seo_liststitle_format' => '2', 'seo_viewtitle_format' => '2', 'seo_title_symbol' => '_',
        'seo_uphtml_after_home' => '1', 'seo_uphtml_after_channel' => '1', 'seo_uphtml_after_pernext' => '1',
        'seo_uphtml_editafter_home' => '1', 'seo_uphtml_editafter_channel' => '1', 'seo_uphtml_editafter_pernext' => '1',
        'seo_uphtml_after_home13' => '1', 'seo_uphtml_after_channel13' => '1', 'seo_uphtml_after_pernext13' => '1',
        'seo_maxpagesize' => '50', 'seo_upnext' => '1', 'seo_pagesize' => '20',
        'seo_start_time' => '', 'seo_startid2' => '', 'seo_startid' => '0', 'seo_endid' => '0',
        'seo_native_error' => '0',
    ];
    private const FLAGS = [
        'seo_uphtml_after_home', 'seo_uphtml_after_channel', 'seo_uphtml_after_pernext',
        'seo_uphtml_editafter_home', 'seo_uphtml_editafter_channel', 'seo_uphtml_editafter_pernext',
        'seo_uphtml_after_home13', 'seo_uphtml_after_channel13', 'seo_uphtml_after_pernext13',
    ];
    private const ENUMS = [
        'seo_pseudo' => ['1', '2', '3'], 'seo_dynamic_format' => ['1', '2'],
        'seo_rewrite_format' => ['1', '2', '3', '4'], 'seo_rewrite_view_format' => ['1', '3', '4'],
        'seo_html_showdir' => ['0', '1'], 'seo_html_listname' => ['1', '2', '3', '4'], 'seo_html_pagename' => ['1', '2', '3', '4'],
        'seo_liststitle_format' => ['1', '2', '3', '4'], 'seo_viewtitle_format' => ['1', '2', '3'],
        'seo_inlet' => ['0', '1'], 'seo_force_inlet' => ['0', '1'], 'seo_upnext' => ['0', '1'], 'seo_showmod' => ['0', '1'],
        'seo_native_error' => ['0', '1'],
    ];
    private const POLICY = ['system_langnum', 'system_home_default_lang', 'web_citysite_open', 'web_cmsmode', 'web_language_switch', 'web_tpl_theme'];

    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function read(array $input = []): array
    {
        $scope = $this->scope($input);
        $records = $this->configuration(array_column($scope['languages'], 'mark'));
        $settings = $this->values($records[$scope['lang']] ?? []);
        $this->assertMainLanguage($scope, $settings);
        return $this->presentation($scope, $records, $settings);
    }

    public function save(array $input): array
    {
        // A stale same-mark language or a replacement primary language cannot
        // inherit permission to alter the new language's configuration.
        $selected = $this->scope($input);
        return $this->configurationChange(fn(): array => $this->repository->connection()->transaction(function () use ($selected, $input): array {
            $scope = $this->scope(['lang' => $selected['lang']], true);
            if ($scope['id'] !== $selected['id'] || $scope['main_id'] !== $selected['main_id']) throw new InvalidArgumentException('语言已删除或重新创建，请刷新后重试');
            $records = $this->configuration(array_column($scope['languages'], 'mark'), true);
            $settings = $this->values($records[$scope['lang']] ?? []);
            $this->assertMainLanguage($scope, $settings);
            $data = $this->normalize($input, $settings, $this->reservedDirectories(true), $scope['lang']);
            $multilingual = (new LanguagePolicy($this->repository))->enabled() && count((new LanguagePolicy($this->repository))->all()) > 1;
            $targets = $multilingual ? $scope['languages'] : [$scope['selected']];
            (new LanguageAccessService($this->repository))->assertLanguages(array_column($targets,'mark'));
            $mode = $data['seo_pseudo'];
            $secondary = $this->enum('seo_pseudo_lang', $input['seo_pseudo_lang'] ?? '1', ['1', '3']);
            foreach ($targets as $index => $language) {
                $lang = $language['mark']; $localized = $data;
                unset($localized['seo_native_error']);
                // Original SEO static mode is limited to the first registered
                // language, including when another language is disabled.
                if ($multilingual && $mode === '2' && $index > 0) $localized['seo_pseudo'] = $secondary;
                foreach ($localized as $name => $value) {
                    $row = $records[$lang][$name] ?? null;
                    if ($row && (string)$this->scalar($row['value']) === $value && $row['inc_type'] === 'seo') continue;
                    $payload = ['value' => $value, 'inc_type' => 'seo', 'is_del' => 0, 'update_time' => time()];
                    if ($row) $this->repository->table('config')->where('id', $row['id'])->update($payload);
                    else $this->repository->table('config')->insert($payload + ['name' => $name, 'lang' => $lang, 'desc' => 'SEO设置']);
                }
            }
            // Public exception display is shared even when the SEO language
            // synchronization policy is disabled. Omission preserves it.
            if (array_key_exists('seo_native_error', $data)) foreach ($scope['languages'] as $language) {
                $lang = $language['mark']; $row = $records[$lang]['seo_native_error'] ?? null;
                if ($row && (string)$this->scalar($row['value']) === $data['seo_native_error'] && $row['inc_type'] === 'seo') continue;
                $payload = ['value' => $data['seo_native_error'], 'inc_type' => 'seo', 'is_del' => 0, 'update_time' => time()];
                if ($row) $this->repository->table('config')->where('id', $row['id'])->update($payload);
                else $this->repository->table('config')->insert($payload + ['name' => 'seo_native_error', 'lang' => $lang, 'desc' => '前台错误显示']);
            }
            // Invalidation failures abort the configuration transaction.
            $this->publish();
            $updated = $this->configuration(array_column($scope['languages'], 'mark'));
            return $this->presentation($scope, $updated, $this->values($updated[$scope['lang']] ?? []))
                + ['affected_languages' => array_column($targets, 'mark')];
        }));
    }

    private function scope(array $input, bool $lock = false): array
    {
        $query = $this->repository->table('language')->order('id asc');
        if ($lock) $query->lock(true);
        $languages = $query->select()->toArray();
        if (!$languages) throw new InvalidArgumentException('请先配置网站语言');
        $lang = $input['lang'] ?? $languages[0]['mark'];
        if (!is_string($lang) || !preg_match('/^[A-Za-z0-9_-]{2,50}$/D', $lang)) throw new InvalidArgumentException('语言不存在');
        foreach ($languages as $language) if ($language['mark'] === $lang) return [
            'lang' => $lang, 'id' => (int)$language['id'], 'selected' => $language, 'languages' => $languages,
            'main' => $languages[0]['mark'], 'main_id' => (int)$languages[0]['id'],
        ];
        throw new InvalidArgumentException('语言不存在');
    }

    private function configuration(array $languages, bool $lock = false): array
    {
        $query = $this->repository->table('config')->whereIn('lang', $languages)->where('is_del', 0)
            ->whereIn('name', array_merge(array_keys(self::DEFAULTS), self::POLICY))->order('id asc');
        if ($lock) $query->lock(true);
        $records = [];
        foreach ($query->select()->toArray() as $row) {
            if (isset($records[$row['lang']][$row['name']])) throw new InvalidArgumentException('语言【' . $row['lang'] . '】存在重复配置：' . $row['name']);
            $records[$row['lang']][$row['name']] = $row;
        }
        return $records;
    }

    private function values(array $records): array
    {
        $values = self::DEFAULTS;
        foreach ($records as $name => $row) $values[$name] = $this->scalar($row['value']);
        // Historical data may store only the combined rewrite format.
        if (!isset($records['seo_rewrite_view_format']) && in_array($values['seo_rewrite_format'], ['3', '4'], true)) $values['seo_rewrite_view_format'] = $values['seo_rewrite_format'];
        return $values;
    }

    private function scalar(mixed $value): string
    {
        if (is_string($value) && preg_match('/^(a|s|i|b|d|O):|^N;$/', $value)) {
            $decoded = @unserialize($value, ['allowed_classes' => false]);
            if ($decoded !== false || $value === 'b:0;') $value = $decoded;
        }
        if (!is_scalar($value) && $value !== null) throw new InvalidArgumentException('SEO配置值必须是文本或数字');
        return (string)$value;
    }

    private function assertMainLanguage(array $scope, array $settings): void
    {
        if ((new LanguagePolicy($this->repository))->enabled() && count((new LanguagePolicy($this->repository))->all()) > 1 && $scope['lang'] !== $scope['main']) throw new InvalidArgumentException('当前语言没有此功能，请切换到【' . $scope['languages'][0]['title'] . '】语言');
    }

    private function presentation(array $scope, array $records, array $settings): array
    {
        $settings['seo_native_error'] = $this->values($records[$scope['main']] ?? [])['seo_native_error'] === '1' ? '1' : '0';
        $home = (new LanguagePolicy($this->repository))->enabled() ? ($settings['system_home_default_lang'] ?? $scope['main']) : $scope['main'];
        $reason = $home !== $scope['main'] ? '前台默认语言不是系统主语言时，不允许使用静态模式' : (!empty($settings['web_citysite_open']) ? '已开启城市分站，静态页面功能不可用' : '');
        $secondary = $scope['languages'][1]['mark'] ?? null;
        $secondaryMode = $secondary ? $this->values($records[$secondary] ?? [])['seo_pseudo'] : '1';
        return ['lang' => $scope['lang'], 'main_lang' => $scope['main'], 'languages' => $scope['languages'],
            'multilingual' => (new LanguagePolicy($this->repository))->enabled() && count((new LanguagePolicy($this->repository))->all()) > 1,
            'home_default_lang' => $home, 'static_allowed' => $reason === '', 'static_unavailable_reason' => $reason,
            'operation_mode' => \app\cms\cache\CachePolicy::isOperational((int)($settings['web_cmsmode'] ?? 0)),
            'config' => array_intersect_key($settings, self::DEFAULTS) + ['seo_pseudo_lang' => $secondaryMode === '3' ? '3' : '1'],
            'reserved_directories' => $this->reservedDirectories()];
    }

    private function normalize(array $input, array $settings, array $reserved, string $lang): array
    {
        foreach ($input as $name => $value) if (str_starts_with((string)$name, 'seo_') && !array_key_exists($name, self::DEFAULTS)
            && !in_array($name, ['seo_pseudo_lang', 'seo_html_arcdir_limit'], true)) throw new InvalidArgumentException('不支持的SEO配置：' . $name);
        if (!array_key_exists('seo_pseudo', $input)) throw new InvalidArgumentException('请选择URL模式');
        $data = [];
        foreach (array_intersect_key($input, self::DEFAULTS) as $name => $value) {
            if (!is_string($value) && !is_int($value)) throw new InvalidArgumentException('SEO配置格式不正确：' . $name);
            $value = trim((string)$value);
            if (strlen($value) > 512 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) throw new InvalidArgumentException('SEO配置值过长或包含无效字符');
            if (isset(self::ENUMS[$name])) $value = $this->enum($name, $value, self::ENUMS[$name]);
            if (in_array($name, self::FLAGS, true)) $value = $this->enum($name, $value, ['0', '1']);
            if (in_array($name, ['seo_pagesize', 'seo_maxpagesize'], true) && (!ctype_digit($value) || (int)$value < 1 || (int)$value > 10000)) throw new InvalidArgumentException('每批文件数须为1至10000');
            if (in_array($name, ['seo_startid', 'seo_endid', 'seo_startid2'], true) && $value !== '' && (!ctype_digit($value) || strlen($value) > 10 || (int)$value > 2147483647)) throw new InvalidArgumentException('文档ID范围格式错误');
            if ($name === 'seo_start_time' && $value !== '') {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) throw new InvalidArgumentException('起始日期请使用YYYY-MM-DD格式');
            }
            if ($name === 'seo_title_symbol' && mb_strlen($value) > 20) throw new InvalidArgumentException('标题连接符最多20个字符');
            if ($name === 'seo_html_templet') $this->validateHomeTemplate($value, $settings, $lang, false);
            if ($name === 'seo_html_position') {
                // This exact value is the original form default, relative to
                // its old controller directory. It means the public root.
                if ($value === '../index.html') $value = 'index.html';
                $value = ltrim($value, '/');
                if ($value === '') $value = 'index.html';
                if (!preg_match('#^[A-Za-z0-9_-][A-Za-z0-9_.-]*(?:/[A-Za-z0-9_-][A-Za-z0-9_.-]*)*\.html?$#D', $value)
                    || str_contains($value, '..')) throw new InvalidArgumentException('首页保存位置必须是站点内的HTML文件路径');
                if (str_contains($value, '/') && in_array(strtolower(explode('/', $value)[0]), $reserved, true)) throw new InvalidArgumentException('首页保存位置与系统目录、栏目或语言标识冲突');
            }
            if ($name === 'seo_html_arcdir') {
                $relative = trim($value, '/');
                if ($relative !== '' && !preg_match('#^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$#D', $relative)) throw new InvalidArgumentException('页面保存路径的格式错误');
                if ($relative !== '' && in_array(strtolower(explode('/', $relative)[0]), $reserved, true)) throw new InvalidArgumentException('页面保存目录与系统目录、栏目或语言标识冲突');
                $value = $relative === '' ? '' : '/' . $relative;
            }
            $data[$name] = $value;
        }
        // HTML form checkboxes absent from POST mean off in upstream handle().
        foreach (self::FLAGS as $name) $data[$name] ??= '0';
        $mode = $data['seo_pseudo'];
        if ($mode === '2' && (((new LanguagePolicy($this->repository))->enabled() && ($settings['system_home_default_lang'] ?? (new LanguagePolicy($this->repository))->main()) !== (new LanguagePolicy($this->repository))->main()) || !empty($settings['web_citysite_open']))) throw new InvalidArgumentException('前台默认语言不是系统主语言或已开启城市分站，不能使用静态模式');
        if ($mode === '3') {
            $format = $data['seo_rewrite_format'] ?? $settings['seo_rewrite_format'];
            if (in_array($format, ['1', '3', '4'], true)) $data['seo_rewrite_format'] = $data['seo_rewrite_view_format'] ?? '1';
        }
        if (isset($data['seo_force_inlet']) && $data['seo_force_inlet'] !== $settings['seo_force_inlet']) $data['seo_inlet'] = $data['seo_force_inlet'];
        $range = array_replace($settings, $data);
        if ((int)$range['seo_endid'] > 0 && (int)$range['seo_startid'] > (int)$range['seo_endid']) throw new InvalidArgumentException('结束文档ID不能小于起始ID');
        if ($range['seo_pseudo'] === '2' && $range['seo_showmod'] === '1') $this->validateHomeTemplate($range['seo_html_templet'], $settings, $lang, true);
        return $data;
    }

    private function enum(string $name, mixed $value, array $allowed): string
    {
        if ((!is_string($value) && !is_int($value)) || !in_array((string)$value, $allowed, true)) throw new InvalidArgumentException('SEO配置选项无效：' . $name);
        return (string)$value;
    }

    private function validateHomeTemplate(string $value, array $settings, string $lang, bool $requireFile): void
    {
        if (!preg_match('#^[A-Za-z0-9_-][A-Za-z0-9_.-]*(?:/[A-Za-z0-9_-][A-Za-z0-9_.-]*)*\.htm$#D', $value)
            || str_contains($value, '..')) throw new InvalidArgumentException('首页模板必须是当前主题内的HTM文件');
        // Incomplete themes may be activated intentionally. Unrelated dynamic
        // SEO settings must remain editable when no PC homepage is available.
        if (!$requireFile) return;
        clearstatcache(true);
        $root = \app\cms\ThemeCatalog::root(\app\cms\ThemeCatalog::configured($settings['web_tpl_theme'] ?? null), 'pc');
        $localized = substr($value, 0, -4) . '_' . $lang . '.htm';
        foreach (array_unique([$localized, $value]) as $relative) {
            $path = $root;
            foreach (explode('/', $relative) as $segment) {
                $path .= '/' . $segment;
                if (is_link($path)) continue 2;
            }
            $actual = realpath($path);
            if ($actual && str_starts_with($actual, $root . DIRECTORY_SEPARATOR) && is_file($actual) && is_readable($actual)) return;
        }
        throw new InvalidArgumentException('当前主题PC首页模板不存在或不可读取');
    }

    private function reservedDirectories(bool $lock = false): array
    {
        $reserved = ['application', 'core', 'data', 'extend', 'install', 'public', 'plugins', 'uploads', 'upload', 'template', 'vendor', 'weapp',
            'tags', 'search', 'user', 'users', 'member', 'reg', 'centre', 'login', 'cart', 'app', 'config', 'plugin', 'process', 'runtime', 'support', 'tests', 'resources', 'storage', 'node_modules', 'admin', 'api', 'm'];
        $query = $this->repository->table('arctype')->where('parent_id', 0);
        if ($lock) $query->lock(true);
        foreach ($query->select()->toArray() as $row) $reserved[] = strtolower((string)$row['dirname']);
        foreach (['language', 'language_mark'] as $table) foreach ($this->repository->table($table)->select()->toArray() as $row) $reserved[] = strtolower((string)$row['mark']);
        return array_values(array_unique($reserved));
    }

    /** Keep filesystem compensation outside the complete configuration transaction. */
    protected function configurationChange(callable $operation): array
    {
        return (new PublicStaticPublisher())->configurationChange($operation);
    }

    protected function publish(): void { \app\cms\compat\Cache::clear(); }
}
