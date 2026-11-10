<?php
declare(strict_types=1);
namespace app\cms\console;

use app\cms\install\InstallerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallerCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cms:install')->setDescription('Install a new website or seal its installation package')
            ->addOption('input', null, InputOption::VALUE_REQUIRED, 'Absolute 0600 JSON input')
            ->addOption('check', null, InputOption::VALUE_NONE, 'Inspect without installing or sealing')
            ->addOption('preflight', null, InputOption::VALUE_NONE, 'Validate SQL and print the exact replacement plan without deleting tables')
            ->addOption('seal', null, InputOption::VALUE_NONE, 'Verify the installed database and archive installation files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($input->getOption('seal') && ($input->getOption('input') !== null || $input->getOption('preflight'))) {
                throw new \InvalidArgumentException('--seal cannot be combined with --input or --preflight');
            }
            if ($input->getOption('preflight') && ($input->getOption('input') === null || $input->getOption('check'))) throw new \InvalidArgumentException('--preflight requires --input and cannot be combined with --check');
            $service = new InstallerService();
            if ($input->getOption('seal')) {
                $result = $service->seal((bool)$input->getOption('check'));
            } elseif ($input->getOption('input') !== null) {
                $data = Maintenance::privateInput((string)$input->getOption('input'));
                $result = $input->getOption('check') ? $service->inspect($data) : ($input->getOption('preflight') ? $service->preflight($data) : $service->install($data));
            } else {
                $result = $service->status(true);
                if (!$result['installed'] && empty($result['blocked'])) $result += $service->environment();
            }
            $output->writeln(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return 0;
        } catch (\Throwable $error) {
            $failure=['error' => $error instanceof \PDOException ? 'Database operation failed' : $error->getMessage()];
            if($error instanceof \app\cms\install\InstallerValidationException)$failure['errors']=$error->errors;
            $output->writeln(json_encode($failure, JSON_UNESCAPED_UNICODE));
            return 1;
        }
    }
}
