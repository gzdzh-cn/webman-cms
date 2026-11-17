<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\extension\{AttachmentStorage, Registry};
use app\cms\Paths;
use app\cms\UploadPaths;
use app\cms\admin\model\Upload;

/** Publish only validated, fully processed business attachments, then commit their record. */
final class AttachmentStorageService
{
    private PluginMarketService $market;

    public function __construct(?PluginMarketService $market = null, private ?\Closure $transport = null, private ?\Closure $persist = null)
    {
        $this->market=$market ?? new PluginMarketService();
    }

    public function store(array $data, int $adminId, string $category = '', bool $localOnly = false): array
    {
        [$path,$relative,$identity]=$this->source($data);
        $digest=hash_file('sha256',$path);$claim=$localOnly?null:$this->market->storageClaim();
        $metadata=['provider'=>'local','sha256'=>$digest,'local_path'=>$relative];
        $url=$data['url'];$remote=false;$stage='upload';
        try {
            if ($claim!==null) {
                $plugin=Registry::get($claim['code']);
                if (!$plugin instanceof AttachmentStorage) throw new \RuntimeException('存储插件能力无效');
                $this->market->assertClaim($claim);
                $metadata+=['object'=>$relative,'bucket'=>$claim['config']['bucket'],'endpoint'=>$claim['config']['endpoint'],'revision'=>$claim['revision']];
                $metadata['provider']=$claim['code'];
                $result=$plugin->upload($claim['config'],$path,$relative,$data['mime_type'],$this->transport);
                $url=$result['url'];$remote=true;
                $metadata=array_replace($metadata,$result['metadata']);
            }
            $this->assertIdentity($path,$identity,$digest);$stage='database';
            $fields=['admin_id'=>$adminId,'name'=>mb_strcut($data['name'],0,128,'UTF-8'),'url'=>$url,
                'file_size'=>(int)filesize($path),'mime_type'=>$data['mime_type'],
                'image_width'=>(int)$data['image_with'],'image_height'=>(int)$data['image_height'],
                'ext'=>$data['ext'],'category'=>$category,'storage'=>$claim['code']??'local',
                'storage_meta'=>json_encode($metadata,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)];
            $persist=$this->persist ?? static function (array $fields): int {
                $upload=new Upload();
                foreach ($fields as $name=>$value) $upload->$name=$value;
                if (!$upload->save()) throw new \RuntimeException('附件记录未保存');
                return (int)$upload->id;
            };
            $id=$persist($fields);
            if (!is_int($id) || $id<1) throw new \RuntimeException('附件记录未保存');
        } catch (\Throwable) {
            if ($claim!==null) {
                $incident=bin2hex(random_bytes(12));
                try { $this->market->recordFailure($incident,$metadata+['stage'=>$stage,'created_at'=>time()]); }
                catch (\Throwable) { try { \support\Log::warning('Attachment failure receipt unavailable; stage '.$stage); } catch (\Throwable) {} }
            }
            throw new \RuntimeException($stage==='database'?'附件记录保存失败，本地文件已保留，请核对后重试':'OSS 上传失败、配置已变化或结果未知，本地文件已保留，请核对后重试');
        }
        $data['url']=$url;$data['size']=$fields['file_size'];$data['id']=$id;
        if ($remote && !$claim['config']['keep_local']) {
            try {
                $this->assertIdentity($path,$identity,$digest);
                if (!unlink($path)) throw new \RuntimeException('Local cleanup failed');
            } catch (\Throwable) {
                $data['storage_warning']='OSS 上传及附件记录保存成功，本地副本未能删除';
            }
        }
        return $data;
    }

    private function source(array $data): array
    {
        $relative=ltrim((string)($data['url']??''),'/');
        if (!preg_match('#^uploads/(?:img|files)/\d{8}/[a-f0-9]{32}\.[a-z0-9]+$#D',$relative)) throw new \InvalidArgumentException('附件路径无效');
        $path=UploadPaths::physical('/'.$relative);$root=realpath(Paths::uploads());
        \app\cms\config\Entry::assertPath($path);
        if (!$root || !str_starts_with($path,$root.'/') || realpath($path)!==$path || ($data['realpath']??null)!==$path) throw new \InvalidArgumentException('附件超出本站上传目录');
        $stat=lstat($path);
        if (!$stat || !is_file($path) || $stat['nlink']!==1) throw new \InvalidArgumentException('附件文件身份无效');
        return [$path,$relative,['dev'=>$stat['dev'],'ino'=>$stat['ino'],'size'=>$stat['size']]];
    }

    private function assertIdentity(string $path, array $expected, string $digest): void
    {
        \app\cms\config\Entry::assertPath($path);clearstatcache(true,$path);$stat=lstat($path);
        if (!$stat || !is_file($path) || $stat['nlink']!==1 || $expected!==['dev'=>$stat['dev'],'ino'=>$stat['ino'],'size'=>$stat['size']] || !hash_equals($digest,hash_file('sha256',$path))) throw new \RuntimeException('附件在上传期间发生变化');
    }
}
