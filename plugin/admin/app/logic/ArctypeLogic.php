<?php

namespace plugin\admin\app\logic;

use plugin\admin\app\model\Arctype;
use plugin\admin\app\common\Util;
use support\exception\BusinessException;

/**
 * 栏目逻辑类
 */
class ArctypeLogic
{
    /**
     * 获取目录名称
     * @param string $typename 栏目名称
     * @param string $dirname 目录名称
     * @param int $id 栏目ID（用于更新时排除自己）
     * @param array $newDirnameArr 新增的目录名称数组（用于批量添加时避免重复）
     * @return string
     */
    public function get_dirname($typename = '', $dirname = '', $id = 0, $newDirnameArr = [])
    {
        $id = intval($id);
        if (!trim($dirname) || empty($dirname)) {
            // 如果没有提供目录名，从栏目名称生成拼音
            $dirname = $this->getPinyin($typename);
        }
        
        // 如果目录名是纯数字，添加随机字符串
        if (strval(intval($dirname)) == strval($dirname)) {
            if (preg_match('/^([0-9]+)$/i', $dirname)) {
                $dirname .= $this->getRandStr(3, 0, 0);
            } else {
                $dirname .= $this->getRandStr(3, 0, 2);
            }
        }
        
        // 替换空格为下划线
        $dirname = preg_replace('/(\s)+/', '_', $dirname);
        
        // 检查唯一性
        if (!$this->dirname_unique($dirname, $id, $newDirnameArr)) {
            $nowDirname = $dirname . $this->getRandStr(3, 0, 2);
            return $this->get_dirname($typename, $nowDirname, $id, $newDirnameArr);
        }

        return $dirname;
    }

