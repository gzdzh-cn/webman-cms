<?php

namespace plugin\admin\app\logic;

use plugin\admin\app\model\Channeltype;
use plugin\admin\app\model\Channelfield;
use plugin\admin\app\model\FieldType;
use plugin\admin\app\common\Util;

/**
 * 字段逻辑类
 */
class FieldLogic
{
    /**
     * 同步文档主表的字段记录到指定模型
     * @param int $channel_id 模型ID
     * @return void
     */
    public function synArchivesTableColumns($channel_id)
    {
        $db = Util::db();
        $prefix = config('plugin.admin.database.connections.mysql.prefix');
        $table = $prefix . 'archives';
        
        // 获取已存在的字段记录
        $channelfieldArr = Channelfield::where('channel_id', $channel_id)
            ->where('ifmain', 1)
            ->pluck('dtype', 'name')
            ->toArray();
        
        // 获取系统模型ID列表
        $channeltype_system_ids = Channeltype::where('ifsystem', 1)->pluck('id')->toArray();
        
        // 控制字段列表
        $controlFields = ['litpic', 'author'];
        
        // 获取 archives 表的所有字段
        $columns = $db->select("SHOW FULL COLUMNS FROM `{$table}`");
        $columns = array_reverse($columns);
        
        $addData = [];
        $new_arr = [];
        
        foreach ($columns as $column) {
            $fieldname = $column->Field;
            $new_arr[] = $fieldname;
            
            // 如果字段记录不存在，则添加
            if (empty($channelfieldArr[$fieldname])) {
                $dtype = $this->toDtype($column->Type, $column->Comment ?? '');
                $dfvalue = $this->toDefault($column->Type, $column->Default ?? '');
                
                // 判断是否为控制字段
                if (in_array($fieldname, $controlFields) && !in_array($channel_id, $channeltype_system_ids)) {
                    $ifcontrol = 0;
                } else {
                    $ifcontrol = 1;
                }
                
                $maxlength = preg_replace('/^([^\(]+)\(([^\)]+)\)(.*)/i', '$2', $column->Type);
                $maxlength = intval($maxlength);
                
                $addData[] = [
                    'name' => $fieldname,
                    'channel_id' => $channel_id,
                    'title' => !empty($column->Comment) ? $column->Comment : $fieldname,
                    'dtype' => $dtype,
                    'define' => $column->Type,
                    'maxlength' => $maxlength,
                    'dfvalue' => $dfvalue,
                    'ifeditable' => 1,
                    'ifsystem' => 1,
                    'ifmain' => 1,
                    'ifcontrol' => $ifcontrol,
                    'add_time' => time(),
                    'update_time' => time(),
                ];
            }
        }
        
        // 批量插入新字段
        if (!empty($addData)) {
            Channelfield::insert($addData);
        }
        
        // 删除不存在的字段记录
        foreach ($channelfieldArr as $fieldname => $dtype) {
            if (!in_array($fieldname, $new_arr)) {
                Channelfield::where('channel_id', $channel_id)
                    ->where('ifmain', 1)
                    ->where('name', $fieldname)
                    ->delete();
            }
        }
    }

