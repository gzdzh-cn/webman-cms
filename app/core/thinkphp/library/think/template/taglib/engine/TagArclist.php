<?php

namespace think\template\taglib\engine;

use support\Db;
use support\Log;
 
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

    public function getArclist($param = array(),  $row = 15, $orderby = '', $addfields = '', $ordermode = '', $tagid = '', $tag = '', $pagesize = 0, $thumb = '', $arcrank = '', $type = '',$siteall = null)
    {
        try {
            // 兼容旧版参数格式
            if (is_numeric($param)) {
                $typeid = $param;
            } else {
                // 新参数格式
                $typeid = isset($param['typeid']) ? $param['typeid'] : 0;
            }
            
            // 添加调试信息
            Log::debug("TagArclist Debug - typeid: $typeid");
            Log::debug("TagArclist Debug - param: " . print_r($param, true));
            
            // 构建查询，修复表名重复前缀问题
            $query = Db::table('archives');
            
            // 基础条件
            $query->where('is_del', '=', 0);
            $query->where('arcrank', '>=', 0);
            
            // 处理typeid参数
            if ($typeid > 0) {
                $query->where('typeid', '=', (int)$typeid);
            }
            
            // 处理notypeid参数
            if (is_array($param) && isset($param['notypeid']) && !empty($param['notypeid'])) {
                $notypeid = $param['notypeid'];
                if (is_array($notypeid)) {
                    $notypeid = array_map('intval', $notypeid);
                } else {
                    $notypeid = array_map('intval', explode(',', $notypeid));
                }
                if (!empty($notypeid)) {
                    $query->whereNotIn('typeid', $notypeid);
                }
            }
            
            // 处理flag参数
            if (is_array($param) && isset($param['flag']) && !empty($param['flag'])) {
                $flag = $param['flag'];
                $query->where('flag', 'LIKE', '%' . $flag . '%');
            }
            
            // 处理noflag参数
            if (is_array($param) && isset($param['noflag']) && !empty($param['noflag'])) {
                $noflag = $param['noflag'];
                $query->where('flag', 'NOT LIKE', '%' . $noflag . '%');
            }
            
            // 处理channel参数
            if (is_array($param) && isset($param['channel']) && !empty($param['channel'])) {
                $channel = $param['channel'];
                $query->where('channel', '=', (int)$channel);
            }
            
            // 处理keyword参数
            if (is_array($param) && isset($param['keyword']) && !empty($param['keyword'])) {
                $keyword = $param['keyword'];
                $query->where('title', 'LIKE', '%' . $keyword . '%');
            }
            
            // 处理idlist参数
            if (is_array($param) && isset($param['idlist']) && !empty($param['idlist'])) {
                $idlist = $param['idlist'];
                if (is_array($idlist)) {
                    $idlist = array_map('intval', $idlist);
                } else {
                    $idlist = array_map('intval', explode(',', $idlist));
                }
                if (!empty($idlist)) {
                    $query->whereIn('aid', $idlist);
                }
            }
            
            // 处理idrange参数
            if (is_array($param) && isset($param['idrange']) && !empty($param['idrange'])) {
                $idrange = $param['idrange'];
                if (strpos($idrange, '-') !== false) {
                    list($start, $end) = explode('-', $idrange);
                    $query->whereBetween('aid', [(int)$start, (int)$end]);
                }
            }
            
            // 处理aid参数
            if (is_array($param) && isset($param['aid']) && !empty($param['aid'])) {
                $aid = $param['aid'];
                $query->where('aid', '=', (int)$aid);
            }
            
        
            // 设置排序，修复ORDER BY语法错误
            if (empty($orderby)) {
                $orderby = 'aid desc';
            } else {
                // 确保排序方向只添加一次
                if (!empty($ordermode) && stripos($orderby, 'asc') === false && stripos($orderby, 'desc') === false) {
                    $orderby .= ' ' . $ordermode;
                }
            }
            
            // 拆分排序字段和方向
            $orderParts = explode(' ', $orderby, 2);
            $orderField = trim($orderParts[0]);
            $orderDirection = isset($orderParts[1]) ? trim($orderParts[1]) : 'desc';
            $query->orderBy($orderField, $orderDirection);
            
            // 设置限制
            $query->limit((int)$row);
            
            // 处理addfields参数
            if (!empty($addfields)) {
                $query->field('*, ' . $addfields);
            }
            
            // 执行查询
            try {
                // 使用前面构建的动态查询 
                $result = $query->get();
 
                // 直接将集合转换为数组
                $resultArray = $result->toArray();
                
                // 确保每个项目都是数组类型，并处理缩略图
                foreach ($resultArray as &$item) {
                    if (is_object($item)) {
                        $item = (array)$item;
                        Log::debug("TagArclist Debug - item: " . $item['title']);
                    }
                    
                    // 处理图片路径，确保有默认图片
                        $item['litpic'] = get_default_pic($item['litpic']);
                        
                        // 如果thumb参数为on，则进行缩略图处理
                        if ($thumb == 'on') {
                            $item['litpic'] = thumb_img($item['litpic']);
                        }
                        
                        // 生成文章详情页URL
                        $item['arcurl'] = arcurl($item);
                }
                
                // 更新结果为转换后的数组
                $result = $resultArray;
     
            } catch (\Exception $e) {
                Log::error("TagArclist Db Error: " . $e->getMessage());
                throw $e;
            }
            
            // 如果是新参数格式，返回与原始EyouCMS一致的格式
            if (is_array($param)) {
                return [
                    'list' => $result,
                    'tag' => $tag
                ];
            }
            
            // 旧参数格式返回简单数组
            return $result;
        } catch (\Throwable $e) {
            // 添加详细的异常信息到日志
            Log::error("TagArclist Exception: " . $e->getMessage());
            // 如果是新参数格式，返回与原始EyouCMS一致的格式
            if (is_array($param)) {
                return [
                    'list' => [],
                    'tag' => $tag
                ];
            }
            return [];
        }
    }
}
