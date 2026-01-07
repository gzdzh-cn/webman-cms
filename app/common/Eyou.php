<?php
// app/common/Eyou.php
namespace app\common;

use support\Db;

class Eyou
{
    public function getGlobal($name = '')
    {
        static $globals = null;
        if (null === $globals) {
            $globals = [
                'web_name' => '我的 Webman CMS',
                'web_keywords' => 'webman, cms, eyoucms',
                'web_description' => '这是一个基于 Webman 重构的 CMS 系统。',
            ];
        }

        return $name ? ($globals[$name] ?? '') : $globals;
    }

    /**
     * 获取导航
     */
    public function getChannel($typeid = 0, $row = 10)
    {
        try {
            // 你确认的真实表名
            $table = 'arctype';

            $query = Db::table($table);

            // 原来的条件
            $query = $query->where(['is_del' => 0, 'is_hidden' => 0]);
            if ((int)$typeid > 0) {
                // 原来的子栏目条件（字段是否存在以你的表结构为准）
                $query = $query->where(['parent_id' => (int)$typeid]);
            }

            // 原来的排序
            $query = $query->orderBy('sort_order')->orderBy('id');

            $rows = $query->limit((int)$row)->get();

            // Db::table()->get() 返回的是 Collection<StdClass>
            // 这里强制把内部的 stdClass 转为 array，确保模板里可以用 $field['xxx'] 访问
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

            // error_log("[Eyou::getChannel] table={$table}, count=" . count($data));

            return $data;
        } catch (\Throwable $e) {
            error_log('[Eyou::getChannel] ERROR: ' . $e->getMessage());
            return [];
        }
    }

    public function getArclist($typeid = 0, $limit = 10, $orderby = 'aid desc')
    {
        try {
            $where = ['arcrank' => ['>=', 0], 'is_del' => 0];
            if ($typeid > 0) {
                $where['typeid'] = $typeid;
            }

            return Db::name('archives')
                ->where($where)
                ->order($orderby)
                ->limit($limit)
                ->select()
                ->toArray();
        } catch (\Throwable $e) {
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
