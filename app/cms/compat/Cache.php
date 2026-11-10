<?php
namespace app\cms\compat;

/** Files are shared across workers; no request values are retained statically. */
final class Cache
{
    private const OBJECT_CLASSES = [\stdClass::class,Collection::class,\app\cms\compat\paginator\Collection::class,
        Paginator::class,\app\cms\compat\paginator\driver\Eyou::class,\app\cms\compat\paginator\driver\Mobile::class,
        \app\cms\compat\paginator\driver\Userseyou::class,\app\cms\compat\paginator\driver\Usersmobile::class];
    private static function supportedValue($value,?\SplObjectStorage $seen=null,int $depth=0):bool {
        if($depth>128)return false;
        $seen??=new \SplObjectStorage();
        if(is_object($value)) {
            if(!in_array(get_class($value),self::OBJECT_CLASSES,true))return false;
            if($seen->contains($value))return true;
            $seen->attach($value);
            $value=(array)$value;
        }
        if(is_array($value))foreach($value as $item)if(!self::supportedValue($item,$seen,$depth+1))return false;
        return !is_resource($value);
    }
    private static function directory(): string { return runtime_path('cms/cache'); }
    private static function securityScope(): string { return \app\cms\template\TemplateMode::current().':'.\app\cms\template\TemplateDialect::VERSION.':'.\app\cms\template\TemplateMode::siteSignature(); }
    private static function generation(): string { return \app\cms\cache\CachePolicy::cacheGeneration(); }
    private static function path(string $key): string {
        // The upstream cache was designed around a single HTTP application
        // context. Include presentation/request configuration so URL-bearing
        // values never cross domains, languages, devices, themes or routes.
        // The two bootstrap registries contain only shared database metadata.
        if(!in_array($key,['extra_global_channeltype','foreign_open_mobile_format'],true)) {
            $context=Runtime::context();
            $scope=[$context->origin(),$context->lang,$context->device,$context->theme,$context->site,
                $context->module,$context->controller,$context->action,$context->tid,$context->aid,
                $context->parameters,$context->config,$context->global];
            $key=hash('sha256',serialize($scope)).':'.$key;
        }
        return self::directory().'/'.self::generation().'-'.hash('sha256',$key).'.cache';
    }
    public static function get($key,$default=false) {
        \app\cms\security\SignedEnvelope::requireKey();
        $path=self::path((string)$key);
        $raw=\app\cms\cache\CacheStorage::read($path);
        if($raw===null)return $default;
        $verified=\app\cms\security\SignedEnvelope::open('cache.data',self::securityScope().':'.$path,$raw);
        if($verified===null)return $default;
        try {$data=@unserialize($verified,['allowed_classes'=>self::OBJECT_CLASSES]);}
        catch(\Throwable $error){return $default;}
        if(!is_array($data)||!array_key_exists('expires',$data)||!array_key_exists('value',$data)||($data['expires']>0&&$data['expires']<time())) { return $default; }
        return self::supportedValue($data['value'])?$data['value']:$default;
    }
    public static function set($key,$value,$expire=null): bool {
        if(!self::supportedValue($value))return false;
        $expire=is_numeric($expire)?(int)$expire:3600;
        $path=self::path((string)$key);
        $expires=$expire>0?time()+$expire:0;
        $signed=\app\cms\security\SignedEnvelope::seal('cache.data',self::securityScope().':'.$path,serialize(['expires'=>$expires,'value'=>$value]),$expires);
        \app\cms\cache\CacheStorage::write($path,$signed,$expire > 0 ? $expire : 0);
        return true;
    }
    public static function has($key): bool { $missing=new \stdClass();return self::get($key,$missing)!==$missing; }
    public static function rm($key): bool { $p=self::path((string)$key);return \app\cms\cache\CacheStorage::remove($p); }
    public static function delete($key): bool {return self::rm($key);}
    public static function clear(...$args): bool {
        \app\cms\cache\CacheStorage::clear();
        return true;
    }
    public static function tag(...$args): self {return new self();}
}
