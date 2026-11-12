<?php
declare(strict_types=1);
namespace app\cms\install;

use app\cms\config\Environment;
use app\cms\config\EnvironmentParser;
use Dotenv\Parser\Lines;
use Dotenv\Parser\Parser;
use RuntimeException;

/** Update existing database overrides only after the database import has completed. */
final class InstallerEnvironment
{
    private const KEYS = ['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASSWORD','DB_PREFIX','DB_TABLE_LAYOUT','CMS_DB_PREFIX','ADMIN_DB_PREFIX'];
    private string $root;

    public function __construct(string $root)
    {
        $real = realpath($root);
        if ($real === false || !is_dir($real) || !is_dir(\app\cms\Paths::config())) throw new RuntimeException('安装配置目录无效');
        $this->root = $real;
    }

    private function read(): ?string
    {
        $path = \app\cms\config\Environment::path();
        clearstatcache(true, $path);
        if (is_link($path)) throw new RuntimeException('.env 不能是链接');
        if (!file_exists($path)) return null;
        $stat = lstat($path);
        if (!$stat || !is_file($path) || !is_readable($path) || ($stat['mode'] & 0444) === 0 || $stat['nlink'] !== 1 || $stat['size'] > 1048576) throw new RuntimeException('.env 文件不可读或状态无效');
        $text = file_get_contents($path);
        if (!is_string($text)) throw new RuntimeException('无法读取 .env');
        return $text;
    }

    private function parse(string $text): array
    {
        // Environment may still be inherited from a master started before this
        // installer was deployed. Use its existing snapshot API, not a new method.
        return EnvironmentParser::parse($text, Environment::processEnvironment());
    }

    private function prepare(?string $before, array $values): ?string
    {
        foreach ($values as $key => $value) {
            if (!in_array($key, self::KEYS, true) || !is_string($value) || preg_match('/[\x00\r\n]/', $value)) throw new RuntimeException('安装数据库配置字段无效');
        }
        $local = $before === null ? [] : $this->parse($before);
        $process = Environment::processEnvironment();
        foreach ($values as $key => $value) {
            // Existing local keys can override a different inherited value on restart.
            if (!array_key_exists($key, $local) && array_key_exists($key, $process) && (string)$process[$key] !== $value) {
                throw new RuntimeException('数据库配置被进程环境变量覆盖: '.$key.'；请修改启动环境后重新启动安装程序');
            }
        }
        $changed = false;
        foreach (array_intersect_key($values, $local) as $key => $value) if ($local[$key] !== $value) $changed = true;
        if (!$changed) return $before;

        // Map the dotenv parser's logical entries back to original physical bytes.
        // A multiline password can contain apparent assignments and comments.
        $parts = preg_split('/(\r\n|\n|\r)/', $before, -1, PREG_SPLIT_DELIM_CAPTURE);
        $records = [];
        for ($i = 0; $i < count($parts); $i += 2) $records[] = [$parts[$i], $parts[$i+1] ?? ''];
        $rawEntries = Lines::process(array_column($records, 0));
        $cursor = 0; $after = '';
        foreach ($rawEntries as $raw) {
            while (isset($records[$cursor]) && (trim($records[$cursor][0]) === '' || str_starts_with(trim($records[$cursor][0]), '#'))) {
                $after .= implode('', $records[$cursor++]);
            }
            $count = substr_count($raw, "\n") + 1;
            $lines = array_slice($records, $cursor, $count);
            if (implode("\n", array_column($lines, 0)) !== $raw) throw new RuntimeException('无法安全更新 .env 数据库配置');
            $entry = (new Parser())->parse($raw)[0];
            $key = $entry->getName();
            if (array_key_exists($key, $values)) {
                $encoded = '"'.str_replace(['\\','"','$'], ['\\\\','\\"','\\$'], $values[$key]).'"';
                $after .= $key.'='.$encoded.$lines[count($lines)-1][1];
            } else foreach ($lines as $line) $after .= implode('', $line);
            $cursor += $count;
        }
        foreach (array_slice($records, $cursor) as $line) $after .= implode('', $line);
        $proposed = $this->parse($after);
        foreach (array_intersect_key($values, $local) as $key => $value) {
            if (($proposed[$key] ?? null) !== $value) throw new RuntimeException('.env 数据库配置验证失败: '.$key);
        }
        return $after;
    }

    private function writable(string $path, bool $directory = false): array
    {
        clearstatcache(true, $path);
        $stat = lstat($path);
        if (!$stat || is_link($path) || ($directory ? !is_dir($path) : !is_file($path)) || (!$directory && $stat['nlink'] !== 1)
            || ($stat['mode'] & 0222) === 0 || !is_writable($path)) throw new RuntimeException('.env 或所在目录不可写，请修正权限后重试');
        return $stat;
    }

