<?php
declare(strict_types=1);
namespace app\cms\maintenance;
use app\cms\Paths;

/** Source bundles contain locked dependencies; standard binaries are built separately. */
final class ReleaseBuilder
{
    public function pack(string $destination, array $binaryDirectories = [], bool $source = true): array
    {
        ini_set('memory_limit','768M');
        if (\Phar::running(false)) throw new \RuntimeException('发布打包只能在源码环境执行');
        AssetArchive::path($destination);
        if (file_exists($destination)) throw new \RuntimeException('发布输出目录已存在');
        if (!is_dir(dirname($destination))) throw new \RuntimeException('发布输出父目录不存在');
        $version=\app\cms\admin\support\BackendVersion::version();ReleaseManifest::version($version);
        $inspection=(new AssetMaintenance())->inspect(true);
        if (!$inspection['ok']) throw new \RuntimeException('资源校验失败，拒绝发布');
        $installed=Paths::code('vendor/composer/installed.php');
        if (!is_file($installed) || !is_file(Paths::code('composer.lock'))) throw new \RuntimeException('源码包需要完整锁定依赖');
        $lock=json_decode((string)file_get_contents(Paths::code('composer.lock')),true,512,JSON_THROW_ON_ERROR);
        $packages=\Composer\InstalledVersions::getInstalledPackages();
        foreach ($lock['packages'] as $dependency) if (!in_array($dependency['name'],$packages,true) || \Composer\InstalledVersions::getReference($dependency['name'])!==($dependency['source']['reference']??$dependency['dist']['reference']??null)) throw new \RuntimeException('vendor 与 composer.lock 不一致：'.$dependency['name']);
        mkdir($destination,0700);
        $result=[];
        if ($source) {
            $files=(new BinaryBuilder())->files([Paths::PUBLIC_ASSETS]);
            foreach (['asset-build-inputs.tar.gz','source-package-assets.json.gz'] as $name) $files['app/cms/resources/'.$name]=Paths::resources($name);
            $result[]=$this->archive($destination,'webman-cms-'.$version.'-source.tar.gz',$files,['kind'=>'source']);
        }
        foreach ($binaryDirectories as $directory) {
            AssetArchive::path($directory);
            $build=json_decode((string)file_get_contents($directory.'/release.json'),true,512,JSON_THROW_ON_ERROR);
            if (($build['cms_version']??'')!==$version || array_diff($build['embedded_resources']??[],[Paths::PUBLIC_ASSETS])) throw new \RuntimeException('二进制版本不符或内嵌了站点专用资源');
            $binary=$directory.'/'.$build['binary_name'];
            if (!is_file($binary) || !hash_equals($build['binary_sha256'],hash_file('sha256',$binary))) throw new \RuntimeException('二进制摘要不符');
            $files=['program'=>$binary];
            if (!$build['embed_admin_assets']) foreach (json_decode((string)file_get_contents(Paths::resources('asset-manifest.json')),true)['files'] as $asset) if (str_starts_with($asset['target'],Paths::PUBLIC_ASSETS.'/')) $files[$asset['target']]=Paths::logical($asset['target']);
            $result[]=$this->archive($destination,'webman-cms-'.$version.'-'.$build['platform'].'-'.($build['embed_admin_assets']?'embedded':'external').'.tar.gz',$files,['kind'=>'binary','platform'=>$build['platform'],'embed_admin_assets'=>$build['embed_admin_assets'],'build_id'=>$build['build_id']]);
        }
        $manifest=['format'=>1,'repository'=>ReleaseManifest::REPOSITORY,'version'=>$version,'min_version'=>'1.0.7','max_version'=>$version,'min_php'=>'8.2.0','migration'=>'backward-compatible-additive','packages'=>$result];
        ReleaseManifest::validate($manifest,'v'.$version);
        file_put_contents($destination.'/'.ReleaseManifest::NAME,json_encode($manifest,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
        return ['version'=>$version,'directory'=>$destination,'packages'=>array_column($result,'name')];
    }
    private function archive(string $directory,string $name,array $files,array $meta): array
    {
        $records=[];
        foreach ($files as $path=>$file) { ReleaseManifest::managed($path,$meta['kind']); AssetArchive::path($file); $records[$path]=['size'=>filesize($file),'sha256'=>hash_file('sha256',$file),'mode'=>$path==='program'||$path==='webman'?0755:0644]; }
        ksort($records);AssetArchive::write($directory.'/'.$name,$files,268435456);
        return $meta+['name'=>$name,'size'=>filesize($directory.'/'.$name),'sha256'=>hash_file('sha256',$directory.'/'.$name),'files'=>$records];
    }
    public function publish(string $directory,string $notesFile,string $provider='gitee'): array
    {
        if($provider==='gitee')return (new GiteeReleasePublisher())->publish($directory,$notesFile);
        if($provider!=='github')throw new \InvalidArgumentException('发布中心必须为 gitee 或 github');
        $token=getenv('GITHUB_TOKEN');if (!$token) throw new \RuntimeException('发布端需要 GITHUB_TOKEN');
        $manifest=ReleaseManifest::validate(json_decode((string)file_get_contents($directory.'/'.ReleaseManifest::NAME),true,512,JSON_THROW_ON_ERROR),'v'.\app\cms\admin\support\BackendVersion::version());
        $client=new \GuzzleHttp\Client(['connect_timeout'=>10,'timeout'=>600,'allow_redirects'=>false,'headers'=>['Authorization'=>'Bearer '.$token,'User-Agent'=>'webman-cms-release','Accept'=>'application/vnd.github+json']]);
        $base='https://api.github.com/repos/'.ReleaseManifest::REPOSITORY;
        $response=$client->post($base.'/releases',['json'=>['tag_name'=>'v'.$manifest['version'],'name'=>'webman-cms '.$manifest['version'],'body'=>(string)file_get_contents($notesFile),'draft'=>true,'prerelease'=>false]]);
        $release=json_decode((string)$response->getBody(),true,512,JSON_THROW_ON_ERROR);
        foreach (array_merge([['name'=>ReleaseManifest::NAME]],$manifest['packages']) as $package) {
            $path=AssetArchive::path($directory.'/'.$package['name']);
            if (isset($package['sha256']) && (!hash_equals($package['sha256'],(string)hash_file('sha256',$path)) || filesize($path)!==$package['size'])) throw new \RuntimeException('发布包在上传前发生变化；草稿已保留');
            $stream=fopen($path,'rb');try{$client->post('https://uploads.github.com/repos/'.ReleaseManifest::REPOSITORY.'/releases/'.$release['id'].'/assets?name='.rawurlencode($package['name']),['headers'=>['Content-Type'=>'application/octet-stream'],'body'=>$stream]);}finally{fclose($stream);}
        }
        return ['id'=>$release['id'],'draft'=>true,'tag'=>$release['tag_name']];
    }
    public function finalize(string $id,string $provider='gitee'): array
    {
        if($provider==='gitee')return (new GiteeReleasePublisher())->finalize($id);
        if($provider!=='github')throw new \InvalidArgumentException('发布中心必须为 gitee 或 github');
        if (!ctype_digit($id) || !getenv('GITHUB_TOKEN')) throw new \RuntimeException('需要草稿 ID 和发布端 GITHUB_TOKEN');
        $client=new \GuzzleHttp\Client(['timeout'=>30,'allow_redirects'=>false,'headers'=>['Authorization'=>'Bearer '.getenv('GITHUB_TOKEN'),'User-Agent'=>'webman-cms-release','Accept'=>'application/vnd.github+json']]);
        $url='https://api.github.com/repos/'.ReleaseManifest::REPOSITORY.'/releases/'.$id;
        $release=json_decode((string)$client->get($url)->getBody(),true,512,JSON_THROW_ON_ERROR);
        if (empty($release['draft']) || !empty($release['prerelease'])) throw new \RuntimeException('只允许显式发布正式版草稿');
        $asset=GithubReleases::asset($release,ReleaseManifest::NAME);
        $response=$client->get($asset['url'],['headers'=>['Accept'=>'application/octet-stream'],'allow_redirects'=>['protocols'=>['https'],'max'=>3]]);
        $manifest=ReleaseManifest::validate(json_decode((string)$response->getBody(),true,512,JSON_THROW_ON_ERROR),$release['tag_name']);
        if ($manifest['version']!==\app\cms\admin\support\BackendVersion::version()) throw new \RuntimeException('发布程序与标签版本不一致');
        foreach ($manifest['packages'] as $package) {
            $packageAsset=GithubReleases::asset($release,$package['name']);
            if ($packageAsset['size']!==$package['size']) throw new \RuntimeException('草稿资产不完整');
            $download=$client->get($packageAsset['url'],['headers'=>['Accept'=>'application/octet-stream'],'stream'=>true,'timeout'=>600,'allow_redirects'=>['protocols'=>['https'],'max'=>3]]);
            $body=$download->getBody();$hash=hash_init('sha256');$size=0;
            try {
                while (!$body->eof()) {
                    $bytes=$body->read(1048576);$size+=strlen($bytes);
                    if ($size>$package['size']) throw new \RuntimeException('草稿资产大小与清单不符');
                    hash_update($hash,$bytes);
                }
            } finally { $body->close(); }
            if ($size!==$package['size'] || !hash_equals($package['sha256'],hash_final($hash))) throw new \RuntimeException('草稿资产 SHA-256 与清单不符');
        }
        $client->patch($url,['json'=>['draft'=>false]]);
        return ['id'=>$id,'published'=>true,'version'=>$manifest['version']];
    }
}
