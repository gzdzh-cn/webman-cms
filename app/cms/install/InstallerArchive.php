<?php
declare(strict_types=1);
namespace app\cms\install;

use RuntimeException;

/** Private installer material. Call seal only while holding the installation lock. */
final class InstallerArchive
{
    private string $root;
    private const RECORD = 'config/cms-installer-archive.json';

    public function __construct(?string $root = null)
    {
        $this->root = rtrim($root ?? base_path(), '/');
        if (!is_dir($this->root) || is_link($this->root) || is_link($this->root . '/config')) {
            throw new RuntimeException('安装归档根目录无效');
        }
    }

    public function seal(bool $check = false): array
    {
        $record = $this->record();
        $source = $this->root . '/install';
        if (!$record) {
            if (!$this->exists($source)) return $this->result('absent', null, $check);
            $tree = $this->inventory($source);
            if (!is_writable($this->root) || !is_writable($this->root . '/config')) {
                throw new RuntimeException('项目根目录或配置目录不可写，无法封存安装资料');
            }
            if ($check) return $this->result('ready', null, true);
            do {
                $suffix = '';
                for ($i = 0; $i < 12; ++$i) $suffix .= chr(97 + random_int(0, 25));
                $directory = 'install_' . $suffix;
            } while ($this->exists($this->root . '/' . $directory));
            $record = ['version' => 1, 'status' => 'prepared', 'directory' => $directory,
                'files' => $tree['files'], 'directories' => $tree['directories'], 'created_at' => time()];
            $this->write($record);
        }
        $destination = $this->root . '/' . $record['directory'];
        $sourceExists = $this->exists($source);
        $destinationExists = $this->exists($destination);
        if ($sourceExists && $destinationExists) throw new RuntimeException('原安装目录与归档目录同时存在，拒绝覆盖或删除');
        if ($record['status'] === 'complete') {
            if ($sourceExists) throw new RuntimeException('已封存后又出现安装目录，请先核对部署文件');
            if (!$destinationExists) return $this->result('removed', $record['directory'], $check);
            $this->verify($destination, $record);
            return $this->result('complete', $record['directory'], $check);
        }
        if (!$sourceExists && !$destinationExists) throw new RuntimeException('封存记录对应的安装资料缺失，停止恢复');
        if ($sourceExists) {
            $this->verify($source, $record);
            if (!is_writable($this->root)) throw new RuntimeException('项目根目录不可写，无法改名安装目录');
            if ($check) return $this->result('pending', $record['directory'], true);
            // Cooperating installers share one lock; never overwrite an existing target.
            if ($this->exists($destination) || !@rename($source, $destination)) {
                throw new RuntimeException('安装目录改名失败，网站已安装，可通过 --seal 重试');
            }
        }
        $this->verify($destination, $record);
        if ($check) return $this->result('pending', $record['directory'], true);
        $record['status'] = 'complete';
        $record['completed_at'] = time();
        $this->write($record);
        return $this->result('complete', $record['directory'], false);
    }

    /** Deployment copies exactly this validated directory, never a glob of install_*. */
    public function deploymentDirectory(): ?string
    {
        $record = $this->record();
        if (!$record) {
            if (!$this->exists($this->root . '/install')) return null;
            $this->inventory($this->root . '/install');
            return 'install';
        }
        $result = $this->seal(true);
        if ($result['archive_status'] === 'removed') return null;
        if ($result['archive_status'] !== 'complete') throw new RuntimeException('安装目录封存尚未完成，请先执行 cms:install --seal');
        return $record['directory'];
    }

    /** Maintenance-only lookup. The Web installer never resolves archived material. */
    public function baselinePath(string $filename): string
    {
        if (!in_array($filename, ['eyoucms-1.7.7.sql', 'webman-admin.sql', 'cms-empty.sql', 'cms-internal.sql'], true)) {
            throw new RuntimeException('不允许的安装基线文件');
        }
        $directory = $this->deploymentDirectory();
        if ($directory === null) throw new RuntimeException('安装资料已移除；已安装站点升级请使用 cms:migrate');
        $file = $this->root . '/' . $directory . '/database/' . $filename;
        if (!is_file($file) || is_link($file)) throw new RuntimeException('安装基线文件缺失');
        return $file;
    }

