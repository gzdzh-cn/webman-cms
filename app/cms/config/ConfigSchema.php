<?php
declare(strict_types=1);
namespace app\cms\config;

/** All deployer-controlled values, their legacy environment names and lifecycle. */
final class ConfigSchema
{
    public static function fields(): array
    {
        // key => [default, environment, type, minimum, maximum]. Redis and update settings are live.
        return [
            'version'=>[1,'','int',1,1],
            'update.provider'=>['gitee','CMS_UPDATE_PROVIDER','update_provider'],
            'update.custom_url'=>['','CMS_UPDATE_CUSTOM_URL','update_url'],
            'update.custom_password'=>['','CMS_UPDATE_CUSTOM_PASSWORD','update_password'],
            'build.output'=>['bin','CMS_BUILD_OUTPUT','build_path'],
            'build.binary_name'=>['cms','CMS_BUILD_BINARY_NAME','binary_name'],
            'build.target'=>[[],'CMS_BUILD_TARGET','targets'],
            'build.build-runtime'=>['bin/build-runtime','CMS_BUILD_RUNTIME','build_path'],
            'build.embed_admin_assets'=>[true,'CMS_BUILD_EMBED_ADMIN_ASSETS','bool'],
            'build.embedded_resources'=>[[],'CMS_BUILD_EMBEDDED_RESOURCES','embedded'],
            'database.host'=>['127.0.0.1','DB_HOST','string'],
            'database.port'=>[3306,'DB_PORT','int',1,65535],
            'database.name'=>['','DB_NAME','string'],
            'database.username'=>['','DB_USER','string'],
            'database.password'=>['','DB_PASSWORD','string'],
            'database.prefix'=>['wa_','DB_PREFIX','string'],
            'database.table_layout'=>['','DB_TABLE_LAYOUT','layout'],
            'database.cms_prefix'=>['ey_','CMS_DB_PREFIX','string'],
            'database.admin_prefix'=>['wa_','ADMIN_DB_PREFIX','string'],
            'database.pool.max_connections'=>[5,'DB_POOL_MAX_CONNECTIONS','int',1,1000],
            'database.pool.min_connections'=>[1,'DB_POOL_MIN_CONNECTIONS','int',0,1000],
            'database.pool.wait_timeout'=>[3,'DB_POOL_WAIT_TIMEOUT','int',1,3600],
            'database.pool.idle_timeout'=>[60,'DB_POOL_IDLE_TIMEOUT','int',1,86400],
            'database.pool.heartbeat_interval'=>[50,'DB_POOL_HEARTBEAT_INTERVAL','int',1,86400],
            'redis.host'=>['127.0.0.1','CMS_REDIS_HOST','string'],
            'redis.port'=>[6379,'CMS_REDIS_PORT','int',1,65535],
            'redis.username'=>['','CMS_REDIS_USERNAME','string'],
            'redis.password'=>['','CMS_REDIS_PASSWORD','string'],
            'redis.database'=>[0,'CMS_REDIS_DATABASE','int',0,2147483647],
            'redis.timeout'=>[0.5,'CMS_REDIS_TIMEOUT','float',0.001,30],
            'redis.read_timeout'=>[0.5,'CMS_REDIS_READ_TIMEOUT','float',0.001,30],
            'redis.prefix'=>['cms:','CMS_REDIS_PREFIX','prefix'],
            'server.listen'=>[8787,'WEBMAN_LISTEN','listen'],
            'server.workers'=>[1,'WEBMAN_WORKERS','int',1,256],
            'server.max_package_size'=>[10485760,'CMS_MAX_PACKAGE_SIZE','int',1048576,1073741824],
            'server.stop_timeout'=>[30,'CMS_STOP_TIMEOUT','int',2,600],
            'server.process_manager'=>['standalone','CMS_PROCESS_MANAGER','manager'],
            // High-performance mode: separate push, generation and monitor processes.
            'performance.separate_task_processes'=>[false,'CMS_SEPARATE_TASK_PROCESSES','bool'],
            'app.timezone'=>['Asia/Shanghai','APP_TIMEZONE','timezone'],
            'app.debug'=>[false,'APP_DEBUG','bool'],
            'app.memory_limit'=>['256M','CMS_MEMORY_LIMIT','memory'],
            'logging.enable'=>[true,'LOG_ENABLE','bool'],
            'logging.print_to_console'=>[true,'LOG_PRINT_TO_CONSOLE','bool'],
            'logging.print_to_file'=>[true,'LOG_PRINT_TO_FILE','bool'],
            'logging.file'=>['logs/webman.log','LOG_FILE','relative'],
            'logging.max_files'=>[7,'LOG_MAX_FILES','int',1,3650],
            'logging.level'=>[200,'LOG_LEVEL','level'],
            'logging.sql.enable'=>[false,'SQL_LOG_ENABLE','bool'],
            'logging.sql.print_to_console'=>[false,'SQL_LOG_PRINT_TO_CONSOLE','bool'],
            'logging.sql.print_to_file'=>[true,'SQL_LOG_PRINT_TO_FILE','bool'],
            'logging.sql.file'=>['logs','SQL_LOG_FILE','relative'],
            'logging.sql.max_files'=>[7,'SQL_LOG_MAX_FILES','int',1,3650],
            'session.name'=>['PHPSID','CMS_SESSION_NAME','identifier'],
            'session.lifetime'=>[604800,'CMS_SESSION_LIFETIME','int',60,31536000],
            'session.cookie_lifetime'=>[31536000,'CMS_COOKIE_LIFETIME','int',0,31536000],
            'session.cookie_path'=>['/','CMS_COOKIE_PATH','url_path'],
            'session.domain'=>['','CMS_COOKIE_DOMAIN','string'],
            'session.http_only'=>[true,'CMS_COOKIE_HTTP_ONLY','true'],
            'session.secure'=>[false,'CMS_COOKIE_SECURE','bool'],
            'session.same_site'=>['','CMS_COOKIE_SAME_SITE','same_site'],
            'static.versioned_max_age'=>[31536000,'CMS_ASSET_MAX_AGE','int',0,31536000],
            'limits.install_sql_bytes'=>[52428800,'CMS_INSTALL_SQL_BYTES','int',1048576,536870912],
            'limits.install_chunk_bytes'=>[2097152,'CMS_INSTALL_CHUNK_BYTES','int',65536,8388608],
            'limits.upload_bytes'=>[10485760,'CMS_UPLOAD_BYTES','int',1024,1073741824],
            'limits.image_pixels'=>[40000000,'CMS_IMAGE_PIXELS','int',1000000,100000000],
            'limits.image_dimension'=>[4096,'CMS_IMAGE_DIMENSION','int',1,8192],
            'limits.language_pack_bytes'=>[2097152,'CMS_LANGUAGE_PACK_BYTES','int',1024,16777216],
            'limits.language_pack_entries'=>[5000,'CMS_LANGUAGE_PACK_ENTRIES','int',1,50000],
            'limits.sms_code_lifetime'=>[1800,'CMS_SMS_CODE_LIFETIME','int',60,86400],
        ];
    }
    public static function environment(string $key,mixed $value): mixed
    {
        if(in_array($key,['build.target','build.embedded_resources'],true)&&$value!==null&&$value!=='') {
            if(!is_string($value)||!str_starts_with(trim($value),'['))throw new \RuntimeException('构建列表环境变量必须是 JSON 数组：'.$key);
            try{$list=json_decode($value,true,32,JSON_THROW_ON_ERROR);}catch(\Throwable){throw new \RuntimeException('构建列表环境变量必须是 JSON 数组：'.$key);}
            if(!is_array($list)||!array_is_list($list))throw new \RuntimeException('构建列表环境变量必须是 JSON 数组：'.$key);
            $value=$list;
        }
        return self::coerce($key,$value);
    }
    public static function coerce(string $key, mixed $value): mixed
    {
        $field = self::fields()[$key] ?? throw new \RuntimeException('未知配置键：'.$key);
        $type = $field[2];
        if(str_starts_with($key,'build.')) {
            if($value===null || $value==='' || ($key==='build.target'&&$value===[]))$value=$field[0];
            if($type==='targets'||$type==='embedded') {
                if(is_string($value)&&str_starts_with(trim($value),'[')) {
                    try{$value=json_decode($value,true,32,JSON_THROW_ON_ERROR);}catch(\Throwable){throw new \RuntimeException('构建列表环境变量必须是 JSON 数组：'.$key);}
                } elseif($type==='targets'&&is_string($value))$value=[$value];
                if(!is_array($value)||!array_is_list($value))throw new \RuntimeException('构建参数必须是字符串列表：'.$key);
                foreach($value as$item) {
                    if(!is_string($item))throw new \RuntimeException('构建列表包含非字符串项：'.$key);
                    if($type==='embedded'&&preg_match('#^(?:public/)?(?:uploads|runtime)(?:/|$)#D',$item))throw new \RuntimeException('禁止打包上传或运行目录：'.$item.'；构建已停止');
                    $allowed=$type==='targets'?['linux-aarch64','linux-x86_64','darwin-aarch64','darwin-x86_64']:\app\cms\support\EmbeddedResources::OPTIONAL;
                    if(!in_array($item,$allowed,true))throw new \RuntimeException('不支持的构建项：'.$key.' = '.$item);
                }
                if(count(array_unique($value))!==count($value))throw new \RuntimeException('构建列表包含重复项：'.$key);
                return $value;
            }
        }
        $invalid = static fn() => new \RuntimeException('配置字段类型或范围无效：'.$key);
        if ($value === null && $field[0] === null) return null;
        if ($type === 'listen') {
            if (is_int($value) || (is_string($value) && preg_match('/^[1-9][0-9]{0,4}$/D', $value))) {
                if ((int)$value < 1 || (int)$value > 65535) throw $invalid();
                return (int)$value;
            }
            // Old deployments may bind loopback or IPv6. Preserve that address on upgrade.
            if (!is_string($value) || !preg_match('#^http://(?:\[[0-9a-fA-F:]+\]|[A-Za-z0-9_.-]+):([0-9]{1,5})$#D', $value, $match)
                || (int)$match[1] < 1 || (int)$match[1] > 65535) throw $invalid();
            return $value;
        }
        if ($type === 'int' || $type === 'level') {
            if (!is_int($value) && !(is_string($value) && preg_match('/^(?:0|[1-9][0-9]*)$/D', $value))) throw $invalid();
            $value = filter_var($value, FILTER_VALIDATE_INT);
            if ($value === false || ($type === 'int' && ($value < $field[3] || $value > $field[4])) || ($type === 'level' && !in_array($value,[100,200,250,300,400,500,550,600],true))) throw $invalid();
        } elseif ($type === 'float') {
            if ((!is_string($value) && !is_int($value) && !is_float($value)) || !is_numeric($value) || !is_finite((float)$value) || $value < $field[3] || $value > $field[4]) throw $invalid();
            $value = (float)$value;
        } elseif ($type === 'bool' || $type === 'true') {
            if (!is_bool($value)) {
                if (!is_string($value) || $value === '') throw $invalid();
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($value === null) throw $invalid();
            }
            if ($type === 'true' && !$value) throw $invalid();
        } else {
            if (!is_string($value) || str_contains($value,"\0") || strlen($value)>8192) throw $invalid();
            if ($type==='update_provider'&&!in_array($value,['gitee','github','custom'],true))throw $invalid();
            if ($type==='update_password'&&(strlen($value)>256||preg_match('/[\r\n]/',$value)))throw $invalid();
            if ($type==='update_url'&&$value!=='') {
                $parts=parse_url($value);
                if(strlen($value)>2048||!filter_var($value,FILTER_VALIDATE_URL)||($parts['scheme']??'')!=='https'||empty($parts['host'])||isset($parts['user'])||isset($parts['pass'])||isset($parts['fragment'])||isset($parts['port'])||preg_match('/[\x00-\x20\x7f\\\\]/',$value))throw $invalid();
            }
            if (in_array($type,['path','build_path'],true) && $value === '') throw $invalid();
            if ($type === 'relative' && ($value === '' || str_starts_with($value,'/') || str_contains($value,'\\') || preg_match('#(^|/)\.{1,2}(/|$)#',$value))) throw $invalid();
            if ($type === 'prefix' && !preg_match('/^[A-Za-z0-9:_-]{1,100}$/D',$value)) throw $invalid();
            if ($type === 'layout' && !in_array($value,['','legacy','unified-backend-v1'],true)) throw $invalid();
            if ($type === 'manager' && !in_array($value,['standalone','systemd'],true)) throw $invalid();
            if ($type === 'same_site' && !in_array($value,['','Lax','Strict','None'],true)) throw $invalid();
            if ($type === 'identifier' && !preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/D',$value)) throw $invalid();
            if ($type === 'binary_name' && (!preg_match('/^[A-Za-z][A-Za-z0-9._-]{0,63}$/D',$value) || in_array(strtolower($value),['release.json','runtime.lock.json','licenses','config.yaml','config','public','template','uploads','runtime','install'],true))) {
                throw new \RuntimeException('build.binary_name 必须以英文字母开头，仅含字母、数字、点、下划线或短横线，长度 1～64，且不能使用配置、资源目录或发布资料保留名称');
            }
            if ($type === 'memory' && !preg_match('/^[1-9][0-9]{0,5}[MG]$/D',$value)) throw $invalid();
            if ($type === 'url_path' && ($value !== '' && (!str_starts_with($value,'/') || str_contains($value,'..') || str_contains($value,'?') || str_contains($value,'#') || str_contains($value,'\\')))) throw $invalid();
            if ($type === 'url' && (!filter_var($value,FILTER_VALIDATE_URL) || !in_array(parse_url($value,PHP_URL_SCHEME),['http','https'],true) || parse_url($value,PHP_URL_USER)!==null)) throw $invalid();
            if ($type === 'timezone' && !in_array($value,\DateTimeZone::listIdentifiers(),true)) throw $invalid();
        }
        return $value;
    }
    public static function flatten(array $data, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $name=>$value) {
            if (!is_string($name) || !preg_match('/^[a-z][a-z0-9_]*$/D',$name) && !($prefix==='build.'&&$name==='build-runtime')) throw new \RuntimeException('YAML 配置键无效');
            $key = $prefix.$name;
            if ($key === 'paths') throw new \RuntimeException('paths 自定义目录配置已移除，请删除该区段；程序使用入口目录下的固定目录结构');
            // Only the retired key setting is accepted for upgrade compatibility.
            if ($key === 'security' && is_array($value) && (!$value || (array_keys($value) === ['key_file'] && is_string($value['key_file'])))) continue;
            if ($key === 'site' && is_array($value)) {
                foreach ($value as $retired=>$setting) {
                    if (!array_key_exists($retired, self::retiredSite()) || !is_string($setting)) throw new \RuntimeException('已废弃 site 区段包含未知字段或无效值');
                }
                continue;
            }
            if($key==='build.embedded_resources'&&is_string($value))throw new \RuntimeException('build.embedded_resources 必须是字符串列表');
            if (array_key_exists($key,self::fields())) { $result[$key]=self::coerce($key,$value); } elseif (is_array($value)) {
                if (substr_count($key,'.')>5) throw new \RuntimeException('YAML 配置嵌套过深');
                $known = false;
                foreach (self::fields() as $field=>$_) if (str_starts_with($field,$key.'.')) { $known=true; break; }
                if (!$known || ($value && array_is_list($value))) throw new \RuntimeException('未知配置区段：'.$key);
                $result += self::flatten($value,$key.'.');
            } else $result[$key] = self::coerce($key,$value);
        }
        return $result;
    }
    public static function expand(array $flat): array
    {
        $result=[];
        foreach($flat as $key=>$value) {
            $cursor=&$result;
            $parts=explode('.',$key);$last=array_pop($parts);
            foreach($parts as $part) { $cursor[$part]??=[]; $cursor=&$cursor[$part]; }
            $cursor[$last]=$value;unset($cursor);
        }
        return $result;
    }
    /** Accepted only to let existing deployments start; these values no longer control the site. */
    public static function retiredSite(): array
    {
        return ['base_path'=>'CMS_BASE_PATH', 'base_url'=>'CMS_BASE_URL', 'theme'=>'CMS_THEME', 'backup_path'=>'CMS_BACKUP_PATH'];
    }
}
