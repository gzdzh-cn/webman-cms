<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;
use RuntimeException;

/** New-upload processing only. Template thumbnail dimensions never resize originals. */
final class ImageUploadPolicyService
{
    public const DEFAULTS=['is_mark'=>'0','is_thumb_mark'=>'0','mark_type'=>'text','mark_width'=>'300','mark_height'=>'300','mark_txt'=>'','mark_img'=>'','mark_txt_size'=>'30','mark_txt_color'=>'#000000','mark_degree'=>'80','mark_quality'=>'90','mark_sel'=>'9','thumb_open'=>'0','thumb_width'=>'300','thumb_height'=>'300','thumb_mode'=>'2','thumb_color'=>'#ffffff'];
    public function __construct(private ?CmsRepository $repository=null) { $this->repository??=new CmsRepository(); }

    public function settings(): array
    {
        $main=(new LanguagePolicy($this->repository))->main();$settings=[];
        foreach($this->repository->table('config')->where(['lang'=>$main,'is_del'=>0])->whereIn('name',array_keys(self::DEFAULTS))->order('id')->select()->toArray()as$row){
            $name=$row['name'];if(array_key_exists($name,$settings))throw new InvalidArgumentException('图片配置重复：'.$name);
            $value=$row['value'];if(is_string($value)&&preg_match('/^(s|i|b|d):|^N;$/',$value)){$decoded=@unserialize($value,['allowed_classes'=>false]);if($decoded!==false||$value==='b:0;')$value=$decoded;}
            if(!is_scalar($value)&&$value!==null)throw new InvalidArgumentException('图片配置格式错误');$settings[$name]=(string)$value;
        }
        return $settings+self::DEFAULTS;
    }

    public static function validateSettings(array $settings): void
    {
        $settings+=self::DEFAULTS;
        foreach(['is_mark','is_thumb_mark','thumb_open']as$key)if(!in_array((string)$settings[$key],['0','1'],true))throw new InvalidArgumentException('图片开关值无效：'.$key);
        foreach(['mark_type'=>['text','img'],'mark_sel'=>array_map('strval',range(1,9)),'thumb_mode'=>['1','2','3']]as$key=>$allowed)if(!in_array((string)$settings[$key],$allowed,true))throw new InvalidArgumentException('图片配置选项无效：'.$key);
        foreach(['mark_width'=>[0,10000],'mark_height'=>[0,10000],'mark_txt_size'=>[6,160],'mark_degree'=>[0,100],'mark_quality'=>[1,100],'thumb_width'=>[0,4096],'thumb_height'=>[0,4096]]as$key=>[$min,$max])if(!preg_match('/^\d+$/D',(string)$settings[$key])||(int)$settings[$key]<$min||(int)$settings[$key]>$max)throw new InvalidArgumentException('图片配置数值超出范围：'.$key);
        foreach(['mark_txt_color','thumb_color']as$key)if(!preg_match('/^#[0-9a-fA-F]{6}$/D',(string)$settings[$key]))throw new InvalidArgumentException('图片颜色必须为六位十六进制颜色');
        if(!mb_check_encoding((string)$settings['mark_txt'],'UTF-8')||mb_strlen((string)$settings['mark_txt'])>100||str_contains((string)$settings['mark_txt'],"\0"))throw new InvalidArgumentException('水印文字格式错误或超过100个字符');
        if($settings['is_mark']==='1'){
            if($settings['mark_type']==='text'&&trim((string)$settings['mark_txt'])==='')throw new InvalidArgumentException('开启文字水印时请填写水印文字');
            if($settings['mark_type']==='img')self::watermarkFile((string)$settings['mark_img']);
            if($settings['mark_type']==='text'&&(!is_file(\app\cms\support\NativeFont::path(\app\cms\Paths::resources('fonts/hgzb.ttf')))||!function_exists('imagettftext')))throw new InvalidArgumentException('文字水印字体或GD字体支持不可用');
        }
        if($settings['thumb_open']==='1'&&(int)$settings['thumb_width']===0&&(int)$settings['thumb_height']===0)throw new InvalidArgumentException('开启缩略图时宽度和高度不能同时为0');
    }

    /** Resolve only an existing, ordinary local upload; never fetch remote watermarks. */
    public static function watermarkFile(string $url): string
    {
        $base=rtrim((string)\app\cms\config\SiteSettings::get('base_path'),'/');if($base!==''&&str_starts_with($url,$base.'/'))$url=substr($url,strlen($base));
        $url=preg_replace('#^/(?:app/admin|web-admin)/(?=(?:public/)?uploads?/)#','/',$url);
        if(!mb_check_encoding($url,'UTF-8')||!preg_match('#^/(?:public/)?uploads?/#D',$url)||preg_match('/[\x00-\x1f\x7f\\\\?#]/',$url)||preg_match('#(?:^|/)\.\.(?:/|$)#',$url))throw new InvalidArgumentException('水印图片必须为本站uploads内的本地图片');
        $path=\app\cms\UploadPaths::physical($url);$root=realpath(\app\cms\Paths::publicRoot('uploads'));$real=realpath($path);
        if(!$root||!$real||!str_starts_with($real,$root.DIRECTORY_SEPARATOR)||!is_file($real))throw new InvalidArgumentException('本地水印图片不存在或越界');
        for($check=$path;strlen($check)>=strlen($root);$check=dirname($check)){if(is_link($check))throw new InvalidArgumentException('水印图片不能使用符号链接');if($check===$root)break;}
        $info=@getimagesize($real);if(!$info||!in_array($info[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP,IMAGETYPE_GIF],true)||$info[0]>2000||$info[1]>2000)throw new InvalidArgumentException('水印图片格式不支持或尺寸超过2000px');
        return $real;
    }