    /**
     * 表字段类型转为自定义字段类型
     * @param string $fieldtype 字段类型
     * @param string $comment 注释
     * @return string
     */
    public function toDtype($fieldtype = '', $comment = '')
    {
        $commentArr = explode('|', $comment);
        if (preg_match('/^int/i', $fieldtype)) {
            $maxlen = preg_replace('/^int\((.*)\)/i', '$1', $fieldtype);
            if (11 == $maxlen) {
                $dtype = 'datetime';
            } else {
                $dtype = 'int';
            }
        } else if (preg_match('/^longtext/i', $fieldtype)) {
            $dtype = 'htmltext';
        } else if (preg_match('/^text/i', $fieldtype)) {
            $maxlen = end($commentArr);
            $maxlen = intval($maxlen);
            if (1001 == $maxlen || 10001 == $maxlen) {
                $dtype = 'imgs';
            } else if (1002 == $maxlen || 10002 == $maxlen) {
                $dtype = 'files';
            } else if (1003 == $maxlen || 10003 == $maxlen) {
                $dtype = 'checkboxs';
            } else {
                $dtype = 'multitext';
            }
        } else if (preg_match('/^enum/i', $fieldtype)) {
            $dtype = 'select';
        } else if (preg_match('/^set/i', $fieldtype)) {
            $dtype = 'checkbox';
        } else if (preg_match('/^float/i', $fieldtype)) {
            $dtype = 'float';
        } else if (preg_match('/^decimal/i', $fieldtype)) {
            $dtype = 'decimal';
        } else if (preg_match('/^tinyint/i', $fieldtype)) {
            $dtype = 'switch';
        } else if (preg_match('/^varchar/i', $fieldtype)) {
            $maxlen = preg_replace('/^varchar\((.*)\)/i', '$1', $fieldtype);
            if (250 == $maxlen) {
                $dtype = 'img';
            } else if (11 == $maxlen) {
                $dtype = 'radios';
            } else if (12 == $maxlen) {
                $dtype = 'selects';
            } else if (1001 == $maxlen || 10001 == $maxlen) {
                $dtype = 'imgs';
            } else if (1002 == $maxlen || 10002 == $maxlen) {
                $dtype = 'files';
            } else {
                $dtype = 'text';
            }
        } else {
            $dtype = 'text';
        }
        
        return $dtype;
    }

    /**
     * 表字段的默认值
     * @param string $fieldtype 字段类型
     * @param string $dfvalue 默认值
     * @return string
     */
    public function toDefault($fieldtype, $dfvalue = '')
    {
        if (preg_match('/^(enum|set)/i', $fieldtype)) {
            $str = preg_replace('/^(enum|set)\((.*)\)/i', '$2', $fieldtype);
            $str = str_replace("'", "", $str);
        } else {
            $str = $dfvalue;
        }
        $str = ("" != $str) ? $str : '';
        
        return $str;
    }

    /**
     * 获取字段类型列表
     * @param int $channel_id 模型ID，用于过滤字段类型
     * @param string $field 要查询的字段，如 'dtype,title'，默认为所有字段
     * @return array
     */
    public function getFieldTypeList($channel_id = 0, $field = '*')
    {
        $db = Util::db();
        $prefix = config('plugin.admin.database.connections.mysql.prefix');
        $table = $prefix . 'field_type';
        
        // 构建 WHERE 条件
        $whereConditions = [];
        $bindings = [];
        
        if (!empty($channel_id)) {
            $dtypes = ['checkbox', 'radio'];
            $checkField = Channelfield::where('channel_id', $channel_id)
                ->where('ifsystem', 0)
                ->whereIn('dtype', $dtypes)
                ->groupBy('dtype')
                ->pluck('dtype')
                ->toArray();
            
            $diff_arr = array_diff($dtypes, $checkField);
            if (!empty($diff_arr)) {
                $placeholders = implode(',', array_fill(0, count($diff_arr), '?'));
                $whereConditions[] = "name NOT IN ({$placeholders})";
                $bindings = array_merge($bindings, $diff_arr);
            }
        }
        
        // 获取表结构，确定存在的字段
        $existingColumns = array_column($db->select("SHOW COLUMNS FROM `{$table}`"), 'Field');
        
        // 确定要查询的字段
        if ($field !== '*') {
            $fields = array_map('trim', explode(',', $field));
        } else {
            $fields = ['name', 'title', 'sort_order', 'ifoption', 'add_time', 'update_time'];
        }
        
        // 确保 name 字段被包含，并过滤不存在的字段
        if (!in_array('name', $fields)) {
            array_unshift($fields, 'name');
        }
        $fields = array_filter($fields, function($f) use ($existingColumns) {
            return in_array($f, $existingColumns);
        });
        
        if (empty($fields)) {
            $fields = $existingColumns;
        }
        
        // 构建并执行 SQL
        $selectFields = implode(', ', array_map(function($f) { return "`{$f}`"; }, $fields));
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $sql = "SELECT {$selectFields} FROM `{$table}` {$whereClause} ORDER BY sort_order ASC";
        
        $rawResults = $db->select($sql, $bindings);
        
        // 转换为数组
        $data = array_map(function($row) {
            return (array)$row;
        }, $rawResults);

        return $data;
    }

