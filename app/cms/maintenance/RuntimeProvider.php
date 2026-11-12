<?php
declare(strict_types=1);
namespace app\cms\maintenance;

use app\cms\config\Entry;
use app\cms\Paths;

/** Pinned micro runtimes, never the host's ordinary PHP executable. */
final class RuntimeProvider
{
    private ?\Closure $progress;
    public function __construct(?callable $progress=null) { $this->progress=$progress===null?null:\Closure::fromCallable($progress); }
    private function report(string $message): void { if($this->progress!==null)($this->progress)($message); }
    public const TARGETS=['linux-aarch64','linux-x86_64','darwin-aarch64','darwin-x86_64'];
    public static function platform(): string
    {
        $os=match(PHP_OS_FAMILY){'Linux'=>'linux','Darwin'=>'darwin',default=>throw new \RuntimeException('不支持的构建操作系统：'.PHP_OS_FAMILY)};
        $cpu=match(strtolower(php_uname('m'))){'arm64','aarch64'=>'aarch64','amd64','x86_64'=>'x86_64',default=>throw new \RuntimeException('不支持的构建 CPU：'.php_uname('m'))};
        return $os.'-'.$cpu;
    }
    public function lock(string $target): array
    {
        $catalog=json_decode((string)file_get_contents(Paths::resources('binary-runtime.catalog.json')),true,512,JSON_THROW_ON_ERROR);
        return $catalog['platforms'][$target]??throw new \RuntimeException('未登记锁定运行时：'.$target);
    }
    public function prepare(string $target,string $directory,?string $provided=null): array
    {
        $lock=$this->lock($target);
        $cache=Entry::absolute($directory).'/'.$target;Entry::assertPath($cache);
        $file=$provided!==null?Entry::absolute($provided):$cache.'/micro.sfx';
        Entry::assertPath($file);
        if($provided!==null)$this->report($target.'：使用指定运行时，正在校验…');
        elseif(is_file($file))$this->report($target.'：命中运行时缓存，正在校验…');
        if($provided===null&&!is_file($file)) {
            $this->report($target.'：运行时缓存未命中，正在获取缓存锁（其他构建占用时需等待）…');
            Paths::ensureDirectory($cache,0700);
            $mutex=fopen($cache.'/.prepare.lock','c+b');if(!$mutex||!flock($mutex,LOCK_EX))throw new \RuntimeException('无法锁定运行时缓存');
            $temporary=null;
            try {
                $this->report($target.'：已取得运行时缓存锁。');
                clearstatcache(true,$file);
                if(!is_file($file)) {
                    $archive=$cache.'/runtime.tar.gz';
                    if(!is_file($archive)||!hash_equals($lock['archive_sha256'],hash_file('sha256',$archive))) {
                        $temporary=$cache.'/.download-'.bin2hex(random_bytes(8));
                        $this->download($lock['url'],$temporary,$target);
                        $this->report($target.'：下载完成，正在校验归档 SHA256…');
                        if(!hash_equals($lock['archive_sha256'],hash_file('sha256',$temporary)))throw new \RuntimeException('运行时归档 SHA256 不匹配：'.$target);
                        if(!rename($temporary,$archive))throw new \RuntimeException('无法保存运行时归档');$temporary=null;
                    }
                    else $this->report($target.'：命中已校验的运行时归档缓存，跳过下载。');
                    $this->report($target.'：正在解压并校验 micro 运行时…');
                    $tar=new \PharData($archive);$member=$lock['member'];
                    if(!isset($tar[$member])||!$tar[$member]->isFile()||$tar[$member]->isLink())throw new \RuntimeException('锁定运行时归档缺少 micro');
                    $bytes=$tar[$member]->getContent();
                    if(!hash_equals($lock['runtime_sha256'],hash('sha256',$bytes)))throw new \RuntimeException('归档运行时 SHA256 不匹配');
                    $temporary=$cache.'/.micro-'.bin2hex(random_bytes(8));
                    if(file_put_contents($temporary,$bytes,LOCK_EX)!==strlen($bytes)||!chmod($temporary,0755)||!rename($temporary,$file))throw new \RuntimeException('无法保存运行时缓存');$temporary=null;
                }else $this->report($target.'：其他构建已准备好运行时，复用缓存。');
            }finally{if($temporary!==null&&is_file($temporary))unlink($temporary);flock($mutex,LOCK_UN);fclose($mutex);}
        }
        if(!is_file($file)||!is_readable($file)||is_link($file)||!hash_equals($lock['runtime_sha256'],hash_file('sha256',$file)))throw new \RuntimeException('运行时 SHA256 不匹配：'.$target.'；请删除损坏缓存或提供锁定 micro.sfx');
        $this->validateHeader($file,$target);
        if($target===self::platform())$this->report($target.'：正在执行 PHP 版本、扩展及 INI 检查…');
        else $this->report($target.'：摘要和平台头校验通过；与本机平台不同，跳过本机执行验证。');
        $verified=$target===self::platform()?$this->probe($file,$lock,$cache):false;
        $this->report($target.'：运行时准备完成（PHP '.$lock['php'].'）。');
        return ['file'=>$file,'lock'=>$lock,'sha256'=>hash_file('sha256',$file),'executed_on_build_host'=>$verified];
    }
    private function probe(string $runtime,array $lock,string $cache): bool
    {
        Paths::ensureDirectory($cache,0700);
        $file=$cache.'/.probe-'.bin2hex(random_bytes(8));
        $ini='';foreach($lock['ini']as$name=>$value)$ini.=$name.'='.$value."\n";
        $payload='<?php echo json_encode(["php"=>PHP_VERSION,"extensions"=>get_loaded_extensions(),"ini"=>ini_get_all(null,false)],JSON_THROW_ON_ERROR);';
        try {
            $out=fopen($file,'xb');if(!$out)throw new \RuntimeException('无法创建运行时检查文件');
            try{$in=fopen($runtime,'rb');try{stream_copy_to_stream($in,$out);}finally{fclose($in);}fwrite($out,"\xfd\xf6\x69\xe6".pack('N',strlen($ini)).$ini.$payload);}finally{fclose($out);}
            chmod($file,0700);
            $process=proc_open([$file],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$cache);
            if(!is_resource($process))throw new \RuntimeException('无法执行匹配平台 micro 运行时');
            stream_set_blocking($pipes[1],false);stream_set_blocking($pipes[2],false);$bytes='';$deadline=microtime(true)+15;
            do{$bytes.=stream_get_contents($pipes[1]);stream_get_contents($pipes[2]);$status=proc_get_status($process);if(!$status['running'])break;if(microtime(true)>$deadline){proc_terminate($process,9);throw new \RuntimeException('micro 运行时验证超时');}usleep(10000);}while(true);
            $bytes.=stream_get_contents($pipes[1]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);
            if(($status['exitcode']>=0?$status['exitcode']:$exit)!==0)throw new \RuntimeException('micro 运行时无法执行，请检查匹配平台工具链');
            $actual=json_decode($bytes,true,32,JSON_THROW_ON_ERROR);$extensions=$actual['extensions']??[];$expected=$lock['extensions'];sort($extensions);sort($expected);
            if(($actual['php']??null)!==$lock['php']||$extensions!==$expected)throw new \RuntimeException('micro PHP 版本或扩展与锁定值不一致');
            foreach($lock['ini']as$name=>$value)if(in_array((string)$value,['0','1'],true)?filter_var($actual['ini'][$name]??null,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE)!==((string)$value==='1'):(string)($actual['ini'][$name]??'')!==(string)$value)throw new \RuntimeException('micro 必需 INI 不匹配：'.$name);
            return true;
        }finally{if(isset($process)&&is_resource($process)){proc_terminate($process,9);proc_close($process);}if(is_file($file))unlink($file);}
    }
    private function download(string $url,string $file,string $target): void
    {
        $host=parse_url($url,PHP_URL_HOST);
        if(parse_url($url,PHP_URL_SCHEME)!=='https'||!in_array($host,['static-php-cli.fra1.digitaloceanspaces.com','download.workerman.net'],true))throw new \RuntimeException('运行时下载来源未授权');
        $offset=0;$started=microtime(true);
        $this->report($target.'：开始下载锁定运行时（来源 '.$host.'）；连接超时 30 秒，单次传输超时 300 秒。');
        for($attempt=0;$attempt<8;$attempt++) {
            $this->report($target.'：下载尝试 '.($attempt+1).'/8'.($offset>0?'，从 '.self::bytes($offset).' 续传':'，正在连接…'));
            $out=fopen($file,$offset===0?'xb':'ab');if(!$out)throw new \RuntimeException('无法创建运行时下载文件');
            $curl=curl_init($url);
            $last=0.0;
            curl_setopt_array($curl,[CURLOPT_FILE=>$out,CURLOPT_FAILONERROR=>true,CURLOPT_TIMEOUT=>300,CURLOPT_CONNECTTIMEOUT=>30,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS]);
            if($this->progress!==null)curl_setopt_array($curl,[CURLOPT_NOPROGRESS=>false,CURLOPT_XFERINFOFUNCTION=>function($curl,float $total,float $received,float $uploadTotal,float $uploaded)use($target,$offset,$started,&$last):int{
                $now=microtime(true);if($now-$last<2)return 0;$last=$now;
                $elapsed=max(0.001,$now-$started);$current=$offset+$received;
                $size=$total>0?' / '.self::bytes($offset+$total).'（'.number_format(min(100,100*$current/($offset+$total)),1).'%）':'（总大小待服务器返回）';
                $this->report($target.'：下载 '.self::bytes($current).$size.'；平均 '.self::bytes($current/$elapsed).'/s；已用 '.number_format($elapsed,0).' 秒');
                return 0;
            }]);
            if($offset>0)curl_setopt($curl,CURLOPT_RESUME_FROM,$offset);
            try{$ok=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);$error=curl_errno($curl);}finally{curl_close($curl);fclose($out);}
            if($ok&&$status===($offset>0?206:200)){
                clearstatcache(true,$file);$size=(int)filesize($file);$elapsed=max(0.001,microtime(true)-$started);
                $this->report($target.'：传输完成，共 '.self::bytes($size).'；平均 '.self::bytes($size/$elapsed).'/s；耗时 '.number_format($elapsed,1).' 秒。');return;
            }
            clearstatcache(true,$file);$next=(int)filesize($file);
            if($next>536870912||$next<=$offset){$this->report($target.'：下载失败（cURL '.$error.'，HTTP '.$status.'），没有可继续的有效下载进度。');throw new \RuntimeException('锁定运行时下载失败，请检查网络后重试');}
            $this->report($target.'：本次传输未完成（cURL '.$error.'，HTTP '.$status.'），已保存 '.self::bytes($next).($attempt<7?'，准备续传。':'，已达到尝试次数上限。'));
            $offset=$next;
        }
        throw new \RuntimeException('锁定运行时下载未完成，请检查网络后重试');
    }
    private static function bytes(float $bytes): string
    {
        foreach(['B','KiB','MiB','GiB']as$unit){if($bytes<1024||$unit==='GiB')return number_format($bytes,$unit==='B'?0:2).' '.$unit;$bytes/=1024;}
        return '';
    }
    private function validateHeader(string $file,string $target): void
    {
        $h=fopen($file,'rb');$bytes=fread($h,64);fclose($h);
        $cpu=str_ends_with($target,'aarch64')?'aarch64':'x86_64';
        if(str_starts_with($target,'linux-')) {
            if(substr($bytes,0,6)!=="\x7fELF\x02\x01"||unpack('v',substr($bytes,18,2))[1]!==($cpu==='aarch64'?183:62))throw new \RuntimeException('运行时 ELF 平台不匹配');
        }elseif(substr($bytes,0,4)!=="\xcf\xfa\xed\xfe"||unpack('V',substr($bytes,4,4))[1]!==($cpu==='aarch64'?0x100000c:0x1000007))throw new \RuntimeException('运行时 Mach-O 平台不匹配');
    }
}
