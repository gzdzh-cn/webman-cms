<?php

namespace app\cms\admin\controller;

use support\Request;
use support\Response;
use support\Log;
use plugin\admin\app\model\Channelfield;
use plugin\admin\app\model\Channeltype;
use plugin\admin\app\model\Arctype;
use plugin\admin\app\model\ChannelfieldBind;
use plugin\admin\app\model\FieldCustomParam;
use plugin\admin\app\model\Region;
use plugin\admin\app\logic\FieldLogic;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 频道字段管理
 */
class ChannelfieldController extends \plugin\admin\app\controller\Crud
{
    /**
     * @var Channelfield
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Channelfield;
    }
    /**
     * 字段列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return \app\cms\admin\support\AdminView::render('channelfield/index', ['channel_id' => $request->get('channel_id', 0)]);
    }

    /**
     * 查询前置方法（重写父类方法）
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function selectInput(Request $request): array
    {
        [$where, $format, $limit, $field, $order, $page] = parent::selectInput($request);
        
        // 添加默认条件：ifcontrol = 0
        $where['ifcontrol'] = 0;
        
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
        // An unscoped model-field list must not mix in the category namespace.
        if (!isset($where['channel_id']) && !isset($where['channelfield.channel_id'])) $model = $model->where('channelfield.channel_id', '>=', 0);
        
        // 处理 where 条件，明确指定表名避免字段歧义
        foreach ($where as $column => $value) {
            // 如果字段名不包含表名，添加表名前缀
            if (strpos($column, '.') === false) {
                $column = 'channelfield.' . $column;
            }
            
            // 特殊处理 only_published 参数，稍后单独处理
            if ($column === 'channelfield.only_published' && $value !== null) {
                continue;
            }
            
            // 如果传入了 is_release 参数，忽略它（不再支持）
            if ($column === 'channelfield.is_release' && $value !== null) {
                continue;
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
        
        // 处理 only_published 参数（只支持此参数，不再支持 is_release）
        // 条件：(is_release=1 AND ifsystem=1) OR (is_release=0 AND ifsystem=0)
        // if (isset($where['only_published']) && $where['only_published'] !== null) {
        //     $model = $model->where(function($q) {
        //         $q->where(function($subQ) {
        //             $subQ->where('channelfield.is_release', 1)
        //                  ->where('channelfield.ifsystem', 1);
        //         })->orWhere(function($subQ) {
        //             $subQ->where('channelfield.is_release', 0)
        //                  ->where('channelfield.ifsystem', 0);
        //         });
        //     });
        // }
        
        // 处理搜索条件 - 支持 keywords 和 keyword 两种参数名（兼容原项目）
        $request = request();
        $keywords = trim($request->get('keywords', '') ?: $request->get('keyword', ''));
        if ($keywords) {
            $keywords = addslashes($keywords); // 防止SQL注入
            $model = $model->where(function($q) use ($keywords) {
                $q->where('channelfield.title', 'like', "%{$keywords}%")
                  ->orWhere('channelfield.name', 'like', "%{$keywords}%");
            });
        }
        
        // 处理排序字段，明确指定表名
        if ($field) {
            if (strpos($field, '.') === false) {
                $field = 'channelfield.' . $field;
            }
            $model = $model->orderBy($field, $order);
        } else {
            // 默认排序：系统字段优先，然后按排序字段和ID排序
            $model = $model->orderBy('channelfield.ifsystem', 'desc')
                          ->orderBy('channelfield.sort_order', 'asc')
                          ->orderBy('channelfield.id', 'asc');
        }
        
        // 处理 typeid 参数：如果提供了 typeid，根据绑定关系过滤字段
        // 规则：系统字段（ifsystem=1）始终显示，非系统字段（ifsystem=0）只显示绑定到该栏目的字段
        $request = request();
        $typeid = (int)$request->get('typeid', 0);
        if ($typeid > 0) {
            // 使用 left join 连接 channelfield_bind 表，以便判断是否有绑定关系
            $model = $model->leftJoin('channelfield_bind', function($join) use ($typeid) {
                $join->on('channelfield.id', '=', 'channelfield_bind.field_id')
                     ->whereIn('channelfield_bind.typeid', [0, $typeid]);
            });
            
            // 添加过滤条件：系统字段（ifsystem=1）始终显示，非系统字段（ifsystem=0）只显示绑定的
            $model = $model->where(function($q) {
                $q->where('channelfield.ifsystem', '=', 1)  // 系统字段始终显示
                  ->orWhere(function($subQ) {
                      $subQ->where('channelfield.ifsystem', '=', 0)  // 非系统字段
                           ->whereNotNull('channelfield_bind.typeid');  // 必须有绑定关系
                  });
            });
        }
        
        // 联查模型表，获取模型名称和字段类型标题
        $model = $model->leftJoin('channeltype', 'channelfield.channel_id', '=', 'channeltype.id')
                      ->leftJoin('field_type', 'channelfield.dtype', '=', 'field_type.name')
                      ->select('channelfield.*', 'channeltype.title as channel_title', 'field_type.title as dtitle');
        
        // 如果有 typeid 过滤，需要去重（因为 join 可能会产生重复记录）
        if ($typeid > 0) {
            $model = $model->distinct();
        }
        
        return $model;
    }

    /**
     * 查询后置处理：补充栏目名称等字段
     * @param mixed $items
     * @return mixed
     */
    protected function afterQuery($items)
    {
        // Imported official rows identify choices by channel/name, not a newer field_id.
        $choiceFields = [];
        foreach ($items as $item) {
            if (in_array($item->dtype, ['checkboxs', 'radios', 'selects'], true)) {
                $choiceFields[] = $item;
            }
        }
        $choices = [];
        if ($choiceFields) {
            $params = FieldCustomParam::whereIn('channel_id', array_unique(array_map(static fn($item) => $item->channel_id, $choiceFields)))
                ->whereIn('name', array_unique(array_map(static fn($item) => $item->name, $choiceFields)))
                ->orderBy('sort_order', 'asc')->orderBy('id', 'asc')
                ->get(['id', 'channel_id', 'name', 'dfvalue']);
            foreach ($params as $param) {
                $choices[$param->channel_id][$param->name][] = ['value' => (string)$param->id, 'text' => (string)$param->dfvalue];
            }
        }
        foreach ($items as $item) {
            // 如果没有关联到模型，显示"通用"或空
            if (empty($item->channel_title)) {
                $item->channel_title = $item->channel_id == -99 ? '栏目字段' : ($item->channel_id == 0 ? '通用' : '');
            }
            if (in_array($item->dtype, ['checkboxs', 'radios', 'selects'], true)) {
                $item->options = $choices[$item->channel_id][$item->name] ?? [];
            }
        }
        return $items;
    }

