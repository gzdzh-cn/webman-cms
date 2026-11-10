<?php
namespace app\cms\admin\controller;
use support\Request;
use support\Response;
/** Compatibility boundary: all new installation goes through the whole-site installer. */
class InstallController extends \plugin\admin\app\controller\Base
{
    protected $noNeedLogin=['step1','step2'];
    public function step1(Request $request):Response{return $this->json(1,'请使用整站安装向导 /install；已有网站不能重新安装');}
    public function step2(Request $request):Response{return $this->json(1,'请使用整站安装向导 /install；已有网站不能重新安装');}
}
