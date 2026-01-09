<?php

namespace think\template\taglib\engine;

use support\Db;

class TagGlobal extends Base
{


    //初始化
    protected function _initialize()
    {
        parent::_initialize();
    }

    public function getGlobal($name = '')
    {
        static $globals = null;
        if (null === $globals) {
            $globals = [
                'web_name' => '我的 Webman CMS',
                'web_keywords' => 'webman, cms, eyoucms',
                'web_description' => '这是一个基于 Webman 重构的 CMS 系统。',
            ];
        }

        return $name ? ($globals[$name] ?? '') : $globals;
    }
}