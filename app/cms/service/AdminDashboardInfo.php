<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use app\cms\ThemeCatalog;
use Composer\InstalledVersions;
use support\Request;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

/** Read-only dashboard facts. Deliberately excludes paths, hosts and credentials. */
final class AdminDashboardInfo
{
    /** @return array{system: list<array>, server: list<array>} */
    public function collect(Request $request, CmsRepository $repository): array
    {
        $site = $this->site($request, $repository);
        $webman = $this->version('workerman/webman-framework');
        $workerman = $this->version('workerman/workerman', Worker::VERSION);
        $database = '暂不可用';
        try {
            $rows = $repository->connection()->query('SELECT VERSION() AS version');
            $database = $this->text($rows[0]['version'] ?? '', '暂不可用');
        } catch (\Throwable) {}

        $connectionLimit = $request->connection?->maxPackageSize;
        $packageLimit = $connectionLimit ?? TcpConnection::$defaultMaxPackageSize;
        $packageNote = $connectionLimit !== null
            ? '当前连接的HTTP整包上限（含请求头与请求体）；代理、业务校验可能更小。'
            : 'Workerman运行时默认整包上限（含请求头与请求体）；连接或代理可能另有限制。';
        $memoryLimit = (string)ini_get('memory_limit');
        $executionTime = (string)ini_get('max_execution_time');

        return [
            'system' => [
                $this->row('site_name', '站点名称', $site['name']),
                $this->row('language', '当前语言', $site['language'], $site['language_note']),
                $this->row('theme', '当前主题', $site['theme']),
                $this->row('url_mode', 'URL模式', $site['url_mode']),
                $this->row('webman', 'Webman版本', $webman),
                $this->row('workerman', 'Workerman版本', $workerman),
                $this->row('admin', '后台组件版本', \app\cms\admin\support\BackendVersion::version(), \app\cms\admin\support\BackendVersion::description()),
            ],
            'server' => [
                $this->row('os', '操作系统', PHP_OS_FAMILY . ' / ' . php_uname('r') . ' / ' . php_uname('m')),
                $this->row('http_server', 'HTTP服务', 'Workerman ' . $workerman),
                $this->row('php', 'PHP版本', PHP_VERSION . ' / ' . PHP_SAPI),
                $this->row('database', 'MySQL版本', $database),
                $this->row('http_package_limit', 'HTTP请求包上限', $this->bytes($packageLimit), $packageNote),
                $this->row('php_memory_limit', 'PHP内存限制', $memoryLimit === '-1' ? '不限（-1）' : $this->text($memoryLimit, '未配置'), '当前PHP进程限制。'),
                $this->row('php_memory_usage', '当前进程内存', $this->bytes(memory_get_usage(true)), '当前常驻worker已分配内存，非服务器总内存。'),
                $this->row('php_memory_peak', '进程内存峰值', $this->bytes(memory_get_peak_usage(true)), '当前进程已分配内存峰值。'),
                $this->row('php_upload_max', 'PHP上传配置', $this->text(ini_get('upload_max_filesize'), '未配置'), 'PHP配置参考，不作为Webman生效上限。'),
                $this->row('php_post_max', 'PHP POST配置', $this->text(ini_get('post_max_size'), '未配置'), 'PHP配置参考，不作为Webman生效上限。'),
                $this->row('php_execution_time', 'PHP执行时间', $executionTime === '0' ? '不限（0秒）' : $this->text($executionTime, '未配置') . '秒', 'PHP CLI配置；应用、代理的独立超时策略仍生效。'),
                $this->row('gd', 'GD图像支持', $this->gd()),
                $this->row('zip', 'Zip支持', $this->extension('zip')),
                $this->row('zlib', 'Zlib支持', extension_loaded('zlib') ? (defined('ZLIB_VERSION') ? (string)ZLIB_VERSION : $this->extension('zlib')) : '未安装'),
            ],
        ];
    }

