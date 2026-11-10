<?php
namespace app\cms\admin\controller;

use support\Request;
use support\Response;

/** Historical permission identity retained without editor functionality. */
class UisetController extends \plugin\admin\app\controller\Base
{
    public function index(Request $request): Response { return response('可视化编辑功能已关闭', 403); }
    public function select(Request $request): Response { return response('可视化编辑功能已关闭', 403); }
    public function save(Request $request): Response { return response('可视化编辑功能已关闭', 403); }
    public function clear(Request $request): Response { return response('可视化编辑功能已关闭', 403); }
}