    /**
     * 字段新增
     * @param Request $request
     * @return Response
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'GET') {
            $channel_id = (int)$request->get('channel_id', 0);
            
            // 获取字段类型列表
            $fieldLogic = $channel_id === -99 ? new \plugin\admin\app\logic\CategoryFieldLogic() : new FieldLogic();
            $fieldTypes = $fieldLogic->getFieldTypeList($channel_id, '*');
            
            // 处理数据，确保格式正确
            $fieldTypesArray = [];
            foreach ($fieldTypes as $ft) {
                $item = is_array($ft) ? $ft : (is_object($ft) ? $ft->toArray() : []);
                
                // 确保 name 存在
                if (empty($item['name'])) {
                    continue;
                }
                
                // 确保 ifoption 存在
                if (!isset($item['ifoption'])) {
                    $requireOptionTypes = ['radio', 'checkbox', 'select', 'region'];
                    $item['ifoption'] = in_array($item['name'], $requireOptionTypes) ? 1 : 0;
                }
                
                $fieldTypesArray[] = $item;
            }
            
            // 获取可见栏目列表
            $arctypeList = [];
            if ($channel_id > 0) {
                $query = Arctype::where('is_del', 0);
                $query = $query->where('channeltype', $channel_id);
                $items = $query->orderBy('sort_order', 'asc')
                              ->orderBy('id', 'asc')
                              ->limit(2000)
                              ->get()
                              ->toArray();
                
                // 处理数据，转换为树形结构
                foreach ($items as &$item) {
                    $item['pid'] = $item['parent_id'];
                    $item['name'] = $item['typename'];
                }
                $arctypeList = $this->buildTree($items);
            }
            
            return \app\cms\admin\support\AdminView::render('channelfield/insert', [
                'channel_id' => $channel_id,
                'field_types' => $fieldTypesArray,
                'arctype_list' => $arctypeList
            ]);
        }
        
        try {
            $scope = (int)($request->get('channel_id', 0) ?: $request->post('channel_id', 0));
            $service = $scope === -99 ? new \app\cms\service\CategoryFieldService() : new \app\cms\service\FieldService();
            $id = $service->save($request->post(), $scope);
            return $this->json(0, 'ok', ['id' => $id]);
        } catch (\Throwable $error) { return $this->fieldFailure($error); }
    }

    /**
     * 字段更新
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'GET') {
            $id = (int)$request->get('id', 0);
            if (!$id) {
                return $this->json(1, 'ID不能为空');
            }
            
            // 先读取字段，以持久化所属类型生成编辑列表。
            $channelfield = Channelfield::find($id);
            if (!$channelfield) {
                return $this->json(1, '记录不存在');
            }
            $channel_id = (int)$channelfield->channel_id;
            $fieldLogic = $channel_id === -99 ? new \plugin\admin\app\logic\CategoryFieldLogic() : new FieldLogic();
            $fieldTypes = $channel_id === -99
                ? $fieldLogic->getFieldTypeListForEdit((string)$channelfield->dtype, 'name,title,ifoption')
                : $fieldLogic->getFieldTypeList($channel_id, 'name,title,ifoption');
            
            // 处理 region 类型的反序列化
            $fieldData = $channelfield->toArray();
            if ($channel_id === -99) $fieldData['title'] = htmlspecialchars_decode((string)$fieldData['title'], ENT_COMPAT);
            if ($channelfield->dtype == 'region' && !empty($channelfield->dfvalue)) {
                try {
                    // 尝试反序列化（可能是序列化的字符串）
                    $regionData = @unserialize($channelfield->dfvalue, ['allowed_classes' => false]);
                    if ($regionData === false && $channelfield->dfvalue !== serialize(false)) {
                        // 如果不是序列化字符串，尝试 JSON 解析
                        $regionData = json_decode($channelfield->dfvalue, true);
                    }
                    if (is_array($regionData)) {
                        $fieldData['region_data'] = $regionData;
                    }
                } catch (\Exception $e) {
                    // 反序列化失败，忽略
                }
            }
            
            // 获取字段绑定的栏目（使用模型）
            try {
                $binds = ChannelfieldBind::where('field_id', $id)
                    ->pluck('typeid')
                    ->toArray();
                $fieldData['typeids'] = $binds;
            } catch (\Exception $e) {
                $fieldData['typeids'] = [];
            }
 
            // 获取选项参数（checkboxs/radios/selects）（使用模型）
            if (in_array($channelfield->dtype, ['checkboxs','radios','selects'])) {
                try {
                    $params = $channel_id === -99 ? FieldCustomParam::where('channel_id', -99)->where('name', $channelfield->name) : FieldCustomParam::where('field_id', $id);
                    $params = $params
                        ->orderBy('sort_order', 'asc')
                        ->get()
                        ->toArray();
                    $fieldData['mcheck'] = array_map(function($item) {
                        return [
                            'id' => $item['id'],
                            'dfvalue' => $item['dfvalue'] ?? '',
                            'sort_order' => $item['sort_order'] ?? 0
                        ];
                    }, $params);
                } catch (\Exception $e) {
                    $fieldData['mcheck'] = [];
                }
            }
            
            return \app\cms\admin\support\AdminView::render('channelfield/update', [
                'channel_id' => $channel_id,
                'id' => $id,
                'field_types' => $fieldTypes,
                'field_data' => $fieldData
            ]);
        }
        
        try {
            $id = (int)($request->post('id', 0) ?: $request->get('id', 0));
            if (!$id) { return $this->json(1, 'ID不能为空'); }
            $scope = (int)Channelfield::where('id', $id)->value('channel_id');
            $service = $scope === -99 ? new \app\cms\service\CategoryFieldService() : new \app\cms\service\FieldService();
            return $this->json(0, 'ok', ['id' => $service->save($request->post(), $scope, $id)]);
        } catch (\Throwable $error) { return $this->fieldFailure($error); }
    }

    /**
     * 字段删除
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $ids = $request->post('id', []);
        $ids = array_values(array_filter(array_map('intval', is_array($ids) ? $ids : explode(',', $ids))));
        if (!$ids) { return $this->json(1, '请选择字段'); }
        try {
            $scopes = Channelfield::whereIn('id', $ids)->distinct()->pluck('channel_id')->toArray();
            if (in_array(-99, array_map('intval', $scopes), true) && count($scopes) !== 1) throw new \InvalidArgumentException('栏目字段和模型字段请分别删除');
            $service = array_map('intval', $scopes) === [-99] ? new \app\cms\service\CategoryFieldService() : new \app\cms\service\FieldService();
            $service->delete($ids); return $this->json(0);
        }
        catch (\Throwable $error) { return $this->fieldFailure($error); }
    }

    private function fieldFailure(\Throwable $error): Response
    {
        if ($error instanceof \InvalidArgumentException) { return $this->json(1, $error->getMessage()); }
        Log::warning('CMS field schema operation failed', ['error_type' => get_class($error), 'file' => basename($error->getFile()), 'line' => $error->getLine()]);
        return $this->json(1, '字段变更失败，请检查字段类型或备份后重试');
    }

    public function categoryFields(Request $request): Response
    {
        try { return $this->json(0, 'ok', (new \app\cms\service\CategoryFieldService())->editorFields((int)$request->get('typeid', 0))); }
        catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
    }

    /**
     * 标签调用
     * @param Request $request
     * @return Response
     */
    public function tagCall(Request $request): Response
    {
        return \app\cms\admin\support\AdminView::render('channelfield/tagCall');
    }

