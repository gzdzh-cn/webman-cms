<?php
declare(strict_types=1);
namespace app\cms\console;

use app\cms\maintenance\DeploymentPermissions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

class PermissionsCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('检查或修复本站受管外置文件权限，不改变所有者')
            ->addOption('check', null, InputOption::VALUE_NONE, '只读检查（默认）')
            ->addOption('fix', null, InputOption::VALUE_NONE, '实际修复权限')
            ->addOption('json', null, InputOption::VALUE_NONE, 'JSON 报告（默认）');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('check') && $input->getOption('fix')) throw new \RuntimeException('--check 和 --fix 不能同时使用');
        $result = (new DeploymentPermissions())->run((bool)$input->getOption('fix'));
        $output->writeln(json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
        return $result['ok'] ? Command::SUCCESS : Command::FAILURE;
    }
}