    private function site(Request $request, CmsRepository $repository): array
    {
        $unavailable = ['name'=>'暂不可用', 'language'=>'暂不可用', 'language_note'=>'站点配置读取失败。', 'theme'=>'暂不可用', 'url_mode'=>'暂不可用'];
        try {
            $languages = $repository->table('language')->order('id asc')->select()->toArray();
            if (!$languages) return $unavailable;
            $byMark = array_column($languages, null, 'mark');
            $main = $languages[0];
            $candidate = \app\cms\CmsContext::current()->lang;
            $language = is_string($candidate) && isset($byMark[$candidate]) ? $byMark[$candidate] : $main;
            $records = $repository->table('config')->where(['lang'=>$language['mark'], 'is_del'=>0])
                ->whereIn('name', ['web_name', 'web_tpl_theme', 'seo_pseudo'])->order('id asc')->select()->toArray();
            $values = []; $duplicates = [];
            foreach ($records as $record) {
                if (array_key_exists($record['name'], $values)) $duplicates[$record['name']] = true;
                $values[$record['name']] = $this->scalar($record['value']);
            }
            $theme = '暂不可用';
            if (!isset($duplicates['web_tpl_theme'])) {
                try { $theme = ThemeCatalog::configured($values['web_tpl_theme'] ?? null); } catch (\Throwable) {}
            }
            $modes = ['1'=>'动态URL', '2'=>'静态页面', '3'=>'伪静态化'];
            return [
                'name'=>isset($duplicates['web_name']) ? '配置重复' : $this->text($values['web_name'] ?? '', '未设置'),
                'language'=>$this->text($language['title'] ?? '', (string)$language['mark']) . '（' . $language['mark'] . '）',
                'language_note'=>'主体语言：' . $this->text($main['title'] ?? '', (string)$main['mark']) . '（' . $main['mark'] . '）',
                'theme'=>$theme,
                'url_mode'=>isset($duplicates['seo_pseudo']) ? '配置重复' : ($modes[$values['seo_pseudo'] ?? '1'] ?? '配置无效'),
            ];
        } catch (\Throwable) { return $unavailable; }
    }

    private function scalar(mixed $value): ?string
    {
        if (is_string($value) && preg_match('/^(a|s|i|b|d|O):|^N;$/', $value)) {
            $decoded = @unserialize($value, ['allowed_classes'=>false]);
            if ($decoded !== false || $value === 'b:0;') $value = $decoded;
        }
        return is_scalar($value) || $value === null ? (string)$value : null;
    }

    private function version(string $package, ?string $fallback = null): string
    {
        try {
            if (InstalledVersions::isInstalled($package)) return $this->text(InstalledVersions::getPrettyVersion($package), $fallback ?? '未知');
        } catch (\Throwable) {}
        return $fallback ?? '未知';
    }

    private function gd(): string
    {
        if (!extension_loaded('gd')) return '未安装';
        try { return $this->text(gd_info()['GD Version'] ?? '', '已启用'); }
        catch (\Throwable) { return '已启用'; }
    }

    private function extension(string $name): string
    {
        return extension_loaded($name) ? $this->text(phpversion($name), '已启用') : '未安装';
    }

    private function text(mixed $value, string $fallback): string
    {
        if (!is_scalar($value)) return $fallback;
        $text = trim(preg_replace('/[\x00-\x1F\x7F]/', '', (string)$value) ?? '');
        return $text !== '' ? mb_substr($text, 0, 200) : $fallback;
    }

    private function bytes(int $bytes): string
    {
        $value = (float)max(0, $bytes); $unit = 'B';
        foreach (['B', 'KiB', 'MiB', 'GiB', 'TiB'] as $candidate) {
            $unit = $candidate;
            if ($value < 1024 || $candidate === 'TiB') break;
            $value /= 1024;
        }
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . ' ' . $unit;
    }

    private function row(string $key, string $label, string $value, string $note = ''): array
    {
        return ['key'=>$key, 'label'=>$label, 'value'=>$value, 'note'=>$note];
    }
}