    /**
     * 获取指定模型的栏目列表（树形结构）
     * @param Request $request
     * @return Response
     */
    public function getArctypeList(Request $request): Response
    {
        $lang = $request->get('lang', \app\cms\CmsContext::current()->lang);
        $contentAccess = new \app\cms\service\ContentAccessService();
        if (!is_string($lang) || !(new \app\cms\repository\CmsRepository())->table('language')->where('mark', $lang)->find()) {
            return json(['code' => 1, 'msg' => '语言不存在', 'data' => []]);
        }
        // 支持 channel_id 和 channeltype 两种参数名（前端使用 channeltype）
        $channel_id = (int)($request->get('channeltype', 0) ?: $request->get('channelId', 0));
        $format = $request->get('format', 'tree');
        $limit = (int)$request->get('limit', 2000);
        
        if ($channel_id > 0) {
            // 先查询指定 current_channel 的所有栏目
            $targetItems = $contentAccess->filterCategoryQuery(Arctype::where('is_del', 0))
                ->where('lang', $lang)
                ->where('current_channel', $channel_id)
                ->select('id', 'typename', 'parent_id', 'current_channel', 'sort_order', 'lang')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->limit($limit)
                ->get()
                ->toArray();
            
            // 收集所有需要查询的父级 ID（递归收集）
            $allItemsMap = []; // 用于存储所有栏目，避免重复查询
            $parentIdsToQuery = []; // 待查询的父级 ID 集合
            
            // 先将目标栏目放入 map
            foreach ($targetItems as $item) {
                $allItemsMap[$item['id']] = $item;
                if (!empty($item['parent_id']) && !isset($allItemsMap[$item['parent_id']])) {
                    $parentIdsToQuery[$item['parent_id']] = $item['parent_id'];
                }
            }
            
            // 递归查询所有父级栏目
            while (!empty($parentIdsToQuery)) {
                $currentParentIds = array_values($parentIdsToQuery);
                $parentIdsToQuery = []; // 清空，准备收集新的父级 ID
                
                $parents = $contentAccess->filterCategoryQuery(Arctype::where('is_del', 0))
                    ->where('lang', $lang)
                    ->whereIn('id', $currentParentIds)
                    ->select('id', 'typename', 'parent_id', 'current_channel', 'sort_order', 'lang')
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc')
                    ->get()
                    ->toArray();
                
                foreach ($parents as $parent) {
                    if (!isset($allItemsMap[$parent['id']])) {
                        $allItemsMap[$parent['id']] = $parent;
                        // 如果父级栏目还有父级，继续收集
                        if (!empty($parent['parent_id']) && !isset($allItemsMap[$parent['parent_id']])) {
                            $parentIdsToQuery[$parent['parent_id']] = $parent['parent_id'];
                        }
                    }
                }
            }
            
            // 转换为数组并标记哪些栏目可以勾选
            $items = [];
            foreach ($allItemsMap as $item) {
                $item['checkable'] = ($item['current_channel'] == $channel_id) ? 1 : 0;
                $items[] = $item;
            }
            
            // 对结果进行排序（与栏目列表排序一致：sort_order asc, id asc）
            usort($items, function($a, $b) {
                if ($a['sort_order'] != $b['sort_order']) {
                    return $a['sort_order'] - $b['sort_order'];
                }
                return $a['id'] - $b['id'];
            });
        } else {
            // 如果没有指定 channel_id，返回所有栏目，都可以勾选
            $items = $contentAccess->filterCategoryQuery(Arctype::where('is_del', 0))
                ->where('lang', $lang)
                ->select('id', 'typename', 'parent_id', 'current_channel', 'sort_order', 'lang')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->limit($limit)
                ->get()
                ->toArray();
            
            foreach ($items as &$item) {
                $item['checkable'] = 1; // 没有指定 channel_id 时，所有都可以勾选
            }
            unset($item);
        }
        
        // An allowed child remains selectable without exposing a forbidden parent.
        $visibleIds = array_fill_keys(array_map('intval', array_column($items, 'id')), true);
        foreach ($items as &$item) if (!isset($visibleIds[(int)$item['parent_id']])) $item['parent_id'] = 0;
        unset($item);

        // 如果是树形格式，先转换为树形结构（buildTree 需要使用 parent_id）
        if ($format === 'tree') {
            $items = $this->buildTree($items);
            // 处理树形数据，只保留前端需要的字段
            $items = $this->processTreeItems($items);
        } else {
            // 非树形格式，只返回前端需要的字段
            foreach ($items as &$item) {
                $item = [
                    'id' => $item['id'],
                    'typename' => $item['typename'],
                    'name' => $item['typename'], // 兼容前端使用 name 字段
                    'lang' => $item['lang'],
                    'checkable' => $item['checkable'] ?? 1,
                ];
            }
            unset($item);
        }
        
        return json(['code' => 0, 'msg' => 'ok', 'data' => $items]);
    }
    

