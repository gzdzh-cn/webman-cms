<?php
/**
 * 易优CMS FilemanagerLogic HTM normalization adaptation.
 * ============================================================================
 * 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.eyoucms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 * Author: 小虎哥 <1105415366@qq.com>
 * Date: 2018-4-3
 */
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use app\cms\ThemeCatalog;
use InvalidArgumentException;
use RuntimeException;

/**
 * Current-language theme source management, following Eyou Filemanager paths.
 * HTM normalization adapted from EyouCMS FilemanagerLogic (海南赞赞网络科技有限公司,
 * author 小虎哥, 2018-4-3); upstream copyright retained.
 */
class TemplateFileService
{
    private const SOURCE_EXTENSIONS = ['htm', 'js', 'css', 'txt'];
    private const IMAGE_TYPES = ['gif' => 'image/gif', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'bmp' => 'image/bmp', 'ico' => 'image/vnd.microsoft.icon', 'avif' => 'image/avif'];
    public const MAX_SOURCE_BYTES = 2097152;
    public const MAX_IMAGE_BYTES = 10485760;

    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    private function validateInput(array $input): void
    {
        foreach (['lang', 'theme', 'path', 'activepath', 'filename', 'hash', 'content'] as $key) if (array_key_exists($key, $input) && !is_string($input[$key])) throw new InvalidArgumentException('模板文件参数必须为字符串');
    }

    protected function scope(string $lang): array
    {
        $languages = $this->repository->table('language')->field('mark,title')->order('sort_order,id')->select()->toArray();
        if (!in_array($lang, array_column($languages, 'mark'), true)) throw new InvalidArgumentException('语言不存在');
        $record = $this->repository->table('config')->where(['name' => 'web_tpl_theme', 'lang' => $lang, 'is_del' => 0])->value('value');
        $theme = ThemeCatalog::configured($record === null ? null : (string)$record);
        $root = realpath(base_path('template'));
        if ($root === false || is_link(base_path('template'))) throw new InvalidArgumentException('模板根目录不存在或无效');
        $directory = $this->walk($root, $theme, true);
        return ['root' => $directory, 'theme' => $theme, 'lang' => $lang, 'languages' => $languages, 'legacy' => false, 'prefix' => '/template/' . $theme];
    }

    private function segments(string $path): array
    {
        if ($path === '') return [];
        $segments = explode('/', $path);
        foreach ($segments as $part) if (!preg_match('/^[A-Za-z0-9_-][A-Za-z0-9_.-]{0,254}$/D', $part) || str_contains($part, '..')) throw new InvalidArgumentException('模板路径格式不正确');
        return $segments;
    }

    private function walk(string $root, string $relative, bool $directory): string
    {
        $current = $root;
        foreach ($this->segments($relative) as $segment) {
            $current .= DIRECTORY_SEPARATOR . $segment;
            clearstatcache(true, $current);
            if (is_link($current)) throw new InvalidArgumentException('禁止访问符号链接');
        }
        $real = realpath($current);
        if ($real === false || ($real !== $root && !str_starts_with($real, $root . DIRECTORY_SEPARATOR)) || ($directory ? !is_dir($real) : !is_file($real))) throw new InvalidArgumentException('模板路径不存在或越界');
        return $real;
    }

    private function relative(array $input, array $scope): string
    {
        if (isset($input['theme']) && ThemeCatalog::canonical($input['theme']) !== $scope['theme']) throw new InvalidArgumentException('网站模板已切换，请刷新后重新打开文件');
        if (array_key_exists('activepath', $input)) {
            $active = str_replace(':', '/', (string)$input['activepath']);
            $active = preg_replace('#^/template/official(?=/|$)#', '/template/default', $active);
            $active = preg_replace('#^/template/(pc|mobile)(?=/|$)#', '/template/default/$1', $active);
            $prefix = $scope['prefix'];
            if ($active === '') $active = $prefix;
            if ($active === $prefix || $active === $prefix . '/') $path = '';
            elseif (str_starts_with($active, $prefix . '/')) $path = substr($active, strlen($prefix) + 1);
            else throw new InvalidArgumentException('模板路径不属于当前语言主题');
            $path = rtrim($path, '/');
            if (isset($input['filename'])) $path .= ($path === '' ? '' : '/') . (string)$input['filename'];
        } else $path = (string)($input['path'] ?? '');
        $this->segments($path);
        return $path;
    }

