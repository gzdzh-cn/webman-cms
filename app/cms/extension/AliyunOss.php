<?php
declare(strict_types=1);
namespace app\cms\extension;

use InvalidArgumentException;

/** Adapted from 3071-20261008 AliyunOss. No original PHP or plugin loader is used. */
final class AliyunOss implements Extension, AttachmentStorage
{
    public function manifest(): array
    {
        return ['code'=>'AliyunOss', 'name'=>'阿里云 OSS 对象存储', 'version'=>'1.0.0',
            'source'=>'EyouCMS AliyunOss / 3071-20261008', 'capabilities'=>['attachment_storage'],
            'description'=>'将后台业务图片、文档和音视频上传至阿里云 OSS。',
            'dependencies'=>['alibabacloud/oss-v2','curl','simplexml','json','openssl'],
            'fields'=>['key_id','key_secret','bucket','endpoint','domain','protocol','keep_local'],
            'configuration'=>[
                'key_id'=>['type'=>'secret','label'=>'AccessKey ID','required_on_enable'=>true],
                'key_secret'=>['type'=>'secret','label'=>'AccessKey Secret','required_on_enable'=>true],
                'bucket'=>['type'=>'string','label'=>'Bucket','required_on_enable'=>true],
                'endpoint'=>['type'=>'public_oss_endpoint','label'=>'公共 Endpoint','required_on_enable'=>true],
                'domain'=>['type'=>'hostname','label'=>'访问域名','required_on_enable'=>false],
                'protocol'=>['type'=>'enum','label'=>'访问协议','values'=>['https','http','relative'],'default'=>'https'],
                'keep_local'=>['type'=>'boolean','label'=>'保留本地副本','default'=>true],
            ]];
    }

    public function defaults(): array
    {
        return ['key_id'=>'', 'key_secret'=>'', 'bucket'=>'', 'endpoint'=>'', 'domain'=>'', 'protocol'=>'https', 'keep_local'=>true];
    }

    public function configure(array $input, array $previous): array
    {
        $allowed=array_merge(array_keys($this->defaults()),['clear_key_id','clear_key_secret']);
        if (array_diff(array_keys($input),$allowed)) throw new InvalidArgumentException('插件配置包含未知字段');
        $config=array_replace($this->defaults(),array_intersect_key($previous,$this->defaults()));
        foreach (['key_id','key_secret','bucket','endpoint','domain','protocol'] as $key) {
            if (!array_key_exists($key,$input)) continue;
            if (!is_string($input[$key])) throw new InvalidArgumentException('插件配置格式错误');
            $value=trim($input[$key]);
            if (strlen($value)>512 || preg_match('/[\x00-\x20\x7f]/',$value)) throw new InvalidArgumentException('插件配置包含无效字符');
            if (in_array($key,['key_id','key_secret'],true) && $value==='') continue;
            $config[$key]=$value;
        }
        foreach (['key_id','key_secret'] as $key) {
            if (array_key_exists('clear_'.$key,$input) && self::boolean($input['clear_'.$key])) {
                if (($input[$key]??'')!=='') throw new InvalidArgumentException('清除凭据时请保持输入为空');
                $config[$key]='';
            }
        }
        if (array_key_exists('keep_local',$input)) $config['keep_local']=self::boolean($input['keep_local']);
        if (!in_array($config['protocol'],['https','http','relative'],true)) throw new InvalidArgumentException('访问协议无效');
        if ($config['bucket']!=='' && !preg_match('/^[a-z0-9][a-z0-9-]{1,61}[a-z0-9]$/D',$config['bucket'])) throw new InvalidArgumentException('Bucket 名称无效');
        if ($config['endpoint']!=='' && !preg_match('/^oss-([a-z0-9]+(?:-[a-z0-9]+)*)\.aliyuncs\.com$/D',$config['endpoint'])) throw new InvalidArgumentException('仅支持阿里云公共 OSS Endpoint，例如 oss-cn-guangzhou.aliyuncs.com');
        if (str_contains($config['endpoint'],'-internal.') || str_contains($config['endpoint'],'-internal-')) throw new InvalidArgumentException('不支持内网 Endpoint');
        if ($config['domain']!=='' && (strlen($config['domain'])>253 || !preg_match('/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/D',$config['domain']))) throw new InvalidArgumentException('访问域名只能填写域名，不能包含协议、端口或路径');
        foreach (['key_id','key_secret'] as $key) if ($config[$key]!=='' && !preg_match('/^[a-zA-Z0-9+\/_=-]{8,256}$/D',$config[$key])) throw new InvalidArgumentException('OSS 凭据格式无效');
        return $config;
    }

    public static function boolean(mixed $value): bool
    {
        if (in_array($value,[true,1,'1'],true)) return true;
        if (in_array($value,[false,0,'0'],true)) return false;
        throw new InvalidArgumentException('开关值无效');
    }

    public function publicConfig(array $config): array
    {
        $config=array_replace($this->defaults(),$config);
        $result=array_intersect_key($config,array_flip(['bucket','endpoint','domain','protocol','keep_local']));
        foreach (['key_id','key_secret'] as $key) $result[$key.'_configured']=$config[$key]!=='';
        return $result;
    }

    public function assertReady(array $config): void
    {
        $config=$this->configure($config,[]);
        foreach (['key_id','key_secret','bucket','endpoint'] as $key) if ($config[$key]==='') throw new InvalidArgumentException('请先完整配置 OSS 凭据、Bucket 和 Endpoint');
        foreach (['curl','simplexml','json','openssl'] as $extension) if (!extension_loaded($extension)) throw new InvalidArgumentException('运行环境缺少 OSS 所需扩展：'.$extension);
        if (!class_exists(\AlibabaCloud\Oss\V2\Client::class)) throw new InvalidArgumentException('运行环境缺少 OSS SDK，请发布配套程序');
    }

    public function disable(array $config): void {}
    public function remove(array $config): void {}

    public function upload(array $config, string $path, string $object, string $mime, ?\Closure $transport = null): array
    {
        $config=$this->configure($config,[]);
        if (!preg_match('#^uploads/(?:img|files)/\d{8}/[a-f0-9]{32}\.[a-z0-9]+$#D',$object)) throw new InvalidArgumentException('OSS 对象路径无效');
        foreach (['key_id','key_secret','bucket','endpoint'] as $key) if ($config[$key]==='') throw new InvalidArgumentException('OSS 配置不完整');
        $send=$transport ?? static fn($c,$p,$o,$m)=>(new OssTransport())->put($c,$p,$o,$m);
        // No raw provider exceptions can escape: SDK diagnostics may contain signed URLs.
        try { $result=$send($config,$path,$object,$mime); }
        catch (\Throwable) { throw new \RuntimeException('OSS 上传失败或结果未知，本地文件已保留，请核对后重试'); }
        if (!is_array($result) || ($result['status']??0)!==200) throw new \RuntimeException('OSS 未确认上传成功，本地文件已保留，请核对后重试');
        $host=$config['domain']!==''?$config['domain']:$config['bucket'].'.'.$config['endpoint'];
        $protocol=match ($config['protocol']) {'http'=>'http://','relative'=>'//',default=>'https://'};
        return ['url'=>$protocol.$host.'/'.$object,'metadata'=>['provider'=>'AliyunOss','object'=>$object,'bucket'=>$config['bucket'],'endpoint'=>$config['endpoint']]];
    }
}