    /**
     * 获取字段类型列表（接口）
     * @param Request $request
     * @return Response
     */
    public function getFieldTypeList(Request $request): Response
    {
        $channel_id = (int)$request->get('channel_id', 0);
        
        $fieldLogic = new FieldLogic();
        $fieldTypes = $fieldLogic->getFieldTypeList($channel_id, '*');
        
        // 处理数据，确保格式正确
        $fieldTypesArray = [];
        foreach ($fieldTypes as $ft) {
            $item = is_array($ft) ? $ft : (is_object($ft) ? $ft->toArray() : []);
            
            // 确保 name 存在
            if (empty($item['name'])) {
                continue;
            }
            
            // 确保 ifoption 存在
            if (!isset($item['ifoption'])) {
                $requireOptionTypes = ['radio', 'checkbox', 'select', 'region'];
                $item['ifoption'] = in_array($item['name'], $requireOptionTypes) ? 1 : 0;
            }
            
            $fieldTypesArray[] = $item;
        }
        
        return json(['code' => 0, 'msg' => 'ok', 'data' => $fieldTypesArray]);
    }

    /**
     * 获取区域数据（三级联动）
     * 参考：EyouCMS Field::ajax_get_region_data
     * @param Request $request
     * @return Response
     */
    public function ajaxGetRegionData(Request $request): Response
    {
        $parent_id = (int)$request->post('parent_id', 0);
        empty($parent_id) && $parent_id = 0;
        
        try {
            // 获取特殊城市ID列表（可以从配置读取）
            $specialCityIds = []; // 可以后续从配置读取 config('global.field_region_type')
            
            // 获取指定区域ID下的城市并判断是否需要处理特殊市返回值
            // 特殊市：北京市，上海市，天津市，重庆市
            $regionData = Region::getSpecialCityRegionList($parent_id, $specialCityIds);
            
            // 处理数据
            $region_html = $region_names = $region_ids = '';
            if ($regionData && count($regionData) > 0) {
                // 拼装下拉选项
                foreach ($regionData as $key => $value) {
                    $item = is_array($value) ? $value : (is_object($value) ? (array)$value : []);
                    $region_id = $item['id'] ?? 0;
                    $region_name = $item['name'] ?? '';
                    
                    $region_html .= "<option value='{$region_id}'>{$region_name}</option>";
                    if ($key > 0) {
                        $region_names .= '，';
                        $region_ids   .= ',';
                    }
                    $region_names .= $region_name;
                    $region_ids   .= $region_id;
                }
            } else if ($parent_id > 0) {
                // 如果没有子级数据，返回当前选择的区域信息
                $currentRegion = Region::find($parent_id);
                if ($currentRegion) {
                    $region_name = $currentRegion->name ?? '';
                    $region_names = $region_name;
                    $region_ids = (string)$parent_id;
                }
            }
            
            // 获取特殊城市配置（不需要显示城市选择框的父级ID数组）
            // 这里可以从配置文件读取，如果没有配置则使用默认值
            $parent_array = []; // 可以后续从配置读取 config('global.field_region_all_type')
            
            return json([
                'code' => 0,
                'msg' => 'ok',
                'region_html'  => $region_html,
                'region_names' => $region_names,
                'region_ids'   => $region_ids,
                'parent_array' => $parent_array
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 1,
                'msg' => '获取区域数据失败：' . $e->getMessage(),
                'region_html' => '',
                'region_names' => '',
                'region_ids' => '',
                'parent_array' => []
            ]);
        }
    }

