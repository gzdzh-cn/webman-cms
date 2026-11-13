<?php
declare(strict_types=1);
namespace app\cms\config;

use app\cms\Paths;
use Symfony\Component\Yaml\Yaml;

/** Serialized, private, compare-before-replace YAML writes for installation and migration. */
final class ConfigFile
{
    public static function assertWritable(bool $installation=false): void
    {
        $file=$installation?DeploymentConfig::discoverFile():DeploymentConfig::file();\app\cms\support\EmbeddedResources::assertWritable($file);Entry::assertPath($file);
        if(file_exists($file)) {
            DeploymentConfig::document($file);$s=lstat($file);
            if(($s['mode']&0222)===0||!is_writable($file))throw new \RuntimeException('YAML 配置文件不可写');
        }
        $parent=dirname($file);
        while(!file_exists($parent)&&dirname($parent)!==$parent)$parent=dirname($parent);
        if(!is_dir($parent)||!is_writable($parent)||(fileperms($parent)&0222)===0)throw new \RuntimeException('YAML 配置目录不可写');
        $lock=dirname($file).'/.config.lock';
        if(is_link($lock))throw new \RuntimeException('YAML 配置锁无效');
        if(is_file($lock)) {
            $s=lstat($lock);if($s['nlink']!==1||($s['mode']&0777)!==0600)throw new \RuntimeException('YAML 配置锁权限无效');
            $h=fopen($lock,'r+');if(!$h)throw new \RuntimeException('无法读取 YAML 配置锁');
            try{if(!flock($h,LOCK_EX|LOCK_NB))throw new \RuntimeException('YAML 配置正在被修改');flock($h,LOCK_UN);}finally{fclose($h);}
        }
    }
    public static function write(array $data, bool $merge=true, bool $installation=false): void
    {
        ConfigSchema::flatten($data);self::assertWritable($installation);
        $file=$installation?DeploymentConfig::discoverFile():DeploymentConfig::file();$directory=dirname($file);
        $expected=self::identity($file);
        Paths::ensureDirectory($directory,0700);$dir=lstat($directory);
        $lockPath=$directory.'/.config.lock';$mask=umask(0077);
        try{$lock=fopen($lockPath,'c+b');}finally{umask($mask);}
        if(!$lock)throw new \RuntimeException('无法创建 YAML 配置锁');
        $temporary=null;
        try {
            if(!flock($lock,LOCK_EX|LOCK_NB))throw new \RuntimeException('YAML 配置正在被修改');
            $ls=lstat($lockPath);$fs=fstat($lock);
            if(is_link($lockPath)||!$ls||$ls['nlink']!==1||$ls['ino']!==$fs['ino']||$ls['dev']!==$fs['dev']||($ls['mode']&0777)!==0600)throw new \RuntimeException('YAML 配置锁身份无效');
            $current=DeploymentConfig::document($file);
            if(self::identity($file)!==$expected)throw new \RuntimeException('YAML 配置已被其他操作修改，请重试');
            $next=$merge?array_replace_recursive($current,$data):$data;
            unset($next['site']);
            if(isset($next['server']['listen']))$next['server']['listen']=ConfigSchema::coerce('server.listen',$next['server']['listen']);
            $next=['version'=>1]+$next;ConfigSchema::flatten($next);
            $bytes="# CMS deployment configuration. Local .env keys override this file.\n".Yaml::dump($next,8,2,Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
            if(is_file($file)) {
                $backup=$directory.'/.backups';Paths::ensureDirectory($backup,0700);
                $backupFile=$backup.'/config-'.gmdate('YmdHis').'-'.bin2hex(random_bytes(4)).'.yaml';
                $h=fopen($backupFile,'xb');if(!$h)throw new \RuntimeException('无法备份原 YAML 配置');
                try{chmod($backupFile,0600);$old=file_get_contents($file);if(!is_string($old)||fwrite($h,$old)!==strlen($old)||!fflush($h))throw new \RuntimeException('YAML 配置备份失败');}finally{fclose($h);}
            }
            $temporary=$directory.'/.config-'.bin2hex(random_bytes(16));$h=fopen($temporary,'xb');
            if(!$h)throw new \RuntimeException('无法创建 YAML 临时文件');
            try{chmod($temporary,0600);if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)||(function_exists('fsync')&&!fsync($h)))throw new \RuntimeException('YAML 配置写入失败');}finally{fclose($h);}
            DeploymentConfig::document($temporary);Entry::assertPath($file);
            $now=lstat($directory);
            if($now['dev']!==$dir['dev']||$now['ino']!==$dir['ino']||self::identity($file)!==$expected)throw new \RuntimeException('YAML 配置或父目录已变化');
            if($installation&&DeploymentConfig::discoverFile()!==$file)throw new \RuntimeException('安装期间 YAML 配置优先级已变化，请使用相同设置重试收尾');
            if(!rename($temporary,$file))throw new \RuntimeException('无法发布 YAML 配置');
            $temporary=null;
        } finally {if($temporary!==null&&is_file($temporary))unlink($temporary);flock($lock,LOCK_UN);fclose($lock);}
    }
    private static function identity(string $file): ?array
    {
        Entry::assertPath($file);clearstatcache(true,$file);
        if(!file_exists($file))return null;
        $s=lstat($file);
        if(!$s||!is_file($file)||$s['nlink']!==1)throw new \RuntimeException('YAML 配置文件身份无效');
        return [$s['dev'],$s['ino'],$s['mode'],hash_file('sha256',$file)];
    }
}
