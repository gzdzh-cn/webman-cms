<?php
declare(strict_types=1);
namespace app\cms\maintenance;

use app\cms\Paths;
use InvalidArgumentException;
use RuntimeException;

/** PHP-only resource verification, construction, packing and restoration. */
final class AssetMaintenance
{
    private const PUBLIC_ROOTS = [Paths::PUBLIC_ASSETS . '/'];
    private const PRIVATE_ROOTS = ['app/cms/resources/fonts/', 'app/cms/resources/defaults/', 'app/cms/resources/legacy-assets/', 'app/view/cms/legacy-template/'];
    private const EXECUTABLE = ['php','phtml','phar','php3','php4','php5','php7','php8','cgi','pl','sh'];
    private const MUTABLE_TARGET = Paths::COMMON_ASSETS . '/generated/lang/foreign_global.js';
    private string $root;
    private bool $mapped;

    public function __construct(?string $root = null)
    {
        $this->mapped = $root === null;
        $this->root = rtrim($root ?? Paths::code(), '/');
        if ((!str_starts_with($this->root, '/') && !str_starts_with($this->root, 'phar://')) || !is_dir($this->root)) throw new InvalidArgumentException('资源项目目录必须是已存在的绝对路径');
        $this->noLinks($this->root);
    }

    /** check validates layout and presence; verify additionally reads every target SHA. */
    public function inspect(bool $verify = false): array
    {
        $file = $this->root . '/app/cms/resources/asset-manifest.json';
        $this->noLinks($file);
        if (!is_file($file)) throw new RuntimeException('资源清单不存在，请先恢复完整源码与资源包');
        $manifest = json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        if (($manifest['format'] ?? null) !== 2 || !isset($manifest['files']) || !is_array($manifest['files']) || !array_is_list($manifest['files']) || !$manifest['files']) throw new RuntimeException('只接受 format=2 的新架构资源清单');
        $targets = []; $sources = []; $urls = []; $readAliases = []; $errors = []; $sourceBytes = 0;
        foreach ($manifest['files'] as $record) {
            if (!is_array($record)) throw new RuntimeException('资源清单项无效');
            $source = $this->relative($record['source'] ?? null);
            $target = $this->relative($record['target'] ?? null);
            $public = $record['public'] ?? null;
            $html = $record['html_allowed'] ?? null;
            $mutable = $record['mutable'] ?? false;
            if (!is_bool($mutable) || ($mutable && $target !== self::MUTABLE_TARGET)) throw new RuntimeException('可变资源只能是语言包生成文件：' . $target);
            if (!is_bool($public) || !is_bool($html) || !is_string($record['action'] ?? null) || !is_array($record['aliases'] ?? null)) throw new RuntimeException('资源分类或别名无效：' . $target);
            $roots = $public ? self::PUBLIC_ROOTS : self::PRIVATE_ROOTS;
            if (!$this->inRoots($target, $roots) || in_array(strtolower(pathinfo($target, PATHINFO_EXTENSION)), self::EXECUTABLE, true)) throw new RuntimeException('资源目标超出允许目录：' . $target);
            $size = $record['size'] ?? $record['bytes'] ?? null;
            $sourceSize = $record['source_size'] ?? null;
            if (!is_int($size) || $size < 0 || !is_int($sourceSize) || $sourceSize < 0 || !$this->sha($record['sha256'] ?? null) || !$this->sha($record['source_sha256'] ?? null)) throw new RuntimeException('资源大小或 SHA 无效：' . $target);
            $sourceIdentity = ['sha256'=>$record['source_sha256'], 'size'=>$sourceSize];
            if (isset($sources[$source]) && $sources[$source] !== $sourceIdentity) throw new RuntimeException('资源来源出现冲突记录：' . $source);
            $sources[$source] = $sourceIdentity;
            if (isset($record['size'], $record['bytes']) && $record['size'] !== $record['bytes']) throw new RuntimeException('资源大小字段冲突：' . $target);
            if ($public && in_array(strtolower(pathinfo($target, PATHINFO_EXTENSION)), ['html','htm'], true) && !$html) throw new RuntimeException('公开 HTML 缺少显式允许标记：' . $target);
            $url = $record['url'] ?? null;
            if (!$public && (($url !== null && $url !== '') || $record['aliases'])) throw new RuntimeException('私有资源不能配置公开 URL：' . $target);
            if (!is_array($record['read_aliases'] ?? [])) throw new RuntimeException('文件读取别名无效：' . $target);
            foreach ($record['read_aliases'] ?? [] as $alias) {
                if (!is_string($alias) || str_starts_with($alias, '//')) throw new RuntimeException('文件读取别名无效');
                $this->relative(ltrim($alias, '/'));
                if (isset($readAliases[$alias]) && $readAliases[$alias] !== $target) throw new RuntimeException('文件读取别名指向多个实体：' . $alias);
                $readAliases[$alias] = $target;
            }
            if ($public) {
                if (!is_string($url) || $url === '') throw new RuntimeException('公开资源缺少 URL：' . $target);
                $canonical = str_starts_with($target, 'public/') ? substr($target, 6) : '/' . $target;
                if ($url !== $canonical) throw new RuntimeException('资源规范 URL 与实体目录不符：' . $target);
                foreach (array_merge([$url], $record['aliases']) as $alias) {
                    $this->url($alias);
                    if (isset($urls[$alias]) && $urls[$alias] !== $target) throw new RuntimeException('资源 URL 指向多个实体：' . $alias);
                    $urls[$alias] = $target;
                }
            }
            $identity = ['sha256'=>$record['sha256'], 'size'=>$size, 'public'=>$public, 'html_allowed'=>$html, 'mutable'=>$mutable];
            if (isset($targets[$target]) && $targets[$target] !== $identity) throw new RuntimeException('资源实体出现冲突记录：' . $target);
            $targets[$target] = $identity;
            $sourceBytes += $sourceSize;
        }
        $bytes = 0; $mutableFiles = [];
        foreach ($targets as $target => $record) {
            $path = $this->mapped ? Paths::logical($target) : $this->root . '/' . $target;
            $this->noLinks($path);
            if (!is_file($path)) { $errors[] = ['target'=>$target, 'reason'=>'missing']; continue; }
            $bytes += filesize($path);
            if ($record['mutable']) {
                $actualSha = (string)hash_file('sha256', $path);
                $mutableFiles[] = ['target'=>$target, 'actual_size'=>filesize($path), 'actual_sha256'=>$actualSha, 'baseline_sha256'=>$record['sha256'], 'matches_baseline'=>filesize($path)===$record['size'] && hash_equals($record['sha256'], $actualSha)];
                continue;
            }
            if ($verify && (filesize($path) !== $record['size'] || !hash_equals($record['sha256'], (string)hash_file('sha256', $path)))) $errors[] = ['target'=>$target, 'reason'=>'size_or_sha_mismatch'];
        }
        return ['ok'=>!$errors, 'mode'=>$verify?'verify':'check', 'format'=>2, 'records'=>count($manifest['files']), 'unique_targets'=>count($targets), 'aliases_and_urls'=>count($urls), 'read_aliases'=>count($readAliases), 'source_bytes'=>$sourceBytes, 'target_bytes'=>$bytes, 'mutable_files'=>$mutableFiles, 'errors'=>$errors];
    }

