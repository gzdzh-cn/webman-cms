<?php
declare(strict_types=1);
namespace app\cms\support;

use app\cms\Paths;

/** Only immutable built-in templates can use archive reads; external themes remain real files. */
final class PackagedFiles
{
    public static function template(string $file, string $root): ?string
    {
        if(!str_starts_with($root,'phar://'))return null;
        $allowed=[Paths::cms('admin/view'),Paths::code('app/view/cms/system')];if(EmbeddedResources::has('template'))$allowed[]=Paths::templates();$trusted=false;
        foreach($allowed as$base)if($root===$base||str_starts_with($root,$base.'/'))$trusted=true;
        if(!$trusted||!str_starts_with($file,rtrim($root,'/').'/')||preg_match('#(?:^|/)\.{1,2}(?:/|$)#',$file)||str_contains($file,'\\')||str_contains($file,"\0")||!EmbeddedResources::realpath($file)||!is_file($file))throw new \RuntimeException('包内模板不存在或不在允许范围');
        return$file;
    }
}
