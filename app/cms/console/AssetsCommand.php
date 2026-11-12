<?php
declare(strict_types=1);
namespace app\cms\console;
use app\cms\maintenance\AssetMaintenance;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssetsCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cms:assets')->setDescription('Check, verify, build or restore versioned CMS assets')
            ->addArgument('action',InputArgument::OPTIONAL,'check|verify|build|restore|pack','check')
            ->addOption('apply',null,InputOption::VALUE_NONE,'Write build/restore outputs; default is read-only');
        foreach(['archive','destination','input','output']as$name)$this->addOption($name,null,InputOption::VALUE_REQUIRED,'Absolute '.$name.' path');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $action=(string)$input->getArgument('action');$apply=(bool)$input->getOption('apply');$service=new AssetMaintenance();$options=[];
            foreach(['archive','destination','input','output']as$name)$options[$name]=$input->getOption($name);
            if(in_array($action,['check','verify'],true)){
                if($apply||array_filter($options))throw new \InvalidArgumentException('check/verify 不接受写入或外部路径参数');
                $report=$service->inspect($action==='verify');$output->writeln(json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));return $report['ok']?0:1;
            }
            if($action==='build') { if($options['archive']||$options['destination'])throw new \InvalidArgumentException('build 使用 --input/--output');$result=$service->build($options,$apply); }
            if($action==='restore') { if($options['input']||$options['output'])throw new \InvalidArgumentException('restore 使用 --archive/--destination');$result=$service->restore($options,$apply); }
            if($action==='pack') { if($options['input']||$options['output']||$options['destination'])throw new \InvalidArgumentException('pack 仅使用 --archive');$result=$service->pack($options,$apply); }
            if(!isset($result))throw new \InvalidArgumentException('操作只能是 check、verify、build、restore 或 pack');
            $output->writeln(json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));return 0;
        } catch(\Throwable $error) { $output->writeln(json_encode(['ok'=>false,'error'=>$error->getMessage()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));return 1; }
    }
}
