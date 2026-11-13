<?php
declare(strict_types=1);
namespace app\cms\install;

use app\cms\database\SqlImporter;
use app\cms\database\Migrations;
use app\cms\database\DatabaseSettings;
use app\cms\persistence\Schema;
use app\cms\persistence\LegacyMarkerStore;
use PDO;
use RuntimeException;

/** Explicit file-unlocked installation; bounded exact-name replacement and durable resumability. */
final class InstallerService
{
    private string $root;
    private const JOURNAL = 'config/cms-installing.json';
    private const PLAN = 'config/cms-install-plan.json';
    private const FORMAT = 3;

    public function __construct(?string $root = null) { $this->root = rtrim($root ?? \app\cms\Paths::root(), '/'); }
    private function path(string $relative): string { return $this->root === \app\cms\Paths::root() ? \app\cms\Paths::logical($relative) : $this->root . '/' . $relative; }
    private function fail(string $field, string $message): never { throw new InstallerValidationException([$field=>$message], $message); }

    private function legacyResumeService(array $input=[]): ?InstallerLegacyV2
    {
        $record=$this->readPrivate(self::JOURNAL);
        if(!$record||($record['version']??null)!==2||($record['phase']??null)==='complete')return null;
        if(trim((string)($input['theme']??''))!=='')$this->fail('theme','已有旧版安装任务，请使用原安装参数通过 CLI 续装；不能将新模板选择应用到旧任务');
        return new InstallerLegacyV2($this->root);
    }

