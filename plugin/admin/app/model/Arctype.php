<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property integer $channeltype 栏目顶级模型ID
 * @property integer $current_channel 栏目当前模型ID
 * @property integer $parent_id 栏目上级ID
 * @property integer $topid 顶级栏目ID
 * @property string $typename 栏目名称
 * @property string $dirname 目录英文名
 * @property string $dirpath 目录存放HTML路径
 * @property string $diy_dirpath 列表静态文件存放规则
 * @property string $rulelist 列表静态文件存放规则
 * @property string $ruleview 文档静态文件存放规则
 * @property string $englist_name 栏目英文名
 * @property integer $grade 栏目等级
 * @property string $typelink 栏目链接
 * @property string $litpic 栏目图片
 * @property string $templist 列表模板文件名
 * @property string $tempview 文档模板文件名
 * @property string $seo_title SEO标题
 * @property string $seo_keywords seo关键字
 * @property string $seo_description seo描述
 * @property integer $sort_order 排序号
 * @property integer $is_hidden 是否隐藏栏目：0=显示，1=隐藏
 * @property integer $is_part 栏目属性：0=内容栏目，1=外部链接
 * @property integer $admin_id 管理员ID
 * @property integer $is_del 伪删除，1=是，0=否
 * @property integer $del_method 伪删除状态，1为主动删除，2为跟随上级栏目被动删除
 * @property integer $status 启用 (1=正常，0=屏蔽)
 * @property integer $is_release 栏目是否应用于会员投稿发布，1是，0否
 * @property string $weapp_code 插件栏目唯一标识
 * @property string $lang 语言标识
 * @property integer $add_time 新增时间
 * @property integer $update_time 更新时间
 * @property integer $target 新窗口打开
 * @property integer $nofollow 防抓取
 * @property integer $typearcrank 阅读权限：0=开放浏览，-1=待审核稿件
 * @property integer $empty_logic 空内容逻辑
 * @property string $page_limit 限制页面 1-栏目页面 0-文档页面
 * @property integer $total_arc 栏目下文档数量
 */
class Arctype extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_arctype';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
