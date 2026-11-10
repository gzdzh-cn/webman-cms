<?php
declare(strict_types=1);
namespace app\cms\console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
abstract class MaintenanceCommand extends Command
{
    protected function configure():void{$this->addOption('dry-run',null,InputOption::VALUE_NONE)->addOption('apply',null,InputOption::VALUE_NONE)->addOption('input',null,InputOption::VALUE_REQUIRED)->addOption('missing-only',null,InputOption::VALUE_NONE);}
    protected function execute(InputInterface $input,OutputInterface $output):int
    {try{if($input->getOption('apply')&&$input->getOption('dry-run'))throw new \InvalidArgumentException('Choose either --apply or --dry-run');$result=match($this->getName()){'cms:database-init'=>Maintenance::initialize(!$input->getOption('apply')),'cms:migrate'=>Maintenance::migrate((bool)$input->getOption('apply')),'cms:admin-bootstrap'=>Maintenance::bootstrap(Maintenance::privateInput((string)$input->getOption('input'))),'cms:sync-menu'=>Maintenance::syncMenu((bool)$input->getOption('apply')),default=>throw new \LogicException('Unsupported maintenance command')};$output->writeln(json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));return 0;}catch(\Throwable $e){$output->writeln(json_encode(['error'=>$e instanceof \PDOException?'Database operation failed':$e->getMessage()],JSON_UNESCAPED_UNICODE));return 1;}}
}
