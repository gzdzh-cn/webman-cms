<?php

namespace plugin\admin\app\logic;

use plugin\admin\app\model\Channeltype;
use plugin\admin\app\model\Channelfield;
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
}

