<?php
declare(strict_types=1);
namespace app\cms\extension;

use AlibabaCloud\Oss\V2\{Client, Config};
use AlibabaCloud\Oss\V2\Credentials\StaticCredentialsProvider;
use AlibabaCloud\Oss\V2\Models\PutObjectRequest;

/** Fixed HTTPS transport. A handler may be injected by local tests, never by configuration. */
final class OssTransport
{
    public function __construct(private ?\Closure $handler = null) {}

    public function put(array $config, string $path, string $object, string $mime): array
    {
        // Validate even when this transport is used directly.
        $plugin=new AliyunOss();$config=$plugin->configure($config,[]);$plugin->assertReady($config);
        if (!preg_match('#^uploads/(?:img|files)/\d{8}/[a-f0-9]{32}\.[a-z0-9]+$#D',$object)) throw new \InvalidArgumentException('OSS 对象路径无效');
        $region=substr($config['endpoint'],4,-strlen('.aliyuncs.com'));
        $options=['request_options'=>['proxy'=>'','allow_redirects'=>false,'verify'=>true,'debug'=>false]];
        if ($this->handler!==null) $options['handler']=$this->handler;
        $client=new Client(new Config(region:$region,endpoint:'https://'.$config['endpoint'],signatureVersion:'v4',
            credentialsProvider:new StaticCredentialsProvider($config['key_id'],$config['key_secret']),
            disableSSL:false,insecureSkipVerify:false,connectTimeout:3.0,readwriteTimeout:30.0,
            proxyHost:'',useInternalEndpoint:false,useCname:false,retryMaxAttempts:1),$options);
        $request=new PutObjectRequest(bucket:$config['bucket'],key:$object,
            contentMd5:base64_encode(hash_file('md5',$path,true)),contentType:$mime,forbidOverwrite:true);
        // SDK 0.5.0 declares a string constructor argument for an int property.
        // Set the documented typed property directly without changing vendor code.
        $request->contentLength=filesize($path);
        $result=$client->putObjectFromFile($request,$path,['request_options'=>['timeout'=>30.0,'connect_timeout'=>3.0,'read_timeout'=>30.0]]);
        return ['status'=>$result->statusCode];
    }
}
