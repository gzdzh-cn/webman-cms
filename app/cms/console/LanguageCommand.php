<?php
declare(strict_types=1);
namespace app\cms\console;

use app\cms\service\LanguageDictionary;
use app\cms\service\LanguagePolicy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/** Offline maintenance. No content synchronization or translation overwrite. */
abstract class LanguageCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('action', InputArgument::OPTIONAL, 'status, migrate, rebuild', 'status');
        $this->addOption('apply', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $policy = new LanguagePolicy();
            $result = match ($input->getArgument('action')) {
                'status' => ['main'=>$policy->main(), 'enabled'=>$policy->enabled(), 'settings'=>$policy->settings(), 'languages'=>$policy->all()],
                'migrate' => $policy->migrate((bool)$input->getOption('apply')),
                'rebuild' => $this->rebuild($policy, (bool)$input->getOption('apply')),
                default => throw new \InvalidArgumentException('请选择 status、migrate 或 rebuild'),
            };
            $output->writeln(json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
            return 0;
        } catch (\Throwable $error) {
            $output->writeln(json_encode(['error'=>$error instanceof \PDOException ? '数据库操作失败' : $error->getMessage()], JSON_UNESCAPED_UNICODE));
            return 1;
        }
    }

    private function rebuild(LanguagePolicy $policy, bool $apply): array
    {
        $dictionary = new LanguageDictionary(); $results = [];
        foreach ($policy->all() as $language) {
            $value = $apply ? $dictionary->cached($language['mark'], true) : $dictionary->resolve($language['mark']);
            $results[] = ['language'=>$language['mark'], 'count'=>count($value['entries']), 'revision'=>$value['revision']];
        }
        if ($apply) \app\cms\service\LanguageAssetService::generated();
        return ['applied'=>$apply, 'languages'=>$results];
    }
}
