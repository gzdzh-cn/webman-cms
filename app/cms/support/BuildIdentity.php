<?php
declare(strict_types=1);
namespace app\cms\support;
final class BuildIdentity
{
    public static function version(): string
    {
        static $id=null;
        if($id===null){$file=\app\cms\Paths::resource('binary-build.json');$id=is_file($file)?(json_decode(file_get_contents($file),true,16,JSON_THROW_ON_ERROR)['build_id']??throw new \RuntimeException('构建身份缺失')):'source';}
        return$id;
    }
}
