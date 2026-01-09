<?php
namespace think\provider;

use Webman\Bootstrap;
use think\view\driver\Think;

class ViewService implements Bootstrap
{
    public static function start($worker)
    {
        // 注册模板引擎，使得 .htm 文件可以使用 EyouTemplate 渲染
        \support\View::setEngine('htm', function() {
            return new Think();
        });
    }
}
