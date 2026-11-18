<?php
declare(strict_types=1);

namespace app\cms\install;

use app\cms\database\SqlImporter;
use RuntimeException;

/** Trusted optional seed, applied to the empty catalog before staging or hashing. */
final class DemoSeeder
{
    public const VERSION = 1;
    private const TIME = 1793491200; // 2026-11-01 UTC; deterministic across preflight/resume.
    private const ASSETS = '/template/webman-default/assets/demo/';

    public static function apply(array $tables, string $prefix): array
    {
        $index=[];foreach($tables as $position=>$table)$index[$table['name']]=$position;
        $required=['arctype','archives','article_content','product_content','single_content','product_img','guestbook_attribute','language_attr','language_attribute','config'];
        foreach($required as $suffix)if(!isset($index[$prefix.$suffix]))throw new RuntimeException('演示基线缺少表：'.$suffix);
        $rows=[];foreach($required as $suffix)$rows[$suffix]=iterator_to_array(SqlImporter::rows($tables[$index[$prefix.$suffix]]),false);
        foreach(['arctype','archives','article_content','product_content','single_content','product_img','guestbook_attribute'] as $suffix){
            if($rows[$suffix])throw new RuntimeException('演示内容只能用于不含业务内容的空站基线：'.$suffix);
        }
        // Empty-site metadata may retain historical definitions. Do not bind the new
        // seed to orphaned original-site IDs; rebuild only these seed-owned groups.
        foreach(['language_attr','language_attribute'] as $suffix)$rows[$suffix]=array_values(array_filter($rows[$suffix],static fn(array $row):bool=>!in_array($row['attr_group']??'', ['arctype','archives','guestbook_attribute'],true)));
        $bindingId=self::next($rows['language_attr'],'id');$definitionId=self::next($rows['language_attribute'],'attr_id');
        $bind=static function(string $group,string $name,int $value,string $title)use(&$rows,&$bindingId,&$definitionId):void{
            $rows['language_attr'][]=['id'=>$bindingId++,'attr_name'=>$name,'attr_value'=>(string)$value,'attr_group'=>$group,'lang'=>'cn','add_time'=>self::TIME,'update_time'=>self::TIME];
            $rows['language_attribute'][]=['attr_id'=>$definitionId++,'attr_title'=>$title,'attr_name'=>$name,'attr_group'=>$group,'is_del'=>0,'add_time'=>self::TIME,'update_time'=>self::TIME];
        };
        // These are the project's own stable demo IDs. A small set intentionally
        // satisfies default's existing fixed typeid references without editing it.
        $categories=[
            [1,'about','关于我们',6,'single','企业简介与服务理念',0],
            [3,'products','产品中心',2,'product','面向企业场景的产品展示',0],
            [2,'news','新闻资讯',1,'article','企业动态、技术分享与服务消息',0],
            [4,'solutions','解决方案',1,'article','从业务需求到实施交付的场景方案',0],
            [54,'contact','联系我们',6,'single','商务咨询与服务联系方式',0],
            [55,'guestbook','在线留言',8,'guestbook','留下需求，我们会尽快与您联系',0],
            [5,'automation','协作与控制',2,'product','协作终端与模块控制产品',3],
            [6,'instruments','检测与感知',2,'product','检测设备与环境监测产品',3],
            [7,'enterprise','服务与展示',2,'product','现场服务与企业展示产品',3],
            [8,'company','企业简介',6,'single','企业理念与服务介绍',1],
        ];
        $categoryIds=[];
        foreach($categories as $order=>[$id,$key,$title,$model,$template,$description,$parent]){
            $categoryIds[$key]=$id;$count=match($key){'products','news'=>6,'automation','instruments','enterprise'=>2,'about','company','contact'=>1,'solutions'=>3,default=>0};
            $rows['arctype'][]=['id'=>$id,'channeltype'=>$model,'current_channel'=>$model,'parent_id'=>$parent,'topid'=>$parent,'typename'=>$title,'dirname'=>$key,'dirpath'=>($parent===3?'/products':($parent===1?'/about':'')).'/'.$key,'grade'=>$parent?1:0,'templist'=>'lists_'.$template.'.htm','tempview'=>'view_'.$template.'.htm','seo_title'=>$title,'seo_description'=>$description,'sort_order'=>($order+1)*10,'status'=>1,'lang'=>'cn','add_time'=>self::TIME,'update_time'=>self::TIME,'total_arc'=>$count];
            if($key==='contact')$rows['arctype'][array_key_last($rows['arctype'])]['templist']='lists_single_contact.htm';
            $bind('arctype','tid'.$id,$id,$title);
        }
        $data=require __DIR__.'/../resources/install/enterprise-demo.php';
        $aid=1;$bodyIds=['single_content'=>1,'product_content'=>1,'article_content'=>1];
        $document=static function(string $category,int $model,string $title,string $intro,string $image,string $body,int $time)use(&$rows,&$aid,&$bodyIds,$categoryIds,$bind):int{
            $id=$aid++;$suffix=match($model){1=>'article',2=>'product',6=>'single'};
            $rows['archives'][]=['aid'=>$id,'typeid'=>$categoryIds[$category],'channel'=>$model,'title'=>$title,'introduction'=>$intro,'litpic'=>$image,'is_litpic'=>1,'is_recom'=>$model===2?1:0,'origin'=>'企业演示站','author'=>'网站编辑','arcrank'=>0,'tempview'=>'view_'.$suffix.'.htm','status'=>1,'sort_order'=>$id*10,'lang'=>'cn','is_del'=>0,'add_time'=>$time,'update_time'=>$time,'seo_title'=>$title,'seo_description'=>$intro];
            $row=['id'=>$bodyIds[$suffix.'_content']++,'aid'=>$id,'content'=>$body,'content_ey_m'=>$body,'add_time'=>$time,'update_time'=>$time];
            if($model===6)$row['typeid']=$categoryIds[$category];
            $rows[$suffix.'_content'][]=$row;$bind('archives','aid'.$id,$id,$title);return $id;
        };
        foreach(['about','contact'] as $key){$entry=$data[$key];$image=self::ASSETS.$key.'.svg';$document($key,6,$entry['title'],$entry['intro'],$image,self::body($entry,$image),self::TIME);}
        $document('company',6,'企业简介',$data['about']['intro'],self::ASSETS.'about.svg',self::body($data['about'],self::ASSETS.'about.svg'),self::TIME);
        foreach(['products'=>2,'news'=>1] as $key=>$model)foreach($data[$key] as $i=>$entry){
            $image=self::ASSETS.($model===2?'product':'news').'-'.sprintf('%02d',$i+1).'.svg';$time=self::TIME-($i+1)*86400;
            $category=$model===2?['automation','instruments','enterprise'][intdiv($i,2)]:$key;
            $id=$document($category,$model,$entry['title'],$entry['intro'],$image,self::body($entry,$image),$time);
            if($model===2)$rows['product_img'][]=['img_id'=>count($rows['product_img'])+1,'aid'=>$id,'title'=>$entry['title'],'image_url'=>$image,'intro'=>$entry['intro'],'mime'=>'image/svg+xml','sort_order'=>0,'add_time'=>$time,'update_time'=>$time];
        }
        foreach([
            ['title'=>'团队协作空间方案','intro'=>'结合会议协作、信息分享与空间管理，让团队工作更加有序。','sections'=>[['理解团队需求','先梳理日常讨论、远程沟通和资料展示的场景，明确空间使用人数、设备条件与内容维护责任。'],['规划实施步骤','以小范围体验验证方案，结合反馈完善使用流程，再安排安装、培训与正式交付。']]],
            ['title'=>'生产质量管理方案','intro'=>'通过标准化检查流程与现场设备规划，帮助企业组织质量管理工作。','sections'=>[['明确检查标准','结合实际工艺和管理要求，整理检查对象、项目和记录方式，形成团队可以执行的工作清单。'],['持续优化流程','在试运行中验证操作效率和记录质量，明确异常反馈与改进责任，逐步形成稳定的管理习惯。']]],
            ['title'=>'企业品牌展示方案','intro'=>'把产品、案例和服务优势组织成清晰的内容体验，便于来访者了解企业。','sections'=>[['整理展示内容','围绕品牌定位与来访者关注的问题规划展示动线，优先使用准确的产品资料、项目说明和服务信息。'],['保持内容鲜活','约定内容更新与日常检查流程，让展示空间随着产品和业务发展及时调整。']]],
        ] as $i=>$entry){$image=self::ASSETS.'news-'.sprintf('%02d',$i+1).'.svg';$document('solutions',1,$entry['title'],$entry['intro'],$image,self::body($entry,$image),self::TIME-($i+7)*86400);}
        foreach([['联系人',0,0],['联系电话',0,1],['需求描述',2,0]] as $i=>[$title,$type,$validation]){
            $id=$i+1;$rows['guestbook_attribute'][]=['attr_id'=>$id,'attr_name'=>$title,'typeid'=>$categoryIds['guestbook'],'form_type'=>0,'attr_input_type'=>$type,'attr_values'=>'','is_showlist'=>1,'required'=>1,'validate_type'=>$validation,'real_validate'=>0,'sort_order'=>($i+1)*10,'lang'=>'cn','is_del'=>0,'add_time'=>self::TIME,'update_time'=>self::TIME];
            $bind('guestbook_attribute','attr_'.$id,$id,$title);
        }
        self::configure($rows['config'],['web_telephone'=>'400-000-2026','web_email'=>'hello@example.com','web_address'=>'示例市创新大道 88 号企业服务中心（演示地址）','web_attr_1'=>'400-000-2026','web_attr_15'=>'hello@example.com','web_attr_16'=>'示例市创新大道 88 号企业服务中心（演示地址）','web_attr_17'=>self::ASSETS.'contact.svg']);
        foreach($rows as $suffix=>$values){$position=$index[$prefix.$suffix];$tables[$position]['inserts']=array_map(static fn(array $row):string=>self::insert($prefix.$suffix,$row),$values);}
        return $tables;
    }

