<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\repository\CmsRepository;
use InvalidArgumentException;
use RuntimeException;

/** Manual normal-indexing submission. The destination is fixed; tests inject a transport. */
class BaiduPushService extends SitemapService
{
    private const TOKEN = 'baidu_push_token';
    public function __construct(?CmsRepository $repository = null, private ?\Closure $transport = null)
    {
        parent::__construct($repository);
    }

    public function status(array $input): array
    {
        $lang = $this->language($input);
        $root = $this->configuredSite($lang, false);
        return ['lang'=>$lang, 'site'=>$root, 'configured'=>$this->token($lang) !== '', 'limit'=>500];
    }

    public function savePush(array $input): array
    {
        $lang = $this->language($input);
        $value = $input['token'] ?? '';
        if (!is_string($value)) throw new InvalidArgumentException('Token格式错误');
        $value = trim($value);
        $clear = $input['clear_token'] ?? '0';
        if (!is_scalar($clear) || !in_array((string)$clear,['0','1'],true)) throw new InvalidArgumentException('清除Token参数错误');
        if ($value !== '' && !preg_match('/^[a-zA-Z0-9_-]{8,200}$/D',$value)) throw new InvalidArgumentException('Token应为百度提供的8至200位字母、数字、下划线或连字符');
        if ((string)$clear === '1' && $value !== '') throw new InvalidArgumentException('清除Token时请保持输入为空');
        // Empty input preserves the secret, and reads never return it.
        if ($value === '' && (string)$clear !== '1') return $this->status(['lang'=>$lang]);
        $this->repository->transaction(function (CmsRepository $r) use ($lang,$value) {
            if (!$r->table('language')->where('mark',$lang)->lock(true)->find()) throw new InvalidArgumentException('语言不存在');
            $rows=$r->table('config')->where(['name'=>self::TOKEN,'lang'=>$lang])->lock(true)->select()->toArray();
            if (count($rows)>1) throw new InvalidArgumentException('百度推送配置重复，请先处理');
            if ($rows) $r->table('config')->where('id',$rows[0]['id'])->update(['value'=>$value,'is_del'=>0,'update_time'=>time()]);
            else $r->table('config')->insert(['name'=>self::TOKEN,'value'=>$value,'inc_type'=>'baidu_push','desc'=>'百度普通收录推送Token','lang'=>$lang,'is_del'=>0,'update_time'=>time()]);
        });
        return $this->status(['lang'=>$lang]);
    }

    public function preview(array $input): array
    {
        [$lang,$site,$urls,$offset,$limit] = $this->selection($input);
        return ['lang'=>$lang,'site'=>$site,'total'=>count($urls),'offset'=>$offset,'limit'=>$limit,'selected'=>count(array_slice($urls,$offset,$limit)), 'urls'=>array_slice($urls,$offset,min(10,$limit))];
    }

    public function push(array $input): array
    {
        [$lang,$site,$urls,$offset,$limit] = $this->selection($input);
        $token=$this->token($lang);
        if ($token==='') throw new InvalidArgumentException('请先保存百度普通收录Token');
        $selected=array_slice($urls,$offset,$limit);
        if (!$selected) throw new InvalidArgumentException('当前范围没有可推送的公开网址');
        if (($input['confirm_http'] ?? '') !== '1') throw new InvalidArgumentException('百度官方推送接口使用HTTP传输Token和网址，请先阅读并勾选提交确认');
        $send=$this->transport ?? fn($site,$token,$body)=>$this->requestBaidu($site,$token,$body);
        try { $response=$send($site,$token,implode("\n",$selected)); }
        catch (\Throwable) { throw new RuntimeException('连接百度推送接口失败或超时；提交结果未知，请先到百度平台核对后再重试'); }
        $data=json_decode((string)($response['body']??''),true);
        if (!is_array($data)) throw new RuntimeException('百度接口返回无法识别的响应；提交结果未知，请到百度平台核对');
        if (isset($data['error'])) {
            $error=is_scalar($data['error'])?(string)$data['error']:'';
            $reason=match($error){'401'=>'Token或站点验证失败，请核对百度资源平台的站点与Token','404'=>'百度推送接口不可用','405'=>'请求方式不被百度接口接受','422'=>'百度拒绝网址格式','429'=>'推送频率或额度受限，请稍后重试',default=>'百度拒绝本次提交，请到百度资源平台核对站点、Token与当日额度'};
            throw new RuntimeException($reason);
        }
        if ((int)($response['status']??0)!==200) throw new RuntimeException('百度接口暂不可用；提交结果未知，请到百度平台核对后重试');
        if (!isset($data['success'],$data['remain']) || !is_int($data['success']) || $data['success']<0 || $data['success']>count($selected) || !is_int($data['remain']) || $data['remain']<0) throw new RuntimeException('百度返回结果不完整，请到百度资源平台核对本次提交');
        $invalid=[];
        foreach (['not_same_site','not_valid'] as $key) {
            if (isset($data[$key]) && !is_array($data[$key])) throw new RuntimeException('百度返回网址明细格式异常，请到百度平台核对');
            $invalid[$key]=array_values(array_intersect($selected,array_filter($data[$key]??[],'is_string')));
        }
        return ['submitted'=>count($selected),'accepted'=>$data['success'],'unaccepted'=>count($selected)-$data['success'],'remaining'=>$data['remain'],'offset'=>$offset,'next_offset'=>$offset+count($selected),'total'=>count($urls),'not_same_site'=>$invalid['not_same_site'],'not_valid'=>$invalid['not_valid'],'message'=>'接口接收数量不代表已收录；实际收录以百度平台为准'];
    }

