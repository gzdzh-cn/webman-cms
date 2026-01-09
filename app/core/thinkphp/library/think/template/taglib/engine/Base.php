<?php

namespace think\template\taglib\engine;

use support\Request;
use support\Db;
/**
 * 基类
 */
class Base
{
    

    //构造函数
    function __construct()
    {
        // 控制器初始化
        $this->_initialize();
    }

    // 初始化
    protected function _initialize()
    {
        
    }
 
}
