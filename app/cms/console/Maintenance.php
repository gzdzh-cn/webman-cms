<?php
declare(strict_types=1);
namespace app\cms\console;
use app\cms\database\Connection;
use app\cms\database\SqlImporter;
use app\cms\database\Migrations;
use app\cms\persistence\Schema;
use app\cms\persistence\StateStore;
use PDO;

final class Maintenance
{
    public static function initialize(bool $dryRun): array
    {
        if(!$dryRun)throw new \RuntimeException('数据库初始化写入已停用；请使用 cms:install --preflight --input 审阅模式、统一前缀及冲突表，再通过 cms:install --input 安装');
        [$cms,$admin]=Connection::prefixes();$name=(string)(\app\cms\database\DatabaseSettings::values()['DB_NAME']);$pdo=Connection::environment(false);
        $archive=new \app\cms\install\InstallerArchive(\app\cms\Paths::root());
        $sources=[[$archive->baselinePath('cms-empty.sql'),'cms_',$cms],[$archive->baselinePath('webman-admin.sql'),'wa_',$admin],[$archive->baselinePath('cms-internal.sql'),'cms_',$cms]];$tables=[];
        foreach($sources as[$path,$from,$to])$tables=array_merge($tables,SqlImporter::tables($path,$from,$to));
        $query=$pdo->prepare('SELECT 1 FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=?');$query->execute([$name]);$exists=(bool)$query->fetchColumn();$present=[];
        if($exists){$query=$pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=?');$query->execute([$name]);$present=$query->fetchAll(PDO::FETCH_COLUMN);}
        $names=array_column($tables,'name');$conflicts=array_values(array_intersect($names,$present));
        return ['database'=>$name,'dry_run'=>true,'write_disabled'=>true,'next_command'=>'cms:install --preflight --input /absolute/private-install.json','baseline_tables'=>count($tables),'target_tables'=>$names,'conflicts'=>$conflicts,'sources'=>array_map(static fn($s)=>['file'=>basename($s[0]),'sha256'=>hash_file('sha256',$s[0])],$sources)];
    }

    public static function migrate(bool $apply): array
    {
        [$cms,$admin]=Connection::prefixes();$pdo=Connection::environment();$pending=Migrations::apply($pdo,$admin,!$apply,$cms);
        $q=$pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (?,?,?)');$names=[$cms.'cms_state_records',$cms.'cms_state_objects',$cms.'cms_state_object_refs'];$q->execute($names);$missing=array_values(array_diff($names,$q->fetchAll(PDO::FETCH_COLUMN)));
        if($apply)Schema::install($pdo,$cms);return ['mode'=>$apply?'apply':'dry-run','migrations'=>$pending,'state_tables_missing'=>$missing];
    }
    public static function bootstrap(array $input): array
    {
        [, $admin]=Connection::prefixes();$pdo=Connection::environment();$username=(string)($input['username']??'admin');$password=(string)($input['password']??'');
        if(!preg_match('/^[A-Za-z][A-Za-z0-9_]{2,31}$/D',$username))throw new \InvalidArgumentException('Invalid administrator username');
        $q=$pdo->prepare('SELECT id FROM `'.$admin.'admins` WHERE username=?');$q->execute([$username]);if($q->fetchColumn())return ['created'=>false,'existing_admin_preserved'=>true];
        if(strlen($password)<12||strlen($password)>72)throw new \InvalidArgumentException('Password must contain 12 to 72 bytes');
        $pdo->beginTransaction();try{$pdo->prepare('INSERT INTO `'.$admin.'admins` (username,nickname,password,created_at,updated_at,status) VALUES(?,?,?,NOW(),NOW(),0)')->execute([$username,'管理员',password_hash($password,PASSWORD_DEFAULT)]);$id=$pdo->lastInsertId();$pdo->prepare('INSERT INTO `'.$admin.'admin_roles`(role_id,admin_id) VALUES(1,?)')->execute([$id]);$pdo->commit();return ['created'=>true,'id'=>(int)$id,'credentials_saved'=>false];}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    public static function syncMenu(bool $apply=false): array
    {
        [$cms,$admin]=Connection::prefixes();$pdo=Connection::environment();$before=$pdo->query('SELECT * FROM `'.$admin.'rules` ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);if($apply)Schema::install($pdo,$cms);
        if(!$apply){$known=array_fill_keys(array_column($before,'key'),true);$missing=[];$inspect=function(array $items)use(&$inspect,$known,&$missing){foreach($items as$item){if(!isset($known[$item['key']]))$missing[]=$item['key'];$inspect($item['children']??[]);}};$inspect(require \app\cms\Paths::cms('admin/config/menu.php'));return ['mode'=>'dry-run','menus_before'=>count($before),'missing_keys'=>$missing];}
        $bytes=json_encode($before,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);(new StateStore('backups:menus',null,$cms))->write(hash('sha256',$bytes),['rows'=>$before,'database'=>\app\cms\database\DatabaseSettings::values()['DB_NAME'],'admin_prefix'=>$admin]);
        $pdo->beginTransaction();try{$walk=function(array $items,int $parent=0)use(&$walk,$pdo,$admin){foreach($items as$item){$children=$item['children']??[];unset($item['children']);$q=$pdo->prepare('SELECT id FROM `'.$admin.'rules` WHERE `key`=?');$q->execute([$item['key']]);$id=$q->fetchColumn();if(!$id){$item=array_intersect_key($item,array_flip(['title','key','icon','href','weight','type','status']));$item+=['pid'=>$parent,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];$pdo->prepare('INSERT INTO `'.$admin.'rules` (`'.implode('`,`',array_keys($item)).'`) VALUES ('.implode(',',array_fill(0,count($item),'?')).')')->execute(array_values($item));$id=$pdo->lastInsertId();}$walk($children,(int)$id);}};$walk(require \app\cms\Paths::cms('admin/config/menu.php'));$current=$pdo->query('SELECT * FROM `'.$admin.'rules` ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);$byId=array_column($current,null,'id');foreach($before as$row)if(($byId[$row['id']]??null)!==$row)throw new \RuntimeException('Existing menu rows changed');$pdo->commit();return ['mode'=>'missing-only','existing_rows_unchanged'=>true,'menus_before'=>count($before),'menus_after'=>count($current)];}catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    public static function privateInput(string $path): array
    {
        if(!str_starts_with($path,'/')||realpath($path)!==$path||is_link($path)||!is_file($path)||(fileperms($path)&0077)!==0||filesize($path)>65536)throw new \InvalidArgumentException('Input must be an absolute private JSON file (0600)');
        foreach([\app\cms\Paths::publicRoot(),\app\cms\Paths::templates('')]as$public)if($path===$public||str_starts_with($path,rtrim($public,'/').'/'))throw new \InvalidArgumentException('Input must not be publicly served');
        $data=json_decode((string)file_get_contents($path),true,512,JSON_THROW_ON_ERROR);if(!is_array($data))throw new \InvalidArgumentException('Invalid JSON input');return $data;
    }
}
