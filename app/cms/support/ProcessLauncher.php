<?php
declare(strict_types=1);
namespace app\cms\support;

use app\cms\Paths;
use app\cms\config\DeploymentConfig;
use app\cms\config\Entry;

/** Fixed executable/arguments only. HTTP input never supplies command paths. */
final class ProcessLauncher
{
    public static function command(array $arguments): array
    {
        $binary=\Phar::running(false);
        if($binary)return array_merge([$binary],$arguments);
        return array_merge([PHP_BINARY,Paths::code('webman')],$arguments);
    }
    public static function managed(): bool { return DeploymentConfig::get('server.process_manager')==='systemd'; }
    public static function assertManaged(): void
    {
        if(!self::managed())return;
        $id=\app\cms\config\Environment::processEnvironment()['INVOCATION_ID']??'';
        if(!preg_match('/^[a-f0-9]{32}$/D',(string)$id))throw new \RuntimeException('systemd 模式必须由配套服务单元启动');
    }
    public static function master(): int
    {
        $file=Paths::runtime('webman.pid');Entry::assertPath($file);clearstatcache(true,$file);
        if(!is_file($file))return 0;
        $value=trim((string)file_get_contents($file));
        if(!ctype_digit($value)||(int)$value<2)throw new \RuntimeException('进程 PID 无效');
        $pid=(int)$value;if(!posix_kill($pid,0))return 0;
        if(PHP_OS_FAMILY==='Linux') {
            $raw=@file_get_contents('/proc/'.$pid.'/cmdline');
            if(!is_string($raw))throw new \RuntimeException('无法核验主进程身份');
            $command=trim(explode("\0",$raw,2)[0]);
        } else {
            $proc=proc_open(['/bin/ps','-p',(string)$pid,'-o','command='],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['file','/dev/null','a']],$pipes);
            if(!is_resource($proc))throw new \RuntimeException('无法核验主进程身份');
            $command=trim(stream_get_contents($pipes[1]));fclose($pipes[1]);proc_close($proc);
        }
        $entries=[Paths::code('webman'),Paths::code('start.php')];if(\Phar::running(false)){$entries[]=\Phar::running(false);$entries[]=Paths::code('webman');}
        $valid=false;foreach($entries as$entry)if($command==='WorkerMan: master process  start_file='.$entry)$valid=true;
        // Some micro/emulation runtimes cannot change argv. Verify the exact
        // executable and selected external config identity instead of a substring.
        if(!$valid&&PHP_OS_FAMILY==='Linux'&&\Phar::running(false)){
            $args=explode("\0",$raw);
            $executable=(string)($args[0]??'');
            if($executable!==''&&!str_starts_with($executable,'/')){
                $cwd=@readlink('/proc/'.$pid.'/cwd');
                $executable=is_string($cwd)?$cwd.'/'.$executable:'';
            }
            $expected=realpath(\Phar::running(false));
            if($expected!==false&&$executable!==''&&realpath($executable)===$expected&&in_array($args[1]??'', ['start','restart'],true)){
                $environment=@file_get_contents('/proc/'.$pid.'/environ');
                if(is_string($environment)){
                    $values=[];foreach(explode("\0",$environment)as$line){$pair=explode('=',$line,2);if(count($pair)===2)$values[$pair[0]]=$pair[1];}
                    $current=\app\cms\config\Environment::processEnvironment();$valid=true;
                    $keys=['CMS_ENV_FILE','CMS_CONFIG_FILE'];
                    // A topology override changes the next launch, not this site's identity.
                    foreach(\app\cms\config\ConfigSchema::fields()as$field=>$spec)if($spec[1]!==''&&!str_starts_with($field,'redis.')&&$field!=='performance.separate_task_processes')$keys[]=$spec[1];
                    foreach($keys as$key)if(($values[$key]??null)!==($current[$key]??null))$valid=false;
                }
            }
        }
        if(!$valid)throw new \RuntimeException('PID 不属于本站程序');return$pid;
    }
    public static function detached(string $kind, array $arguments): never
    {
        if(EmbeddedResources::binary()&&$kind==='install')exit(1);
        if(!in_array($kind,['admin','install'],true)||!function_exists('pcntl_fork')||!function_exists('posix_setsid'))exit(1);
        $pid=pcntl_fork();if($pid<0)exit(1);if($pid>0)exit(0);if(posix_setsid()<0)exit(1);
        $pid=pcntl_fork();if($pid<0)exit(1);if($pid>0)exit(0);
        try{
            if($kind==='admin'&&count($arguments)===1&&preg_match('/^[a-f0-9]{32}$/D',$arguments[0]))(new \app\cms\service\AdminRestartService())->execute($arguments[0]);
            elseif($kind==='install'&&count($arguments)===2&&preg_match('/^[a-f0-9]{24}$/D',$arguments[0])&&ctype_digit($arguments[1]))(new \app\cms\install\InstallerHandoff())->execute($arguments[0],(int)$arguments[1]);
            else exit(1);
        }catch(\Throwable){fwrite(STDERR,"CMS restart executor failed.\n");exit(1);}exit(0);
    }
}