    /**
     * 处理树形数据，只保留前端需要的字段
     * @param array $items
     * @return array
     */
    private function processTreeItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $processed = [
                'id' => $item['id'],
                'typename' => $item['typename'],
                'name' => $item['typename'], // 兼容前端使用 name 字段
                'checkable' => isset($item['checkable']) ? $item['checkable'] : 1, // 是否可勾选，1=可勾选，0=不可勾选
                'current_channel' => isset($item['current_channel']) ? $item['current_channel'] : 0, // 保留 current_channel 字段
                'lang' => $item['lang'] ?? 'cn',
            ];
            
            // 如果有 children，递归处理
            if (isset($item['children']) && is_array($item['children'])) {
                $processed['children'] = $this->processTreeItems($item['children']);
            }
            
            $result[] = $processed;
        }
        return $result;
    }
    
    /**
     * 将扁平数据转换为树形结构
     * @param array $items
     * @param int $pid
     * @return array
     */
    private function buildTree(array $items, $pid = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $pid) {
                $children = $this->buildTree($items, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        
        // 对树节点进行排序（与栏目列表排序一致：sort_order asc, id asc）
        usort($tree, function($a, $b) {
            if ($a['sort_order'] != $b['sort_order']) {
                return $a['sort_order'] - $b['sort_order'];
            }
            return $a['id'] - $b['id'];
        });
        
        return $tree;
    }
}

