<?php
declare(strict_types=1);
namespace app\cms\console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument,InputInterface,InputOption};
use Symfony\Component\Console\Output\OutputInterface;
class UpdateCommand extends Command
{
    protected function configure():void{$this->setName('cms:update')->setDescription('Inspect or recover a durable local upgrade task')->addArgument('action',InputArgument::OPTIONAL,'status|recover','status')->addOption('id',null,InputOption::VALUE_REQUIRED,'Task ID');}
    protected function execute(InputInterface $input,OutputInterface $output):int
    {
        try{$service=new \app\cms\service\AdminUpdateService();$id=(string)$input->getOption('id');$result=match($input->getArgument('action')){'refresh'=>$service->check(true),'status'=>$service->status($id),'recover'=>$service->recover($id),default=>throw new \InvalidArgumentException('操作必须为 status 或 recover')};$output->writeln(json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));return 0;}
        catch(\Throwable $e){$output->writeln($e instanceof \PDOException?'数据库不可用':$e->getMessage());return 1;}
    }
}
