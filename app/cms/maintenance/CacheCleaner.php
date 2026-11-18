<?php
declare(strict_types=1);
namespace app\cms\maintenance;
use app\cms\Paths;
final class CacheCleaner
{
    public static function assertStopped():void
    {
        $pidFile=(string)config('server.pid_file',Paths::runtime('webman.pid'));
        if(is_file($pidFile)){
            $pid=(int)trim((string)file_get_contents($pidFile));
            if($pid>0 && (!function_exists('posix_kill')||@posix_kill($pid,0)))throw new \RuntimeException('请先停止全部Webman worker，再清理runtime');
        }
    }
    public function clear(bool $all=false):array
    {
        self::assertStopped();$root=Paths::runtime();
        if(is_link($root))throw new \RuntimeException('runtime不能是符号链接');
        $names=['cms/cache','cms/cache-store','cms/templates','cms/admin/views','cms/generated-cache','cms/languages','cache/assets','views'];
        if ($all) $names[]='sessions';
        $targets=array_map(fn($name)=>$root.'/'.$name,$names);$removed=0;
        foreach($targets as $target)$this->inspect($target);
        self::assertStopped();
        \app\cms\cache\CachePolicy::rotate($all);
        foreach($targets as $target)$removed+=$this->remove($target);
        return ['all_runtime'=>false,'removed_files'=>$removed,'sessions_expired'=>$all,'cache_invalidated'=>true,'redis_keys_expire_by_ttl'=>true];
    }
    private function inspect(string $path):void
    {
        if(is_link($path))throw new \RuntimeException('拒绝清理含符号链接的runtime');
        if(!file_exists($path)||is_file($path))return;
        if(!is_dir($path))throw new \RuntimeException('运行目录包含特殊文件');
        foreach(scandir($path) as $name)if($name!=='.'&&$name!=='..')$this->inspect($path.'/'.$name);
    }
    private function remove(string $path):int
    {
        if(is_link($path))throw new \RuntimeException('拒绝清理含符号链接的runtime');
        if(!file_exists($path))return 0;
        if(is_file($path)){if(!unlink($path))throw new \RuntimeException('无法删除运行文件');return 1;}
        if(!is_dir($path))throw new \RuntimeException('运行目录包含特殊文件');
        $count=0;foreach(scandir($path) as $name)if($name!=='.'&&$name!=='..')$count+=$this->remove($path.'/'.$name);
        if(!rmdir($path))throw new \RuntimeException('无法删除运行目录');return $count;
    }
}
