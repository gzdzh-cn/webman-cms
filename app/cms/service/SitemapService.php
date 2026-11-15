<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\compat\Runtime;
use app\cms\repository\CmsRepository;
use support\Request;
use support\Response;

/** Enterprise maps use the original category/document URL helpers and configuration keys. */
class SitemapService extends StaticGenerationService
{
    public const DEFAULTS = ['sitemap_xml'=>1,'sitemap_txt'=>1,'sitemap_html'=>1,'ai_content_index_html'=>0,'sitemap_auto'=>0,'sitemap_not1'=>0,'sitemap_not2'=>1,'sitemap_archives_num'=>1000,'sitemap_tags_num'=>1000,
        'sitemap_changefreq_index'=>'always','sitemap_changefreq_list'=>'hourly','sitemap_changefreq_view'=>'daily','sitemap_priority_index'=>'1.0','sitemap_priority_list'=>'0.8','sitemap_priority_view'=>'0.5'];
    public function __construct(?CmsRepository $repository=null,?string $directory=null) { parent::__construct($repository,$directory??'sitemap'); }

    public function listing(array $input=[]):array
    {
        $allowed=(new LanguageAccessService($this->repository))->allowedLanguages();
        $languages=array_values(array_filter($this->languages(),static fn($language)=>in_array($language['mark'],$allowed,true)));$lang=(string)($input['lang']??CmsContext::current()->lang);
        if(!in_array($lang,array_column($languages,'mark'),true))throw new \InvalidArgumentException('请选择有效语言');
        $site=$this->site($lang,'pc');$files=[];
        foreach($this->manifest()['entries'] as $entry)if(in_array($entry['lang'],$allowed,true))$files[]=array_intersect_key($entry,array_flip(['url','format','lang','links','generated_at','bytes']));
        return ['lang'=>$lang,'languages'=>$languages,'settings'=>$this->settings($site),'files'=>$files,'urls'=>$this->urls($site),'ai_available'=>AiContentIndexService::available($site),'ai_url'=>(new AiContentIndexService($this->repository,$this->store->root()))->url($site),'automatic_tasks'=>array_values(array_filter($this->manifest()['automatic']??[],static fn($task)=>in_array($task['lang'],$allowed,true))), 'queue_tasks'=>(new SitemapQueue($this->repository, $this->store))->summaries($allowed)];
    }

