<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Shared by the server and the frozen executor; never accepts a caller-selected origin. */
final class ReleaseDownload
{
    public static function assertCustomUrl(string $url,string $source): void
    {
        $parts=parse_url($url);$base=parse_url($source);
        if(!$parts||!$base||($parts['scheme']??'')!=='https'||empty($parts['host'])||strtolower($parts['host'])!==strtolower($base['host']??'')||isset($parts['user'])||isset($parts['pass'])||isset($parts['fragment'])||isset($parts['port'])||preg_match('/[\x00-\x20\x7f\\\\]/',$url))throw new \RuntimeException('自定义升级地址必须是配置源的同源 HTTPS 地址');
    }
    public static function assertUrl(string $url,string $provider): void
    {
        $parts=parse_url($url);
        $hosts=match($provider){'gitee'=>['gitee.com','foruda.gitee.com'],'github'=>['api.github.com','github.com','release-assets.githubusercontent.com','objects.githubusercontent.com'],default=>throw new \RuntimeException('未知升级中心')};
        if(!$parts||($parts['scheme']??'')!=='https'||isset($parts['user'])||isset($parts['pass'])||isset($parts['port'])||isset($parts['fragment'])||!in_array($parts['host']??'',$hosts,true))throw new \RuntimeException('升级下载地址或重定向不可信');
    }
    public static function redirect(string $base,string $location): string
    {
        if($location===''||preg_match('/[\x00-\x20\x7f\\\\]/',$location))throw new \RuntimeException('升级下载重定向无效');
        if(str_starts_with($location,'https://'))return $location;
        if(str_starts_with($location,'//'))return 'https:'.$location;
        if(str_starts_with($location,'/'))return 'https://'.parse_url($base,PHP_URL_HOST).$location;
        throw new \RuntimeException('升级下载重定向必须为 HTTPS 或根路径');
    }
    public static function expected(array $release,array $package): string
    {
        $provider=$release['provider']??'github';
        if($provider==='gitee')return GiteeReleases::url($release,GiteeReleases::asset($release,$package['name']));
        if($provider==='github')return GithubReleases::url($release,GithubReleases::asset($release,$package['name']));
        if($provider==='custom'){
            $assets=array_values(array_filter($release['assets']??[],static fn($a)=>is_array($a)&&($a['name']??'')===$package['name']));
            if(count($assets)!==1)throw new \RuntimeException('自定义发布缺少唯一升级资产');
            $url=(string)($assets[0]['url']??'');self::assertCustomUrl($url,(string)($release['source_url']??''));return $url;
        }
        throw new \RuntimeException('未知升级中心');
    }
}
