<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\Arctype;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Log;
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
     * 过滤所有字段的文本，去掉前后空格
     * @param array $data
     * @return array
     */
    protected function trimFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }
        return $data;
    }

    /**
     * 插入前置方法（重写父类方法）
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function insertInput(Request $request): array
    {
        $data = parent::insertInput($request);
        return $this->trimFields($data);
    }

    /**
     * 更新前置方法（重写父类方法）
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function updateInput(Request $request): array
    {
        [$id, $data] = parent::updateInput($request);
        $data = $this->trimFields($data);
        return [$id, $data];
    }


    
    /**
     * 查询前置方法（重写父类方法）
     * 限制最大数量为 1000，并设置合适的默认值
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function selectInput(Request $request): array
    {
        // 先检查是否传递了 limit 参数
        $requestLimit = $request->get('limit');
        $hasLimitParam = $requestLimit !== null;
        
        [$where, $format, $limit, $field, $order, $page] = parent::selectInput($request);
        
        // 如果 limit 参数未传递，设置默认值为 1000（用于树形结构）
        if (!$hasLimitParam) {
            $limit = 1000;
        } elseif ($limit > 1000) {
            // 限制最大数量为 1000
            $limit = 1000;
        }
        
        return [$where, $format, $limit, $field, $order, $page];
    }

    /**
     * 指定查询where条件,并没有真正的查询数据库操作
     * 重写父类方法，添加联查模型表
     * @param array $where
     * @param string|null $field
     * @param string $order
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\support\Model
     */
    protected function doSelect(array $where, ?string $field = null, string $order = 'desc')
    {
        Log::info('log test');
        $model = $this->model;
        
        // 处理 where 条件，明确指定表名避免字段歧义
        foreach ($where as $column => $value) {
            // 如果字段名不包含表名，添加表名前缀
            if (strpos($column, '.') === false) {
                $column = 'wa_arctype.' . $column;
            }
            
            if (is_array($value)) {
                if ($value[0] === 'like' || $value[0] === 'not like') {
                    $model = $model->where($column, $value[0], "%$value[1]%");
                } elseif (in_array($value[0], ['>', '=', '<', '<>'])) {
                    $model = $model->where($column, $value[0], $value[1]);
                } elseif ($value[0] == 'in' && !empty($value[1])) {
                    $valArr = $value[1];
                    if (is_string($value[1])) {
                        $valArr = explode(",", trim($value[1]));
                    }
                    $model = $model->whereIn($column, $valArr);
                } elseif ($value[0] == 'not in' && !empty($value[1])) {
                    $valArr = $value[1];
                    if (is_string($value[1])) {
                        $valArr = explode(",", trim($value[1]));
                    }
                    $model = $model->whereNotIn($column, $valArr);
                } elseif ($value[0] == 'null') {
                    $model = $model->whereNull($column);
                } elseif ($value[0] == 'not null') {
                    $model = $model->whereNotNull($column);
                } elseif ($value[0] !== '' || $value[1] !== '') {
                    $model = $model->whereBetween($column, $value);
                }
            } else {
                $model = $model->where($column, $value);
            }
        }
        
        // 处理排序字段，明确指定表名
        if ($field) {
            if (strpos($field, '.') === false) {
                $field = 'wa_arctype.' . $field;
            }
            $model = $model->orderBy($field, $order);
        }
        
        // 联查模型表，获取模型名称
        $model = $model->leftJoin('wa_channeltype', 'wa_arctype.current_channel', '=', 'wa_channeltype.id')
                      ->select('wa_arctype.*', 'wa_channeltype.title as channel_name');
        
        return $model;
    }

    /**
     * 查询后置处理：补充树形选择需要的字段
     * @param mixed $items
     * @return mixed
     */
    protected function afterQuery($items)
    {
        foreach ($items as $item) {
            // 兼容前端树组件的字段名
            $item->pid = $item->parent_id;
            // 提供通用的名称字段给树选择接口
            $item->name = $item->typename;
            // 如果没有关联到模型，显示空字符串
            if (empty($item->channel_name)) {
                $item->channel_name = '';
            }
        }
        return $items;
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
