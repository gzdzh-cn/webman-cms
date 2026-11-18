<?php
declare(strict_types=1);
namespace app\cms\config;

use app\cms\support\EmbeddedResources;

/** Live update settings, with deployment precedence and private atomic writes. */
final class UpdateSourceConfig
{
    private const KEYS=['provider'=>'CMS_UPDATE_PROVIDER','custom_url'=>'CMS_UPDATE_CUSTOM_URL','custom_password'=>'CMS_UPDATE_CUSTOM_PASSWORD'];
    public static function values(): array
    {
        $document=DeploymentConfig::document();$saved=$document['update']??[];$env=Environment::overrides();$out=[];
        foreach(self::KEYS as $key=>$name)$out[$key]=ConfigSchema::environment('update.'.$key,$env[$name]??$saved[$key]??($key==='provider'?'gitee':''));
        return $out;
    }
    public static function identity(?array $values=null): string {return hash('sha256',json_encode($values??self::values(),JSON_THROW_ON_ERROR));}
    private static function backupDirectory(): string
    {
        $root=dirname(\app\cms\Paths::root()).'/.cms-upgrades-'.substr(hash('sha256',\app\cms\Paths::root()),0,20);
        \app\cms\maintenance\AssetArchive::path($root);
        if(!is_dir($root)&&!mkdir($root,0700))throw new \RuntimeException('无法创建项目外私有目录，请检查部署权限');
        $s=stat($root);if(!$s||($s['mode']&0077)||$s['uid']!==posix_geteuid())throw new \RuntimeException('升级目录必须由本站用户独占且权限为 0700');
        $path=$root.'/configuration';\app\cms\maintenance\AssetArchive::path($path);
        if(!is_dir($path)&&!mkdir($path,0700))throw new \RuntimeException('无法创建配置备份目录');
        $s=stat($path);if(!$s||($s['mode']&0077)||$s['uid']!==posix_geteuid())throw new \RuntimeException('配置备份目录必须为当前用户独占的 0700 目录');
        return $path;
    }
    private static function target(): string
    {
        if(EmbeddedResources::yaml())throw new \RuntimeException('内嵌 YAML 为只读，请重新构建配置后部署');
        $path=Environment::path();$env=Environment::overrides();
        // A local .env can persist all three keys together, overriding process values.
        if(is_file($path))return $path;
        foreach(self::KEYS as $name)if(array_key_exists($name,$env))throw new \RuntimeException('升级源由进程环境控制，请先调整部署环境或配置本地 .env');
        return DeploymentConfig::file();
    }
    public static function summary(): array
    {
        $values=self::values();$error=null;$path=null;
        try{$path=self::target();if(!is_writable(is_file($path)?$path:dirname($path)))$error='升级配置文件或目录不可写';}catch(\RuntimeException $e){$error=$e->getMessage();}
        return ['provider'=>$values['provider'],'custom_url'=>$values['custom_url'],'password_configured'=>$values['custom_password']!=='','writable'=>$error===null,'reason'=>$error,'file'=>$path===Environment::path()?'.env':'yaml'];
    }
    public static function save(array $input): array
    {
        self::target();$directory=self::backupDirectory();$path=$directory.'/write.lock';
        \app\cms\maintenance\AssetArchive::path($path);$mask=umask(0077);
        try{$lock=fopen($path,'c+b');}finally{umask($mask);}
        if(!$lock)throw new \RuntimeException('无法创建升级配置锁');
        try{
            if(!flock($lock,LOCK_EX|LOCK_NB))throw new \RuntimeException('升级源正在被修改，请稍后重试');
            $s=lstat($path);$f=fstat($lock);
            if(is_link($path)||!$s||$s['nlink']!==1||$s['ino']!==$f['ino']||$s['dev']!==$f['dev']||($s['mode']&0777)!==0600)throw new \RuntimeException('升级配置锁身份无效');
            return self::persist($input);
        }finally{flock($lock,LOCK_UN);fclose($lock);}
    }
    private static function persist(array $input): array
    {
        $current=self::values();$next=$current;
        foreach(['provider','custom_url'] as $key)if(array_key_exists($key,$input))$next[$key]=ConfigSchema::coerce('update.'.$key,$input[$key]);
        if(array_key_exists('custom_password',$input)&&$input['custom_password']!=='')$next['custom_password']=ConfigSchema::coerce('update.custom_password',$input['custom_password']);
        if(($input['clear_password']??'0')==='1')$next['custom_password']='';
        if($next['provider']==='custom'&&($next['custom_url']===''||$next['custom_password']===''))throw new \InvalidArgumentException('自定义升级源需要请求链接和密码');
        $file=self::target();
        if($file!==Environment::path()){ConfigFile::write(['update'=>$next],true,false,self::backupDirectory());}
        else {
            Entry::assertPath($file);$stat=lstat($file);
            if(!$stat||is_link($file)||!is_file($file)||$stat['nlink']!==1||$stat['size']>1048576||!is_writable($file)||!is_writable(dirname($file)))throw new \RuntimeException('.env 配置文件不可安全写入');
            $lockPath=dirname($file).'/.cms-update-env.lock';if(is_link($lockPath))throw new \RuntimeException('升级配置锁无效');
            $lock=fopen($lockPath,'c+b');if(!$lock)throw new \RuntimeException('无法锁定升级配置');
            $temporary=null;
            try{
                chmod($lockPath,0600);if(!flock($lock,LOCK_EX))throw new \RuntimeException('无法锁定升级配置');
                $ls=lstat($lockPath);$fs=fstat($lock);
                if(is_link($lockPath)||!$ls||$ls['nlink']!==1||$ls['ino']!==$fs['ino']||$ls['dev']!==$fs['dev'])throw new \RuntimeException('升级配置锁身份无效');
                Entry::assertPath($file);$identity=lstat($file);
                if(!$identity||$identity['nlink']!==1||!is_file($file))throw new \RuntimeException('.env 身份无效');
                $before=file_get_contents($file);if(!is_string($before))throw new \RuntimeException('无法读取 .env');Environment::parse($before);
                $bytes=$before;
                foreach(self::KEYS as $key=>$name){
                    $line=$name.'="'.str_replace(['\\','"','$',"\r","\n"],['\\\\','\\"','\\$', '\\r','\\n'],$next[$key]).'"';
                    $pattern='/^[ \t]*(?:export[ \t]+)?'.preg_quote($name,'/').'[ \t]*=.*$/m';
                    if(preg_match($pattern,$bytes))$bytes=preg_replace_callback($pattern,static fn()=>$line,$bytes);else $bytes.=(str_ends_with($bytes,"\n")||$bytes===''?'':"\n").$line."\n";
                }
                $parsed=Environment::parse($bytes);foreach(self::KEYS as $key=>$name)if(($parsed[$name]??null)!==$next[$key])throw new \RuntimeException('升级配置编码校验失败');
                $backup=self::backupDirectory();
                $saved=$backup.'/env-'.gmdate('YmdHis').'-'.bin2hex(random_bytes(5));$h=fopen($saved,'xb');if(!$h)throw new \RuntimeException('配置备份失败');try{chmod($saved,0600);if(fwrite($h,$before)!==strlen($before)||!fflush($h))throw new \RuntimeException('配置备份失败');}finally{fclose($h);}
                $temporary=tempnam(dirname($file),'.cms-env-');chmod($temporary,0600);
                Entry::assertPath($file);clearstatcache(true,$file);$now=lstat($file);
                if(file_put_contents($temporary,$bytes)!==strlen($bytes)||!$now||$now['ino']!==$identity['ino']||$now['dev']!==$identity['dev']||file_get_contents($file)!==$before||!rename($temporary,$file))throw new \RuntimeException('配置发生变化或写入失败，请重试');
                Environment::refresh();
            }finally{if($temporary&&is_file($temporary))unlink($temporary);flock($lock,LOCK_UN);fclose($lock);}
        }
        if(self::values()!==$next)throw new \RuntimeException('配置保存后被其他配置覆盖，请检查部署配置');
        return self::summary();
    }
}
