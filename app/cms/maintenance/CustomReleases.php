<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Password-protected app_update source. Credentials are never part of release cache data. */
final class CustomReleases
{
    public function __construct(private array $config,private ?\GuzzleHttp\Client $client=null)
    {
        $this->client??=new \GuzzleHttp\Client(['connect_timeout'=>5,'timeout'=>30,'http_errors'=>false,'allow_redirects'=>false]);
    }
    public function latest(): ?array
    {
        $url=$this->config['custom_url'];ReleaseDownload::assertCustomUrl($url,$url);
        if($this->config['custom_password']==='')throw new \RuntimeException('自定义源尚未设置密码');
        $response=$this->client->get($url,['headers'=>['X-App-Update-Password'=>$this->config['custom_password'],'Accept'=>'application/json','User-Agent'=>'webman-cms-updater'],'progress'=>static function($total,$received):void{if($total>8388608||$received>8388608)throw new \RuntimeException('自定义清单响应过大');}]);
        try{
            $status=$response->getStatusCode();
            if(in_array($status,[401,403],true))throw new \RuntimeException('自定义升级源密码错误或访问被拒绝');
            if($status===429)throw new \RuntimeException('自定义升级源请求过于频繁，请稍后重试');
            if($status!==200)throw new \RuntimeException('自定义升级源响应异常（HTTP '.$status.'）');
            $bytes=$response->getBody()->read(8388609);if(strlen($bytes)>8388608)throw new \RuntimeException('自定义清单响应过大');
            $payload=json_decode($bytes,true,512,JSON_THROW_ON_ERROR);
            if(($payload['code']??null)!==1000)throw new \RuntimeException('自定义升级源未返回有效版本信息');
            $release=$payload['data']??null;if($release===null)return null;
            if(!is_array($release)||!preg_match('/^[1-9][0-9]*$/D',(string)($release['id']??'')))throw new \RuntimeException('自定义发布身份无效');
            $manifest=ReleaseManifest::validate($release['manifest']??[],(string)($release['tag']??''));
            if(!is_array($release['assets']??null)||count($release['assets'])>16)throw new \RuntimeException('自定义资产列表无效');
            foreach($release['assets'] as &$asset){
                if(!is_array($asset))throw new \RuntimeException('自定义资产无效');
                $assetUrl=(string)($asset['url']??'');
                if(str_starts_with($assetUrl,'/')&&!str_starts_with($assetUrl,'//'))$assetUrl='https://'.parse_url($url,PHP_URL_HOST).$assetUrl;
                ReleaseDownload::assertCustomUrl($assetUrl,$url);$asset['url']=$assetUrl;
            }unset($asset);
            foreach($manifest['packages'] as $package){$assets=array_values(array_filter($release['assets']??[],static fn($a)=>is_array($a)&&($a['name']??'')===$package['name']));if(count($assets)!==1||($assets[0]['size']??null)!==$package['size']||($assets[0]['sha256']??'')!==$package['sha256'])throw new \RuntimeException('自定义升级资产与清单不一致');ReleaseDownload::assertCustomUrl((string)($assets[0]['url']??''),$url);}
            return ['provider'=>'custom','id'=>(string)$release['id'],'tag'=>'v'.$manifest['version'],'version'=>$manifest['version'],'published_at'=>(string)($release['published_at']??''),'notes'=>mb_substr((string)($release['notes']??''),0,50000),'manifest'=>$manifest,'assets'=>$release['assets'],'source_url'=>$url];
        }finally{$response->getBody()->close();}
    }
}
