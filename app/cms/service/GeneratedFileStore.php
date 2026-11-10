<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\persistence\StateStore;

/** Durable database records and content snapshots; only compiled templates use runtime. */
final class GeneratedFileStore
{
    private StateStore $store;
    private string $scope;

    public function __construct(string $root, ?StateStore $store = null)
    {
        // Legacy explicit directories are accepted as isolated identifiers, never read at runtime.
        if (in_array($root, ['static','sitemap','public-static'], true)) $this->scope = $root;
        elseif (preg_match('/^custom-[a-f0-9]{40}$/D', $root)) $this->scope = $root;
        elseif (str_starts_with($root, '/') && !str_contains($root, "\0")) $this->scope = 'custom-' . substr(hash('sha256', rtrim($root, '/')), 0, 40);
        else throw new \InvalidArgumentException('生成存储范围无效');
        $this->store = $store ?? new StateStore('generated:' . $this->scope);
    }
    public function root(): string { return $this->scope; }
    public function cacheDirectory(): string
    {
        $path = runtime_path('cms/generated-cache/' . $this->scope . '/compiled');
        if (\app\cms\cache\CachePolicy::isRedis()) return $path;
        $part = '';
        foreach (explode('/', trim($path, '/')) as $component) {
            if ($component === '.' || $component === '..' || $component === '') throw new \RuntimeException('编译缓存目录无效');
            $part .= '/' . $component;
            if (is_link($part) || (file_exists($part) && !is_dir($part))) throw new \RuntimeException('编译缓存目录包含链接或非目录');
        }
        if (!is_dir($path) && !mkdir($path, 0750, true) && !is_dir($path)) throw new \RuntimeException('无法创建编译缓存目录');
        return $path;
    }
    public function locked(callable $operation): mixed { return $this->store->locked(fn() => $operation($this)); }
    public function transaction(callable $operation): mixed { return $this->store->transaction($operation); }
    /** Related progress records become visible together after one short commit. */
    public function writeBatch(array $records): void
    {
        $this->store->transaction(function() use ($records): void {
            foreach($records as $name=>$data)$this->write($name,$data);
        });
    }
    public function read(string $name, array $default = []): array { $this->metadata($name); return $this->store->read($name, $default); }
    public function write(string $name, array $data): void { $this->metadata($name); $this->store->write($name, $data); }
    public function put(string $bytes, string $extension): array { return $this->store->putObject($bytes, $extension); }
    public function bytes(array $entry): ?string { return $this->store->objectBytes($entry); }
    public function remove(array $entry): bool { return $this->store->removeObject($entry); }
    private function metadata(string $name): void
    {
        if (!preg_match('/^(?:manifest|job-[a-f0-9]{32})\.json$/D', $name)) throw new \InvalidArgumentException('生成记录名称无效');
    }

    /** Canonical path/query identity, without fragments or transport-specific origin. */
    public static function urlKey(string $url, string $origin, string $base = ''): string
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_match('/[\x00-\x20\x7f\\\\]/', $url) || str_starts_with($url, '//')) throw new \InvalidArgumentException('生成URL无效');
        $parts = parse_url($url);
        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) throw new \InvalidArgumentException('生成URL无效');
        if (isset($parts['host'])) {
            $actual = strtolower(($parts['scheme'] ?? 'http') . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : ''));
            if ($actual !== strtolower(rtrim($origin, '/'))) throw new \InvalidArgumentException('生成URL不属于当前站点');
        } elseif (isset($parts['scheme'])) throw new \InvalidArgumentException('生成URL协议无效');
        $path = $parts['path'] ?? '/';
        if (!str_starts_with($path, '/') || preg_match('/%(?:00|2f|5c)/i', $path)) throw new \InvalidArgumentException('生成URL路径无效');
        $decoded = rawurldecode($path);
        foreach (explode('/', $decoded) as $segment) if ($segment === '.' || $segment === '..') throw new \InvalidArgumentException('生成URL包含越界路径');
        if ($base !== '' && $path !== $base && !str_starts_with($path, rtrim($base, '/') . '/')) throw new \InvalidArgumentException('生成URL超出站点目录');
        if (preg_match('#(?:^|/)(?:app|admin|template|uploads?|public|config|vendor|runtime)(?:/|$)#i', $decoded) || preg_match('/\.(?:php[0-9]?|phtml|phar|inc|css|js|json|svg|png|jpe?g|gif|webp|ico|woff2?|ttf|zip|pdf)(?:\/|$)/i', $decoded) && !preg_match('#(?:^|/)index\.php$#i', $decoded)) throw new \InvalidArgumentException('生成URL不是公开HTML页面');
        parse_str($parts['query'] ?? '', $query);
        if (array_filter($query, 'is_array')) throw new \InvalidArgumentException('生成URL查询参数无效');
        ksort($query);
        return $path . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
    }
}
