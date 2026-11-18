<?php
declare(strict_types=1);
namespace app\cms\support;
class CaptchaBuilder extends \Webman\Captcha\CaptchaBuilder
{
    protected function getFontPath($font) { return NativeFont::path($font); }
}
