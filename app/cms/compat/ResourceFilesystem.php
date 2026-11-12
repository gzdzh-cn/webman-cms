<?php
namespace app\cms\compat;

use app\cms\UploadPaths;
use app\cms\ThemeCatalog;

/** Scoped adapters for legacy helper/template I/O; never replace PHP globals. */
final class ResourceFilesystem
{
    public const FUNCTIONS = ['file_exists', 'is_file', 'is_dir', 'is_readable', 'is_writable', 'filesize', 'filemtime', 'filetype', 'stat', 'realpath', 'getimagesize', 'exif_imagetype', 'mime_content_type', 'file_get_contents', 'file_put_contents', 'fopen', 'readfile', 'mkdir', 'unlink', 'rmdir', 'copy', 'rename', 'chmod', 'glob', 'md5_file', 'sha1_file', 'hash_file', 'clearstatcache'];

    private static function path($path) { if (is_string($path)) {
            $legacyRoot = rtrim(base_path(), '/') . '/data/';
            if (str_starts_with($path, $legacyRoot)) $path = \app\cms\Paths::legacyData(substr($path, strlen($legacyRoot)));
            elseif (str_starts_with($path, './data/')) $path = \app\cms\Paths::legacyData(substr($path, 7));
            elseif (str_starts_with($path, 'data/')) $path = \app\cms\Paths::legacyData(substr($path, 5));
        }
        return ThemeCatalog::physical(UploadPaths::physical($path)); }

    /** Registered assets are read aliases only; never redirect a mutation. */
    private static function readPath($path)
    {
        return \app\cms\AssetManifest::readPath($path) ?? self::path($path);
    }

    private static function writePath($path) { $file=self::path($path); if(is_string($file))\app\cms\support\EmbeddedResources::assertWritable($file); return $file; }

    /** Only language derivatives with the exact generated name scheme are virtual. */
    private static function derivedPath($path): ?string
    {
        $path = self::readPath($path);
        if (!is_string($path)) return null;
        $prefix = \app\cms\Paths::runtime('cache/assets/');
        if (!str_starts_with($path, $prefix)) return null;
        return preg_match('/^(?:foreign|ey-global)-[a-f0-9]{64}\.js$/D', substr($path, strlen($prefix))) ? $path : null;
    }
    private static function derivedBytes(string $path): string
    {
        return \app\cms\service\LanguageAssetService::readDerived($path);
    }
    private static function derivedExists(string $path): bool
    {
        try { self::derivedBytes($path); return true; } catch (\RuntimeException) { return false; }
    }
    private static function derivedStream(string $path)
    {
        $bytes = self::derivedBytes($path);
        $stream = fopen('php://memory', 'w+b');
        if ($stream === false) throw new \RuntimeException('无法打开语言资源内存流');
        if (fwrite($stream, $bytes) !== strlen($bytes) || !rewind($stream)) {
            fclose($stream);
            throw new \RuntimeException('无法读取语言资源');
        }
        return $stream;
    }