    public function build(array $options, bool $apply = false): array
    {
        $this->sourceOnly();
        if (!$apply) {
            if (!empty($options['input']) || !empty($options['output'])) throw new InvalidArgumentException('--input/--output 需要 --apply');
            return $this->inspect(true) + ['engine'=>'php'];
        }
        $manifestPath = $this->root . '/app/cms/resources/asset-manifest.json';
        $manifestBytes = (string)file_get_contents($manifestPath);
        $manifest = json_decode($manifestBytes, true, 512, JSON_THROW_ON_ERROR);
        $this->manifestFiles();
        if (($manifest['build']['engine'] ?? null) !== 'php-copy-v1') throw new RuntimeException('资源清单需要迁移到 PHP 构建配方');
        $output = AssetArchive::path($options['output'] ?: $this->root);
        $input = !empty($options['input']) ? AssetArchive::path($options['input']) : null;
        if ($input !== null && !is_dir($input)) throw new RuntimeException('构建输入目录不存在');
        $records = $this->manifestFiles();
        $records['app/cms/resources/asset-manifest.json'] = ['size'=>strlen($manifestBytes),'sha256'=>hash('sha256',$manifestBytes)];
        AssetPublication::preflight($output, $records);
        $temp = AssetPublication::temporary(); $staged = [];
        try {
            $sources = [];
            foreach ($manifest['files'] as $record) {
                $name = AssetArchive::relative($record['source']);
                $identity = ['size'=>$record['source_size'],'sha256'=>$record['source_sha256']];
                if (isset($sources[$name]) && $sources[$name] !== $identity) throw new RuntimeException('构建源记录冲突');
                $sources[$name] = $identity;
                if (($record['minify'] ?? false) || $record['source_sha256'] !== $record['sha256'] || $record['source_size'] !== $record['size']) throw new RuntimeException('PHP 原字节构建不接受外部加工配方');
            }
            $paths = [];
            if ($input === null) {
                AssetArchive::read($this->root . '/app/cms/resources/asset-build-inputs.tar.gz', $manifest['build_sources_sha256'], $sources,
                    static function(string $name, string $bytes) use ($temp, &$paths): void { $paths[$name] = AssetPublication::stage($temp.'/input', $name, $bytes); });
            } else foreach ($sources as $name=>$record) {
                $path = AssetPublication::target($input, $name);
                if (!AssetPublication::matches($path, $record)) throw new RuntimeException('构建输入校验失败：'.$name);
                $paths[$name] = $path;
            }
            foreach ($manifest['files'] as $record) {
                $name = $record['target'];
                if (!isset($staged[$name])) $staged[$name] = AssetPublication::stage($temp.'/output', $name, (string)file_get_contents($paths[$record['source']]));
            }
            $staged['app/cms/resources/asset-manifest.json'] = AssetPublication::stage($temp.'/output','app/cms/resources/asset-manifest.json',$manifestBytes);
            $created = AssetPublication::publish($output, $records, $staged);
            return ['ok'=>true,'mode'=>'built','engine'=>'php','unique_targets'=>count($records)-1,'created_files'=>$created,'manifest_unchanged'=>true];
        } finally { AssetPublication::remove($temp); }
    }

