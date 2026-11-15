<?php
declare(strict_types=1);
namespace app\cms\console;

use app\cms\cache\CachePolicy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheModeCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cms:cache-mode')->setDescription('检查或切换 CMS 缓存模式')
            ->addOption('check', null, InputOption::VALUE_NONE, '检查当前模式及 Redis')
            ->addOption('set', null, InputOption::VALUE_REQUIRED, '切换到 1（runtime）或 3（Redis）；开发行为使用 app.debug');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $check = (bool)$input->getOption('check');
            $mode = $input->getOption('set');
            if ($check === ($mode !== null)) throw new \InvalidArgumentException('请选择 --check 或 --set=1|3');
            $result = $check ? CachePolicy::health() : CachePolicy::transition((string)$mode);
            $output->writeln(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return $check && !$result['redis_available'] ? 1 : 0;
        } catch (\Throwable $error) {
            $output->writeln($error instanceof \PDOException ? '持久化数据库检查失败' : $error->getMessage());
            return 1;
        }
    }
}
