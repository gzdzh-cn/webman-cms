<?php

namespace plugin\admin\app\model;

use plugin\admin\app\common\Util;
use plugin\admin\app\model\Base;
use plugin\admin\app\model\Tagindex;

/**
 * 标签关联模型
 */
class Taglist extends Base
{
    protected $table = 'taglist';
    
    /**
     * 禁用自动时间戳管理（使用 add_time 和 update_time 字段）
     */
    public $timestamps = false;
    
    /**
     * 保存标签
     * @param int $aid 文档ID
     * @param int $typeid 栏目ID
     * @param string $tag 标签字符串（逗号分隔）
     * @param int $arcrank 阅读权限
     * @param string $opt 操作类型：add 新增，edit 编辑
     * @return bool
     */
    public function savetags($aid = 0, $typeid = 0, $tag = '', $arcrank = 0, $opt = 'add')
    {
        $rs = true;
        $tag = strip_tags(htmlspecialchars_decode($tag));
        
        if ($opt == 'add') {
            $tag = str_replace('，', ',', $tag);
            $tags = explode(',', $tag);
            $tags = array_map('trim', $tags);
            $tags = array_unique($tags);
            
            foreach($tags as $tag) {
                $tag = trim($tag);
                if (empty($tag) || $tag != stripslashes($tag)) {
                    continue;
                }
                
                $r = $this->InsertOneTag($tag, $aid, $typeid, $arcrank);
                if ($rs !== false) {
                    $rs = $r;
                }
            }
        } else if ($opt == 'edit') {
            $rs = $this->UpdateOneTag($aid, $typeid, $tag, $arcrank);
        }
        
        return $rs;
    }
    
    /**
     * 插入一个标签
     * @param string $tag 标签
     * @param int $aid 文档ID
     * @param int $typeid 栏目ID
     * @param int $arcrank 阅读权限
     * @return bool
     */
    private function InsertOneTag($tag, $aid, $typeid = 0, $arcrank = 0)
    {
        $tag = trim($tag);
        if (empty($tag)) {
            return true;
        }
        
        if (empty($typeid)) {
            $typeid = 0;
        }
        
        $rs = false;
        $addtime = time();
        $lang = 'cn'; // 默认语言，可以根据实际情况调整
        
        // 检查标签索引表中是否存在该标签
        $tagindex = Tagindex::where('tag', $tag)
            ->where('lang', $lang)
            ->first();
        
        if (empty($tagindex)) {
            // 不存在，插入新标签到 tagindex 表
            $tagindex = new Tagindex();
            $tagindex->tag = $tag;
            $tagindex->typeid = $typeid;
            $tagindex->seo_title = '';
            $tagindex->seo_keywords = '';
            $tagindex->seo_description = '';
            $tagindex->total = 1;
            $tagindex->weekup = $addtime;
            $tagindex->monthup = $addtime;
            $tagindex->lang = $lang;
            $tagindex->add_time = $addtime;
            $tagindex->update_time = $addtime;
            $rs = $tagindex->save();
            $tid = $tagindex->id;
        } else {
            // 存在，更新 total 字段
            $tagindex->increment('total', 1, [
                'update_time' => $addtime,
            ]);
            $rs = true;
            $tid = $tagindex->id;
        }
        
        if ($rs !== false) {
            // 插入标签关联记录到 taglist 表
            $taglist = new Taglist();
            $taglist->tid = $tid;
            $taglist->aid = $aid;
            $taglist->typeid = $typeid;
            $taglist->tag = $tag;
            $taglist->arcrank = $arcrank;
            $taglist->lang = $lang;
            $taglist->add_time = $addtime;
            $taglist->update_time = $addtime;
            $rs = $taglist->save();
        }
        
        return $rs !== false;
    }
    
