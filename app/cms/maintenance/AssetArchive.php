<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Small, strict tar.gz transport: no executable metadata or external archivers. */
final class AssetArchive
{
    public static function relative(string $name): string
    {
        if ($name === '' || strlen($name) > 1024 || strpbrk($name, "\\:\0\r\n") !== false || preg_match('/[\x00-\x1f\x7f]/', $name)) throw new \RuntimeException('资源路径无效');
        foreach (explode('/', $name) as $part) if ($part === '' || $part[0] === '.') throw new \RuntimeException('资源路径无效');
        return $name;
    }
    public static function path(string $path): string
    {
        if (!str_starts_with($path, '/') || str_contains($path, "\0")) throw new \RuntimeException('资源维护需要绝对路径');
        $current = '';
        foreach (explode('/', trim($path, '/')) as $part) {
            if ($part === '' || $part === '.' || $part === '..') throw new \RuntimeException('资源维护路径无效');
            $current .= '/' . $part;
            if (is_link($current)) throw new \RuntimeException('资源维护路径不能包含符号链接');
            if (file_exists($current) && $current !== rtrim($path, '/') && !is_dir($current)) throw new \RuntimeException('资源维护父路径不是目录');
        }
        return rtrim($path, '/');
    }
    public static function read(string $archive, string $sha256, array $expected, callable $receive, int $maxMember = 67108864): void
    {
        self::path($archive);
        if (!is_file($archive) || !hash_equals($sha256, (string)hash_file('sha256', $archive))) throw new \RuntimeException('资源归档 SHA256 不一致');
        $stream = @gzopen($archive, 'rb');
        if (!$stream) throw new \RuntimeException('无法读取资源归档');
        $seen = []; $metadata = []; $longName = null; $ended = false;
        try {
            while (true) {
                $header = self::take($stream, 512);
                if ($header === str_repeat("\0", 512)) {
                    if (self::take($stream, 512) !== str_repeat("\0", 512)) throw new \RuntimeException('资源归档结束标记无效');
                    while (!gzeof($stream)) { $tail = gzread($stream, 65536); if ($tail === false || trim($tail, "\0") !== '') throw new \RuntimeException('资源归档包含额外内容'); }
                    $ended = true; break;
                }
                $checksum = self::octal(substr($header, 148, 8));
                $checked = substr_replace($header, str_repeat(' ', 8), 148, 8);
                if (array_sum(unpack('C*', $checked)) !== $checksum) throw new \RuntimeException('资源归档头校验失败');
                $size = self::octal(substr($header, 124, 12));
                if ($size > $maxMember) throw new \RuntimeException('资源归档成员过大');
                $type = $header[156];
                $name = rtrim(substr($header, 0, 100), "\0");
                $prefix = rtrim(substr($header, 345, 155), "\0");
                if ($prefix !== '') $name = $prefix . '/' . $name;
                if ($type === 'x' || $type === 'L') {
                    if ($size > 65536 || $metadata || $longName !== null) throw new \RuntimeException('资源归档扩展头无效');
                    $bytes = self::take($stream, $size); self::padding($stream, $size);
                    if ($type === 'L') $longName = rtrim($bytes, "\0\n");
                    else $metadata = self::pax($bytes);
                    continue;
                }
                if ($type !== '0' && $type !== "\0") throw new \RuntimeException('资源归档只允许普通文件');
                if (trim(substr($header, 157, 100), "\0") !== '') throw new \RuntimeException('资源归档不能包含链接');
                $name = self::relative($metadata['path'] ?? $longName ?? $name); $metadata = []; $longName = null;
                if (isset($seen[$name]) || !isset($expected[$name])) throw new \RuntimeException('资源归档包含重复或未登记文件');
                $record = $expected[$name];
                if ($size !== ($record['size'] ?? $record['bytes'] ?? null)) throw new \RuntimeException('资源归档成员大小不一致：' . $name);
                $bytes = self::take($stream, $size); self::padding($stream, $size);
                if (!hash_equals($record['sha256'], hash('sha256', $bytes))) throw new \RuntimeException('资源归档成员 SHA256 不一致：' . $name);
                $seen[$name] = true; $receive($name, $bytes);
            }
            if (!$ended || $metadata || $longName !== null || count($seen) !== count($expected)) throw new \RuntimeException('资源归档成员清单不完整');
        } finally { gzclose($stream); }
    }
    private static function take($stream, int $length): string
    {
        $bytes = '';
        while (strlen($bytes) < $length) {
            $chunk = @gzread($stream, min(65536, $length - strlen($bytes)));
            if ($chunk === false || $chunk === '') throw new \RuntimeException('资源归档被截断或压缩流损坏');
            $bytes .= $chunk;
        }
        return $bytes;
    }
    private static function padding($stream, int $size): void
    {
        $length = (512 - $size % 512) % 512;
        if ($length && self::take($stream, $length) !== str_repeat("\0", $length)) throw new \RuntimeException('资源归档填充无效');
    }
    private static function octal(string $value): int
    {
        $value = trim($value, "\0 ");
        if ($value === '') return 0;
        if (!preg_match('/^[0-7]{1,11}$/D', $value)) throw new \RuntimeException('资源归档数字字段无效');
        return (int)octdec($value);
    }
    private static function pax(string $bytes): array
    {
        $result = [];
        while ($bytes !== '') {
            if (!preg_match('/^([1-9][0-9]*) /', $bytes, $m)) throw new \RuntimeException('资源归档 PAX 格式无效');
            $length = (int)$m[1];
            if ($length > strlen($bytes) || $length <= strlen($m[0]) || $bytes[$length - 1] !== "\n") throw new \RuntimeException('资源归档 PAX 长度无效');
            $entry = substr($bytes, strlen($m[0]), $length - strlen($m[0]) - 1); $bytes = substr($bytes, $length);
            if (!str_contains($entry, '=')) throw new \RuntimeException('资源归档 PAX 字段无效');
            [$key, $value] = explode('=', $entry, 2);
            if (!in_array($key, ['path','mtime','atime','ctime','uid','gid','uname','gname'], true) || isset($result[$key])) throw new \RuntimeException('资源归档 PAX 字段不受支持');
            $result[$key] = $value;
        }
        return $result;
    }
    /** Stable ustar entries, sorted paths, fixed owner/time/mode. */
    public static function write(string $archive, array $files, int $maxMember = 67108864): void
    {
        self::path($archive);
        $exclusive = @fopen($archive, 'xb');
        if (!$exclusive) throw new \RuntimeException('资源归档已存在或不可写');
        chmod($archive, 0600);
        $stream = $exclusive;
        try {
            if (!stream_filter_append($stream, 'zlib.deflate', STREAM_FILTER_WRITE, ['window'=>31,'level'=>9])) throw new \RuntimeException('无法创建资源压缩流');
            ksort($files);
            foreach ($files as $name => $source) {
                self::relative($name); self::path($source);
                if (!is_file($source) || filesize($source) > $maxMember) throw new \RuntimeException('资源归档输入无效');
                $bytes = file_get_contents($source); if (!is_string($bytes)) throw new \RuntimeException('无法读取资源归档输入');
                $short = $name; $prefix = '';
                if (strlen($short) > 100) {
                    $cut = false;
                    for ($i=0; $i<strlen($name); $i++) if ($name[$i]==='/' && $i<=155 && strlen($name)-$i-1<=100) $cut=$i;
                    if ($cut === false) throw new \RuntimeException('资源归档路径过长');
                    $prefix = substr($name, 0, $cut); $short = substr($name, $cut + 1);
                    if (strlen($short) > 100 || strlen($prefix) > 155) throw new \RuntimeException('资源归档路径过长');
                }
                $header = str_pad($short, 100, "\0") . sprintf('%07o', 0644) . "\0" . str_repeat('0000000' . "\0", 2)
                    . sprintf('%011o', strlen($bytes)) . "\0" . '00000000000' . "\0" . str_repeat(' ', 8) . '0' . str_repeat("\0", 100)
                    . "ustar\0" . '00' . str_repeat("\0", 32 + 32 + 8 + 8) . str_pad($prefix, 155, "\0") . str_repeat("\0", 12);
                $header = substr_replace($header, sprintf('%06o', array_sum(unpack('C*', $header))) . "\0 ", 148, 8);
                self::put($stream, $header); self::put($stream, $bytes);
                self::put($stream, str_repeat("\0", (512 - strlen($bytes) % 512) % 512));
            }
            self::put($stream, str_repeat("\0", 1024)); fclose($stream); $stream = null;
        } catch (\Throwable $error) { if (is_resource($stream)) fclose($stream); @unlink($archive); throw $error; }
    }
    private static function put($stream, string $bytes): void
    {
        $offset = 0;
        while ($offset < strlen($bytes)) { $written = fwrite($stream, substr($bytes, $offset, 65536)); if (!$written) throw new \RuntimeException('资源归档写入失败'); $offset += $written; }
    }
}
