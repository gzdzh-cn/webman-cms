<?php

namespace plugin\admin\app\controller;

use plugin\admin\app\common\Util;
use support\Db;
use support\Request;
use support\Response;
 
class TestController extends Base
{
    /**
     * 不需要登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['index', 'getTable'];

    /**
     * 示例方法
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $table = 'wa_rules';
        // 使用 withoutTablePrefix() 临时禁用前缀，因为 $table 已经包含完整表名
        $db = Util::db();
        $result = $db->withoutTablePrefix(function() use ($db, $table) {
            return $db->table($table)->get();
        });
        return json(['code' => 0, 'msg' => 'ok', 'data' => $result]);
    }

    public function getTable(Request $request): Response
    {
        $table = 'wa_rules';
        $result = Db::table($table)->get();
        return json(['code' => 0, 'msg' => 'ok', 'data' => $result]);
    }

}
