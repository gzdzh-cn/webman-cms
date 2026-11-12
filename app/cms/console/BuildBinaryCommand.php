<?php
declare(strict_types=1);
namespace app\cms\console;
use app\cms\config\{DeploymentConfig,ConfigSchema};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface,InputOption};
use Symfony\Component\Console\Output\{ConsoleOutputInterface,OutputInterface};
class BuildBinaryCommand extends Command
{
    protected function configure():void
    {
        $this->setName('cms:build-bin')->setDescription('构建锁定运行时的多平台二进制')
            ->addOption('output',null,InputOption::VALUE_REQUIRED,'输出根目录（默认 bin）')
            ->addOption('binary-name',null,InputOption::VALUE_REQUIRED,'生成的二进制文件名（默认 cms）')
            ->addOption('target',null,InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,'目标平台，可重复指定')
            ->addOption('build-runtime',null,InputOption::VALUE_REQUIRED,'运行时缓存目录（默认 bin/build-runtime）')
            ->addOption('embedded-resources',null,InputOption::VALUE_REQUIRED,'内置资源 JSON 字符串数组')
            ->addOption('runtime',null,InputOption::VALUE_REQUIRED,'单平台锁定 micro.sfx 文件');
    }
    protected function execute(InputInterface $input,OutputInterface $output):int
    {
        try {
            if(\Phar::running(false))throw new \RuntimeException('请在源码构建环境执行');
            $options=DeploymentConfig::group('build');
            foreach(['output','binary-name','build-runtime','embedded-resources']as$name)if($input->getOption($name)!==null){$key=match($name){'embedded-resources'=>'embedded_resources','binary-name'=>'binary_name',default=>$name};$options[$key]=ConfigSchema::coerce('build.'.$key,$input->getOption($name));}
            if($input->getOption('target'))$options['target']=ConfigSchema::coerce('build.target',$input->getOption('target'));
            if($input->getOption('runtime')&&count($options['target'])>1)throw new \RuntimeException('--runtime 仅允许单平台');
            if(ini_get('phar.readonly')) {
                $args=[PHP_BINARY,'-d','phar.readonly=0',\app\cms\Paths::code('webman'),'cms:build-bin','--output='.$options['output'],'--binary-name='.$options['binary_name'],'--build-runtime='.$options['build-runtime'],'--embedded-resources='.json_encode($options['embedded_resources'],JSON_THROW_ON_ERROR)];
                foreach($options['target']as$target)$args[]='--target='.$target;
                if($input->getOption('runtime'))$args[]='--runtime='.$input->getOption('runtime');
                if($output->isQuiet())$args[]='--quiet';
                if(!$output->isDecorated())$args[]='--no-ansi';
                $process=proc_open($args,[0=>STDIN,1=>STDOUT,2=>STDERR],$pipes,\app\cms\config\Entry::directory());
                if(!is_resource($process))throw new \RuntimeException('无法启动受控构建子进程');return proc_close($process);
            }
            $started=microtime(true);
            $timezone=new \DateTimeZone((string)DeploymentConfig::get('app.timezone'));
            $progressOutput=$output instanceof ConsoleOutputInterface?$output->getErrorOutput():$output;
            $progress=static function(string $message)use($progressOutput,$started,$timezone):void{
                $progressOutput->writeln(\Symfony\Component\Console\Formatter\OutputFormatter::escape(sprintf('[%s +%.1fs] %s',(new \DateTimeImmutable('now',$timezone))->format('H:i:s'),microtime(true)-$started,$message)));
            };
            $result=(new \app\cms\maintenance\BinaryBuilder($progress))->build($options['output'],$options['target'],$options['build-runtime'],$options['embedded_resources'],$input->getOption('runtime'),$options['binary_name']);
            $progress('构建完成，耗时 '.number_format(microtime(true)-$started,1).' 秒；共发布 '.count($result['releases']).' 个平台。');
            $output->writeln(json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));return 0;
        }catch(\Throwable$error){$output->writeln('<error>构建已停止：'.\Symfony\Component\Console\Formatter\OutputFormatter::escape($error->getMessage()).'</error>');return 1;}
    }
}
