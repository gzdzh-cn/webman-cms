<?php
declare(strict_types=1);
namespace app\cms\maintenance;
use app\cms\Paths;
/** Every HTTP request / background generation unit holds a shared publication lock. */
final class DatabaseTraffic
{
    private static function handle()
    {
        $file=Paths::root('config/cms-database-traffic.lock');if(is_link($file))throw new \RuntimeException('数据库发布锁类型错误');$f=fopen($file,'c');if(!$f)throw new \RuntimeException('无法读取数据库发布锁');chmod($file,0600);return$f;
    }
    public static function read(callable $operation,callable $unavailable):mixed
    {
        if(DatabaseBackup::maintenance())return$unavailable();$f=self::handle();
        if(!flock($f,LOCK_SH|LOCK_NB)){fclose($f);return$unavailable();}
        try{if(DatabaseBackup::maintenance())return$unavailable();return$operation();}finally{flock($f,LOCK_UN);fclose($f);}
    }
    public static function exclusive(callable $operation):mixed
    {
        $f=self::handle();$deadline=microtime(true)+10;
        while(!flock($f,LOCK_EX|LOCK_NB)){if(microtime(true)>$deadline){fclose($f);throw new \RuntimeException('正在等待现有请求退出，请稍后继续原恢复任务');}usleep(100000);}
        try{return$operation();}finally{flock($f,LOCK_UN);fclose($f);}
    }
}