    public function process(string $path,?array $settings=null): array
    {
        $settings=($settings??$this->settings())+self::DEFAULTS;
        if(!in_array((string)$settings['is_mark'],['0','1'],true))throw new InvalidArgumentException('图片水印开关值无效');
        if((string)$settings['is_mark']==='1')self::validateWaterSettings($settings);
        $bytes=file_get_contents($path);$info=$bytes===false?false:@getimagesizefromstring($bytes);
        if($info&&($info[2]===IMAGETYPE_GIF||($info[2]===IMAGETYPE_WEBP&&str_contains(substr($bytes,0,128),'ANIM'))||($info[2]===IMAGETYPE_PNG&&str_contains($bytes,'acTL')))){
            (new UploadedImageService())->resizeToFit($path,PHP_INT_MAX,PHP_INT_MAX);
            return ['watermarked'=>false,'reason'=>'animation_preserved'];
        }
        // Existing upload size limit has priority; thumb_* only controls derived images.
        (new UploadedImageService())->resizeToFit($path,1170,1170);
        return $this->watermark($path,$settings);
    }

    public function watermark(string $path,array $settings,bool $thumbnail=false): array
    {
        $settings+=self::DEFAULTS;
        $settings=array_map(static fn($v)=>is_scalar($v)?(string)$v:$v,$settings);
        if($settings['is_mark']!=='1'||($thumbnail&&$settings['is_thumb_mark']!=='1'))return ['watermarked'=>false,'reason'=>'disabled'];
        self::validateWaterSettings($settings);
        $info=@getimagesize($path);if(!$info)throw new RuntimeException('无法读取新上传图片');
        if($info[0]<=(int)$settings['mark_width']||$info[1]<=(int)$settings['mark_height'])return ['watermarked'=>false,'reason'=>'below_threshold'];
        // Retain animation bytes instead of flattening GIF/APNG/animated WebP.
        $bytes=file_get_contents($path);if($bytes===false)throw new RuntimeException('无法读取新上传图片');
        if($info[2]===IMAGETYPE_GIF||($info[2]===IMAGETYPE_WEBP&&str_contains(substr($bytes,0,128),'ANIM'))||($info[2]===IMAGETYPE_PNG&&str_contains($bytes,'acTL')))return ['watermarked'=>false,'reason'=>'animation_preserved'];
        if(!in_array($info[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP,IMAGETYPE_BMP],true))return ['watermarked'=>false,'reason'=>'format_preserved'];
        if($settings['mark_type']==='img'&&$this->hasTransparency($bytes))return ['watermarked'=>false,'reason'=>'transparency_preserved'];
        $temporary=tempnam(dirname($path),'.image-watermark-');if($temporary===false)throw new RuntimeException('无法创建水印处理文件');$memory=ini_get('memory_limit');$time=ini_get('max_execution_time');
        try{
            $image=\app\cms\compat\Image::open($path);
            if($settings['mark_type']==='text'){
                $alpha=str_pad(dechex((int)round((100-(int)$settings['mark_degree'])*127/100)),2,'0',STR_PAD_LEFT);
                $image->text((string)$settings['mark_txt'],\app\cms\support\NativeFont::path(\app\cms\Paths::resources('fonts/hgzb.ttf')),(int)$settings['mark_txt_size'],(string)$settings['mark_txt_color'].$alpha,(int)$settings['mark_sel']);
            }else{
                $water=self::watermarkFile((string)$settings['mark_img']);$mark=getimagesize($water);
                if($mark[0]>=$info[0]||$mark[1]>=$info[1])return ['watermarked'=>false,'reason'=>'watermark_larger_than_image'];
                $image->water($water,(int)$settings['mark_sel'],(int)$settings['mark_degree']);
            }
            $image->save($temporary,null,(int)$settings['mark_quality']);unset($image);
            if($info[2]===IMAGETYPE_JPEG)(new UploadedImageService())->restoreJpegExif($bytes,$temporary);
            if(!@getimagesize($temporary)||!chmod($temporary,fileperms($path)&0777)||!rename($temporary,$path))throw new RuntimeException('无法保存水印图片');
            return ['watermarked'=>true,'reason'=>'applied'];
        }finally{ini_set('memory_limit',$memory);ini_set('max_execution_time',$time);if(is_file($temporary))unlink($temporary);}
    }

    private function hasTransparency(string $bytes): bool
    {
        $image=@imagecreatefromstring($bytes);if(!$image)throw new RuntimeException('无法读取图片透明度');
        if(!imageistruecolor($image))return imagecolortransparent($image)>=0;
        for($y=0;$y<imagesy($image);$y++)for($x=0;$x<imagesx($image);$x++)if((imagecolorat($image,$x,$y)>>24&127)>0)return true;
        return false;
    }

    private static function validateWaterSettings(array $settings): void
    {
        // Template-only recipe values cannot prevent an otherwise valid upload.
        foreach(array_keys($settings)as$key)if(str_starts_with($key,'thumb_'))unset($settings[$key]);
        self::validateSettings($settings);
    }
}
