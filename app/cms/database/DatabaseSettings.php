<?php
declare(strict_types=1);
namespace app\cms\database;

use app\cms\config\Environment;
use PhpParser\Node;
use PhpParser\ParserFactory;

final class DatabaseSettings
{
    private static array $savedCache = [];
    private const BEGIN = '// BEGIN CMS DATABASE SETTINGS';
    private const END = '// END CMS DATABASE SETTINGS';
    private const KEYS = ['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASSWORD','DB_PREFIX','DB_TABLE_LAYOUT','CMS_DB_PREFIX','ADMIN_DB_PREFIX'];
    private const DEFAULTS = ['DB_HOST'=>'127.0.0.1','DB_PORT'=>'3306','DB_NAME'=>'','DB_USER'=>'','DB_PASSWORD'=>'','DB_PREFIX'=>'wa_','DB_TABLE_LAYOUT'=>'','CMS_DB_PREFIX'=>'ey_','ADMIN_DB_PREFIX'=>'wa_'];
    private static function path(?string $root): string
    {
        $base = realpath($root ?? dirname(__DIR__, 3));
        if (!$base || !is_dir($base) || realpath($base.'/config') !== $base.'/config') throw new \RuntimeException('配置目录无效');
        $path = $base.'/config/database.php';
        clearstatcache(true, $path);
        if (is_link($path) || !is_file($path) || !is_readable($path)) throw new \RuntimeException('数据库配置文件无效');
        $stat = lstat($path);
        if (!$stat || $stat['nlink'] !== 1 || $stat['size'] > 1048576) throw new \RuntimeException('数据库配置文件状态无效');
        return $path;
    }
    private static function parse(string $contents): array
    {
        // Locate only standalone PHP comment tokens; marker-looking text in
        // a quoted password is data and never defines the block boundary.
        $markers = []; $offset = 0;
        foreach (token_get_all($contents) as $token) {
            $text = is_array($token) ? $token[1] : $token;
            if (is_array($token) && $token[0] === T_COMMENT) {
                $comment = rtrim($text, " \t\r");
                if ($comment === self::BEGIN || $comment === self::END) {
                    $prefix = substr($contents, 0, $offset);
                    $line = strrpos($prefix, "\n");
                    $indent = substr($prefix, $line === false ? 0 : $line + 1);
                    $rest = substr($contents, $offset + strlen($text));
                    $newline = strpos($rest, "\n");
                    $suffix = $newline === false ? $rest : substr($rest, 0, $newline);
                    if (!preg_match('/^[ \t]*$/D', $indent) || !preg_match('/^[ \t\r]*$/D', $suffix)) throw new \RuntimeException('数据库配置标记必须独占一行');
                    $markers[$comment][] = [$offset, $offset + strlen($text)];
                }
            }
            $offset += strlen($text);
        }
        if (count($markers[self::BEGIN] ?? []) !== 1 || count($markers[self::END] ?? []) !== 1) throw new \RuntimeException('数据库配置受管块无效');
        $begin = $markers[self::BEGIN][0][1];
        $end = $markers[self::END][0][0];
        if ($end < $begin) throw new \RuntimeException('数据库配置受管块无效');
        $parser = (new ParserFactory())->createForHostVersion();
        try {
            $parser->parse($contents);
            $nodes = $parser->parse('<?php '.substr($contents, $begin, $end - $begin));
        } catch (\PhpParser\Error $e) { throw new \RuntimeException('数据库配置语法无效', 0, $e); }
        if (count($nodes) !== 1 || !$nodes[0] instanceof Node\Stmt\Expression || !$nodes[0]->expr instanceof Node\Expr\Assign
            || !$nodes[0]->expr->var instanceof Node\Expr\Variable || $nodes[0]->expr->var->name !== 'cmsDatabaseSettings'
            || !$nodes[0]->expr->expr instanceof Node\Expr\Array_) throw new \RuntimeException('数据库配置受管块结构无效');
        $out = [];
        foreach ($nodes[0]->expr->expr->items as $item) {
            if (!$item || $item->byRef || $item->unpack || !$item->key instanceof Node\Scalar\String_ || !$item->value instanceof Node\Scalar\String_
                || !in_array($item->key->value, self::KEYS, true) || array_key_exists($item->key->value, $out)) throw new \RuntimeException('数据库配置仅允许固定字段及字符串字面量');
            $out[$item->key->value] = $item->value->value;
        }
        return [$out, $begin, $end];
    }
    public static function saved(?string $root = null): array
    {
        $path = self::path($root);
        $bytes = file_get_contents($path);
        if (!is_string($bytes)) throw new \RuntimeException('读取数据库配置失败');
        $digest = hash('sha256', $bytes);
        if ((self::$savedCache[$path]['digest'] ?? null) !== $digest) self::$savedCache[$path] = ['digest'=>$digest, 'values'=>self::parse($bytes)[0]];
        return self::$savedCache[$path]['values'];
    }
    public static function values(?string $root = null): array
    {
        $values = array_replace(self::DEFAULTS, self::saved($root), array_intersect_key(Environment::overrides($root), array_flip(self::KEYS)));
        self::validate($values);
        return $values;
    }
    /** PHP fallback values for readable config files; validate effective overrides first. */
    public static function fallbacks(?string $root = null): array
    {
        self::values($root);
        return array_replace(self::DEFAULTS, self::saved($root));
    }
    private static function validate(array $values): void
    {
        foreach (self::KEYS as $key) {
            if (!array_key_exists($key, $values) || !is_string($values[$key])) throw new \InvalidArgumentException('数据库配置类型无效: '.$key);
        }
        if (!preg_match('/^[A-Za-z0-9_.:-]{1,253}$/D', $values['DB_HOST'])) throw new \InvalidArgumentException('数据库主机无效');
        if (!preg_match('/^[1-9][0-9]{0,4}$/D', $values['DB_PORT']) || (int)$values['DB_PORT'] < 1 || (int)$values['DB_PORT'] > 65535) throw new \InvalidArgumentException('数据库端口无效');
        if ($values['DB_NAME'] !== '' && !preg_match('/^[A-Za-z0-9_]{1,64}$/D', $values['DB_NAME'])) throw new \InvalidArgumentException('数据库名称无效');
        if (!in_array($values['DB_TABLE_LAYOUT'], ['', 'legacy', 'unified-backend-v1'], true)) throw new \InvalidArgumentException('数据库表布局无效');
        foreach (['DB_PREFIX', 'CMS_DB_PREFIX', 'ADMIN_DB_PREFIX'] as $key) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,47}$/D', $values[$key])) throw new \InvalidArgumentException('数据库表前缀无效: '.$key);
        }
        if ($values['DB_TABLE_LAYOUT'] === 'unified-backend-v1' && strlen($values['DB_PREFIX']) > 40) throw new \InvalidArgumentException('统一布局表前缀过长');
    }
    private static function writable(string $path, bool $directory = false): array
    {
        clearstatcache(true, $path);
        $stat = lstat($path);
        if (!$stat || is_link($path) || ($directory ? !is_dir($path) : !is_file($path))
            || ($stat['mode'] & 0222) === 0 || !is_writable($path)) throw new \RuntimeException('数据库配置文件或目录不可写');
        return $stat;
    }
    private static function privateFile(string $path, $handle): void
    {
        if (!chmod($path, 0600)) throw new \RuntimeException('数据库配置文件权限设置失败');
        clearstatcache(true, $path);
        $file = lstat($path); $open = fstat($handle);
        if (!$file || !$open || is_link($path) || $file['dev'] !== $open['dev'] || $file['ino'] !== $open['ino']
            || $file['nlink'] !== 1 || ($file['mode'] & 0777) !== 0600) throw new \RuntimeException('数据库配置文件权限或身份无效');
    }
    public static function assertOverrides(array $expected, ?string $root = null): void
    {
        $overrides = Environment::overrides($root);
        foreach ($expected as $key => $value) {
            if (!in_array($key, self::KEYS, true) || !is_scalar($value)) throw new \InvalidArgumentException('数据库配置字段无效');
            if (array_key_exists($key, $overrides) && (string)$overrides[$key] !== (string)$value) throw new \RuntimeException('数据库配置被环境变量覆盖: '.$key);
        }
    }
    /** Validate writability and lock availability without creating a file. */
    public static function assertWritable(?string $root = null): void
    {
        $path = self::path($root); $dir = dirname($path); $lockPath = $dir.'/.cms-database.lock';
        $parent = self::writable($dir, true); self::writable($path);
        clearstatcache(true, $lockPath);
        if (is_link($lockPath)) throw new \RuntimeException('数据库配置锁无效');
        if (!file_exists($lockPath)) return;
        $expected = lstat($lockPath);
        if (!$expected || !is_file($lockPath) || $expected['nlink'] !== 1 || ($expected['mode'] & 07777) !== 0600) throw new \RuntimeException('数据库配置锁权限或类型无效');
        $handle = @fopen($lockPath, 'r+');
        if (!$handle) throw new \RuntimeException('无法打开数据库配置锁');
        $locked = false;
        try {
            $open = fstat($handle); clearstatcache(true, $lockPath); $actual = lstat($lockPath);
            if (!$open || !$actual || is_link($lockPath) || $open['dev'] !== $actual['dev'] || $open['ino'] !== $actual['ino']
                || $actual['dev'] !== $expected['dev'] || $actual['ino'] !== $expected['ino'] || $actual['nlink'] !== 1
                || ($actual['mode'] & 07777) !== 0600) throw new \RuntimeException('数据库配置锁发生变化');
            if (!flock($handle, LOCK_EX | LOCK_NB)) throw new \RuntimeException('数据库配置正被其他操作使用');
            $locked = true;
            $now = self::writable($dir, true);
            if ($now['dev'] !== $parent['dev'] || $now['ino'] !== $parent['ino']) throw new \RuntimeException('数据库配置目录发生变化');
            self::writable($path);
        } finally {
            if ($locked) flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
    public static function write(array $values, ?string $root = null): void
    {
        foreach ($values as $key => $value) {
            if (!in_array($key, self::KEYS, true) || (!is_string($value) && !($key === 'DB_PORT' && is_int($value)))) throw new \InvalidArgumentException('数据库配置字段或值无效');
            if ($key === 'DB_PORT' && is_int($value)) $values[$key] = (string)$value;
        }
        self::validate(array_replace(self::DEFAULTS, self::saved($root), $values));
        self::assertWritable($root);
        $path = self::path($root); $dir = dirname($path); $lockPath = $dir.'/.cms-database.lock';
        $dirStat = self::writable($dir, true);
        self::writable($path);
        clearstatcache(true, $lockPath);
        if (is_link($lockPath) || (file_exists($lockPath) && (!is_file($lockPath) || lstat($lockPath)['nlink'] !== 1))) throw new \RuntimeException('数据库配置锁无效');
        $oldUmask = umask(0077);
        try { $lock = fopen($lockPath, 'c'); } finally { umask($oldUmask); }
        if (!$lock) throw new \RuntimeException('无法打开数据库配置锁');
        $tmp = null;
        try {
            if (!flock($lock, LOCK_EX | LOCK_NB)) throw new \RuntimeException('无法锁定数据库配置');
            $lockStat = fstat($lock); $linkStat = lstat($lockPath);
            if (!$lockStat || !$linkStat || is_link($lockPath) || $lockStat['ino'] !== $linkStat['ino'] || $linkStat['nlink'] !== 1) throw new \RuntimeException('数据库配置锁发生变化');
            self::privateFile($lockPath, $lock);
            $nowDir = self::writable($dir, true);
            if ($nowDir['dev'] !== $dirStat['dev'] || $nowDir['ino'] !== $dirStat['ino']) throw new \RuntimeException('数据库配置目录发生变化');
            self::writable($path);
            $path = self::path($root); $before = file_get_contents($path); $beforeStat = lstat($path);
            if (!is_string($before) || !$beforeStat) throw new \RuntimeException('无法读取数据库配置');
            [$saved, $begin, $end] = self::parse($before);
            foreach ($values as $key => $value) $saved[$key] = (string)$value;
            $block = "\n\$cmsDatabaseSettings = [\n";
            foreach (self::KEYS as $key) if (array_key_exists($key, $saved)) $block .= '    '.var_export($key, true).' => '.var_export($saved[$key], true).",\n";
            $block .= "];\n";
            $after = substr($before, 0, $begin).$block.substr($before, $end);
            self::parse($after);
            $tmp = $dir.'/.cms-database-'.bin2hex(random_bytes(16)).'.tmp';
            $oldUmask = umask(0077);
            try { $handle = fopen($tmp, 'x'); } finally { umask($oldUmask); }
            if (!$handle) throw new \RuntimeException('无法创建数据库配置临时文件');
            try {
                self::privateFile($tmp, $handle);
                if (fwrite($handle, $after) !== strlen($after) || !fflush($handle) || (function_exists('fsync') && !fsync($handle))) throw new \RuntimeException('写入数据库配置失败');
            } finally { fclose($handle); }
            clearstatcache(true, $path);
            $nowDir = self::writable($dir, true);
            if ($nowDir['dev'] !== $dirStat['dev'] || $nowDir['ino'] !== $dirStat['ino']) throw new \RuntimeException('数据库配置目录发生变化');
            $currentStat = self::writable($path); $current = file_get_contents($path);
            if (is_link($path) || !$currentStat || $currentStat['ino'] !== $beforeStat['ino'] || $currentStat['dev'] !== $beforeStat['dev'] || $current !== $before) throw new \RuntimeException('数据库配置并发变化');
            if (!rename($tmp, $path)) throw new \RuntimeException('发布数据库配置失败');
            $tmp = null;
            unset(self::$savedCache[$path]);
        } finally {
            if ($tmp !== null && is_file($tmp) && !is_link($tmp)) unlink($tmp);
            flock($lock, LOCK_UN); fclose($lock);
        }
    }
}
