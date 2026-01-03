<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\Archive;
use plugin\admin\app\model\Arctype;
use plugin\admin\app\model\Channeltype;
use plugin\admin\app\model\Channelfield;
use plugin\admin\app\logic\FieldLogic;
use plugin\admin\app\common\Util;
use plugin\admin\app\common\Auth;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 内容管理 
 */
class ArchiveController extends Crud
{
    
    /**
     * @var Archive
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Archive;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('archive/index');
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
            $post = $request->post();
            
            // 获取栏目信息
            $typeid = (int)($post['typeid'] ?? 0);
            if (!$typeid) {
                return $this->json(1, '请选择栏目');
            }
            
            $arctype = Arctype::find($typeid);
            if (!$arctype) {
                return $this->json(1, '栏目不存在');
            }
            
            $channelId = $arctype->current_channel ?? 0;
            if (!$channelId) {
                return $this->json(1, '模型ID不能为空');
            }
            
            // 分离主表字段和动态字段
            $mainFields = ['typeid', 'stypeid', 'channel', 'title', 'subtitle', 'introduction', 'litpic', 
                          'is_b', 'is_head', 'is_special', 'is_top', 'is_recom', 'is_jump', 'is_litpic', 
                          'is_roll', 'is_slide', 'is_diyattr', 'origin', 'author', 'click', 'arcrank', 
                          'jumplinks', 'ismake', 'seo_title', 'seo_keywords', 'seo_description', 
                          'attrlist_id', 'merchant_id', 'free_shipping', 'users_price', 'crossed_price', 
                          'users_discount_type', 'users_free', 'old_price', 'sales_num', 'virtual_sales', 
                          'sales_all', 'stock_count', 'stock_show', 'prom_type', 'logistics_type', 
                          'tempview', 'status', 'sort_order', 'lang', 'admin_id', 'users_id', 
                          'arc_level_id', 'restric_type', 'users_score', 'is_del', 'del_method', 
                          'joinaid', 'downcount', 'appraise', 'collection', 'htmlfilename', 
                          'province_id', 'city_id', 'area_id', 'add_time', 'update_time', 
                          'removal_time', 'no_vip_pay', 'editor_remote_img_local', 'editor_img_clear_link', 
                          'editor_ai_create', 'reason', 'stock_code'];
            
            $mainData = [];
            $dynamicData = [];
            
            // 获取该模型的所有字段定义
            $fieldLogic = new FieldLogic();
            $channelFields = Channelfield::where('channel_id', $channelId)
                ->where('ifeditable', 1)
                ->pluck('name')
                ->toArray();
            
            // 分离数据
            foreach ($post as $key => $value) {
                if (in_array($key, $mainFields)) {
                    $mainData[$key] = $value;
                } elseif (in_array($key, $channelFields) || in_array($key, ['content', 'content_ey_m'])) {
                    $dynamicData[$key] = $value;
                }
            }
            
            // 使用父类的 inputFilter 过滤主表数据（检查字段是否存在、处理空值、处理数组等）
            $mainData = $this->inputFilter($mainData);
            
            // 处理主表数据
            $mainData['channel'] = $channelId;
            $mainData['typeid'] = $typeid;
            $mainData['add_time'] = $mainData['add_time'] ?? time();
            $mainData['update_time'] = $mainData['update_time'] ?? time();
            $mainData['admin_id'] = $mainData['admin_id'] ?? admin_id();
            $mainData['lang'] = $mainData['lang'] ?? 'cn';
            $mainData['status'] = $mainData['status'] ?? 1;
            $mainData['arcrank'] = $mainData['arcrank'] ?? 0;
            $mainData['sort_order'] = $mainData['sort_order'] ?? 100;
            
            // 插入主表
            $aid = Archive::insertGetId($mainData);
            if (!$aid) {
                return $this->json(1, '插入失败');
            }
            
            // 处理动态字段值
            if (!empty($dynamicData)) {
                $dynamicData = $fieldLogic->handleAddonField($channelId, $dynamicData);
            }
            
            // 获取模型对应的附加表
            $channeltype = Channeltype::find($channelId);
            if ($channeltype) {
                $prefix = config('plugin.admin.database.connections.mysql.prefix', 'wa_');
                $tableName = $prefix . $channeltype->table . '_content';
                
                // 插入附加表（动态字段存储在附加表中）
                if (!empty($dynamicData)) {
                    $db = Util::db();
                    
                    // 检查附加表是否存在
                    $tableExists = $db->select("SHOW TABLES LIKE '{$tableName}'");
                    if (!empty($tableExists)) {
                        // 使用统一的附加表数据过滤方法（类似 inputFilter，但基于附加表结构）
                        $filteredDynamicData = $this->filterAddonTableData($tableName, $dynamicData);
                        $filteredDynamicData['aid'] = $aid;
                        
                        $keys = array_keys($filteredDynamicData);
                        $placeholders = implode(',', array_fill(0, count($keys), '?'));
                        $sql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $keys) . "`) VALUES ({$placeholders})";
                        $db->statement($sql, array_values($filteredDynamicData));
                    } else {
                        // 如果附加表不存在，将动态字段存储到 archives 表（作为备用方案）
                        if (!empty($dynamicData)) {
                            $dynamicData['update_time'] = time();
                            Archive::where('aid', $aid)->update($dynamicData);
                        }
                    }
                }
            }
            
            return $this->json(0, '操作成功', ['aid' => $aid]);
        }
        return view('archive/insert');
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
            $post = $request->post();
            
            $aid = (int)($post['aid'] ?? 0);
            if (!$aid) {
                return $this->json(1, 'ID不能为空');
            }
            
            // 获取文档信息
            $archive = Archive::find($aid);
            if (!$archive) {
                return $this->json(1, '文档不存在');
            }
            
            $typeid = (int)($post['typeid'] ?? $archive->typeid ?? 0);
            if (!$typeid) {
                return $this->json(1, '栏目ID不能为空');
            }
            
            $arctype = Arctype::find($typeid);
            if (!$arctype) {
                return $this->json(1, '栏目不存在');
            }
            
            $channelId = $arctype->current_channel ?? $archive->channel ?? 0;
            if (!$channelId) {
                return $this->json(1, '模型ID不能为空');
            }
            
            // 分离主表字段和动态字段
            $mainFields = ['aid', 'typeid', 'stypeid', 'channel', 'title', 'subtitle', 'introduction', 'litpic', 
                          'is_b', 'is_head', 'is_special', 'is_top', 'is_recom', 'is_jump', 'is_litpic', 
                          'is_roll', 'is_slide', 'is_diyattr', 'origin', 'author', 'click', 'arcrank', 
                          'jumplinks', 'ismake', 'seo_title', 'seo_keywords', 'seo_description', 
                          'attrlist_id', 'merchant_id', 'free_shipping', 'users_price', 'crossed_price', 
                          'users_discount_type', 'users_free', 'old_price', 'sales_num', 'virtual_sales', 
                          'sales_all', 'stock_count', 'stock_show', 'prom_type', 'logistics_type', 
                          'tempview', 'status', 'sort_order', 'lang', 'admin_id', 'users_id', 
                          'arc_level_id', 'restric_type', 'users_score', 'is_del', 'del_method', 
                          'joinaid', 'downcount', 'appraise', 'collection', 'htmlfilename', 
                          'province_id', 'city_id', 'area_id', 'add_time', 'update_time', 
                          'removal_time', 'no_vip_pay', 'editor_remote_img_local', 'editor_img_clear_link', 
                          'editor_ai_create', 'reason', 'stock_code'];
            
            $mainData = [];
            $dynamicData = [];
            
            // 获取该模型的所有字段定义
            $fieldLogic = new FieldLogic();
            $channelFields = Channelfield::where('channel_id', $channelId)
                ->where('ifeditable', 1)
                ->pluck('name')
                ->toArray();
            
            // 分离数据
            foreach ($post as $key => $value) {
                if (in_array($key, $mainFields)) {
                    $mainData[$key] = $value;
                } elseif (in_array($key, $channelFields) || in_array($key, ['content', 'content_ey_m'])) {
                    $dynamicData[$key] = $value;
                }
            }
            
            // 使用父类的 inputFilter 过滤主表数据（检查字段是否存在、处理空值、处理数组等）
            $mainData = $this->inputFilter($mainData);
            
            // 处理主表数据
            unset($mainData['aid']); // 移除 aid，因为它是 where 条件
            $mainData['channel'] = $channelId;
            $mainData['typeid'] = $typeid;
            $mainData['update_time'] = time();
            
            // 更新主表
            if (!empty($mainData)) {
                Archive::where('aid', $aid)->update($mainData);
            }
            
            // 处理动态字段值
            if (!empty($dynamicData)) {
                $dynamicData = $fieldLogic->handleAddonField($channelId, $dynamicData);
            }
            
            // 获取模型对应的附加表
            $channeltype = Channeltype::find($channelId);
            if ($channeltype) {
                $prefix = config('plugin.admin.database.connections.mysql.prefix', 'wa_');
                $tableName = $prefix . $channeltype->table . '_content';
                
                // 更新或插入附加表（动态字段存储在附加表中）
                if (!empty($dynamicData)) {
                    $db = Util::db();
                    
                    // 检查附加表是否存在
                    $tableExists = $db->select("SHOW TABLES LIKE '{$tableName}'");
                    if (!empty($tableExists)) {
                        // 查找附加表记录
                        $contentRecord = $db->select("SELECT * FROM `{$tableName}` WHERE `aid` = ?", [$aid]);
                        
                        // 使用统一的附加表数据过滤方法（类似 inputFilter，但基于附加表结构）
                        $filteredDynamicData = $this->filterAddonTableData($tableName, $dynamicData);
                        $filteredDynamicData['aid'] = $aid;
                        
                        if (!empty($contentRecord)) {
                            // 更新
                            $setParts = [];
                            $values = [];
                            foreach ($filteredDynamicData as $key => $value) {
                                if ($key !== 'aid') {
                                    $setParts[] = "`{$key}` = ?";
                                    $values[] = $value;
                                }
                            }
                            $values[] = $aid;
                            $sql = "UPDATE `{$tableName}` SET " . implode(', ', $setParts) . " WHERE `aid` = ?";
                            $db->statement($sql, $values);
                        } else {
                            // 插入
                            $keys = array_keys($filteredDynamicData);
                            $placeholders = implode(',', array_fill(0, count($keys), '?'));
                            $sql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $keys) . "`) VALUES ({$placeholders})";
                            $db->statement($sql, array_values($filteredDynamicData));
                        }
                    } else {
                        // 如果附加表不存在，将动态字段存储到 archives 表（作为备用方案）
                        if (!empty($dynamicData)) {
                            $dynamicData['update_time'] = time();
                            Archive::where('aid', $aid)->update($dynamicData);
                        }
                    }
                }
            }
            
            return $this->json(0, '操作成功', ['aid' => $aid]);
        }
        return view('archive/update');
    }

    /**
     * 单页模型编辑页面
     * @param Request $request
     * @return Response
     */
    public function singlePage(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $id = (int)($request->post('id', 0) ?: $request->get('id', 0));
            if (!$id) {
                return $this->json(1, 'ID不能为空');
            }
            
            // 获取栏目信息
            $arctype = Arctype::find($id);
            if (!$arctype) {
                return $this->json(1, '栏目不存在');
            }
            
            $channelId = $arctype->current_channel ?? 0;
            if (!$channelId) {
                return $this->json(1, '模型ID不能为空');
            }
            
            $post = $request->post();
            
            // 分离主表字段和动态字段
            $mainFields = ['id', 'typename', 'litpic', 'seo_keywords', 'seo_description', 'typelink'];
            $mainData = [];
            $dynamicData = [];
            
            // 获取该模型的所有字段定义
            $fieldLogic = new FieldLogic();
            $channelFields = Channelfield::where('channel_id', $channelId)
                ->where('ifeditable', 1)
                ->pluck('name')
                ->toArray();
            
            // 分离数据
            foreach ($post as $key => $value) {
                if (in_array($key, $mainFields)) {
                    $mainData[$key] = $value;
                } elseif (in_array($key, $channelFields) || in_array($key, ['content', 'content_ey_m'])) {
                    $dynamicData[$key] = $value;
                }
            }
            
            // 处理动态字段值
            if (!empty($dynamicData)) {
                $dynamicData = $fieldLogic->handleAddonField($channelId, $dynamicData);
            }
            
            // 更新主表（arctype 表）
            if (!empty($mainData)) {
                unset($mainData['id']); // 移除 id，因为它是 where 条件
                if (!empty($mainData)) {
                    $mainData['update_time'] = time();
                    Arctype::where('id', $id)->update($mainData);
                }
            }
            
            // 获取模型对应的附加表
            $channeltype = Channeltype::find($channelId);
            if (!$channeltype) {
                return $this->json(1, '模型不存在');
            }
            
            $prefix = config('plugin.admin.database.connections.mysql.prefix', 'wa_');
            $tableName = $prefix . $channeltype->table . '_content';
            
            // 查找或创建 archives 记录（主表）
            $archive = Archive::where('typeid', $id)->first();
            $aid = 0;
            if ($archive) {
                $aid = $archive->aid;
            } else {
                // 创建 archives 记录
                $archiveData = [
                    'typeid' => $id,
                    'channel' => $channelId,
                    'title' => $arctype->typename ?? '',
                    'status' => 1,
                    'arcrank' => 0,
                    'sort_order' => 100,
                    'add_time' => time(),
                    'update_time' => time(),
                ];
                $aid = Archive::insertGetId($archiveData);
            }
            
            // 更新或插入附加表（动态字段存储在附加表中）
            if (!empty($dynamicData) && $aid > 0) {
                $db = Util::db();
                
                // 检查附加表是否存在
                $tableExists = $db->select("SHOW TABLES LIKE '{$tableName}'");
                if (!empty($tableExists)) {
                    // 查找附加表记录
                    $contentRecord = $db->select("SELECT * FROM `{$tableName}` WHERE `aid` = ?", [$aid]);
                    
                    // 使用统一的附加表数据过滤方法（类似 inputFilter，但基于附加表结构）
                    $filteredDynamicData = $this->filterAddonTableData($tableName, $dynamicData);
                    $filteredDynamicData['aid'] = $aid;
                    
                    if (!empty($contentRecord)) {
                        // 更新
                        $setParts = [];
                        $values = [];
                        foreach ($filteredDynamicData as $key => $value) {
                            if ($key !== 'aid') {
                                $setParts[] = "`{$key}` = ?";
                                $values[] = $value;
                            }
                        }
                        $values[] = $aid;
                        $sql = "UPDATE `{$tableName}` SET " . implode(', ', $setParts) . " WHERE `aid` = ?";
                        $db->statement($sql, $values);
                    } else {
                        // 插入
                        $keys = array_keys($filteredDynamicData);
                        $placeholders = implode(',', array_fill(0, count($keys), '?'));
                        $sql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $keys) . "`) VALUES ({$placeholders})";
                        $db->statement($sql, array_values($filteredDynamicData));
                    }
                } else {
                    // 如果附加表不存在，将动态字段存储到 archives 表（作为备用方案）
                    if ($archive) {
                        $dynamicData['update_time'] = time();
                        Archive::where('aid', $aid)->update($dynamicData);
                    }
                }
            }
            
