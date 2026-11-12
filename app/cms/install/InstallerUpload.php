<?php
declare(strict_types=1);
namespace app\cms\install;

/** Session-owned, bounded SQL uploads, deliberately outside disposable runtime. */
final class InstallerUpload
{
    public const LIMIT = 50 * 1024 * 1024;
    public const CHUNK = 2 * 1024 * 1024;
    private string $directory;
    public function __construct(private string $root, private string $owner)
    {
        if ($owner === '' || !is_dir(\app\cms\Paths::install()) || is_link(\app\cms\Paths::install())) throw new \RuntimeException('安装上传入口已关闭');
        $this->directory = \app\cms\Paths::install('.imports');
        foreach ([\app\cms\Paths::install(), $this->directory] as $path) {
            if (is_link($path) || (file_exists($path) && !is_dir($path))) throw new \RuntimeException('安装上传目录无效');
        }
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true)) throw new \RuntimeException('无法创建私有上传目录');
        chmod($this->directory, 0700);
    }
    public function start(mixed $name, mixed $size): array
    {
        if (!is_string($name) || strlen($name)>255 || !preg_match('/\.sql$/iD', $name)
            || !is_scalar($size) || !ctype_digit((string)$size) || (int)$size<1 || (int)$size>\app\cms\config\DeploymentConfig::get('limits.install_sql_bytes')) {
            throw new \InvalidArgumentException('请选择不超过 50 MiB 的完整 .sql 文件');
        }
        $id = bin2hex(random_bytes(20));
        $state = ['owner'=>hash('sha256',$this->owner),'name'=>basename($name),'size'=>(int)$size,'received'=>0,'index'=>0,'chunks'=>[],'created_at'=>time()];
        $path = $this->path($id, '.json');
        $h=fopen($path,'x+b'); if(!$h)throw new \RuntimeException('无法记录上传'); chmod($path,0600);
        fwrite($h,json_encode($state,JSON_THROW_ON_ERROR));fclose($h);
        $h=fopen($this->path($id,'.part'),'x+b');if(!$h)throw new \RuntimeException('无法创建上传文件');fclose($h);chmod($this->path($id,'.part'),0600);
        return ['upload_id'=>$id,'chunk_size'=>\app\cms\config\DeploymentConfig::get('limits.install_chunk_bytes'),'max_size'=>\app\cms\config\DeploymentConfig::get('limits.install_sql_bytes')];
    }
    public function chunk(string $id, mixed $index, string $temporary): array
    {
        if (!is_scalar($index)||!ctype_digit((string)$index)) throw new \InvalidArgumentException('上传分片编号无效');
        $bytes=file_get_contents($temporary); if($bytes===false||strlen($bytes)<1||strlen($bytes)>\app\cms\config\DeploymentConfig::get('limits.install_chunk_bytes'))throw new \InvalidArgumentException('上传分片大小无效');
        return $this->locked($id,function(array &$state)use($id,$index,$bytes):array{
            $index=(int)$index;$hash=hash('sha256',$bytes);
            if($index<$state['index']){
                if(($state['chunks'][$index]??null)!==$hash)throw new \RuntimeException('重复分片内容不一致');
                return ['received'=>$state['received'],'index'=>$state['index']];
            }
            if($index!==$state['index']||$state['received']+strlen($bytes)>$state['size']||isset($state['sha256']))throw new \RuntimeException('上传分片顺序或大小不正确');
            $path=$this->path($id,'.part');$h=fopen($path,'r+b');if(!$h)throw new \RuntimeException('上传文件缺失');
            try{
                $actual=fstat($h)['size'];
                if($actual===$state['received']){fseek($h,$state['received']);if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h))throw new \RuntimeException('上传分片保存失败');}
                elseif($actual===$state['received']+strlen($bytes)){fseek($h,$state['received']);if(fread($h,strlen($bytes))!==$bytes)throw new \RuntimeException('中断分片校验失败');}
                else throw new \RuntimeException('上传文件大小变化，请重新上传');
            }finally{fclose($h);}
            $state['received']+=strlen($bytes);$state['index']++;$state['chunks'][]=$hash;
            return ['received'=>$state['received'],'index'=>$state['index']];
        });
    }
    public function finish(string $id): array
    {
        return $this->locked($id,function(array &$state)use($id):array{
            $path=$this->path($id,'.part');
            if($state['received']!==$state['size']||filesize($path)!==$state['size'])throw new \RuntimeException('SQL 尚未完整上传');
            $state['sha256']=hash_file('sha256',$path);
            return ['upload_token'=>$id.'.'.hash_hmac('sha256',$id.'|'.$state['sha256'],$this->owner),'sha256'=>$state['sha256'],'size'=>$state['size'],'name'=>$state['name']];
        });
    }
    public function resolve(string $token): array
    {
        if(!preg_match('/^([a-f0-9]{40})\.([a-f0-9]{64})$/D',$token,$m))throw new \InvalidArgumentException('请先完整上传 SQL 文件');
        return $this->locked($m[1],function(array &$state)use($m):array{
            $path=$this->path($m[1],'.part');$sha=$state['sha256']??'';
            if($sha===''||!hash_equals(hash_hmac('sha256',$m[1].'|'.$sha,$this->owner),$m[2])||!hash_equals($sha,hash_file('sha256',$path)))throw new \RuntimeException('SQL 上传凭据或内容已变化，请重新上传');
            return ['sql_file'=>$path,'sql_sha256'=>$sha];
        });
    }
    private function path(string $id,string $suffix):string
    {
        if(!preg_match('/^[a-f0-9]{40}$/D',$id))throw new \InvalidArgumentException('上传编号无效');
        $path=$this->directory.'/'.$id.$suffix;
        if(is_link($path))throw new \RuntimeException('上传路径无效');
        return $path;
    }
    private function locked(string $id,callable $fn):array
    {
        $path=$this->path($id,'.json');$h=@fopen($path,'r+b');if(!$h)throw new \RuntimeException('上传记录不存在，请重新上传');
        try{
            if(!flock($h,LOCK_EX))throw new \RuntimeException('上传正在处理');
            $state=json_decode(stream_get_contents($h),true,512,JSON_THROW_ON_ERROR);
            if(!hash_equals($state['owner']??'',hash('sha256',$this->owner)))throw new \RuntimeException('上传不属于当前安装会话');
            $result=$fn($state);$json=json_encode($state,JSON_THROW_ON_ERROR);rewind($h);
            if(fwrite($h,$json)!==strlen($json)||!ftruncate($h,strlen($json))||!fflush($h))throw new \RuntimeException('上传进度保存失败');
            return $result;
        }finally{flock($h,LOCK_UN);fclose($h);}
    }
}
