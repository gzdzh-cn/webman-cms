<?php
declare(strict_types=1);
namespace app\cms\config;

use Symfony\Component\Yaml\Yaml;

/** Immutable startup config, with explicitly scoped Redis refreshes. No PHP config execution. */
final class DeploymentConfig
{
    private static ?array $snapshot=null;
    private static ?string $file=null;
    private static array $sources=[];
    private static array $documents=[];
    public static function file(): string
    {
        return self::$file ??= self::discoverFile();
    }
    /** Installation resolves the next process's file without changing this worker's database snapshot. */
    public static function discoverFile(): string
    {
        if($embedded=\app\cms\support\EmbeddedResources::yaml())return $embedded;
        $env=Environment::overrides();$file=null;
        if(array_key_exists('CMS_CONFIG_FILE',$env))$file=Entry::absolute((string)$env['CMS_CONFIG_FILE']);
        else foreach(\app\cms\support\EmbeddedResources::YAML as $candidate) {
            $path=Entry::absolute($candidate);clearstatcache(true,$path);
            if(file_exists($path)||is_link($path)){$file=$path;break;}
        }
        $file??=Entry::absolute('config/manifest/config.yaml');
        Entry::assertPath($file);
        return $file;
    }
    public static function document(?string $path=null): array
    {
        $path??=self::file();$embedded=str_starts_with($path,'phar://');
        if($embedded) { if($path!==\app\cms\support\EmbeddedResources::yaml() || !\app\cms\support\EmbeddedResources::realpath($path))throw new \RuntimeException('内置 YAML 路径未登记'); } else Entry::assertPath($path);
        clearstatcache(true,$path);
        if(!file_exists($path)) return [];
        $stat=$embedded?stat($path):lstat($path);
        if(!$stat || !is_file($path) || (!$embedded&&!is_readable($path)) || (!$embedded&&$stat['nlink']!==1) || $stat['size']>1048576 || (!$embedded&&($stat['mode']&0007)!==0)) throw new \RuntimeException('YAML 配置文件类型、大小或私有权限无效');
        $bytes=file_get_contents($path);
        if(!is_string($bytes) || str_contains($bytes,"\0")) throw new \RuntimeException('无法读取 YAML 配置');
        $digest=hash('sha256',$bytes);
        if((self::$documents[$path]['digest']??null)===$digest) return self::$documents[$path]['data'];
        try { $data=Yaml::parse($bytes,Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE); }
        catch(\Symfony\Component\Yaml\Exception\ParseException $error) { throw new \RuntimeException('YAML 格式无效，请检查第 '.max(1,$error->getParsedLine()).' 行'); }
        if(!is_array($data) || ($data && array_is_list($data))) throw new \RuntimeException('YAML 顶层必须是配置映射');
        ConfigSchema::flatten($data);
        self::$documents[$path]=['digest'=>$digest,'data'=>$data];
        return $data;
    }
    public static function initialize(): void
    {
        if(self::$snapshot!==null)return;
        $saved=ConfigSchema::flatten(self::document());$env=Environment::overrides();$flat=[];
        foreach(ConfigSchema::fields() as $key=>[$default,$name]) {
            $value=array_key_exists($name,$env)&&$name!=='' ? $env[$name] : ($saved[$key]??$default);
            $flat[$key]=$name!==''&&array_key_exists($name,$env)?ConfigSchema::environment($key,$value):ConfigSchema::coerce($key,$value);
            self::$sources[$key]=$name!==''&&array_key_exists($name,$env)?Environment::source($name):(array_key_exists($key,$saved)?(\app\cms\support\EmbeddedResources::yaml()?'embedded':'yaml'):'default');
        }
        self::validateRelations($flat);
        self::$snapshot=$flat;
    }
    private static function validateRelations(array $flat): void
    {
        if($flat['database.pool.min_connections']>$flat['database.pool.max_connections'])throw new \RuntimeException('数据库连接池最小数量超过最大数量');
        if($flat['limits.install_chunk_bytes']>$flat['server.max_package_size']-65536)throw new \RuntimeException('安装分片超过请求大小限制');
        if($flat['session.same_site']==='None'&&!$flat['session.secure'])throw new \RuntimeException('SameSite=None 需要启用安全 Cookie');
    }
    public static function get(string $key): mixed
    {
        self::initialize();return self::$snapshot[$key]??(array_key_exists($key,self::$snapshot)?null:throw new \RuntimeException('未知配置键：'.$key));
    }
    /** Workerman requires a transport URI; deployers normally supply only the HTTP port. */
    public static function listener(mixed $value=null): string
    {
        $listen=ConfigSchema::coerce('server.listen',$value ?? self::get('server.listen'));
        return is_int($listen) ? 'http://0.0.0.0:'.$listen : $listen;
    }
    public static function group(string $name, bool $live=false): array
    {
        if($live && $name!=='redis')throw new \LogicException('仅 Redis 配置允许请求内刷新');
        if(!$live) { self::initialize();$flat=self::$snapshot; }
        else {
            $saved=ConfigSchema::flatten(self::document());$env=Environment::overrides();$flat=[];
            foreach(ConfigSchema::fields()as$key=>[$default,$var])if(str_starts_with($key,'redis.'))$flat[$key]=ConfigSchema::coerce($key,array_key_exists($var,$env)?$env[$var]:($saved[$key]??$default));
        }
        return ConfigSchema::expand($flat)[$name]??[];
    }
    public static function environmentValue(string $name, mixed $fallback=null): mixed
    {
        foreach(ConfigSchema::fields()as$key=>$field)if($field[1]===$name)return self::get($key);
        return Environment::value($name,$fallback);
    }
    public static function report(): array
    {
        self::initialize();$fields=[];
        foreach(ConfigSchema::fields()as$key=>$spec)$fields[$key]=['value'=>preg_match('/password/',$key)?'[redacted]':self::$snapshot[$key],'default'=>preg_match('/password/',$key)?'[redacted]':$spec[0],'type'=>$spec[2],'sensitive'=>(bool)preg_match('/password/',$key),'source'=>self::$sources[$key],'environment'=>$spec[1],'effective'=>str_starts_with($key,'redis.')?'next_request':(str_starts_with($key,'build.')?'build':'restart')];
        $document=self::document();
        $warnings=array_key_exists('key_file',(array)($document['security']??[]))?['security.key_file 已废弃并忽略，可删除该字段；程序不再使用本地密钥文件']:[];
        if(array_key_exists('CMS_SECURITY_KEY_FILE',Environment::overrides()))$warnings[]='CMS_SECURITY_KEY_FILE 已废弃并忽略，可删除该环境变量';
        if(array_key_exists('site',$document))$warnings[]='site 区段已废弃并忽略，可删除；网址与主题使用后台设置，根路径和私有备份目录由系统管理';
        foreach(ConfigSchema::retiredSite() as $name)if(array_key_exists($name,Environment::overrides()))$warnings[]=$name.' 已废弃并忽略，可删除该环境变量';
        $highPerformance = self::get('performance.separate_task_processes');
        $topology = ['mode'=>$highPerformance?'separate':'combined', 'high_performance'=>$highPerformance, 'background_processes'=>$highPerformance?['cms-baidu-push','cms-generation']:['cms-background'], 'production_processes'=>self::get('server.workers')+($highPerformance?4:2), 'monitor_process'=>$highPerformance, 'memory_watchdog'=>$highPerformance?'monitor':'master', 'effective'=>'restart', 'reload_switches_mode'=>false];
        return ['ok'=>true,'process_topology'=>$topology,'fixed'=>(bool)\app\cms\support\EmbeddedResources::yaml(),'configuration_source'=>\app\cms\support\EmbeddedResources::yaml()?'embedded':'external','file'=>self::file(),'exists'=>is_file(self::file()),'fields'=>$fields,'warnings'=>$warnings];
    }
}
