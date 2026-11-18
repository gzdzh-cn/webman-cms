<?php
declare(strict_types=1);
namespace app\cms\service;
use app\cms\Paths;
use app\cms\maintenance\{AssetArchive,GiteeReleases,ReleaseDownload,ReleaseManifest,MaintenanceGate,RuntimeProvider};
use app\cms\persistence\StateStore;
use app\cms\support\{EmbeddedResources,ProcessLauncher};

final class AdminUpdateService
{
    private StateStore $store;
    private const CACHE = 'release-cache-gitee-v1';
    public function __construct(?StateStore $store=null, private ?GiteeReleases $releases=null)
    {
        $this->store=$store??new StateStore(self::scope());$this->releases??=new GiteeReleases();
    }
    public static function scope(): string { return 'maintenance:update:'.substr(hash('sha256',Paths::root()),0,20); }
    public static function deployment(): array
    {
        $build=EmbeddedResources::manifest();
        return ['version'=>\app\cms\admin\support\BackendVersion::version(),'kind'=>EmbeddedResources::binary()?'binary':'source','platform'=>RuntimeProvider::platform(),'build_id'=>$build['build_id']??null,'embed_admin_assets'=>EmbeddedResources::hasAssets(),'custom'=>EmbeddedResources::binary() && (bool)array_diff($build['embedded_resources']??[],[Paths::PUBLIC_ASSETS])];
    }
    public function check(bool $force=false): array
    {
        $refresh=$this->store->locked(function()use($force):bool{
            $cache=$this->store->read(self::CACHE);
            if(!$force && time()-(int)($cache['attempted_at']??0)<self::cacheLifetime($cache))return false;
            $cache['attempted_at']=time();$this->store->write(self::CACHE,$cache);return true;
        });
        if($refresh){
            $release=null;try{$release=$this->releases->latest();$error=null;}
            catch(\Throwable $e){
                $context=method_exists($e,'getHandlerContext')?$e->getHandlerContext():[];
                $error=($context['errno']??null)===28?'Gitee 连接超时，已保留上次结果，请稍后重试':'Gitee 检查更新失败，已保留上次结果，请稍后重试';
            }
            $this->store->locked(function()use($release,$error):void{
                $cache=$this->store->read(self::CACHE);$cache['checking_until']=0;
                if($error===null){$cache['release']=$release;$cache['checked_at']=time();unset($cache['error']);}else $cache['error']=$error;
                $this->store->write(self::CACHE,$cache);
            });
        }
        $cache=$this->store->read(self::CACHE);
        $deployment=self::deployment();$release=$cache['release']??null;
        $new=$release && version_compare($release['version'],$deployment['version'],'>');
        $package=$release?ReleaseManifest::package($release['manifest'],$deployment):null;
        $compatible=$release && version_compare($deployment['version'],$release['manifest']['min_version'],'>=') && version_compare($deployment['version'],$release['manifest']['max_version'],'<=') && version_compare(PHP_VERSION,$release['manifest']['min_php'],'>=');
        $reason=$deployment['custom']?'此二进制内嵌站点配置或主题等专用资源，需要专用升级包':(!$package?'正式发布尚无匹配当前平台及资源方式的升级包':(!$compatible?'当前版本或 PHP 环境不符合升级要求':''));
        $current=$this->store->read('current');
        return ['current'=>$deployment['version'],'latest'=>$release['version']??null,'release_id'=>$release?self::identity($release):null,'published_at'=>$release['published_at']??null,'notes'=>$release['notes']??'','has_update'=>(bool)$new,'upgradeable'=>(bool)($new&&$package&&$compatible),'reason'=>$reason,'checked_at'=>$cache['checked_at']??null,'error'=>$cache['error']??null,'checking'=>(int)($cache['checking_until']??0)>time(),'task_id'=>$current['id']??null];
    }
    /** HTTP only schedules a short detached check, and immediately returns the site cache. */
    private static function cacheLifetime(array $cache): int { return !empty($cache['error']) ? 60 : 900; }
    public function cachedCheck(bool $force=false): array
    {
        $launch=$this->store->locked(function()use($force):bool{
            $cache=$this->store->read(self::CACHE);
            if((int)($cache['checking_until']??0)>time() || (!$force && time()-(int)($cache['attempted_at']??0)<self::cacheLifetime($cache)))return false;
            $cache['attempted_at']=time();$cache['checking_until']=time()+120;$this->store->write(self::CACHE,$cache);return true;
        });
        if($launch){
            try{
                if(!function_exists('pcntl_fork')||!function_exists('pcntl_exec')||!function_exists('posix_setsid')||!function_exists('posix_kill'))throw new \RuntimeException('独立更新检查不可用');
                $pid=pcntl_fork();if($pid<0)throw new \RuntimeException('独立更新检查启动失败');
                if($pid===0){
                    try {
                    if(posix_setsid()<0)self::finishFork();$child=pcntl_fork();if($child<0)self::finishFork();if($child>0)self::finishFork();
                    foreach(get_resources('stream') as $stream)if(is_resource($stream)&&$stream!==STDIN&&$stream!==STDOUT&&$stream!==STDERR)@fclose($stream);
                    $command=ProcessLauncher::command(['cms:update','refresh']);pcntl_exec(array_shift($command),$command,\app\cms\config\Environment::processEnvironment());
                    } finally { self::finishFork(); }
                }
                pcntl_waitpid($pid,$status);
            }catch(\Throwable){$cache=$this->store->read(self::CACHE);$cache['checking_until']=0;$cache['error']='后台更新检查不可用，请稍后重试';$this->store->write(self::CACHE,$cache);}
        }
        return $this->check(false);
    }
    private static function identity(array $release): string { return $release['id'].':'.hash('sha256','gitee:'.GiteeReleases::REPOSITORY.':'.json_encode($release['manifest'],JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)); }
    public function start(string $identity,int $adminId): array
    {
        foreach (['pcntl_fork','pcntl_exec','posix_setsid','posix_kill','proc_open'] as $function) if (!function_exists($function)) throw new \RuntimeException('环境缺少独立升级执行能力');
        if ($adminId<1) throw new \RuntimeException('管理员身份无效');
        if (ProcessLauncher::managed()) throw new \RuntimeException('systemd 部署请使用专用维护窗口升级，当前在线执行器仅支持独立进程或 cms:container');
        $master=ProcessLauncher::master();
        if (!$master || posix_getppid()!==$master) throw new \RuntimeException('无法核验本站服务主进程');
        return $this->store->locked(function()use($identity,$adminId,$master):array {
            $current=$this->store->read('current');
            if (!empty($current['id'])) {
                $task=$this->store->read('task-'.$current['id']);
                if (!empty($task['rollback_error'])) throw new \RuntimeException('上次升级恢复尚未完成，请先运行 cms:update recover');
                if (!in_array($task['stage']??'',['succeeded','failed','rolled_back'],true)) return $this->summary($task);
            }
            $info=$this->check(true);
            if (!empty($info['error']) || !$info['upgradeable'] || !hash_equals($info['release_id'],$identity)) throw new \RuntimeException($info['error']??($info['reason']?:'发布已变化，请重新检查'));
            $release=$this->store->read(self::CACHE)['release'];$package=ReleaseManifest::package($release['manifest'],self::deployment());
            $id=bin2hex(random_bytes(16));$directory=$this->privateDirectory().'/'.$id;
            if (!mkdir($directory,0700)) throw new \RuntimeException('无法创建项目外私有升级目录');
            $gate=fopen(MaintenanceGate::file(),'c+b');if (!$gate||!flock($gate,LOCK_EX|LOCK_NB)) throw new \RuntimeException('安装、重启或生成任务正在执行');
            try {
                if (is_file(Paths::config('cms-installing.lock'))) { $h=fopen(Paths::config('cms-installing.lock'),'r');if(!$h||!flock($h,LOCK_EX|LOCK_NB))throw new \RuntimeException('安装正在执行');flock($h,LOCK_UN);fclose($h); }
                $db=\app\cms\database\DatabaseSettings::values();
                $job=['id'=>$id,'root'=>Paths::root(),'directory'=>$directory,'scope'=>$this->store->scope(),'prefix'=>$this->store->prefix(),'database'=>$db,'release'=>$release,'package'=>$package,'url'=>ReleaseDownload::expected($release,$package),'version'=>$release['version'],'deployment'=>self::deployment(),'executable'=>EmbeddedResources::binary()?\Phar::running(false):PHP_BINARY,'entry'=>EmbeddedResources::binary()?null:Paths::code('webman'),'old_master'=>$master,'stop_timeout'=>(int)\app\cms\config\DeploymentConfig::get('server.stop_timeout'),'gate'=>MaintenanceGate::file(),'env'=>\app\cms\config\Environment::processEnvironment()];
                foreach (['UpgradeExecutor','AssetArchive','ReleaseManifest','RuntimeProvider','BinaryBuilder','GithubReleases','GiteeReleases','ReleaseDownload'] as $class) $this->copy(Paths::cms('maintenance/'.$class.'.php'),$directory.'/'.$class.'.php',0600);
                $runner="<?php\ndeclare(strict_types=1);\nini_set('memory_limit','768M');\nini_set('zend.exception_ignore_args','1');\nforeach(['AssetArchive','RuntimeProvider','BinaryBuilder','ReleaseManifest','GithubReleases','GiteeReleases','ReleaseDownload','UpgradeExecutor'] as \$class) require __DIR__.'/'.\$class.'.php';\n\\app\\cms\\maintenance\\UpgradeExecutor::supervise(__DIR__.'/job.json');\n";
                $this->write($directory.'/runner.php',$runner);
                if (EmbeddedResources::binary()) $this->freezeBinary($directory.'/executor');
                $this->write($directory.'/job.json',json_encode($job,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
                $task=['id'=>$id,'admin'=>$adminId,'stage'=>'scheduled','created_at'=>time(),'version'=>$release['version'],'old_master'=>$master,'directory'=>$directory,'message'=>'正在启动独立升级执行器'];
                $this->store->write('task-'.$id,$task);$this->store->write('current',['id'=>$id]);
                $this->launch($directory);
                return $this->summary($task);
            } finally {flock($gate,LOCK_UN);fclose($gate);}
        });
    }
    private function privateDirectory(): string
    {
        $path=dirname(Paths::root()).'/.cms-upgrades-'.substr(hash('sha256',Paths::root()),0,20);
        AssetArchive::path($path);
        if (!is_dir($path) && !mkdir($path,0700)) throw new \RuntimeException('无法创建项目外升级目录');
        $stat=stat($path);if (!$stat || ($stat['mode']&0077) || $stat['uid']!==posix_geteuid()) throw new \RuntimeException('升级目录必须由本站用户独占且权限为 0700');
        return $path;
    }
    private function launch(string $directory): void
    {
        $pid=pcntl_fork();if ($pid<0) throw new \RuntimeException('无法创建升级执行进程');
        if ($pid===0) {
            try {
            if (posix_setsid()<0) self::finishFork();
            $child=pcntl_fork();if($child<0)self::finishFork();if($child>0)self::finishFork();
            foreach (get_resources('stream') as $stream) if (is_resource($stream) && $stream!==STDIN && $stream!==STDOUT && $stream!==STDERR) @fclose($stream);
            if (EmbeddedResources::binary()) pcntl_exec($directory.'/executor',['cms:update-executor',$directory.'/runner.php'],\app\cms\config\Environment::processEnvironment());
            else pcntl_exec(PHP_BINARY,[$directory.'/runner.php'],\app\cms\config\Environment::processEnvironment());
            } finally { self::finishFork(); }
        }
        pcntl_waitpid($pid,$status);
    }
    private static function finishFork(): never
    {
        // PHP exit destroys inherited PDO/mysqli handles and sends COM_QUIT on
        // the worker's live socket. Reap this short-lived fork without running
        // those destructors; the daemon grandchild replaces itself with exec.
        posix_kill(getmypid(), SIGKILL);
        exit(1);
    }
    private function freezeBinary(string $target): void
    {
        // micro's ordinary file IO hides the native prefix of its own binary.
        // Its raw self stream is required to preserve the executable runtime.
        if (!function_exists('micro_open_self')) throw new \RuntimeException('二进制运行时缺少完整执行器备份能力');
        $input=\micro_open_self();$output=fopen($target,'xb');
        if (!$input || !$output) throw new \RuntimeException('无法创建独立二进制执行器');
        $hash=hash_init('sha256');$size=0;
        try {
            if (!chmod($target,0600)) throw new \RuntimeException('执行器私有权限设置失败');
            while (!feof($input)) {
                $bytes=fread($input,1048576);
                if ($bytes===false || ($bytes==='' && !feof($input)) || fwrite($output,$bytes)!==strlen($bytes)) throw new \RuntimeException('完整二进制执行器复制失败');
                hash_update($hash,$bytes);$size+=strlen($bytes);
            }
            if (!$size || !fflush($output) || (function_exists('fsync')&&!fsync($output))) throw new \RuntimeException('完整二进制执行器写入失败');
        } finally { fclose($input);fclose($output); }
        if (!hash_equals(hash_final($hash),hash_file('sha256',$target)) || !chmod($target,0700)) throw new \RuntimeException('完整二进制执行器摘要校验失败');
    }
    private function copy(string $source,string $target,int $mode): void { if (!copy($source,$target)||!chmod($target,$mode)||!hash_equals(hash_file('sha256',$source),hash_file('sha256',$target))) throw new \RuntimeException('独立执行器复制校验失败'); }
    private function write(string $path,string $bytes): void { $h=fopen($path,'xb');if(!$h)throw new \RuntimeException('私有执行资料已存在');try{if(!chmod($path,0600)||fwrite($h,$bytes)!==strlen($bytes)||!fflush($h))throw new \RuntimeException('私有执行资料写入失败');}finally{fclose($h);} }
    public function status(string $id): array
    {
        if (!preg_match('/^[a-f0-9]{32}$/D',$id)) throw new \RuntimeException('升级任务无效');
        $task=$this->store->read('task-'.$id);if(!$task)throw new \RuntimeException('升级任务不存在');
        if (!in_array($task['stage'],['succeeded','failed','rolled_back'],true) && time()-(int)$task['created_at']>15) {
            $directory=$this->privateDirectory().'/'.$id;
            AssetArchive::path($directory.'/executor.lock');
            if (($task['directory']??'')===$directory && is_file($directory.'/job.json')) {
                $mutex=fopen($directory.'/executor.lock','c+b');
                if ($mutex) { $available=flock($mutex,LOCK_EX|LOCK_NB);if($available)flock($mutex,LOCK_UN);fclose($mutex);if($available)$this->launch($directory); }
            }
        }
        return $this->summary($task);
    }
    public function recover(string $id): array
    {
        $this->status($id);
        $task=$this->store->read('task-'.$id);
        if (in_array($task['stage'],['succeeded','rolled_back'],true)) return $this->summary($task);
        $directory=$this->privateDirectory().'/'.$id;
        AssetArchive::path($directory.'/runner.php');
        if (($task['directory']??'')!==$directory || !is_file($directory.'/job.json') || !is_file($directory.'/runner.php')) throw new \RuntimeException('私有恢复资料缺失');
        $this->launch($directory);
        return ['id'=>$id,'message'=>'已调度原任务的私有恢复执行器，请继续查询状态'];
    }
    private function summary(array $task): array { return array_intersect_key($task,array_flip(['id','stage','message','created_at','updated_at','version','error','rollback_error'])); }
    /** Invoked by a real HTTP worker after bootstrap; CLI probes cannot acknowledge startup. */
    public static function acknowledge(): void
    {
        $service=new self();$pointer=$service->store->read('current');if(empty($pointer['id']))return;
        $id=$pointer['id'];$task=$service->store->read('task-'.$id);
        if (($task['stage']??'')!=='restarting' || posix_getppid()===(int)$task['old_master']) return;
        $identity=self::deployment();if ($identity['version']!==$task['version'] || $identity['build_id']!==($task['expected_build_id']??null)) throw new \RuntimeException('升级后新进程版本或构建身份不符');
        if (!(new \app\cms\maintenance\AssetMaintenance())->inspect(true)['ok']) throw new \RuntimeException('升级后系统资源校验失败');
        $service->store->connection()->query('SELECT 1')->fetchColumn();
        $service->store->locked(function()use($service,$id,$identity):void{$task=$service->store->read('task-'.$id);if(($task['stage']??'')==='restarting'){$task['ack']=['master'=>posix_getppid(),'worker'=>getmypid(),'version'=>$identity['version'],'build_id'=>$identity['build_id'],'at'=>time()];$service->store->write('task-'.$id,$task);}});
    }
}
