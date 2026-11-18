<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Public, fixed-repository HTTPS transport; no publishing credentials at runtime. */
final class GithubReleases
{
    private \GuzzleHttp\Client $client;
    public function __construct(?\GuzzleHttp\Client $client = null)
    {
        $this->client = $client ?? new \GuzzleHttp\Client(['connect_timeout'=>4,'timeout'=>12,'allow_redirects'=>false,'headers'=>['User-Agent'=>'webman-cms-updater','Accept'=>'application/vnd.github+json']]);
    }
    private function json(string $url): array
    {
        for ($redirect=0; $redirect<6; $redirect++) {
            $parts=parse_url($url);
            if (($parts['scheme']??'')!=='https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || !in_array($parts['host']??'', ['api.github.com','github.com','release-assets.githubusercontent.com','objects.githubusercontent.com'],true)) throw new \RuntimeException('GitHub 清单下载地址不可信');
            $response = $this->client->get($url,['allow_redirects'=>false]);
            if (!in_array($response->getStatusCode(),[301,302,303,307,308],true)) break;
            $url=$response->getHeaderLine('Location');
        }
        if ($response->getStatusCode() !== 200) throw new \RuntimeException('GitHub 响应异常');
        $body = $response->getBody()->read(8388609);
        if (strlen($body) > 8388608) throw new \RuntimeException('GitHub 清单响应过大');
        return json_decode($body,true,512,JSON_THROW_ON_ERROR);
    }
    public function latest(): ?array
    {
        $releases = $this->json('https://api.github.com/repos/'.ReleaseManifest::REPOSITORY.'/releases?per_page=100');
        $stable = [];
        foreach ($releases as $release) {
            if (!is_array($release) || !empty($release['draft']) || !empty($release['prerelease'])) continue;
            try { $version = ReleaseManifest::version((string)$release['tag_name']); } catch (\Throwable) { continue; }
            $release['cms_version'] = $version; $stable[] = $release;
        }
        usort($stable,static fn($a,$b)=>version_compare($b['cms_version'],$a['cms_version']));
        if (!$stable) return null;
        $release = $stable[0];
        $manifestAsset = self::asset($release, ReleaseManifest::NAME);
        $manifest = ReleaseManifest::validate($this->json(self::url($release,$manifestAsset)),$release['tag_name']);
        foreach ($manifest['packages'] as $package) {
            $asset=self::asset($release,$package['name']);
            if (($asset['size']??null)!==$package['size']) throw new \RuntimeException('GitHub 资产大小与清单不符');
        }
        return ['provider'=>'github','id'=>(string)$release['id'],'tag'=>$release['tag_name'],'version'=>$release['cms_version'],'published_at'=>$release['published_at']??'','notes'=>mb_substr((string)($release['body']??''),0,50000),'manifest'=>$manifest,'assets'=>$release['assets']];
    }
    public static function asset(array $release,string $name): array
    {
        $found=array_values(array_filter($release['assets']??[],static fn($a)=>($a['name']??'')===$name && ($a['state']??'')==='uploaded'));
        if (count($found)!==1) throw new \RuntimeException('正式发布缺少唯一的升级资产：'.$name);
        return $found[0];
    }
    public static function url(array $release,array $asset): string
    {
        $tag=$release['tag_name']??$release['tag'];
        ReleaseManifest::version($tag);
        $expected='https://github.com/'.ReleaseManifest::REPOSITORY.'/releases/download/'.rawurlencode($tag).'/'.rawurlencode($asset['name']);
        if (($asset['browser_download_url']??'')!==$expected) throw new \RuntimeException('升级资产不是固定仓库的正式下载地址');
        return $expected;
    }
}