    private static function body(array $entry,string $image):string
    {
        $escape=static fn(string $value):string=>htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $body='<p>'.$escape($entry['intro']).'</p><p><img src="'.$escape($image).'" alt="'.$escape($entry['title']).'" /></p>';
        foreach($entry['sections'] as [$title,$text])$body.='<h2>'.$escape($title).'</h2><p>'.$escape($text).'</p>';
        return $body.'<p>本页为可编辑的企业官网演示内容，请在正式发布前替换为您的真实资料。</p>';
    }

    private static function configure(array &$rows,array $values):void
    {
        $id=self::next($rows,'id');$seen=[];
        foreach($rows as &$row)if(($row['lang']??'cn')==='cn'&&array_key_exists($row['name']??'',$values)){$row['value']=$values[$row['name']];$row['is_del']=0;$row['update_time']=self::TIME;$seen[$row['name']]=true;}unset($row);
        foreach($values as $name=>$value)if(!isset($seen[$name]))$rows[]=['id'=>$id++,'name'=>$name,'value'=>$value,'inc_type'=>'web','desc'=>'企业演示联系资料','lang'=>'cn','is_del'=>0,'update_time'=>self::TIME];
    }

    private static function next(array $rows,string $key):int
    {
        return $rows?max(array_map(static fn(array $row):int=>(int)($row[$key]??0),$rows))+1:1;
    }

    private static function insert(string $table,array $row):string
    {
        $literal=static function(mixed $value):string{
            if($value===null)return 'NULL';if(is_int($value))return (string)$value;
            return "'".str_replace(["\\","'","\0","\n","\r","\x1a"],["\\\\","\\'","\\0","\\n","\\r","\\Z"],(string)$value)."'";
        };
        return 'INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',array_map($literal,array_values($row))).')';
    }
}
