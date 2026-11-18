<?php
declare(strict_types=1);
namespace app\cms\console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface,InputOption};
use Symfony\Component\Console\Output\OutputInterface;
class UpdateProbeCommand extends Command
{
    protected function configure(): void { $this->setName('cms:update-probe')->setDescription('Internal fresh-process deployment and migration probe')->addOption('migrate',null,InputOption::VALUE_NONE); }
    protected function execute(InputInterface $input,OutputInterface $output): int
    {
        try {
            $pdo=\app\cms\database\Connection::environment();$pdo->query('SELECT 1')->fetchColumn();
            if ($input->getOption('migrate')) \app\cms\database\Migrations::apply($pdo,\app\cms\database\TableNames::admin(),false,\app\cms\database\TableNames::cms());
            if (!(new \app\cms\maintenance\AssetMaintenance())->inspect(true)['ok']) throw new \RuntimeException('资源验证失败');
            $deployment=\app\cms\service\AdminUpdateService::deployment();
            $output->writeln(json_encode(['ok'=>true,'version'=>$deployment['version'],'build_id'=>$deployment['build_id']]));return 0;
        } catch (\Throwable $error) { $output->writeln($error instanceof \PDOException?'升级数据库检查失败':$error->getMessage());return 1; }
    }
}
