<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键
 * @property integer $typeid 栏目ID
 * @property integer $field_id 字段ID
 * @property integer $add_time 新增时间
 * @property integer $update_time 更新时间
 */
class ChannelfieldBind extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'channelfield_bind';

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
        'typeid',
        'field_id',
        'add_time',
        'update_time',
    ];
}


