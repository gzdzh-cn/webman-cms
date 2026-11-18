<?php
declare(strict_types=1);
namespace app\cms\console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument,InputInterface,InputOption};
use Symfony\Component\Console\Output\OutputInterface;
class ReleaseCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cms:release')->setDescription('Build upgrade packages, upload a draft, or explicitly publish a release')
            ->addArgument('action',InputArgument::REQUIRED,'pack|publish|finalize')
            ->addOption('output',null,InputOption::VALUE_REQUIRED,'Absolute new output directory')
            ->addOption('binary',null,InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,'Built standard binary directory',[])
            ->addOption('no-source',null,InputOption::VALUE_NONE,'Only package specified binaries')
            ->addOption('notes',null,InputOption::VALUE_REQUIRED,'Release notes file')
            ->addOption('provider',null,InputOption::VALUE_REQUIRED,'gitee (default) or github','gitee')
            ->addOption('id',null,InputOption::VALUE_REQUIRED,'Draft release ID');
    }
    protected function execute(InputInterface $input,OutputInterface $output): int
    {
        try {
            $service=new \app\cms\maintenance\ReleaseBuilder();
            $result=match($input->getArgument('action')) {
                'pack'=>$service->pack((string)$input->getOption('output'),$input->getOption('binary'),!$input->getOption('no-source')),
                'publish'=>$service->publish((string)$input->getOption('output'),(string)$input->getOption('notes'),(string)$input->getOption('provider')),
                'finalize'=>$service->finalize((string)$input->getOption('id'),(string)$input->getOption('provider')),
                default=>throw new \InvalidArgumentException('操作必须为 pack、publish 或 finalize')
            };
            $output->writeln(json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));return 0;
        } catch (\Throwable $error) { $output->writeln($error instanceof \GuzzleHttp\Exception\GuzzleException?'发布请求失败；请检查预览版或草稿和发布端凭据后重试':$error->getMessage());return 1; }
    }
}
