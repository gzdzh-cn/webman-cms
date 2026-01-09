<?php

namespace think\template\taglib\engine;

use support\Db;

/**
 * 栏目列表
 */
class TagChannel extends Base
{

    public $currentclass = '';

    //初始化
    protected function _initialize()
    {
        parent::_initialize();
 
    }

   
    /**
     * 获取导航
     */
    public function getChannel($typeid = 0, $row = 10)
    {
        error_log('[Eyou::getChannel] called typeid='.$typeid.' row='.$row);
        try {
            // 你确认的真实表名
            $table = 'arctype';

     
            $query = Db::table($table);

            $query = $query->where(['is_del' => 0, 'is_hidden' => 0]);
            if ((int)$typeid > 0) {
                // 子栏目条件（字段是否存在以你的表结构为准）
                $query = $query->where('parent_id', (int) $typeid);
            }

            // 排序
            $query = $query->orderBy('sort_order')->orderBy('id');
            $rows = $query->selectRaw('*, id as typeid')->limit((int) $row)->get();

            $data = [];
            foreach ($rows as $item) {
                if (is_array($item)) {
                    $data[] = $item;
                } elseif (is_object($item)) {
                    $data[] = (array)$item;
                } else {
                    $data[] = (array)$item;
                }
            }


            return $data;
        } catch (\Throwable $e) {
            error_log('[Eyou::getChannel] ERROR: ' . $e->getMessage());
            return [];
        }
    }



    public function isCurrent($typeid)
    {
        $current = (int) (request()->get('tid', 0));
        return (int)$current === (int)$typeid;
    }

    public function config($name = '', $default = null)
    {
        return config($name, $default);
    }
}
