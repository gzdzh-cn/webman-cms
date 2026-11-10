<?php
declare(strict_types=1);
namespace app\cms\maintenance;

use app\cms\Paths;
use app\cms\database\TableNames;
use app\cms\repository\CmsRepository;
use app\cms\template\TemplateMode;
use InvalidArgumentException;
use RuntimeException;

/** Private, authenticated project backups. Uploaded SQL is never accepted here. */
final class DatabaseBackup
{
    private string $directory;
    private string $secret;
    private ?array $incomingReferences=null;
    private bool $foldReferenceNames=false;
    private bool $normalizeCharacterSet=false;
    public function __construct(private ?CmsRepository $repository=null, ?string $directory=null)
    {
        $this->repository??=new CmsRepository();
        $this->directory=$directory??(\app\cms\config\SiteSettings::get('backup_path')?:dirname(Paths::root()).'/cms-private-backups-'.substr(hash('sha256',Paths::root()),0,12));
        if(!str_starts_with($this->directory,'/')||str_contains($this->directory,'..')||str_starts_with(rtrim($this->directory,'/').'/',Paths::root().'/'))throw new InvalidArgumentException('备份目录必须位于项目外的私有绝对路径');
        Paths::ensureDirectory($this->directory,0700);
        $resolved=realpath($this->directory);if($resolved===false||$resolved===realpath(Paths::root())||str_starts_with($resolved.'/',realpath(Paths::root()).'/'))throw new InvalidArgumentException('备份目录不可解析到项目公开范围');
        $this->directory=$resolved;if(!chmod($this->directory,0700))throw new RuntimeException('无法设置备份目录私有权限');
        $key=$this->directory.'/.key';
        if(is_link($key))throw new RuntimeException('备份密钥不能是链接');
        if(is_link($this->directory.'/.key-init'))throw new RuntimeException('备份密钥锁不能是链接');
        $init=fopen($this->directory.'/.key-init','c');if(!$init||!flock($init,LOCK_EX))throw new RuntimeException('备份密钥锁不可用');
        try{if(!is_file($key)){$f=@fopen($key,'x');if(!$f)throw new RuntimeException('无法创建备份密钥');try{chmod($key,0600);if(fwrite($f,random_bytes(32))!==32)throw new RuntimeException('备份密钥写入失败');}finally{fclose($f);}}
        $this->secret=(string)file_get_contents($key);if(strlen($this->secret)!==32)throw new RuntimeException('备份密钥不可用');}finally{flock($init,LOCK_UN);fclose($init);}
    }
    private function locked(callable $operation): mixed
    {
        $path=$this->directory.'/.lock';if(is_link($path))throw new RuntimeException('备份锁不能是链接');$f=fopen($path,'c');chmod($path,0600);
        if(!flock($f,LOCK_EX|LOCK_NB)){fclose($f);throw new RuntimeException('已有备份或恢复任务正在执行');}
        try{$this->incomingReferences=null;return $operation();}finally{flock($f,LOCK_UN);fclose($f);}
    }
    private function id(string $id): string {if(!preg_match('/^[a-f0-9]{32}$/D',$id))throw new InvalidArgumentException('备份或恢复编号无效');return$id;}
    private function path(string $id,string $file): string
    {
        $path=$this->directory.'/'.$this->id($id);if(is_link($path)||!is_dir($path))throw new InvalidArgumentException('本地备份不存在');
        if(!preg_match('/^(?:manifest\.json|task\.json|cleanup\.json|part-[0-9]{6}\.jsonl)$/D',$file))throw new InvalidArgumentException('备份文件名无效');
        $full=$path.'/'.$file;if(is_link($full))throw new InvalidArgumentException('备份文件不能是链接');return$full;
    }
    private function write(string $id,string $file,array $data): void
    {
        $path=$this->path($id,$file);$payload=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);$envelope=['payload'=>$payload,'hmac'=>hash_hmac('sha256',$payload,$this->secret)];
        $tmp=$path.'.'.bin2hex(random_bytes(5)).'.tmp';$bytes=json_encode($envelope,JSON_THROW_ON_ERROR);
        if(file_put_contents($tmp,$bytes,LOCK_EX)!==strlen($bytes)){@unlink($tmp);throw new RuntimeException('私有备份状态写入失败');}chmod($tmp,0600);if(!rename($tmp,$path)){@unlink($tmp);throw new RuntimeException('私有备份状态发布失败');}
    }
    private function read(string $id,string $file): array
    {
        $path=$this->path($id,$file);if(!is_file($path)||filesize($path)>16*1024*1024)throw new InvalidArgumentException('备份清单缺失或过大');
        try{$e=json_decode((string)file_get_contents($path),true,128,JSON_THROW_ON_ERROR);if(!is_string($e['payload']??null)||!is_string($e['hmac']??null)||!hash_equals(hash_hmac('sha256',$e['payload'],$this->secret),$e['hmac']))throw new RuntimeException('signature');$data=json_decode($e['payload'],true,128,JSON_THROW_ON_ERROR);}catch(\Throwable $e){throw new InvalidArgumentException('备份清单签名或格式损坏',0,$e);}return$data;
    }
    private function createDirectory(): string
    {
        do{$id=bin2hex(random_bytes(16));$path=$this->directory.'/'.$id;}while(file_exists($path));if(!mkdir($path,0700))throw new RuntimeException('无法创建私有备份目录');return$id;
    }
    private function quote(string $table): string {$this->repository->identifier($table);if(strlen($table)>64)throw new InvalidArgumentException('表名过长');return'`'.$table.'`';}
    private function database(): string {return(string)$this->repository->connection()->query('SELECT DATABASE() AS db')[0]['db'];}
    private function identity(): string {
        $c=(array)config('think-orm.connections.mysql',[]);$server=$this->repository->connection()->query('SELECT @@hostname AS host, @@port AS port')[0];
        return hash('sha256',json_encode([$this->database(),TableNames::cms(),TableNames::admin(),Paths::root(),array_intersect_key($c,array_flip(['hostname','hostport','socket','dsn'])),$server],JSON_THROW_ON_ERROR));
    }
    private function tables(): array
    {
        $rows=$this->repository->connection()->query('SELECT TABLE_NAME,TABLE_TYPE,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME');$out=[];
        foreach($rows as$row){$name=$row['TABLE_NAME'];if(!str_starts_with($name,TableNames::cms())&&!str_starts_with($name,TableNames::admin()))continue;
            if(preg_match('/^cmsrestore_[a-f0-9]{16}_[sb][0-9]+$/D',$name))continue;
            $this->quote($name);if($row['TABLE_TYPE']!=='BASE TABLE'||strtolower((string)$row['ENGINE'])!=='innodb')throw new InvalidArgumentException('首版备份仅支持 InnoDB 实体表：'.$name);$out[]=$name;
        }if(!$out)throw new InvalidArgumentException('当前站点没有可备份的数据表');return$out;
    }
    private function loadIncomingReferences(bool $refresh=false): void
    {
        if($this->incomingReferences!==null&&!$refresh)return;
        $db=$this->repository->connection();
        try {
            $server=$db->query('SELECT VERSION() AS version, @@lower_case_table_names AS lower_names')[0]??[];
            $version=(string)($server['version']??'');
            // General KEY_COLUMN_USAGE / REFERENTIAL_CONSTRAINTS can open every
            // server table in MySQL 5.7. Read the small InnoDB dictionary instead.
            if(preg_match('/^5\.7\./D',$version))$dictionary='INNODB_SYS_FOREIGN';
            elseif(preg_match('/^8\.\d+\./D',$version))$dictionary='INNODB_FOREIGN';
            else throw new RuntimeException('数据库版本不在受支持的MySQL5.7或8.x范围');
            $this->normalizeCharacterSet=preg_match('/^8\./D',$version)===1;
            $schema=$this->database();
            // InnoDB encodes special identifiers in dictionary names. Refuse
            // names we cannot compare exactly rather than omit an incoming FK.
            if(!preg_match('/^[A-Za-z0-9_]+$/D',$schema))throw new RuntimeException('数据库名称无法安全映射到InnoDB字典');
            $fold=(int)($server['lower_names']??0)!==0;
            if($fold)$schema=strtolower($schema);
            $pattern=strtr($schema,['='=>'==','%'=>'=%','_'=>'=_']).'/%';
            $rows=$db->query('SELECT REF_NAME FROM information_schema.'.$dictionary." WHERE REF_NAME LIKE ? ESCAPE '='",[$pattern]);
            $incoming=[];$prefix=$schema.'/';
            foreach($rows as$row){
                if(!is_string($row['REF_NAME']??null))throw new RuntimeException('InnoDB外键元数据格式无效');
                $name=$fold?strtolower($row['REF_NAME']):$row['REF_NAME'];
                if(!str_starts_with($name,$prefix))throw new RuntimeException('InnoDB外键元数据范围无效');
                $table=substr($name,strlen($prefix));
                if(!preg_match('/^[A-Za-z0-9_]+$/D',$table))throw new RuntimeException('引用表名称无法安全映射到InnoDB字典');
                $incoming[$table]=true;
            }
            $this->foldReferenceNames=$fold;$this->incomingReferences=$incoming;
        } catch(\Throwable $error) {
            throw new RuntimeException('无法安全读取InnoDB外键元数据；请检查受支持的MySQL版本及PROCESS权限。未执行替换操作',0,$error);
        }
    }
    private function hasIncomingReference(string $table): bool {return isset($this->incomingReferences[$this->foldReferenceNames?strtolower($table):$table]);}
    private function schema(string $table): array
    {
        $quoted=$this->quote($table);$db=$this->repository->connection();$create=$db->query('SHOW CREATE TABLE '.$quoted)[0]['Create Table'];
        $triggers=$db->query('SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND EVENT_OBJECT_TABLE=?',[$table]);
        // RENAME TABLE also retargets foreign keys owned by other tables. The
        // referencing table can be outside this site's prefixes or database.
        $this->loadIncomingReferences();
        if($this->hasIncomingReference($table))throw new InvalidArgumentException('数据表存在入向外键，暂不支持备份或替换：'.$table);
        if($triggers||preg_match('/\b(?:FOREIGN KEY\b|GENERATED ALWAYS\b|CHECK\s*\(|PARTITION BY\b|TABLESPACE\b)/i',$create))throw new InvalidArgumentException('暂不支持触发器、外键、生成列、检查约束或分区：'.$table);
        $columns=$db->query('SHOW COLUMNS FROM '.$quoted);foreach($columns as$c)if(!in_array(strtolower((string)$c['Extra']),['','auto_increment'],true))throw new InvalidArgumentException('不支持的列特性：'.$table.'.'.$c['Field']);
        $normalized=preg_replace('/\bAUTO_INCREMENT=\d+\s*/i','',$create);
        // MySQL8 can add an explicit character set while replaying its own
        // SHOW CREATE TABLE output. COLLATE already determines that set.
        // Limit canonicalization to the type/charset prefix of string columns;
        // defaults, comments and the executable original DDL stay untouched.
        // MySQL5.7 fingerprints retain their prior exact representation.
        if($this->normalizeCharacterSet)$normalized=preg_replace_callback('/^(  `[^`]+` (?:char|varchar|tinytext|text|mediumtext|longtext)(?:\([0-9]+\))?) CHARACTER SET ([A-Za-z0-9_]+) COLLATE ([A-Za-z0-9_]+)(?= )/mi',static fn($m)=>str_starts_with(strtolower($m[3]),strtolower($m[2]).'_')?$m[1].' COLLATE '.$m[3]:$m[0],$normalized);
        $normalized=preg_replace('/^CREATE TABLE `[^`]+`/','CREATE TABLE `__TABLE__`',$normalized);return['ddl'=>$create,'schema_sha'=>hash('sha256',$normalized),'columns'=>array_column($columns,'Field')];
    }
    private function encodedRow(array $row): string {return json_encode(array_map(static fn($v)=>$v===null?null:base64_encode((string)$v),array_values($row)),JSON_THROW_ON_ERROR);}
    private function fingerprint(string $table): ?array
    {
        $exists=$this->repository->connection()->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$table]);if(!$exists)return null;
        $schema=$this->schema($table);$hashes=[];foreach($this->repository->connection()->query('SELECT * FROM '.$this->quote($table))as$row)$hashes[]=hash('sha256',$this->encodedRow($row));sort($hashes,SORT_STRING);
        return['schema_sha'=>$schema['schema_sha'],'rows'=>count($hashes),'data_sha'=>hash('sha256',implode('',$hashes))];
    }
    public function create(): array
    {
        return$this->locked(function(){if(self::maintenance())throw new RuntimeException('数据库恢复维护期间不可备份');$tables=$this->tables();$db=$this->repository->connection();$id=$this->createDirectory();$manifest=['format'=>1,'kind'=>'webman-cms-private-backup','id'=>$id,'created_at'=>time(),'actor'=>function_exists('admin_id')&&request()?(int)admin_id():0,'identity'=>$this->identity(),'site'=>TemplateMode::siteSignature(),'mode'=>TemplateMode::current(),'tables'=>[]];$part=0;
            $schemas=[];foreach($tables as$table)$schemas[$table]=$this->schema($table);
            $db->execute('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$db->startTrans();
            try{foreach($tables as$table){$schema=$schemas[$table];$entry=$schema+['rows'=>0,'parts'=>[]];$buffer='';$count=0;$rowHashes=[];
                $flush=function()use(&$buffer,&$count,&$part,&$entry,$id){if($buffer==='')return;$name=sprintf('part-%06d.jsonl',++$part);$path=$this->path($id,$name);if(file_put_contents($path,$buffer,LOCK_EX)!==strlen($buffer))throw new RuntimeException('备份分卷写入失败');chmod($path,0600);$entry['parts'][]=['name'=>$name,'bytes'=>strlen($buffer),'rows'=>$count,'sha256'=>hash('sha256',$buffer)];$buffer='';$count=0;};
                foreach($db->query('SELECT * FROM '.$this->quote($table))as$row){$line=$this->encodedRow($row);if(strlen($line)>16*1024*1024)throw new InvalidArgumentException('单条记录超出首版16MiB限制：'.$table);if($count>=200||strlen($buffer)+strlen($line)>1024*1024)$flush();$buffer.=$line."\n";$count++;$entry['rows']++;$rowHashes[]=hash('sha256',$line);}$flush();sort($rowHashes,SORT_STRING);$entry['data_sha']=hash('sha256',implode('',$rowHashes));$manifest['tables'][$table]=$entry;
                if($schema!==$this->schema($table))throw new RuntimeException('备份期间表结构发生变化，请重试');
            }$this->loadIncomingReferences(true);foreach($tables as$table)if($this->hasIncomingReference($table))throw new RuntimeException('备份期间出现入向外键，请重试');$db->commit();}catch(\Throwable$e){$db->rollback();throw$e;}
            $manifest['complete']=true;$this->write($id,'manifest.json',$manifest);return['id'=>$id,'tables'=>count($tables),'rows'=>array_sum(array_column($manifest['tables'],'rows')),'created_at'=>$manifest['created_at']];
        });
    }
    public function backups(): array
    {
        $out=[];foreach(scandir($this->directory)?:[]as$id)if(preg_match('/^[a-f0-9]{32}$/D',$id)&&!is_link($this->directory.'/'.$id)&&is_file($this->directory.'/'.$id.'/manifest.json')){try{$m=$this->read($id,'manifest.json');$out[]=['id'=>$id,'created_at'=>$m['created_at'],'tables'=>count($m['tables']),'rows'=>array_sum(array_column($m['tables'],'rows')),'valid'=>true];}catch(\Throwable){$out[]=['id'=>$id,'valid'=>false];}}return array_reverse($out);
    }
    private function verify(string $id): array
    {
        $m=$this->read($id,'manifest.json');if(($m['format']??null)!==1||($m['kind']??'')!=='webman-cms-private-backup'||empty($m['complete'])||!is_array($m['tables']??null)||!hash_equals($m['identity'],$this->identity()))throw new InvalidArgumentException('仅能恢复本项目、本数据库前缀的完整本地备份');
        foreach($m['tables']as$table=>$entry){$this->quote($table);$rows=0;foreach($entry['parts']as$part){$file=$this->path($id,$part['name']);if(!is_file($file)||filesize($file)!==$part['bytes']||!hash_equals($part['sha256'],hash_file('sha256',$file)))throw new InvalidArgumentException('备份分卷损坏：'.$part['name']);$rows+=$part['rows'];}if($rows!==$entry['rows'])throw new InvalidArgumentException('备份记录数量损坏');}return$m;
    }
    public function preview(string $backup): array
    {
        return$this->locked(function()use($backup){$m=$this->verify($backup);$token=$this->createDirectory();$tables=[];foreach($m['tables']as$table=>$entry)$tables[$table]=['before'=>$this->fingerprint($table),'after'=>array_intersect_key($entry,array_flip(['schema_sha','rows','data_sha']))];
            $task=['format'=>1,'token'=>$token,'backup'=>$backup,'identity'=>$this->identity(),'actor'=>function_exists('admin_id')&&request()?(int)admin_id():0,'created_at'=>time(),'phase'=>'prepared','tables'=>$tables,'loaded'=>[],'site'=>TemplateMode::siteSignature()];$this->write($token,'task.json',$task);return['token'=>$token,'tables'=>$tables,'expires_at'=>$task['created_at']+1800,'retained'=>'本次备份以外的表不删除；被替换表保留在私有恢复前镜像中'];
        });
    }
    private function stage(string $token,int $i): string {return'cmsrestore_'.substr($token,0,16).'_s'.$i;}
    private function previous(string $token,int $i): string {return'cmsrestore_'.substr($token,0,16).'_b'.$i;}
    /** A signed creation intent must precede DDL; existing unknown tables stay untouched. */
    private function prepareStage(array &$task,string $token,string $table,string $stage,array $entry): bool
    {
        $expected=$task['tables'][$table]['after'];$current=$this->fingerprint($stage);
        $intent=$task['staging'][$table]??null;
        if($intent!==null&&($intent['table']??null)!==$stage)throw new RuntimeException('暂存表归属记录不一致：'.$table);
        if($intent!==null&&($intent['schema_sha']??null)!==$expected['schema_sha'])throw new RuntimeException('暂存表结构归属记录不一致：'.$table);
        if($current!==null){
            if($intent===null)throw new RuntimeException('暂存表已存在且不属于本任务，拒绝修改：'.$stage);
            if($current===$expected){$task['loaded'][$table]=true;$this->write($token,'task.json',$task);return false;}
            // All rows are loaded in one InnoDB transaction. An interrupted
            // attempt therefore leaves an empty table or a complete revision.
            if($current['schema_sha']!==$expected['schema_sha']||$current['rows']!==0||$current['data_sha']!==hash('sha256',''))throw new RuntimeException('暂存表存在未知或已修改内容，拒绝修改：'.$stage);
            return true;
        }
        if($intent===null){$task['staging'][$table]=['table'=>$stage,'schema_sha'=>$expected['schema_sha']];$this->write($token,'task.json',$task);}
        $ddl=preg_replace('/^CREATE TABLE `'.preg_quote($table,'/').'`/D','CREATE TABLE '.$this->quote($stage),$entry['ddl'],1,$replaced);
        if($replaced!==1)throw new RuntimeException('备份结构身份无效');
        $this->repository->connection()->execute($ddl);
        return true;
    }
    private function maintenanceWrite(string $token): void
    {
        $file=Paths::root('config/cms-database-maintenance.json');if(is_link($file))throw new RuntimeException('维护状态不能是链接');$existing=self::maintenance();if($existing&&($existing['token']??'')!==$token)throw new RuntimeException('其他数据库任务正在维护');
        $bytes=json_encode(['token'=>$token,'started_at'=>time()],JSON_THROW_ON_ERROR);$tmp=$file.'.'.bin2hex(random_bytes(5));if(file_put_contents($tmp,$bytes)!==strlen($bytes))throw new RuntimeException('无法开启维护状态');chmod($tmp,0600);if(!rename($tmp,$file))throw new RuntimeException('无法发布维护状态');
    }
    public static function maintenance(): array
    {
        $file=Paths::root('config/cms-database-maintenance.json');if(!file_exists($file))return[];if(is_link($file))return['invalid'=>true];try{return json_decode((string)file_get_contents($file),true,16,JSON_THROW_ON_ERROR)?:['invalid'=>true];}catch(\Throwable){return['invalid'=>true];}
    }
    private function maintenanceClear(string $token): void {if((self::maintenance()['token']??'')===$token&&!unlink(Paths::root('config/cms-database-maintenance.json')))throw new RuntimeException('数据库已恢复，但维护状态未能解除');}
    public function restore(string $token): array
    {
        return$this->locked(function()use($token){$task=$this->read($token,'task.json');if(!hash_equals($task['identity'],$this->identity()))throw new InvalidArgumentException('恢复目标已改变');if($task['phase']==='complete'){\app\cms\compat\Cache::clear();$this->maintenanceClear($token);return$this->status($token);}
            if($task['phase']==='prepared'&&time()-$task['created_at']>1800)throw new InvalidArgumentException('恢复预检已过期');
            $actor=function_exists('admin_id')&&request()?(int)admin_id():0;if($actor!==0&&$actor!==$task['actor'])throw new InvalidArgumentException('恢复任务不属于当前管理员');
            $m=$this->verify($task['backup']);$db=$this->repository->connection();$i=0;
            if($task['phase']==='prepared'){$task['phase']='loading';$this->write($token,'task.json',$task);}
            if($task['phase']==='loading'){
                foreach($m['tables']as$table=>$entry){$stage=$this->stage($token,$i++);if(isset($task['loaded'][$table])){if($this->fingerprint($stage)!==$task['tables'][$table]['after'])throw new RuntimeException('已加载暂存表发生变化：'.$table);continue;}
                    if(!$this->prepareStage($task,$token,$table,$stage,$entry))continue;
                    $db->startTrans();try{foreach($entry['parts']as$part){$f=fopen($this->path($task['backup'],$part['name']),'r');try{while(($line=fgets($f,24*1024*1024))!==false){$values=json_decode($line,true,64,JSON_THROW_ON_ERROR);if(count($values)!==count($entry['columns']))throw new RuntimeException('备份列数量错误');$values=array_map(static function($v){if($v===null)return null;if(!is_string($v)||($decoded=base64_decode($v,true))===false)throw new RuntimeException('备份记录编码错误');return$decoded;},$values);$db->execute('INSERT INTO '.$this->quote($stage).' ('.implode(',',array_map($this->quote(...),$entry['columns'])).') VALUES ('.implode(',',array_fill(0,count($values),'?')).')',$values);}}finally{fclose($f);}}$db->commit();}catch(\Throwable$e){$db->rollback();throw$e;}
                    if($this->fingerprint($stage)!==$task['tables'][$table]['after'])throw new RuntimeException('暂存表校验未通过：'.$table);$task['loaded'][$table]=true;$this->write($token,'task.json',$task);
                }
                $this->maintenanceWrite($token);return DatabaseTraffic::exclusive(function()use(&$task,$token,$m,$db){$this->loadIncomingReferences(true);foreach($task['tables']as$table=>$entry)if($this->fingerprint($table)!==$entry['before']){$this->maintenanceClear($token);throw new RuntimeException('目标表已变化，拒绝覆盖：'.$table.'；需重新预检');}
                $task['phase']='publishing';$this->write($token,'task.json',$task);return $this->publish($task,$token,$db);});
            }
            if($task['phase']==='publishing')return DatabaseTraffic::exclusive(fn()=>$this->publish($task,$token,$db));
            throw new RuntimeException('恢复任务阶段无效');
        });
    }
    private function publish(array &$task,string $token,$db):array
    {
                $this->loadIncomingReferences(true);$this->maintenanceWrite($token);$i=0;$rename=[];$published=true;
                foreach($task['tables']as$table=>$entry){$stage=$this->stage($token,$i);$old=$this->previous($token,$i++);if($this->fingerprint($stage)!==null){$published=false;if($this->fingerprint($table)!==$entry['before']||$this->fingerprint($old)!==null)throw new RuntimeException('发布前目标冲突，保持维护状态');if($entry['before']!==null)$rename[]=$this->quote($table).' TO '.$this->quote($old);$rename[]=$this->quote($stage).' TO '.$this->quote($table);}elseif($this->fingerprint($table)!==$entry['after']||($entry['before']!==null&&$this->fingerprint($old)!==$entry['before']))throw new RuntimeException('发布中断后内容不一致，保持维护状态');}
                if(!$published){if(count($rename)!==array_sum(array_map(static fn($v)=>$v['before']===null?1:2,$task['tables'])))throw new RuntimeException('发布状态混合，拒绝继续');$db->execute('RENAME TABLE '.implode(',',$rename));}
                foreach($task['tables']as$table=>$entry)if($this->fingerprint($table)!==$entry['after'])throw new RuntimeException('发布后校验失败');$task['phase']='complete';$task['completed_at']=time();$this->write($token,'task.json',$task);\app\cms\compat\Cache::clear();$this->maintenanceClear($token);
        return$this->status($token);
    }
    /** Roll back only the exact published revision; later edits are never overwritten. */
    public function rollback(string $token,bool $apply=false): array
    {
        return $this->locked(function()use($token,$apply){
            $task=$this->read($token,'task.json');
            if(isset($task['cleanup']))throw new InvalidArgumentException('本次恢复的前镜像清理已开始，不能再回退；备份包仍保留');
            if(!hash_equals($task['identity'],$this->identity())||!in_array($task['phase'],['complete','rolling_back','rolled_back'],true))throw new InvalidArgumentException('此任务没有可回退的已发布版本');
            if($task['phase']==='rolled_back'){\app\cms\compat\Cache::clear();$this->maintenanceClear($token);return['token'=>$token,'phase'=>'rolled_back'];}
            $diff=[];$index=0;
            foreach($task['tables']as$table=>$entry){$old=$this->previous($token,$index);$stage=$this->stage($token,$index++);$current=$this->fingerprint($table);
                if($task['phase']==='complete'&&($current!==$entry['after']||$this->fingerprint($old)!==$entry['before']||$this->fingerprint($stage)!==null))throw new RuntimeException('恢复后的表或前镜像已经变化，拒绝回退：'.$table);
                $diff[$table]=['current'=>$current,'restore'=>$entry['before']];
            }
            if(!$apply)return['token'=>$token,'phase'=>$task['phase'],'tables'=>$diff,'apply_required'=>true];
            $this->maintenanceWrite($token);
            return DatabaseTraffic::exclusive(function()use(&$task,$token){$this->loadIncomingReferences(true);$db=$this->repository->connection();$rename=[];$index=0;$already=true;
                foreach($task['tables']as$table=>$entry){$old=$this->previous($token,$index);$stage=$this->stage($token,$index++);
                    if($this->fingerprint($stage)===null){$already=false;if($this->fingerprint($table)!==$entry['after']||$this->fingerprint($old)!==$entry['before'])throw new RuntimeException('回退前数据发生变化，保持维护状态');$rename[]=$this->quote($table).' TO '.$this->quote($stage);if($entry['before']!==null)$rename[]=$this->quote($old).' TO '.$this->quote($table);}
                    elseif($this->fingerprint($stage)!==$entry['after']||$this->fingerprint($table)!==$entry['before'])throw new RuntimeException('回退中断状态不一致，保持维护状态');
                }
                if(!$already){if(count($rename)!==array_sum(array_map(static fn($v)=>$v['before']===null?1:2,$task['tables'])))throw new RuntimeException('回退状态混合，拒绝继续');$task['phase']='rolling_back';$this->write($token,'task.json',$task);$db->execute('RENAME TABLE '.implode(',',$rename));}
                $task['phase']='rolled_back';$this->write($token,'task.json',$task);\app\cms\compat\Cache::clear();$this->maintenanceClear($token);return['token'=>$token,'phase'=>'rolled_back'];
            });
        });
    }
    /** List signed task receipts without inspecting or deleting database objects. */
    public function tasks(): array
    {
        $identity=$this->identity();$out=[];foreach(scandir($this->directory)?:[]as$token){
            if(!preg_match('/^[a-f0-9]{32}$/D',$token)||is_link($this->directory.'/'.$token)||!is_file($this->directory.'/'.$token.'/task.json'))continue;
            try{$task=$this->read($token,'task.json');if(($task['token']??null)!==$token||!hash_equals((string)($task['identity']??''),$identity))throw new InvalidArgumentException('恢复任务身份无效');$out[]=$this->taskStatus($task)+['valid'=>true,'created_at'=>$task['created_at']??null];}
            catch(\Throwable){$out[]=['token'=>$token,'valid'=>false,'phase'=>'invalid'];}
        }return array_reverse($out);
    }

    private function actor(): int {return function_exists('admin_id')&&request()?(int)admin_id():0;}
    private function cleanupTask(string $token): array
    {
        $task=$this->read($token,'task.json');
        if(($task['format']??null)!==1||($task['token']??null)!==$token||!hash_equals((string)($task['identity']??''),$this->identity())||!hash_equals((string)($task['site']??''),TemplateMode::siteSignature()))throw new InvalidArgumentException('清理任务的站点或数据库身份已改变');
        if(!in_array($task['phase']??'', ['complete','rolled_back'],true))throw new InvalidArgumentException('只允许清理已完成或已回退任务，未完成任务必须先恢复');
        if(!is_array($task['tables']??null)||!$task['tables']||!is_array($task['loaded']??null))throw new InvalidArgumentException('恢复任务的归属清单不完整');
        $manifest=$this->verify((string)($task['backup']??''));
        if(array_keys($manifest['tables'])!==array_keys($task['tables']))throw new InvalidArgumentException('恢复任务与备份表清单不一致');
        $index=0;
        foreach($task['tables']as$table=>$entry){
            $this->quote($table);
            if((!str_starts_with($table,TableNames::cms())&&!str_starts_with($table,TableNames::admin()))||str_starts_with($table,'cmsrestore_'))throw new InvalidArgumentException('恢复表不在本站业务范围：'.$table);
            $after=array_intersect_key($manifest['tables'][$table],array_flip(['schema_sha','rows','data_sha']));
            if(($entry['after']??null)!==$after||empty($task['loaded'][$table])||($task['staging'][$table]['table']??null)!==$this->stage($token,$index++)||($task['staging'][$table]['schema_sha']??null)!==$after['schema_sha'])throw new InvalidArgumentException('恢复表的创建归属不完整：'.$table);
            foreach([$entry['before']??null,$after]as$fingerprint)if($fingerprint!==null&&(!is_array($fingerprint)||array_keys($fingerprint)!==['schema_sha','rows','data_sha']||!preg_match('/^[a-f0-9]{64}$/D',(string)$fingerprint['schema_sha'])||!preg_match('/^[a-f0-9]{64}$/D',(string)$fingerprint['data_sha'])||!is_int($fingerprint['rows'])||$fingerprint['rows']<0))throw new InvalidArgumentException('恢复表指纹损坏：'.$table);
        }
        return$task;
    }
    /** Signed immutable task content; cleanup progress does not alter its identity. */
    private function cleanupTaskSha(array $task): string {unset($task['cleanup']);return hash('sha256',json_encode($task,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));}
    private function cleanupObjects(array $task,string $token,bool $allowMissing=false): array
    {
        $objects=[];$index=0;$db=$this->repository->connection();$this->loadIncomingReferences(true);
        foreach($task['tables']as$table=>$entry){
            $old=$this->previous($token,$index);$stage=$this->stage($token,$index++);
            $name=$task['phase']==='complete'?$old:$stage;
            $other=$task['phase']==='complete'?$stage:$old;
            $expected=$task['phase']==='complete'?$entry['before']:$entry['after'];
            if($this->fingerprint($other)!==null)throw new RuntimeException('出现非预期的恢复对象，拒绝清理：'.$other);
            $actual=$this->fingerprint($name);
            if($expected===null){if($actual!==null)throw new RuntimeException('此表没有本任务的前镜像，拒绝清理：'.$name);continue;}
            if($actual===null&&!$allowMissing)throw new RuntimeException('清理对象缺失，不能确认归属：'.$name);
            if($actual!==null&&$actual!==$expected)throw new RuntimeException('清理对象已经变化，拒绝删除：'.$name);
            $meta=$actual===null?[]:($db->query('SELECT DATA_LENGTH,INDEX_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$name])[0]??[]);
            $objects[$name]=['table'=>$name,'source_table'=>$table,'kind'=>$task['phase']==='complete'?'before_image':'rolled_back_stage','fingerprint'=>$expected,'allocated_bytes'=>(int)($meta['DATA_LENGTH']??0)+(int)($meta['INDEX_LENGTH']??0),'exists'=>$actual!==null];
        }return$objects;
    }
    private function cleanupObjectSha(array $objects): string
    {
        return hash('sha256',json_encode(array_map(static fn($row)=>array_intersect_key($row,array_flip(['table','source_table','kind','fingerprint'])),$objects),JSON_THROW_ON_ERROR));
    }
    /** Read-only database preview. The signed confirmation receipt is private. */
    public function cleanupPreview(string $token): array
    {
        return$this->locked(function()use($token){
            if(self::maintenance())throw new RuntimeException('数据库维护期间不可清理恢复资料');
            $task=$this->cleanupTask($token);
            if(isset($task['cleanup'])){
                $plan=$this->read($token,'cleanup.json');
                if(!in_array($task['cleanup']['state']??'', ['cleaning','complete'],true)||($plan['state']??'')==='complete')throw new InvalidArgumentException('本任务的恢复资料已清理，原备份包仍保留');
                if(($plan['actor']??null)!==$this->actor()||!hash_equals((string)($plan['task_sha']??''),$this->cleanupTaskSha($task))||($task['cleanup']['confirmation']??'')!==($plan['confirmation']??null)||!hash_equals((string)($plan['identity']??''),$this->identity())||!hash_equals((string)($plan['site']??''),TemplateMode::siteSignature()))throw new InvalidArgumentException('不能接管其他管理员或已变化的清理任务');
                $objects=$this->cleanupObjects($task,$token,true);
                if(!hash_equals((string)($plan['objects_sha']??''),$this->cleanupObjectSha($objects)))throw new InvalidArgumentException('原清理清单已经变化');
                return['token'=>$token,'confirmation'=>$plan['confirmation'],'phase'=>$task['phase'],'objects'=>array_values($plan['objects']),'estimated_bytes'=>array_sum(array_column($plan['objects'],'allocated_bytes')),'resuming'=>true,'warning'=>'继续原清理任务，不重新删除已完成对象；本任务已不能再回退，原备份包保留','apply_required'=>true];
            }
            $objects=$this->cleanupObjects($task,$token);$plan=['format'=>1,'token'=>$token,'confirmation'=>bin2hex(random_bytes(16)),'identity'=>$this->identity(),'site'=>TemplateMode::siteSignature(),'actor'=>$this->actor(),'created_at'=>time(),'state'=>'prepared','task_sha'=>$this->cleanupTaskSha($task),'objects'=>$objects,'objects_sha'=>$this->cleanupObjectSha($objects),'removed'=>[]];
            $this->write($token,'cleanup.json',$plan);
            return['token'=>$token,'confirmation'=>$plan['confirmation'],'phase'=>$task['phase'],'objects'=>array_values($objects),'estimated_bytes'=>array_sum(array_column($objects,'allocated_bytes')),'expires_at'=>$plan['created_at']+1800,'warning'=>'清理仅删除所列恢复前镜像或回退暂存表，完成后本任务不能再回退；原备份包及任务收据保留','apply_required'=>true];
        });
    }
    /** Explicit exact-object cleanup; interruption resumes the same signed intent. */
    public function cleanup(string $token,string $confirmation): array
    {
        return$this->locked(function()use($token,$confirmation){
            if(self::maintenance())throw new RuntimeException('数据库维护期间不可清理恢复资料');
            $plan=$this->read($token,'cleanup.json');$task=$this->cleanupTask($token);
            if(!preg_match('/^[a-f0-9]{32}$/D',$confirmation)||!hash_equals((string)($plan['confirmation']??''),$confirmation)||($plan['token']??null)!==$token||($plan['format']??null)!==1||!hash_equals((string)($plan['identity']??''),$this->identity())||!hash_equals((string)($plan['site']??''),TemplateMode::siteSignature())||($plan['actor']??null)!==$this->actor())throw new InvalidArgumentException('清理确认号、管理员或站点身份不匹配');
            if(!in_array($plan['state']??'', ['prepared','cleaning','complete'],true)||!hash_equals((string)($plan['task_sha']??''),$this->cleanupTaskSha($task))||!is_array($plan['objects']??null)||!hash_equals((string)($plan['objects_sha']??''),$this->cleanupObjectSha($plan['objects'])))throw new InvalidArgumentException('清理清单或任务已经变化，请勿手动修改恢复资料');
            $intent=$task['cleanup']??null;
            if($intent!==null&&(($intent['confirmation']??'')!==$confirmation||!in_array($intent['state']??'', ['cleaning','complete'],true)))throw new InvalidArgumentException('恢复任务已绑定其他清理操作');
            if($plan['state']==='complete'&&($intent['state']??'')==='complete')return['token'=>$token,'phase'=>'cleaned','removed'=>count($plan['objects']),'replayed'=>true];
            if($plan['state']==='prepared'&&$intent===null&&time()-$plan['created_at']>1800)throw new InvalidArgumentException('清理预检已过期，请重新预览');
            return DatabaseTraffic::exclusive(function()use(&$task,&$plan,$token,$confirmation,$intent){
                $objects=$this->cleanupObjects($task,$token,$intent!==null);
                if(!hash_equals($plan['objects_sha'],$this->cleanupObjectSha($objects)))throw new RuntimeException('清理对象与预检不一致，拒绝删除');
                if($intent===null){$task['cleanup']=['confirmation'=>$confirmation,'state'=>'cleaning','started_at'=>time()];$this->write($token,'task.json',$task);}
                $plan['state']='cleaning';$this->write($token,'cleanup.json',$plan);
                foreach($objects as$name=>$object){
                    $this->loadIncomingReferences(true);$actual=$this->fingerprint($name);
                    if($actual!==null){if($actual!==$object['fingerprint'])throw new RuntimeException('清理对象在执行期间改变，停止删除：'.$name);$this->repository->connection()->execute('DROP TABLE '.$this->quote($name));}
                    if(!in_array($name,$plan['removed'],true))$plan['removed'][]=$name;$this->write($token,'cleanup.json',$plan);
                }
                $task['cleanup']['state']='complete';$task['cleanup']['completed_at']=time();$this->write($token,'task.json',$task);$plan['state']='complete';$plan['completed_at']=time();$this->write($token,'cleanup.json',$plan);
                return['token'=>$token,'phase'=>'cleaned','removed'=>count($objects),'estimated_bytes'=>array_sum(array_column($plan['objects'],'allocated_bytes')),'backup_retained'=>true];
            });
        });
    }
    private function taskStatus(array $task): array
    {
        $cleanup=isset($task['cleanup'])?array_intersect_key($task['cleanup'],array_flip(['state','started_at','completed_at'])):null;
        // A terminal task write may precede an interrupted final receipt write.
        // Keep the original cleanup reachable from the list after a refresh.
        if(($cleanup['state']??'')==='complete'){
            $plan=$this->read($task['token'],'cleanup.json');
            if(($plan['state']??'')==='cleaning'){
                if(($plan['token']??null)!==$task['token']||($plan['confirmation']??null)!==($task['cleanup']['confirmation']??null)||!hash_equals((string)($plan['task_sha']??''),$this->cleanupTaskSha($task))||!hash_equals((string)($plan['identity']??''),(string)$task['identity'])||!hash_equals((string)($plan['site']??''),(string)$task['site']))throw new InvalidArgumentException('清理收据与恢复任务不一致');
                $cleanup['state']='cleaning';$cleanup['finish_pending']=true;
            }
        }
        return['token'=>$task['token'],'backup'=>$task['backup'],'phase'=>$task['phase'],'loaded'=>count($task['loaded']),'tables'=>count($task['tables']),'completed_at'=>$task['completed_at']??null,'cleanup'=>$cleanup];
    }
    public function status(string $token): array {return$this->taskStatus($this->read($token,'task.json'));}
}
