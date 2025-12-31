<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * 区域模型
 * @property integer $id 主键
 * @property string $name 区域名称
 * @property integer $parent_id 父级ID
 */
class Region extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'region';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * 获取指定父级ID下的区域列表
     * @param int $parent_id 父级ID
     * @return array
     */
    public static function getRegionList($parent_id = 0)
    {
        try {
            return self::where('parent_id', $parent_id)
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            // 如果表不存在或其他错误，返回空数组
            error_log('获取区域列表失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 获取指定区域ID下的城市并判断是否需要处理特殊市返回值
     * 特殊市：北京市，上海市，天津市，重庆市
     * 参考：EyouCMS Field::SpecialCityDealWith
     * @param int $parent_id 父级ID
     * @param array $specialCityIds 特殊城市ID列表（可以从配置读取）
     * @return array
     */
    public static function getSpecialCityRegionList($parent_id = 0, $specialCityIds = [])
    {
        empty($parent_id) && $parent_id = 0;
        
        // 先获取直接子级
        $regionData = self::getRegionList($parent_id);
        
        // 如果 parent_id 在特殊城市列表中，则获取这些城市的下一级区域（区县）
        if (in_array($parent_id, $specialCityIds) && !empty($regionData)) {
            $region_ids = [];
            foreach ($regionData as $item) {
                $region_ids[] = $item['id'] ?? 0;
            }
            
            if (!empty($region_ids)) {
                // 获取这些城市的下一级区域
                $regionData = self::whereIn('parent_id', $region_ids)
                    ->orderBy('id', 'asc')
                    ->get()
                    ->toArray();
            }
        }
        
        return $regionData;
    }
}