    /**
     * 判断目录名称的唯一性
     * @param string $dirname 目录名称
     * @param int $typeid 栏目ID（用于更新时排除自己）
     * @param array $newDirnameArr 新增的目录名称数组
     * @return bool
     */
    public function dirname_unique($dirname = '', $typeid = 0, $newDirnameArr = [])
    {
        // 获取所有已存在的目录名称
        $query = Arctype::select('id', 'dirname');
        if ($typeid > 0) {
            $query = $query->where('id', '!=', $typeid);
        }
        $result = $query->pluck('dirname', 'id')->toArray();
        $existingDirnames = array_values($result);
        
        // 禁用目录名称列表（系统内置）
        $disableDirname = [
            'admin', 'api', 'index', 'home', 'user', 'member', 
            'search', 'tags', 'sitemap', 'rss', '404', '403'
        ];
        
        // 合并已存在的目录名称和禁用列表
        $allDirnames = array_merge($disableDirname, $existingDirnames);
        if (!empty($newDirnameArr)) {
            $allDirnames = array_merge($allDirnames, $newDirnameArr);
        }
        
        // 检查是否冲突（不区分大小写）
        foreach ($allDirnames as $val) {
            if (strtolower($dirname) == strtolower($val)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * 获取拼音
     * @param string $str
     * @return string
     */
    protected function getPinyin($str)
    {
        if (empty($str)) {
            return '';
        }
        
        // 尝试使用 overtrue/pinyin 库（如果已安装）
        if (class_exists('\Overtrue\Pinyin\Pinyin')) {
            try {
                $pinyin = new \Overtrue\Pinyin\Pinyin();
                // 使用 permalink 方法，去除分隔符，转换为小写
                // permalink 方法会将中文转换为拼音，并用分隔符连接（这里设为空字符串表示无分隔符）
                $result = $pinyin->permalink($str, '');
                // 只保留字母和数字，去除其他字符
                $result = preg_replace('/[^a-z0-9]/', '', strtolower($result));
                return $result;
            } catch (\Exception $e) {
                // 如果出错，回退到简化版本
            }
        }
        
        // 简化实现：如果包含中文，使用简化处理
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $str)) {
            // 如果没有拼音库，只保留字母和数字
            return preg_replace('/[^\w]/', '', strtolower($str));
        }
        
        // 非中文字符，直接返回小写字母和数字
        return preg_replace('/[^\w]/', '', strtolower($str));
    }

    /**
     * 生成随机字符串
     * @param int $length 长度
     * @param int $numeric 是否纯数字
     * @param int $letter 是否包含字母
     * @return string
     */
    protected function getRandStr($length = 6, $numeric = 0, $letter = 1)
    {
        $chars = '';
        if ($numeric) {
            $chars .= '0123456789';
        }
        if ($letter) {
            $chars .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (empty($chars)) {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * 验证栏目数据
     * @param array $data 栏目数据
     * @throws BusinessException
     */
    public function validateArctypeData(array $data): void
    {
        // 验证栏目名称不能为空
        if (empty($data['typename'])) {
            throw new BusinessException('栏目名称不能为空');
        }
        
        // 验证模板字段
        $currentChannel = isset($data['current_channel']) ? intval($data['current_channel']) : 0;
        if ($currentChannel == 6 || $currentChannel == 8) {
            // 单页模型和留言模型：只验证templist
            if (empty($data['templist']) || $data['templist'] == '0') {
                $templateName = $currentChannel == 6 ? '单页模板' : '留言模板';
                throw new BusinessException($templateName . '不能为空');
            }
        } elseif ($currentChannel > 0) {
            // 其他模型：验证templist和tempview
            if (empty($data['templist']) || $data['templist'] == '0') {
                throw new BusinessException('栏目模板不能为空');
            }
            if (empty($data['tempview']) || $data['tempview'] == '0') {
                throw new BusinessException('文档模板不能为空');
            }
        }
    }

    /**
     * 处理栏目数据（公共逻辑）
     * @param array $data 栏目数据
     * @return array 处理后的数据
     */
    public function processArctypeData(array $data): array
    {
        // 处理栏目名称
        if (isset($data['typename'])) {
            $data['typename'] = str_replace('\\', '/', $data['typename']);
            $data['typename'] = addslashes(htmlspecialchars(strip_tags($data['typename'])));
            $data['typename'] = str_replace(["\'", "&amp;"], ["'", "&"], $data['typename']);
        }
        
        // 处理外部链接
        $data['typelink'] = (!empty($data['is_part']) && $data['is_part'] == 1) ? ($data['typelink'] ?? '') : '';
        $data['target'] = !empty($data['target']) ? 1 : 0;
        $data['nofollow'] = !empty($data['nofollow']) ? 1 : 0;
        
        // 处理分页限制
        if (isset($data['page_limit']) && is_array($data['page_limit'])) {
            $data['page_limit'] = empty($data['page_limit']) ? '' : implode(',', $data['page_limit']);
        }
        
        // 处理封面图（本地/远程）
        $is_remote = !empty($data['is_remote']) ? $data['is_remote'] : 0;
        if ($is_remote == 1) {
            $data['litpic'] = $data['litpic_remote'] ?? '';
        } else {
            $data['litpic'] = $data['litpic_local'] ?? '';
        }
        
        // 处理SEO关键词
        if (isset($data['seo_keywords'])) {
            $data['seo_keywords'] = str_replace('，', ',', $data['seo_keywords']);
        }
        
        // 处理列表/文档命名规则
        if (isset($data['rulelist'])) {
            $data['rulelist'] = trim($data['rulelist']);
        }
        if (isset($data['ruleview'])) {
            $data['ruleview'] = trim($data['ruleview']);
        }
        
        // 处理模板字段：确保templist和tempview正确保存
        if (isset($data['templist'])) {
            if ($data['templist'] == '0' || empty($data['templist'])) {
                $data['templist'] = '';
            } else {
                $data['templist'] = trim($data['templist']);
            }
        }
        
        if (isset($data['tempview'])) {
            if ($data['tempview'] == '0' || empty($data['tempview'])) {
                $data['tempview'] = '';
            } else {
                $data['tempview'] = trim($data['tempview']);
            }
        }
        
        return $data;
    }

    /**
     * 处理目录名称
     * @param array $data 栏目数据
     * @param int $id 栏目ID（用于更新时排除自己，新增时为0）
     * @return string 处理后的目录名称
     * @throws BusinessException
     */
    public function processDirname(array $data, int $id = 0): string
    {
        $inputDirname = isset($data['dirname']) ? trim($data['dirname']) : '';
        
        // 只有当 dirname 为空时，才使用 typename 转拼音
        if (empty($inputDirname)) {
            // dirname 为空，使用 typename 转拼音
            $dirname = $this->get_dirname(
                $data['typename'] ?? '', 
                '', 
                $id
            );
        } else {
            // dirname 不为空，使用用户传入的值（清理后）
            $dirname = preg_replace('/([^\w\-])/i', '', $inputDirname);
            // 检查唯一性
            if (!$this->dirname_unique($dirname, $id)) {
                $arctype_is_del = Arctype::where('dirname', $dirname)
                    ->when($id > 0, function($query) use ($id) {
                        return $query->where('id', '!=', $id);
                    })
                    ->value('is_del');
                if (empty($arctype_is_del)) {
                    throw new BusinessException('目录名称与系统内置冲突，请更改！');
                } else {
                    throw new BusinessException('目录名称与回收站里的栏目冲突，请更改！');
                }
            }
        }
        
        return $dirname;
    }

    /**
     * 计算目录路径
     * @param string $dirname 目录名称
     * @param int|null $parentId 父栏目ID
     * @param string $currentDirpath 当前目录路径（更新时使用）
     * @param bool $parentIdChanged 父栏目是否变化（更新时使用）
     * @param bool $dirnameChanged 目录名称是否变化（更新时使用）
     * @return string 目录路径
     */
    public function calculateDirpath(string $dirname, ?int $parentId = null, string $currentDirpath = '', bool $parentIdChanged = false, bool $dirnameChanged = false): string
    {
        // 如果是更新且路径未变化，使用当前路径
        if (!empty($currentDirpath) && !$parentIdChanged && !$dirnameChanged) {
            return $currentDirpath;
        }
        
        // 重新计算路径
        $predirpath = ''; // 父栏目的目录路径
        
        // 如果有父栏目，获取父栏目的目录路径
        if (!empty($parentId)) {
            $parentInfo = Arctype::select('dirpath')
                ->where('id', $parentId)
                ->first();
            if ($parentInfo) {
                $predirpath = $parentInfo->dirpath ?? '';
            }
        }
        
        $baseDirpath = '';
        if (!empty($predirpath)) {
            // 有父栏目，使用父栏目的 dirpath
            $baseDirpath = rtrim($predirpath, '/');
        }
        
        // 构建完整的 dirpath：基础路径 + / + 目录名称
        if (!empty($baseDirpath)) {
            $dirpath = $baseDirpath . '/' . $dirname;
        } else {
            // 顶级栏目，dirpath = /dirname
            $dirpath = '/' . $dirname;
        }
        
        return $dirpath;
    }

    /**
     * 获取父栏目信息（用于计算topid、grade、channeltype等）
     * @param int|null $parentId 父栏目ID
     * @return array|null ['id', 'channeltype', 'topid', 'grade', 'dirpath', 'diy_dirpath']
     */
    public function getParentInfo(?int $parentId): ?array
    {
        if (empty($parentId)) {
            return null;
        }
        
        $parentInfo = Arctype::select('id', 'channeltype', 'topid', 'grade', 'dirpath', 'diy_dirpath')
            ->where('id', $parentId)
            ->first();
        
        if (!$parentInfo) {
            return null;
        }
        
        return [
            'id' => $parentInfo->id,
            'channeltype' => $parentInfo->channeltype ?? 0,
            'topid' => !empty($parentInfo->topid) ? $parentInfo->topid : $parentInfo->id,
            'grade' => $parentInfo->grade ?? 0,
            'dirpath' => $parentInfo->dirpath ?? '',
            'diy_dirpath' => $parentInfo->diy_dirpath ?? '',
        ];
    }
}

