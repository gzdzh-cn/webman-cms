<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Gitee has prereleases, not GitHub drafts: prepare a preview, then explicitly finalize. */
final class GiteeReleasePublisher
{
    private \GuzzleHttp\Client $client;
    private string $token;
    public function __construct(?\GuzzleHttp\Client $client=null)
    {
        $this->token=(string)getenv('GITEE_TOKEN');
        if($this->token==='')throw new \RuntimeException('发布端需要 GITEE_TOKEN');
        $this->client=$client??new \GuzzleHttp\Client(['connect_timeout'=>10,'timeout'=>600,'allow_redirects'=>false,'headers'=>['User-Agent'=>'webman-cms-release','Accept'=>'application/json']]);
    }
    private function api(string $method,string $path,array $fields=[]): array
    {
        $options=$method==='GET'?['query'=>['access_token'=>$this->token]+$fields]:['form_params'=>['access_token'=>$this->token]+$fields];
        $response=$this->client->request($method,GiteeReleases::API.$path,$options);
        return json_decode((string)$response->getBody(),true,512,JSON_THROW_ON_ERROR);
    }
    public function publish(string $directory,string $notesFile): array
    {
        $version=\app\cms\admin\support\BackendVersion::version();
        $manifest=ReleaseManifest::validate(json_decode((string)file_get_contents($directory.'/'.ReleaseManifest::NAME),true,512,JSON_THROW_ON_ERROR),'v'.$version);
        $files=array_merge([['name'=>ReleaseManifest::NAME]],$manifest['packages']);
        // Validate all local assets before creating any remote state.
        foreach($files as $file){$path=AssetArchive::path($directory.'/'.$file['name']);if(!is_file($path)||isset($file['sha256'])&&(!hash_equals($file['sha256'],hash_file('sha256',$path))||filesize($path)!==$file['size']))throw new \RuntimeException('发布文件不完整或摘要不符');}
        $release=$this->api('POST','/releases',['tag_name'=>'v'.$version,'name'=>'webman-cms '.$version,'body'=>(string)file_get_contents($notesFile),'target_commitish'=>'v'.$version,'prerelease'=>'true']);
        $id=(string)($release['id']??'');if(!ctype_digit($id))throw new \RuntimeException('Gitee 未返回有效发行版身份');
        foreach($files as $file){
            $stream=fopen($directory.'/'.$file['name'],'rb');
            try{$this->client->post(GiteeReleases::API.'/releases/'.$id.'/attach_files',['multipart'=>[['name'=>'access_token','contents'=>$this->token],['name'=>'file','contents'=>$stream,'filename'=>$file['name']]]]);}
            finally{if(is_resource($stream))fclose($stream);}
        }
        return ['provider'=>'gitee','id'=>$id,'prerelease'=>true,'tag'=>$release['tag_name'],'message'=>'附件已上传为预览版；校验后执行 finalize 正式发布'];
    }
    private function bytes(string $url,int $limit,?string $sha=null): string
    {
        for($i=0;$i<6;$i++){
            ReleaseDownload::assertUrl($url,'gitee');
            $response=$this->client->get($url,['allow_redirects'=>false,'stream'=>true]);
            $body=$response->getBody();
            try{
                if(in_array($response->getStatusCode(),[301,302,303,307,308],true)){$url=ReleaseDownload::redirect($url,$response->getHeaderLine('Location'));continue;}
                if($response->getStatusCode()!==200)throw new \RuntimeException('Gitee 附件下载失败');
                $hash=hash_init('sha256');$size=0;$result='';
                while(!$body->eof()){$bytes=$body->read(1048576);$size+=strlen($bytes);if($size>$limit)throw new \RuntimeException('Gitee 附件超过声明大小');hash_update($hash,$bytes);if($sha===null)$result.=$bytes;}
                if($sha!==null&&($size!==$limit||!hash_equals($sha,hash_final($hash))))throw new \RuntimeException('Gitee 附件大小或 SHA-256 不符');
                return $result;
            }finally{$body->close();}
        }
        throw new \RuntimeException('Gitee 附件重定向过多');
    }
    public function finalize(string $id): array
    {
        if(!preg_match('/^[1-9][0-9]*$/D',$id))throw new \RuntimeException('需要 Gitee 预览发行版 ID');
        $release=$this->api('GET','/releases/'.$id);
        if(empty($release['prerelease']))throw new \RuntimeException('只允许显式发布已准备的 Gitee 预览版');
        $release['assets']=$this->api('GET','/releases/'.$id.'/attach_files',['per_page'=>100]);
        if(count($release['assets'])>=100)throw new \RuntimeException('Gitee 附件列表不完整');
        $manifest=ReleaseManifest::validate(json_decode($this->bytes(GiteeReleases::url($release,GiteeReleases::asset($release,ReleaseManifest::NAME)),8388608),true,512,JSON_THROW_ON_ERROR),$release['tag_name']);
        if($manifest['version']!==\app\cms\admin\support\BackendVersion::version())throw new \RuntimeException('发布程序与标签版本不一致');
        foreach($manifest['packages']as$package){$asset=GiteeReleases::asset($release,$package['name']);if(($asset['size']??null)!==$package['size'])throw new \RuntimeException('Gitee 附件大小与清单不符');$this->bytes(GiteeReleases::url($release,$asset),$package['size'],$package['sha256']);}
        $this->api('PATCH','/releases/'.$id,['tag_name'=>$release['tag_name'],'name'=>$release['name'],'body'=>$release['body'],'prerelease'=>'false']);
        return ['provider'=>'gitee','id'=>$id,'published'=>true,'version'=>$manifest['version']];
    }
}
