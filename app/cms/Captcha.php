<?php
namespace app\cms;

use support\Request;
use support\Response;

/** Image endpoint and session contract used by unchanged Eyou form JavaScript. */
final class Captcha
{
    public static function image(Request $request, CmsContext $context): Response
    {
        $type = preg_replace('/[^\w-]/', '', (string)$request->input('type', 'default'));
        $token = preg_replace('/[^\w-]/', '', (string)$request->input('token', ''));
        $options = array_replace($context->getConfig('captcha.default', []), $context->getConfig('captcha.' . $type . '.config', []));
        $length = max(3, min(6, (int)($options['length'] ?? 4)));
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $challenge = '';
        for ($i = 0; $i < $length; $i++) $challenge .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        $request->session()->set('captcha.' . $type . '.' . ($token ?: $type), $challenge);
        $width = max(100, min(400, (int)($options['imageW'] ?: $length * 28)));
        $height = max(32, min(120, (int)($options['imageH'] ?: 40)));
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 245, 248, 250));
        for ($i = 0; $i < 8; $i++) imageline($image, random_int(0, $width - 1), random_int(0, $height - 1), random_int(0, $width - 1), random_int(0, $height - 1), imagecolorallocate($image, random_int(160, 220), random_int(160, 220), random_int(160, 220)));
        for ($i = 0; $i < $length; $i++) imagestring($image, 5, (int)(($width - 12) / $length * $i) + 8, random_int(5, max(5, $height - 22)), $challenge[$i], imagecolorallocate($image, random_int(10, 100), random_int(10, 100), random_int(10, 100)));
        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        return response($bytes, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'no-store, no-cache, must-revalidate']);
    }
}