    private function kind(string $name): ?string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($ext, self::SOURCE_EXTENSIONS, true)) return 'source';
        if (isset(self::IMAGE_TYPES[$ext])) return 'image';
        if (in_array($ext, ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phar', 'sql', 'db', 'sqlite', 'sqlite3', 'ini', 'env'], true)) return null;
        return 'file'; // Other static files have metadata only; no source read/write action.
    }

    public function listing(array $input): array
    {
        $this->validateInput($input);
        $scope = $this->scope((string)($input['lang'] ?? 'cn')); $path = $this->relative($input, $scope); $directory = $this->walk($scope['root'], $path, true); $items = [];
        foreach (scandir($directory) ?: [] as $name) {
            if ($name === '.' || $name === '..') continue;
            try { $this->segments($name); } catch (InvalidArgumentException) { continue; }
            $file = $directory . '/' . $name;
            if (is_link($file)) continue;
            $kind = is_dir($file) ? 'directory' : $this->kind($name);
            if ($kind === null || (!is_dir($file) && !is_file($file))) continue;
            $relative = ($path === '' ? '' : $path . '/') . $name;
            $items[] = ['name' => $name, 'path' => $relative, 'kind' => $kind, 'bytes' => is_file($file) ? filesize($file) : null, 'modified' => filemtime($file), 'hash' => $kind === 'image' ? hash_file('sha256', $file) : null, 'url' => $kind === 'image' ? $scope['prefix'] . '/' . $relative : null];
        }
        usort($items, static fn($a, $b) => (($a['kind'] !== 'directory') <=> ($b['kind'] !== 'directory')) ?: strnatcasecmp($a['name'], $b['name']));
        return ['lang' => $scope['lang'], 'languages' => $scope['languages'], 'theme' => $scope['theme'], 'activepath' => $scope['prefix'] . ($path === '' ? '' : '/' . $path), 'path' => $path, 'items' => $items, 'source_extensions' => self::SOURCE_EXTENSIONS];
    }

    public function read(array $input): array
    {
        $this->validateInput($input);
        $scope = $this->scope((string)($input['lang'] ?? 'cn')); $path = $this->relative($input, $scope);
        if ($this->kind($path) !== 'source') throw new InvalidArgumentException('仅支持htm/js/css/txt源文件');
        $file = $this->walk($scope['root'], $path, false);
        if (filesize($file) > self::MAX_SOURCE_BYTES) throw new InvalidArgumentException('源文件超过2MiB限制');
        $content = file_get_contents($file);
        if ($content === false || strlen($content) > self::MAX_SOURCE_BYTES || !mb_check_encoding($content, 'UTF-8')) throw new InvalidArgumentException('源文件必须为2MiB以内的UTF-8文本');
        return ['path' => $path, 'content' => $content, 'hash' => hash('sha256', $content), 'lang' => $scope['lang'], 'theme' => $scope['theme']];
    }

    private function source(string $content, string $extension): string
    {
        if (strlen($content) > self::MAX_SOURCE_BYTES || !mb_check_encoding($content, 'UTF-8') || str_contains($content, "\0")) throw new InvalidArgumentException('源文件必须为2MiB以内的UTF-8文本');
        if ($extension === 'htm') {
            // Upstream decodes HTML entities exactly once for HTM only.
            $content = htmlspecialchars_decode($content, ENT_QUOTES);
        }
        if (preg_match('/<\?(?!xml\b)/i', $content) || preg_match('/<[^>]*\blanguage\s*=\s*(?:"php"|\'php\'|php(?=[\s>]))/i', $content)) throw new InvalidArgumentException('源文件不允许包含PHP起始标签');
        if ($extension === 'htm') \app\cms\template\TemplateSourcePolicy::validate($content, \app\cms\template\TemplateMode::current());
        return $content;
    }

    public function save(array $input, bool $create = false): array
    {
        $this->validateInput($input);
        return $this->mutate($input, (string)($input['content'] ?? ''), 'source', $create);
    }

    public function replaceImage(array $input, string $bytes): array
    {
        throw new InvalidArgumentException('模板目录不支持图片上传或替换');
    }

    protected function publish(): void
    {
        \app\cms\compat\Cache::clear();
        // Set only after publication succeeds; the outer admin middleware can
        // preserve this operation's file/cache compensation boundary.
        \app\cms\CmsContext::current()->memo['cms.template_file_cache_published'] = true;
    }

    private function temporary(string $directory, string $bytes, int $mode): string
    {
        $path = $directory . '/.cms-' . bin2hex(random_bytes(16)); $handle = @fopen($path, 'xb');
        if (!$handle) throw new RuntimeException('无法创建模板临时文件');
        try {
            $offset = 0; $length = strlen($bytes);
            while ($offset < $length) { $written = fwrite($handle, substr($bytes, $offset)); if ($written === false || $written === 0) throw new RuntimeException('写入模板文件失败'); $offset += $written; }
            if (!fflush($handle) || (function_exists('fsync') && !fsync($handle)) || !chmod($path, $mode)) throw new RuntimeException('保存模板文件失败');
        } catch (\Throwable $error) { fclose($handle); @unlink($path); throw $error; }
        fclose($handle); return $path;
    }

    private function mutate(array $input, string $bytes, string $kind, bool $create, string $mime = ''): array
    {
        $scope = $this->scope((string)($input['lang'] ?? 'cn')); $path = $this->relative($input, $scope);
        if ($this->kind($path) !== $kind) throw new InvalidArgumentException('不支持的模板文件类型');
        // Normalize by the resolved target, never by a separate request filename.
        if ($kind === 'source') $bytes = $this->source($bytes, strtolower(pathinfo($path, PATHINFO_EXTENSION)));
        if ($kind === 'image') {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)); $expected = self::IMAGE_TYPES[$ext];
            if ($mime !== $expected && !($ext === 'ico' && $mime === 'image/x-icon') && !($ext === 'bmp' && $mime === 'image/x-ms-bmp')) throw new InvalidArgumentException('图片格式必须与原文件扩展名一致');
        }
        $parent = dirname($path); if ($parent === '.') $parent = '';
        $directory = $this->walk($scope['root'], $parent, true); $file = $directory . '/' . basename($path);
        $lockDirectory = runtime_path('locks/template-files');
        \app\cms\Paths::ensureDirectory($lockDirectory, 0700);
        $lockPath = $lockDirectory . '/' . hash('sha256', $file) . '.lock';
        clearstatcache(true, $lockPath);
        $existingLock = @lstat($lockPath);
        if (is_link($lockPath) || ($existingLock && ((($existingLock['mode'] & 0170000) !== 0100000) || $existingLock['nlink'] !== 1))) throw new RuntimeException('模板锁文件无效');
        $lock = @fopen($lockPath, 'c');
        $opened = $lock ? fstat($lock) : false; $actual = @lstat($lockPath);
        if (!$lock || !$opened || !$actual || is_link($lockPath) || $opened['nlink'] !== 1 || $opened['ino'] !== $actual['ino'] || $opened['dev'] !== $actual['dev'] || !flock($lock, LOCK_EX)) { if ($lock) fclose($lock); throw new RuntimeException('模板文件加锁失败'); }
        @chmod($lockPath, 0600); $temporary = null; $rollback = null;
        try {
            if ($this->walk($scope['root'], $parent, true) !== $directory || is_link($file)) throw new InvalidArgumentException('模板路径已变更');
            clearstatcache(true, $file); $exists = file_exists($file);
            if ($create && $exists) throw new InvalidArgumentException('同名模板文件已存在');
            if (!$create && (!$exists || !is_file($file))) throw new InvalidArgumentException('模板文件不存在');
            if ($exists && (($stat = lstat($file)) === false || $stat['nlink'] !== 1)) throw new InvalidArgumentException('禁止修改硬链接文件');
            $old = $exists ? file_get_contents($file) : null;
            if ($exists && $old === false) throw new RuntimeException('无法读取原模板文件');
            if (!$create && (!preg_match('/^[a-f0-9]{64}$/D', (string)($input['hash'] ?? '')) || !hash_equals(hash('sha256', $old), (string)$input['hash']))) throw new InvalidArgumentException('文件已被修改，请重新读取后保存');
            if ($exists && hash_equals(hash('sha256', $old), hash('sha256', $bytes))) return ['path' => $path, 'hash' => hash('sha256', $bytes), 'changed' => false];
            $mode = $exists ? (fileperms($file) & 0777) : 0644;
            $temporary = $this->temporary($directory, $bytes, $mode);
            if ($exists) $rollback = $this->temporary($directory, $old, $mode);
            if ($this->walk($scope['root'], $parent, true) !== $directory || is_link($file)) throw new InvalidArgumentException('模板路径已变更');
            if ($create) { if (!@link($temporary, $file)) throw new InvalidArgumentException('同名模板文件已存在或目录不可写'); unlink($temporary); $temporary = null; }
            elseif (!rename($temporary, $file)) throw new RuntimeException('发布模板文件失败');
            else $temporary = null;
            try { $this->publish(); }
            catch (\Throwable $error) {
                if ($create ? !unlink($file) : !rename($rollback, $file)) throw new RuntimeException('缓存发布与模板恢复失败，请检查模板文件', 0, $error);
                $rollback = null;
                throw new RuntimeException('缓存发布失败，模板内容已恢复', 0, $error);
            }
            return ['path' => $path, 'hash' => hash('sha256', $bytes), 'changed' => true];
        } finally {
            if ($temporary !== null && is_file($temporary)) @unlink($temporary);
            if ($rollback !== null && is_file($rollback)) @unlink($rollback);
            flock($lock, LOCK_UN); fclose($lock);
        }
    }
}
