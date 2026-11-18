<?php
declare(strict_types=1);
namespace app\cms\console;
use app\cms\maintenance\DatabaseBackup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class DatabaseBackupCommand extends Command
{
    protected function configure():void {$this->setDescription('Private database backup with checksum validation, checked restore and explicit before-image cleanup')->addArgument('operation',InputArgument::REQUIRED,'list|tasks|create|preview|restore|status|rollback|cleanup')->addOption('id',null,InputOption::VALUE_REQUIRED,'Local backup ID or task token')->addOption('plan',null,InputOption::VALUE_REQUIRED,'Exact cleanup confirmation returned by the preview')->addOption('apply',null,InputOption::VALUE_NONE,'Confirm the exact previewed restore, rollback or cleanup');}
    protected function execute(InputInterface $input,OutputInterface $output):int
    {
        try{$service=new DatabaseBackup();$operation=$input->getArgument('operation');$id=(string)$input->getOption('id');if($operation==='restore'&&!$input->getOption('apply'))throw new \InvalidArgumentException('恢复需先preview检查完整差异，再以原token指定--apply');$result=match($operation){'list'=>$service->backups(),'tasks'=>$service->tasks(),'create'=>$service->create(),'preview'=>$service->preview($id),'restore'=>$service->restore($id),'status'=>$service->status($id),'rollback'=>$service->rollback($id,(bool)$input->getOption('apply')),'cleanup'=>$input->getOption('apply')?$service->cleanup($id,(string)$input->getOption('plan')):$service->cleanupPreview($id),default=>throw new \InvalidArgumentException('操作需为list/tasks/create/preview/restore/status/rollback/cleanup')};$output->writeln(json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));return 0;}catch(\Throwable$e){$output->writeln(json_encode(['error'=>$e instanceof \PDOException?'数据库操作失败，请检查连接及私有日志':$e->getMessage()],JSON_UNESCAPED_UNICODE));return 1;}
    }
}
