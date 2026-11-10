<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** One request-local upload policy for admin, editor and attachment upload entrypoints. */
class CmsUploadPolicyService
{
    private ?array $settings=null;
    public function __construct(private ?CmsRepository $repository=null) { $this->repository??=new CmsRepository(); }
    protected function settings():array
    {
        if($this->settings!==null)return $this->settings;
        $lang=$this->repository->table('language')->order('id asc')->value('mark');
        if(!$lang)throw new InvalidArgumentException('上传设置所属语言不存在');
        $rows=$this->repository->table('config')->where(['lang'=>$lang,'is_del'=>0])->whereIn('name',['image_type','file_type','media_type','file_size','file_uploadname_open'])->order('id asc')->select()->toArray();$settings=[];
        foreach($rows as$row){$name=$row['name'];if(array_key_exists($name,$settings))throw new InvalidArgumentException('上传设置存在重复项');$value=$row['value'];if(is_string($value)&&preg_match('/^(s|i|b|d):|^N;$/',$value)){$decoded=@unserialize($value,['allowed_classes'=>false]);if($decoded!==false||$value==='b:0;')$value=$decoded;}if(!is_scalar($value)&&$value!==null)throw new InvalidArgumentException('上传设置格式错误');$settings[$name]=(string)$value;}
        return $this->settings=$settings+['image_type'=>implode('|',BasicSettingsService::IMAGE_EXTENSIONS),'file_type'=>BasicSettingsService::DEFAULT_FILES,'media_type'=>'mp3|wav|ogg|mp4|webm|mov','file_size'=>'50','file_uploadname_open'=>'0'];
    }
    public function validate(string $extension,int $bytes,bool $imageOnly=false):void
    {
        $settings=$this->settings();$extension=strtolower($extension);
        if(!preg_match('/^[a-z0-9][a-z0-9-]{0,15}$/D',$extension))throw new InvalidArgumentException('不支持该格式的文件上传');
        $group=in_array($extension,BasicSettingsService::IMAGE_EXTENSIONS,true)?'image_type':(in_array($extension,BasicSettingsService::MEDIA_EXTENSIONS,true)?'media_type':'file_type');
        if($imageOnly&&$group!=='image_type')throw new InvalidArgumentException('此入口仅支持图片上传');
        $supported=match($group){'image_type'=>BasicSettingsService::IMAGE_EXTENSIONS,'media_type'=>BasicSettingsService::MEDIA_EXTENSIONS,default=>BasicSettingsService::DOCUMENT_EXTENSIONS};
        $allowed=array_values(array_intersect(array_map(static fn($v)=>strtolower(trim($v)),explode('|',$settings[$group])),$supported));
        if(!in_array($extension,$allowed,true))throw new InvalidArgumentException('不支持该格式的文件上传');
        if(!preg_match('/^\d+$/D',$settings['file_size'])||(int)$settings['file_size']<1||(int)$settings['file_size']>1024)throw new InvalidArgumentException('附件上传大小设置无效');
        $limit=(int)$settings['file_size']*1024*1024;if($bytes<0||$bytes>$limit)throw new InvalidArgumentException('上传文件不能超过'.$settings['file_size'].'MB');
    }
    public function filename(string $original,string $extension,string $directory):string
    {
        return bin2hex(random_bytes(16)).'.'.strtolower($extension);
    }
}
