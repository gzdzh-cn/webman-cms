<?php
declare(strict_types=1);
namespace app\cms\admin\support;

/** Presentation metadata is deliberately independent of database column comments. */
final class UiDefinition
{
    public static function forModule(string $module, array $schema): array
    {
        $all = require dirname(__DIR__).'/config/ui.php';
        if (!isset($all[$module])) throw new \InvalidArgumentException('未登记模块界面');
        $page=$all[$module];
        $defaults=[
            'id'=>'ID','aid'=>'所属文档','file_id'=>'ID','nav_id'=>'ID','position_id'=>'导航位置','form_id'=>'ID','attr_id'=>'ID','tpl_id'=>'ID','log_id'=>'ID',
            'title'=>'名称','intro'=>'说明','url'=>'网站地址','status'=>'状态','sort_order'=>'排序','typeid'=>'所属栏目','litpic'=>'封面图','lang'=>'语言',
            'target'=>'新窗口打开','nofollow'=>'禁止权重传递','email'=>'联系邮箱','add_time'=>'创建时间','update_time'=>'更新时间','file_size'=>'文件大小','file_ext'=>'文件类型',
            'seo_title'=>'SEO标题','seo_keywords'=>'SEO关键词','seo_description'=>'SEO描述','ip'=>'IP地址','admin_id'=>'操作人',
        ];
        $fields=[];
        foreach ($schema as $key=>$column) {
            $type=preg_match('/text|blob/i',$column['Type'])?'textarea':(preg_match('/int|decimal|float|double/i',$column['Type'])?'number':'text');
            $definition=['label'=>$defaults[$key]??'扩展字段（'.$key.'）','type'=>$type];
            if(in_array($key,['status','target','nofollow'],true))$definition+=['options'=>$key==='status'?[1=>'启用',0=>'停用']:[0=>'否',1=>'是']];
            if(isset($definition['options']))$definition['type']='select';
            if(in_array($key,['add_time','update_time'],true))$definition['type']='datetime';
            if(in_array($key,['litpic','logo'],true))$definition['type']='image';
            if($key==='file_size')$definition['type']='size';
            $fields[$key]=array_replace($definition,$page['definitions'][$key]??[]);
        }
        if($module==='guestbook')$fields['_submission']=['label'=>'提交内容','type'=>'submission'];
        $page['columns']=array_values(array_filter($page['columns'],static fn($key)=>isset($fields[$key])));
        $page['fields']=array_values(array_filter($page['fields'],static fn($key)=>isset($schema[$key])));
        $page['definitions']=$fields;
        return $page+['page'=>false,'readonly'=>false];
    }
}
