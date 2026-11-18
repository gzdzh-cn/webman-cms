<?php
namespace app\cms\admin\support;

class CmsUtil extends \plugin\admin\app\common\Util
{
    public static function db(): \Illuminate\Database\Connection
    {
        return \support\Db::connection('plugin.admin.cms');
    }

    public static function getTablePrefix(bool $autoDetect = true): string
    {
        return (string)config('cms_admin.database.connections.cms.prefix', 'ey_');
    }
}