    public function restore(array $options, bool $apply = false): array
    {
        $this->sourceOnly();
        if (empty($options['archive'])) throw new InvalidArgumentException('restore 需要 --archive 指定 format=2 资源包');
        $archive = AssetArchive::path($options['archive']);
        $destination = AssetArchive::path($options['destination'] ?: $this->root);
        if (!is_dir($destination)) throw new RuntimeException('恢复目标目录必须存在');
        $path = $this->root . '/app/cms/resources/source-package-assets.json.gz';
        $this->noLinks($path);
        $bytes = @gzdecode((string)file_get_contents($path), 16777216);
        if ($bytes === false) throw new RuntimeException('资源恢复清单损坏');
        $manifest = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
        if (($manifest['format'] ?? null) !== 2 || !is_array($manifest['files'] ?? null) || !$manifest['files'] || !$this->sha($manifest['archive']['sha256'] ?? null)) throw new RuntimeException('资源恢复清单格式无效');
        $records = $manifest['files'];
        foreach ($records as $name=>&$record) {
            $this->assetTarget($name); $this->record($record);
        }
        unset($record);
        if (isset($manifest['archive']['bytes']) && filesize($archive) !== $manifest['archive']['bytes']) throw new RuntimeException('资源归档大小不一致');
        $pending = AssetPublication::preflight($destination, $records);
        $temp = $apply ? AssetPublication::temporary() : null; $staged = [];
        try {
            AssetArchive::read($archive, $manifest['archive']['sha256'], $records,
                static function(string $name, string $bytes) use($apply,$temp,&$staged): void { if ($apply) $staged[$name] = AssetPublication::stage($temp,$name,$bytes); });
            $created = $apply ? AssetPublication::publish($destination,$records,$staged) : 0;
            return ['ok'=>true,'mode'=>$apply?'restored':'checked','engine'=>'php','format'=>2,'verified_files'=>count($records),'pending_files'=>count($pending),'created_files'=>$created];
        } finally { if ($temp !== null) AssetPublication::remove($temp); }
    }

