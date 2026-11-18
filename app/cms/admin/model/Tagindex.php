<?php

namespace app\cms\admin\model;

use plugin\admin\app\model\Base;

/**
 * 标签索引模型
 */
class Tagindex extends \plugin\admin\app\model\CmsModel
{
    protected $table = 'tagindex';
    
    /**
     * 禁用自动时间戳管理（使用 add_time 和 update_time 字段）
     */
    public $timestamps = false;
}

