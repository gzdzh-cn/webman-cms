<?php

namespace app\cms\admin\controller;

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
class ArctypeController extends \plugin\admin\app\controller\Crud
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
        return \app\cms\admin\support\AdminView::render('arctype/index');
    }

    public function batch(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        try {
            return (new \app\cms\service\CategoryStructureAccessService())->run($this->structureLanguage($request),function($r){
                (new \app\cms\service\ContentAccessService($r))->assertCreateCategory();
                $language=\app\cms\CmsContext::current()->lang;
                $models=$r->table('channeltype')->where(['is_del'=>0,'status'=>1])->where('id','<>',51)->field('id,title,nid')->order('id')->select()->toArray();
                $models=array_values(array_filter($models,static fn($model)=>!in_array($model['nid'],['ask','shop','shop_product','users','member'],true)));
                $categories=(new \app\cms\service\ContentAccessService($r))->filterCategoryQuery($r->table('arctype'))->where(['lang'=>$language,'is_del'=>0])->field('id,parent_id,typename,grade')->order('sort_order,id')->select()->toArray();
                return \app\cms\admin\support\AdminView::raw('arctype/batch',['batchModels'=>$models,'batchCategories'=>$categories,'batchLanguage'=>$language]);
            });
        }catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
    }

    public function batchPreview(Request $request): Response
    {
        return $this->batchWrite($request,fn($service,$language)=>$service->preview($request->post(),$language));
    }

    public function batchRun(Request $request): Response
    {
        return $this->batchWrite($request,fn($service,$language)=>$service->execute((string)$request->post('plan_token',''),$language));
    }

    public function batchStatus(Request $request): Response
    {
        if($request->method()!=='GET')return response('Method Not Allowed',405);
        try{return $this->json(0,'ok',(new \app\cms\service\CategoryBatchService())->status((string)$request->get('plan_token',''),\app\cms\CmsContext::current()->lang));}
        catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
    }

    private function batchWrite(Request $request,callable $operation): Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        try{
            \app\cms\admin\support\LanguageAdminSecurity::assertWrite($request);
            $result=$operation(new \app\cms\service\CategoryBatchService(),\app\cms\CmsContext::current()->lang);
            return $this->json(0,$result['status']==='complete'?'栏目已创建':'预检完成',$result);
        }catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
        catch(\Throwable $error){\support\Log::error('Category batch failed: '.$error->getMessage());return $this->json(1,'批量创建未完成，请检查任务状态或重新预检；已提交的任务不会重复创建');}
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        try {
            return (new \app\cms\service\CategoryStructureAccessService())->run($this->structureLanguage($request), function ($repository) use ($request) {
                if ($request->method() !== 'POST') { return \app\cms\admin\support\AdminView::render('arctype/insert'); }
                $id = (new \app\cms\service\CategoryService($repository))->save($request->post(), (int)admin_id());
                return $this->json(0, '操作成功', ['id' => $id]);
            }, $request->method() === 'POST');
        } catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
    }

    /** Same current-language resolution as LanguageController::save. */
    private function structureLanguage(Request $request): ?string
    {
        return \app\cms\CmsContext::current()->lang;
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() !== 'POST') { return \app\cms\admin\support\AdminView::render('arctype/update'); }
        try {
            $id = (int)($request->post('id', 0) ?: $request->get('id', 0));
            if ($id <= 0) { return $this->json(1, 'ID不能为空'); }
            (new \app\cms\service\CategoryService())->save($request->post(), (int)admin_id(), $id);
            return $this->json(0, '操作成功', ['id' => $id]);
        } catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
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
        if ($request->method() !== 'POST') return $this->json(1, '删除失败');
        try {
            return (new \app\cms\service\CategoryStructureAccessService())->run($this->structureLanguage($request), function ($repository) use ($request) {
                (new \app\cms\service\CategoryService($repository))->recycle($this->deleteInput($request));
                return $this->json(0, '已移入回收站');
            }, true);
        } catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
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
        
        return (new \app\cms\service\ContentAccessService())->filterCategoryQuery($model, 'arctype');
    }

    /**
     * 查询后置处理：补充树形选择需要的字段
     * @param mixed $items
     * @return mixed
     */
    protected function afterQuery($items)
    {
        $pending = $this->pendingCategoryTranslations($items);
        foreach ($items as $item) {
            $item->pending_translation = isset($pending[(int)$item->id])
                && $pending[(int)$item->id] === (string)$item->lang;
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

    /** Read pending labels in one query; corrupt metadata must not become a trusted status. */
    private function pendingCategoryTranslations($items): array
    {
        $names = [];
        foreach ($items as $item) $names[] = 'arctype.'.(int)$item->id;
        if (!$names) return [];
        $rows = (new \app\cms\repository\CmsRepository())->table('cms_state_records')
            ->where('scope', 'language.pending')->whereIn('name', $names)->select()->toArray();
        $pending = [];
        foreach ($rows as $row) {
            if (!hash_equals((string)$row['sha256'], hash('sha256', (string)$row['payload']))) {
                throw new \RuntimeException('语言状态校验失败');
            }
            $value = json_decode((string)$row['payload'], true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($value)) throw new \RuntimeException('语言状态格式无效');
            if (($value['state'] ?? '') === 'pending' && is_string($value['lang'] ?? null)) {
                $pending[(int)substr((string)$row['name'], strlen('arctype.'))] = $value['lang'];
            }
        }
        return $pending;
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
                'pending_translation' => (bool)($item->pending_translation ?? false),
                'current_channel' => (int)$item->current_channel,
                'lang' => (string)$item->lang,
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
                    'pending_translation' => (bool)($node['pending_translation'] ?? false),
                    'current_channel' => (int)($node['current_channel'] ?? 0),
                    'lang' => (string)($node['lang'] ?? ''),
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
    public function base(Request $request): Response
    {
        return $this->legacyEditor($request, 'base');
    }

    /**
     * 新增页面 - 高级设置 Tab 内容
     * 仅返回视图，用于通过 /app/admin/arctype/advanced 加载 advanced.html
     * @return Response
     */
    public function advanced(Request $request): Response
    {
        return $this->legacyEditor($request, 'advanced');
    }

    /** Retain old editor URLs without depending on removed iframe partials. */
    private function legacyEditor(Request $request, string $section): Response
    {
        $id = (int)$request->get('id', 0);
        if ($id < 1) return $this->json(1, '请选择要编辑的栏目');
        $row = (new \app\cms\repository\CmsRepository())->table('arctype')->where('id', $id)->find();
        if (!$row || !empty($row['is_del'])) return $this->json(1, '栏目不存在或已在回收站');
        try { (new \app\cms\service\ContentAccessService())->assertCategory($row); }
        catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
        return \app\cms\admin\support\AdminView::render('arctype/update', ['initialSection' => $section]);
    }

    /**
     * 获取模板文件列表
     * @param Request $request
     * @return Response
     */
    public function getTemplates(Request $request): Response
    {
        $channelId = (int)$request->get('channel_id', 0);
        $type = (string)$request->get('type', 'list');
        if (!in_array($type, ['list', 'view'], true)) { return $this->json(1, '模板类型错误'); }
        $device = (string)$request->get('device', 'pc');
        if (!in_array($device, ['pc', 'mobile'], true)) { return $this->json(1, '模板设备错误'); }
        $repository = new \app\cms\repository\CmsRepository();
        $language = (string)$request->get('lang', \app\cms\CmsContext::current()->lang);
        $themeRow = $repository->table('config')->where(['name' => 'web_tpl_theme', 'lang' => $language, 'is_del' => 0])->find();
        try { $theme = \app\cms\ThemeCatalog::configured($themeRow ? (string)$themeRow['value'] : null); }
        catch (\InvalidArgumentException $error) { return $this->json(1, '模板主题目录错误'); }
        $root = realpath(base_path('template'));
        $templateDir = realpath(base_path('template/' . ($theme === '' ? '' : $theme . '/') . $device));
        if (!$root || !$templateDir || !str_starts_with($templateDir . DIRECTORY_SEPARATOR, $root . DIRECTORY_SEPARATOR) || !is_dir($templateDir)) { return $this->json(1, '模板目录不存在'); }
        $prefix = (string)$request->get('prefix', '');
        $family = $type === 'list' ? 'lists_' : 'view_';
        if ($channelId > 0) {
            $model = $repository->table('channeltype')->where(['id' => $channelId, 'is_del' => 0])->find();
            if (!$model) { return $this->json(1, '模型不存在'); }
            $prefix = in_array($channelId, [6, 8], true) ? 'lists_' . $model['nid'] : $family . $model['nid'];
        }
        if ($prefix !== '' && !preg_match('/^[a-zA-Z0-9_-]+$/', $prefix)) { return $this->json(1, '模板前缀错误'); }
        // A custom model may reuse the article templates supplied by an original theme.
        $files = array_filter(scandir($templateDir), static fn($file) => preg_match('/^' . preg_quote($family, '/') . '[a-zA-Z0-9_-]+\.html?$/', $file) && is_file($templateDir . '/' . $file));
        usort($files, static function ($left, $right) use ($prefix) {
            $preferred = (int)str_starts_with($right, $prefix) <=> (int)str_starts_with($left, $prefix);
            return $preferred ?: strnatcasecmp($left, $right);
        });
        $templates = array_map(static fn($filename) => ['value' => $filename, 'name' => $filename], $files);
        return $this->json(0, 'success', $templates);
    }


}
