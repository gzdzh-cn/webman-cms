<?php
declare(strict_types=1);
namespace app\cms\console;

use app\cms\config\{DeploymentConfig, Environment};
use app\cms\Paths;
use app\cms\support\ProcessLauncher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** Keep a container alive across an authorized replacement of the CMS master. */
class ContainerCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if(PHP_OS_FAMILY!=='Linux'||ProcessLauncher::managed())throw new \RuntimeException('容器入口需要 Linux 和 standalone 进程模式');
        \app\cms\support\BinaryStartup::check();
        Paths::ensureDirectory(Paths::runtime('locks'),0700);
        $path=Paths::runtime('locks/container.lock');\app\cms\config\Entry::assertPath($path);
        $lock=fopen($path,'c+b');
        if(!$lock||!flock($lock,LOCK_EX|LOCK_NB))throw new \RuntimeException('已有容器入口正在运行');
        chmod($path,0600);$stopping=false;
        pcntl_async_signals(true);
        foreach([SIGTERM,SIGINT,SIGQUIT]as$signal)pcntl_signal($signal,static function()use(&$stopping):void{$stopping=true;});
        try{
            if(ProcessLauncher::master())throw new \RuntimeException('程序已启动，请使用单一容器入口');
            $child=proc_open(ProcessLauncher::command(['start','-d']),[0=>['file','/dev/null','r'],1=>STDOUT,2=>STDERR],$pipes,Paths::root(),Environment::processEnvironment());
            if(!is_resource($child)||proc_close($child)!==0)throw new \RuntimeException('容器内程序启动失败');
            $missingSince=null;
            while(!$stopping){
                $master=ProcessLauncher::master();
                if($master)$missingSince=null;
                else{$missingSince??=microtime(true);if(microtime(true)-$missingSince>\app\cms\service\AdminRestartService::restartTimeout()+60)throw new \RuntimeException('程序主进程未恢复，容器入口退出');}
                usleep(250000);
            }
            $master=ProcessLauncher::master();
            if($master){
                posix_kill($master,SIGQUIT);$deadline=microtime(true)+(int)DeploymentConfig::get('server.stop_timeout')+5;
                while(microtime(true)<$deadline&&ProcessLauncher::master()===$master)usleep(100000);
                if(ProcessLauncher::master()===$master){posix_kill($master,SIGINT);$deadline=microtime(true)+10;while(microtime(true)<$deadline&&ProcessLauncher::master()===$master)usleep(100000);}
                if(ProcessLauncher::master()===$master)throw new \RuntimeException('程序停止超时');
            }
            return 0;
        }finally{flock($lock,LOCK_UN);fclose($lock);}
    }
}
