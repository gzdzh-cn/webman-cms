<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\Arctype;
use plugin\admin\app\model\Channeltype;
use plugin\admin\app\controller\Crud;
use plugin\admin\app\common\Tree;
use plugin\admin\app\logic\ArctypeLogic;
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
            $data = $this->insertInput($request);
            $insertId = $this->doInsert($data);
            
            if ($insertId) {
                // 删除多余的问答栏目
                if (isset($data['current_channel']) && $data['current_channel'] == 51) {
                    Arctype::where('current_channel', 51)
                        ->where('id', '!=', $insertId)
                        ->delete();
                }
                
                return $this->json(0, 'ok', ['id' => $insertId]);
            }
            
            throw new BusinessException('操作失败!');
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
            $id = (int)($request->post('id', 0) ?: $request->get('id', 0));
            if (!$id) {
                return $this->json(1, 'ID不能为空');
            }
            
            $post = $request->post();
            
            // 如果只传了 id 和 sort_order，只更新 sort_order 字段（用于双击修改排序）
            if (isset($post['sort_order']) && count($post) <= 2) {
                $arctype = Arctype::find($id);
                if (!$arctype) {
                    return $this->json(1, '记录不存在');
                }
                $arctype->sort_order = (int)$post['sort_order'];
                $arctype->update_time = time();
                $arctype->save();
                return $this->json(0, 'ok', ['id' => $id]);
            }
            
            // 在 inputFilter 之前获取 inherit_option（因为这不是数据库字段，会被过滤）
            $inheritOption = $request->post('inherit_option', 0);
            $inheritOption = ($inheritOption == 1 || $inheritOption === '1') ? 1 : 0;
            
            [$id, $data] = $this->updateInput($request);
            
            // 如果勾选了继承选项，保存需要继承的字段值
            $inheritFields = [];
            if ($inheritOption == 1) {
                // 获取当前更新的 typearcrank 和 page_limit 值
                // 从 $data 中获取，如果不存在则从数据库记录中获取
                $currentArctype = Arctype::find($id);
                $inheritFields['typearcrank'] = $data['typearcrank'] ?? ($currentArctype->typearcrank ?? 0);
                $inheritFields['page_limit'] = $data['page_limit'] ?? ($currentArctype->page_limit ?? 0);
            }
            
            // 执行更新
            $this->doUpdate($id, $data);
            
            // 如果勾选了继承选项，更新所有下级栏目
            if ($inheritOption == 1 && !empty($inheritFields)) {
                $this->updateChildrenInheritFields($id, $inheritFields);
            }
            
            return $this->json(0);
        }
        return view('arctype/update');
    }

    
    /**
     * 更新所有下级栏目的继承字段
     * @param int $parentId 父栏目ID
     * @param array $inheritFields 需要继承的字段（typearcrank, page_limit）
     * @return void
     */
    protected function updateChildrenInheritFields(int $parentId, array $inheritFields): void
    {
        // 获取所有下级栏目ID
        $childrenIds = $this->getAllChildrenIds($parentId);
        
        if (empty($childrenIds)) {
            return;
        }
        
        // 构建更新数据
        $updateData = [
            'update_time' => time(),
        ];
        
        // 只更新传入的字段
        if (isset($inheritFields['typearcrank'])) {
            $updateData['typearcrank'] = $inheritFields['typearcrank'];
        }
        
        if (isset($inheritFields['page_limit'])) {
            $updateData['page_limit'] = $inheritFields['page_limit'];
        }
        
        // 批量更新所有下级栏目
        if (!empty($updateData)) {
            Arctype::whereIn('id', $childrenIds)
                ->where('is_del', 0)
                ->update($updateData);
        }
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        
        // 获取所有要删除的栏目ID（包括子栏目）
        $allIds = [];
        foreach ($ids as $id) {
            $childrenIds = $this->getAllChildrenIds($id);
            $allIds = array_merge($allIds, [$id], $childrenIds);
        }
        
        // 去重
        $allIds = array_unique($allIds);
        
        // 删除所有栏目（包括子栏目）
        if (!empty($allIds)) {
            $this->doDelete($allIds);
        }
        
        return $this->json(0);
    }

    /**
     * 递归获取所有子栏目ID
     * @param int $parentId 父栏目ID
     * @return array 所有子栏目ID数组
     */
    protected function getAllChildrenIds($parentId): array
    {
        $childrenIds = [];
        
        // 获取直接子栏目
        $children = Arctype::where('parent_id', $parentId)->pluck('id')->toArray();
        
        foreach ($children as $childId) {
            $childrenIds[] = $childId;
            // 递归获取子栏目的子栏目
            $grandChildren = $this->getAllChildrenIds($childId);
            $childrenIds = array_merge($childrenIds, $grandChildren);
        }
        
        return $childrenIds;
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
        
        // 验证数据
        $arctypeLogic = new ArctypeLogic();
        
        // 过滤字段文本，去掉前后空格
        $data = $arctypeLogic->trimFields($data);
        
        // 确保 page_limit 字段存在（即使为空也要保留）
        if (!isset($data['page_limit'])) {
            $data['page_limit'] = '';
        }
        
        $arctypeLogic->validateArctypeData($data);
        
        // 问答模型只能存在一个
        if (isset($data['current_channel']) && $data['current_channel'] == 51) {
            $ask_info = Arctype::where('current_channel', 51)
                ->where('is_del', 0)
                ->orderBy('id', 'desc')
                ->first();
            if ($ask_info) {
                throw new BusinessException('问答模型只能创建一个栏目!');
            }
        }
        
        // 处理公共数据
        $data = $arctypeLogic->processArctypeData($data);
        
        // 处理目录名称
        $dirname = $arctypeLogic->processDirname($data, 0);
        
        // 获取父栏目信息
        $parentInfo = $arctypeLogic->getParentInfo($data['parent_id'] ?? null);
        
        // 计算topid、channeltype、grade
        $topid = 0;
        $channeltype = $data['current_channel'] ?? 0;
        $grade = 0; // 默认等级为0（顶级栏目）
        if ($parentInfo) {
            $channeltype = $parentInfo['channeltype'];
            $topid = $parentInfo['topid'];
            // 栏目等级 = 父栏目等级 + 1
            $grade = $parentInfo['grade'] + 1;
        }
        
        // 计算目录路径
        $dirpath = $arctypeLogic->calculateDirpath($dirname, $data['parent_id'] ?? null);
        
        // 处理自定义目录路径
        $diy_dirpath = '';
        if (isset($data['diy_dirpath']) && !empty($data['diy_dirpath'])) {
            // 如果传入了自定义路径，使用自定义路径
            $diy_dirpath = '/' . trim($data['diy_dirpath'], '/');
        } else {
            // 否则使用 dirpath
            $diy_dirpath = $dirpath;
        }
        
        // 构建新数据
        $newData = [
            'topid' => $topid,
            'dirname' => $dirname,
            'dirpath' => $dirpath,
            'diy_dirpath' => $diy_dirpath,
            'typelink' => $data['typelink'] ?? '',
            'litpic' => $data['litpic'] ?? '',
            'channeltype' => $channeltype,
            'current_channel' => $data['current_channel'] ?? 0,
            'grade' => $grade, // 栏目等级
            'seo_keywords' => $data['seo_keywords'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'page_limit' => $data['page_limit'] ?? '', // 限制页面（已通过 processArctypeData 处理为逗号分隔字符串）
            'admin_id' => admin_id(),
            'empty_logic' => (isset($data['current_channel']) && $data['current_channel'] == 6) ? 0 : 1,
            'sort_order' => 100,
            'add_time' => time(),
            'update_time' => time(),
        ];
        
        // 合并数据
        $data = array_merge($data, $newData);
        
        return $data;
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
        // 验证数据
        $arctypeLogic = new ArctypeLogic();
        
        // 过滤字段文本，去掉前后空格
        $data = $arctypeLogic->trimFields($data);
        
        // 确保 page_limit 字段存在（即使为空也要保留）
        if (!isset($data['page_limit'])) {
            $data['page_limit'] = '';
        }
        
        $arctypeLogic->validateArctypeData($data);
        
        // 处理公共数据
        $data = $arctypeLogic->processArctypeData($data);
        
        // 处理目录名称
        $dirname = $arctypeLogic->processDirname($data, $id);
        
        // 获取当前记录信息
        $currentArctype = Arctype::find($id);
        if (!$currentArctype) {
            throw new BusinessException('栏目不存在');
        }
        
        // 判断父栏目和目录名称是否变化
        $parentIdChanged = isset($data['parent_id']) && $data['parent_id'] != $currentArctype->parent_id;
        $dirnameChanged = isset($data['dirname']) && trim($data['dirname']) != $currentArctype->dirname;
        
        // 获取父栏目信息
        $newParentId = $parentIdChanged ? ($data['parent_id'] ?? null) : ($currentArctype->parent_id ?? null);
        $parentInfo = $arctypeLogic->getParentInfo($newParentId);
        
        // 计算topid、channeltype、grade
        $topid = $currentArctype->topid ?? 0;
        $channeltype = $data['current_channel'] ?? $currentArctype->channeltype ?? 0;
        $grade = $currentArctype->grade ?? 0;
        
        // 如果父栏目变化，重新计算
        if ($parentIdChanged && $parentInfo) {
            $channeltype = $parentInfo['channeltype'];
            $topid = $parentInfo['topid'];
            // 栏目等级 = 父栏目等级 + 1
            $grade = $parentInfo['grade'] + 1;
        }
        
        // 计算目录路径
        $dirpath = $arctypeLogic->calculateDirpath(
            $dirname, 
            $newParentId, 
            $currentArctype->dirpath ?? '',
            $parentIdChanged,
            $dirnameChanged
        );
        
        // 处理自定义目录路径
        $diy_dirpath = '';
        if (isset($data['diy_dirpath']) && !empty($data['diy_dirpath'])) {
            // 如果传入了自定义路径，使用自定义路径
            $diy_dirpath = '/' . trim($data['diy_dirpath'], '/');
        } else {
            // 否则使用 dirpath
            $diy_dirpath = $dirpath;
        }
        
        // 构建更新数据
        $updateData = [
            'typename' => $data['typename'] ?? '',
            'dirname' => $dirname,
            'dirpath' => $dirpath,
            'diy_dirpath' => $diy_dirpath,
            'typelink' => $data['typelink'] ?? '',
            'litpic' => $data['litpic'] ?? '',
            'channeltype' => $channeltype,
            'current_channel' => $data['current_channel'] ?? $currentArctype->current_channel ?? 0,
            'topid' => $topid,
            'grade' => $grade,
            'seo_keywords' => $data['seo_keywords'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'typearcrank' => $data['typearcrank'] ?? $currentArctype->typearcrank ?? 0, // 访问权限
            'page_limit' => $data['page_limit'] ?? '', // 限制页面（已通过 processArctypeData 处理为逗号分隔字符串）
            'update_time' => time(),
        ];
        
        // 如果父栏目变化，更新 parent_id
        if ($parentIdChanged) {
            $updateData['parent_id'] = $data['parent_id'] ?? 0;
        }
        
        // 合并数据
        $data = array_merge($data, $updateData);
        
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
        $model = $this->model;
        
        // 处理 where 条件，明确指定表名避免字段歧义
        foreach ($where as $column => $value) {
            // 如果字段名不包含表名，添加表名前缀
            if (strpos($column, '.') === false) {
                $column = 'arctype.' . $column;
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
                $field = 'arctype.' . $field;
            }
            $model = $model->orderBy($field, $order);
        }
        
        // 联查模型表，获取模型名称
        $model = $model->leftJoin('channeltype', 'arctype.current_channel', '=', 'channeltype.id')
                      ->select('arctype.*', 'channeltype.title as channel_name');
        
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
            // 兼容 pearDtree 组件的字段名
            $item->parentId = $item->parent_id;
            $item->title = $item->typename;
            // 如果没有关联到模型，显示空字符串
            if (empty($item->channel_name)) {
                $item->channel_name = '';
            }
        }
        return $items;
    }

    /**
     * 格式化树 - 重写父类方法，转换为 pearDtree 期望的格式
     * @param $items
     * @param $total
     * @return Response
     */
    protected function formatTree($items, $total = 0): Response
    {
        if (empty($items)) {
            return $this->json(0, 'ok', []);
        }
        
        $format_items = [];
        $primary_key = $this->model->getKeyName();
        foreach ($items as $item) {
            // 确保所有必要的字段都存在
            $parentId = $item->pid ?? $item->parentId ?? $item->parent_id ?? 0;
            $title = $item->title ?? $item->typename ?? $item->name ?? (string)$item->$primary_key;
            
            $format_items[] = [
                'id' => (int)$item->$primary_key,
                'parentId' => (int)$parentId,
                'title' => (string)$title,
            ];
        }
        
        if (empty($format_items)) {
            return $this->json(0, 'ok', []);
        }
        
        $tree = new Tree($format_items, 'parentId');
        $treeData = $tree->getTree();
        
        // 递归转换字段名，确保所有节点都符合 pearDtree 格式
        $convertTree = function($nodes) use (&$convertTree) {
            if (empty($nodes) || !is_array($nodes)) {
                return [];
            }
            $result = [];
            foreach ($nodes as $node) {
                if (!is_array($node)) {
                    continue;
                }
                // 确保 id 和 parentId 是字符串或数字（pearDtree 可能期望字符串）
                $converted = [
                    'id' => isset($node['id']) ? (string)$node['id'] : '0',
                    'parentId' => isset($node['parentId']) ? (string)$node['parentId'] : '0',
                    'title' => isset($node['title']) ? (string)$node['title'] : (isset($node['name']) ? (string)$node['name'] : ''),
                ];
                // 递归处理子节点
                if (isset($node['children']) && is_array($node['children'])) {
                    if (!empty($node['children'])) {
                        $converted['children'] = $convertTree($node['children']);
                    }
                }
                $result[] = $converted;
            }
            return $result;
        };
        
        $treeData = $convertTree($treeData);
        return $this->json(0, 'ok', $treeData);
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

    /**
     * 获取模板文件列表
     * @param Request $request
     * @return Response
     */
    public function getTemplates(Request $request): Response
    {
        $channelId = $request->get('channel_id', 0); // 模型ID
        $type = $request->get('type', 'list'); // 类型：list 或 view
        $templateDir = base_path('template/pc');
        
        if (!is_dir($templateDir)) {
            return $this->json(1, '模板目录不存在');
        }
        
        $templates = [];
        $prefix = '';
        
        if ($channelId > 0) {
            // 根据模型ID获取nid
            $channelType = Channeltype::where('id', $channelId)->first();
            if (!$channelType) {
                return $this->json(1, '模型不存在');
            }
            
            $nid = $channelType->nid ?? '';
            
            // 根据模型类型确定模板前缀
            if ($channelId == 6) {
                // 单页模型：lists_single
                $prefix = 'lists_single';
            } elseif ($channelId == 8) {
                // 留言模型：lists_guestbook
                $prefix = 'lists_guestbook';
            } else {
                // 其他模型：根据nid匹配 lists_{nid} 或 view_{nid}
                if ($type == 'list') {
                    $prefix = 'lists_' . $nid;
                } else {
                    $prefix = 'view_' . $nid;
                }
            }
        } else {
            // 兼容旧的方式：直接使用prefix参数
            $prefix = $request->get('prefix', '');
        }
        
        if (!empty($prefix)) {
            // 读取指定前缀的模板文件
            $files = glob($templateDir . '/' . $prefix . '*.htm');
            if ($files) {
                foreach ($files as $file) {
                    $filename = basename($file);
                    $templates[] = [
                        'value' => $filename,
                        'name' => $filename
                    ];
                }
            }
        }
        
        return $this->json(0, 'success', $templates);
    }


}
