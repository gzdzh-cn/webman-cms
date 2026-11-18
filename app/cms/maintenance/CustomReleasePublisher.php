<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Separate administrator credentials are used only on the publishing machine. */
final class CustomReleasePublisher
{
    private \GuzzleHttp\Client $client;
    private string $base;
    public function __construct()
    {
        $this->base=rtrim((string)getenv('CMS_UPDATE_PUBLISH_URL'),'/');
        ReleaseDownload::assertCustomUrl($this->base,$this->base);
        $parts=parse_url($this->base);
        if(isset($parts['query'])||!in_array($parts['path']??'',['','/'],true))throw new \RuntimeException('发布地址必须为 HTTPS 站点根地址');
        $token=(string)getenv('CMS_UPDATE_PUBLISH_TOKEN');
        if($token===''||preg_match('/[\x00-\x20\x7f]/',$token))throw new \RuntimeException('发布端需要独立管理员 CMS_UPDATE_PUBLISH_TOKEN');
        $this->client=new \GuzzleHttp\Client(['connect_timeout'=>10,'timeout'=>600,'allow_redirects'=>false,'http_errors'=>false,'headers'=>['Authorization'=>$token,'Accept'=>'application/json','User-Agent'=>'webman-cms-release']]);
    }
    private function request(string $method,string $path,array $options=[]): mixed
    {
        $response=$this->client->request($method,$this->base.'/admin/app_update/'.$path,$options);
        try{
            if($response->getStatusCode()!==200)throw new \RuntimeException('自定义发布接口失败（HTTP '.$response->getStatusCode().'）');
            $bytes=$response->getBody()->read(16777217);if(strlen($bytes)>16777216)throw new \RuntimeException('发布接口响应过大');
            $data=json_decode($bytes,true,512,JSON_THROW_ON_ERROR);
            if(($data['code']??null)!==1000)throw new \RuntimeException('自定义发布接口拒绝操作，请检查管理员权限、草稿及资产状态');
            return $data['data']??null;
        }finally{$response->getBody()->close();}
    }
    public function publish(string $directory,string $notesFile): array
    {
        $appID=(string)getenv('CMS_UPDATE_PUBLISH_APP_ID');
        if(!preg_match('/^[1-9][0-9]*$/D',$appID))throw new \RuntimeException('发布端需要 CMS_UPDATE_PUBLISH_APP_ID');
        $manifest=ReleaseManifest::validate(json_decode((string)file_get_contents($directory.'/'.ReleaseManifest::NAME),true,512,JSON_THROW_ON_ERROR),'v'.\app\cms\admin\support\BackendVersion::version());
        $profiles=$this->request('GET','version/apps');
        $profile=array_values(array_filter((array)$profiles,static fn($p)=>is_array($p)&&($p['platform']??'')==='other'&&(string)($p['appId']??'')===$appID));
        if(count($profile)!==1||empty($profile[0]['passwordConfigured']))throw new \RuntimeException('请先登记 webman-cms／其他平台并设置访问密码');
        $parts=array_map('intval',explode('.',$manifest['version']));
        if(max($parts)>999)throw new \RuntimeException('自定义发布版本分段不能超过999');
        $receipt=$directory.'/custom-draft.json';$draft=null;
        if(is_file($receipt)){
            $saved=json_decode((string)file_get_contents($receipt),true,512,JSON_THROW_ON_ERROR);
            if(($saved['origin']??'')!==$this->base||($saved['app_id']??'')!==$appID||($saved['version']??'')!==$manifest['version'])throw new \RuntimeException('草稿回执与当前发布身份不符');
            $draft=$this->request('GET','version/info',['query'=>['id'=>$saved['id']]]);
            if(($draft['status']??'')==='published')throw new \RuntimeException('该自定义版本已经发布');
        }
        $data=['id'=>(string)($draft['id']??''),'packageName'=>$profile[0]['packageName'],'platform'=>'other','versionName'=>$manifest['version'],'versionCode'=>$parts[0]*1000000+$parts[1]*1000+$parts[2],'updateTitle'=>'webman-cms '.$manifest['version'],'updateContent'=>(string)file_get_contents($notesFile),'releaseManifest'=>json_encode($manifest,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)];
        $id=(string)$this->request('POST','version/save',['json'=>$data]);
        if(!preg_match('/^[1-9][0-9]*$/D',$id))throw new \RuntimeException('自定义草稿身份无效');
        $bytes=json_encode(['id'=>$id,'origin'=>$this->base,'app_id'=>$appID,'version'=>$manifest['version']],JSON_THROW_ON_ERROR)."\n";
        if(file_put_contents($receipt,$bytes)!==strlen($bytes)||!chmod($receipt,0600))throw new \RuntimeException('无法保存草稿回执');
        $existing=$draft['assets']??[];
        foreach($manifest['packages'] as $package){
            $path=AssetArchive::path($directory.'/'.$package['name']);
            if(filesize($path)!==$package['size']||!hash_equals($package['sha256'],hash_file('sha256',$path)))throw new \RuntimeException('上传前升级包已变化，草稿已保留');
            $matches=array_values(array_filter($existing,static fn($a)=>($a['name']??'')===$package['name']));
            if($matches){if(count($matches)!==1||(int)$matches[0]['size']!==$package['size']||$matches[0]['sha256']!==$package['sha256'])throw new \RuntimeException('草稿已有同名但摘要不同的资产');continue;}
            $stream=fopen($path,'rb');if(!$stream)throw new \RuntimeException('无法读取升级包');
            try{$this->request('POST','version/upload',['multipart'=>[['name'=>'id','contents'=>$id],['name'=>'file','contents'=>$stream,'filename'=>$package['name'],'headers'=>['Content-Type'=>'application/octet-stream']]]]);}finally{if(is_resource($stream))fclose($stream);}
        }
        $this->verify($this->request('GET','version/info',['query'=>['id'=>$id]]),$manifest);
        return ['id'=>$id,'draft'=>true,'version'=>$manifest['version']];
    }
    private function verify(array $draft,array $manifest): void
    {
        if(($draft['platform']??'')!=='other'||($draft['version_name']??'')!==$manifest['version']||json_decode((string)($draft['release_manifest']??''),true)!==$manifest)throw new \RuntimeException('草稿版本或清单不一致');
        $assets=$draft['assets']??[];if(count($assets)!==count($manifest['packages']))throw new \RuntimeException('自定义草稿资产数量不一致');
        foreach($manifest['packages'] as $package){$matches=array_values(array_filter($assets,static fn($a)=>($a['name']??'')===$package['name']));if(count($matches)!==1||(int)$matches[0]['size']!==$package['size']||$matches[0]['sha256']!==$package['sha256'])throw new \RuntimeException('自定义草稿资产大小或摘要不一致');}
    }
    public function finalize(string $id): array
    {
        if(!ctype_digit($id))throw new \RuntimeException('草稿 ID 无效');
        $draft=$this->request('GET','version/info',['query'=>['id'=>$id]]);
        if(!in_array($draft['status']??'',['draft','uploaded','withdrawn'],true))throw new \RuntimeException('只能显式发布草稿');
        $manifest=ReleaseManifest::validate(json_decode((string)($draft['release_manifest']??''),true,512,JSON_THROW_ON_ERROR),'v'.\app\cms\admin\support\BackendVersion::version());
        $this->verify($draft,$manifest);
        $this->request('POST','version/publish',['json'=>['id'=>$id]]);
        return ['id'=>$id,'published'=>true,'version'=>$manifest['version']];
    }
}