    /**
     * 解析模型对应的数据表（优先附加表，若不存在则回落主表）
     * @param Channeltype $channeltype
     * @return string
     * @throws \Exception
     */
    public function resolveChannelTable(Channeltype $channeltype)
    {
        $prefix = config('plugin.admin.database.connections.mysql.prefix');
        $baseTable = $prefix . $channeltype->table;
        $contentTable = $baseTable . '_content';
        $db = Util::db();

        // SHOW TABLES LIKE 不支持参数绑定，这里表名来源于受控的配置与数据库字段
        $hasContent = $db->select("SHOW TABLES LIKE '{$contentTable}'");
        if (!empty($hasContent)) {
            return $contentTable;
        }

        $hasBase = $db->select("SHOW TABLES LIKE '{$baseTable}'");
        if (!empty($hasBase)) {
            return $baseTable;
        }

        throw new \Exception("模型数据表不存在：{$contentTable} 或 {$baseTable}");
    }

    /**
     * 向指定数据表添加字段
     * @param string $table
     * @param string $ntabsql ALTER TABLE 中的 ADD 片段
     * @throws \Exception
     */
    public function addFieldToTable(string $table, string $ntabsql)
    {
        $db = Util::db();
        $sql = "ALTER TABLE `{$table}` ADD {$ntabsql}";
        $db->statement($sql);
    }

    /**
     * 修改指定数据表的字段（使用 CHANGE COLUMN）
     * @param string $table
     * @param string $oldFieldName 旧字段名
     * @param string $ntabsql ALTER TABLE 中的字段定义片段（包含字段名和类型）
     * @throws \Exception
     */
    public function changeFieldInTable(string $table, string $oldFieldName, string $ntabsql)
    {
        $db = Util::db();
        $sql = "ALTER TABLE `{$table}` CHANGE COLUMN `{$oldFieldName}` {$ntabsql}";
        $db->statement($sql);
    }

