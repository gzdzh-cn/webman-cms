<?php

namespace think\template\taglib\engine;

use support\Db;
use support\Request;



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
     * @param string $typeid 栏目ID
     * @param string $type 类型（son/self/top）
     * @param string $currentclass 选中样式
     * @param string $notypeid 排除的栏目ID
     * @param string $modelid 模型ID
     * @return array
     */
    public function getChannel($typeid = '', $type = 'top', $currentclass = '', $notypeid = '', $modelid = '')
    {
        $this->currentclass = $currentclass;
        // 设置默认row值，保持与webman原有逻辑一致
        $row = 10;
        
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
        }
        */

        // 多语言（webman版本中简化处理）
        /*
        $lang = !empty($this->home_lang) ? $this->home_lang : config('home_lang');
        */

        // 调用getSwitchType方法获取栏目列表
        $result = $this->getSwitchType($modelid, $typeid, $type, $notypeid);

        return $result;
    }

    /**
     * 根据类型获取栏目列表
     * @param string $modelid 模型ID
     * @param string $typeid 栏目ID
     * @param string $type 类型（son/self/top/sonself/first）
     * @param string $notypeid 排除的栏目ID
     * @return array
     */
    public function getSwitchType($modelid = '', $typeid = '', $type = 'top', $notypeid = '')
    {
        $result = [];
        
        // 根据type参数调用不同的方法
        switch ($type) {
            case 'son': // 下一级栏目
                $result = $this->getSon($typeid, false);
                break;
                
            case 'self': // 同级栏目
                $result = $this->getSelf($typeid);
                break;
                
            case 'top': // 顶级栏目
                $result = $this->getTop($modelid, $notypeid);
                break;
                
            case 'sonself': // 下级、同级栏目
                $result = $this->getSon($typeid, true);
                break;
                
            case 'first': // 第一级栏目
                $result = $this->getFirst($typeid);
                break;
                
            default: // 默认顶级栏目
                $result = $this->getTop($modelid, $notypeid);
                break;
        }
        
        return $result;
    }

    /**
     * 获取子栏目
     * @param string $typeid 栏目ID
     * @param boolean $self 是否包含自身
     * @return array
     */
    public function getSon($typeid = '', $self = false)
    {
        if (empty($typeid)) {
            return [];
        }
        
        $table = 'arctype';
        $query = Db::table($table)->where(['is_del' => 0, 'is_hidden' => 0, 'status' => 1]);
        
        // 获取子栏目
        $query->where('parent_id', (int)$typeid);
        
        // 排序
        $query->orderBy('sort_order')->orderBy('id');
        // 简化查询，先获取主栏目数据
        $rows = $query->select(['*', 'id as typeid'])->get();
        
        // 然后手动计算has_children
        foreach ($rows as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            $has_children = Db::table($table)->where('parent_id', (int)$item['typeid'])->count();
            $item['has_children'] = $has_children;
        }
        
        // 处理结果
        $result = [];
        foreach ($rows as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            
            // 初始化扩展属性
            $item['extends'] = '';
            
            // 生成typeurl
            if (!empty($item['is_part']) && $item['is_part'] == 1) {
                // 外部链接
                $item['typeurl'] = $item['typelink'] ?? '';
                if (!empty($item['typeurl']) && !is_http_url($item['typeurl'])) {
                    // 处理相对路径
                    $typeurl = '//' . request()->host();
                    $root_dir = ROOT_DIR ?? '';
                    if (!empty($root_dir) && !preg_match('#^' . $root_dir . '(.*)$#i', $item['typeurl'])) {
                        $typeurl .= $root_dir;
                    }
                    $typeurl .= '/' . trim($item['typeurl'], '/');
                    if (preg_match('/\/([\w\-]+)$/i', $typeurl)) {
                        $typeurl .= '/';
                    }
                    $item['typeurl'] = $typeurl;
                }
                
                // 处理target属性
                if (!empty($item['target'])) {
                    $item['extends'] .= " target='_blank' ";
                }
                
                // 处理nofollow属性
                if (!empty($item['nofollow'])) {
                    $item['extends'] .= " rel='nofollow' ";
                }
            } else {
                // 内部链接
                $ctl_name = 'Article';
                try {
                    $channeltype_info = Db::table('channeltype')->where(['id' => (int)$item['channeltype']])->first();
                    if ($channeltype_info) {
                        $ctl_name = $channeltype_info['ctl_name'] ?? 'Article';
                    }
                } catch (\Throwable $e) {
                    // 忽略错误
                }
                $item['typeurl'] = typeurl('home/' . $ctl_name . '/lists', $item);
            }
            
            // 处理封面图
            $item['litpic'] = handle_subdir_pic($item['litpic'] ?? '');
            
            // 处理选中状态
            $current_typeid = (int)request()->get('tid', 0);
            $all_parents = [];
            
            // 获取所有父级栏目
            if ($current_typeid > 0) {
                $temp_typeid = $current_typeid;
                while (true) {
                    $parent = Db::table($table)->where('id', $temp_typeid)->value('parent_id');
                    if (empty($parent) || (int)$parent === 0) {
                        break;
                    }
                    $all_parents[] = (int)$parent;
                    $temp_typeid = $parent;
                }
            }
            
            // 检查是否为当前栏目或其父级栏目
            if ((int)$item['typeid'] === $current_typeid || in_array((int)$item['typeid'], $all_parents)) {
                $item['currentclass'] = $this->currentclass;
                $item['currentstyle'] = $this->currentclass;
            } else {
                $item['currentclass'] = '';
                $item['currentstyle'] = '';
            }
            
            $result[] = $item;
        }
        
        // 如果包含自身
        if ($self && !empty($typeid)) {
            $self_channel = Db::table($table)->where('id', (int)$typeid)->field('*, id as typeid')->first();
            if ($self_channel) {
                if (is_object($self_channel)) {
                    $self_channel = (array)$self_channel;
                }
                
                // 初始化扩展属性
                $self_channel['extends'] = '';
                
                // 生成typeurl
                if (!empty($self_channel['is_part']) && $self_channel['is_part'] == 1) {
                    // 外部链接
                    $self_channel['typeurl'] = $self_channel['typelink'] ?? '';
                    if (!empty($self_channel['typeurl']) && !is_http_url($self_channel['typeurl'])) {
                        // 处理相对路径
                        $typeurl = '//' . request()->host();
                        $root_dir = ROOT_DIR ?? '';
                        if (!empty($root_dir) && !preg_match('#^' . $root_dir . '(.*)$#i', $self_channel['typeurl'])) {
                            $typeurl .= $root_dir;
                        }
                        $typeurl .= '/' . trim($self_channel['typeurl'], '/');
                        if (preg_match('/\/([\w\-]+)$/i', $typeurl)) {
                            $typeurl .= '/';
                        }
                        $self_channel['typeurl'] = $typeurl;
                    }
                    
                    // 处理target属性
                    if (!empty($self_channel['target'])) {
                        $self_channel['extends'] .= " target='_blank' ";
                    }
                    
                    // 处理nofollow属性
                    if (!empty($self_channel['nofollow'])) {
                        $self_channel['extends'] .= " rel='nofollow' ";
                    }
                } else {
                    // 内部链接
                    $ctl_name = 'Article';
                    try {
                        $channeltype_info = Db::table('channeltype')->where(['id' => (int)$self_channel['channeltype']])->first();
                        if ($channeltype_info) {
                            $ctl_name = $channeltype_info['ctl_name'] ?? 'Article';
                        }
                    } catch (\Throwable $e) {
                        // 忽略错误
                    }
                    $self_channel['typeurl'] = typeurl('home/' . $ctl_name . '/lists', $self_channel);
                }
                
                // 处理封面图
                $self_channel['litpic'] = handle_subdir_pic($self_channel['litpic'] ?? '');
                
                // 处理选中状态
                $current_typeid = (int)request()->get('tid', 0);
                if ((int)$self_channel['typeid'] === $current_typeid) {
                    $self_channel['currentclass'] = $this->currentclass;
                    $self_channel['currentstyle'] = $this->currentclass;
                } else {
                    $self_channel['currentclass'] = '';
                    $self_channel['currentstyle'] = '';
                }
                
                // 添加到结果集
                array_unshift($result, $self_channel);
            }
        }
        
        return $result;
    }

    /**
     * 获取指定栏目的第一级栏目
     * @param string $typeid 栏目ID
     * @return array
     */
    public function getFirst($typeid)
    {
        if (empty($typeid)) {
            return [];
        }
        
        $table = 'arctype';
        
        // 获取指定栏目的顶级栏目ID
        $top_typeid = $this->getTopTypeid($typeid);
        
        // 查询顶级栏目的子栏目
        $query = Db::table($table)->where(['is_del' => 0, 'is_hidden' => 0, 'status' => 1, 'parent_id' => (int)$top_typeid]);
        
        // 排序
        $query->orderBy('sort_order')->orderBy('id');
        // 简化查询，先获取主栏目数据
        $rows = $query->select(['*', 'id as typeid'])->get();
        
        // 然后手动计算has_children
        foreach ($rows as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            $has_children = Db::table($table)->where('parent_id', (int)$item['typeid'])->count();
            $item['has_children'] = $has_children;
        }
        
        // 处理结果
        $result = [];
        foreach ($rows as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            
            // 初始化扩展属性
            $item['extends'] = '';
            
            // 生成typeurl
            if (!empty($item['is_part']) && $item['is_part'] == 1) {
                // 外部链接
                $item['typeurl'] = $item['typelink'] ?? '';
                if (!empty($item['typeurl']) && !is_http_url($item['typeurl'])) {
                    // 处理相对路径
                    $typeurl = '//' . request()->host();
                    $root_dir = ROOT_DIR ?? '';
                    if (!empty($root_dir) && !preg_match('#^' . $root_dir . '(.*)$#i', $item['typeurl'])) {
                        $typeurl .= $root_dir;
                    }
                    $typeurl .= '/' . trim($item['typeurl'], '/');
                    if (preg_match('/\/([\w\-]+)$/i', $typeurl)) {
                        $typeurl .= '/';
                    }
                    $item['typeurl'] = $typeurl;
                }
                
                // 处理target属性
                if (!empty($item['target'])) {
                    $item['extends'] .= " target='_blank' ";
                }
                
                // 处理nofollow属性
                if (!empty($item['nofollow'])) {
                    $item['extends'] .= " rel='nofollow' ";
                }
            } else {
                // 内部链接
                $ctl_name = 'Article';
                try {
                    $channeltype_info = Db::table('channeltype')->where(['id' => (int)$item['channeltype']])->first();
                    if ($channeltype_info) {
                        $ctl_name = $channeltype_info['ctl_name'] ?? 'Article';
                    }
                } catch (\Throwable $e) {
                    // 忽略错误
                }
                $item['typeurl'] = typeurl('home/' . $ctl_name . '/lists', $item);
            }
            
            // 处理封面图
            $item['litpic'] = handle_subdir_pic($item['litpic'] ?? '');
            
            // 处理选中状态
            $current_typeid = (int)request()->get('tid', 0);
            if ((int)$item['typeid'] === $current_typeid) {
                $item['currentclass'] = $this->currentclass;
                $item['currentstyle'] = $this->currentclass;
            } else {
                $item['currentclass'] = '';
                $item['currentstyle'] = '';
            }
            
            $result[] = $item;
        }
        
        return $result;
    }

    /**
     * 获取同级栏目
     * @param string $typeid 栏目ID
     * @return array
     */
    public function getSelf($typeid)
    {
        if (empty($typeid)) {
            return [];
        }
        
        $table = 'arctype';
        
        // 获取父栏目ID
        $parent_id = Db::table($table)->where('id', (int)$typeid)->value('parent_id');
        
        // 查询同级栏目
        $query = Db::table($table)->where(['is_del' => 0, 'is_hidden' => 0, 'status' => 1, 'parent_id' => (int)$parent_id]);
        
        // 排序
        $query->orderBy('sort_order')->orderBy('id');
        // 简化查询，先获取主栏目数据
        $rows = $query->select(['*', 'id as typeid'])->get();
        
        // 然后手动计算has_children
        foreach ($rows as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            $has_children = Db::table($table)->where('parent_id', (int)$item['typeid'])->count();
            $item['has_children'] = $has_children;
        }
        
        // 处理结果
        $result = [];
        foreach ($rows as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            
            // 初始化扩展属性
            $item['extends'] = '';
            
            // 生成typeurl
            if (!empty($item['is_part']) && $item['is_part'] == 1) {
                // 外部链接
                $item['typeurl'] = $item['typelink'] ?? '';
                if (!empty($item['typeurl']) && !is_http_url($item['typeurl'])) {
                    // 处理相对路径
                    $typeurl = '//' . request()->host();
                    $root_dir = ROOT_DIR ?? '';
                    if (!empty($root_dir) && !preg_match('#^' . $root_dir . '(.*)$#i', $item['typeurl'])) {
                        $typeurl .= $root_dir;
                    }
                    $typeurl .= '/' . trim($item['typeurl'], '/');
                    if (preg_match('/\/([\w\-]+)$/i', $typeurl)) {
                        $typeurl .= '/';
                    }
                    $item['typeurl'] = $typeurl;
                }
                
                // 处理target属性
                if (!empty($item['target'])) {
                    $item['extends'] .= " target='_blank' ";
                }
                
                // 处理nofollow属性
                if (!empty($item['nofollow'])) {
                    $item['extends'] .= " rel='nofollow' ";
                }
            } else {
                // 内部链接
                $ctl_name = 'Article';
                try {
                    $channeltype_info = Db::table('channeltype')->where(['id' => (int)$item['channeltype']])->first();
                    if ($channeltype_info) {
                        $ctl_name = $channeltype_info['ctl_name'] ?? 'Article';
                    }
                } catch (\Throwable $e) {
                    // 忽略错误
                }
                $item['typeurl'] = typeurl('home/' . $ctl_name . '/lists', $item);
            }
            
            // 处理封面图
            $item['litpic'] = handle_subdir_pic($item['litpic'] ?? '');
            
            // 处理选中状态
            $current_typeid = (int)request()->get('tid', 0);
            if ((int)$item['typeid'] === $current_typeid) {
                $item['currentclass'] = $this->currentclass;
                $item['currentstyle'] = $this->currentclass;
            } else {
                $item['currentclass'] = '';
                $item['currentstyle'] = '';
            }
            
            $result[] = $item;
        }
        
        return $result;
    }

    /**
     * 获取顶级栏目
     * @param string $modelid 模型ID
     * @param string $notypeid 排除的栏目ID
     * @return array
     */
    public function getTop($modelid = '', $notypeid = '')
    {
        $table = 'arctype';
        $query = Db::table($table)->where(['is_del' => 0, 'is_hidden' => 0, 'status' => 1, 'parent_id' => 0]);
        
        // 模型ID筛选
        if (!empty($modelid)) {
            $query->where('channeltype', (int)$modelid);
        }
        
        // 排除指定栏目
        if (!empty($notypeid)) {
            $query->whereNotIn('id', explode(',', $notypeid));
        }
        
        // 排序
        $query->orderBy('sort_order')->orderBy('id');
        // 简化查询，先获取主栏目数据
        $rows = $query->select(['*', 'id as typeid'])->get();
        
        // 然后手动计算has_children
        foreach ($rows as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            $has_children = Db::table($table)->where('parent_id', (int)$item['typeid'])->count();
            $item['has_children'] = $has_children;
        }
        
        // 处理结果
        $result = [];
        foreach ($rows as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            
            // 初始化扩展属性
            $item['extends'] = '';
            
            // 生成typeurl
            if (!empty($item['is_part']) && $item['is_part'] == 1) {
                // 外部链接
                $item['typeurl'] = $item['typelink'] ?? '';
                if (!empty($item['typeurl']) && !is_http_url($item['typeurl'])) {
                    // 处理相对路径
                    $typeurl = '//' . request()->host();
                    $root_dir = ROOT_DIR ?? '';
                    if (!empty($root_dir) && !preg_match('#^' . $root_dir . '(.*)$#i', $item['typeurl'])) {
                        $typeurl .= $root_dir;
                    }
                    $typeurl .= '/' . trim($item['typeurl'], '/');
                    if (preg_match('/\/([\w\-]+)$/i', $typeurl)) {
                        $typeurl .= '/';
                    }
                    $item['typeurl'] = $typeurl;
                }
                
                // 处理target属性
                if (!empty($item['target'])) {
                    $item['extends'] .= " target='_blank' ";
                }
                
                // 处理nofollow属性
                if (!empty($item['nofollow'])) {
                    $item['extends'] .= " rel='nofollow' ";
                }
            } else {
                // 内部链接
                $ctl_name = 'Article';
                try {
                    $channeltype_info = Db::table('channeltype')->where(['id' => (int)$item['channeltype']])->first();
                    if ($channeltype_info) {
                        $ctl_name = $channeltype_info['ctl_name'] ?? 'Article';
                    }
                } catch (\Throwable $e) {
                    // 忽略错误
                }
                $item['typeurl'] = typeurl('home/' . $ctl_name . '/lists', $item);
            }
            
            // 处理封面图
            $item['litpic'] = handle_subdir_pic($item['litpic'] ?? '');
            
            // 处理选中状态
            $current_typeid = (int)request()->get('tid', 0);
            if ((int)$item['typeid'] === $current_typeid) {
                $item['currentclass'] = $this->currentclass;
                $item['currentstyle'] = $this->currentclass;
            } else {
                $item['currentclass'] = '';
                $item['currentstyle'] = '';
            }
            
            $result[] = $item;
        }
        
        return $result;
    }

    /**
     * 获取指定栏目的顶级栏目ID
     * @param string $typeid 栏目ID
     * @return int
     */
    public function getTopTypeid($typeid = 0)
    {
        if (empty($typeid)) {
            return 0;
        }
        
        $table = 'arctype';
        $top_typeid = (int)$typeid;
        
        while (true) {
            $parent_id = Db::table($table)->where('id', $top_typeid)->value('parent_id');
            if (empty($parent_id) || (int)$parent_id === 0) {
                break;
            }
            $top_typeid = (int)$parent_id;
        }
        
        return $top_typeid;
    }
}