<?php
declare(strict_types=1);
namespace app\cms\config;

/** Snapshot the actual process environment before any dotenv loader mutates it. */
final class Environment
{
    private static ?array $process = null;
    private static array $files = [];
    public static function initialize(?string $root = null): void
    {
        if (self::$process === null) self::$process = getenv() ?: [];
        self::file($root);
    }
    private static function root(?string $root): string
    {
        $path = $root ?? dirname(__DIR__, 3);
        $real = realpath($path);
        if ($real === false || !is_dir($real)) throw new \RuntimeException('配置根目录无效');
        return $real;
    }
    private static function file(?string $root): array
    {
        $path = self::root($root).'/.env';
        clearstatcache(true, $path);
        if (is_link($path)) throw new \RuntimeException('.env 不能是链接');
        if (!file_exists($path)) { unset(self::$files[$path]); return []; }
        if (!is_file($path) || !is_readable($path) || filesize($path) > 1048576) throw new \RuntimeException('.env 文件无效');
        $text = file_get_contents($path);
        if (!is_string($text) || str_contains($text, "\0")) throw new \RuntimeException('.env 文件无效');
        $digest = hash('sha256', $text);
        if ((self::$files[$path]['digest'] ?? null) === $digest) return self::$files[$path]['values'];
        $values = self::parse($text);
        self::$files[$path] = ['digest' => $digest, 'values' => $values];
        return $values;
    }
    /** Parse a proposed local file with the same process snapshot as normal reads. */
    public static function parse(string $text): array
    {
        if (self::$process === null) self::$process = getenv() ?: [];
        return EnvironmentParser::parse($text, self::$process);
    }
    public static function overrides(?string $root = null): array
    {
        if (self::$process === null) self::initialize($root);
        return array_replace(self::$process, self::file($root));
    }
    public static function value(string $key, mixed $fallback = null, ?string $root = null): mixed
    {
        $values = self::overrides($root);
        return array_key_exists($key, $values) ? $values[$key] : $fallback;
    }
    public static function boolean(string $key, bool $fallback = false, ?string $root = null): bool
    {
        $v = self::value($key, $fallback, $root);
        if (is_bool($v)) return $v;
        if (is_string($v) || is_int($v)) {
            $out = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($out !== null && $v !== '') return $out;
        }
        throw new \InvalidArgumentException("环境变量 $key 不是有效布尔值");
    }
    public static function integer(string $key, int $fallback = 0, int|string|null $min = PHP_INT_MIN, int $max = PHP_INT_MAX, ?string $root = null): int
    {
        if (!is_int($min)) { if ($root !== null || $max !== PHP_INT_MAX) throw new \InvalidArgumentException('整数参数无效'); $root = $min; $min = PHP_INT_MIN; }
        $v = self::value($key, $fallback, $root);
        if (is_int($v)) { if ($v < $min || $v > $max) throw new \InvalidArgumentException("环境变量 $key 超出范围"); return $v; }
        if (is_string($v) && preg_match('/^-?(?:0|[1-9][0-9]*)$/D', $v)) {
            $result = filter_var($v, FILTER_VALIDATE_INT);
            if ($result !== false && $result >= $min && $result <= $max) return $result;
        }
        throw new \InvalidArgumentException("环境变量 $key 不是有效整数");
    }
    public static function refresh(?string $root = null): void { unset(self::$files[self::root($root).'/.env']); self::file($root); }
    public static function processEnvironment(): array
    {
        if (self::$process === null) self::initialize();
        return self::$process;
    }
    public static function sources(?string $root = null, ?array $keys = null): array
    {
        if (self::$process === null) self::initialize($root);
        $file = self::file($root); $process = self::$process;
        if ($keys !== null) { $filter = array_fill_keys($keys, true); $file = array_intersect_key($file, $filter); $process = array_intersect_key($process, $filter); }
        ksort($file); ksort($process);
        return ['env' => hash('sha256', serialize($file)), 'process' => hash('sha256', serialize($process))];
    }
}
