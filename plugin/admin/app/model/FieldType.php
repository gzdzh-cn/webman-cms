<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * 字段类型模型
 * @property integer $id 主键
 * @property string $name 字段类型标识
 * @property string $title 字段类型名称
 * @property integer $sort_order 排序
 * @property integer $status 状态
 */
class FieldType extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'field_type';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'name';
    
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'sort_order',
        'status',
    ];
}