    /**
     * 生成字段SQL语句
     * @param string $dtype 字段类型
     * @param string $name 字段名称
     * @param string $dfvalue 默认值
     * @param string $title 字段标题
     * @return array [SQL语句, 数据类型, 最大长度]
     */
    public function GetFieldMake($dtype, $name, $dfvalue, $title)
    {
        $db = Util::db();
        $name = addslashes($name);
        $title = addslashes($title);
        $comment = $title;
        
        $ntabsql = '';
        $buideType = '';
        $maxlength = 0;
        
        switch ($dtype) {
            case 'text':
                $maxlength = 255;
                $ntabsql = "`{$name}` varchar(255) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = 'varchar(255)';
                break;
            case 'int':
                $ntabsql = "`{$name}` int(11) NOT NULL DEFAULT '0' COMMENT '{$comment}'";
                $buideType = 'int(11)';
                $maxlength = 11;
                break;
            case 'float':
                $ntabsql = "`{$name}` float NOT NULL DEFAULT '0' COMMENT '{$comment}'";
                $buideType = 'float';
                break;
            case 'decimal':
                $ntabsql = "`{$name}` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '{$comment}'";
                $buideType = 'decimal(10,2)';
                break;
            case 'datetime':
                $ntabsql = "`{$name}` int(11) NOT NULL DEFAULT '0' COMMENT '{$comment}'";
                $buideType = 'int(11)';
                $maxlength = 11;
                break;
            case 'multitext':
                $ntabsql = "`{$name}` text COMMENT '{$comment}'";
                $buideType = 'text';
                break;
            case 'htmltext':
                $ntabsql = "`{$name}` longtext COMMENT '{$comment}'";
                $buideType = 'longtext';
                break;
            case 'img':
                $ntabsql = "`{$name}` varchar(250) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = 'varchar(250)';
                $maxlength = 250;
                break;
            case 'imgs':
                $ntabsql = "`{$name}` text COMMENT '{$comment}|1001'";
                $buideType = 'text';
                break;
            case 'files':
                $ntabsql = "`{$name}` text COMMENT '{$comment}|1002'";
                $buideType = 'text';
                break;
            case 'radio':
                $dfvalue = addslashes($dfvalue);
                $dfvalueArr = explode(',', $dfvalue);
                $enumValues = array_map(function($v) {
                    return "'" . addslashes(trim($v)) . "'";
                }, $dfvalueArr);
                $enumStr = implode(',', $enumValues);
                $ntabsql = "`{$name}` enum({$enumStr}) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = "enum({$enumStr})";
                $maxlength = count($dfvalueArr);
                break;
            case 'checkbox':
                $dfvalue = addslashes($dfvalue);
                $dfvalueArr = explode(',', $dfvalue);
                if (count($dfvalueArr) > 64) {
                    $dfvalueArr = array_slice($dfvalueArr, 0, 64);
                }
                $setValues = array_map(function($v) {
                    return "'" . addslashes(trim($v)) . "'";
                }, $dfvalueArr);
                $setStr = implode(',', $setValues);
                $ntabsql = "`{$name}` set({$setStr}) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = "set({$setStr})";
                $maxlength = count($dfvalueArr);
                break;
            case 'select':
                $dfvalue = addslashes($dfvalue);
                $dfvalueArr = explode(',', $dfvalue);
                $enumValues = array_map(function($v) {
                    return "'" . addslashes(trim($v)) . "'";
                }, $dfvalueArr);
                $enumStr = implode(',', $enumValues);
                $ntabsql = "`{$name}` enum({$enumStr}) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = "enum({$enumStr})";
                $maxlength = count($dfvalueArr);
                break;
            case 'radios':
                $ntabsql = "`{$name}` varchar(11) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = 'varchar(11)';
                $maxlength = 11;
                break;
            case 'selects':
                $ntabsql = "`{$name}` varchar(12) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = 'varchar(12)';
                $maxlength = 12;
                break;
            case 'checkboxs':
                $ntabsql = "`{$name}` text COMMENT '{$comment}|1003'";
                $buideType = 'text';
                break;
            case 'region':
                $ntabsql = "`{$name}` varchar(255) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = 'varchar(255)';
                $maxlength = 255;
                break;
            default:
                $maxlength = 255;
                $ntabsql = "`{$name}` varchar(255) NOT NULL DEFAULT '' COMMENT '{$comment}'";
                $buideType = 'varchar(255)';
        }
        
        return [$ntabsql, $buideType, $maxlength];
    }

    /**
     * 检测字段是否存在于主表与附加表中
     * @param string $table 表名
     * @param string $fieldname 字段名
     * @param int $channel_id 模型ID
     * @param array $excludeFields 排除的字段名数组（更新时传入旧字段名）
     * @return bool true表示冲突, false表示不冲突
     */
    public function checkChannelFieldList($table, $fieldname, $channel_id, $excludeFields = [])
    {
        $db = Util::db();
        
        // 如果字段名在排除列表中（更新时字段名未改变），允许通过
        if (!empty($excludeFields) && in_array($fieldname, $excludeFields)) {
            return false;
        }
        
        // 检查字段是否存在于指定表中
        try {
            $columns = $db->select("SHOW COLUMNS FROM `{$table}` LIKE '{$fieldname}'");
            if (!empty($columns)) {
                // 如果字段存在于表中，但不在排除列表中，说明冲突
                return true;
            }
        } catch (\Exception $e) {
            // 表不存在时返回false
        }
        
        // 检查是否与系统保留字段冲突
        $systemFields = ['id', 'aid', 'typeid', 'channel', 'add_time', 'update_time', 'is_del'];
        if (in_array($fieldname, $systemFields)) {
            return true;
        }
        
        // 检查是否与已存在的字段记录冲突(排除指定字段)
        $query = Channelfield::where('channel_id', $channel_id)
            ->where('name', $fieldname);
        
        if (!empty($excludeFields)) {
            $query = $query->whereNotIn('name', $excludeFields);
        }
        
        $exists = $query->exists();
        if ($exists) {
            return true;
        }
        
        return false;
    }

