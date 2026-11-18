<?php
/**
 * 易优CMS
 * ============================================================================
 * 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.eyoucms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 * Author: 小虎哥 <1105415366@qq.com>
 * Date: 2018-4-3
 */

namespace app\cms\compat\template\taglib\eyou;
use function app\cms\compat\config;
use function app\cms\compat\input;
use function app\cms\compat\request;
use function app\cms\compat\cache;
use function app\cms\compat\cookie;
use function app\cms\compat\session;
use function app\cms\compat\model;
use function app\cms\compat\M;
use function app\cms\compat\lang;
use app\cms\compat\Runtime;


use app\cms\compat\Db;

/**
 * 友情链接
 */
class TagFlink extends Base
{
    //初始化
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取友情链接
     * @author wengxianhu by 2018-4-20
     */
    public function getFlink($type = 'text', $limit = '', $groupid = '')
    {
        if ($type == 'text' || $type == 'textall') {
            $typeid = 1;
        } elseif ($type == 'image') {
            $typeid = 2;
        }

        $condition = array();
        if (!empty($typeid)) {
            $condition['a.typeid'] = array('eq', $typeid);
        }
        if (!is_numeric($groupid) && $groupid === '') {
            if ($this->main_lang == $this->home_lang || !$this->language_split) {
                $groupid = 1;
            } else {
                $groupid = Db::name('links_group')->where(['lang'=>$this->home_lang])->order('id asc')->cache(true, EYOUCMS_CACHE_TIME, 'links_group')->value('id');
            }
        }
        if (!empty($groupid) && $groupid != 'all') {
            /*多语言*/
            $groupid = model('LanguageAttr')->getBindValue($groupid, 'links_group');
            if (empty($groupid)) {
                echo '标签flink报错：找不到与第一套【'.$this->main_lang.'】语言关联绑定的属性 groupid 值。';
                return false;
            } else {
                if ($this->language_split) {
                    $this->lang = Db::name('links_group')->where(['id'=>$groupid])->cache(true, EYOUCMS_CACHE_TIME, 'links_group')->value('lang');
                    if ($this->lang != $this->home_lang) {
                        $lang_title = Db::name('language_mark')->where(['mark'=>$this->home_lang])->value('cn_title');
                        echo "标签flink报错：【{$lang_title}】语言 groupid 值不存在。";
                        return false;
                    }
                }
            }
            /*--end*/
            $condition['a.groupid'] = array('eq', $groupid);
        }
        // 多城市站点
        if ($this->city_switch_on) {
            if (!empty($this->site_info)) {
                $site_flink_showall = tpCache('site.site_flink_showall');
                if ($this->site_info['level'] == 1) { // 省份
                    $province_where = [$this->siteid];
                    if (empty($site_flink_showall)) { // 分站是否显示全国
                        $province_where[] = 0;
                    }
                    $condition[] = Db::raw(" (a.province_id IN (".implode(',', $province_where).") AND a.city_id = 0) ");
                } else if ($this->site_info['level'] == 2) { // 城市
                    $province_where = '';
                    if (empty($site_flink_showall)) { // 分站是否显示全国
                        $province_where = ' OR a.province_id = 0 ';
                    }
                    $condition[] = Db::raw(" ((a.city_id = ".$this->siteid." AND a.area_id = 0) {$province_where} ) ");
                } else { // 区域
                    $province_where = '';
                    if (empty($site_flink_showall)) { // 分站是否显示全国
                        $province_where = ' OR a.province_id = 0 ';
                    }
                    $condition[] = Db::raw(" (a.area_id = ".$this->siteid." {$province_where} ) ");
                }
            } else {   //以下为主站内容展示
                //主页不显示分站友情链接
                $condition[] = Db::raw("a.province_id = 0");
            }
        }
        if (empty($condition['a.typeid']) && empty($condition['a.groupid'])) {
            $condition['a.lang'] = $this->home_lang;
        }
        $condition['a.status'] = 1;
        $result = M("links")->alias('a')->where($condition)
            ->order('a.sort_order asc')
            ->limit($limit)
            ->cache(true,EYOUCMS_CACHE_TIME,"links")
            ->select();
        foreach ($result as $key => $val) {
            $val['logo'] = get_default_pic($val['logo']);
            $val['target'] = ($val['target'] == 1) ? ' target="_blank" ' : ' target="_self" ';
            $val['nofollow'] = ($val['nofollow'] == 1) ? ' rel="nofollow" ' : '';
            $result[$key] = $val;
        }

        return $result;
    }
}