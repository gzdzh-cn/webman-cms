<?php


namespace think\template\taglib\engine;


use support\Db;

/**
 * 文章列表
 */
class TagArclist extends Base
{


    //初始化
    protected function _initialize()
    {
        parent::_initialize();
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
}
