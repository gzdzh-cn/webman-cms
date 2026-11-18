<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Public Gitee releases. Publishing credentials never enter a running site. */
final class GiteeReleases
{
    public const REPOSITORY = 'gzdzh/webman-cms';
    public const API = 'https://gitee.com/api/v5/repos/'.self::REPOSITORY;
    private \GuzzleHttp\Client $client;
    private float $deadline=0;
    public function __construct(?\GuzzleHttp\Client $client=null)
    {
        $this->client=$client??new \GuzzleHttp\Client(['connect_timeout'=>5,'timeout'=>30,'allow_redirects'=>false,'http_errors'=>false,'headers'=>['User-Agent'=>'webman-cms-updater','Accept'=>'application/json']]);
    }
    private function json(string $url): array
    {
        for($i=0;$i<6;$i++){
            ReleaseDownload::assertUrl($url,'gitee');
            $remaining=$this->deadline-microtime(true);
            if($remaining<=0)throw new \RuntimeException('Gitee 检查更新超时');
            $response=$this->client->get($url,['allow_redirects'=>false,'timeout'=>min(30,$remaining),'progress'=>static function($total,$received):void{if($total>8388608||$received>8388608)throw new \RuntimeException('Gitee 清单响应过大');}]);
            try {
                $status=$response->getStatusCode();
                if(in_array($status,[301,302,303,307,308],true)){$url=ReleaseDownload::redirect($url,$response->getHeaderLine('Location'));continue;}
                if($status!==200)throw new \RuntimeException('Gitee 响应异常（HTTP '.$status.'）');
                $body=$response->getBody()->read(8388609);
                if(strlen($body)>8388608)throw new \RuntimeException('Gitee 清单响应过大');
                $result=json_decode($body,true,512,JSON_THROW_ON_ERROR);
                if(!is_array($result))throw new \RuntimeException('Gitee 返回格式无效');
                return $result;
            } finally {$response->getBody()->close();}
        }
        throw new \RuntimeException('Gitee 重定向过多');
    }
    public function latest(): ?array
    {
        $this->deadline=microtime(true)+90;
        $stable=[];
        // Bound pagination and refuse incomplete discovery instead of silently missing a release.
        for($page=1;$page<=10;$page++){
            $batch=$this->json(self::API.'/releases?per_page=100&page='.$page);
            if(!array_is_list($batch))throw new \RuntimeException('Gitee 发行版列表无效');
            foreach($batch as $release){
                if(!is_array($release)||!empty($release['draft'])||!empty($release['prerelease']))continue;
                try{$release['cms_version']=ReleaseManifest::version((string)($release['tag_name']??''));}catch(\Throwable){continue;}
                $stable[]=$release;
            }
            if(count($batch)<100)break;
            if($page===10)throw new \RuntimeException('Gitee 发行版数量超出检查范围');
        }
        usort($stable,static fn($a,$b)=>version_compare($b['cms_version'],$a['cms_version']));
        if(!$stable)return null;
        $release=$stable[0];
        self::numericId($release['id']??null);
        $release['assets']=$this->json(self::API.'/releases/'.$release['id'].'/attach_files?per_page=100');
        if(!array_is_list($release['assets'])||count($release['assets'])>=100)throw new \RuntimeException('Gitee 附件列表无效或不完整');
        $manifest=ReleaseManifest::validate($this->json(self::url($release,self::asset($release,ReleaseManifest::NAME))),$release['tag_name']);
        foreach($manifest['packages']as$package){$asset=self::asset($release,$package['name']);if(($asset['size']??null)!==$package['size'])throw new \RuntimeException('Gitee 附件大小与清单不符');}
        return ['provider'=>'gitee','id'=>(string)$release['id'],'tag'=>$release['tag_name'],'version'=>$release['cms_version'],'published_at'=>$release['created_at']??'','notes'=>mb_substr((string)($release['body']??''),0,50000),'manifest'=>$manifest,'assets'=>$release['assets']];
    }
    public static function asset(array $release,string $name): array
    {
        $found=array_values(array_filter($release['assets']??[],static fn($a)=>is_array($a)&&($a['name']??'')===$name));
        if(count($found)!==1)throw new \RuntimeException('Gitee 正式发布缺少唯一附件：'.$name);
        self::numericId($found[0]['id']??null);
        return $found[0];
    }
    private static function numericId(mixed $id): void {if(!is_int($id)&&!is_string($id)||!preg_match('/^[1-9][0-9]*$/D',(string)$id))throw new \RuntimeException('Gitee 发布或附件身份无效');}
    public static function url(array $release,array $asset): string
    {
        self::numericId($release['id']??null);self::numericId($asset['id']??null);
        ReleaseManifest::version($release['tag_name']??$release['tag']);
        return self::API.'/releases/'.$release['id'].'/attach_files/'.$asset['id'].'/download';
    }
}