    /**
     * 处理字段数据（用于 insert 和 update）
     * @param array $post 表单数据
     * @param int $channel_id 模型ID
     * @param string|null $oldFieldName 旧字段名（更新时传入，新增时为null）
     * @return array ['success' => bool, 'data' => array, 'error' => string]
     */
    public function processFieldData($post, $channel_id, $oldFieldName = null)
    {
        // 数据清理和验证
        $post['title'] = htmlspecialchars(trim($post['title'] ?? ''));
        $post['name'] = trim($post['name'] ?? '');
        $post['dtype'] = trim($post['dtype'] ?? '');
        $post['dfvalue'] = trim($post['dfvalue'] ?? '');

        // 验证必填字段
        if (empty($post['dtype']) || empty($post['title']) || empty($post['name'])) {
            return ['success' => false, 'error' => '缺少必填信息！'];
        }

        // 判断是否存在|杠
        if (strpos($post['dfvalue'], '|') !== false) {
            return ['success' => false, 'error' => '不可输入 | 杠'];
        }

        // 验证字段名称格式
        if (!preg_match('/^(\w)+$/', $post['name']) || preg_match('/^([_]+|[0-9]+)$/', $post['name'])) {
            return ['success' => false, 'error' => '字段名称格式不正确！'];
        } else if (preg_match('/^type/', $post['name'])) {
            return ['success' => false, 'error' => '字段名称不允许以type开头！'];
        } else if (preg_match('/^ey_/', $post['name'])) {
            return ['success' => false, 'error' => '字段名称不允许以 ey_ 开头！'];
        }

        if (!is_string($post['dtype'])) {
            return ['success' => false, 'error' => '数据类型不正确！'];
        }

        // 处理 checkboxs/radios/selects 类型
        if (in_array($post['dtype'], ['checkboxs','radios','selects'])) {
            if (empty($post['mcheck']) || !is_array($post['mcheck']) || count($post['mcheck']) == 0) {
                return ['success' => false, 'error' => '选项参数至少填一项'];
            }
            $post['dfvalue'] = '';
        }

        // 字段类型是否具备筛选功能
        if (empty($post['IsScreening_status'])) {
            $post['is_screening'] = 0;
        } else {
            $post['is_screening'] = 1;
        }

        $dfvalue = $post['dfvalue'];

        // 去除中文逗号，过滤左右空格与空值
        if (in_array($post['dtype'], ['radio','checkbox','select','region'])) {
            $dfvalue = str_replace('，', ',', $dfvalue);
            $pattern = ['"', '\'', ';', '&', '?', '='];
            $dfvalue = preg_replace('/[' . preg_quote(implode('', $pattern), '/') . ']/', '', $dfvalue);
            $dfvalueArr = explode(',', $dfvalue);
            foreach ($dfvalueArr as $key => $val) {
                $tmp_val = trim($val);
                if (empty($tmp_val)) {
                    unset($dfvalueArr[$key]);
                    continue;
                }
                $dfvalueArr[$key] = $tmp_val;
            }
            $dfvalueArr = array_unique($dfvalueArr);
            $dfvalue = implode(',', $dfvalueArr);
        }

        // 处理 region 类型
        if ('region' == $post['dtype']) {
            if (!empty($post['region_data'])) {
                $post['region_data'] = [
                    'region_id' => preg_replace('/([^\d\,]+)/i', '', $post['region_data']['region_id'] ?? ''),
                    'region_ids' => preg_replace('/([^\d\,]+)/i', '', $post['region_data']['region_ids'] ?? ''),
                    'region_names' => preg_replace("/([^\x{4e00}-\x{9fa5}\,\，]+)/u", '', $post['region_data']['region_names'] ?? ''),
                    'province_name' => preg_replace("/([^\x{4e00}-\x{9fa5}]+)/u", '', $post['region_data']['province_name'] ?? ''),
                    'province_code' => preg_replace('/([^\d]+)/i', '', $post['region_data']['province_code'] ?? ''),
                    'city_name' => preg_replace("/([^\x{4e00}-\x{9fa5}]+)/u", '', $post['region_data']['city_name'] ?? ''),
                    'city_code' => preg_replace('/([^\d]+)/i', '', $post['region_data']['city_code'] ?? ''),
                ];
                $post['dfvalue'] = $post['region_data']['region_id'];
                $post['region_data'] = serialize($post['region_data']);
            } else {
                return ['success' => false, 'error' => '请选择区域范围！'];
            }
        } else {
            // 默认值必填字段验证
            $fieldTypes = $this->getFieldTypeList($channel_id, 'name,title');
            $fieldTypeMap = [];
            foreach ($fieldTypes as $ft) {
                $fieldTypeMap[$ft['name']] = $ft;
            }

            // 检查字段类型是否需要选项
            $requireOptionTypes = ['radio', 'checkbox', 'select', 'region'];
            if (in_array($post['dtype'], $requireOptionTypes) && empty($dfvalue)) {
                $typeTitle = $fieldTypeMap[$post['dtype']]['title'] ?? $post['dtype'];
                return ['success' => false, 'error' => "你设定了字段为【{$typeTitle}】类型，默认值不能为空！"];
            }
            unset($post['region_data']);
        }

        // 获取当前模型对应的数据表
        $channeltype = Channeltype::find($channel_id);
        if (!$channeltype) {
            return ['success' => false, 'error' => '模型不存在'];
        }

        try {
            // 解析目标数据表（优先附加表，回落主表）
            $table = $this->resolveChannelTable($channeltype);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        // 检测字段是否存在于主表与附加表中（更新时排除旧字段名）
        $excludeFields = $oldFieldName ? [$oldFieldName] : [];
        if ($this->checkChannelFieldList($table, $post['name'], $channel_id, $excludeFields)) {
            return ['success' => false, 'error' => "字段名称 {$post['name']} 与系统字段冲突！"];
        }

        // 验证可见栏目
        if (empty($post['typeids']) || !is_array($post['typeids'])) {
            return ['success' => false, 'error' => '请选择可见栏目！'];
        }
        $typeids = array_filter(array_map('intval', $post['typeids']));
        if (empty($typeids)) {
            return ['success' => false, 'error' => '请选择可见栏目！'];
        }

        // checkbox 类型限制最多64个选项
        if ("checkbox" == $post['dtype']) {
            $dfvalue = explode(',', $dfvalue);
            if (64 < count($dfvalue)) {
                $dfvalue = array_slice($dfvalue, 0, 64);
            }
            $dfvalue = implode(',', $dfvalue);
        }

        // 组装完整的SQL语句
        $fieldinfos = $this->GetFieldMake($post['dtype'], $post['name'], $dfvalue, $post['title']);
        $ntabsql = $fieldinfos[0];
        $buideType = $fieldinfos[1];
        $maxlength = $fieldinfos[2];

        // 处理 region_data
        if (!empty($post['region_data'])) {
            $dfvalue = $post['region_data'];
            unset($post['region_data']);
        }

        return [
            'success' => true,
            'data' => [
                'post' => $post,
                'dfvalue' => $dfvalue,
                'maxlength' => $maxlength,
                'buideType' => $buideType,
                'ntabsql' => $ntabsql,
                'table' => $table,
                'typeids' => $typeids,
                'channel_id' => $channel_id,
            ]
        ];
    }

    /**
     * 处理动态字段的值（用于 insert 和 update）
     * 根据字段类型转换和验证字段值
     * @param array $dataExt 字段数据
     * @param int $channel_id 模型ID
     * @return array 处理后的字段数据
     */
    public function handleAddonField($channel_id, $dataExt)
    {
        $nowDataExt = [];
        if (!empty($dataExt) && !empty($channel_id)) {
            // 获取字段类型列表
            $fieldTypeList = Channelfield::where('channel_id', $channel_id)
                ->pluck('dtype', 'name')
                ->toArray();
            
            foreach ($dataExt as $key => $val) {
                // 处理远程/本地图片字段的后缀
                $key = preg_replace('/^(.*)(_eyou_is_remote|_eyou_remote|_eyou_local)$/', '$1', $key);
                
                $dtype = !empty($fieldTypeList[$key]) ? $fieldTypeList[$key] : '';
                
                switch ($dtype) {
                    case 'checkbox':
                    case 'checkboxs':
                        // 复选框：将数组转换为逗号分隔的字符串
                        if (is_array($val)) {
                            $val = implode(',', array_filter($val));
                        }
                        break;

                    case 'switch':
                    case 'int':
                        // 开关/整数：转换为整数
                        $val = intval($val);
                        break;

                    case 'img':
                        // 单图：处理远程/本地图片
                        $is_remote = !empty($dataExt[$key.'_eyou_is_remote']) ? $dataExt[$key.'_eyou_is_remote'] : 0;
                        if (1 == $is_remote) {
                            $val = $dataExt[$key.'_eyou_remote'] ?? '';
                        } else {
                            $val = $dataExt[$key.'_eyou_local'] ?? '';
                        }
                        break;

                    case 'imgs':
                        // 多图：序列化为数组格式
                        if (is_array($val)) {
                            $imgData = [];
                            $imgsIntroArr = !empty($dataExt[$key.'_eyou_intro']) ? $dataExt[$key.'_eyou_intro'] : [];
                            foreach ($val as $k2 => $v2) {
                                $v2 = trim($v2);
                                if (!empty($v2)) {
                                    $imgData[] = [
                                        'image_url' => $v2,
                                        'intro' => !empty($imgsIntroArr[$k2]) ? $imgsIntroArr[$k2] : '',
                                    ];
                                }
                            }
                            $val = serialize($imgData);
                        }
                        break;

                    case 'files':
                        // 文件：将数组转换为逗号分隔的字符串
                        if (is_array($val)) {
                            foreach ($val as $k2 => $v2) {
                                if (empty($v2)) {
                                    unset($val[$k2]);
                                    continue;
                                }
                                $val[$k2] = trim($v2);
                            }
                            $val = implode(',', $val);
                        }
                        break;

                    case 'datetime':
                        // 日期时间：转换为时间戳
                        $val = !empty($val) ? strtotime($val) : time();
                        break;

                    case 'decimal':
                        // 小数：保留两位小数
                        $moneyArr = explode('.', $val);
                        $money1 = !empty($moneyArr[0]) ? intval($moneyArr[0]) : '0';
                        $money2 = !empty($moneyArr[1]) ? substr($moneyArr[1], 0, 2) : '00';
                        $val = $money1.'.'.$money2;
                        break;
                    
                    default:
                        // 其他类型：去除空格，数组转换为逗号分隔字符串
                        if (is_array($val)) {
                            $new_val = [];
                            foreach ($val as $_k => $_v) {
                                $_v = trim($_v);
                                if (!empty($_v)) {
                                    $new_val[] = $_v;
                                }
                            }
                            $val = $new_val;
                        } else {
                            $val = trim($val);
                        }
                        break;
                }
                
                $nowDataExt[$key] = $val;
            }
        }

        return $nowDataExt;
    }
}

