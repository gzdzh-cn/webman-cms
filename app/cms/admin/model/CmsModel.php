<?php
namespace app\cms\admin\model;

/** Original Eyou tables have integer add_time/update_time, never Eloquent timestamps. */
class CmsModel extends \support\Model
{
    protected $connection = 'plugin.admin.cms';
    public $timestamps = false;
}
