<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;
use RuntimeException;

/** A single public robots.txt, with private revision receipts and no arbitrary paths. */
final class RobotsService
{
    public const MAX_BYTES = 262144;
    public function __construct(private ?CmsRepository $repository = null, private ?string $publicRoot = null)
    {
        $this->repository ??= new CmsRepository();$this->publicRoot ??= public_path();
    }

    public static function validate(string $content): void
    {
        if(strlen($content)>self::MAX_BYTES||!mb_check_encoding($content,'UTF-8')||preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/',$content))throw new InvalidArgumentException('robots.txt 必须为256KiB以内的UTF-8文本，不能包含控制字符');
    }

    private function path(): string
    {
        $root=realpath($this->publicRoot);
        if(!$root||!is_dir($root)||is_link($this->publicRoot))throw new RuntimeException('公开资源目录不存在或为链接');
        $file=$root.'/robots.txt';clearstatcache(true,$file);
        if(is_link($file))throw new InvalidArgumentException('禁止编辑 robots.txt 符号链接');
        if(file_exists($file)&&(!is_file($file)||($stat=lstat($file))===false||$stat['nlink']!==1))throw new InvalidArgumentException('robots.txt 必须为普通单链接文件');
        return $file;
    }

    public function read(): array
    {
        $file=$this->path();$exists=is_file($file);
        if($exists&&filesize($file)>self::MAX_BYTES)throw new InvalidArgumentException('robots.txt 超过256KiB限制');
        $content=$exists?file_get_contents($file):'';
        if($content===false)throw new RuntimeException('无法读取 robots.txt');
        self::validate($content);
        return ['path'=>'/robots.txt','exists'=>$exists,'content'=>$content,'hash'=>hash('sha256',$content),'max_bytes'=>self::MAX_BYTES];
    }

    public function revisions(): array
    {
        $current=$this->read();$store=new LanguageStateStore('robots-revisions',$this->repository);$items=[];
        $rows=$this->repository->table('cms_state_records')->where('scope','robots-revisions')->order('updated_at desc,name desc')->limit(30)->select()->toArray();
        foreach($rows as$row){
            $record=$store->read($row['name']);
            $matches=($record['phase']??'')==='complete'&&$current['exists']===($record['after_exists']??true)&&hash_equals($current['hash'],(string)($record['after_sha256']??''));
            $items[]=['revision'=>$row['name'],'created_at'=>(int)($record['created_at']??0),'phase'=>$record['phase']??'unknown','before_sha256'=>$record['before']['hash']??'','after_sha256'=>$record['after_sha256']??'','before_exists'=>(bool)($record['before']['exists']??false),'can_restore'=>$matches];
        }
        return ['items'=>$items,'hash'=>$current['hash'],'exists'=>$current['exists']];
    }

    public function restore(array $input): array
    {
        $revision=$input['revision']??null;
        if(!is_string($revision)||!preg_match('/^save\.[a-f0-9]{24}$/D',$revision))throw new InvalidArgumentException('恢复版本无效');
        $store=new LanguageStateStore('robots-revisions',$this->repository);
        return $store->locked(function()use($store,$revision,$input){
            $record=$store->read($revision);if(($record['phase']??'')!=='complete'||!isset($record['before']['content']))throw new InvalidArgumentException('该版本未完成，不能自动恢复');
            $current=$this->read();
            if($current['exists']!==($record['after_exists']??true)||!hash_equals($current['hash'],(string)($record['after_sha256']??'')))throw new InvalidArgumentException('此版本之后文件已变化，不能覆盖后续修改');
            return $this->publish(['content'=>$record['before']['content'],'hash'=>$input['hash']??'','exists'=>$input['exists']??''],(bool)$record['before']['exists'],$revision);
        });
    }

    public function save(array $input): array { return $this->publish($input,true); }

    private function publish(array $input,bool $targetExists,?string $restoredFrom=null): array
    {
        foreach(['content','hash','exists']as$key)if(!array_key_exists($key,$input)||!is_scalar($input[$key]))throw new InvalidArgumentException('robots.txt 保存参数不完整');
        $content=(string)$input['content'];self::validate($content);
        $hash=(string)$input['hash'];if(!preg_match('/^[a-f0-9]{64}$/D',$hash)||!in_array((string)$input['exists'],['0','1'],true))throw new InvalidArgumentException('请先重新读取 robots.txt');
        (new LanguageAccessService($this->repository))->assertLanguages(array_column((new LanguagePolicy($this->repository))->all(),'mark'));
        $store=new LanguageStateStore('robots-revisions',$this->repository);
        return $store->locked(function()use($input,$content,$hash,$store,$targetExists,$restoredFrom){
            $before=$this->read();
            if($before['exists']!==((string)$input['exists']==='1')||!hash_equals($before['hash'],$hash))throw new InvalidArgumentException('robots.txt 已被修改，请重新读取后再保存');
            $nextHash=hash('sha256',$content);
            if($before['exists']===$targetExists&&hash_equals($nextHash,$before['hash']))return $before+['changed'=>false];
            $file=$this->path();$directory=dirname($file);$mode=$before['exists']?(fileperms($file)&0777):0644;
            $temporary=null;$published=false;$task='save.'.bin2hex(random_bytes(12));
            $receipt=['phase'=>'prepared','before'=>$before,'after_sha256'=>$nextHash,'after_exists'=>$targetExists,'restored_from'=>$restoredFrom,'created_at'=>time()];
            try{
                // The only durable preimage lives in private database state.
                $store->write($task,$receipt);
                if($targetExists)$temporary=$this->temporary($directory,$content,$mode);
                $current=$this->read();if($current['exists']!==$before['exists']||!hash_equals($current['hash'],$before['hash']))throw new InvalidArgumentException('robots.txt 已被其他进程修改，未覆盖现有文件');
                if(!$targetExists){if($before['exists']&&!unlink($file))throw new RuntimeException('无法恢复 robots.txt 原始不存在状态');}
                elseif(!$before['exists']){if(!link($temporary,$file))throw new RuntimeException('robots.txt 已出现或目录不可写');unlink($temporary);$temporary=null;}
                else{if(!rename($temporary,$file))throw new RuntimeException('无法发布 robots.txt，请检查目录权限');$temporary=null;}
                $published=true;$receipt['phase']='complete';$receipt['completed_at']=time();$store->write($task,$receipt);
                return ['path'=>'/robots.txt','exists'=>$targetExists,'hash'=>$nextHash,'content'=>$content,'changed'=>true,'revision'=>$task];
            }catch(\Throwable $error){
                if($published){
                    $current=$this->read();
                    if($current['exists']!==$targetExists||!hash_equals($current['hash'],$nextHash))throw new RuntimeException('保存回执失败且文件已再次变化，请人工核对，未覆盖后续修改',0,$error);
                    if($before['exists']){$temporary=$this->temporary($directory,$before['content'],$mode);if(!rename($temporary,$file))throw new RuntimeException('恢复原文件失败，请检查私有修复记录',0,$error);$temporary=null;}
                    elseif(is_file($file)&&!unlink($file))throw new RuntimeException('无法撤回新文件，请检查私有修复记录',0,$error);
                }
                throw $error;
            }finally{if($temporary!==null&&is_file($temporary))unlink($temporary);}
        });
    }

    private function temporary(string $directory,string $content,int $mode): string
    {
        $file=$directory.'/.robots-'.bin2hex(random_bytes(16));$handle=@fopen($file,'xb');
        if(!$handle)throw new RuntimeException('无法创建 robots.txt 临时文件');
        try{$offset=0;while($offset<strlen($content)){$written=fwrite($handle,substr($content,$offset));if(!$written)throw new RuntimeException('写入 robots.txt 失败');$offset+=$written;}if(!fflush($handle)||(function_exists('fsync')&&!fsync($handle))||!chmod($file,$mode))throw new RuntimeException('无法保存 robots.txt');}
        catch(\Throwable $error){fclose($handle);@unlink($file);throw $error;}
        fclose($handle);return $file;
    }
}
