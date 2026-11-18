<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\UploadPaths;
use InvalidArgumentException;
use RuntimeException;

/** Validate uploads in a private staging directory before publishing a random name. */
final class SecureUploadService
{
    private const IMAGES = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','bmp'=>'image/bmp','ico'=>'image/vnd.microsoft.icon','avif'=>'image/avif'];
    private const TYPES = [
        'pdf'=>['application/pdf'], 'txt'=>['text/plain'], 'csv'=>['text/plain','text/csv'],
        'docx'=>['application/zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xlsx'=>['application/zip','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'pptx'=>['application/zip','application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'mp3'=>['audio/mpeg'], 'wav'=>['audio/x-wav','audio/wav','audio/vnd.wave'], 'ogg'=>['audio/ogg','video/ogg','application/ogg'],
        'mp4'=>['video/mp4','application/mp4'], 'webm'=>['video/webm','audio/webm'], 'mov'=>['video/quicktime'],
    ];

    /** Callback receives a private validated file; it is always removed after use. */
    public static function staged($file, string $extension, int $limit, callable $callback): mixed
    {
        $limit=min($limit,\app\cms\config\DeploymentConfig::get('limits.upload_bytes'));
        $extension = strtolower($extension);
        $name = (string)$file->getUploadName();
        if (!$file->isValid() || $limit < 1 || $file->getSize() <= 0 || $file->getSize() > $limit) throw new InvalidArgumentException('上传文件无效或超过大小限制');
        if ($name !== 'blob' && (str_contains($name, '/') || str_contains($name, '\\') || preg_match('/[\x00-\x1f\x7f]/', $name) || substr_count($name, '.') !== 1 || strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== $extension)) throw new InvalidArgumentException('上传文件名无效，不能包含多重扩展名');
        if (!isset(self::IMAGES[$extension]) && !isset(self::TYPES[$extension])) throw new InvalidArgumentException('该文件格式暂不支持安全上传');
        $tempRoot = realpath(sys_get_temp_dir()); $public = \app\cms\support\EmbeddedResources::realpath(\app\cms\Paths::publicRoot());
        if (!$tempRoot || !$public || $tempRoot === $public || str_starts_with($tempRoot, $public . DIRECTORY_SEPARATOR)) throw new RuntimeException('上传暂存目录不安全');
        $directory = $tempRoot . '/cms-upload-' . bin2hex(random_bytes(16));
        if (!mkdir($directory, 0700)) throw new RuntimeException('无法创建上传暂存目录');
        $path = $directory . '/data';
        try {
            $file->move($path);
            clearstatcache(true, $path);
            if (!is_file($path) || is_link($path) || filesize($path) <= 0 || filesize($path) > $limit) throw new InvalidArgumentException('上传文件大小无效');
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            if (isset(self::IMAGES[$extension])) {
                $info = @getimagesize($path); $expected = self::IMAGES[$extension];
                if (!$info || (($info['mime'] ?? '') !== $expected && !($extension === 'ico' && ($info['mime'] ?? '') === 'image/x-icon') && !($extension === 'bmp' && ($info['mime'] ?? '') === 'image/x-ms-bmp'))) throw new InvalidArgumentException('图片内容与扩展名不匹配');
                if ($extension !== 'ico') {
                    if (!function_exists('imagecreatefromstring') || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > \app\cms\config\DeploymentConfig::get('limits.image_pixels')) throw new InvalidArgumentException('无法安全解码图片');
                    $bytes = file_get_contents($path); $image = $bytes === false ? false : @imagecreatefromstring($bytes);
                    if (!$image) throw new InvalidArgumentException('图片内容无效');
                    imagedestroy($image); unset($bytes);
                }
                $mime = $info['mime'];
            } elseif (!in_array($mime, self::TYPES[$extension], true)) throw new InvalidArgumentException('文件内容与扩展名不匹配');
            $handle = fopen($path, 'rb'); $tail = '';
            if (!$handle) throw new RuntimeException('无法检查上传文件');
            try {
                while (!feof($handle)) {
                    $chunk = fread($handle, 65536); if ($chunk === false) throw new RuntimeException('无法检查上传文件');
                    $sample = $tail . $chunk;
                    if (preg_match('/<\?php(?=\s|\/\*|\/\/)|<\?=\s*(?:\$[a-z_][a-z0-9_]*|[a-z_][a-z0-9_]*\s*\()|<\?=\s*[0-9\"\'][^?]{0,512}\?>|<\?\s*(?:echo|eval|include|require)\b[^?]{0,512}\?>|__HALT_COMPILER\s*\(/i', $sample)) throw new InvalidArgumentException('文件包含不允许的脚本内容');
                    $tail = substr($sample, -1024);
                }
            } finally { fclose($handle); }
            if (in_array($extension, ['docx','xlsx','pptx'], true)) self::checkOffice($path, $extension);
            return $callback($path, (string)$mime);
        } finally { if (is_file($path) || is_link($path)) @unlink($path); @rmdir($directory); }
    }

    private static function checkOffice(string $path, string $extension): void
    {
        if (!class_exists(\ZipArchive::class)) throw new InvalidArgumentException('校验 Office 文件需要 Zip 扩展');
        $zip = new \ZipArchive(); if ($zip->open($path, \ZipArchive::CHECKCONS) !== true) throw new InvalidArgumentException('Office 文件无效');
        try {
            $required = match ($extension) {'docx'=>'word/document.xml','xlsx'=>'xl/workbook.xml','pptx'=>'ppt/presentation.xml'};
            if ($zip->numFiles > 10000 || $zip->locateName('[Content_Types].xml') === false || $zip->locateName($required) === false) throw new InvalidArgumentException('Office 文件结构无效');
            $total = 0;
            for ($i=0; $i<$zip->numFiles; $i++) {
                $stat = $zip->statIndex($i); $name = $stat['name'] ?? ''; $total += (int)($stat['size'] ?? 0);
                $zip->getExternalAttributesIndex($i, $os, $attributes); $type = ($attributes >> 16) & 0170000;
                if ($total > 100*1024*1024 || $name === '' || str_starts_with($name, '/') || str_contains($name, '\\') || preg_match('#(?:^|/)\.\.(?:/|$)|[\x00-\x1f:]|(?:vbaProject\.bin|\.(?:php\d*|phtml|pht|phar|exe|dll|js|vbs|zip|rar|7z))$#i', $name) || ($type !== 0 && $type !== 0100000 && $type !== 0040000)) throw new InvalidArgumentException('Office 文件包含不支持的内容');
            }
        } finally { $zip->close(); }
    }

    /** Copy into the destination filesystem, then publish without overwriting. */
    public static function publish(string $staged, string $relativeDirectory, string $name): string
    {
        if (!preg_match('/^[a-f0-9]{32}(?:\.(?:lg|md|sm))?\.[a-z0-9]+$/D', $name)) throw new InvalidArgumentException('上传文件名无效');
        $directory = UploadPaths::ensureDirectory($relativeDirectory);
        $target = $directory . DIRECTORY_SEPARATOR . $name;
        $temporary = $directory . DIRECTORY_SEPARATOR . '.upload-' . bin2hex(random_bytes(16));
        $out = @fopen($temporary, 'xb'); if (!$out) throw new RuntimeException('无法创建上传文件');
        try {
            $in = fopen($staged, 'rb'); if (!$in) throw new RuntimeException('无法读取暂存文件');
            try { if (stream_copy_to_stream($in, $out) !== filesize($staged) || !fflush($out)) throw new RuntimeException('无法写入上传文件'); }
            finally { fclose($in); }
            fclose($out); $out = null;
            if (UploadPaths::ensureDirectory($relativeDirectory) !== $directory || !@link($temporary, $target)) throw new RuntimeException('无法发布上传文件');
            return $target;
        } finally { if (is_resource($out)) fclose($out); @unlink($temporary); }
    }

    public static function store($file, string $extension, int $limit, string $relativeDirectory): array
    {
        return self::staged($file, $extension, $limit, static function (string $staged, string $mime) use ($extension, $relativeDirectory): array {
            $name = bin2hex(random_bytes(16)) . '.' . strtolower($extension);
            return ['path'=>self::publish($staged, $relativeDirectory, $name), 'url'=>'/' . $relativeDirectory . '/' . $name, 'mime'=>$mime];
        });
    }
}
