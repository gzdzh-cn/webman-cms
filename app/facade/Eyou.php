<?php
// app/facade/Eyou.php
namespace app\facade;

use think\Facade;

/**
 * EyouCMS 功能门面类
 * 
 * @method static mixed getGlobal(string $name = '') 获取全局变量
 * @method static array getChannel(int $typeid = 0, int $row = 10) 获取导航
 * @method static array getArclist(int $typeid = 0, int $limit = 10, string $orderby = 'a.add_time desc') 获取文章列表
 * @method static bool isCurrent(int $typeid) 判断当前栏目
 * @method static mixed config(string $name = '', $default = null) 获取配置
 */
class Eyou extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\common\Eyou::class;
    }
}