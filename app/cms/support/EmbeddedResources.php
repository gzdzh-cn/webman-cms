<?php
declare(strict_types=1);
namespace app\cms\support;

/** Only paths registered in this executable's build manifest may use archive IO. */
final class EmbeddedResources
{
    public const YAML = ['config.yaml', 'config/config.yaml', 'config/manifest/config.yaml'];
    public const OPTIONAL = ['public', 'template', ...self::YAML];
    private static ?array $manifest = null;
    public static function binary(): bool { return \Phar::running(false) !== ''; }
    public static function code(): string { return dirname(__DIR__, 3); }
    public static function manifest(): array
    {
        if (self::$manifest === null) {
            $file=self::code().'/app/cms/resources/binary-build.json';
            self::$manifest=self::binary() && is_file($file) ? json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR) : [];
        }
        return self::$manifest;
    }
    public static function has(string $root): bool { return in_array($root,self::manifest()['embedded_resources']??[],true); }
    public static function yaml(): ?string
    {
        foreach(self::YAML as $file) if(self::has($file)) return self::code().'/'.$file;
        return null;
    }
    public static function relative(string $file): ?string
    {
        $prefix=self::code().'/';
        if(!self::binary() || !str_starts_with($file,$prefix))return null;
        $relative=substr($file,strlen($prefix));
        if(str_contains($relative,"\0")||str_contains($relative,'\\')||str_contains($relative,':')||preg_match('#(?:^|/)\.{1,2}(?:/|$)#',$relative))return null;
        return $relative;
    }
    public static function realpath(string $file): string|false
    {
        if(!str_contains($file,'://'))return realpath($file);
        $relative=self::relative($file);
        if($relative===null)return false;
        if(isset(self::manifest()['files'][$relative]) && is_file($file))return $file;
        if(is_dir($file))foreach(self::manifest()['files']??[] as $path=>$_)if(str_starts_with($path,rtrim($relative,'/').'/'))return rtrim($file,'/');
        return false;
    }
    public static function glob(string $pattern,int $flags=0): array|false
    {
        if(!str_contains($pattern,'://'))return glob($pattern,$flags);
        $directory=dirname($pattern);$mask=basename($pattern);
        if(self::realpath($directory)===false)return [];
        $result=[];
        foreach(scandir($directory)?:[]as$name)if($name!=='.'&&$name!=='..'&&fnmatch($mask,$name)) {
            $path=$directory.'/'.$name;
            if(self::realpath($path)!==false&&(!($flags&GLOB_ONLYDIR)||is_dir($path)))$result[]=$path;
        }
        if(!($flags&GLOB_NOSORT))sort($result);
        return $result;
    }
    public static function assertWritable(string $file): void
    {
        if(str_contains($file,'://'))throw new \RuntimeException('此资源已内置于二进制，为只读；请修改源码后重新构建');
    }
}
