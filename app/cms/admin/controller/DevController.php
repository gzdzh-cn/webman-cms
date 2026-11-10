<?php
namespace app\cms\admin\controller;
class DevController
{
    public function formBuild() { return response('开发工具已关闭', 403); }
}
