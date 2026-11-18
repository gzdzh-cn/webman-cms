<?php
declare(strict_types=1);
namespace app\cms\console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class StateMigrateCommand extends Command
{
    protected function configure():void{$this->setName('cms:state-migrate')->setDescription('Audit/import legacy runtime state without deleting sources')->addOption('source',null,InputOption::VALUE_REQUIRED,'Legacy runtime directory')->addOption('apply',null,InputOption::VALUE_NONE,'Apply import; default is read-only')->addOption('register-installed',null,InputOption::VALUE_NONE,'Register this existing site after a successful migration');}
    protected function execute(InputInterface $input,OutputInterface $output):int
    {
        try{
            $apply=(bool)$input->getOption('apply');if($apply)\app\cms\maintenance\CacheCleaner::assertStopped();
            if($input->getOption('register-installed')&&!$apply)throw new \InvalidArgumentException('登记现有网站必须同时指定--apply');
            $result=(new \app\cms\maintenance\StateMigration())->run($input->getOption('source')?:null,$apply);
            if($input->getOption('register-installed'))(new \app\cms\install\InstallerService())->registerExisting(\app\cms\persistence\Connection::open(),\app\cms\persistence\Connection::prefix(),\app\cms\database\TableNames::admin());
            $output->writeln(json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));return 0;
        }catch(\Throwable $e){$output->writeln($e instanceof \PDOException?'持久化数据库操作失败':$e->getMessage());return 1;}
    }
}
