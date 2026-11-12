<?php
declare(strict_types=1);
namespace app\cms\maintenance;
use app\cms\config\{Entry,ConfigSchema};
use app\cms\Paths;
use app\cms\support\EmbeddedResources;

/** A batch is assembled privately and published only after every target succeeds. */
final class BinaryBuilder
{
    private ?\Closure $progress;
    public function __construct(?callable $progress=null) { $this->progress=$progress===null?null:\Closure::fromCallable($progress); }
    private function report(string $message): void { if($this->progress!==null)($this->progress)($message); }
    private const CONFIG=['app','autoload','bootstrap','cms_admin','cms_cache','cms_language_pack','cms_site','cms_sms','container','database','dependence','event','exception','log','middleware','process','route','server','session','static','think-orm','translation','view'];
    public function build(string $output,array $targets,string $runtimeDirectory,array $embedded=[],?string $runtime=null,string $binaryName='cms'): array
    {
        if(EmbeddedResources::binary())throw new \RuntimeException('请在源码构建环境执行');
        $binaryName=ConfigSchema::coerce('build.binary_name',$binaryName);
        $embedded=ConfigSchema::coerce('build.embedded_resources',$embedded);
        $targets=ConfigSchema::coerce('build.target',$targets)?:[RuntimeProvider::platform()];
        if($runtime!==null&&count($targets)!==1)throw new \RuntimeException('--runtime 文件参数仅允许单平台构建，请使用 --build-runtime 目录');
        if(ini_get('phar.readonly'))throw new \RuntimeException('构建子进程未开启 PHAR 写入');
        $output=Entry::absolute($output);$runtimeDirectory=Entry::absolute($runtimeDirectory);
        $this->report('开始构建；目标平台：'.implode(', ',$targets));
        $this->report('输出目录：'.$output.'；运行时缓存：'.$runtimeDirectory);
        $this->report('二进制文件名：'.$binaryName);
        $this->report('可选内置资源：'.($embedded?implode(', ',$embedded):'无（使用外置资源）'));
        foreach([$output,$runtimeDirectory]as$directory) {
            Entry::assertPath($directory);
            foreach(['config_dir','template_dir','assets_dir','uploads_dir','html_dir','runtime_dir','install_dir']as$key) {
                $root=Paths::directory($key);
                if($directory===$root||str_starts_with($directory,$root.'/')||str_starts_with($root,$directory.'/'))throw new \RuntimeException('构建目录与站点数据目录重叠：'.$key);
            }
            if($directory===Paths::code()||$directory===Paths::root())throw new \RuntimeException('构建目录不能覆盖入口目录');
            foreach(['app','support','vendor','config']as$codeDirectory) {
                $codeRoot=Paths::code($codeDirectory);
                if($directory===$codeRoot||str_starts_with($directory,$codeRoot.'/'))throw new \RuntimeException('构建输出或运行时缓存不能位于源码输入目录：'.$codeDirectory);
            }
        }
        $this->report('正在校验系统资源清单和 SHA256…');
        $assets=(new AssetMaintenance())->inspect(true);
        if(!$assets['ok'])throw new \RuntimeException('系统资源与锁定清单不一致');
        $this->report('系统资源校验通过：'.$assets['unique_targets'].' 个文件。正在扫描打包输入…');
        // Validate ALL selected resources before downloading or creating any staging directory.
        $files=$this->files($embedded);$identity=[];
        $count=0;$last=microtime(true);
        foreach($files as$relative=>$source){$identity[$relative]=hash_file('sha256',$source);$count++;if(microtime(true)-$last>=2){$this->report('打包输入摘要校验：'.$count.'/'.count($files).' 个文件');$last=microtime(true);}}
        $this->report('打包输入已确认：'.count($files).' 个文件。');
        $provider=new RuntimeProvider($this->progress);$locks=[];
        foreach($targets as$target)$locks[$target]=$provider->lock($target);
        $buildId=hash('sha256',json_encode([$identity,$embedded,$locks,$binaryName],JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
        $version=gmdate('Ymd\THis\Z').'-'.substr($buildId,0,12);
        Paths::ensureDirectory($output,0750);
        $names=[];
        foreach($targets as$target) {
            $names[$target]='cms-'.$target;
            $destination=$output.'/'.$names[$target];
            if($runtimeDirectory===$destination||str_starts_with($runtimeDirectory,$destination.'/'))throw new \RuntimeException('运行时缓存目录不能位于平台发布目录内：'.$destination);
        }
        $stage=$output.'/.cms-build-'.bin2hex(random_bytes(12));Paths::ensureDirectory($stage,0700);
        $keepStage=false;$results=[];
        try {
            $this->report('正在复制文件到本批暂存目录…');$count=0;$last=microtime(true);
            foreach($files as$relative=>$source){$bytes=file_get_contents($source);if(!is_string($bytes)||!hash_equals($identity[$relative],hash('sha256',$bytes)))throw new \RuntimeException('打包输入在构建期间发生变化：'.$relative);$this->write($stage.'/code/'.$relative,$bytes);$count++;if(microtime(true)-$last>=2){$this->report('暂存文件：'.$count.'/'.count($files));$last=microtime(true);}}
            $this->report('文件暂存完成。');$index=0;
            foreach($targets as$target) {
                $this->report('['.(++$index).'/'.count($targets).'] '.$target.'：准备 PHP 运行时…');
                $prepared=$provider->prepare($target,$runtimeDirectory,$runtime);$lock=$prepared['lock'];
                $build=['format'=>2,'version'=>$version,'build_id'=>$buildId,'binary_name'=>$binaryName,'platform'=>$target,'php'=>$lock['php'],'extensions'=>$lock['extensions'],'ini'=>$lock['ini'],'runtime_sha256'=>$prepared['sha256'],'runtime_executed_on_build_host'=>$prepared['executed_on_build_host'],'runtime_lock_sha256'=>hash('sha256',json_encode($lock,JSON_THROW_ON_ERROR)),'composer_lock_sha256'=>$identity['composer.lock'],'asset_manifest_sha256'=>$identity['app/cms/resources/asset-manifest.json'],'embedded_resources'=>$embedded,'files'=>$identity];
                $meta=$stage.'/code/app/cms/resources/binary-build.json';file_put_contents($meta,json_encode($build,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
                $this->report($target.'：正在生成 PHAR 代码归档…');
                $archive=$stage.'/'.$target.'.phar';$phar=new \Phar($archive,0,'webman');$phar->startBuffering();$phar->buildFromDirectory($stage.'/code');$phar->setSignatureAlgorithm(\Phar::SHA256);
                $phar->setStub("<?php define('IN_PHAR',true); Phar::mapPhar('webman'); require 'phar://webman/webman'; __HALT_COMPILER();");$phar->stopBuffering();unset($phar);
                $release=$stage.'/'.$names[$target];Paths::ensureDirectory($release,0750);
                $this->report($target.'：正在合并运行时与代码，生成二进制…');
                $binary=$release.'/'.$binaryName;$this->combine($prepared['file'],$archive,$lock['ini'],$binary);
                $this->report($target.'：正在收集许可证并核验产物摘要…');
                $this->licenses($release,$target);
                $this->write($release.'/runtime.lock.json',json_encode($lock,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
                $build['binary_sha256']=hash_file('sha256',$binary);$artifacts=[];
                foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($release,\FilesystemIterator::SKIP_DOTS))as$item)if($item->isFile())$artifacts[substr($item->getPathname(),strlen($release)+1)]=hash_file('sha256',$item->getPathname());ksort($artifacts);$build['artifacts']=$artifacts;
                $this->write($release.'/release.json',json_encode($build,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
                $results[]=['platform'=>$target,'output'=>$output.'/'.$names[$target],'binary'=>$output.'/'.$names[$target].'/'.$binaryName,'sha256'=>$build['binary_sha256'],'packaged_files'=>count($identity),'runtime_sha256'=>$prepared['sha256']];
                $this->report($target.'：产物准备完成，等待整批发布。');
            }
            $this->report('所有目标构建通过，正在发布本批目录…');
            $this->publish($output,$stage,$names,$keepStage,$binaryName);
            return ['ok'=>true,'version'=>$version,'build_id'=>$buildId,'binary_name'=>$binaryName,'embedded_resources'=>$embedded,'releases'=>$results];
        }catch(\Throwable$error){$this->report('构建失败，正在撤回本批产物并清理暂存文件；有效运行时缓存保留。');if($keepStage)$this->report('自动恢复未完成，暂存备份已保留：'.$stage);throw$error;}
        finally{if(!$keepStage)$this->remove($stage);}
    }
    /** Serialize replacement; keep previous releases until the whole batch commits. */
    private function publish(string $output,string $stage,array $names,bool &$keepStage,string $binaryName='cms'): void
    {
        $path=$output.'/.cms-publish.lock';Entry::assertPath($path);
        $stat=@lstat($path);
        if($stat&&(!is_file($path)||$stat['nlink']!==1))throw new \RuntimeException('发布锁文件类型无效');
        $mutex=@fopen($path,'c+b');
        if(!$mutex)throw new \RuntimeException('无法创建构建发布锁');
        $backups=[];$published=[];
        try {
            if(!chmod($path,0600)||!flock($mutex,LOCK_EX))throw new \RuntimeException('无法取得构建发布锁');
            foreach($names as$name) {
                $destination=$output.'/'.$name;Entry::assertPath($destination);
                clearstatcache(true,$destination);
                if(file_exists($destination)&&!is_dir($destination))throw new \RuntimeException('平台发布位置必须为目录：'.$destination);
                if(!is_dir($stage.'/'.$name)||is_link($stage.'/'.$name))throw new \RuntimeException('本批平台产物目录无效：'.$name);
            }
            Paths::ensureDirectory($stage.'/previous',0700);
            foreach($names as$target=>$name) {
                $destination=$output.'/'.$name;
                if(is_dir($destination)) {
                    $backup=$stage.'/previous/'.$name;
                    if(!@rename($destination,$backup))throw new \RuntimeException('无法暂存上一批平台产物：'.$name);
                    $backups[$destination]=$backup;
                    $this->report('正在覆盖 '.$target.' 的上一批产物…');
                }
                if(!@rename($stage.'/'.$name,$destination))throw new \RuntimeException('无法发布平台产物：'.$name);
                $published[]=$destination;
                $this->report('已发布 '.$target.'：'.$destination.'/'.$binaryName);
            }
        }catch(\Throwable$error) {
            $failed=[];
            foreach(array_reverse($published)as$destination) {
                try{$this->remove($destination);clearstatcache(true,$destination);if(file_exists($destination)||is_link($destination))$failed[]=$destination;}
                catch(\Throwable){$failed[]=$destination;}
            }
            foreach(array_reverse($backups,true)as$destination=>$backup)if(!@rename($backup,$destination))$failed[]=$destination;
            if($failed) {
                $keepStage=true;
                throw new \RuntimeException('发布失败且自动恢复未完成；此前产物备份保留于 '.$stage.'/previous；请检查：'.implode('、',array_unique($failed)),0,$error);
            }
            throw new \RuntimeException('发布失败，此前产物已保留或恢复：'.$error->getMessage(),0,$error);
        }finally{flock($mutex,LOCK_UN);fclose($mutex);}
    }
    private function files(array $embedded): array
    {
        $result=[];$root=Paths::code();
        foreach(['app','support','vendor']as$directory)foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/'.$directory,\FilesystemIterator::SKIP_DOTS))as$item) {
            $name=substr($item->getPathname(),strlen($root)+1);
            if($item->isLink())throw new \RuntimeException('打包输入包含符号链接：'.$name);
            if(!$item->isFile()||preg_match('#(?:^|/)(?:\.[^/]+|tests?|docs|examples?)(?:/|$)#i',$name))continue;
            if(str_starts_with($name,'app/cms/install/view/')||str_ends_with(strtolower($name),'.sql'))continue;
            if(str_starts_with($name,'app/cms/maintenance/')&&!preg_match('#^app/cms/maintenance/[A-Za-z]+\.php$#D',$name))continue;
            if(str_starts_with($name,'app/cms/resources/')&&!$this->resource(substr($name,18)))continue;
            $result[$name]=$item->getPathname();
        }
        foreach(self::CONFIG as$name)$result['config/'.$name.'.php']=$root.'/config/'.$name.'.php';
        foreach(['webman','start.php','composer.json','composer.lock','LICENSE','MIT-LICENSE.txt']as$name)if(is_file($root.'/'.$name))$result[$name]=$root.'/'.$name;
        foreach(['webman/console/app.php','webman/event/app.php','webman/event/bootstrap.php','webman/event/command.php']as$name)if(is_file($root.'/config/plugin/'.$name))$result['config/plugin/'.$name]=$root.'/config/plugin/'.$name;
        foreach($embedded as$entry) {
            if(in_array($entry,EmbeddedResources::YAML,true)) {
                $source=$root.'/'.$entry;if(!is_file($source))throw new \RuntimeException('指定打包的 YAML 文件不存在：'.$entry);
                \app\cms\config\DeploymentConfig::document($source);$result[$entry]=$source;
            }elseif($entry==='template')$this->collect(Paths::templates(),'template',$result);
            elseif($entry==='public') {
                $manifest=json_decode((string)file_get_contents(Paths::resources('asset-manifest.json')),true,512,JSON_THROW_ON_ERROR);
                foreach($manifest['files']as$asset)if(str_starts_with($asset['target'],'public/admin-assets/'))$result[$asset['target']]=Paths::logical($asset['target']);
                // Root public files are deployment-independent resources; uploads and HTML never enter the archive.
                foreach(scandir(Paths::publicRoot())?:[]as$name)if(preg_match('/^(?:robots\.txt|favicon\.(?:ico|png|svg)|[A-Za-z0-9_-]+\.(?:png|jpg|jpeg|webp|gif|svg|css|js|woff2?))$/D',$name)&&is_file(Paths::publicRoot($name)))$result['public/'.$name]=Paths::publicRoot($name);
            }
        }
        ksort($result);
        foreach($result as$relative=>$path){Entry::assertPath($path);$s=lstat($path);if(!$s||!is_file($path)||!is_readable($path)||$s['nlink']!==1)throw new \RuntimeException('打包输入文件身份无效：'.$relative);}
        return$result;
    }
    private function collect(string $directory,string $prefix,array &$files): void
    {
        Entry::assertPath($directory);if(!is_dir($directory)||!is_readable($directory))throw new \RuntimeException('打包资源目录无效：'.$prefix);
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory,\FilesystemIterator::SKIP_DOTS))as$item) {
            $relative=substr($item->getPathname(),strlen($directory)+1);
            if($item->isLink())throw new \RuntimeException('打包资源含符号链接：'.$prefix.'/'.$relative);
            if(!$item->isFile()||preg_match('#(?:^|/)(?:\.[^/]+|runtime|uploads|backups?|node_modules|bin)(?:/|$)#i',$relative)||preg_match('/\.(?:php\d*|phtml|phar|sql|env|ini|bak|key|pem|log)$/i',$relative))continue;
            $files[$prefix.'/'.$relative]=$item->getPathname();
        }
    }
    private function resource(string $relative): bool
    {
        if(in_array($relative,['asset-manifest.json','backend-provenance.json','binary-runtime.catalog.json','legacy/data/conf/version.txt','legacy/data/conf/pinyin.dat','fonts/hgzb.ttf'],true))return true;
        foreach(['defaults/','languages/','licenses/','legacy-assets/','compat-assets/']as$prefix)if(str_starts_with($relative,$prefix))return true;
        return false;
    }
    private function combine(string $runtime,string $archive,array $ini,string $file): void
    {
        $out=fopen($file,'xb');if(!$out)throw new \RuntimeException('二进制输出冲突');
        try {
            foreach([$runtime,$archive]as$source) {
                if($source===$archive){$text='';foreach($ini as$key=>$value)$text.=$key.'='.$value."\n";$header="\xfd\xf6\x69\xe6".pack('N',strlen($text)).$text;if(fwrite($out,$header)!==strlen($header))throw new \RuntimeException('INI 写入失败');}
                $in=fopen($source,'rb');try{if(stream_copy_to_stream($in,$out)!==filesize($source))throw new \RuntimeException('二进制写入不完整');}finally{fclose($in);}
            }
            if(!fflush($out))throw new \RuntimeException('二进制写入失败');
        }finally{fclose($out);}chmod($file,0755);
    }
    private function licenses(string $release,string $target): void
    {
        foreach(['LICENSE','MIT-LICENSE.txt']as$name)if(is_file(Paths::code($name)))$this->write($release.'/licenses/'.$name,(string)file_get_contents(Paths::code($name)));
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(Paths::code('vendor'),\FilesystemIterator::SKIP_DOTS))as$item)if($item->isFile()&&preg_match('/^(?:LICEN[CS]E|COPYING|NOTICE)(?:\..*)?$/i',$item->getFilename()))$this->write($release.'/licenses/composer/'.substr($item->getPathname(),strlen(Paths::code('vendor/'))),(string)file_get_contents($item->getPathname()));
        $directory=Paths::resources('licenses/binary-'.$target);
        if(is_dir($directory))foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory,\FilesystemIterator::SKIP_DOTS))as$item)if($item->isFile())$this->write($release.'/licenses/runtime/'.substr($item->getPathname(),strlen($directory)+1),(string)file_get_contents($item->getPathname()));
        $directory=Paths::resources('licenses/binary-common');
        if(!is_dir($directory))throw new \RuntimeException('缺少锁定运行时许可证');
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory,\FilesystemIterator::SKIP_DOTS))as$item)if($item->isFile())$this->write($release.'/licenses/runtime/common/'.substr($item->getPathname(),strlen($directory)+1),(string)file_get_contents($item->getPathname()));
    }
    private function write(string $file,string $bytes): void { Paths::ensureDirectory(dirname($file),0750);$h=fopen($file,'xb');if(!$h)throw new \RuntimeException('构建文件冲突');try{if(fwrite($h,$bytes)!==strlen($bytes))throw new \RuntimeException('构建写入失败');}finally{fclose($h);} }
    private function remove(string $directory): void
    {
        if(!is_dir($directory)||is_link($directory))return;
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST)as$item){if($item->isDir()&&!$item->isLink())rmdir($item->getPathname());else unlink($item->getPathname());}rmdir($directory);
    }
}
