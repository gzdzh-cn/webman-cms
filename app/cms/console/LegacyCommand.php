<?php
declare(strict_types=1);
namespace app\cms\console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
abstract class LegacyCommand extends Command
{
    private const COMMANDS=['cms:migrate-navigation'=>'migrate_admin_navigation.php','cms:migrate-engines'=>'migrate_table_engines.php','cms:repair-single-pages'=>'repair_single_pages.php'];
    protected function configure():void
    {
        foreach(['dry-run','apply','verify-backup','maintenance-confirmed']as$name)$this->addOption($name,null,InputOption::VALUE_NONE);
        foreach(['expect','rollback','prefixes','plan','fingerprint','backup-dir','ids','admin-lang']as$name)$this->addOption($name,null,InputOption::VALUE_REQUIRED);
    }
    protected function execute(InputInterface $input,OutputInterface $output):int
    {
        $names=['cms:migrate-navigation'=>'navigation','cms:migrate-engines'=>'engines','cms:repair-single-pages'=>'single-pages'];
        $args=\app\cms\support\ProcessLauncher::command(['cms:legacy-executor',$names[$this->getName()]]);
        foreach($input->getOptions()as$key=>$value){if(in_array($key,['help','quiet','verbose','version','ansi','no-ansi','no-interaction'],true)||$value===null||$value===false)continue;$args[]='--'.$key.($value===true?'':'='.$value);}
        $process=proc_open($args,[0=>STDIN,1=>STDOUT,2=>STDERR],$pipes,\app\cms\config\Entry::directory());
        if(!is_resource($process))throw new \RuntimeException('Unable to start maintenance command');return proc_close($process);
    }
}
