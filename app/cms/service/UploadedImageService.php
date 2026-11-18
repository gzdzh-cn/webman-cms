<?php
declare(strict_types=1);
namespace app\cms\service;

use RuntimeException;

/** Validate uploads and reuse the isolated Eyou GD renderer for resized images. */
class UploadedImageService
{
    public function resizeToFit(string $path, int $maxWidth = 1170, int $maxHeight = 1170): void
    {
        $bytes = file_get_contents($path);
        $info = $bytes === false ? false : @getimagesizefromstring($bytes);
        $image = $info && function_exists('imagecreatefromstring') ? @imagecreatefromstring($bytes) : false;
        if (!$image || !$info) { throw new RuntimeException('Invalid image upload or unavailable GD extension'); }
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $expected = image_type_to_extension($info[2], false);
        if ($extension !== $expected && !($expected === 'jpeg' && $extension === 'jpg')) { throw new RuntimeException('Image extension does not match decoded format'); }
        // Preserve small files byte-for-byte, including GIF animation and EXIF.
        if ($info[0] <= $maxWidth && $info[1] <= $maxHeight) { return; }
        unset($image);
        $temporary = tempnam(dirname($path), '.resize-');
        if ($temporary === false) { throw new RuntimeException('Cannot create resized image'); }
        $memoryLimit = ini_get('memory_limit'); $timeLimit = ini_get('max_execution_time');
        try {
            // The existing compatibility renderer resizes every GIF frame.
            $renderer = \app\cms\compat\Image::open($path);
            $renderer->thumb($maxWidth, $maxHeight)->save($temporary, null, 90);
            unset($renderer);
            // Resizing raw JPEG pixels must retain the original EXIF orientation.
            if ($info[2] === IMAGETYPE_JPEG) {
                $exif = $this->jpegExifSegments($bytes);
                if ($exif !== '') {
                    $resized = file_get_contents($temporary);
                    if ($resized === false || file_put_contents($temporary, substr($resized, 0, 2) . $exif . substr($resized, 2)) === false) { throw new RuntimeException('Cannot preserve JPEG orientation'); }
                }
            }
            chmod($temporary, fileperms($path) & 0777);
            if (!getimagesize($temporary) || !rename($temporary, $path)) { throw new RuntimeException('Cannot save resized image'); }
        } finally {
            ini_set('memory_limit', $memoryLimit); ini_set('max_execution_time', $timeLimit);
            if (is_file($temporary)) { unlink($temporary); }
        }
    }

    /** Create the backend's existing centered avatar sizes without legacy vendor APIs. */
    public function squareThumbnails(string $source, array $destinations): void
    {
        $bytes = file_get_contents($source); $info = $bytes === false ? false : @getimagesizefromstring($bytes);
        $image = $info && function_exists('imagecreatefromstring') ? @imagecreatefromstring($bytes) : false;
        if (!$info || !$image) { throw new RuntimeException('Invalid avatar image'); }
        foreach ($destinations as $path => $size) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION)); $expected = image_type_to_extension($info[2], false);
            if ($extension !== $expected && !($expected === 'jpeg' && $extension === 'jpg')) { throw new RuntimeException('Avatar extension does not match decoded format'); }
        }
        $side = min($info[0], $info[1]); $x = (int)(($info[0] - $side) / 2); $y = (int)(($info[1] - $side) / 2);
        foreach ($destinations as $path => $size) {
            $canvas = imagecreatetruecolor($size, $size);
            imagealphablending($canvas, false); imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            if (!imagecopyresampled($canvas, $image, 0, 0, $x, $y, $size, $size, $side, $side)) { throw new RuntimeException('Cannot resize avatar image'); }
            $written = match ($info[2]) { IMAGETYPE_JPEG => imagejpeg($canvas,$path,90), IMAGETYPE_PNG => imagepng($canvas,$path), IMAGETYPE_GIF => imagegif($canvas,$path), default => false };
            if (!$written) { throw new RuntimeException('Cannot save avatar image'); }
        }
    }

    /** Retain orientation metadata when a new-upload transformation re-encodes JPEG pixels. */
    public function restoreJpegExif(string $originalBytes, string $destination): void
    {
        $exif=$this->jpegExifSegments($originalBytes);
        if($exif==='')return;
        $bytes=file_get_contents($destination);
        if($bytes===false||substr($bytes,0,2)!=="\xff\xd8"||file_put_contents($destination,substr($bytes,0,2).$exif.substr($bytes,2))===false)throw new RuntimeException('Cannot preserve JPEG orientation');
    }

    private function jpegExifSegments(string $bytes): string
    {
        $result = ''; $offset = 2; $length = strlen($bytes);
        while ($offset + 4 <= $length && ord($bytes[$offset]) === 0xff) {
            $marker = ord($bytes[$offset + 1]);
            if ($marker === 0xda || $marker === 0xd9) { break; }
            $size = unpack('n', substr($bytes, $offset + 2, 2))[1];
            if ($size < 2 || $offset + 2 + $size > $length) { break; }
            if ($marker === 0xe1 && substr($bytes, $offset + 4, 6) === "Exif\0\0") { $result .= substr($bytes, $offset, $size + 2); }
            $offset += $size + 2;
        }
        return $result;
    }
}