    /**
     * 更新标签
     * @param int $aid 文档ID
     * @param int $typeid 栏目ID
     * @param string $tags 标签字符串（逗号分隔）
     * @param int $arcrank 阅读权限
     * @return bool
     */
    private function UpdateOneTag($aid, $typeid, $tags = '', $arcrank = 0)
    {
        $rs = true;
        $lang = 'cn'; // 默认语言，可以根据实际情况调整
        
        // 获取旧标签
        $oldtag = $this->GetTags($aid);
        $oldtags = !empty($oldtag) ? explode(',', $oldtag) : [];
        
        // 处理新标签
        $tags = str_replace('，', ',', $tags);
        $new_tags = !empty($tags) ? explode(',', $tags) : [];
        $new_tags = array_map('trim', $new_tags);
        $new_tags = array_filter($new_tags);
        
        if (!empty($tags) || !empty($oldtags)) {
            // 原来的tag转成小写，匹配不区分大小写
            $lower_oldtags = [];
            foreach ($oldtags as $key => $val) {
                $lower_oldtags[$key] = strtolower(trim($val));
            }
            
            // 新的tag转成小写，匹配不区分大小写
            $lower_newtags = [];
            foreach ($new_tags as $key => $val) {
                $lower_newtags[$key] = strtolower(trim($val));
            }
            
            // 添加新标签
            foreach($new_tags as $tag) {
                $tag = trim($tag);
                if (empty($tag) || $tag != stripslashes($tag)) {
                    continue;
                }
                
                if (!in_array(strtolower($tag), $lower_oldtags)) {
                    $r = $this->InsertOneTag($tag, $aid, $typeid, $arcrank);
                    if ($rs !== false) {
                        $rs = $r;
                    }
                }
            }
            
            // 获取旧标签的统计信息
            $taglistMap = [];
            if (!empty($oldtags)) {
                $taglistRow = Taglist::whereIn('tag', $oldtags)
                    ->where('lang', $lang)
                    ->selectRaw('count(tid) as total, tag')
                    ->groupBy('tag')
                    ->get()
                    ->toArray();
                
                foreach ($taglistRow as $val) {
                    $val = is_array($val) ? $val : (array)$val;
                    $taglistMap[md5($val['tag'])] = $val;
                }
            }
            
            $time = time();
            // 删除不存在的标签
            foreach ($oldtags as $tag) {
                $tag = trim($tag);
                if (empty($tag)) {
                    continue;
                }
                
                $update_time = $time;
                if (!in_array(strtolower($tag), $lower_newtags)) {
                    // 删除标签关联
                    Taglist::where('aid', $aid)
                        ->where('tag', $tag)
                        ->delete();
                    
                    // 更新标签统计
                    $total = !empty($taglistMap[md5($tag)]) ? $taglistMap[md5($tag)]['total'] - 1 : 0;
                    $tagindex = Tagindex::where('tag', $tag)
                        ->where('lang', $lang)
                        ->first();
                    
                    if ($tagindex) {
                        if ($total > 0) {
                            $tagindex->total = $total;
                            $tagindex->update_time = $update_time;
                            $tagindex->save();
                        } else {
                            // 如果 total 为 0，可以选择删除或保留标签索引
                            // 这里选择保留，只更新 total 为 0
                            $tagindex->total = 0;
                            $tagindex->update_time = $update_time;
                            $tagindex->save();
                        }
                    }
                } else {
                    // 更新标签关联信息
                    Taglist::where('aid', $aid)
                        ->where('tag', $tag)
                        ->where('lang', $lang)
                        ->update([
                            'typeid' => $typeid,
                            'update_time' => $update_time,
                        ]);
                    
                    // 更新阅读权限
                    Taglist::where('aid', $aid)
                        ->update([
                            'arcrank' => $arcrank,
                        ]);
                }
            }
        }
        
        return $rs;
    }
    
    /**
     * 获取文档的所有标签
     * @param int $aid 文档ID
     * @return string 标签字符串（逗号分隔）
     */
    public function GetTags($aid)
    {
        $tags = '';
        $rows = Taglist::where('aid', $aid)
            ->select('tag')
            ->get()
            ->toArray();
        
        $tagArray = [];
        foreach ($rows as $row) {
            $row = is_array($row) ? $row : (array)$row;
            $tagArray[] = $row['tag'];
        }
        
        return implode(',', $tagArray);
    }
}

