<?php
declare(strict_types=1);
namespace app\cms\persistence;

use app\cms\Paths;
use PDO;

/** Mutable upstream conf markers live in DB; bundled dictionaries/version are read-only. */
final class LegacyMarkerStore
{
    private StateStore $store;
    public function __construct(?PDO $pdo = null, ?string $prefix = null)
    {
        $this->store = new StateStore('legacy:conf', $pdo, $prefix);
    }
    public function read(string $name, ?string $default = null): ?string
    {
        $this->name($name);
        $record = $this->store->read($name);
        if ($record) {
            $bytes = base64_decode((string)($record['base64'] ?? ''), true);
            if ($bytes === false || !hash_equals((string)($record['sha256'] ?? ''), hash('sha256', $bytes))) throw new \RuntimeException('兼容配置校验失败');
            return $bytes;
        }
        if ($name === 'version.txt') {
            $seed = Paths::legacyData('conf/version.txt');
            if (is_file($seed) && !is_link($seed)) return (string)file_get_contents($seed);
        }
        return $default;
    }
    public function has(string $name): bool { return $this->read($name) !== null; }
    public function write(string $name, string $bytes): void
    {
        $this->name($name);
        $this->store->locked(fn() => $this->store->write($name, ['base64'=>base64_encode($bytes),'sha256'=>hash('sha256',$bytes)]));
    }
    /** Compatibility-only file view. Removing it loses no authoritative value. */
    public function cachedPath(string $name): string
    {
        $this->name($name);
        $directory = Paths::runtime('cms/legacy-conf');
        Paths::ensureDirectory($directory);
        $path = $directory.'/'.$name;
        if (is_link($path)) throw new \RuntimeException('兼容配置缓存不能是链接');
        $bytes = $this->read($name);
        if ($bytes !== null && (!is_file($path) || hash_file('sha256', $path) !== hash('sha256',$bytes))) {
            $temporary = tempnam($directory, '.marker-');
            if ($temporary === false) throw new \RuntimeException('无法建立兼容配置缓存');
            try {
                if (file_put_contents($temporary,$bytes,LOCK_EX) !== strlen($bytes) || !rename($temporary,$path)) throw new \RuntimeException('无法写入兼容配置缓存');
            } finally { if (is_file($temporary)) unlink($temporary); }
        } elseif ($bytes === null && is_file($path)) unlink($path);
        return $path;
    }
    /** Imports once, never silently restores stale source flags over later DB values. */
    public function importDirectory(string $directory, bool $apply = false): array
    {
        if (is_link($directory)) throw new \RuntimeException('兼容配置来源不能是链接');
        $items = [];
        foreach (is_dir($directory) ? new \DirectoryIterator($directory) : [] as $file) {
            if ($file->isDot() || $file->getFilename() === 'pinyin.dat') continue;
            if (!$file->isFile() || $file->isLink()) throw new \RuntimeException('兼容配置来源包含未知目录或链接');
            if ($file->getFilename() === '.DS_Store') continue;
            $name = $file->getFilename(); $this->name($name);
            $bytes = file_get_contents($file->getPathname());
            if ($bytes === false) throw new \RuntimeException('无法读取兼容配置来源');
            $items[$name] = ['bytes'=>$bytes,'sha256'=>hash('sha256',$bytes)];
        }
        ksort($items, SORT_STRING);
        $inspect = function () use ($items, $apply): array {
            $pdo = $this->store->connection(); $prefix = $this->store->prefix();
            $exists = Schema::exists($pdo, $prefix);
            $audit = new StateStore('migration:legacy-conf-v1', $pdo, $prefix);
            $report = ['source_files'=>count($items),'pending'=>0,'already_imported'=>0,'bytes'=>0,'dry_run'=>!$apply];
            foreach ($items as $name=>$item) {
                $previous = $exists ? $audit->read($name) : [];
                if ($previous) {
                    if (($previous['sha256']??'') !== $item['sha256']) throw new \RuntimeException('已迁移兼容配置来源发生变化：'.$name);
                    $report['already_imported']++; continue;
                }
                if ($exists && $this->store->has($name) && $this->read($name) !== $item['bytes']) throw new \RuntimeException('兼容配置数据库与来源冲突：'.$name);
                $report['pending']++; $report['bytes'] += strlen($item['bytes']);
                if ($apply) {
                    $this->store->write($name,['base64'=>base64_encode($item['bytes']),'sha256'=>$item['sha256']]);
                    $audit->write($name,['sha256'=>$item['sha256'],'bytes'=>strlen($item['bytes']),'imported_at'=>time()]);
                }
            }
            return $report;
        };
        if (!$apply) return $inspect();
        // Schema is installed by the explicit installation/state-migration command.
        return $this->store->locked(fn() => $this->store->transaction($inspect));
    }
    private function name(string $name): void
    {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,170}\.txt$/D', $name)) throw new \InvalidArgumentException('兼容配置名称无效');
    }
}
