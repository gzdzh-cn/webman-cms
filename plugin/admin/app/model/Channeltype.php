<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $nid 识别id
 * @property string $title 名称
 * @property string $ntitle 左侧菜单名称
 * @property string $table 表名
 * @property integer $status 状态(1=启用，0=屏蔽)
 * @property integer $ifsystem 字段分类，1=系统(不可修改)，0=自定义
 * @property integer $is_repeat_title 文档标题重复，1=允许，0=不允许
 * @property integer $is_release 模型是否允许应用于会员投稿发布，1是，0否
 * @property integer $is_litpic_users_release 缩略图是否应用于会员投稿，1=允许，0=不允许
 * @property string $data 额外序列化存储数据
 * @property integer $is_del 伪删除，1=是，0=否
 * @property integer $sort_order 排序
 * @property integer $add_time 新增时间
 * @property integer $update_time 更新时间
 */
class Channeltype extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'channeltype';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