    private function result(string $status, ?string $directory, bool $check): array
    {
        return ['archive_status' => $status, 'archive_pending' => in_array($status, ['ready', 'pending'], true),
            'archive_directory' => $directory, 'dry_run' => $check];
    }

    private function exists(string $path): bool
    {
        clearstatcache(true, $path);
        return file_exists($path) || is_link($path);
    }

    private function record(): ?array
    {
        $path = $this->root . '/' . self::RECORD;
        if (!$this->exists($path)) return null;
        if (is_link($path) || !is_file($path) || filesize($path) > 8 * 1024 * 1024) throw new RuntimeException('安装归档记录路径无效');
        try { $record = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR); }
        catch (\Throwable $error) { throw new RuntimeException('安装归档记录损坏'); }
        if (!is_array($record) || ($record['version'] ?? null) !== 1
            || !in_array($record['status'] ?? null, ['prepared', 'complete'], true)
            || !is_string($record['directory'] ?? null) || !preg_match('/^install_[a-z]{12}$/D', $record['directory'])
            || !is_array($record['files'] ?? null) || !is_array($record['directories'] ?? null)) {
            throw new RuntimeException('安装归档记录无效');
        }
        foreach ($record['files'] as $name => $item) {
            $this->relative((string)$name);
            if (!is_array($item) || !is_string($item['sha256'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $item['sha256'])
                || !is_int($item['size'] ?? null) || $item['size'] < 0) throw new RuntimeException('安装归档文件清单无效');
        }
        foreach ($record['directories'] as $name) $this->relative($name);
        return $record;
    }

    private function relative(mixed $name): void
    {
        if (!is_string($name) || $name === '' || str_contains($name, "\0") || str_contains($name, '\\')
            || str_starts_with($name, '/') || preg_match('#(^|/)(?:\.|\.\.)(?:/|$)#', $name)) {
            throw new RuntimeException('安装归档包含越界路径');
        }
    }

    private function inventory(string $path): array
    {
        if (!is_dir($path) || is_link($path)) throw new RuntimeException('安装资料不能为符号链接或普通文件');
        $files = []; $directories = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($path) + 1);
            $this->relative($relative);
            if ($item->isLink()) throw new RuntimeException('安装资料含符号链接，拒绝封存');
            if ($item->isDir()) { $directories[] = $relative; continue; }
            if (!$item->isFile()) throw new RuntimeException('安装资料含特殊文件，拒绝封存');
            $hash = hash_file('sha256', $item->getPathname());
            if ($hash === false) throw new RuntimeException('无法读取安装资料');
            $files[$relative] = ['sha256' => $hash, 'size' => $item->getSize()];
        }
        ksort($files); sort($directories, SORT_STRING);
        return ['files' => $files, 'directories' => $directories];
    }

    private function verify(string $path, array $record): void
    {
        $actual = $this->inventory($path);
        if ($actual['files'] !== $record['files'] || $actual['directories'] !== $record['directories']) {
            throw new RuntimeException('安装资料与归档SHA清单不一致，拒绝继续');
        }
    }

    private function write(array $record): void
    {
        $path = $this->root . '/' . self::RECORD;
        if (is_link($path) || is_link(dirname($path)) || !is_writable(dirname($path))) throw new RuntimeException('安装归档记录不可写');
        $temporary = tempnam(dirname($path), '.seal-');
        if ($temporary === false) throw new RuntimeException('无法创建安装归档记录');
        try {
            chmod($temporary, 0600);
            $bytes = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
            if (file_put_contents($temporary, $bytes, LOCK_EX) !== strlen($bytes) || !@rename($temporary, $path)) {
                throw new RuntimeException('安装归档记录写入失败，可通过 --seal 恢复');
            }
        } finally { if (is_file($temporary)) unlink($temporary); }
    }
}
