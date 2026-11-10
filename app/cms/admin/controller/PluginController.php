<?php
namespace app\cms\admin\controller;

use support\Request;
use support\Response;

/** Historical controller identity retained, online extension management disabled. */
class PluginController extends \plugin\admin\app\controller\Base
{
    public function index(Request $request): Response { return response('插件功能已关闭', 403); }
    public function list(Request $request): Response { return response('插件功能已关闭', 403); }
    public function install(Request $request): Response { return response('插件功能已关闭', 403); }
    public function uninstall(Request $request): Response { return response('插件功能已关闭', 403); }
    public function pay(Request $request): Response { return response('插件功能已关闭', 403); }
    public function captcha(Request $request): Response { return response('插件功能已关闭', 403); }
    public function login(Request $request): Response { return response('插件功能已关闭', 403); }
    public function getInstalledPlugins(Request $request): Response { return response('插件功能已关闭', 403); }
}
