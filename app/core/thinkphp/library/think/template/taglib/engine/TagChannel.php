<?php

namespace think\template\taglib\engine;

use support\Db;
use support\Log;

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
     * 获取指定级别的栏目列表
     * @param string type son表示下一级栏目,self表示同级栏目,top顶级栏目
     * @param boolean $self 包括自己本身
     * @author wengxianhu by 2018-4-26
     */
    public function getChannel($typeid = '', $type = 'top', $currentclass = '', $notypeid = '', $modelid = '')
    {
        $this->currentclass = $currentclass;
        // 设置默认row值，保持与webman原有逻辑一致
        $row = 10;
        Log::debug('[Eyou::getChannel] called typeid='.$typeid.' type='.$type);
        
        try {
            // 保持与原始EyouCMS逻辑一致
            if (!empty($modelid)) {
                $typeid = '';
            } else {
                // webman版本中没有$this->tid，使用默认值0
                $typeid = !empty($typeid) ? $typeid : 0;
            }

            // 处理多语言（webman版本中简化处理）
            // 原始版本的多语言逻辑暂时注释，因为webman版本中没有相关模型
            /*
            if (!empty($typeid)) {
                $typeid = model('LanguageAttr')->getBindValue($typeid, 'arctype');
                if (empty($typeid)) {
                    echo '标签channel报错：找不到与第一套【'.self::$main_lang.'】语言关联绑定的属性 typeid 值 。';
                    return false;
                }
            }
            */

            if (empty($typeid)) {
                /*应用于没有指定tid的列表，默认获取该控制器下的第一级栏目ID*/
                $table = 'arctype';
                $map = [
                    'is_del' => 0,
                    'is_hidden' => 0,
                    'parent_id' => 0,
                ];
                
                // 获取当前控制器名
                $controller_name = request()->controller;
                
                // 查找对应模型ID
                $channeltype_info = Db::table('channeltype')->where(['ctl_name'=>$controller_name])->first();
                if (!empty($channeltype_info)) {
                    $map['channeltype'] = $channeltype_info['id'];
                }
                
                // 会员分组查询条件（webman版本中简化处理）
                $group_user_where = [];
                
                if (empty($group_user_where)) {
                    $typeid = Db::table($table)->where($map)->orderBy('sort_order', 'asc')->limit(1)->value('id');
                } else {
                    $typeid = Db::table($table)->alias('a')
                        ->leftJoin('weapp_users_group_arctype b', 'a.id = b.type_id')
                        ->where($map)
                        ->where($group_user_where)
                        ->orderBy('sort_order', 'asc')
                        ->limit(1)
                        ->value('id');
                }
            }

            // 语言分割检查（webman版本中简化处理）
            /*
            if (self::$language_split) {
                $this->lang = Db::name('arctype')->where(['id'=>$typeid])->cache(true, EYOUCMS_CACHE_TIME, 'arctype')->value('lang');
                if ($this->lang != self::$home_lang) {
                    $lang_title = Db::name('language_mark')->where(['mark'=>self::$home_lang])->value('cn_title');
                    echo "标签channel报错：【{$lang_title}】语言 typeid 值不存在。";
                    return false;
                }
            }
            */

            // 调用getSwitchType方法获取结果
            $result = $this->getSwitchType($modelid, $typeid, $type, $notypeid, $row);

            return $result;
        } catch (\Throwable $e) {
            Log::error('[Eyou::getChannel] ERROR: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 根据类型获取栏目列表
     * @param string $modelid 模型ID
     * @param string $typeid 栏目ID
     * @param string $type 类型（son/self/top）
     * @param string $notypeid 排除的栏目ID
     * @param int $row 获取数量
     * @return array
     */
    private function getSwitchType($modelid, $typeid, $type, $notypeid, $row = 10)
    {
        $table = 'arctype';
        $query = Db::table($table)->where(['is_del' => 0, 'is_hidden' => 0]);
        
        // 类型筛选
        if (!empty($modelid)) {
            // 根据模型ID筛选
            $query = $query->where('channeltype', (int)$modelid);
        } else {
            if ((int)$typeid > 0) {
                // 根据type参数确定查询条件
                if ('son' == $type) {
                    // 子栏目
                    $query = $query->where('parent_id', (int) $typeid);
                } elseif ('self' == $type) {
                    // 同级栏目
                    $parent_id = Db::table($table)->where('id', (int)$typeid)->value('parent_id');
                    $query = $query->where('parent_id', (int) $parent_id);
                } else {
                    // 默认顶级栏目
                    $query = $query->where('parent_id', 0);
                }
            } else {
                // 顶级栏目
                $query = $query->where('parent_id', 0);
            }
        }
        
        // 排除指定栏目
        if (!empty($notypeid)) {
            if (is_array($notypeid)) {
                $query = $query->whereNotIn('id', $notypeid);
            } else {
                $query = $query->whereNotIn('id', explode(',', $notypeid));
            }
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
