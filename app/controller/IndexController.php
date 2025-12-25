<?php

namespace app\controller;

use support\Request;
use support\Db;

class IndexController
{
    public function index(Request $request)
    {
        return <<<EOF
<style>
  * {
    padding: 0;
    margin: 0;
  }
  iframe {
    border: none;
    overflow: scroll;
  }
</style>
<iframe
  src="https://www.workerman.net/wellcome"
  width="100%"
  height="100%"
  allow="clipboard-write"
  sandbox="allow-scripts allow-same-origin allow-popups"
></iframe>
EOF;
    }

    public function view(Request $request)
    {
        return view('index/view', ['name' => 'webman']);
    }

    public function json(Request $request)
    {
        return json(['code' => 0, 'msg' => 'ok']);
    }

    public function test(Request $request)
    {
        $data = Db::table('wa_arctype')->get();
        return json(['code' => 0, 'msg' => 'ok', 'data' => $data]);
    }

}
