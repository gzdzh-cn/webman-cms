<?php

namespace app\controller;

use support\Request;
use support\Db;

class IndexController
{
    public function index(Request $request)
    {
        // 获取当前分类ID，用于导航高亮
        $tid = $request->get('tid', 0);
        
        // 传递给模板的数据
        $data = [
            'tid' => $tid,
            'seo_title' => '首页',
            'seo_keywords' => 'webman, cms, eyoucms',
            'seo_description' => '这是一个基于 Webman 重构的 CMS 系统。',
        ];
        
        // 渲染模板（template/pc/index.htm）
        // 注意：这里传模板名不要带后缀，think-template 会按 view_suffix 自动补 .htm
        return view('index', $data);
    }

    public function view(Request $request, $id = 0)
    {
        // 获取文章详情
        $article = [];
        if ($id > 0) {
            $article = Db::table('archives')
                ->where('id', $id)
                ->first();
        }
        
        // 传递给模板的数据
        $data = [
            'article' => $article,
            'seo_title' => $article['title'] ?? '文章详情',
            'seo_keywords' => $article['keywords'] ?? '',
            'seo_description' => $article['description'] ?? '',
        ];
        
        return view('view', $data);
    }

    public function json(Request $request)
    {
        return json(['code' => 0, 'msg' => 'ok']);
    }

    public function test(Request $request)
    {
        // 测试数据库连接
        try {
            $data = Db::table('arctype')->get();
            return json(['code' => 0, 'msg' => '数据库连接成功', 'data' => $data]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '数据库错误: ' . $e->getMessage()]);
        }
    }
}