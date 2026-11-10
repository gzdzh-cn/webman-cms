<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\compat\Runtime;
use app\cms\repository\CmsRepository;
use app\cms\template\TemplateRenderer;

/** Original AI index data contract and template, within the licensed corporate scope. */
class AiContentIndexService extends StaticGenerationService
{
    public function __construct(?CmsRepository $repository=null,?string $directory=null){parent::__construct($repository,$directory??'sitemap');}
    public static function available(CmsContext $site):bool{return is_numeric($site->global['php_servicemeal']??null)&&(float)$site->global['php_servicemeal']>=5;}
    public static function response(\support\Request $request,CmsContext $context):?\support\Response
    {
        $path=$request->path();$base=rtrim((string)($context->global['web_cmspath']??''),'/');
        if($base!==''&&str_starts_with($path,$base.'/'))$path=substr($path,strlen($base));
        if(!preg_match('#^/ai-content-index(?:_[A-Za-z0-9_-]{2,50})?\.html$#D',$path))return null;
        return SitemapService::responseForPath($request,$context);
    }

    public function url(CmsContext $site):string
    {
        $languages=$this->languages();$home=$languages[0]['mark']??$site->lang;
        foreach($languages as$language)if(!empty($language['is_home_default'])){$home=$language['mark'];break;}
        return rtrim((string)($site->global['web_cmspath']??''),'/').'/ai-content-index'.($site->lang===$home?'':'_'.$site->lang).'.html';
    }

    public function document(CmsContext $site):array
    {
        if(!self::available($site))throw new \RuntimeException('AI内容索引需要原服务档位 php_servicemeal >= 5');
        if(empty($site->global['ai_content_index_html']))throw new \RuntimeException('AI内容索引尚未开启');
        $previous=CmsContext::current();CmsContext::set($site);Runtime::boot($site);
        try{
            $eyou=$this->data($site);$body=$this->renderTemplate($site,$eyou);
            $count=count($eyou['field']['type_list'])+count($eyou['field']['tags_list']);
            foreach($eyou['field']['arc_list']as$group)$count+=count($group['list']);
            return ['url'=>$this->url($site),'body'=>$body,'links'=>$count];
        }finally{CmsContext::set($previous);Runtime::boot($previous);}
    }

    public function live(CmsContext $site):\support\Response
    {
        if(!self::available($site)||empty($site->global['ai_content_index_html']))return response('AI内容索引未开启或服务档位不支持',404,['Content-Type'=>'text/plain; charset=utf-8']);
        return response($this->document($site)['body'],200,['Content-Type'=>'text/html; charset=utf-8','X-CMS-Sitemap'=>'live','X-Content-Type-Options'=>'nosniff']);
    }

    protected function renderTemplate(CmsContext $site,array $eyou):string
    {
        $templateRoot=\app\cms\template\TemplateMode::systemRoot($site);
        $template=$templateRoot.'/ai-content-index.htm';
        if(!is_file($template)||is_link($template))throw new \RuntimeException('AI内容索引模板不存在');
        $cache=$this->store->cacheDirectory();
        return (new TemplateRenderer($site,['template_root'=>$templateRoot,'cache_path'=>$cache]))->render('ai-content-index',['eyou'=>$eyou]);
    }