            return $this->json(0, '操作成功');
        }
        return view('archive/singlePage');
    }

    /**
     * 获取附加表内容（用于单页模型）
     * @param Request $request
     * @return Response
     */
    public function getContent(Request $request): Response
    {
        $aid = (int)$request->get('aid', 0);
        $channelId = (int)$request->get('channel_id', 0);
        
        if (!$aid || !$channelId) {
            return $this->json(1, '参数错误');
        }
        
        // 获取模型对应的附加表
        $channeltype = Channeltype::find($channelId);
        if (!$channeltype) {
            return $this->json(1, '模型不存在');
        }
        
        $prefix = config('plugin.admin.database.connections.mysql.prefix', 'wa_');
        $tableName = $prefix . $channeltype->table . '_content';
        
        $db = Util::db();
        
        // 检查附加表是否存在
        $tableExists = $db->select("SHOW TABLES LIKE '{$tableName}'");
        if (empty($tableExists)) {
            return $this->json(0, 'ok', []);
        }
        
        // 查询附加表数据
        $content = $db->select("SELECT * FROM `{$tableName}` WHERE `aid` = ?", [$aid]);
        
        if (!empty($content)) {
            $data = (array)$content[0];
            
            // 处理序列化的字段（如 imgs 类型）
            foreach ($data as $key => $value) {
                if (is_string($value) && !empty($value)) {
                    // 尝试反序列化
                    $unserialized = @unserialize($value);
                    if ($unserialized !== false) {
                        $data[$key] = $unserialized;
                    }
                }
            }
            
            return $this->json(0, 'ok', $data);
        }
        
        return $this->json(0, 'ok', []);
    }

    /**
     * 指定查询where条件,并没有真正的查询数据库操作
     * 重写父类方法，添加联查栏目表
     * @param array $where
     * @param string|null $field
     * @param string $order
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\support\Model
     */
    /**
     * 查询前置（重写父类方法，保留 exclude_channel 参数）
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function selectInput(Request $request): array
    {
        $field = $request->get('field');
        $order = $request->get('order', 'asc');
        $format = $request->get('format', 'normal');
        $limit = (int)$request->get('limit', $format === 'tree' ? 1000 : 10);
        $limit = $limit <= 0 ? 10 : $limit;
        $order = $order === 'asc' ? 'asc' : 'desc';
        $where = $request->get();
        $page = (int)$request->get('page');
        $page = $page > 0 ? $page : 1;
        $table = config('plugin.admin.database.connections.mysql.prefix') . $this->model->getTable();

        $allow_column = Util::db()->select("desc `$table`");
        if (!$allow_column) {
            throw new BusinessException('表不存在');
        }
        $allow_column = array_column($allow_column, 'Field', 'Field');
        if (!in_array($field, $allow_column)) {
            $field = null;
        }
        
        // 保留 exclude_channel 参数（不是表字段，但需要用于过滤）
        $excludeChannel = $where['exclude_channel'] ?? null;
        
        foreach ($where as $column => $value) {
            if (
                $value === '' || !isset($allow_column[$column]) ||
                is_array($value) && (empty($value) || !in_array($value[0], ['null', 'not null']) && !isset($value[1]))
            ) {
                unset($where[$column]);
            }
        }
        
        // 如果 exclude_channel 被过滤掉了，重新添加
        if ($excludeChannel !== null && !isset($where['exclude_channel'])) {
            $where['exclude_channel'] = $excludeChannel;
        }
        
        // 按照数据限制字段返回数据
        if ($this->dataLimit && !Auth::isSuperAdmin()) {
            if ($this->dataLimit === 'personal') {
                $where[$this->dataLimitField] = admin_id();
            } elseif ($this->dataLimit === 'auth') {
                $where[$this->dataLimitField] = ['in', implode(',', Auth::getScopeAdminIds(true))];
            }
        }
        return [$where, $format, $limit, $field, $order];
    }

    protected function doSelect(array $where, ?string $field = null, string $order = 'desc')
    {
        $model = $this->model;
        
        // 处理 exclude_channel 参数（排除指定的模型）
        $excludeChannel = null;
        if (isset($where['exclude_channel'])) {
            $excludeChannel = $where['exclude_channel'];
            unset($where['exclude_channel']); // 从 where 中移除，避免被当作普通条件处理
        }
        
        // 处理 where 条件
        foreach ($where as $column => $value) {
            // 如果字段名不包含表名，添加表名前缀
            if (strpos($column, '.') === false) {
                $column = 'archives.' . $column;
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
                $field = 'archives.' . $field;
            }
            $model = $model->orderBy($field, $order);
        } else {
            // 默认按排序号和时间排序
            $model = $model->orderBy('archives.sort_order', 'desc')
                          ->orderBy('archives.add_time', 'desc');
        }
        
        // 联查栏目表，获取栏目名称
        $model = $model->leftJoin('arctype', 'archives.typeid', '=', 'arctype.id')
                      ->select('archives.*', 'arctype.typename');
        
        // 处理 exclude_channel：排除指定的模型（单页模型和留言模型）
        if ($excludeChannel !== null) {
            $excludeChannels = [];
            if (is_string($excludeChannel)) {
                $excludeChannels = array_map('trim', explode(',', $excludeChannel));
            } elseif (is_array($excludeChannel)) {
                $excludeChannels = $excludeChannel;
            } else {
                $excludeChannels = [(string)$excludeChannel];
            }
            if (!empty($excludeChannels)) {
                $model = $model->whereNotIn('arctype.current_channel', $excludeChannels);
            }
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
            // 如果没有关联到栏目，显示空字符串
            if (empty($item->typename)) {
                $item->typename = '';
            }
        }
        return $items;
    }

    /**
     * 过滤附加表数据（类似 inputFilter，但基于附加表结构）
     * 
     * 为什么需要这个过滤：
     * 1. inputFilter 是基于主表（archives）结构，不能用于附加表
     * 2. 附加表（如 wa_article_content）是动态表，字段结构可能不同
     * 3. handleAddonField 处理后的数据可能包含附加表中不存在的字段
     * 4. 附加表可能没有某些字段（如 typeid、add_time、update_time 等）
     * 
     * @param string $tableName 附加表名
     * @param array $data 要过滤的数据
     * @return array 过滤后的数据
     * @throws BusinessException
     */
    protected function filterAddonTableData(string $tableName, array $data): array
    {
        $db = Util::db();
        $tableColumns = $db->select("SHOW COLUMNS FROM `{$tableName}`");
        if (!$tableColumns) {
            throw new BusinessException("附加表 {$tableName} 不存在", 2);
        }
        
        $columns = array_column($tableColumns, 'Type', 'Field');
        $filteredData = [];
        
        foreach ($data as $col => $item) {
            // 只保留表中存在的字段
            if (!isset($columns[$col])) {
                continue;
            }
            
            // 非字符串类型传空则为null（类似 inputFilter 的逻辑）
            if ($item === '' && strpos(strtolower($columns[$col]), 'varchar') === false && strpos(strtolower($columns[$col]), 'text') === false) {
                $filteredData[$col] = null;
            } else {
                $filteredData[$col] = $item;
            }
            
            // 处理数组（转换为逗号分隔字符串）
            if (is_array($filteredData[$col])) {
                $filteredData[$col] = implode(',', $filteredData[$col]);
            }
        }
        
        // 处理时间字段（如果存在）
        if (isset($columns['add_time']) && !isset($filteredData['add_time'])) {
            $filteredData['add_time'] = time();
        }
        if (isset($columns['update_time']) && !isset($filteredData['update_time'])) {
            $filteredData['update_time'] = time();
        }
        
        return $filteredData;
    }

}