    private function lockPath(): string { return \app\cms\Paths::config('.cms-install-env.lock'); }

    private function assertLock(): void
    {
        $path = $this->lockPath();
        clearstatcache(true, $path);
        if (is_link($path)) throw new RuntimeException('.env 更新锁无效');
        if (!file_exists($path)) return;
        $stat = lstat($path);
        if (!$stat || !is_file($path) || $stat['nlink'] !== 1 || ($stat['mode'] & 07777) !== 0600) throw new RuntimeException('.env 更新锁无效');
    }

    /** Read-only preflight: neither rewrite .env nor create it for PHP-only installs. */
    public function inspect(array $values): void
    {
        $before = $this->read();
        if ($this->prepare($before, $values) === $before) return;
        $this->writable(dirname(Environment::path()), true);
        $this->writable(\app\cms\config\Environment::path());
        $this->writable(\app\cms\Paths::config(''), true);
        $this->assertLock();
        if (is_file($this->lockPath())) {
            $handle = fopen($this->lockPath(), 'r+');
            if (!$handle) throw new RuntimeException('无法打开 .env 更新锁');
            try {
                $opened = fstat($handle); $linked = lstat($this->lockPath());
                if (!$opened || !$linked || is_link($this->lockPath()) || $opened['dev'] !== $linked['dev'] || $opened['ino'] !== $linked['ino']) throw new RuntimeException('.env 更新锁发生变化');
                if (!flock($handle, LOCK_EX | LOCK_NB)) throw new RuntimeException('.env 正在更新，请稍后重试');
            } finally { flock($handle, LOCK_UN); fclose($handle); }
        }
    }

    public function synchronize(array $values): void
    {
        $this->inspect($values);
        $before = $this->read();
        if ($this->prepare($before, $values) === $before) return;
        $this->assertLock();
        $oldUmask = umask(0077);
        try { $lock = fopen($this->lockPath(), 'c+'); } finally { umask($oldUmask); }
        if (!$lock) throw new RuntimeException('无法打开 .env 更新锁');
        $tmp = null;
        try {
            if (!flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('.env 正在更新，请稍后重试');
            $this->assertLock();
            $opened = fstat($lock); $linked = lstat($this->lockPath());
            if (!$opened || !$linked || $opened['dev'] !== $linked['dev'] || $opened['ino'] !== $linked['ino']) throw new RuntimeException('.env 更新锁发生变化');
            $parent = $this->writable(dirname(Environment::path()), true);
            $stat = $this->writable(\app\cms\config\Environment::path());
            $before = $this->read(); $after = $this->prepare($before, $values);
            if ($after === $before) return;
            $tmp = dirname(Environment::path()).'/.env-install-'.bin2hex(random_bytes(16));
            $oldUmask = umask(0077);
            try { $handle = fopen($tmp, 'x+b'); } finally { umask($oldUmask); }
            if (!$handle) throw new RuntimeException('无法创建 .env 临时文件');
            try {
                if (!chmod($tmp, 0600) || fwrite($handle, $after) !== strlen($after) || !fflush($handle)
                    || (function_exists('fsync') && !fsync($handle))) throw new RuntimeException('写入 .env 失败');
                $tempStat = fstat($handle);
            } finally { fclose($handle); }
            $nowParent = $this->writable(dirname(Environment::path()), true); $now = $this->writable(\app\cms\config\Environment::path());
            if ($nowParent['dev'] !== $parent['dev'] || $nowParent['ino'] !== $parent['ino'] || $now['dev'] !== $stat['dev']
                || $now['ino'] !== $stat['ino'] || $this->read() !== $before) throw new RuntimeException('.env 在安装期间发生变化，请重新检查后重试');
            clearstatcache(true, $tmp); $tempLink = lstat($tmp);
            if (!$tempStat || !$tempLink || is_link($tmp) || !is_file($tmp) || $tempLink['nlink'] !== 1 || ($tempLink['mode'] & 0777) !== 0600
                || $tempStat['dev'] !== $tempLink['dev'] || $tempStat['ino'] !== $tempLink['ino']) throw new RuntimeException('.env 临时文件发生变化');
            if (!rename($tmp, \app\cms\config\Environment::path())) throw new RuntimeException('保存 .env 失败');
            $tmp = null;
            Environment::refresh($this->root);
        } finally {
            if ($tmp !== null && is_file($tmp) && !is_link($tmp)) unlink($tmp);
            flock($lock, LOCK_UN); fclose($lock);
        }
    }
}
