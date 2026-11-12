<?php
declare(strict_types=1);
namespace app\cms\database;
use app\cms\config\DeploymentConfig;
use app\cms\config\ConfigFile;
use app\cms\config\Environment;
final class DatabaseSettings
{
    public const FIELDS = ['DB_HOST'=> 'host', 'DB_PORT'=> 'port', 'DB_NAME'=> 'name', 'DB_USER'=> 'username', 'DB_PASSWORD'=> 'password', 'DB_PREFIX'=> 'prefix', 'DB_TABLE_LAYOUT'=> 'table_layout', 'CMS_DB_PREFIX'=> 'cms_prefix', 'ADMIN_DB_PREFIX'=> 'admin_prefix'];
    public static function saved(?string $root=null): array
    {
        $saved=DeploymentConfig::document()['database']??[];$out=[];
        foreach(self::FIELDS as $key=>$field)if(array_key_exists($field,$saved))$out[$key]=(string)$saved[$field];
        return $out;
    }
    public static function values(?string $root=null): array
    {
        $settings=DeploymentConfig::group('database');$out=[];
        foreach(self::FIELDS as $key=>$field)$out[$key]=(string)$settings[$field];
        self::validate($out);return $out;
    }
    public static function fresh(bool $installation=false): array
    {
        $saved=DeploymentConfig::document($installation?DeploymentConfig::discoverFile():null)['database']??[];$env=Environment::overrides();$out=[];
        foreach(self::FIELDS as$key=>$field){$default=\app\cms\config\ConfigSchema::fields()['database.'.$field][0];$value=array_key_exists($key,$env)?$env[$key]:($saved[$field]??$default);$out[$key]=(string)\app\cms\config\ConfigSchema::coerce('database.'.$field,$value);}
        self::validate($out);return$out;
    }
    public static function fallbacks(?string $root=null): array { return self::values($root); }
    public static function assertWritable(?string $root=null): void { ConfigFile::assertWritable(true); }
    public static function assertOverrides(array $expected, ?string $root=null): void
    {
        $overrides=Environment::overrides();
        foreach($expected as $key=>$value)if(!isset(self::FIELDS[$key])||!is_scalar($value))throw new \InvalidArgumentException('数据库配置字段无效');
        foreach($expected as $key=>$value)if(array_key_exists($key,$overrides)&&(string)$overrides[$key]!== (string)$value)throw new \RuntimeException('数据库配置被环境变量覆盖：'.$key);
    }
    public static function write(array $values, ?string $root=null): void
    {
        $next=self::fresh(true);$data=[];
        foreach($values as $key=>$value){if(!isset(self::FIELDS[$key])||(!is_string($value)&&!($key==='DB_PORT'&&is_int($value))))throw new \InvalidArgumentException('数据库配置字段无效');$next[$key]=(string)$value;$data[self::FIELDS[$key]]=$key==='DB_PORT'?(int)$value:(string)$value;}
        self::validate($next);ConfigFile::write(['database'=>$data],true,true);
    }
    private static function validate(array $values): void
    {
        foreach (array_keys(self::FIELDS) as $key) {
            if (!array_key_exists($key, $values) || !is_string($values[$key])) throw new \InvalidArgumentException('数据库配置类型无效: '.$key);
        }
        if (!preg_match('/^[A-Za-z0-9_.:-]{1,253}$/D', $values['DB_HOST'])) throw new \InvalidArgumentException('数据库主机无效');
        if (!preg_match('/^[1-9][0-9]{0,4}$/D', $values['DB_PORT']) || (int)$values['DB_PORT'] < 1 || (int)$values['DB_PORT'] > 65535) throw new \InvalidArgumentException('数据库端口无效');
        if ($values['DB_NAME'] !== '' && !preg_match('/^[A-Za-z0-9_]{1,64}$/D', $values['DB_NAME'])) throw new \InvalidArgumentException('数据库名称无效');
        if (!in_array($values['DB_TABLE_LAYOUT'], ['', 'legacy', 'unified-backend-v1'], true)) throw new \InvalidArgumentException('数据库表布局无效');
        foreach (['DB_PREFIX', 'CMS_DB_PREFIX', 'ADMIN_DB_PREFIX'] as $key) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,47}$/D', $values[$key])) throw new \InvalidArgumentException('数据库表前缀无效: '.$key);
        }
        if ($values['DB_TABLE_LAYOUT'] === 'unified-backend-v1' && strlen($values['DB_PREFIX']) > 40) throw new \InvalidArgumentException('统一布局表前缀过长');
    }
}
