<?php
declare(strict_types=1);
namespace app\cms\template;

use app\cms\CmsContext;
use app\cms\ThemeCatalog;
use app\cms\UploadPaths;
use app\cms\compat\Request;

/** Bounded helpers callable by templates, separate from the unrestricted PHP API. */
final class TemplateRuntime
{
    public static function request(string $source, string $key): mixed
    {
        TemplateCapabilities::requestKey($source, $key);
        return Request::instance()->param($key);
    }

    public static function url($route = '', $parameters = [], $suffix = true, $domain = false, $mode = null, $format = null, $inlet = null): string
    {
        if (!is_string($route) || (!is_string($parameters) && !is_array($parameters))
            || (!is_bool($suffix) && !is_string($suffix)) || (!is_bool($domain) && !is_string($domain))) throw new \InvalidArgumentException('模板 URL 参数格式无效');
        if (preg_match('/[\x00-\x20<>"\'\\\\]/', $route) || str_contains($route, '..') || str_contains($route, '://')) throw new \InvalidArgumentException('模板 URL 路由无效');
        // Legacy links can be built, but the dispatcher retains its disabled-feature boundary.
        return \app\cms\UrlGenerator::build($route, $parameters, $suffix, $domain, $mode, $format, $inlet);
    }

    public static function thumb($image = '', $width = '', $height = '', $mode = ''): string
    {
        if (!is_string($image)) throw new \InvalidArgumentException('模板缩略图路径无效');
        foreach ([$width, $height] as $size) if ($size !== '' && (!is_scalar($size) || !preg_match('/^[0-9]+$/D', (string)$size) || (int)$size > 4096)) throw new \InvalidArgumentException('模板缩略图尺寸必须在0至4096之间');
        if ($mode !== '' && (!is_scalar($mode) || !preg_match('/^[0-6]$/D', (string)$mode))) throw new \InvalidArgumentException('模板缩略图模式无效');
        if ($image === '') return '';
        if (preg_match('/[\x00-\x20<>"\'\\\\]/', $image)) throw new \InvalidArgumentException('模板缩略图路径无效');
        // Remote images are emitted unchanged; no request or image decoder touches them.
        if (preg_match('~^https?://~i', $image)) {
            if (!filter_var($image, FILTER_VALIDATE_URL) || parse_url($image, PHP_URL_USER) !== null) throw new \InvalidArgumentException('远程图片地址无效');
            return $image;
        }
        // The registered no-image asset already has a versioned public URL.
        // Emit that exact URL unchanged; shared assets and arbitrary queries remain forbidden.
        if ($image === \app\cms\AssetManifest::url('/public/static/common/images/not_adv.jpg')) return $image;
        $relative = rawurldecode($image);
        if (preg_match('~(?:^|/)\.{1,2}(?:/|$)|[:%?\#\x00\\\\]~', $relative)) throw new \InvalidArgumentException('模板缩略图路径无效');
        $relative = ltrim($relative, '/');
        $base = trim((string)(CmsContext::current()->global['web_cmspath'] ?? ''), '/');
        if ($base !== '' && str_starts_with($relative, $base.'/')) $relative = substr($relative, strlen($base) + 1);
        if (preg_match('~^(?:public/)?(?:uploads|upload)/~', $relative)) {
            $physical = UploadPaths::physical($relative);
            $allowed = realpath(public_path('uploads'));
        } elseif (str_starts_with($relative, 'template/')) {
            $physical = ThemeCatalog::physical($relative);
            $allowed = ThemeCatalog::root(explode('/', CmsContext::current()->theme)[0]);
        } else throw new \InvalidArgumentException('模板缩略图仅支持本站上传和当前主题图片');
        $actual = realpath($physical);
        if (!$actual) return $image;
        if (!$allowed || !str_starts_with($actual, rtrim($allowed, '/').DIRECTORY_SEPARATOR) || !is_file($actual)) throw new \InvalidArgumentException('模板缩略图超出当前资源目录');
        for ($p = $physical; dirname($p) !== $p && $p !== '.'; $p = dirname($p)) if (is_link($p)) throw new \InvalidArgumentException('模板缩略图不能使用符号链接');
        if (filesize($actual) > 20 * 1024 * 1024) throw new \InvalidArgumentException('模板图片超过20MiB');
        $info = @getimagesize($actual);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP,IMAGETYPE_GIF,IMAGETYPE_BMP], true) || $info[0] * $info[1] > 16000000) throw new \InvalidArgumentException('模板图片格式或像素数量不受支持');
        // Preserve animations without invoking the legacy unbounded GIF decoder.
        if ($info[2] === IMAGETYPE_GIF) return $image;
        $config = (array)\tpCache('thumb');
        if ($width === '' && $height === '' && $mode === '' && empty($config['thumb_open'])) return $image;
        $width = (int)($width ?: ($config['thumb_width'] ?? 300));
        $height = (int)($height ?: ($config['thumb_height'] ?? 300));
        if ($width === 0) $width = $info[0];
        if ($height === 0) $height = $info[1];
        if ($width < 1 || $height < 1 || $width > 4096 || $height > 4096 || $info[0]*$info[1]+$width*$height > 24000000) throw new \InvalidArgumentException('模板缩略图资源消耗超过限制');
        $mode = (int)($mode ?: ($config['thumb_mode'] ?? 1));
        if ($mode < 1 || $mode > 6) throw new \InvalidArgumentException('模板缩略图模式无效');
        $name = hash('sha256', json_encode(['template-thumb-v1', hash_file('sha256', $actual), $width, $height, $mode]));
        $url = '/uploads/thumb/template/'.$width.'_'.$height.'/'.$name.'.png';
        $target = UploadPaths::physical($url);
        if (is_link($target)) throw new \InvalidArgumentException('模板缩略图不能使用符号链接');
        if (is_file($target)) return (string)\get_absolute_url($url);
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0755, true) && !is_dir(dirname($target))) throw new \RuntimeException('无法创建模板缩略图目录');
        // Revalidate after mkdir, including existing in-tree symlinks.
        $target = UploadPaths::physical($url);
        for ($p = dirname($target); dirname($p) !== $p; $p = dirname($p)) if (is_link($p)) throw new \InvalidArgumentException('模板缩略图不能使用符号链接');
        $source = $output = null; $temporary = null;
        try {
            $source = imagecreatefromstring(file_get_contents($actual));
            if (!$source) throw new \RuntimeException('无法解码模板图片');
            $sw = $info[0]; $sh = $info[1]; $sx = $sy = $dx = $dy = 0;
            if ($mode === 1) { $dw = $width; $dh = $height; } // upstream mode 1 means fixed dimensions
            elseif ($mode === 2) {
                $scale = min($width/$sw, $height/$sh, 1); $dw = max(1, (int)round($sw*$scale)); $dh = max(1, (int)round($sh*$scale));
                $dx = (int)(($width-$dw)/2); $dy = (int)(($height-$dh)/2);
            } elseif ($mode === 6) { $dw = $width; $dh = $height; }
            else {
                $scale = max($width/$sw, $height/$sh); $cropW = min($sw, $width/$scale); $cropH = min($sh, $height/$scale);
                $sx = $mode === 4 ? 0 : ($mode === 5 ? $sw-$cropW : ($sw-$cropW)/2);
                $sy = $mode === 4 ? 0 : ($mode === 5 ? $sh-$cropH : ($sh-$cropH)/2);
                $sw = $cropW; $sh = $cropH; $dw = $width; $dh = $height;
            }
            $output = imagecreatetruecolor($width, $height);
            imagealphablending($output, false); imagesavealpha($output, true);
            imagefill($output, 0, 0, imagecolorallocatealpha($output, 255, 255, 255, 127));
            imagecopyresampled($output, $source, $dx, $dy, (int)$sx, (int)$sy, $dw, $dh, (int)$sw, (int)$sh);
            $temporary = tempnam(dirname($target), '.thumb-');
            if (!$temporary || !imagepng($output, $temporary) || !rename($temporary, $target)) throw new \RuntimeException('无法保存模板缩略图');
            chmod($target, 0644);
            return (string)\get_absolute_url($url);
        } finally {
            if ($source) imagedestroy($source);
            if ($output) imagedestroy($output);
            if ($temporary && is_file($temporary)) unlink($temporary);
        }
    }
}
