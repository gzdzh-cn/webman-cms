<?php
declare(strict_types=1);
namespace app\cms\console;

use app\cms\config\{ConfigFile, ConfigSchema, DeploymentConfig, Entry, Environment, LegacyConfigExport};
use app\cms\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

class DeploymentCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('check', null, InputOption::VALUE_NONE)->addOption('json', null, InputOption::VALUE_NONE);
        if ($this->getName() === 'cms:config-migrate') $this->addOption('source', null, InputOption::VALUE_REQUIRED, 'Existing source deployment', Entry::directory())->addOption('apply', null, InputOption::VALUE_NONE);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $result = match ($this->getName()) {
                'cms:config' => DeploymentConfig::report(),
                'cms:paths' => $this->paths(),
                'cms:deployment' => $this->preflight(),
                'cms:runtime' => $this->runtime(),
                'cms:config-migrate' => $this->migrate((string)$input->getOption('source'), (bool)$input->getOption('apply')),
                default => throw new \LogicException('Unknown deployment command'),
            };
            $output->writeln(json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
            return $result['ok'] ? 0 : 1;
        } catch (\Throwable $error) {
            $output->writeln(json_encode(['ok'=>false, 'error'=>$error->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            return 1;
        }
    }
    /** No process start, port reservation or business writes; binaries verify the installed database. */
    private function preflight(): array
    {
        \app\cms\support\BinaryStartup::check();
        DeploymentConfig::report();
        $runtime=$this->runtime(); $paths=$this->paths();
        $assets=(new \app\cms\maintenance\AssetMaintenance())->inspect(true);
        return ['ok'=>$runtime['ok']&&$paths['ok']&&$assets['ok'],'mode'=>'read_only_preflight',
            'configuration_valid'=>true,'runtime'=>$runtime,'paths'=>$paths['paths'],
            'assets'=>$assets,'database_accessed'=>\app\cms\support\EmbeddedResources::binary(),'server_started'=>false,'external_tools'=>false];
    }
    private function paths(): array
    {
        $rows=[]; $ok=true;
        foreach (Paths::DIRECTORIES as $name=>$_) {
            $path=Paths::directory($name); if(!str_starts_with($path,'phar://'))Entry::assertPath($path);
            $exists=is_dir($path); $ancestor=$path;
            while (!file_exists($ancestor) && dirname($ancestor)!==$ancestor) $ancestor=dirname($ancestor);
            $write=!in_array($name,['assets_dir'],true)&&!str_starts_with($path,'phar://');
            $available=$exists ? is_readable($path) && (!$write || is_writable($path)) : ($name!=='assets_dir' && is_dir($ancestor) && is_writable($ancestor));
            if (!$available) $ok=false;
            $rows[$name]=['path'=>$path, 'exists'=>$exists, 'required'=>$write?'read_write':'read', 'ok'=>$available];
        }
        return ['ok'=>$ok, 'paths'=>$rows];
    }
    private function runtime(): array
    {
        $required=['ctype','curl','dom','fileinfo','filter','gd','hash','iconv','json','mbstring','openssl','pcntl','PDO','pdo_mysql','Phar','posix','session','SimpleXML','tokenizer','xml','zip','zlib'];
        if (\Phar::running(false)) $required[]='redis';
        $missing=array_values(array_filter($required,static fn($name)=>!extension_loaded($name)));
        $binary=(bool)\Phar::running(false);
        $locked=true;$iniMismatch=[];
        if($binary){
            $build=json_decode(file_get_contents(Paths::resources('binary-build.json')),true,32,JSON_THROW_ON_ERROR);
            $locked=\app\cms\maintenance\RuntimeProvider::platform()===($build['platform']??null)&&PHP_VERSION===($build['php']??null);
            $actual=get_loaded_extensions();$expected=$build['extensions']??[];sort($actual);sort($expected);$locked=$locked&&$actual===$expected;
            foreach($build['ini']??[] as $name=>$expected){
                $actual=(string)ini_get($name);
                $matches=in_array((string)$expected,['0','1'],true)
                    ? (filter_var($actual,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE)===((string)$expected==='1'))
                    : $actual===(string)$expected;
                if(!$matches)$iniMismatch[]=$name;
            }
            $locked=$locked&&!$iniMismatch;
        }
        return ['ok'=>!$missing&&PHP_VERSION_ID>=80200&&$locked,
            'php'=>PHP_VERSION,'platform'=>PHP_OS_FAMILY.'/'.php_uname('m'),'binary'=>$binary,'missing_extensions'=>$missing,'ini_mismatch'=>$iniMismatch,'pcre_jit'=>(bool)ini_get('pcre.jit'),'redis'=>extension_loaded('redis')];
    }
    private function migrate(string $source, bool $apply): array
    {
        $source=Entry::absolute($source); Entry::assertPath($source);
        if (DeploymentConfig::document()) throw new \RuntimeException('YAML 已存在配置，拒绝覆盖；请逐字段人工合并');
        $data=(new LegacyConfigExport())->read($source); $flat=ConfigSchema::flatten($data);
        ConfigFile::assertWritable();
        if ($apply) {
            $backup=dirname(DeploymentConfig::file()).'/.migration-'.gmdate('YmdHis').'-'.bin2hex(random_bytes(4));
            Paths::ensureDirectory($backup,0700);
            foreach(scandir($source.'/config') as $name) {
                if (!preg_match('/^[a-z][a-z0-9_-]*\.php$/D',$name))continue;
                $from=$source.'/config/'.$name;Entry::assertPath($from);$stat=lstat($from);
                if(!$stat||!is_file($from)||$stat['nlink']!==1||$stat['size']>1048576)throw new \RuntimeException('旧配置文件状态无效');
                $bytes=file_get_contents($from);$handle=fopen($backup.'/'.$name,'xb');
                if(!$handle)throw new \RuntimeException('无法创建旧配置备份');
                try{chmod($backup.'/'.$name,0600);if(fwrite($handle,$bytes)!==strlen($bytes)||!fflush($handle))throw new \RuntimeException('旧配置备份失败');}finally{fclose($handle);}
            }
            ConfigFile::write($data,false);
            if (ConfigSchema::flatten(DeploymentConfig::document())!==$flat)throw new \RuntimeException('迁移结果与源参数不一致');
        }
        $overridden=[];$env=Environment::overrides();
        foreach($flat as $key=>$_){$name=ConfigSchema::fields()[$key][1];if($name!==''&&array_key_exists($name,$env))$overridden[]=$key;}
        return ['ok'=>true,'mode'=>$apply?'applied':'read_only','fields'=>array_keys($flat),'environment_overrides_preserved'=>$overridden,'restart_required'=>$apply,'private_backup_created'=>$apply];
    }
}
