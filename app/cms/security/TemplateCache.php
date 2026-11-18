<?php
declare(strict_types=1);
namespace app\cms\security;

/** Template metadata and render-local byte snapshots; no signing secret. */
final class TemplateCache
{
    private const PREFIX = "CMS-TEMPLATE-2\n";
    private array $verified = [];
    public function __construct(private string $purpose, private string $scope, private int $lifetime = 0) {}
    private function context(string $path): string { return hash('sha256',$this->purpose."\n".$this->scope."\n".$path); }
    public function write(string $path, string $bytes): void
    {
        if(strlen($bytes)>RuntimeFiles::MAX_BYTES)throw new \RuntimeException('模板缓存超过大小限制');
        $meta=['context'=>$this->context($path),'expires'=>$this->lifetime>0?time()+$this->lifetime:0,'size'=>strlen($bytes)];
        \app\cms\cache\CacheStorage::write($path, self::PREFIX.json_encode($meta,JSON_THROW_ON_ERROR)."\n".$bytes, preg_match('/\.render-[a-f0-9]{32}$/D', $path) ? 60 : $this->lifetime);
        $this->verified[$path] = $bytes;
    }
    public function read(string $path): ?string
    {
        if (isset($this->verified[$path])) return $this->verified[$path];
        // Backend PHP must come from this render's freshly compiled trusted source.
        if($this->purpose!=='template.front')return null;
        $raw = \app\cms\cache\CacheStorage::read($path);
        if($raw===null||!str_starts_with($raw,self::PREFIX))return null;
        $parts=explode("\n",substr($raw,strlen(self::PREFIX)),2);
        if(count($parts)!==2||strlen($parts[0])>2048||strlen($parts[1])>RuntimeFiles::MAX_BYTES)return null;
        try{
            $meta=json_decode($parts[0],true,8,JSON_THROW_ON_ERROR);
            if(!is_array($meta)||($meta['context']??null)!==$this->context($path)||($meta['size']??null)!==strlen($parts[1])||!is_int($meta['expires']??null)||($meta['expires']!==0&&$meta['expires']<=time()))return null;
            $end=strpos($parts[1],"\n");if($end===false)return null;
            $header=substr($parts[1],0,$end);
            if(\app\cms\template\TemplateDependencies::decode($header)===null)return null;
            \app\cms\template\CompiledTemplatePolicy::validate(substr($parts[1],$end+1));
            return $this->verified[$path]=$parts[1];
        }catch(\Throwable){return null;}
    }
}
