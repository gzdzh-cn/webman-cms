<?php
declare(strict_types=1);
namespace app\cms\console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class CacheClearCommand extends Command
{
    protected function configure():void{$this->setName('cms:cache-clear')->setDescription('Invalidate site caches and clear rebuildable files after workers stop')->addOption('all',null,InputOption::VALUE_NONE,'Also invalidate this site\'s sessions; preserve logs and control files');}
    protected function execute(InputInterface $input,OutputInterface $output):int
    {
        try{$result=(new \app\cms\maintenance\CacheCleaner())->clear((bool)$input->getOption('all'));$output->writeln(json_encode($result,JSON_UNESCAPED_UNICODE));return 0;}
        catch(\Throwable $e){$output->writeln($e instanceof \PDOException?'持久化数据库检查失败':$e->getMessage());return 1;}
    }
}