    private function assertPaths(): void
    {
        foreach (['config','public','install','install/database','.env',self::JOURNAL,self::PLAN,'config/cms-installing.lock','config/cms-installed.json'] as $part) {
            if (is_link($this->path($part))) throw new RuntimeException('安装路径不能是符号链接');
        }
    }
    private function files(): array
    {
        $directory=$this->path('install');$lock=$directory.'/install.lock';
        clearstatcache(true,$directory);clearstatcache(true,$lock);
        if(is_link($directory)||(file_exists($directory)&&!is_dir($directory))||is_link($lock)||(file_exists($lock)&&!is_file($lock)))throw new RuntimeException('安装目录或 install.lock 类型无效');
        return ['directory'=>is_dir($directory),'locked'=>is_file($lock)];
    }
    public function status(bool $verifyDatabase=false): array
    {
        if (\app\cms\support\EmbeddedResources::binary()) return ['installed'=>true,'reason'=>'binary_deployment'];
        try {
            $files=$this->files();
            if(!$files['directory'])return ['installed'=>true,'reason'=>'installation_directory_absent'];
            if($files['locked'])return ['installed'=>true,'reason'=>'installation_file_locked'];
            $journal=$this->journal();
            return ['installed'=>false,'reason'=>$journal&&($journal['phase']??'')!=='complete'?'resume_available':'installation_unlocked','resume_version'=>$journal&&($journal['phase']??'')!=='complete'?(int)$journal['version']:null];
        } catch(\Throwable $error) { return ['installed'=>true,'reason'=>'invalid_configuration','blocked'=>true]; }
    }
    private function assertOpen(): void
    {
        if(\app\cms\support\EmbeddedResources::binary())throw new RuntimeException('二进制安装入口已关闭');
        $files=$this->files();
        if(!$files['directory']||$files['locked'])throw new RuntimeException('安装入口已关闭；重装请恢复标准 install 目录并删除 install/install.lock');
    }
    public function environment(): array
    {
        $checks=['php'=>version_compare(PHP_VERSION,'8.2','>='),'pdo_mysql'=>extension_loaded('pdo_mysql'),'pcntl'=>extension_loaded('pcntl'),'posix'=>extension_loaded('posix'),'mbstring'=>extension_loaded('mbstring'),'gd'=>extension_loaded('gd'),'config_writable'=>is_writable($this->path('config')),'root_writable'=>is_writable($this->root),'public_writable'=>is_writable($this->path('public')),'baseline_present'=>is_file($this->path('install/database/cms-empty.sql'))&&is_file($this->path('install/database/cms-internal.sql'))&&is_file($this->path('install/database/webman-admin.sql'))];
        $checks += $this->databaseConfigChecks();
        try{$checks['assets_ready']=(new \app\cms\maintenance\AssetMaintenance($this->root === \app\cms\Paths::root() ? null : $this->root))->inspect(true)['ok'];}catch(\Throwable $error){$checks['assets_ready']=false;}
        return ['php_version'=>PHP_VERSION,'checks'=>$checks,'ready'=>!in_array(false,$checks,true)];
    }
    public function inspect(array $input): array
    {
        if($legacy=$this->legacyResumeService($input))return $legacy->inspect($input);
        $this->assertPaths();$this->assertOpen();$in=$this->normalize($input,false,false);$this->assertConfigOverrides($in);$pdo=$this->connect($in,false);$exists=$this->databaseExists($pdo,$in['database']);
        $tables=[];if($exists){$pdo->exec('USE `'.$in['database'].'`');$tables=$this->tableNames($pdo);}
        return ['database_exists'=>$exists,'existing_tables'=>count($tables),'db_prefix'=>$in['db_prefix'],'template_mode'=>$in['template_mode'],'import_demo'=>$in['import_demo'],'demo'=>$in['import_demo'],'data_strategy'=>$in['data_strategy'],'theme'=>$in['theme'],'environment'=>$this->environment()];
    }
    public function preflight(array $input): array
    {
        if($legacy=$this->legacyResumeService($input))return $legacy->preflight($input);
        $this->assertPaths();$this->assertOpen();$in=$this->normalize($input,false);$this->assertConfigOverrides($in);$catalog=$this->catalog($in);$identity=$this->identity($in,$catalog);$journal=$this->journal();
        if($journal&&($journal['phase']??'')!=='complete'){
            $this->assertIdentity($journal,$identity);
            return $this->progress($journal)+['plan_token'=>$journal['plan_token'],'conflicts'=>array_keys($journal['deletions']),'target_tables'=>array_column($catalog['tables'],'name'),'source_prefix'=>$catalog['source_prefix'],'template_mode'=>$in['template_mode'],'import_demo'=>$in['import_demo'],'demo'=>$in['import_demo'],'data_strategy'=>$in['data_strategy'],'theme'=>$in['theme'],'resume'=>true];
        }
        $pdo=$this->connect($in,false);$exists=$this->databaseExists($pdo,$in['database']);$conflicts=[];
        if($exists){$pdo->exec('USE `'.$in['database'].'`');$conflicts=$this->conflicts($pdo,array_column($catalog['tables'],'name'));$this->assertForeignKeys($pdo,array_keys($conflicts));}
        $plan=['version'=>self::FORMAT,'token'=>bin2hex(random_bytes(32)),'identity'=>$identity,'database_exists'=>$exists,'conflicts'=>$conflicts,'created_at'=>time(),'target_tables'=>array_column($catalog['tables'],'name')];
        $this->writePrivate(self::PLAN,$plan);
        return ['plan_token'=>$plan['token'],'database_exists'=>$exists,'conflicts'=>array_keys($conflicts),'conflict_count'=>count($conflicts),'target_tables'=>$plan['target_tables'],'source_prefix'=>$catalog['source_prefix'],'template_mode'=>$in['template_mode'],'convert_innodb'=>$in['convert_innodb'],'import_demo'=>$in['import_demo'],'demo'=>$in['import_demo'],'data_strategy'=>$in['data_strategy'],'theme'=>$in['theme'],'resume'=>false];
    }
    /** CLI completion uses exactly the same checkpointed operations as the HTTP steps. */
    public function install(array $input): array
    {
        do{$result=$this->step($input,15);}while(!empty($result['pending']));return $result;
    }
    public function step(array $input,float $budget=3,?string $browserOwner=null): array
    {
        if($legacy=$this->legacyResumeService($input))return $legacy->step($input,$budget);
        $this->assertPaths();$this->assertOpen();$in=$this->normalize($input,true);$this->assertConfigOverrides($in);
        $environment=$this->environment();
        if(!$environment['ready']) {
            $previous=$this->journal();
            $checks=$environment['checks'];
            if($previous && ($previous['phase']??'')!=='complete' && !isset($previous['config_storage'])) {
                unset($checks['database_config_readable'],$checks['database_config_writable'],$checks['database_config_managed']);
            }
            if(in_array(false,$checks,true))throw new RuntimeException('安装环境检查未通过');
        }
        $catalog=$this->catalog($in);$identity=$this->identity($in,$catalog);$deadline=microtime(true)+max(.1,min($budget,30));
        $file=$this->path('config/cms-installing.lock');$handle=fopen($file,'c+');
        if(!$handle||!chmod($file,0600)||!flock($handle,LOCK_EX|LOCK_NB))throw new RuntimeException('安装正在执行，请稍后重试');
        try{
            $this->assertOpen();$this->assertConfigOverrides($in);$pdo=$this->connect($in,false);$lockName='cms-install-'.substr(hash('sha256',$in['database']),0,40);$lock=$pdo->prepare('SELECT GET_LOCK(?,0)');$lock->execute([$lockName]);
            if((int)$lock->fetchColumn()!==1)throw new RuntimeException('所选数据库正在执行其他安装');
            try{
                $journal=$this->journal();
                if(!$journal||($journal['phase']??'')==='complete')$journal=$this->begin($pdo,$in,$catalog,$identity,$input,$browserOwner);
                else $this->assertIdentity($journal,$identity);
                $pdo->exec('USE `'.$in['database'].'`');
                if($journal['phase']==='loading'){
                    foreach($catalog['tables'] as $table){
                        if(($journal['tables'][$table['name']]['phase']??null)==='ready')continue;
                        if(!$this->loadTable($pdo,$table,$journal,$deadline,false))return $this->progress($journal);
                        if(microtime(true)>=$deadline)return $this->progress($journal);
                    }
                    foreach($catalog['tables'] as $table){$state=$journal['tables'][$table['name']];if(!$this->tableExists($pdo,$state['stage'])||!$this->verifyCheckpoint($pdo,$table,$journal,$state['stage']))throw new RuntimeException('已校验暂存表在发布边界发生变化：'.$table['name']);}
                    // All CREATE/INSERT statements have succeeded against owned tables.
                    // Recheck the complete conflict set before the first original table is removed.
                    $actual=$this->conflicts($pdo,array_column($catalog['tables'],'name'));
                    $expected=[];foreach($journal['deletions'] as $name=>$item)$expected[$name]=$item['metadata'];
                    if($actual!==$expected)throw new RuntimeException('暂存导入期间目标表发生变化，停止替换，请核对原表及任务记录');
                    $this->assertForeignKeys($pdo,array_keys($actual));
                    $journal['phase']='dropping';$this->saveJournal($journal);
                }
                if($journal['phase']==='dropping'){
                    foreach($journal['deletions'] as $table=>$item){
                        if($item['state']==='deleted')continue;
                        $present=$this->tableExists($pdo,$table);
                        if($present){if($this->metadata($pdo,$table)!==$item['metadata'])throw new RuntimeException('冲突表在预检后发生变化，请停止并核对：'.$table);$pdo->exec('DROP TABLE `'.$table.'`');}
                        $journal['deletions'][$table]['state']='deleted';$this->saveJournal($journal);
                        if(microtime(true)>=$deadline)return $this->progress($journal);
                    }
                    $journal['phase']='publishing';$this->saveJournal($journal);
                }
                if($journal['phase']==='publishing'){
                    foreach($catalog['tables'] as $table){
                        if(($journal['tables'][$table['name']]['phase']??null)==='complete')continue;
                        if(!$this->loadTable($pdo,$table,$journal,$deadline,true))return $this->progress($journal);
                        if(microtime(true)>=$deadline)return $this->progress($journal);
                    }
                    foreach($catalog['tables'] as $table){$state=$journal['tables'][$table['name']];if(!$this->tableExists($pdo,$table['name'])||!$this->verifyCheckpoint($pdo,$table,$journal,$table['name']))throw new RuntimeException('已发布表在收尾边界发生变化：'.$table['name']);}
                    $journal['phase']='finalizing';$this->saveJournal($journal);
                }
                if($journal['phase']==='finalizing')$this->finalizeDatabase($pdo,$in,$journal);
                return $this->finish($in,$journal);
            } finally{$pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lockName]);}
        }finally{flock($handle,LOCK_UN);fclose($handle);}
    }
    private function begin(PDO $pdo,array $in,array $catalog,string $identity,array $raw,?string $browserOwner=null): array
    {
        $plan=$this->readPrivate(self::PLAN);
        if(!$plan||($plan['version']??null)!==self::FORMAT||!is_string($raw['plan_token']??null)||!hash_equals((string)$plan['token'],$raw['plan_token'])||!hash_equals((string)$plan['identity'],$identity)||time()-(int)$plan['created_at']>3600)$this->fail('plan_token','请先完成安装预检，再提交当前删除清单');
        $exists=$this->databaseExists($pdo,$in['database']);$actual=[];
        if($exists){$pdo->exec('USE `'.$in['database'].'`');$actual=$this->conflicts($pdo,array_column($catalog['tables'],'name'));$this->assertForeignKeys($pdo,array_keys($actual));}
        if($exists!==$plan['database_exists']||$actual!==$plan['conflicts'])$this->fail('database','数据库或冲突表已变化，请重新预检');
        $this->assertConfigOverrides($in);
        $this->retireHistory();
        if(!$exists)$pdo->exec('CREATE DATABASE `'.$in['database'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
        $pdo->exec('USE `'.$in['database'].'`');
        $deletions=[];foreach($actual as $name=>$metadata)$deletions[$name]=['state'=>'planned','metadata'=>$metadata];
        $journal=['version'=>self::FORMAT,'config_storage'=>'yaml-database-v1','id'=>bin2hex(random_bytes(12)),'identity'=>$identity,'plan_token'=>$plan['token'],'phase'=>'loading','deletions'=>$deletions,'tables'=>[],'total'=>count($catalog['tables']),'source_sha256'=>$catalog['source_sha256'],'template_mode'=>$in['template_mode'],'db_prefix'=>$in['db_prefix'],'site_signature'=>bin2hex(random_bytes(24)),'created_at'=>time()];
        $journal['import_demo']=$in['import_demo'];$journal['data_strategy']=$in['data_strategy'];$journal['theme']=$in['theme'];$journal['theme_signature']=$in['theme_signature'];$journal['demo_version']=$in['data_strategy']==='builtin_demo'?DemoSeeder::VERSION:null;
        if($browserOwner!==null&&$browserOwner!=='')$journal['browser_owner_hash']=hash('sha256',$browserOwner);
        $this->saveJournal($journal);return $journal;
    }
    /** A chunk commits its staging contents before recording its fingerprint. A crash gap rebuilds only that owned stage. */
    private function loadTable(PDO $pdo,array $table,array &$journal,float $deadline,bool $publish): bool
    {
        $name=$table['name'];$state=$journal['tables'][$name]??null;
        if($state&&$state['phase']==='complete'){
            if(!$this->tableExists($pdo,$name)||!$this->verifyCheckpoint($pdo,$table,$journal,$name))throw new RuntimeException('已完成表被外部修改或移除，停止续装：'.$name);return true;
        }
        if(!$state){
            if($publish&&$this->tableExists($pdo,$name))throw new RuntimeException('发现未经本次任务发布的目标表：'.$name);
            $state=['phase'=>'creating','stage'=>'__cmsi_'.substr($journal['id'],0,12).'_'.substr(hash('sha256',$name),0,16),'position'=>0];
            if($this->tableExists($pdo,$state['stage']))throw new RuntimeException('暂存表名称冲突，停止安装');
            $journal['tables'][$name]=$state;$this->saveJournal($journal);
        }
        $stage=$state['stage'];
        if($state['phase']==='ready'){
            if(!$this->tableExists($pdo,$stage)||!$this->verifyCheckpoint($pdo,$table,$journal,$stage))throw new RuntimeException('已校验暂存表被修改或移除，停止续装：'.$name);
            if(!$publish)return true;
            if($this->tableExists($pdo,$name))throw new RuntimeException('正式表仍存在，拒绝覆盖：'.$name);
            $state['phase']='publishing';$journal['tables'][$name]=$state;$this->saveJournal($journal);
        }
        if($state['phase']==='publishing'){
            $stageExists=$this->tableExists($pdo,$stage);$finalExists=$this->tableExists($pdo,$name);
            if($stageExists&&$finalExists)throw new RuntimeException('暂存表与正式表同时存在，停止发布：'.$name);
            if($stageExists){if(!$this->verifyCheckpoint($pdo,$table,$journal,$stage))throw new RuntimeException('待发布暂存表已变化');$pdo->exec('RENAME TABLE `'.$stage.'` TO `'.$name.'`');}
            elseif(!$finalExists)throw new RuntimeException('发布中断后找不到本次表：'.$name);
            if(!$this->verifyCheckpoint($pdo,$table,$journal,$name))throw new RuntimeException('发布表与校验记录不符：'.$name);
            $state['phase']='complete';$journal['tables'][$name]=$state;$this->saveJournal($journal);return true;
        }
        $stageTable=SqlImporter::renameTable($table,$stage);
        if($this->tableExists($pdo,$stage)){
            $valid=$state['phase']==='loading'&&isset($state['fingerprint'])&&$this->verifyCheckpoint($pdo,$table,$journal,$stage);
            if(!$valid){$pdo->exec('DROP TABLE `'.$stage.'`');$state['position']=0;unset($state['fingerprint']);$state['phase']='creating';$journal['tables'][$name]=$state;$this->saveJournal($journal);}
        }elseif($state['phase']==='loading'){throw new RuntimeException('本次暂存表丢失，停止续装：'.$name);}
        if(!$this->tableExists($pdo,$stage))$pdo->exec($stageTable['create']);
        $transactional=str_contains(strtolower($this->metadata($pdo,$stage)['engine']),'innodb');
        if($transactional)$pdo->beginTransaction();
        try{
            $position=(int)$state['position'];$count=count($stageTable['inserts']);
            while($position<$count){$pdo->exec($stageTable['inserts'][$position]);++$position;if(microtime(true)>=$deadline)break;}
            if($transactional)$pdo->commit();
        }catch(\Throwable $error){if($pdo->inTransaction())$pdo->rollBack();throw new RuntimeException('表 '.$name.' 导入失败，请使用相同文件及设置重试（SQLSTATE '.$error->getCode().'）');}
        $state['phase']='loading';$state['position']=$position;$state['fingerprint']=$this->fingerprint($pdo,$stage);$journal['tables'][$name]=$state;$this->saveJournal($journal);
        if($position<count($stageTable['inserts']))return false;
        $state['phase']='ready';$journal['tables'][$name]=$state;$this->saveJournal($journal);
        return $this->loadTable($pdo,$table,$journal,$deadline,$publish);
    }
    private function finalizeDatabase(PDO $pdo,array $in,array &$journal): void
    {
        Migrations::apply($pdo,$in['admin_prefix'],false,$in['cms_prefix']);Schema::install($pdo,$in['cms_prefix']);
        (new LegacyMarkerStore($pdo,$in['cms_prefix']))->importDirectory($this->path('app/cms/resources/legacy/data/conf'),true);
        $this->configureSite($pdo,$in);$this->menus($pdo,$in['admin_prefix']);
        $pdo->beginTransaction();
        try{
            $id=AdminSeeder::initialize($pdo,$in['admin_prefix'],$in['admin_username'],$in['admin_password']);
            $options=$in['admin_prefix'].'options';$q=$pdo->prepare('INSERT INTO `'.$options.'` (name,value,created_at,updated_at) VALUES (?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE value=VALUES(value),updated_at=NOW()');$q->execute(['cms_template_mode',$in['template_mode']]);$q->execute(['cms_site_signature',$journal['site_signature']]);$enabled=(int)$pdo->query('SELECT COUNT(*) FROM `'.$in['cms_prefix'].'language` WHERE status=1')->fetchColumn()>1;$q->execute(['cms_multilingual_enabled',$enabled?'1':'0']);
            \app\cms\service\HtmltextProvenanceService::markInstalled($pdo,$in['cms_prefix'],$in['data_strategy']);
            $pdo->commit();
        }catch(\Throwable $error){if($pdo->inTransaction())$pdo->rollBack();throw $error;}
        if(!(new LegacyInstallationInspector())->inspect($pdo,$in['cms_prefix'])['recognized'])throw new RuntimeException('导入后的核心结构或必要基础数据不完整，安装未完成');
        $journal['admin_id']=$id;
        $journal['phase']='database_complete';$this->saveJournal($journal);
    }
    private function finish(array $in,array &$journal): array
    {
        // Older FORMAT3 database_complete records predate the handoff ID.
        // Recover it by identity, never by choosing the first administrator.
        if (empty($journal['admin_id'])) {
            $pdo=$this->connect($in);
            $q=$pdo->prepare('SELECT value FROM `'.$in['admin_prefix'].'options` WHERE name=?');
            $q->execute(['cms_site_signature']);
            if (!hash_equals((string)$journal['site_signature'],(string)$q->fetchColumn())) throw new RuntimeException('已完成数据库与本次安装不匹配');
            $q->execute(['cms_template_mode']);
            if ($q->fetchColumn()!==$in['template_mode']) throw new RuntimeException('已完成数据库模式与本次安装不匹配');
            $id=AdminSeeder::administrator($pdo,$in['admin_prefix'],$in['admin_username'],$in['admin_password']);
            if(!$id)throw new RuntimeException('安装管理员不存在，不能完成安装');
            $journal['admin_id']=$id;$this->saveJournal($journal);
        }
        $result=['installed'=>true,'pending'=>false,'done'=>true,'task_id'=>$journal['id'],'tables'=>$journal['total'],'template_mode'=>$in['template_mode'],'db_prefix'=>$in['db_prefix'],'import_demo'=>$in['import_demo'],'demo'=>$in['import_demo'],'data_strategy'=>$in['data_strategy'],'theme'=>$in['theme'],'login_url'=>'/web-admin','restart_required'=>true];
        $ready=false;
        try{
            if (in_array($journal['config_storage'] ?? null,['php-database-v1','yaml-database-v1'],true)) {
                $this->checkDatabaseOverrides($in);
                DatabaseSettings::write($this->databaseValues($in), $this->root);
                (new InstallerEnvironment($this->root))->synchronize($this->databaseValues($in));
                DatabaseSettings::assertOverrides($this->databaseValues($in), $this->root);
            } else $this->writeEnvironment($in);
            $this->writeInstalled($in,'installer');
            // Until the lock exists the same form can retry database_complete; do
            // not erase its SQL or mark the task complete before that boundary.
            $this->writeInstallLock();$ready=true;$this->cleanUploads();$result+=(new InstallerArchive($this->root))->seal();
            $journal['phase']='complete';$this->saveJournal($journal);
            if(is_file($this->path(self::PLAN)))unlink($this->path(self::PLAN));
        }catch(\Throwable $error){$result=array_replace($result,['archive_pending'=>true,'archive_status'=>'pending','archive_message'=>'数据库已完成，配置或安装资料封存待处理；请勿重复导入，使用相同设置续装；环境配置已保存后也可使用 cms:install --seal 完成收尾']);}
        $result['finish_status']=$ready?'ready':'pending';
        if($ready){
            try{$result['handoff']=(new InstallerHandoff($this->root))->prepare($journal,!empty($result['archive_pending']));}
            catch(\Throwable $error){$result['handoff']=['restart_status'=>'failed','login_available'=>false,'message'=>'安装已完成，自动重启安排失败，请检查私有安装记录和进程权限','archive_pending'=>!empty($result['archive_pending'])];}
        }else $result['handoff']=['restart_status'=>'finishing_required','login_available'=>false,'message'=>'数据库已完成，配置或安装锁尚未保存；请使用相同设置补做收尾，不要重复导入','archive_pending'=>true];
        return $result;
    }
    private function catalog(array &$in): array
    {
        $baseline=SqlImporter::tables($this->path('install/database/cms-empty.sql'),'cms_',$in['cms_prefix']);$sourcePrefix=null;$sourceSha=null;
        if($in['import_sql']){
            $file=$in['sql_file'];$real=realpath($file);$install=realpath($this->path('install'));
            if(!$real||!$install||!str_starts_with($real,$install.'/')||!is_file($real)||is_link($file)||filesize($real)>\app\cms\config\DeploymentConfig::get('limits.install_sql_bytes'))$this->fail('sql_file','上传 SQL 文件无效或超出私有安装目录');
            $sourceSha=hash_file('sha256',$real);if($in['sql_sha256']!==''&&!hash_equals($sourceSha,$in['sql_sha256']))$this->fail('sql_file','上传 SQL 文件校验失败，请重新上传');
            try{
                $sourcePrefix=SqlImporter::detectPrefix($real);$tables=SqlImporter::tables($real,$sourcePrefix,$in['cms_prefix'],$in['convert_innodb'],true);
                $names=[];foreach($tables as $table){$suffix=substr($table['name'],strlen($in['cms_prefix']));if(str_starts_with($suffix,'backend_')||str_starts_with($suffix,'cms_state_')||$suffix==='cms_install_state')throw new RuntimeException('SQL 含本系统保留表：'.$suffix);$names[$suffix]=$table;}
                $required=['config'=>['id','name','value','inc_type','lang','is_del'],'arctype'=>['id','channeltype','current_channel','parent_id','typename','dirname','templist','tempview','lang','status','is_del'],'archives'=>['aid','typeid','channel','title','tempview','lang','status','is_del'],'channeltype'=>['id','nid','title','table','ctl_name','ifsystem','status','is_del'],'channelfield'=>['id','name','channel_id'],'language'=>['id','title','mark','is_home_default','is_admin_default','status']];
                foreach($required as $suffix=>$columns){if(!isset($names[$suffix]))throw new RuntimeException('缺少必需核心表：'.$sourcePrefix.$suffix);foreach($columns as $column)if(!preg_match('/`'.preg_quote($column,'/').'`\s+[A-Za-z]/',$names[$suffix]['create']))throw new RuntimeException('核心表缺少字段：'.$suffix.'.'.$column);}
                if($in['import_demo'])$this->assertSourceSeeds($names);
                else foreach($names as $sourceTable)foreach(SqlImporter::rows($sourceTable) as $unused){}
                if(!hash_equals($sourceSha,(string)hash_file('sha256',$real)))throw new RuntimeException('SQL 文件在预检时发生变化，请重新上传');
                foreach($baseline as $table){$suffix=substr($table['name'],strlen($in['cms_prefix']));if(!isset($names[$suffix])){$table['inserts']=[];$tables[]=$table;}}
                if(!$in['import_demo'])$tables=StructureSeeder::apply($tables,$baseline);
            }catch(\RuntimeException $error){$this->fail('sql_file',$error->getMessage());}
        }else{$tables=$in['import_demo']?DemoSeeder::apply($baseline,$in['cms_prefix']):$baseline;}
        if($in['template_mode']==='eyou'){$selected=(new InstallerThemes($this->root))->resolve($in['theme']);$in['theme']=$selected['theme'];$in['theme_signature']=$selected['signature'];}
        else{$in['theme']='webman-default';$in['theme_signature']=null;}
        $backend=SqlImporter::tables($this->path('install/database/webman-admin.sql'),'wa_',$in['admin_prefix']);
        $internal=SqlImporter::tables($this->path('install/database/cms-internal.sql'),'cms_',$in['cms_prefix']);
        $tables=array_merge($tables,$backend,$internal);$hash=hash_init('sha256');$seen=[];
        foreach($tables as $table){if(isset($seen[strtolower($table['name'])]))$this->fail('db_prefix','目标表名重名：'.$table['name']);$seen[strtolower($table['name'])]=true;hash_update($hash,$table['create']);foreach($table['inserts'] as $sql)hash_update($hash,$sql);}
        return ['tables'=>$tables,'source_prefix'=>$sourcePrefix,'source_sha256'=>$sourceSha,'sha256'=>hash_final($hash)];
    }
    private function normalize(array $in,bool $administrator,bool $requireSql=true): array
    {
        $port=array_key_exists('port',$in)?$in['port']:3306;
        $validPort=(is_int($port)||(is_string($port)&&preg_match('/^(?:0|[1-9][0-9]*)$/D',$port))) && (int)$port>=1 && (int)$port<=65535;
        $data=['host'=>trim((string)($in['host']??'127.0.0.1')),'port'=>$validPort?(int)$port:0,'database'=>trim((string)($in['database']??'')),'user'=>trim((string)($in['user']??'')),'password'=>(string)($in['password']??''),'db_prefix'=>(string)($in['db_prefix']??$in['cms_prefix']??'wa_'),'compatibility'=>filter_var($in['compatibility']??false,FILTER_VALIDATE_BOOLEAN),'import_sql'=>filter_var($in['import_sql']??false,FILTER_VALIDATE_BOOLEAN),'convert_innodb'=>filter_var($in['convert_innodb']??true,FILTER_VALIDATE_BOOLEAN),'sql_file'=>(string)($in['sql_file']??''),'sql_sha256'=>(string)($in['sql_sha256']??'')];
        $errors=[];
        $data['import_demo']=filter_var($in['import_demo']??false,FILTER_VALIDATE_BOOLEAN);
        $data['theme']=is_string($in['theme']??null)?trim($in['theme']):'';
        $data['theme_signature']=null;
        $data['data_strategy']=$data['compatibility']?($data['import_demo']?'source_full':'source_schema'):($data['import_demo']?'builtin_demo':'empty');
        if($requireSql&&$data['compatibility']&&!$data['import_sql'])$errors['sql_file']='兼容 EyouCMS 模式必须上传完整 SQL 文件';
        if(!preg_match('/^[A-Za-z0-9_.:-]{1,253}$/D',$data['host']))$errors['host']='数据库主机格式无效';if(!$validPort)$errors['port']='数据库端口应在 1 至 65535 之间';
        if($data['user']===''||strlen($data['user'])>128)$errors['user']='请填写有效数据库用户名';
        if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/D',$data['database']))$errors['database']='数据库名只能包含字母、数字和下划线，并以字母开头';
        if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,31}$/D',$data['db_prefix']))$errors['db_prefix']='表前缀需以字母开头，长度不超过 32 字符';
        if(!$data['compatibility']&&($data['import_sql']||$data['sql_file']!==''||!empty($in['upload_token'])))$errors['compatibility']='只有兼容 EyouCMS 模式可以上传或导入原站 SQL';
        if($requireSql&&$data['import_sql']&&$data['sql_file']==='')$errors['sql_file']='请选择并上传完整 EyouCMS SQL';
        if($data['sql_sha256']!==''&&!preg_match('/^[a-f0-9]{64}$/D',$data['sql_sha256']))$errors['sql_file']='SQL 校验值无效';
        foreach(['host','database','user','password','db_prefix','sql_file','theme'] as $key)if(preg_match('/[\x00\r\n]/',$data[$key]))$errors[$key]='输入不能包含换行或空字节';
        $data['cms_prefix']=$data['db_prefix'];$data['admin_prefix']=$data['db_prefix'].'backend_';$data['template_mode']=$data['compatibility']?'eyou':'webman';
        if($administrator){
            $password=$in['admin_password']??null;$confirmation=$in['admin_password_confirm']??null;
            if(!is_string($password)||strlen($password)<8||strlen($password)>72)$errors['admin_password']='管理员密码需为 8 至 72 字节';
            if(!is_string($confirmation)||$confirmation==='')$errors['admin_password_confirm']='请再次输入管理员密码';elseif(is_string($password)&&!hash_equals($password,$confirmation))$errors['admin_password_confirm']='两次输入的管理员密码不一致';
            $data+=['admin_username'=>trim((string)($in['admin_username']??'')),'admin_password'=>is_string($password)?$password:'','site_name'=>trim((string)($in['site_name']??'我的网站')),'base_url'=>rtrim((string)($in['base_url']??''),'/')];
            if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{2,31}$/D',$data['admin_username']))$errors['admin_username']='管理员账号需为 3 至 32 位字母、数字或下划线';
            if(strcasecmp($data['admin_username'],'king')===0)$errors['admin_username']='king 为默认内容管理账号，请为超级管理员使用其他账号';
            if($data['data_strategy']!=='source_full'){if($data['site_name']===''||mb_strlen($data['site_name'])>100)$errors['site_name']='网站名称需为 1 至 100 字';if(!filter_var($data['base_url'],FILTER_VALIDATE_URL)||!in_array(parse_url($data['base_url'],PHP_URL_SCHEME),['http','https'],true)||parse_url($data['base_url'],PHP_URL_USER)!==null||parse_url($data['base_url'],PHP_URL_QUERY)!==null)$errors['base_url']='网站网址需为有效 HTTP 或 HTTPS 根网址';}
        }
        if($errors)throw new InstallerValidationException($errors);
        return $data;
    }
    /** Required semantic seed markers are checked before any database mutation. */
    private function assertSourceSeeds(array $tables): void
    {
        $config=[];$article=false;$language=false;
        foreach(SqlImporter::rows($tables['config']) as $row)if(in_array($row['name']??null,['web_name','web_basehost'],true))$config[$row['name']]=true;
        foreach(SqlImporter::rows($tables['channeltype']) as $row)if(($row['nid']??null)==='article'&&($row['table']??null)==='article'&&($row['ctl_name']??null)==='Article')$article=true;
        foreach(SqlImporter::rows($tables['language']) as $row)if(is_string($row['mark']??null)&&$row['mark']!=='')$language=true;
        if(count($config)!==2)throw new RuntimeException('原站 SQL 缺少 web_name 或 web_basehost 基础配置');
        if(!$article)throw new RuntimeException('原站 SQL 缺少文章模型基础数据');
        if(!$language)throw new RuntimeException('原站 SQL 缺少有效语言基础数据');
        // Also reject malformed column lists/row widths outside the seed tables.
        foreach($tables as $table)foreach(SqlImporter::rows($table) as $unused){}
    }
    public function connect(array $input,bool $database=true): PDO
    {
        try{$pdo=new PDO('mysql:host='.$input['host'].';port='.$input['port'].($database?';dbname='.$input['database']:'').';charset=utf8mb4',$input['user'],$input['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>5]);$pdo->exec("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION'");return $pdo;}
        catch(\PDOException $error){$code=(int)($error->errorInfo[1]??$error->getCode());if(in_array($code,[1045,1698],true))throw new InstallerValidationException(['user'=>'账号、密码或数据库访问授权不正确','password'=>'账号、密码或数据库访问授权不正确']);if(in_array($code,[2002,2003,2005],true))throw new InstallerValidationException(['host'=>'无法连接数据库，请检查主机和端口','port'=>'无法连接数据库，请检查主机和端口']);throw new InstallerValidationException(['database'=>'数据库连接失败，请检查名称、权限及服务状态']);}
    }
    private function configureSite(PDO $pdo,array $in): void
    {
        $table=$in['cms_prefix'].'config';$defaults=['web_name'=>$in['site_name'],'web_title'=>$in['site_name'],'web_basehost'=>$in['base_url'],'web_tpl_theme'=>$in['theme'],'seo_pseudo'=>'3','seo_html_arcdir'=>'/html','seo_html_showdir'=>'0','seo_native_error'=>'0','web_status'=>'0'];
        if($in['data_strategy']!=='source_full')$defaults+=['web_logo'=>'','web_mobile_logo'=>'','web_keywords'=>'','web_description'=>'','web_copyright'=>'','web_recordnum'=>'','web_garecordnum'=>'','web_thirdcode_pc'=>'','web_thirdcode_wap'=>''];
        foreach($defaults as $name=>$value){$q=$pdo->prepare('SELECT id FROM `'.$table.'` WHERE name=? AND lang=?');$q->execute([$name,'cn']);$id=$q->fetchColumn();if($id){if($in['data_strategy']!=='source_full')$pdo->prepare('UPDATE `'.$table.'` SET value=?,is_del=0 WHERE id=?')->execute([$value,$id]);}else{$pdo->prepare('INSERT INTO `'.$table.'` (name,value,lang,inc_type,is_del) VALUES (?,?,?,?,0)')->execute([$name,$value,'cn',explode('_',$name)[0]]);}}
        // The explicit installation selection supersedes every imported language's old theme path.
        $languages=$pdo->query('SELECT mark FROM `'.$in['cms_prefix'].'language`')->fetchAll(PDO::FETCH_COLUMN);
        foreach(array_unique(array_merge(['cn'],$languages)) as $lang){
            $q=$pdo->prepare('SELECT COUNT(*) FROM `'.$table.'` WHERE name=? AND lang=?');$q->execute(['web_tpl_theme',$lang]);
            if((int)$q->fetchColumn()>0)$pdo->prepare('UPDATE `'.$table.'` SET value=?,is_del=0 WHERE name=? AND lang=?')->execute([$in['theme'],'web_tpl_theme',$lang]);
            else $pdo->prepare('INSERT INTO `'.$table.'` (name,value,lang,inc_type,is_del) VALUES (?,?,?,?,0)')->execute(['web_tpl_theme',$in['theme'],$lang,'web']);
        }
        $config=json_decode((string)file_get_contents($this->path('app/cms/resources/defaults/pear.config.json')),true,512,JSON_THROW_ON_ERROR);
        $pdo->prepare('UPDATE `'.$in['admin_prefix'].'options` SET value=? WHERE name=?')->execute([json_encode($config,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),'system_config']);
        foreach(['users','roles','rules'] as $suffix)$pdo->prepare('UPDATE `'.$in['admin_prefix'].'options` SET name=? WHERE name=?')->execute(['table_form_schema_'.$in['admin_prefix'].$suffix,'table_form_schema_wa_'.$suffix]);
    }
    private function menus(PDO $pdo,string $prefix): void
    {
        $walk=function(array $items,int $parent=0)use(&$walk,$pdo,$prefix){foreach($items as $item){$children=$item['children']??[];unset($item['children']);$q=$pdo->prepare('SELECT id FROM `'.$prefix.'rules` WHERE `key`=?');$q->execute([$item['key']]);$id=$q->fetchColumn();if(!$id){$item=array_intersect_key($item,array_flip(['title','key','icon','href','weight','type','status']));$item+=['pid'=>$parent,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];$columns=array_keys($item);$pdo->prepare('INSERT INTO `'.$prefix.'rules` (`'.implode('`,`',$columns).'`) VALUES ('.implode(',',array_fill(0,count($columns),'?')).')')->execute(array_values($item));$id=$pdo->lastInsertId();}$walk($children,(int)$id);}};
        $walk(require $this->path('app/cms/admin/config/menu.php'));
    }
    private function identity(array $in,array $catalog): string
    {
        $parts=[$in['host'],$in['port'],$in['database'],$in['user'],$in['db_prefix'],$in['template_mode'],$in['import_sql'],$in['convert_innodb'],$catalog['source_sha256'],$catalog['source_prefix'],$catalog['sha256'],self::FORMAT];
        $parts[]=['data_strategy'=>$in['data_strategy'],'with_data'=>$in['import_demo'],'demo_version'=>$in['data_strategy']==='builtin_demo'?DemoSeeder::VERSION:null,'theme'=>$in['theme'],'theme_signature'=>$in['theme_signature']];
        return hash('sha256',json_encode($parts,JSON_THROW_ON_ERROR));
    }
    private function assertIdentity(array $journal,string $identity): void {if(($journal['version']??null)!==self::FORMAT||!hash_equals((string)($journal['identity']??''),$identity))throw new RuntimeException('已有未完成任务，请使用相同数据库、模式、演示选项、前缀、SQL 和引擎选项续装');}
    private function databaseExists(PDO $pdo,string $name): bool {$q=$pdo->prepare('SELECT 1 FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=?');$q->execute([$name]);return (bool)$q->fetchColumn();}
    private function tableNames(PDO $pdo): array {return $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);}
    private function tableExists(PDO $pdo,string $name): bool {$q=$pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$name]);return (bool)$q->fetchColumn();}
    private function conflicts(PDO $pdo,array $names): array {$result=[];foreach($names as $name)if($this->tableExists($pdo,$name))$result[$name]=$this->metadata($pdo,$name);ksort($result);return $result;}
    private function metadata(PDO $pdo,string $name): array
    {
        $q=$pdo->prepare('SELECT TABLE_TYPE,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$name]);$row=$q->fetch(PDO::FETCH_ASSOC);
        if(!$row||$row['TABLE_TYPE']!=='BASE TABLE')throw new RuntimeException('目标对象不是可安装的普通表：'.$name);
        $create=$pdo->query('SHOW CREATE TABLE `'.$name.'`')->fetch(PDO::FETCH_NUM)[1];
        return ['schema'=>hash('sha256',$create),'rows'=>(int)$pdo->query('SELECT COUNT(*) FROM `'.$name.'`')->fetchColumn(),'engine'=>$row['ENGINE'],'data'=>$this->fingerprint($pdo,$name)['data']];
    }
    private function fingerprint(PDO $pdo,string $name): array
    {
        $create=$pdo->query('SHOW CREATE TABLE `'.$name.'`')->fetch(PDO::FETCH_NUM)[1];$create=InstallerTableIdentity::normalize($create);
        $keys=$pdo->query('SHOW KEYS FROM `'.$name.'` WHERE Key_name=\'PRIMARY\'')->fetchAll(PDO::FETCH_ASSOC);usort($keys,static fn(array $a,array $b):int=>(int)$a['Seq_in_index']<=>(int)$b['Seq_in_index']);$order=array_column($keys,'Column_name');
        $orderSql=$order?'`'.implode('`,`',$order).'`':implode(',',array_map(static fn(string $column):string=>'BINARY `'.str_replace('`','``',$column).'`',array_column($pdo->query('SHOW COLUMNS FROM `'.$name.'`')->fetchAll(PDO::FETCH_ASSOC),'Field')));
        $query=$pdo->query('SELECT * FROM `'.$name.'` ORDER BY '.$orderSql);$hash=hash_init('sha256');$count=0;while($row=$query->fetch(PDO::FETCH_ASSOC)){hash_update($hash,serialize($row));++$count;}$query->closeCursor();
        return ['schema'=>hash('sha256',$create),'rows'=>$count,'data'=>hash_final($hash),'auto_increment'=>InstallerTableIdentity::counter($create)];
    }
    /** Called only under both installation locks; conflict originals always use strict metadata(). */
    private function verifyCheckpoint(PDO $pdo, array $table, array &$journal, string $name): bool
    {
        $state = $journal['tables'][$table['name']] ?? [];
        $expected = $state['fingerprint'] ?? [];
        $actual = $this->fingerprint($pdo, $name);
        if (($actual['rows'] ?? null) !== ($expected['rows'] ?? null)
            || ($actual['data'] ?? null) !== ($expected['data'] ?? null)) return false;
        $proof = hash('sha256', json_encode($expected, JSON_THROW_ON_ERROR));
        $repairs = $journal['counter_repairs'][$name] ?? [];
        $last = $repairs ? array_key_last($repairs) : null;
        if (($actual['schema'] ?? null) === ($expected['schema'] ?? null)) {
            if (array_key_exists('auto_increment', $expected) && $expected['auto_increment'] !== $actual['auto_increment']) return false;
            // An ALTER can have committed before its completion receipt was saved.
            if ($last !== null && $repairs[$last]['phase'] === 'planned') {
                if (($repairs[$last]['checkpoint'] ?? '') !== $proof
                    || ($repairs[$last]['source_create'] ?? '') !== hash('sha256', $table['create'])
                    || ($repairs[$last]['to'] ?? '') !== $actual['auto_increment']
                    || ($repairs[$last]['task'] ?? '') !== ($journal['id'] ?? null)
                    || ($repairs[$last]['table'] ?? '') !== $name
                    || ($repairs[$last]['expected_schema'] ?? '') !== $expected['schema']) return false;
                $journal['counter_repairs'][$name][$last]['phase'] = 'complete';
                $this->saveJournal($journal);
            }
            return true;
        }
        // Private task identity and rename topology are mandatory, including for already renamed tables.
        if (!preg_match('/^[a-f0-9]{24}$/D', (string)($journal['id'] ?? ''))
            || !in_array($journal['phase'] ?? '', ['loading','dropping','publishing'], true)) return false;
        $stage = '__cmsi_' . substr($journal['id'], 0, 12) . '_' . substr(hash('sha256', $table['name']), 0, 16);
        if (($state['stage'] ?? '') !== $stage) return false;
        if ($name === $stage) {
            if (!in_array($state['phase'] ?? '', ['loading','ready','publishing'], true)) return false;
            if ($state['phase'] === 'publishing' && $this->tableExists($pdo, $table['name'])) return false;
        } elseif ($name === $table['name']) {
            if ($journal['phase'] !== 'publishing' || !in_array($state['phase'] ?? '', ['publishing','complete'], true)
                || $this->tableExists($pdo, $stage)) return false;
            if (isset($journal['deletions'][$name]) && $journal['deletions'][$name]['state'] !== 'deleted') return false;
        } else return false;
        $wanted = array_key_exists('auto_increment', $expected)
            ? $expected['auto_increment'] : InstallerTableIdentity::sourceCounter($table['create']);
        $current = $actual['auto_increment'];
        if (InstallerTableIdentity::decimal($wanted) === null || $current === null
            || !InstallerTableIdentity::greater($wanted, $current)) return false;
        $create = InstallerTableIdentity::normalize($pdo->query('SHOW CREATE TABLE `' . $name . '`')->fetch(PDO::FETCH_NUM)[1]);
        if (!InstallerTableIdentity::isInnoDb($create) || hash('sha256', $create) !== $actual['schema']) return false;
        $restored = InstallerTableIdentity::withCounter($create, $wanted);
        if ($restored === null || hash('sha256', $restored) !== $expected['schema']) return false;
        $intent = ['phase'=>'planned', 'from'=>$current, 'to'=>$wanted, 'before_schema'=>$actual['schema'],
            'checkpoint'=>$proof, 'source_create'=>hash('sha256', $table['create']), 'expected_schema'=>$expected['schema'],
            'task'=>$journal['id'], 'table'=>$name, 'created_at'=>time()];
        if ($last !== null && $repairs[$last]['phase'] === 'planned') {
            foreach (['from','to','before_schema','checkpoint','source_create','expected_schema','task','table'] as $key)
                if (($repairs[$last][$key] ?? null) !== $intent[$key]) return false;
        } else {
            $journal['counter_repairs'][$name][] = $intent;
            $last = array_key_last($journal['counter_repairs'][$name]);
            $this->saveJournal($journal);
        }
        $pdo->exec('ALTER TABLE `' . $name . '` AUTO_INCREMENT=' . $wanted);
        $verified = $this->fingerprint($pdo, $name);
        foreach (['schema','rows','data'] as $key) if ($verified[$key] !== $expected[$key])
            throw new RuntimeException('安装表计数恢复后校验失败，停止续装：' . $table['name']);
        $journal['counter_repairs'][$name][$last]['phase'] = 'complete';
        $this->saveJournal($journal);
        return true;
    }

    private function assertForeignKeys(PDO $pdo,array $conflicts): void
    {
        if(!$conflicts)return;$database=(string)$pdo->query('SELECT DATABASE()')->fetchColumn();$q=$pdo->prepare('SELECT TABLE_SCHEMA,TABLE_NAME,REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA=? AND REFERENCED_TABLE_NAME IS NOT NULL');$q->execute([$database]);
        foreach($q->fetchAll(PDO::FETCH_ASSOC) as $row)if(in_array($row['REFERENCED_TABLE_NAME'],$conflicts,true))$this->fail('database','冲突表存在外键引用，请先处理依赖：'.$row['TABLE_SCHEMA'].'.'.$row['TABLE_NAME']);
    }
    private function progress(array $journal): array
    {
        $complete=count(array_filter($journal['tables'],static fn(array $state):bool=>in_array($state['phase'],['ready','publishing','complete'],true)));$deleted=count(array_filter($journal['deletions'],static fn(array $state):bool=>$state['state']==='deleted'));
        return ['installed'=>false,'pending'=>true,'done'=>false,'task_id'=>$journal['id'],'phase'=>$journal['phase'],'demo'=>(bool)($journal['import_demo']??false),'progress'=>['completed'=>$complete,'total'=>$journal['total'],'deleted'=>$deleted,'delete_total'=>count($journal['deletions'])]];
    }
    private function journal(): ?array
    {
        $record=$this->readPrivate(self::JOURNAL);
        // Old completed/local installation metadata is not an installation lock.
        // Preserve it in history when a new, explicitly unlocked task begins.
        if(!$record)return null;
        if(!in_array($record['version']??null,[2,self::FORMAT],true)){if(($record['phase']??null)!=='complete')throw new RuntimeException('不支持的未完成安装任务版本，不能启动新的安装覆盖');return null;}
        if(!in_array($record['phase']??'', ['loading','dropping','publishing','finalizing','database_complete','complete'],true))throw new RuntimeException('安装任务阶段记录无效，请核对私有恢复记录');
        return $record;
    }
    private function saveJournal(array $data): void {$data['updated_at']=time();$this->writePrivate(self::JOURNAL,$data);}
    private function readPrivate(string $relative): ?array
    {
        $file=$this->path($relative);if(is_link($file))throw new RuntimeException('安装记录不能为符号链接');if(!is_file($file))return null;$value=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR);if(!is_array($value))throw new RuntimeException('安装记录损坏');return $value;
    }
    private function writePrivate(string $relative,array $data): void
    {
        $file=$this->path($relative);if(is_link($file)||!is_dir(dirname($file))||is_link(dirname($file)))throw new RuntimeException('安装记录路径无效');$tmp=tempnam(dirname($file),'.install-');
        try{chmod($tmp,0600);$bytes=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";if(file_put_contents($tmp,$bytes,LOCK_EX)!==strlen($bytes)||!rename($tmp,$file))throw new RuntimeException('无法写入安装恢复记录');}finally{if(is_file($tmp))unlink($tmp);}
    }
    private function retireHistory(): void
    {
        $files=[self::JOURNAL,'config/cms-installed.json','config/cms-installer-archive.json','config/cms-install-handoff.json'];$history=$this->path('config/cms-install-history');
        if(is_link($history))throw new RuntimeException('安装历史目录无效');if(!is_dir($history)&&!mkdir($history,0700))throw new RuntimeException('无法保存历史安装记录');
        $directory=$history.'/'.date('YmdHis').'-'.bin2hex(random_bytes(6));if(!mkdir($directory,0700))throw new RuntimeException('无法创建安装历史记录');
        foreach($files as $relative){$source=$this->path($relative);if(is_link($source))throw new RuntimeException('历史安装记录不能是符号链接');if(is_file($source)&&!rename($source,$directory.'/'.basename($relative)))throw new RuntimeException('无法转存历史安装记录');}
    }
    private function cleanUploads(): void
    {
        $root=$this->path('install/.imports');if(!is_dir($root)||is_link($root))return;
        $iterator=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);
        foreach($iterator as $entry){if($entry->isLink())throw new RuntimeException('上传暂存目录包含符号链接');if($entry->isDir())rmdir($entry->getPathname());elseif($entry->isFile())unlink($entry->getPathname());else throw new RuntimeException('上传暂存目录包含异常文件');}rmdir($root);
    }
    private function writeInstallLock(): void
    {
        $files=$this->files();if(!$files['directory']||$files['locked'])return;$file=$this->path('install/install.lock');$handle=fopen($file,'x+b');if(!$handle)throw new RuntimeException('安装锁写入失败');try{if(!chmod($file,0600)||!fflush($handle))throw new RuntimeException('安装锁写入失败');}finally{fclose($handle);}
    }
    private function writeInstalled(array $in,string $source): void {$this->writePrivate('config/cms-installed.json',['version'=>2,'installed_at'=>time(),'source'=>$source,'database'=>$in['database'],'db_prefix'=>$in['db_prefix'],'cms_prefix'=>$in['cms_prefix'],'admin_prefix'=>$in['admin_prefix'],'template_mode'=>$in['template_mode']]);}
    /** FORMAT3 records created before PHP storage retain their original recovery path. */
    private function assertConfigOverrides(array $in): void
    {
        $journal=$this->journal();
        if ($journal && ($journal['phase'] ?? '') !== 'complete') {
            $storage=$journal['config_storage'] ?? null;
            if ($storage === null) return;
            if (!in_array($storage,['php-database-v1','yaml-database-v1'],true)) throw new RuntimeException('安装配置存储版本无效');
        }
        $this->checkDatabaseOverrides($in);
    }
    private function checkDatabaseOverrides(array $in): void
    {
        $checks=$this->databaseConfigChecks();
        if (in_array(false, $checks, true)) throw new RuntimeException('YAML 数据库配置不可读、不可写或格式无效');
        (new InstallerEnvironment($this->root))->inspect($this->databaseValues($in));
    }
    private function databaseConfigChecks(): array
    {
        $readable=true;$writable=true;$valid=true;
        try{\app\cms\config\DeploymentConfig::document(\app\cms\config\DeploymentConfig::discoverFile());}catch(\Throwable){$readable=false;$valid=false;}
        try{DatabaseSettings::assertWritable();}catch(\Throwable){$writable=false;}
        return ['database_config_readable'=>$readable,'database_config_writable'=>$writable,'database_config_managed'=>$valid];
    }

    private function databaseValues(array $in): array
    {
        return ['DB_HOST'=>$in['host'],'DB_PORT'=>(string)$in['port'],'DB_NAME'=>$in['database'],
            'DB_USER'=>$in['user'],'DB_PASSWORD'=>$in['password'],'DB_PREFIX'=>$in['db_prefix'],
            'DB_TABLE_LAYOUT'=>'unified-backend-v1','CMS_DB_PREFIX'=>$in['cms_prefix'],'ADMIN_DB_PREFIX'=>$in['admin_prefix']];
    }
    private function environmentValues(): array {$file=$this->path('.env');if(is_link($file))throw new RuntimeException('环境配置不能为符号链接');return is_file($file)?\Dotenv\Dotenv::parse((string)file_get_contents($file)):[];}
    private function configuredInput(array $env): array
    {
        $prefix=($env['DB_TABLE_LAYOUT']??'')==='unified-backend-v1'?($env['DB_PREFIX']??'wa_'):($env['CMS_DB_PREFIX']??'ey_');$data=$this->normalize(['host'=>$env['DB_HOST']??'','port'=>$env['DB_PORT']??3306,'database'=>$env['DB_NAME']??'','user'=>$env['DB_USER']??'','password'=>$env['DB_PASSWORD']??'','db_prefix'=>$prefix],false,false);
        if(($env['DB_TABLE_LAYOUT']??'')!=='unified-backend-v1')$data['admin_prefix']=$env['ADMIN_DB_PREFIX']??'wa_';return $data;
    }
    private function writeEnvironment(array $in): void
    {
        $file=$this->path('.env');if(is_link($file)||(file_exists($file)&&!is_file($file)))throw new RuntimeException('环境配置路径无效');$content=is_file($file)?(string)file_get_contents($file):'';
        foreach(['CMS_DB_PREFIX','ADMIN_DB_PREFIX'] as $obsolete)$content=preg_replace('/^'.preg_quote($obsolete,'/').'\s*=.*(?:\r?\n|$)/m','',$content);
        $values=['DB_HOST'=>$in['host'],'DB_PORT'=>$in['port'],'DB_NAME'=>$in['database'],'DB_USER'=>$in['user'],'DB_PASSWORD'=>$in['password'],'DB_PREFIX'=>$in['db_prefix'],'DB_TABLE_LAYOUT'=>'unified-backend-v1','CMS_THEME'=>$in['theme'],'APP_DEBUG'=>'false'];
        foreach($values as $key=>$value){$encoded='"'.str_replace(['\\','"','$'],['\\\\','\\"','\\$'],(string)$value).'"';$line=$key.'='.$encoded;if(preg_match('/^'.preg_quote($key,'/').'\s*=.*$/m',$content))$content=preg_replace_callback('/^'.preg_quote($key,'/').'\s*=.*$/m',static fn()=>$line,$content);else$content.=($content!==''&&!str_ends_with($content,"\n")?"\n":'').$line."\n";}
        $tmp=tempnam($this->root,'.env-install-');try{chmod($tmp,0600);if(file_put_contents($tmp,$content,LOCK_EX)!==strlen($content)||!@rename($tmp,$file))throw new RuntimeException('环境配置写入失败');}finally{if(is_file($tmp))unlink($tmp);}
    }
    /** Explicit finishing path; no installation marker table is read or written. */
    public function seal(bool $check=false): array
    {
        if($legacy=$this->legacyResumeService())return $legacy->seal($check);
        $this->assertPaths();$file=$this->path('config/cms-installing.lock');
        // A check may take a shared lock on an existing file, but creates nothing.
        $handle=$check?(is_file($file)?fopen($file,'r'):null):fopen($file,'c+');
        if($handle&&(!$check&&!chmod($file,0600)||!flock($handle,($check?LOCK_SH:LOCK_EX)|LOCK_NB)))throw new RuntimeException('安装正在执行，请稍后重试');
        if(!$check&&!$handle)throw new RuntimeException('无法获取安装互斥锁');
        try{
            $journal=$this->journal();
            if($journal&&!in_array($journal['phase']??'', ['database_complete','complete'],true))throw new RuntimeException('安装数据库阶段尚未完成，请使用相同安装参数续装，不能提前封存');
            $legacyJournal=$journal && ($journal['phase']??'')!=='complete' && !isset($journal['config_storage']);
            $in=$this->configuredInput($legacyJournal ? $this->environmentValues() : DatabaseSettings::values($this->root));$pdo=$this->connect($in);$lockName='cms-install-'.substr(hash('sha256',$in['database']),0,40);$lock=$pdo->prepare('SELECT GET_LOCK(?,0)');$lock->execute([$lockName]);
            if((int)$lock->fetchColumn()!==1)throw new RuntimeException('所选数据库正在执行其他安装');
            try{
                if(!(new LegacyInstallationInspector())->inspect($pdo,$in['cms_prefix'])['recognized'])throw new RuntimeException('实际数据库核心结构或必要基础数据不完整，拒绝封存');
                $admin=$in['admin_prefix'].'admins';if(!$this->tableExists($pdo,$admin)||(int)$pdo->query('SELECT COUNT(*) FROM `'.$admin.'`')->fetchColumn()===0)throw new RuntimeException('后台管理员未初始化，拒绝封存');
                $q=$pdo->prepare('SELECT value FROM `'.$in['admin_prefix'].'options` WHERE name=?');$q->execute(['cms_template_mode']);$mode=$q->fetchColumn();
                if($mode!==false&&!in_array($mode,['webman','eyou'],true))throw new RuntimeException('实际数据库模板模式无效，拒绝封存');
                $in['template_mode']=$mode===false?'eyou':$mode;
                if($journal&&(($journal['db_prefix']??null)!==$in['db_prefix']||($journal['template_mode']??null)!==$in['template_mode']))throw new RuntimeException('数据库配置与本次任务不匹配，拒绝封存');
                if($journal&&in_array($journal['version']??null,[2,self::FORMAT],true)){$q->execute(['cms_site_signature']);$signature=$q->fetchColumn();if(!is_string($signature)||!hash_equals((string)($journal['site_signature']??''),$signature))throw new RuntimeException('实际数据库并非本次已完成的安装，请使用原表单补写环境配置后再封存');}
                if(!$check){$this->writeInstalled($in,'seal-recovery');$this->writeInstallLock();$this->cleanUploads();}
                $result=['installed'=>true]+(new InstallerArchive($this->root))->seal($check);
                if(!$check&&$journal){
                    $journal['phase']='complete';$this->saveJournal($journal);if(is_file($this->path(self::PLAN)))unlink($this->path(self::PLAN));
                    // Old journals without a recorded administrator cannot
                    // acquire a new browser qualification through CLI recovery.
                    if(!empty($journal['admin_id'])){
                        try{$result['handoff']=(new InstallerHandoff($this->root))->prepare($journal,false);}
                        catch(\Throwable $error){$result['handoff']=['restart_status'=>'failed','login_available'=>false,'message'=>'安装收尾已完成，自动重启安排失败，请检查私有安装记录和进程权限'];}
                    }
                }
                return $result;
            }finally{$pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lockName]);}
        }finally{if($handle){flock($handle,LOCK_UN);fclose($handle);}}
    }
    public function registerExisting(PDO $pdo,string $cmsPrefix,string $adminPrefix): array
    {
        if(!(new LegacyInstallationInspector())->inspect($pdo,$cmsPrefix)['recognized'])throw new RuntimeException('已有站点核心结构不完整');
        if(!$this->tableExists($pdo,$adminPrefix.'admins'))throw new RuntimeException('已有站点后台结构不完整');
        $in=['database'=>(string)$pdo->query('SELECT DATABASE()')->fetchColumn(),'db_prefix'=>$cmsPrefix,'cms_prefix'=>$cmsPrefix,'admin_prefix'=>$adminPrefix,'template_mode'=>'eyou'];$this->writeInstalled($in,'existing-site-migration');$this->writeInstallLock();return ['registered'=>true,'database'=>$in['database']];
    }
}