    /** Create a same-version resource transport and its self-contained restoration manifest. */
    public function pack(array $options, bool $apply = false): array
    {
        $this->sourceOnly();
        $inspection = $this->inspect(true);
        if (!$inspection['ok']) throw new RuntimeException('资源完整性检查失败');
        $records = $this->manifestFiles();
        if (empty($options['archive'])) throw new InvalidArgumentException('pack 需要 --archive 指定新资源包位置');
        $archive = AssetArchive::path($options['archive']); $manifestPath = $archive . '.manifest.json.gz';
        if (str_starts_with($archive,$this->root.'/')) throw new RuntimeException('资源包输出必须位于项目外');
        foreach ([$archive,$manifestPath] as $path) if (file_exists($path)) throw new RuntimeException('资源包或配套清单已存在，拒绝覆盖');
        if (!$apply) return ['ok'=>true,'mode'=>'pack_check','engine'=>'php','files'=>count($records),'archive'=>$archive];
        $files = [];
        foreach ($records as $name=>$record) $files[$name] = $this->mapped ? Paths::logical($name) : $this->root.'/'.$name;
        $metadataCreated=false;
        try {
            AssetArchive::write($archive,$files);
            $manifest = json_decode((string)file_get_contents($this->root.'/app/cms/resources/asset-manifest.json'),true,512,JSON_THROW_ON_ERROR);
            $payload = ['format'=>2,'revision'=>$manifest['revision'],'archive'=>['name'=>basename($archive),'bytes'=>filesize($archive),'sha256'=>hash_file('sha256',$archive)],'files'=>$records];
            // Verify transport bytes against the frozen records before it is deliverable.
            AssetArchive::read($archive,$payload['archive']['sha256'],$records,static function():void {});
            $bytes = gzencode(json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),9);
            $out = @fopen($manifestPath,'xb'); if (!$out) throw new RuntimeException('无法创建配套资源清单');
            $metadataCreated=true;
            try { chmod($manifestPath,0600); if (fwrite($out,$bytes)!==strlen($bytes)) throw new RuntimeException('配套资源清单写入失败'); } finally { fclose($out); }
            return ['ok'=>true,'mode'=>'packed','engine'=>'php','files'=>count($records),'archive'=>$archive,'manifest'=>$manifestPath,'sha256'=>$payload['archive']['sha256']];
        } catch (\Throwable $error) { if (is_file($archive)) unlink($archive); if ($metadataCreated && is_file($manifestPath)) unlink($manifestPath); throw $error; }
    }
    private function sourceOnly(): void
    {
        if (\Phar::running(false)) throw new RuntimeException('资源构建、打包和恢复仅在源码环境执行；二进制使用同版本发布包');
    }
    private function manifestFiles(): array
    {
        $manifest = json_decode((string)file_get_contents($this->root.'/app/cms/resources/asset-manifest.json'),true,512,JSON_THROW_ON_ERROR);
        if (($manifest['format']??null)!==2 || !is_array($manifest['files']??null) || !$manifest['files']) throw new RuntimeException('资源清单格式无效');
        $files=[];
        foreach ($manifest['files'] as $item) {
            $name=$this->assetTarget($item['target']??''); $record=['size'=>$item['size']??null,'sha256'=>$item['sha256']??null,'mode'=>0644]; $this->record($record);
            if (isset($files[$name]) && $files[$name]!==$record) throw new RuntimeException('资源目标记录冲突');
            $files[$name]=$record;
        }
        ksort($files); return $files;
    }
    private function record(array &$record): void
    {
        $size=$record['size']??$record['bytes']??null; $mode=$record['mode']??0644;
        if (!is_int($size)||$size<0||$size>67108864||!$this->sha($record['sha256']??null)||!is_int($mode)||$mode<0||$mode>0777) throw new RuntimeException('资源文件元数据无效');
        if (isset($record['size'],$record['bytes']) && $record['size']!==$record['bytes']) throw new RuntimeException('资源文件大小字段冲突');
        $record['size']=$size; $record['mode']=$mode;
    }
    private function assetTarget(string $name): string
    {
        $this->relative($name);
        if (!$this->inRoots($name,array_merge(self::PUBLIC_ROOTS,self::PRIVATE_ROOTS)) || in_array(strtolower(pathinfo($name,PATHINFO_EXTENSION)),self::EXECUTABLE,true)) throw new RuntimeException('资源目标超出允许范围');
        return $name;
    }
    private function relative(mixed $path): string
    {
        if (!is_string($path) || $path === '' || str_starts_with($path, '/') || str_contains($path, '\\') || preg_match('/[\x00-\x1f\x7f]/', $path)) throw new RuntimeException('资源相对路径无效');
        foreach (explode('/', $path) as $part) if ($part === '' || $part === '.' || $part === '..' || str_starts_with($part, '.')) throw new RuntimeException('资源相对路径无效');
        return $path;
    }
    private function url(mixed $url): void
    {
        if (!is_string($url) || !str_starts_with($url, '/') || str_starts_with($url, '//') || strpbrk($url, "?#\\\r\n\0") !== false) throw new RuntimeException('资源 URL 无效');
        $decoded = $url;
        for ($i=0;$i<3;$i++) { $next=rawurldecode($decoded); if ($next===$decoded) break; $decoded=$next; }
        $this->relative(substr($decoded, 1));
    }
    private function inRoots(string $path, array $roots): bool { foreach ($roots as $root) if (str_starts_with($path, $root)) return true; return false; }
    private function sha(mixed $value): bool { return is_string($value) && preg_match('/^[a-f0-9]{64}$/D', $value) === 1; }
    private function absolute(string $path): void { if (!str_starts_with($path, '/')) throw new InvalidArgumentException('维护输入输出必须为绝对路径'); $this->noLinks($path); }
    private function noLinks(string $path): void
    {
        if(str_starts_with($path,'phar://') && ($path===Paths::code() || str_starts_with($path,Paths::code().'/')))return;
        $current='';foreach(explode('/',ltrim($path,'/'))as$part){if($part===''||$part==='.'||$part==='..')throw new RuntimeException('维护路径无效');$current.='/'.$part;if(is_link($current))throw new RuntimeException('资源路径不能包含符号链接：'.$current);}
    }
}
