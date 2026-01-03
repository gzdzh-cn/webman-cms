<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
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
class ChannelfieldController extends Crud
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
        return view('channelfield/index', ['channel_id' => $request->get('channel_id', 0)]);
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
        
        // 处理 where 条件，明确指定表名避免字段歧义
        foreach ($where as $column => $value) {
            // 如果字段名不包含表名，添加表名前缀
            if (strpos($column, '.') === false) {
                $column = 'channelfield.' . $column;
            }
            
            // 特殊处理 is_release 和 ifsystem 的复杂条件
            if ($column === 'channelfield.is_release' && $value !== null) {
                // 移除 is_release 条件，稍后单独处理
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
        
        // 处理 is_release 和 ifsystem 的复杂条件
        // 条件：(is_release=1 AND ifsystem=1) OR (is_release=0 AND ifsystem=0)
        if (isset($where['is_release']) && $where['is_release'] !== null) {
            $model = $model->where(function($q) {
                $q->where(function($subQ) {
                    $subQ->where('channelfield.is_release', 1)
                         ->where('channelfield.ifsystem', 1);
                })->orWhere(function($subQ) {
                    $subQ->where('channelfield.is_release', 0)
                         ->where('channelfield.ifsystem', 0);
                });
            });
        }
        
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
                     ->where('channelfield_bind.typeid', '=', $typeid);
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
        foreach ($items as $item) {
            // 如果没有关联到模型，显示"通用"或空
            if (empty($item->channel_title)) {
                $item->channel_title = $item->channel_id == 0 ? '通用' : '';
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
            
            return view('channelfield/insert', [
                'channel_id' => $channel_id,
                'field_types' => $fieldTypesArray,
                'arctype_list' => $arctypeList
            ]);
        }
        
        // POST 请求处理
        $channel_id = (int)$request->get('channel_id', 0);
        if (empty($channel_id)) {
            return $this->json(1, '请选择所属模型');
        }
        
        $post = $request->post();
        
        // 使用共享逻辑处理字段数据
        $fieldLogic = new FieldLogic();
        $result = $fieldLogic->processFieldData($post, $channel_id);
        if (!$result['success']) {
            return $this->json(1, $result['error']);
        }
        
        $data = $result['data'];
        $post = $data['post'];
        $dfvalue = $data['dfvalue'];
        $maxlength = $data['maxlength'];
        $buideType = $data['buideType'];
        $ntabsql = $data['ntabsql'];
        $table = $data['table'];
        $typeids = $data['typeids'];
        
        // 执行新增字段到数据表（新增时才执行）
        try {
            $fieldLogic->addFieldToTable($table, $ntabsql);
        } catch (\Exception $e) {
            return $this->json(1, '操作失败：' . $e->getMessage());
        }
        
        // 保存新增字段的记录
        $newData = [
            'dfvalue' => $dfvalue,
            'maxlength' => $maxlength,
            'define' => $buideType,
            'ifcontrol' => 0,
            'sort_order' => (int)($post['sort_order'] ?? 100),
            'add_time' => time(),
            'update_time' => time(),
            'channel_id' => $channel_id,
            'title' => $post['title'],
            'name' => $post['name'],
            'dtype' => $post['dtype'],
            'ifmain' => 0,
            'status' => 1,
            'is_release' => (int)($post['is_release'] ?? 0),
            'is_screening' => (int)($post['is_screening'] ?? 0),
            'remark' => $post['remark'] ?? '',
            'dfvalue_unit' => $post['dfvalue_unit'] ?? '',
            'typeid' => (int)($post['typeid'] ?? 0),
            'set_type' => (int)($post['set_type'] ?? 0),
        ];
        
        $channelfield = new Channelfield();
        $channelfield->fill($newData);
        $channelfield->save();
        $field_id = $channelfield->id;
        
        // 多选项、单选项、下拉框
        if (in_array($post['dtype'], ['checkboxs','radios','selects'])) {
            $mdatas = [];
            foreach ($post['mcheck'] as $val) {
                $mdatas[] = [
                    'field_id' => $field_id,
                    'dfvalue' => trim($val['dfvalue'] ?? ''),
                    'name' => trim($post['name']),
                    'sort_order' => intval($val['sort_order'] ?? 0),
                    'add_time' => time(),
                    'update_time' => time(),
                    'dtype' => $post['dtype'],
                    'channel_id' => $channel_id,
                ];
            }
            if (!empty($mdatas)) {
                // 使用模型批量写入 field_custom_param
                try {
                    FieldCustomParam::insert($mdatas);
                } catch (\Exception $e) {
                    error_log('field_custom_param insert failed: ' . $e->getMessage());
                }
            }
        }
        
        // 保存栏目与字段绑定的记录
        if (!empty($typeids)) {
            $addData = [];
            foreach ($typeids as $val) {
                if (empty($val)) {
                    continue;
                }
                $addData[] = [
                    'typeid' => $val,
                    'field_id' => $field_id,
                    'add_time' => time(),
                    'update_time' => time(),
                ];
            }
            if (!empty($addData)) {
                try {
                    ChannelfieldBind::insert($addData);
                } catch (\Exception $e) {
                    error_log('channelfield_bind insert failed: ' . $e->getMessage());
                }
            }
        }
        
        return $this->json(0, 'ok', ['id' => $field_id]);
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
            
            $channel_id = (int)$request->get('channel_id', 0);
            
            // 从 logic 层获取 field_type 列表，传递 channel_id 用于过滤
            $fieldLogic = new FieldLogic();
            $fieldTypes = $fieldLogic->getFieldTypeList($channel_id, 'name,title,ifoption');
            
            // 获取字段数据
            $channelfield = Channelfield::find($id);
            if (!$channelfield) {
                return $this->json(1, '记录不存在');
            }
            
            // 处理 region 类型的反序列化
            $fieldData = $channelfield->toArray();
            if ($channelfield->dtype == 'region' && !empty($channelfield->dfvalue)) {
                try {
                    // 尝试反序列化（可能是序列化的字符串）
                    $regionData = @unserialize($channelfield->dfvalue);
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
                    $params = FieldCustomParam::where('field_id', $id)
                        ->orderBy('sort_order', 'asc')
                        ->get()
                        ->toArray();
                    $fieldData['mcheck'] = array_map(function($item) {
                        return [
                            'dfvalue' => $item['dfvalue'] ?? '',
                            'sort_order' => $item['sort_order'] ?? 0
                        ];
                    }, $params);
                } catch (\Exception $e) {
                    $fieldData['mcheck'] = [];
                }
            }
            
            return view('channelfield/update', [
                'channel_id' => $channel_id,
                'id' => $id,
                'field_types' => $fieldTypes,
                'field_data' => $fieldData
            ]);
        }
        
        // POST 请求：更新数据
        $id = (int)($request->post('id', 0) ?: $request->get('id', 0));
        if (!$id) {
            return $this->json(1, 'ID不能为空');
        }
        
        $channelfield = Channelfield::find($id);
        if (!$channelfield) {
            return $this->json(1, '记录不存在');
        }
        
        $channel_id = $channelfield->channel_id;
        $post = $request->post();
        
        // 如果只传了 id 和 ifeditable，只更新 ifeditable 字段（用于隐藏/显示功能）
        if (isset($post['ifeditable']) && count($post) <= 2) {
            $channelfield->ifeditable = (int)$post['ifeditable'];
            $channelfield->update_time = time();
            $channelfield->save();
            return $this->json(0, 'ok', ['id' => $id]);
        }
        
        // 如果只传了 id 和 sort_order，只更新 sort_order 字段（用于双击修改排序）
        if (isset($post['sort_order']) && count($post) <= 2) {
            $channelfield->sort_order = (int)$post['sort_order'];
            $channelfield->update_time = time();
            $channelfield->save();
            return $this->json(0, 'ok', ['id' => $id]);
        }
        
        // 使用共享逻辑处理字段数据（更新时排除旧字段名）
        $fieldLogic = new FieldLogic();
        $oldFieldName = $channelfield->name; // 获取旧字段名
        $result = $fieldLogic->processFieldData($post, $channel_id, $oldFieldName);
        if (!$result['success']) {
            return $this->json(1, $result['error']);
        }
        
        $data = $result['data'];
        $post = $data['post'];
        $dfvalue = $data['dfvalue'];
        $maxlength = $data['maxlength'];
        $buideType = $data['buideType'];
        $typeids = $data['typeids'];
        
        // 如果字段名或类型改变，需要修改数据表结构
        if ($channelfield->name != $post['name'] || $channelfield->dtype != $post['dtype']) {
            try {
                $table = $data['table'];
                $ntabsql = $data['ntabsql'];
                
                // 使用 CHANGE COLUMN 修改字段（更安全，不会丢失数据）
                $fieldLogic->changeFieldInTable($table, $channelfield->name, $ntabsql);
            } catch (\Exception $e) {
                // 如果 CHANGE COLUMN 失败（可能是字段类型不兼容），尝试 DROP + ADD
                try {
                    $db = \plugin\admin\app\common\Util::db();
                    $db->statement("ALTER TABLE `{$table}` DROP COLUMN `{$channelfield->name}`");
                    $fieldLogic->addFieldToTable($table, $ntabsql);
                } catch (\Exception $e2) {
                    return $this->json(1, '更新字段结构失败：' . $e2->getMessage());
                }
            }
        }
        
        // 更新字段记录
        $channelfield->fill([
            'dfvalue' => $dfvalue,
            'maxlength' => $maxlength,
            'define' => $buideType,
            'sort_order' => (int)($post['sort_order'] ?? 100),
            'update_time' => time(),
            'title' => $post['title'],
            'name' => $post['name'],
            'dtype' => $post['dtype'],
            'is_release' => (int)($post['is_release'] ?? 0),
            'is_screening' => (int)($post['is_screening'] ?? 0),
            'remark' => $post['remark'] ?? '',
            'dfvalue_unit' => $post['dfvalue_unit'] ?? '',
            'set_type' => (int)($post['set_type'] ?? 0),
            'ifeditable' => isset($post['ifeditable']) ? (int)$post['ifeditable'] : $channelfield->ifeditable,
        ]);
        $channelfield->save();
        
        // 更新选项参数（checkboxs/radios/selects）（使用模型）
        if (in_array($post['dtype'], ['checkboxs','radios','selects'])) {
            try {
                // 删除旧参数
                FieldCustomParam::where('field_id', $id)->delete();
                
                // 插入新参数
                if (!empty($post['mcheck'])) {
                    $mdatas = [];
                    foreach ($post['mcheck'] as $val) {
                        $mdatas[] = [
                            'field_id' => $id,
                            'dfvalue' => trim($val['dfvalue'] ?? ''),
                            'name' => trim($post['name']),
                            'sort_order' => intval($val['sort_order'] ?? 0),
                            'add_time' => time(),
                            'update_time' => time(),
                            'dtype' => $post['dtype'],
                            'channel_id' => $channel_id,
                        ];
                    }
                    if (!empty($mdatas)) {
                        FieldCustomParam::insert($mdatas);
                    }
                }
            } catch (\Exception $e) {
                error_log('field_custom_param update failed: ' . $e->getMessage());
            }
        }
        
        // 更新栏目绑定（使用模型）
        try {
            // 删除旧绑定
            ChannelfieldBind::where('field_id', $id)->delete();
            
            // 插入新绑定
            if (!empty($typeids)) {
                $addData = [];
                foreach ($typeids as $val) {
                    if (empty($val)) {
                        continue;
                    }
                    $addData[] = [
                        'typeid' => $val,
                        'field_id' => $id,
                        'add_time' => time(),
                        'update_time' => time(),
                    ];
                }
                if (!empty($addData)) {
                    ChannelfieldBind::insert($addData);
                }
            }
        } catch (\Exception $e) {
            error_log('channelfield_bind update failed: ' . $e->getMessage());
        }
        
        return $this->json(0, 'ok', ['id' => $id]);
    }

    /**
     * 字段删除
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $ids = $request->post('id', '');
        if (empty($ids)) {
            return $this->json(1, 'ID不能为空');
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return $this->json(1, 'ID格式错误');
        }
        
        // 先查询出要删除的字段记录，用于后续删除对应数据表的字段
        $fields = Channelfield::whereIn('id', $ids)->get();
        
        // 验证：系统字段（ifsystem=1）不能删除
        $systemFields = $fields->where('ifsystem', 1);
        if ($systemFields->isNotEmpty()) {
            $systemFieldNames = $systemFields->pluck('title', 'name')->map(function($title, $name) {
                return $title ? "{$title}({$name})" : $name;
            })->implode('、');
            return $this->json(1, "系统字段不能删除：" . $systemFieldNames);
        }
        
        // 删除附加表 / 主表中的实际字段（wa_article_content 等）
        if (!$fields->isEmpty()) {
            $fieldLogic = new FieldLogic();
            $db = \plugin\admin\app\common\Util::db();
            
            foreach ($fields as $field) {
                try {
                    /** @var Channelfield $field */
                    $channeltype = Channeltype::find($field->channel_id);
                    if (!$channeltype) {
                        continue;
                    }
                    // 解析目标数据表（优先 *_content，回落主表）
                    $table = $fieldLogic->resolveChannelTable($channeltype);
                    // 删字段：ALTER TABLE `table` DROP COLUMN `name`
                    $sql = "ALTER TABLE `{$table}` DROP COLUMN `{$field->name}`";
                    $db->statement($sql);
                } catch (\Exception $e) {
                    // 表不存在、字段不存在等情况，记录日志但不中断整个删除流程
                    error_log('drop channel field column failed: ' . $e->getMessage());
                }
            }
        }
        
        // 删除字段记录本身
        Channelfield::whereIn('id', $ids)->delete();
        
        // 同时删除与字段相关的绑定与选项数据，保持数据一致性（使用模型）
        try {
            ChannelfieldBind::whereIn('field_id', $ids)->delete();
            FieldCustomParam::whereIn('field_id', $ids)->delete();
        } catch (\Exception $e) {
            // 表不存在或其它异常时，记录日志但不影响主流程
            error_log('delete channelfield relations failed: ' . $e->getMessage());
        }
        
        return $this->json(0);
    }

    /**
     * 标签调用
     * @param Request $request
     * @return Response
     */
    public function tagCall(Request $request): Response
    {
        return view('channelfield/tagCall');
    }

    /**
     * 获取指定模型的栏目列表（树形结构）
     * @param Request $request
     * @return Response
     */
    public function getArctypeList(Request $request): Response
    {
        // 支持 channel_id 和 channeltype 两种参数名（前端使用 channeltype）
        $channel_id = (int)($request->get('channeltype', 0) ?: $request->get('channelId', 0));
        $format = $request->get('format', 'tree');
        $limit = (int)$request->get('limit', 2000);
        
        if ($channel_id > 0) {
            // 先查询指定 current_channel 的所有栏目
            $targetItems = Arctype::where('is_del', 0)
                ->where('current_channel', $channel_id)
                ->select('id', 'typename', 'parent_id', 'current_channel', 'sort_order')
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
                
                $parents = Arctype::where('is_del', 0)
                    ->whereIn('id', $currentParentIds)
                    ->select('id', 'typename', 'parent_id', 'current_channel', 'sort_order')
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
            $items = Arctype::where('is_del', 0)
                ->select('id', 'typename', 'parent_id', 'current_channel', 'sort_order')
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

