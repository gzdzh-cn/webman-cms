<?php
declare(strict_types=1);
namespace app\cms\service;

/** Owned public HTML; database records retain hashes and withdrawal tombstones. */
final class PublicStaticPublisher
{
    private GeneratedFileStore $metadata;
    private string $publicRoot;
    private bool $mapped;

    public function __construct(?string $publicRoot = null, ?string $registry = null)
    {
        $this->mapped = $publicRoot === null;
        $this->publicRoot = rtrim($publicRoot ?? \app\cms\Paths::publicRoot(), '/');
        $this->metadata = new GeneratedFileStore($registry ?? 'public-static');
    }

    /** URL prefixes and deployment bases never change the physical document root. */
    public function relativePath(array $task): string
    {
        $url = GeneratedFileStore::urlKey($task['url'], $task['origin'] ?? 'http://localhost', $task['base'] ?? '');
        $path = rawurldecode((string)parse_url($url, PHP_URL_PATH));
        $base = rtrim((string)($task['base'] ?? ''), '/');
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) $path = substr($path, strlen($base));
        $directory = trim((string)($task['static_directory'] ?? 'html'), '/');
        if ($directory === '') $directory = 'html';
        if (!preg_match('#^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$#D', $directory)) throw new \RuntimeException('静态页面保存目录无效');
        $prefix = '/' . $directory;
        if (($task['static_showdir'] ?? '1') !== '0' && ($path === $prefix || str_starts_with($path, $prefix . '/'))) $path = substr($path, strlen($prefix));
        $path = ltrim($path, '/');
        if ($path === '' || str_ends_with($path, '/')) $path .= 'index.html';
        if (preg_match('#(?:^|/)index\.php$#i', $path)) $path = substr($path, 0, -9) . 'index.html';
        if (!preg_match('/\.html?$/i', $path)) $path .= '/index.html';
        $query = (string)(parse_url($url, PHP_URL_QUERY) ?? '');
        if ($query !== '') $path = preg_replace('/(\.html?)$/i', '--q-' . substr(hash('sha256', $query), 0, 24) . '$1', $path);
        // Two rendered devices must never overwrite each other even on a shared URL.
        return 'html/' . (($task['device'] ?? 'pc') === 'mobile' ? '__mobile/' : '') . $path;
    }

    public function assertWritable(array $task): void
    {
        $this->metadata->locked(function () use ($task): void {
            $records = $this->records();
            $relative = $this->relativePath($task);
            $this->assertDestination($relative, $task, $records);
            // A pre-existing visible file would shadow the generated route.
            $path = rawurldecode((string)parse_url($task['url'], PHP_URL_PATH));
            $base = rtrim((string)($task['base'] ?? ''), '/');
            if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) $path = substr($path, strlen($base));
            $visible = ltrim($path, '/');
            if ($visible === '' || str_ends_with($visible, '/')) $visible .= 'index.html';
            if (!preg_match('#(?:^|/)index\.php$#i', $visible)) {
                // A language or category URL may name its directory without
                // a trailing slash. Check the same index file that publication
                // will use, while target() still rejects links and file parents.
                if (pathinfo($visible, PATHINFO_EXTENSION) === '') $visible .= '/index.html';
                $target = $this->target($visible);
                if (is_file($target) && !isset($records['files'][$visible])) throw new \RuntimeException('生成URL已由公开文件占用，未覆盖原文件：' . $task['url']);
            }
        });
    }

    public function publish(array $task, string $bytes, callable $stillCurrent): array
    {
        return $this->metadata->locked(function () use ($task, $bytes, $stillCurrent): array {
            if (!$stillCurrent()) throw new \RuntimeException('SEO或模板配置已变化，请重新创建任务');
            $records = $this->records(); $before = $records; $relative = $this->relativePath($task);
            $this->assertDestination($relative, $task, $records);
            $hash = hash('sha256', $bytes);
            // Persist ownership first: an interrupted publication can never become a raw asset.
            $records['files'][$relative] = ['sha256' => $hash, 'identity' => $this->identity($task), 'active' => true, 'url' => $task['url'], 'lang' => $task['lang'], 'device' => $task['device']];
            $this->metadata->write('manifest.json', $records);
            try { $this->atomic($relative, $bytes); }
            catch (\Throwable $error) { $this->metadata->write('manifest.json', $before); throw $error; }
            return ['public_file' => $relative, 'public_sha256' => $hash];
        });
    }

    public function bytes(array $entry): ?string
    {
        if (!isset($entry['public_file'], $entry['public_sha256'])) return null;
        $records = $this->records(); $owned = $records['files'][$entry['public_file']] ?? null;
        if (!$owned || empty($owned['active']) || $owned['sha256'] !== $entry['public_sha256']) return null;
        $path = $this->target($entry['public_file']);
        if (!is_file($path)) return null;
        $bytes = file_get_contents($path);
        return is_string($bytes) && hash_equals($entry['public_sha256'], hash('sha256', $bytes)) ? $bytes : null;
    }

    public function ownsFile(string $file): bool
    {
        $root = \app\cms\support\EmbeddedResources::realpath($this->publicRoot);
        if($this->mapped){$html=realpath(\app\cms\Paths::publicHtml());if($html&&str_starts_with($file,$html.'/'))return isset($this->records()['files']['html/'.substr($file,strlen($html)+1)]);}
        // Normalize the lexical path without following symlinks.
        $parts = [];
        foreach (explode('/', $file) as $part) { if ($part === '' || $part === '.') continue; if ($part === '..') array_pop($parts); else $parts[] = $part; }
        $file = '/' . implode('/', $parts);
        if (!$root || !str_starts_with($file, $root . '/')) return false;
        return isset($this->records()['files'][substr($file, strlen($root) + 1)]);
    }

    /** Only unchanged owned files are removed. Keep tombstones to protect stale callbacks. */
    public function withdraw(array $entries): array
    {
        return $this->metadata->locked(function () use ($entries): array {
            $records = $this->records(); $protected = [];
            foreach ($entries as $entry) {
                $relative = $entry['public_file'] ?? null;
                if (!$relative || !isset($records['files'][$relative]) || $records['files'][$relative]['sha256'] !== ($entry['public_sha256'] ?? null)) continue;
                if (!$this->removeUnchanged($relative, $records['files'][$relative]['sha256'])) $protected[] = $relative;
                $records['files'][$relative]['active'] = false;
            }
            $this->metadata->write('manifest.json', $records);
            return $protected;
        });
    }

    /** Hold the publication lock through DB commit and compensate on any save failure. */
    public function configurationChange(callable $operation): mixed
    {
        return $this->metadata->locked(function () use ($operation): mixed {
            $before = $this->records(); $next = $before; $backup = [];
            try {
                foreach ($before['files'] as $relative => $entry) {
                    if (empty($entry['active'])) continue;
                    $path = $this->target($relative);
                    if (is_file($path) && hash_file('sha256', $path) === $entry['sha256']) {
                        $bytes = file_get_contents($path);
                        if (!is_string($bytes)) throw new \RuntimeException('无法备份已有静态页面');
                        $backup[$relative] = $bytes;
                        if (!unlink($path)) throw new \RuntimeException('无法撤回已有静态页面');
                    }
                    $next['files'][$relative]['active'] = false;
                }
                $this->metadata->write('manifest.json', $next);
                return $operation();
            } catch (\Throwable $error) {
                foreach ($backup as $relative => $bytes) {
                    if (file_exists($this->target($relative))) throw new \RuntimeException('配置保存失败，静态页面恢复路径被占用，请检查发布目录', 0, $error);
                    $this->atomic($relative, $bytes);
                }
                $this->metadata->write('manifest.json', $before);
                throw $error;
            }
        });
    }

    private function identity(array $task): string { return hash('sha256', serialize([$task['lang'], $task['device'], $task['kind'], $task['id'], $task['page']])); }
    private function records(): array { return $this->metadata->read('manifest.json', ['version' => 1, 'files' => []]); }

    private function assertDestination(string $relative, array $task, array $records): void
    {
        $path = $this->target($relative); $owned = $records['files'][$relative] ?? null;
        if ($owned && !empty($owned['active']) && $owned['identity'] !== $this->identity($task)) throw new \RuntimeException('静态文件路径与其他页面冲突，未覆盖');
        if (file_exists($path) && (!is_file($path) || !$owned || hash_file('sha256', $path) !== $owned['sha256'])) throw new \RuntimeException('静态文件路径已被占用或修改，未覆盖：' . $relative);
    }

    private function target(string $relative): string
    {
        if ($relative === '' || str_starts_with($relative, '/') || preg_match('/[\x00-\x20\x7f\\\\]/', $relative)) throw new \RuntimeException('静态文件路径无效');
        $base=$this->publicRoot;
        if($this->mapped&&str_starts_with($relative,'html/')){$base=\app\cms\Paths::publicHtml();$relative=substr($relative,5);}
        if (!str_starts_with($base, 'phar://')) \app\cms\config\Entry::assertPath($base);
        $root = \app\cms\support\EmbeddedResources::realpath($base);
        if (!$root || is_link($base)) throw new \RuntimeException('公开目录不存在或为链接');
        $path = $root; $parts = explode('/', $relative);
        foreach ($parts as $index => $part) {
            if ($part === '' || $part === '.' || $part === '..' || str_starts_with($part, '.')) throw new \RuntimeException('静态文件路径越界');
            $path .= '/' . $part;
            if (is_link($path) || ($index < count($parts) - 1 && file_exists($path) && !is_dir($path))) throw new \RuntimeException('静态文件路径包含链接或非目录');
        }
        if (!preg_match('/\.html?$/i', $path)) throw new \RuntimeException('只能发布HTML页面');
        return $path;
    }

    private function atomic(string $relative, string $bytes): void
    {
        $path = $this->target($relative); $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('无法创建静态页面目录');
        $path = $this->target($relative); $temp = tempnam($directory, '.cms-static-');
        try {
            if (!$temp || file_put_contents($temp, $bytes, LOCK_EX) !== strlen($bytes) || !chmod($temp, 0644) || !rename($temp, $path)) throw new \RuntimeException('无法发布静态页面');
        } finally { if ($temp && is_file($temp)) unlink($temp); }
    }

    private function removeUnchanged(string $relative, string $hash): bool
    {
        $path = $this->target($relative);
        if (!file_exists($path)) return true;
        return is_file($path) && hash_file('sha256', $path) === $hash && unlink($path);
    }
}
