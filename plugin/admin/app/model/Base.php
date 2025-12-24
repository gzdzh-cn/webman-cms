<?php

namespace plugin\admin\app\model;

use DateTimeInterface;
use support\Model;
use plugin\admin\app\common\Util;


class Base extends Model
{
    /**
     * @var string
     */
    protected $connection = 'plugin.admin.mysql';

    /**
     * 格式化日期
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * 获取数据库连接实例
     * 重写此方法以确保 SQL 日志监听器被注册
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        // 触发 SQL 日志监听器注册
        Util::db();
        return parent::getConnection();
    }
}