    private function selection(array $input): array
    {
        $lang=$this->language($input);$root=$this->configuredSite($lang,true);
        $offset=$this->integer($input['offset']??'0',0,1000000,'起始位置');
        $limit=$this->integer($input['limit']??'500',1,2000,'单次推送数量');
        $site=$this->site($lang,'pc',$root);
        $links=$this->links($site);$urls=[];
        foreach($links as$link){$url=(string)$link['url'];$parts=parse_url($url);if(!$parts||!in_array($parts['scheme']??'',['http','https'],true)||isset($parts['user'],$parts['pass'])||isset($parts['user'])||isset($parts['port'])||isset($parts['fragment'])||preg_match('/[\x00-\x20\x7f]/',$url))continue;
            $origin=($parts['scheme']??'').'://'.strtolower($parts['host']??'');
            if($origin!==$root)continue;$urls[$url]=$url;
        }
        return [$lang,$root,array_values($urls),$offset,$limit];
    }

    private function configuredSite(string $lang,bool $required): string
    {
        $value=$this->repository->table('config')->where(['name'=>'web_basehost','lang'=>$lang,'is_del'=>0])->value('value');
        $value=is_string($value)?trim($value):'';if($value!==''&&!str_contains($value,'://'))$value='https://'.$value;
        $p=parse_url($value);
        $valid=$p&&in_array($p['scheme']??'',['http','https'],true)&&!isset($p['user'])&&!isset($p['pass'])&&!isset($p['port'])&&!isset($p['query'])&&!isset($p['fragment'])&&in_array($p['path']??'',['','/'],true)&&preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/iD',$p['host']??'');
        if(!$valid){if($required)throw new InvalidArgumentException('请先在基本设置填写当前语言的公开网站根网址，例如 https://www.example.com（不能使用本地地址、端口或路径）');return '';}
        return strtolower($p['scheme'].'://'.$p['host']);
    }

    private function token(string $lang): string
    {
        $rows=$this->repository->table('config')->where(['name'=>self::TOKEN,'lang'=>$lang,'is_del'=>0])->select()->toArray();
        if(count($rows)>1)throw new InvalidArgumentException('百度推送配置重复，请先处理');
        return (string)($rows[0]['value']??'');
    }
    private function language(array $input): string
    {
        $lang=$input['lang']??CmsContext::current()->lang;
        if(!is_string($lang)||!preg_match('/^[a-z][a-z0-9_-]{1,49}$/iD',$lang))throw new InvalidArgumentException('语言参数错误');
        (new LanguageAccessService($this->repository))->assertLanguages([$lang]);
        if(!$this->repository->table('language')->where('mark',$lang)->find())throw new InvalidArgumentException('语言不存在');
        return $lang;
    }
    private function integer(mixed $value,int $min,int $max,string $title): int
    {
        if(!is_scalar($value)||!preg_match('/^\d{1,7}$/D',(string)$value)||(int)$value<$min||(int)$value>$max)throw new InvalidArgumentException($title.'超出范围');return (int)$value;
    }
    private function requestBaidu(string $site,string $token,string $body): array
    {
        if(!function_exists('curl_init'))throw new RuntimeException('服务器缺少cURL扩展');
        $ch=curl_init('http://data.zz.baidu.com/urls?'.http_build_query(['site'=>$site,'token'=>$token]));$output='';
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Content-Type: text/plain'],CURLOPT_CONNECTTIMEOUT=>4,CURLOPT_TIMEOUT=>12,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTP,CURLOPT_WRITEFUNCTION=>static function($ch,string $chunk)use(&$output):int{if(strlen($output)+strlen($chunk)>1048576)return 0;$output.=$chunk;return strlen($chunk);}]);
        try{if(curl_exec($ch)===false)throw new RuntimeException('百度连接失败');return ['status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'body'=>$output];}finally{curl_close($ch);}
    }
}
