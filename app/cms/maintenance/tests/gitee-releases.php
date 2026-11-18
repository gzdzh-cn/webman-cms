<?php
declare(strict_types=1);
require dirname(__DIR__,4).'/vendor/autoload.php';
use app\cms\maintenance\{GiteeReleases,GiteeReleasePublisher,ReleaseBuilder,ReleaseDownload,ReleaseManifest};
use GuzzleHttp\{Client,HandlerStack,Middleware};
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

$count=0;
function expect(bool $value,string $label): void {global $count;if(!$value)throw new RuntimeException($label);$count++;}
function rejected(callable $action,string $label): void {try{$action();}catch(Throwable){expect(true,$label);return;}throw new RuntimeException($label);}
$files=[];foreach(['webman','composer.lock','vendor/autoload.php','app/cms/resources/backend-provenance.json']as$name)$files[$name]=['size'=>1,'sha256'=>str_repeat('a',64),'mode'=>0644];
$package=['name'=>'webman-cms-1.0.10-source.tar.gz','kind'=>'source','size'=>123,'sha256'=>str_repeat('a',64),'files'=>$files];
$manifest=['format'=>1,'repository'=>ReleaseManifest::REPOSITORY,'version'=>'1.0.10','min_version'=>'1.0.7','max_version'=>'1.0.10','min_php'=>'8.2.0','migration'=>'backward-compatible-additive','packages'=>[$package]];
$stable=['id'=>20,'tag_name'=>'v1.0.10','created_at'=>'2026-11-18T12:00:00+08:00','body'=>'更新说明','prerelease'=>false];
$assets=[['id'=>30,'name'=>ReleaseManifest::NAME,'size'=>strlen(json_encode($manifest))],['id'=>31,'name'=>$package['name'],'size'=>123]];
function checker(array $responses,array &$history): GiteeReleases {$stack=HandlerStack::create(new MockHandler($responses));$stack->push(Middleware::history($history));return new GiteeReleases(new Client(['handler'=>$stack,'http_errors'=>false]));}
function jsonResponse(mixed $value): Response {return new Response(200,[],json_encode($value,JSON_THROW_ON_ERROR));}
$history=[];$service=checker([jsonResponse([array_replace($stable,['tag_name'=>'v9.0.0','prerelease'=>true]),array_replace($stable,['tag_name'=>'v8.0.0','draft'=>true]),array_replace($stable,['tag_name'=>'nightly']),array_replace($stable,['tag_name'=>'v1.0.9']),$stable]),jsonResponse($assets),new Response(302,['Location'=>'https://foruda.gitee.com/attach_file/test/manifest.json']),jsonResponse($manifest)],$history);
$release=$service->latest();expect($release['version']==='1.0.10'&&$release['provider']==='gitee','highest stable version');expect($release['notes']==='更新说明','notes preserved');expect(count($history)===4,'expected requests');expect((string)$history[2]['request']->getUri()===GiteeReleases::API.'/releases/20/attach_files/30/download','fixed manifest endpoint');expect(ReleaseDownload::expected($release,$package)===GiteeReleases::API.'/releases/20/attach_files/31/download','fixed package endpoint');
$history=[];expect(checker([jsonResponse([])],$history)->latest()===null,'no release');
$history=[];rejected(fn()=>checker([new Response(503)],$history)->latest(),'network response rejected');
$history=[];rejected(fn()=>checker([jsonResponse([$stable]),jsonResponse(array_merge($assets,[$assets[0]]))],$history)->latest(),'duplicate manifest rejected');
$wrong=$manifest;$wrong['version']='1.0.9';$history=[];rejected(fn()=>checker([jsonResponse([$stable]),jsonResponse($assets),jsonResponse($wrong)],$history)->latest(),'wrong version rejected');
$wrongAssets=$assets;$wrongAssets[1]['size']=124;$history=[];rejected(fn()=>checker([jsonResponse([$stable]),jsonResponse($wrongAssets),jsonResponse($manifest)],$history)->latest(),'wrong size rejected');
$history=[];rejected(fn()=>checker([new Response(302,['Location'=>'https://example.com/manifest'])],$history)->latest(),'redirect rejected before request');
foreach(['http://gitee.com/file','https://gitee.com.evil.test/file','https://gitee.com@evil.test/file','https://gitee.com:443/file','https://gitee.com/file#x','https://api.github.com/file','https://127.0.0.1/file']as$url)rejected(fn()=>ReleaseDownload::assertUrl($url,'gitee'),'untrusted URL rejected');
expect(ReleaseDownload::redirect('https://gitee.com/a','/b')==='https://gitee.com/b','root redirect');rejected(fn()=>ReleaseDownload::redirect('https://gitee.com/a',"/b\r\nHost: evil"),'header injection rejected');
$old=['tag'=>'v1.0.9','assets'=>[['name'=>'package.tar.gz','state'=>'uploaded','browser_download_url'=>'https://github.com/'.ReleaseManifest::REPOSITORY.'/releases/download/v1.0.9/package.tar.gz']]];expect(ReleaseDownload::expected($old,['name'=>'package.tar.gz'])===$old['assets'][0]['browser_download_url'],'existing GitHub jobs remain valid');
expect(ReleaseManifest::package($manifest,['kind'=>'source'])===$package,'source selection');expect(ReleaseManifest::package($manifest,['kind'=>'binary','custom'=>true])===null,'custom binary rejected');
$reflection=new ReflectionClass(app\cms\service\AdminUpdateService::class);$identity=$reflection->getMethod('identity')->invoke(null,$release);expect($identity!==$release['id'].':'.hash('sha256',json_encode($manifest,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)),'Gitee identity differs from GitHub');
putenv('GITEE_TOKEN=test-only');$history=[];$stack=HandlerStack::create(new MockHandler([jsonResponse(['id'=>20,'prerelease'=>true,'tag_name'=>'v1.0.10','name'=>'version','body'=>'notes']),jsonResponse($assets),jsonResponse($wrong)]));$stack->push(Middleware::history($history));$publisher=new GiteeReleasePublisher(new Client(['handler'=>$stack]));rejected(fn()=>$publisher->finalize('20'),'publisher rejects mismatched manifest');expect(count($history)===3,'publisher never finalizes unverified preview');putenv('GITEE_TOKEN');
echo json_encode(['ok'=>true,'assertions'=>$count],JSON_THROW_ON_ERROR),PHP_EOL;
