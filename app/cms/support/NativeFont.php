<?php
declare(strict_types=1);
namespace app\cms\support;
use app\cms\Paths;

/** FreeType requires a native filename. Temporary, private and process-owned. */
final class NativeFont
{
    private static array $files=[];
    public static function path(string $file): string
    {
        if (!str_starts_with($file,'phar://')) return $file;
        if ($file!==Paths::resources('fonts/hgzb.ttf') && !preg_match('#^'.preg_quote(Paths::code('vendor/webman/captcha/src/Font/'),'#').'captcha[0-4]\.ttf$#D',$file)) throw new \RuntimeException('Unregistered native font');
        if(isset(self::$files[$file]) && is_file(self::$files[$file])) return self::$files[$file];
        $bytes=file_get_contents($file);
        if(!is_string($bytes)||strlen($bytes)>8388608)throw new \RuntimeException('Native font unavailable');
        $temp=tempnam(sys_get_temp_dir(),'cms-font-');
        if($temp===false)throw new \RuntimeException('Unable to allocate native font');
        chmod($temp,0600);
        if(file_put_contents($temp,$bytes)!==strlen($bytes)){unlink($temp);throw new \RuntimeException('Unable to prepare native font');}
        self::$files[$file]=$temp;
        register_shutdown_function(static function()use($temp){if(is_file($temp)&&!is_link($temp))unlink($temp);});
        return $temp;
    }
}
