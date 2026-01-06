<?php

namespace plugin\admin\app\model;

use support\Model;

/**
 * @property integer $img_id 主键(NOT NULL)
 * @property integer $aid 文档ID(NOT NULL)
 * @property string $title 图片标题(NOT NULL)
 * @property string $image_url 图片路径(NOT NULL)
 * @property string $intro 图片描述(NOT NULL)
 * @property integer $width 图片宽度(NOT NULL)
 * @property integer $height 图片高度(NOT NULL)
 * @property string $filesize 文件大小(NOT NULL)
 * @property string $mime MIME类型(NOT NULL)
 * @property integer $sort_order 排序(NOT NULL)
 * @property integer $add_time 添加时间(NOT NULL)
 * @property integer $update_time 更新时间(NOT NULL)
 */
class ProductImg extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_img';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'img_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

}

