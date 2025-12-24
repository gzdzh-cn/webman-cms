<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键
 * @property string $name 字段名称
 * @property integer $channel_id 模型ID
 * @property string $title 字段标题
 * @property string $dtype 字段类型
 * @property string $define 数据类型
 * @property integer $maxlength 最大长度
 * @property string $dfvalue 默认值
 * @property string $dfvalue_unit 数值单位
 * @property string $remark 提示文字
 * @property integer $is_screening 是否筛选
 * @property integer $is_release 是否支持投稿
 * @property integer $ifeditable 是否可编辑
 * @property integer $ifrequire 是否必填
 * @property integer $ifsystem 是否系统字段
 * @property integer $ifmain 是否主表字段
 * @property integer $ifcontrol 是否控制字段
 * @property integer $sort_order 排序
 * @property integer $status 状态
 * @property integer $add_time 新增时间
 * @property integer $update_time 更新时间
 * @property integer $set_type 设置类型
 */
class Channelfield extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_channelfield';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * 关联模型
     */
    public function channeltype()
    {
        return $this->belongsTo(Channeltype::class, 'channel_id', 'id');
    }
}