    public function save(array $input):array
    {
        $settings=$this->validateSettings($input);$languages=$this->languages();
        if(!empty($settings['ai_content_index_html'])&&!AiContentIndexService::available($this->site(CmsContext::current()->lang,'pc')))throw new \InvalidArgumentException('AI内容索引需要原服务档位 php_servicemeal >= 5');
        $selected=$input['sitemap_lang']??$input['languages']??[CmsContext::current()->lang];
        if(!is_array($selected)||!$selected||array_diff($selected,array_column($languages,'mark')))throw new \InvalidArgumentException('地图语言选择无效');
        $settings['sitemap_lang']=json_encode(array_values(array_unique($selected)),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        $allLanguages=$this->repository->table('language')->order('id asc')->select()->toArray();
        (new LanguageAccessService($this->repository))->assertLanguages(array_column($allLanguages,'mark'));
        $this->repository->transaction(function()use($settings,$allLanguages){
            foreach($allLanguages as $language){
                $locked=$this->repository->table('language')->where(['id'=>$language['id'],'mark'=>$language['mark']])->lock(true)->find();
                if(!$locked)throw new \InvalidArgumentException('语言已变更，请刷新后重试');
                foreach($settings as $name=>$value){
                    $rows=$this->repository->table('config')->where(['name'=>$name,'lang'=>$language['mark'],'is_del'=>0])->lock(true)->select()->toArray();
                    if(count($rows)>1)throw new \InvalidArgumentException('地图配置存在重复记录：'.$name);
                    if($rows)$this->repository->table('config')->where('id',$rows[0]['id'])->update(['value'=>(string)$value,'update_time'=>time()]);
                    else $this->repository->table('config')->insert(['name'=>$name,'value'=>(string)$value,'inc_type'=>'sitemap','desc'=>'网站地图','lang'=>$language['mark'],'is_del'=>0,'update_time'=>time()]);
                }
            }
        });
        try{$this->scheduleAutomatic();$warning='';}catch(\Throwable$error){$warning='设置已保存，但自动生成排队失败：'.$error->getMessage();}
        return ['settings'=>$settings,'warning'=>$warning,'message'=>$warning?:'地图设置已保存；已开启自动更新的地图会在后台生成，否则请手动生成'];
    }

    public function generate(array $input,bool $includeHomeLanguage=true):array
    {
        $data=$this->listing($input);$settings=$data['settings'];$languages=$input['languages']??$settings['sitemap_lang'];
        if(!is_array($languages)||!$languages||array_diff($languages,array_column($data['languages'],'mark')))throw new \InvalidArgumentException('地图语言选择无效');
        $languages=$includeHomeLanguage?$this->generationLanguages($languages,$this->languages()):array_values(array_unique($languages));
        (new LanguageAccessService($this->repository))->assertLanguages($languages);
        $formats=$input['formats']??array_values(array_filter(['xml','txt','html','ai'],fn($format)=>$this->formatEnabled($settings,$format)));
        if(!is_array($formats)||!array_is_list($formats)||!$formats)throw new \InvalidArgumentException('请选择有效的地图格式');
        foreach($formats as$format)if(!is_string($format)||!in_array($format,['xml','txt','html','ai'],true))throw new \InvalidArgumentException('请选择有效的地图格式');
        $formats=array_values(array_unique($formats));
        if(array_key_exists('formats',$input))foreach($formats as$format)if(!$this->formatEnabled($settings,$format))throw new \InvalidArgumentException(strtoupper($format).' 地图尚未启用，请先保存设置');
        return (new SitemapQueue($this->repository, $this->store))->enqueue($languages, $formats);
    }

    public function clean(array $input): array
    {
        return $this->store->locked(function () use ($input): array {
            (new SitemapQueue($this->repository, $this->store))->assertIdle((string)($input['lang']??''));
            return parent::clean($input);
        });
    }

    /** Internal queue already checked its stored site identity; HTTP callers authorize above. */
    public static function response(Request $request,CmsContext $context):?Response
    {
        if($ai=AiContentIndexService::response($request,$context))return $ai;
        return self::responseForPath($request,$context);
    }

    public static function responseForPath(Request $request,CmsContext $context):?Response
    {
        if(!in_array($request->method(),['GET','HEAD'],true))return null;
        $base=rtrim((string)($context->global['web_cmspath']??''),'/');$path=$request->path();
        if($base!==''&&str_starts_with($path,$base.'/'))$path=substr($path,strlen($base));
        if(preg_match('#^/ai-content-index(?:_([A-Za-z0-9_-]{2,50}))?\.html$#D',$path,$ai))$match=[0=>$ai[0],1=>$ai[1]??'',2=>'',3=>'ai'];
        elseif(!preg_match('#^/sitemap(?:_([A-Za-z0-9_-]{2,50}?))?(?:-([1-9][0-9]{0,5}))?\.(xml|txt|html)$#D',$path,$match))return null;
        $service=new self();$lang=$match[1]??'';$format=$match[3];$device='pc';
        $prefix=(string)($context->global['web_mobile_domain']??'');
        if(!empty($context->global['web_mobile_domain_open'])&&$prefix!==''&&($lang===$prefix||str_starts_with($lang,$prefix.'_'))){$device='mobile';$lang=$lang===$prefix?'':substr($lang,strlen($prefix)+1);}
        if($lang!==''&&!in_array($lang,array_column($service->languages(),'mark'),true))return response('地图语言不存在',404,['Content-Type'=>'text/plain; charset=utf-8']);
        try{$site=($lang!==''&&$lang!==$context->lang)||$device!==$context->device?$service->site($lang?:$context->lang,$device):$context;}catch(\Throwable){return response('地图语言不存在',404);}
        $settings=$service->settings($site);
        if(!$service->formatEnabled($settings,$format)||($format==='ai'&&!AiContentIndexService::available($site)))return response('地图格式已停用或服务档位不支持',404,['Content-Type'=>'text/plain; charset=utf-8']);
        {
            $entry=$service->manifest()['entries'][hash('sha256',$base.$path)]??null;
            if($entry&&($entry['signature']??'')===$service->sitemapSignature($site)){
                $bytes=$service->store->bytes($entry);if($bytes!==null)return $service->fileResponse($request->method()==='HEAD'?'':$bytes,$format,'generated');
            }
        }
        foreach($service->documents($site,$service->links($site),$format)as$document)if($document['url']===$base.$path||($format==='ai'&&$path==='/ai-content-index.html')||(empty($match[2])&&$path==='/sitemap.'.$format&&$document['url']===$service->urls($site)[$format]))return $service->fileResponse($request->method()==='HEAD'?'':$document['body'],$format,'live');
        return response('地图分片不存在',404,['Content-Type'=>'text/plain; charset=utf-8']);
    }

    public function live(CmsContext $context,string $format='xml'):Response
    {
        if(!in_array($format,['xml','txt','html'],true)||empty($this->settings($context)['sitemap_'.$format]))return response('地图格式已停用',404);
        return $this->fileResponse($this->documents($context,$this->links($context),$format)[0]['body'],$format,'live');
    }

    protected function settings(CmsContext $site):array
    {
        $settings=array_replace(self::DEFAULTS,array_intersect_key($site->global,self::DEFAULTS));$langs=$site->global['sitemap_lang']??[$site->lang];
        if(is_string($langs))$langs=json_decode($langs,true);$settings['sitemap_lang']=is_array($langs)&&$langs?$langs:[$site->lang];return $settings;
    }

    protected function links(CmsContext $site):array
    {
        $previous=CmsContext::current();CmsContext::set($site);Runtime::boot($site);
        try{
            $settings=$this->settings($site);$links=[];
            $home=$this->absolute((string)($site->global['lang_url']??(($site->global['web_cmspath']??'').'/')),$site);
            $links[]=$this->link($home,$site->global['web_name']??'',time(),'index',$settings);
            $query=$this->repository->table('arctype')->where(['lang'=>$site->lang,'is_del'=>0,'status'=>1]);
            if(!empty($settings['sitemap_not1']))$query->where('is_hidden',0);if(!empty($settings['sitemap_not2']))$query->where('is_part',0);
            $types=array_column(array_filter($query->order('sort_order asc,id asc')->select()->toArray(),static fn($row)=>empty($row['typearcrank'])),null,'id');
            foreach($types as$type){$url=(int)($type['is_part']??0)===1&&!empty($type['typelink'])?$type['typelink']:typeurl('home/Lists/index',$type,true,true);$links[]=$this->link($this->absolute($url,$site),$type['typename'],time(),'list',$settings);}
            $limit=max(0,(int)$settings['sitemap_archives_num']);
            if($limit>0&&$types){
                $query=$this->repository->table('archives')->where(['lang'=>$site->lang,'status'=>1,'is_del'=>0,'arcrank'=>0])->whereIn('channel',$site->getConfig('global.allow_release_channel',[]));
                if(!empty($settings['sitemap_not2']))$query->where('is_jump',0);
                foreach($query->order('aid desc')->limit($limit)->select()->toArray()as$archive){
                    if(!isset($types[$archive['typeid']])||(int)$types[$archive['typeid']]['current_channel']!==(int)$archive['channel'])continue;
                    $url=!empty($archive['is_jump'])&&!empty($archive['jumplinks'])?$archive['jumplinks']:arcurl('home/View/index',array_replace($types[$archive['typeid']],$archive),true,true);
                    $links[]=$this->link($this->absolute($url,$site),$archive['title'],(int)($archive['update_time']?:$archive['add_time']),'view',$settings);
                }
            }
            $tagsLimit=max(0,(int)$settings['sitemap_tags_num']);
            $pcRoot=\app\cms\ThemeCatalog::root((string)($site->global['web_tpl_theme']??'default'),'pc');
            if($tagsLimit>0&&is_file($pcRoot.'/lists_tags.htm'))foreach($this->repository->table('tagindex')->where('lang',$site->lang)->order('add_time desc')->limit($tagsLimit)->select()->toArray()as$tag){$links[]=$this->link($this->absolute(tagurl('home/Tags/lists',['tagid'=>$tag['id']],true,true),$site),(string)($tag['tag']??$tag['tagname']??''),(int)(($tag['update_time']??0)?:$tag['add_time']),'view',$settings)+['tag'=>true];}
            $mobileMode=!empty($site->global['web_mobile_domain_open'])?($site->device==='mobile'?'mobile':'pc'):(!empty($site->global['response_type'])?'htmladapt':'pc,mobile');
            foreach($links as&$link){$link['mobile_mode']=$mobileMode;if($site->device==='mobile')$link['url']=pc_to_mobile_url($link['url']);}unset($link);
            $unique=[];foreach($links as$link)if(preg_match('#^https?://[^/]+(?:/|$)#i',$link['url'])&&!preg_match('/[\x00-\x20\x7f]/',$link['url']))$unique[$link['url']]=$link;return array_values($unique);
        }finally{CmsContext::set($previous);Runtime::boot($previous);}
    }

    protected function documents(CmsContext $site,array $links,string $format):array
    {
        if($format==='ai')return [(new AiContentIndexService($this->repository,$this->store->root()))->document($site)];
        $url=$this->urls($site)[$format];
        if($format==='txt')return [['url'=>$url,'body'=>implode("\r\n",array_column($links,'url'))."\r\n",'links'=>count($links)]];
        if($format==='html'){
            $templateRoot=\app\cms\template\TemplateMode::systemRoot($site);
            $template=$templateRoot.'/sitemap.htm';
            if(is_file($template)&&!is_link($template)){
                $eyou=['seo_title'=>$this->escape($site->global['web_name']??'').'_网站地图','seo_keywords'=>'','seo_description'=>'','index'=>['url'=>$this->escape($links[0]['url']??$site->origin().'/'),'title'=>$this->escape($links[0]['title']??$site->global['web_name']??'')],'type_list'=>[],'arc_list'=>[],'tags_list'=>[],'ask_list'=>[]];
                foreach(array_slice($links,1)as$link){$group=!empty($link['tag'])?'tags_list':(($link['kind']??'')==='list'?'type_list':'arc_list');$eyou[$group][]=['url'=>$this->escape($link['url']),'title'=>$this->escape($link['title'])];}
                $previous=CmsContext::current();
                try{$cache=$this->store->locked(fn()=> $this->store->cacheDirectory());$body=(new \app\cms\template\TemplateRenderer($site,['template_root'=>$templateRoot,'cache_path'=>$cache]))->render('sitemap',['eyou'=>$eyou]);return [['url'=>$url,'body'=>$body,'links'=>count($links)]];}finally{CmsContext::set($previous);Runtime::boot($previous);}
            }
            $body='<!doctype html><html lang="'.$this->escape($site->lang).'"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>网站地图</title></head><body><main><h1>网站地图</h1><ul>';
            foreach($links as$link)$body.='<li><a href="'.$this->escape($link['url']).'">'.$this->escape($link['title']).'</a></li>';
            return [['url'=>$url,'body'=>$body.'</ul></main></body></html>','links'=>count($links)]];
        }
        $chunks=[];$current=[];$size=150;
        foreach($links as$link){$node=$this->xmlNode($link);if($current&&(count($current)>=50000||$size+strlen($node)>49*1024*1024)){$chunks[]=$current;$current=[];$size=150;}$current[]=$node;$size+=strlen($node);}
        if($current||!$chunks)$chunks[]=$current;
        $wrap=static fn($nodes)=>'<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0">'.implode('',$nodes).'</urlset>';
        if(count($chunks)===1)return [['url'=>$url,'body'=>$wrap($chunks[0]),'links'=>count($links)]];
        $documents=[];$index='<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach($chunks as$number=>$nodes){$part=substr($url,0,-4).'-'.($number+1).'.xml';$documents[]=['url'=>$part,'body'=>$wrap($nodes),'links'=>count($nodes)];$index.='<sitemap><loc>'.$this->escape($site->origin().$part).'</loc><lastmod>'.date('Y-m-d').'</lastmod></sitemap>';}
        array_unshift($documents,['url'=>$url,'body'=>$index.'</sitemapindex>','links'=>count($links)]);return $documents;
    }

    protected function urls(CmsContext $site):array
    {
        $languages=$this->languages();$home='';foreach($languages as$language)if(!empty($language['is_home_default'])){$home=$language['mark'];break;}$home=$home?:($languages[0]['mark']??$site->lang);
        $mobile=$site->device==='mobile'&&!empty($site->global['web_mobile_domain_open'])?'_'.($site->global['web_mobile_domain']??'m'):'';
        $stem=rtrim((string)($site->global['web_cmspath']??''),'/').'/sitemap'.$mobile.($site->lang===$home?'':'_'.$site->lang);return ['xml'=>$stem.'.xml','txt'=>$stem.'.txt','html'=>$stem.'.html'];
    }
    /** Original generation always includes the enabled homepage language. */
    protected function generationLanguages(array $selected,array $languages):array
    {
        $home=$languages[0]['mark']??null;
        foreach($languages as$language)if(!empty($language['is_home_default'])){$home=$language['mark'];break;}
        if($home!==null)$selected[]=$home;
        return array_values(array_unique($selected));
    }
    protected function validateSettings(array $input):array
    {
        $result=self::DEFAULTS;
        foreach(['sitemap_xml','sitemap_txt','sitemap_html','ai_content_index_html','sitemap_auto','sitemap_not1','sitemap_not2']as$name){$value=$input[$name]??0;if(!in_array($value,[0,1,'0','1'],true))throw new \InvalidArgumentException('地图选项无效');$result[$name]=(int)$value;}
        $count=$input['sitemap_archives_num']??1000;if(!ctype_digit((string)$count)||(int)$count>1000000)throw new \InvalidArgumentException('文档数量应为0至1000000，0表示不含文档');$result['sitemap_archives_num']=(int)$count;
        $tags=$input['sitemap_tags_num']??1000;if(!ctype_digit((string)$tags)||(int)$tags>1000000)throw new \InvalidArgumentException('标签数量应为0至1000000');$result['sitemap_tags_num']=(int)$tags;
        foreach(['index','list','view']as$type){$freq=$input['sitemap_changefreq_'.$type]??self::DEFAULTS['sitemap_changefreq_'.$type];if(!in_array($freq,['always','hourly','daily','weekly','monthly','yearly','never'],true))throw new \InvalidArgumentException('更新频率无效');$priority=$input['sitemap_priority_'.$type]??self::DEFAULTS['sitemap_priority_'.$type];if(!is_scalar($priority)||!preg_match('/^(?:0(?:\.[0-9])?|1(?:\.0)?)$/D',(string)$priority))throw new \InvalidArgumentException('优先级应为0.0至1.0');$result['sitemap_changefreq_'.$type]=$freq;$result['sitemap_priority_'.$type]=(string)$priority;}
        return $result;
    }

    /** Debounced durable queue, called after content/category transactions have committed. */
    public function scheduleAutomatic(?Request $request=null):array
    {
        $pending=[];
        foreach($this->languages()as$language){
            $site=$this->site($language['mark'],'pc');$settings=$this->settings($site);
            if(empty($settings['sitemap_auto'])||!in_array($site->lang,$this->generationLanguages($settings['sitemap_lang'],$this->languages()),true))continue;
            $pending[hash('sha256',$site->origin().'|'.$site->lang)]=['site_signature'=>\app\cms\template\TemplateMode::siteSignature($site),'template_mode'=>\app\cms\template\TemplateMode::current($site),'dialect_version'=>\app\cms\template\TemplateDialect::VERSION,'lang'=>$site->lang,'origin'=>$site->origin(),'base'=>(string)($site->global['web_cmspath']??''),'revision'=>bin2hex(random_bytes(8)),'queued_at'=>time(),'state'=>'pending'];
        }
        if(!$pending)return ['queued'=>0];
        return $this->store->locked(function()use($pending){$manifest=$this->manifest();$manifest['automatic']=array_replace($manifest['automatic']??[],$pending);$this->store->write('manifest.json',$manifest);return ['queued'=>count($pending)];});
    }

    /** One language per tick, with a revision check so concurrent edits remain queued. */
    public function advanceAutomatic():array
    {
        return (new SitemapQueue($this->repository, $this->store))->advance();
    }
    protected function link(string $url,string $title,int $time,string $type,array $settings):array{return ['url'=>html_entity_decode($url,ENT_QUOTES|ENT_HTML5,'UTF-8'),'title'=>$title,'time'=>$time,'kind'=>$type,'frequency'=>$settings['sitemap_changefreq_'.$type],'priority'=>$settings['sitemap_priority_'.$type]];}
    protected function absolute(string $url,CmsContext $site):string{return preg_match('#^https?://#i',$url)?$url:$site->origin().'/'.ltrim($url,'/');}
    protected function escape(mixed $value):string{return htmlspecialchars(preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f]/','',(string)$value),ENT_XML1|ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
    protected function xmlNode(array $link):string{return '<url><loc>'.$this->escape($link['url']).'</loc><lastmod>'.date('Y-m-d',max(0,(int)$link['time'])).'</lastmod><changefreq>'.$this->escape($link['frequency']).'</changefreq><priority>'.$this->escape($link['priority']).'</priority>'.(isset($link['mobile_mode'])&&$link['mobile_mode']!=='pc'?'<mobile:mobile type="'.$this->escape($link['mobile_mode']).'"/>':'').'</url>';}
    protected function formatEnabled(array $settings,string $format):bool{return !empty($settings[$format==='ai'?'ai_content_index_html':'sitemap_'.$format]);}
    private function fileResponse(string $body,string $format,string $source):Response{return response($body,200,['Content-Type'=>['xml'=>'application/xml','txt'=>'text/plain','html'=>'text/html','ai'=>'text/html'][$format].'; charset=utf-8','X-CMS-Sitemap'=>$source,'X-Content-Type-Options'=>'nosniff','Cache-Control'=>'no-cache']);}
    protected function sitemapSignature(CmsContext $site):string{return hash('sha256',serialize([$this->signature($site),$this->settings($site)]));}
}
