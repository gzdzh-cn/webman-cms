<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键
 * @property integer $field_id 字段ID
 * @property integer $channel_id 模型ID
 * @property string $dtype 字段类型(radio/checkbox/selects等)
 * @property string $name 字段标识
 * @property string $dfvalue 选项值
 * @property integer $sort_order 排序
 * @property integer $add_time 新增时间
 * @property integer $update_time 更新时间
 */
class FieldCustomParam extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'field_custom_param';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'field_id',
        'channel_id',
        'dtype',
        'name',
        'dfvalue',
        'sort_order',
        'add_time',
        'update_time',
    ];
}


