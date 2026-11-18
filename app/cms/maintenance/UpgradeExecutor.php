<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Frozen with its dependencies outside the deployment before any managed file changes. */
final class UpgradeExecutor
{
    private \PDO $pdo;
    private array $job;
    private string $directory;
    private string $journal;
    public function __construct(string $file)
    {
        AssetArchive::path($file);
        $stat=stat($file);
        if (!$stat || ($stat['mode']&0077) || $stat['uid']!==posix_geteuid()) throw new \RuntimeException('升级执行资料不是本站私有文件');
        $this->job=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
        $this->directory=dirname($file);$this->journal=$this->directory.'/journal.json';
        $v=$this->job['database'];
        $this->pdo=new \PDO('mysql:host='.$v['DB_HOST'].';port='.$v['DB_PORT'].';dbname='.$v['DB_NAME'].';charset=utf8mb4',$v['DB_USER'],$v['DB_PASSWORD'],[\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,\PDO::ATTR_EMULATE_PREPARES=>false]);
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,47}$/D',$this->job['prefix'])) throw new \RuntimeException('升级状态表前缀无效');
    }
    public static function supervise(string $file): void
    {
        $executor=new self($file);
        $mutex=fopen(dirname($file).'/executor.lock','c+b');chmod(dirname($file).'/executor.lock',0600);
        if (!$mutex || !flock($mutex,LOCK_EX|LOCK_NB)) return;
        $state=$executor->read();
        if (in_array($state['stage']??'',['succeeded','rolled_back'],true) || (($state['stage']??'')==='failed' && !is_file($executor->journal))) return;
        // A second invocation after a machine/process interruption restores the
        // journal first; it never guesses how far a previous switch advanced.
        if (is_file($executor->journal)) { $executor->rollback('上次执行中断，正在恢复原程序');return; }
        $executor->persist(['executor_pid'=>getmypid()]);
        $pid=pcntl_fork();
        if ($pid<0) {$executor->persist(['stage'=>'failed','message'=>'独立升级进程创建失败']);return;}
        if ($pid===0) {
            pcntl_async_signals(true);
            foreach([SIGTERM,SIGINT,SIGHUP] as $signal) pcntl_signal($signal,static function():void { throw new \RuntimeException('升级执行被中断'); });
            $child=new self($file);
            try {$child->execute();exit(0);}catch(\Throwable $error){$child->failure($error);exit(1);}
        }
        // Reconnect after fork so neither process shares a MySQL socket.
        unset($executor);pcntl_waitpid($pid,$status);$executor=new self($file);
        $state=$executor->read();
        if (!in_array($state['stage']??'',['succeeded','rolled_back','failed'],true)) $executor->rollback('升级执行器意外退出，正在恢复原程序');
        flock($mutex,LOCK_UN);fclose($mutex);
    }
    private function execute(): void
    {
        $job=$this->job;$manifest=ReleaseManifest::validate($job['release']['manifest'],$job['release']['tag']);
        $package=ReleaseManifest::package($manifest,$job['deployment']);
        if ($package!==$job['package'] || $manifest['version']!==$job['version'] || !version_compare($manifest['version'],$job['deployment']['version'],'>') || version_compare($job['deployment']['version'],$manifest['min_version'],'<') || version_compare($job['deployment']['version'],$manifest['max_version'],'>') || version_compare(PHP_VERSION,$manifest['min_php'],'<')) throw new \RuntimeException('升级版本、平台或兼容要求不符');
        $this->phase('downloading','正在下载正式升级包');
        $archive=$this->directory.'/package.tar.gz';
        $this->download($job['url'],$archive,$package['size']);
        $this->phase('verifying','正在校验升级包摘要与受管文件');
        $stage=$this->directory.'/payload';if(!mkdir($stage,0700))throw new \RuntimeException('暂存目录已存在');
        AssetArchive::read($archive,$package['sha256'],$package['files'],function($path,$bytes)use($package,$stage):void{
            if (!hash_equals($package['files'][$path]['sha256'],hash('sha256',$bytes))) throw new \RuntimeException('升级成员摘要校验失败');
            $target=$stage.'/'.$path;$this->directory(dirname($target));
            if (file_put_contents($target,$bytes)!==strlen($bytes)||!chmod($target,$package['files'][$path]['mode'])) throw new \RuntimeException('升级暂存写入失败');
        },268435456);
        if ($package['kind']==='source') {
            $version=json_decode((string)file_get_contents($stage.'/app/cms/resources/backend-provenance.json'),true,512,JSON_THROW_ON_ERROR)['version']??'';
            if ($version!==$job['version']) throw new \RuntimeException('包内程序版本与正式发布不一致');
        }
        $this->phase('preflight','正在检查权限、空间和部署完整性');
        if ($package['kind']==='binary') {
            $identity=$this->candidateIdentity($stage.'/program');
            $build=$identity['build']??[];
            if (($identity['version']??'')!==$job['version'] || ($build['cms_version']??'')!==$job['version'] || ($build['build_id']??'')!==$package['build_id'] || ($build['platform']??'')!==$package['platform'] || ($build['embed_admin_assets']??null)!==$package['embed_admin_assets'] || array_diff($build['embedded_resources']??[],['app/cms/resources/admin-assets'])) throw new \RuntimeException('包内二进制版本、平台、构建或资源方式不匹配');
        }
        $this->command(['cms:deployment','--check']);
        $gate=fopen($job['gate'],'c+b');
        if(!$gate||!flock($gate,LOCK_EX|LOCK_NB))throw new \RuntimeException('安装、重启或生成正在执行，请完成后重试');
        try {
            if ($this->master()!==$job['old_master']) throw new \RuntimeException('服务主进程已变化，取消升级');
            $before=[];$bytes=0;
            foreach($package['files'] as $path=>$file) {
                $target=$this->target($path);AssetArchive::path($target);
                $parent=dirname($target);while(!file_exists($parent))$parent=dirname($parent);
                if(!is_writable($parent))throw new \RuntimeException('受管文件父目录不可写');
                if(file_exists($target)){
                    $stat=lstat($target);if(!is_file($target)||$stat['nlink']!==1||!is_readable($target))throw new \RuntimeException('受管文件部署身份无效');
                    $before[$path]=['sha256'=>hash_file('sha256',$target),'size'=>filesize($target),'mode'=>$stat['mode']&0777];$bytes+=filesize($target);
                }else $before[$path]=null;
                $bytes+=$file['size'];
            }
            if ((disk_free_space($job['root'])?:0)<$bytes*2+67108864 || (disk_free_space($this->directory)?:0)<$bytes+67108864) throw new \RuntimeException('升级或私有备份磁盘空间不足');
            $this->phase('backing_up','正在备份受管文件，站点资料保持原位');
            $backup=$this->directory.'/backup';$this->directory($backup);
            foreach($before as $path=>$file) if($file!==null){$target=$backup.'/'.$path;$this->directory(dirname($target));if(!copy($this->target($path),$target)||!chmod($target,0600)||!hash_equals($file['sha256'],hash_file('sha256',$target)))throw new \RuntimeException('原程序私有备份校验失败');}
            // Write the complete intent before the first replacement. Rollback
            // distinguishes unchanged originals, installed bytes and new files by hash.
            $journal=['before'=>$before,'applied'=>array_keys($package['files']),'stopped'=>false];$this->saveJournal($journal);
            $this->phase('stopping','正在停止当前程序');
            $this->command(['stop'],$job['stop_timeout']+20);
            if ($this->master()) throw new \RuntimeException('旧进程未完整停止');
            $journal['stopped']=true;$this->saveJournal($journal);
            $this->phase('switching','正在切换受管程序文件');
            foreach($package['files'] as $path=>$file){
                $target=$this->target($path);AssetArchive::path($target);$old=$before[$path];
                if(($old===null&&file_exists($target))||($old!==null&&(!is_file($target)||!hash_equals($old['sha256'],hash_file('sha256',$target)))))throw new \RuntimeException('文件在预检后发生变化，已停止切换');
                $this->directory(dirname($target));$temporary=dirname($target).'/.cms-upgrade-'.$job['id'];
                if(file_exists($temporary)||is_link($temporary))throw new \RuntimeException('原子切换暂存路径冲突');
                if(!copy($stage.'/'.$path,$temporary)||!chmod($temporary,$file['mode'])||!hash_equals($file['sha256'],hash_file('sha256',$temporary)))throw new \RuntimeException('原子切换文件校验失败');
                if(!rename($temporary,$target))throw new \RuntimeException('原子切换失败');
            }
            $this->phase('migrating','正在执行声明为向后兼容的增量迁移');
            $this->command(['cms:update-probe','--migrate'],120);
            // The process must start after the replacement, never just reload.
            $this->persist(['stage'=>'restarting','message'=>'正在完整启动并等待新 HTTP 进程验证','expected_build_id'=>$package['build_id']??null,'ack'=>null]);
            $this->command(['start','-d'],$job['stop_timeout']+30);
            $deadline=microtime(true)+60;
            do {
                $state=$this->read();$ack=$state['ack']??null;
                if($ack && $ack['version']===$job['version'] && $ack['build_id']===($package['build_id']??null) && $ack['master']!==$job['old_master'] && $this->master()===$ack['master']) {
                    $this->phase('succeeded','升级成功，新进程版本、构建、系统资源和数据库均已验证');return;
                }
                usleep(250000);
            }while(microtime(true)<$deadline);
            throw new \RuntimeException('新进程未通过版本、构建、资源及数据库验证');
        }finally{flock($gate,LOCK_UN);fclose($gate);}
    }
    private function target(string $path): string { return $path==='program'?$this->job['executable']:$this->job['root'].'/'.$path; }
    private function candidateIdentity(string $program): array
    {
        $process=proc_open([$program,'cms:release-identity'],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['file','/dev/null','a']],$pipes,$this->directory,$this->job['env']);
        if(!is_resource($process))throw new \RuntimeException('无法检查候选二进制');
        stream_set_blocking($pipes[1],false);$bytes='';$deadline=microtime(true)+20;
        do{$bytes.=stream_get_contents($pipes[1]);$state=proc_get_status($process);if(!$state['running'])break;usleep(100000);}while(microtime(true)<$deadline&&strlen($bytes)<8388608);
        if($state['running'])proc_terminate($process,9);fclose($pipes[1]);proc_close($process);
        if($state['running']||$state['exitcode']!==0)throw new \RuntimeException('候选二进制无法运行');
        return json_decode($bytes,true,512,JSON_THROW_ON_ERROR);
    }
    private function phase(string $stage,string $message): void { $this->persist(['stage'=>$stage,'message'=>$message]); }
    private function failure(\Throwable $error): void
    {
        $message=$error instanceof \PDOException?'升级数据库操作失败':($error instanceof \RuntimeException?$error->getMessage():'升级执行失败，请查看私有执行资料');
        if(is_file($this->journal))$this->rollback($message);else $this->persist(['stage'=>'failed','message'=>$message]);
    }
    private function rollback(string $reason): void
    {
        $gate=fopen($this->job['gate'],'c+b');
        if(!$gate||!flock($gate,LOCK_EX|LOCK_NB)){$this->persist(['stage'=>'failed','message'=>$reason,'rollback_error'=>'恢复互斥被占用，请运行 cms:update recover']);return;}
        try {
            if(!is_file($this->journal)){$this->persist(['stage'=>'failed','message'=>$reason]);return;}
            $journal=json_decode((string)file_get_contents($this->journal),true,512,JSON_THROW_ON_ERROR);
            $this->phase('rolling_back','升级失败，正在恢复原程序并完整重启');
            if($this->master())$this->command(['stop'],$this->job['stop_timeout']+20);
            if($this->master())throw new \RuntimeException('无法停止待回退程序');
            foreach(array_reverse($journal['applied'])as$path){
                $target=$this->target($path);AssetArchive::path($target);$before=$journal['before'][$path];
                if(is_file($target)){$hash=hash_file('sha256',$target);if($before!==null&&hash_equals($before['sha256'],$hash))continue;if(!hash_equals($this->job['package']['files'][$path]['sha256'],$hash))throw new \RuntimeException('回退文件被外部修改，已保留备份');}
                if($before===null){if(is_file($target)&&!unlink($target))throw new \RuntimeException('新增程序文件撤销失败');}
                else {
                    $original=$this->directory.'/backup/'.$path;
                    if(!is_file($original)||!hash_equals($before['sha256'],hash_file('sha256',$original)))throw new \RuntimeException('回退备份摘要无效');
                    $temporary=dirname($target).'/.cms-rollback-'.$this->job['id'];
                    if(is_link($temporary))throw new \RuntimeException('回退暂存路径无效');
                    if(!copy($original,$temporary)||!chmod($temporary,$before['mode'])||!rename($temporary,$target))throw new \RuntimeException('回退文件发布失败');
                }
            }
            $this->command(['cms:deployment','--check']);$this->command(['start','-d'],$this->job['stop_timeout']+30);
            $this->command(['cms:update-probe'],30);
            if(!$this->master())throw new \RuntimeException('原程序重启失败');
            $this->persist(['rollback_error'=>null,'stage'=>'rolled_back','message'=>$reason.'；已恢复原程序并重启，业务数据未回滚']);
        }catch(\Throwable $error){$this->persist(['stage'=>'failed','message'=>$reason,'rollback_error'=>$error instanceof \PDOException?'恢复状态数据库连接失败':$error->getMessage()]);}
        finally{flock($gate,LOCK_UN);fclose($gate);}
    }
    private function saveJournal(array $journal): void
    {
        $temp=$this->journal.'.tmp';$data=json_encode($journal,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        $h=fopen($temp,'wb');if(!$h)throw new \RuntimeException('恢复日志不可写');
        try{if(!chmod($temp,0600)||fwrite($h,$data)!==strlen($data)||!fflush($h)||(function_exists('fsync')&&!fsync($h)))throw new \RuntimeException('恢复日志持久化失败');}finally{fclose($h);}
        if(!rename($temp,$this->journal))throw new \RuntimeException('恢复日志发布失败');
    }
    private function directory(string $path): void { AssetArchive::path($path);if(!is_dir($path)&&!mkdir($path,0700,true))throw new \RuntimeException('升级目录不可写'); }
    private function command(array $arguments,int $timeout=40): void
    {
        $command=[$this->job['executable']];if($this->job['entry'])$command[]=$this->job['entry'];$command=array_merge($command,$arguments);
        $process=proc_open($command,[0=>['file','/dev/null','r'],1=>['file',$this->directory.'/process.log','a'],2=>['file',$this->directory.'/process.log','a']],$pipes,$this->job['root'],$this->job['env']);
        @chmod($this->directory.'/process.log',0600);
        if(!is_resource($process))throw new \RuntimeException('维护子进程启动失败');
        $deadline=microtime(true)+$timeout;
        do{$state=proc_get_status($process);if(!$state['running'])break;usleep(100000);}while(microtime(true)<$deadline);
        if($state['running']){proc_terminate($process,9);proc_close($process);throw new \RuntimeException('维护子进程执行超时');}
        $code=$state['exitcode'];proc_close($process);if($code!==0)throw new \RuntimeException('维护检查或启动失败：'.$arguments[0]);
    }
    private function master(): int
    {
        $file=$this->job['root'].'/runtime/webman.pid';AssetArchive::path($file);
        $pid=is_file($file)?trim((string)file_get_contents($file)):'';
        if(!ctype_digit($pid)||(int)$pid<2||!posix_kill((int)$pid,0))return 0;
        $entry=$this->job['entry']??('phar://'.$this->job['executable'].'/webman');
        if (PHP_OS_FAMILY==='Linux') {
            $raw=@file_get_contents('/proc/'.$pid.'/cmdline');
            if (!is_string($raw)) throw new \RuntimeException('无法核验升级目标主进程');
            $command=trim(explode("\0",$raw,2)[0]);
            $args=explode("\0",$raw);
            $binaryMatch=$this->job['entry']===null && realpath($args[0]??'')===realpath($this->job['executable']) && in_array($args[1]??'',['start','restart'],true);
        } else {
            $process=proc_open(['/bin/ps','-p',$pid,'-o','command='],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['file','/dev/null','a']],$pipes);
            if(!is_resource($process))throw new \RuntimeException('无法核验升级目标主进程');
            $command=trim(stream_get_contents($pipes[1]));fclose($pipes[1]);proc_close($process);$binaryMatch=false;
        }
        $entries=[$entry,$this->job['root'].'/start.php',$this->job['executable']];$valid=$binaryMatch;
        foreach($entries as $candidate)if($command==='WorkerMan: master process  start_file='.$candidate)$valid=true;
        if(!$valid)throw new \RuntimeException('PID 不属于本站升级目标，已停止操作');
        return(int)$pid;
    }
    private function read(): array
    {
        $q=$this->pdo->prepare('SELECT payload,sha256 FROM `'.$this->job['prefix'].'cms_state_records` WHERE scope=? AND name=?');$q->execute([$this->job['scope'],'task-'.$this->job['id']]);$row=$q->fetch(\PDO::FETCH_ASSOC);
        if(!$row||!hash_equals($row['sha256'],hash('sha256',$row['payload'])))throw new \RuntimeException('升级持久任务不可读');
        return json_decode($row['payload'],true,512,JSON_THROW_ON_ERROR);
    }
    private function persist(array $changes): void
    {
        // Match StateStore's advisory lock; startup acknowledgements merge with progress.
        $database=(string)$this->pdo->query('SELECT DATABASE()')->fetchColumn();$lock='cms-state-'.substr(hash('sha256',$database.':'.$this->job['prefix'].':'.$this->job['scope']),0,53);
        $q=$this->pdo->prepare('SELECT GET_LOCK(?,10)');$q->execute([$lock]);if((int)$q->fetchColumn()!==1)throw new \RuntimeException('升级持久任务锁不可用');
        try{$state=array_replace($this->read(),$changes,['updated_at'=>time()]);$payload=json_encode($state,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);$q=$this->pdo->prepare('UPDATE `'.$this->job['prefix'].'cms_state_records` SET payload=?,sha256=?,updated_at=? WHERE scope=? AND name=?');$q->execute([$payload,hash('sha256',$payload),time(),$this->job['scope'],'task-'.$this->job['id']]);}
        finally{$this->pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lock]);}
    }
    private function download(string $url,string $target,int $size): void
    {
        if(!function_exists('curl_init'))throw new \RuntimeException('升级执行器需要内置 cURL');
        $expected=ReleaseDownload::expected($this->job['release'],$this->job['package']);
        if($url!==$expected)throw new \RuntimeException('升级下载地址不属于正式发布');
        $stream=fopen($target,'xb');if(!$stream)throw new \RuntimeException('升级下载目标已存在');chmod($target,0600);
        try {
            for($i=0;$i<6;$i++){
                $parts=parse_url($url);$host=$parts['host']??'';
                ReleaseDownload::assertUrl($url,$this->job['release']['provider']??'github');
                $location=null;$count=0;$curl=curl_init($url);
                curl_setopt_array($curl,[CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>600,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_USERAGENT=>'webman-cms-updater',CURLOPT_HEADERFUNCTION=>static function($c,$line)use(&$location){if(str_starts_with(strtolower($line),'location:'))$location=trim(substr($line,9));return strlen($line);},CURLOPT_WRITEFUNCTION=>static function($c,$bytes)use($stream,$size,&$count){$count+=strlen($bytes);if($count>$size)return 0;return fwrite($stream,$bytes);}]);
                $ok=curl_exec($curl);$code=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);$errno=curl_errno($curl);curl_close($curl);
                if($ok===false)throw new \RuntimeException($count>$size?'升级包超过清单大小':($errno===28?'升级包下载超时，请稍后重试':'升级下载失败（cURL '.$errno.'）'));
                if($code===200){if($count!==$size)throw new \RuntimeException('升级包大小不一致');fflush($stream);return;}
                if(!in_array($code,[301,302,303,307,308],true)||!$location)throw new \RuntimeException('升级下载响应失败（HTTP '.$code.'）');
                ftruncate($stream,0);rewind($stream);$url=ReleaseDownload::redirect($url,$location);
            }
            throw new \RuntimeException('升级下载重定向过多');
        }finally{fclose($stream);}
    }
}