    protected function data(CmsContext $site):array
    {
        $settings=array_replace(SitemapService::DEFAULTS,array_intersect_key($site->global,SitemapService::DEFAULTS));
        $allowed=array_map('intval',$site->getConfig('global.allow_release_channel',[]));
        $channels=$this->repository->table('channeltype')->where('status',1)->order('id asc')->select()->toArray();
        $channels=array_column(array_filter($channels,static fn($row)=>!in_array((int)$row['id'],[6,8,51],true)&&in_array((int)$row['id'],$allowed,true)),null,'id');
        $features=$descriptions=[];
        foreach($channels as$channel){$id=(int)$channel['id'];$features[]=[1=>'文章发布',2=>'产品展示',3=>'图片相册',4=>'文件下载',5=>'视频播放'][$id]??$channel['ntitle'];$descriptions[]=[1=>'文章',2=>'产品',3=>'图集',4=>'下载资源',5=>'视频'][$id]??$channel['ntitle'];}
        $field=['channeltype_list'=>$channels,'zygn_channel_text'=>implode('、',array_slice($features,0,5)),'seo_title'=>'网站内容索引 - AI抓取友好页面','seo_keywords'=>'','seo_description'=>'本页面为AI爬虫提供网站完整内容索引，包括'.implode('、',array_slice($descriptions,0,5)).'等所有内容类型，便于理解网站结构和内容','web_telephone'=>'','web_email'=>'','web_address'=>'','type_list'=>[],'arc_list'=>[],'tags_list'=>[],'faq_list'=>[],'ai_content_index_url'=>$site->origin().$this->url($site)];
        foreach($site->global as$name=>$value)if(str_starts_with((string)$name,'web_attr_')&&is_scalar($value)){
            $value=(string)$value;
            if(check_mobile($value)||check_landline($value))$field['web_telephone']=$value;
            elseif(check_email($value))$field['web_email']=$value;
            elseif(preg_match('/^(北京|天津|河北|山西|内蒙古|辽宁|吉林|黑龙江|上海|江苏|浙江|安徽|福建|江西|山东|河南|湖北|湖南|广东|广西|海南|重庆|四川|贵州|云南|西藏|陕西|甘肃|青海|宁夏|新疆|台湾|香港|澳门|中国)/u',$value))$field['web_address']=$value;
        }
        $query=$this->repository->table('arctype')->where(['lang'=>$site->lang,'is_del'=>0,'status'=>1]);
        if(!empty($settings['sitemap_not1']))$query->where('is_hidden',0);if(!empty($settings['sitemap_not2']))$query->where('is_part',0);
        $types=array_column(array_filter($query->order('id asc')->select()->toArray(),static fn($row)=>empty($row['typearcrank'])),null,'id');
        foreach($types as$type){$type['typeurl']=(int)$type['is_part']===1&&!empty($type['typelink'])?$type['typelink']:typeurl('home/Lists/index',$type,true,true);$field['type_list'][]=$type;}
        $limit=max(0,(int)$settings['sitemap_archives_num']);
        foreach($channels as$channel){
            $rows=[];
            if($limit>0){$query=$this->repository->table('archives')->where(['channel'=>$channel['id'],'lang'=>$site->lang,'status'=>1,'is_del'=>0,'arcrank'=>0]);if(!empty($settings['sitemap_not2']))$query->where('is_jump',0);$rows=$query->order('aid desc')->limit($limit)->select()->toArray();}
            $items=[];
            foreach($rows as$row){if(!isset($types[$row['typeid']])||(int)$types[$row['typeid']]['current_channel']!==(int)$row['channel'])continue;$row=array_replace($types[$row['typeid']],$row);$row['arcurl']=!empty($row['is_jump'])&&!empty($row['jumplinks'])?$row['jumplinks']:arcurl('home/View/index',$row,true,true);$items[]=$row;}
            $items=$this->enrich($items,(int)$channel['id'],$site);
            $field['arc_list'][$channel['id']]=['list'=>$items,'channel_info'=>$channel];
        }
        $tagsLimit=max(0,(int)$settings['sitemap_tags_num']);
        if($tagsLimit>0)foreach($this->repository->table('tagindex')->where('lang',$site->lang)->order('id desc')->limit($tagsLimit)->select()->toArray()as$tag)$field['tags_list'][]=['link'=>tagurl('home/Tags/lists',['tagid'=>$tag['id']],true,true),'tag'=>$tag['tag'],'total'=>$tag['total']];
        $groups=array_column($this->repository->table('faq_group')->where(['lang'=>$site->lang,'status'=>1])->select()->toArray(),null,'group_id');
        foreach($this->repository->table('faq_asklist')->where('lang',$site->lang)->order('sort_order asc,asklist_id desc')->select()->toArray()as$faq){if(!isset($groups[$faq['group_id']]))continue;$faq['group_title']=$groups[$faq['group_id']]['group_title'];$faq['asklist_title']=htmlspecialchars_decode($faq['asklist_title']);$faq['asklist_content']=str_replace(PHP_EOL,'<br/>',htmlspecialchars_decode($faq['asklist_content']));$field['faq_list'][]=$faq;}
        return ['global'=>$site->global,'field'=>$field];
    }

    protected function enrich(array $items,int $channel,CmsContext $site):array
    {
        if(!$items)return $items;$ids=array_column($items,'aid');$extra=[];
        if($channel===3||$channel===4){$table=$channel===3?'images_upload':'download_file';foreach($this->repository->table($table)->whereIn('aid',$ids)->order('sort_order asc')->select()->toArray()as$row)$extra[$row['aid']][]=$row;}
        elseif($channel===5)$extra=array_column($this->repository->table('media_content')->whereIn('aid',$ids)->select()->toArray(),null,'aid');
        foreach($items as&$item){
            if($channel===3){$item['image_list']=$extra[$item['aid']]??[];foreach($item['image_list']as&$image){$image['image_url']=handle_subdir_pic($image['image_url']);if(isset($image['intro']))$image['intro']=htmlspecialchars_decode($image['intro']);}unset($image);}
            elseif($channel===4){$item['file_list']=$extra[$item['aid']]??[];$item['file_size']='0KB';foreach($item['file_list']as&$file){$file['file_url']=handle_subdir_pic($file['file_url'],'soft');$file['file_size']=empty($file['file_size'])?'':format_bytes($file['file_size']);if($file['file_size']!=='')$item['file_size']=$file['file_size'];}unset($file);}
            elseif($channel===5){$item=array_replace($extra[$item['aid']]??[],$item);$item['courseware']=!empty($item['courseware'])?url('home/View/download_media_file',['aid'=>$item['aid']]):'';$item['total_duration']=gmSecondFormat($item['total_duration']??0,':');$item['total_video']=$item['total_video']??0;}
        }unset($item);return $items;
    }
}
