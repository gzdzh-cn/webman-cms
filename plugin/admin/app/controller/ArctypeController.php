<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\Arctype;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 栏目管理 
 */
class ArctypeController extends Crud
{
    
    /**
     * @var Arctype
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Arctype;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('arctype/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('arctype/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('arctype/update');
    }


    
    /**
     * 新增页面 - 基础设置 Tab 内容
     * 仅返回视图，用于通过 /app/admin/arctype/base 加载 base.html
     * @return Response
     */
    public function base(): Response
    {
        return view('arctype/base');
    }

    /**
     * 新增页面 - 高级设置 Tab 内容
     * 仅返回视图，用于通过 /app/admin/arctype/advanced 加载 advanced.html
     * @return Response
     */
    public function advanced(): Response
    {
        return view('arctype/advanced');
    }


}