    public static function file_exists($filename) { $path = self::derivedPath($filename); return $path !== null ? self::derivedExists($path) : \file_exists(self::readPath($filename)); }
    public static function is_file($filename) { $path = self::derivedPath($filename); return $path !== null ? self::derivedExists($path) : \is_file(self::readPath($filename)); }
    public static function is_dir($filename) { return \is_dir(self::readPath($filename)); }
    public static function is_readable($filename) { $path = self::derivedPath($filename); return $path !== null ? self::derivedExists($path) : \is_readable(self::readPath($filename)); }
    public static function is_writable($filename) { return \is_writable(self::path($filename)); }
    public static function filesize($filename) { $path = self::derivedPath($filename); return $path !== null ? strlen(self::derivedBytes($path)) : \filesize(self::readPath($filename)); }
    public static function filemtime($filename) { return \filemtime(self::readPath($filename)); }
    public static function filetype($filename) { return \filetype(self::readPath($filename)); }
    public static function stat($filename) { return \stat(self::readPath($filename)); }
    public static function realpath($filename) { $path = self::derivedPath($filename); return $path !== null ? (self::derivedExists($path) ? $path : false) : \app\cms\support\EmbeddedResources::realpath(self::readPath($filename)); }
    public static function getimagesize($filename, &$image_info = null) { return \getimagesize(self::readPath($filename), $image_info); }
    public static function exif_imagetype($filename) { return \exif_imagetype(self::readPath($filename)); }
    public static function mime_content_type($filename) { return \mime_content_type(self::readPath($filename)); }
    public static function file_get_contents($filename, ...$arguments)
    {
        $path = self::derivedPath($filename);
        if ($path === null) return \file_get_contents(self::readPath($filename), ...$arguments);
        $stream = self::derivedStream($path);
        try { return stream_get_contents($stream, $arguments[3] ?? null, $arguments[2] ?? -1); }
        finally { fclose($stream); }
    }
    public static function file_put_contents($filename, ...$arguments) { return \file_put_contents(self::writePath($filename), ...$arguments); }
    public static function fopen($filename, ...$arguments)
    {
        $read = in_array($arguments[0] ?? '', ['r', 'rb', 'rt'], true);
        $path = $read ? self::derivedPath($filename) : null;
        return $path !== null ? self::derivedStream($path) : \fopen($read ? self::readPath($filename) : self::writePath($filename), ...$arguments);
    }
    public static function readfile($filename, ...$arguments)
    {
        $path = self::derivedPath($filename);
        if ($path === null) return \readfile(self::readPath($filename), ...$arguments);
        $bytes = self::derivedBytes($path); echo $bytes; return strlen($bytes);
    }
    public static function mkdir($directory, ...$arguments) { return \mkdir(self::writePath($directory), ...$arguments); }
    public static function unlink($filename, ...$arguments) { return \unlink(self::writePath($filename), ...$arguments); }
    public static function rmdir($directory, ...$arguments) { return \rmdir(self::writePath($directory), ...$arguments); }
    public static function copy($from, $to, ...$arguments) { return \copy(self::readPath($from), self::writePath($to), ...$arguments); }
    public static function rename($from, $to, ...$arguments) { return \rename(self::writePath($from), self::writePath($to), ...$arguments); }
    public static function chmod($filename, ...$arguments) { return \chmod(self::writePath($filename), ...$arguments); }
    public static function glob($pattern, $flags = 0) { return ThemeCatalog::isTemplatePath($pattern) ? ThemeCatalog::glob($pattern, $flags) : UploadPaths::glob($pattern, $flags); }
    public static function md5_file($filename, ...$arguments) { $path = self::derivedPath($filename); return $path !== null ? md5(self::derivedBytes($path), $arguments[0] ?? false) : \md5_file(self::readPath($filename), ...$arguments); }
    public static function sha1_file($filename, ...$arguments) { $path = self::derivedPath($filename); return $path !== null ? sha1(self::derivedBytes($path), $arguments[0] ?? false) : \sha1_file(self::readPath($filename), ...$arguments); }
    public static function hash_file($algo, $filename, ...$arguments) { $path = self::derivedPath($filename); return $path !== null ? hash($algo, self::derivedBytes($path), $arguments[0] ?? false) : \hash_file($algo, self::readPath($filename), ...$arguments); }
    public static function clearstatcache($clear_realpath_cache = false, $filename = '') { \clearstatcache($clear_realpath_cache, self::readPath($filename)); }

    /** Replace only resolved native function callees; preserve all other bytes. */
    public static function rewrite(string $php): string
    {
        if (!preg_match('/\\b(?:' . implode('|', self::FUNCTIONS) . ')\\b/i', $php)) return $php;
        $parser = (new \PhpParser\ParserFactory())->createForHostVersion();
        try { $nodes = $parser->parse($php); } catch (\PhpParser\Error $error) { return $php; }
        if (!$nodes) return $php;
        $nodes = (new \PhpParser\NodeTraverser(new \PhpParser\NodeVisitor\NameResolver(null, ['replaceNodes' => false])))->traverse($nodes);
        $finder = new \PhpParser\NodeFinder(); $edits = [];
        foreach ($finder->findInstanceOf($nodes, \PhpParser\Node\Expr\FuncCall::class) as $call) {
            if (!$call->name instanceof \PhpParser\Node\Name) continue;
            $name = $call->name;
            $resolved = $name->getAttribute('resolvedName');
            $candidate = strtolower(($resolved ?? $name)->toString());
            if (!in_array($candidate, self::FUNCTIONS, true)) continue;
            $replacement = '\\' . __CLASS__ . '::' . $candidate;
            $fallback = $name->getAttribute('namespacedName');
            if ($resolved === null && $fallback !== null && str_contains($fallback->toString(), '\\')) {
                $local = $fallback->toString();
                // PHP resolves unqualified namespace functions at execution time.
                // Keep that fallback even if another file defines the function later.
                $replacement = '(\\function_exists(' . var_export($local, true) . ') ? ' . var_export($local, true)
                    . ' : ' . var_export(__CLASS__ . '::' . $candidate, true) . ')';
            }
            $edits[] = [$name->getStartFilePos(), $name->getEndFilePos() + 1, $replacement];
        }
        usort($edits, static fn(array $a, array $b): int => $b[0] <=> $a[0]);
        foreach ($edits as [$start, $end, $replacement]) $php = substr_replace($php, $replacement, $start, $end - $start);
        return $php;
    }
}
