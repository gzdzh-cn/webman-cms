<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Enterprise city-site tree and original site_* configuration. */
class CitysiteService
{
    public const SETTINGS = ['web_citysite_open','site_template','site_default_home','site_seoset','site_seo_title','site_seo_keywords','site_seo_description'];
    private const RESERVED = ['www','admin','api','home','index','index.php','public','static','template','uploads','upload','data','runtime','vendor','app','plugin','search','tags','sitemap','robots','weapp','install','login','user','member','shop'];
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }
    public function listing(string $search = ''): array
    {
        $rows=$this->repository->table('citysite')->order('sort_order,id')->select()->toArray();$tree=[];$seen=[];
        $walk=function(int $parent,int $depth)use(&$walk,&$tree,&$seen,$rows,$search){foreach($rows as $row){$id=(int)$row['id'];if((int)$row['parent_id']!==$parent||isset($seen[$id]))continue;$seen[$id]=true;$row['depth']=$depth;if($search===''||mb_stripos($row['name'].' '.$row['domain'],$search)!==false)$tree[]=$row;$walk($id,$depth+1);}};
        $walk(0,0);foreach($rows as $row)if(!isset($seen[(int)$row['id']])){$row['depth']=0;$row['tree_warning']='上级地区缺失或循环';$tree[]=$row;}
        return ['code'=>0,'msg'=>'ok','count'=>count($tree),'data'=>$tree];
    }
    public function save(array $input): int
    {
        return $this->locked(function($r)use($input){
            $rows=$r->table('citysite')->lock(true)->select()->toArray();$map=array_column($rows,null,'id');$id=(int)($input['id']??0);$old=$map[$id]??null;
            if($id&&!$old)throw new InvalidArgumentException('地区不存在');
            $data=$r->filter('citysite',$input);unset($data['level'],$data['topid'],$data['add_time'],$data['update_time']);$merged=$data+($old??[]);
            $name=trim((string)($merged['name']??''));$domain=strtolower(trim((string)($merged['domain']??'')));$parent=(int)($merged['parent_id']??0);
            if($name===''||mb_strlen($name)>32)throw new InvalidArgumentException('地区名称必填，且不超过32个字符');
            if(!preg_match('/^[a-z0-9][a-z0-9_-]{0,49}$/D',$domain)||in_array($domain,self::RESERVED,true)||$r->table('arctype')->where('dirname',$domain)->find())throw new InvalidArgumentException('英文站点标识无效或与系统目录冲突');
            foreach($rows as$row){if((int)$row['id']===$id)continue;if(strtolower($row['domain'])===$domain)throw new InvalidArgumentException('英文站点标识已存在');if((int)$row['parent_id']===$parent&&$row['name']===$name)throw new InvalidArgumentException('同级地区名称已存在');}
            $seen=[$id];$ancestor=$parent;$level=1;$top=0;
            while($ancestor){if(in_array($ancestor,$seen,true))throw new InvalidArgumentException('地区不能移动到自身或下级');if(!isset($map[$ancestor]))throw new InvalidArgumentException('上级地区不存在');$seen[]=$ancestor;$top=$ancestor;$ancestor=(int)$map[$ancestor]['parent_id'];$level++;}
            $descendants=$this->descendants([$id],$map);$relativeDepth=0;foreach($descendants as$child){if($child===$id)continue;$at=$child;$depth=0;$visited=[];while($at!==$id){if(isset($visited[$at])||!isset($map[$at]))throw new InvalidArgumentException('地区层级损坏');$visited[$at]=true;$at=(int)$map[$at]['parent_id'];$depth++;}$relativeDepth=max($relativeDepth,$depth);}
            if($level+$relativeDepth>3)throw new InvalidArgumentException('城市分站最多三级');
            foreach(['status','is_hot','is_open','seoset','showall']as$key)if(isset($data[$key]))$data[$key]=(int)!empty($data[$key]);
            foreach(['seo_title','seo_keywords']as$key)if(isset($data[$key])&&mb_strlen((string)$data[$key])>200)throw new InvalidArgumentException('SEO标题和关键词不能超过200个字符');
            if(isset($data['seo_description'])&&strlen((string)$data['seo_description'])>60000)throw new InvalidArgumentException('SEO描述过长');
            $data['initial']=strtoupper((string)($data['initial']??($old['initial']??'')));if($data['initial']==='')$data['initial']=function_exists('getFirstCharter')?(string)getFirstCharter($name):strtoupper(substr($name,0,1));
            if(!preg_match('/^[A-Z0-9]{1,5}$/D',$data['initial']))throw new InvalidArgumentException('首字母请填写1至5位英文字母或数字');
            if(isset($data['sort_order'])&&((int)$data['sort_order']<0||(int)$data['sort_order']>999999))throw new InvalidArgumentException('排序范围为0至999999');
            $data+=['status'=>(int)($old['status']??1)];$data['name']=$name;$data['domain']=$domain;$data['parent_id']=$parent;$data['level']=$level;$data['topid']=$level===1?0:$top;$data['update_time']=time();
            if($id)$r->table('citysite')->where('id',$id)->update($data);else{$data['add_time']=time();$id=(int)$r->table('citysite')->insertGetId($data);}
            $current=$data+($old??[]);$map[$id]=$current+['id'=>$id];$queue=[$id];
            while($queue){$owner=array_shift($queue);foreach($map as$childId=>$row){if((int)$row['parent_id']!==$owner)continue;$parentRow=$map[$owner];$update=['level'=>(int)$parentRow['level']+1,'topid'=>(int)$parentRow['topid']?:$owner,'update_time'=>time()];if(empty($current['status']))$update['status']=0;$r->table('citysite')->where('id',$childId)->update($update);$map[$childId]=$update+$row;$queue[]=(int)$childId;}}
            if(!empty($current['status'])){foreach(array_slice($seen,1)as$ancestorId)$r->table('citysite')->where('id',$ancestorId)->update(['status'=>1,'update_time'=>time()]);}
            $settings=$this->settings();$default=(int)$settings['site_default_home'];if($default&&!$r->table('citysite')->where(['id'=>$default,'status'=>1])->find())$this->putConfiguration('site_default_home','0');
            if(!empty($settings['web_citysite_open'])&&!$r->table('citysite')->where('status',1)->find())throw new InvalidArgumentException('开启城市分站时至少保留一个启用地区');
            return $id;
        });
    }
    public function delete(array $ids): void
    {
        $ids=array_values(array_filter(array_unique(array_map('intval',$ids))));if(!$ids)throw new InvalidArgumentException('请选择地区');
        $this->locked(function($r)use($ids){$rows=$r->table('citysite')->lock(true)->select()->toArray();$map=array_column($rows,null,'id');foreach($ids as$id)if(!isset($map[$id]))throw new InvalidArgumentException('地区不存在');$all=$this->descendants($ids,$map);
            foreach($all as$id){$field=match((int)$map[$id]['level']){1=>'province_id',2=>'city_id',3=>'area_id',default=>null};if($field&&$r->table('archives')->where($field,$id)->find())throw new InvalidArgumentException('地区仍被文档使用，请先调整文档所属地区');}
            $settings=$this->settings();if(!empty($settings['web_citysite_open'])&&!$r->table('citysite')->whereNotIn('id',$all)->where('status',1)->find())throw new InvalidArgumentException('开启城市分站时至少保留一个启用地区');
            $r->table('citysite')->whereIn('id',$all)->delete();if(in_array((int)$settings['site_default_home'],$all,true))$this->putConfiguration('site_default_home','0');
        });
    }
    public function settings(): array
    {
        $main=(new LanguagePolicy($this->repository))->main();
        $rows=$this->repository->table('config')->where(['lang'=>$main,'is_del'=>0])->whereIn('name',self::SETTINGS)->order('id')->select()->toArray();$data=[];
        foreach($rows as $row){if(array_key_exists($row['name'],$data))throw new InvalidArgumentException('城市配置重复：'.$row['name']);$data[$row['name']]=$row['value'];}
        return $data+['web_citysite_open'=>'0','site_template'=>'0','site_default_home'=>'0','site_seoset'=>'0','site_seo_title'=>'','site_seo_keywords'=>'','site_seo_description'=>''];
    }
    public function saveSettings(array $input): void
    {
        (new LanguageAccessService($this->repository))->assertLanguages(array_column((new LanguagePolicy($this->repository))->all(),'mark'));
        $this->locked(function($r)use($input){$data=array_intersect_key($input,array_flip(self::SETTINGS));$merged=$data+$this->settings();
            foreach($data as$key=>$value)if(!is_scalar($value))throw new InvalidArgumentException('站点配置格式错误');
            foreach(['web_citysite_open','site_template','site_seoset']as$key)if(isset($data[$key]))$data[$key]=(string)(int)!empty($data[$key]);
            (new SiteModulePolicy($r))->assertEnable('city',!empty($merged['web_citysite_open']));
            if(!empty($merged['web_citysite_open'])&&!$r->table('citysite')->where('status',1)->find())throw new InvalidArgumentException('请先创建一个启用地区');
            $default=(int)($merged['site_default_home']??0);if($default&&!$r->table('citysite')->where(['id'=>$default,'status'=>1])->find())throw new InvalidArgumentException('默认地区不存在或已停用');
            if(isset($data['site_default_home']))$data['site_default_home']=(string)$default;
            foreach($data as$key=>$value){if(strlen((string)$value)>60000)throw new InvalidArgumentException('站点配置内容过长');$this->putConfiguration($key,(string)$value);}
        });
    }
    private function descendants(array $ids,array $map): array
    {
        $result=[];$queue=$ids;while($queue){$id=(int)array_shift($queue);if(isset($result[$id])||!isset($map[$id]))continue;$result[$id]=true;foreach($map as$childId=>$row)if((int)$row['parent_id']===$id)$queue[]=(int)$childId;}return array_keys($result);
    }
    private function putConfiguration(string $name,string $value): void
    {
        $r=$this->repository;$main=(new LanguagePolicy($r))->main();$rows=$r->table('config')->where(['name'=>$name,'lang'=>$main])->lock(true)->select()->toArray();if(count($rows)>1)throw new InvalidArgumentException('城市配置重复：'.$name);$row=$rows[0]??null;$data=['value'=>$value,'inc_type'=>$name==='web_citysite_open'?'web':'site','is_del'=>0,'update_time'=>time()];if($row)$r->table('config')->where('id',$row['id'])->update($data);else$r->table('config')->insert($r->filter('config',$data+['name'=>$name,'lang'=>$main]));
    }
    private function locked(callable $callback)
    {
        return (new SiteModulePolicy($this->repository))->locked(function()use($callback){
        $name='cms-citysite-'.substr(hash('sha256',$this->repository->fullName('citysite')),0,48);$lock=$this->repository->connection()->query('SELECT GET_LOCK(?,5) acquired',[$name]);if((int)($lock[0]['acquired']??0)!==1)throw new InvalidArgumentException('地区正在更新，请稍后重试');
        try{return $this->repository->transaction($callback);}finally{$this->repository->connection()->query('SELECT RELEASE_LOCK(?)',[$name]);}
        });
    }
}
