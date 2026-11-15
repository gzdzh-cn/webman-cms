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
use app\cms\compat\Request;

/**
 * 基类
 */
#[\AllowDynamicProperties]
class Base
{
    /**
     * 子目录
     */
    public $root_dir = '';

    public $request = null;

    /**
     * 当前栏目ID
     */
    public $tid = 0;

    /**
     * 当前文档aid
     */
    public $aid = 0;

    /**
     * 是否开启多语言
     */
    public $lang_switch_on = null;

    /**
     * 前台当前语言
     */
    public $lang = null;

    /**
     * 主体语言（语言列表中最早一条）
     */
    public $main_lang = null;

    /**
     * 前台当前语言
     */
    public $home_lang = null;

    /**
     * 是否开启多城市站点
     */
    public $city_switch_on = null;

    /**
     * 前台当前城市站点
     */
    public $home_site = '';

    /**
     * 当前城市站点ID
     */
    public $siteid = null;

    /**
     * 当前城市站点信息
     */
    public $site_info = null;
    
    public $php_sessid = '';

    /**
     * 多语言分离
     */
    public $language_split = null;

    /**
     * 全部文档的数据
     * @var array
     */
    public $archivesData = null;

    /**
     * 支付方式
     * @var [type]
     */
    public $pay_method_arr = [
        'wechat'     => 'WeChat',
        'alipay'     => 'Alipay',
        'artificial' => 'Manual recharge',
        'balance'    => 'Balance',
        'admin_pay'  => 'Backend',
        'delivery_pay' => 'cash on delivery',
        'Paypal'     => 'PayPal',
        'UnionPay'   => 'UnionPay',
        'noNeedPay'  => 'No payment required',
        'tikTokPay'  => 'Tiktok',
        'baiduPay'   => 'Baidu',
        'Hupijiaopay' => 'Tiger skin pepper',
        'PersonPay' => 'Alipay',
    ];

    /**
     * 订单状态
     * @var [type]
     */
    public $order_status_arr = [
        -1  => 'closed',
        0   => 'obligation',
        1   => 'Pending shipment',
        2   => 'Pending receipt of goods',
        3   => 'Completed',
        4   => 'Order Expires',
    ];

    //构造函数
    function __construct()
    {
        // 控制器初始化
        $this->_initialize();
    }

    // 初始化
    protected function _initialize()
    {
        $this->php_sessid = (string)Request::instance()->cookie('PHPSESSID', '');
        if (null == $this->request) {
            $this->request = Request::instance();
        }

        $this->root_dir = \app\cms\compat\Runtime::constant('ROOT_DIR'); // 子目录安装路径
        // 多语言
        if (null === $this->main_lang) {
            $this->main_lang = get_main_lang();
            $this->home_lang = get_home_lang();
        }
        $this->lang = $this->home_lang;
        // 多语言分离
        if (null === $this->language_split) $this->language_split = tpCache('language.language_split');

        /*多城市*/
        $this->home_site = get_home_site();
        null === $this->city_switch_on && $this->city_switch_on = config('city_switch_on');
        if ($this->city_switch_on && null === $this->site_info) {
            if (!empty($this->home_site)) {
                $this->site_info = Db::name('citysite')->where('domain', $this->home_site)->cache(true, EYOUCMS_CACHE_TIME, 'citysite')->find();
            } else {
                $site_info = cookie('site_info');
                $this->site_info = empty($site_info) ? [] : (array)json_decode($site_info);
            }
        }
        if (!empty($this->site_info)) {
            $this->siteid = $this->site_info['id'];
        }
        $this->siteid = intval($this->siteid);

        $this->tid = input("param.tid/s", '');
        $this->tid = getTrueTypeid($this->tid); // tid为目录名称的情况下

        $this->aid = input("param.aid/s", '');
        $this->aid = getTrueAid($this->aid); // 在aid传值为自定义文件名的情况下

        if (null === $this->archivesData) {
            if ('Buildhtml' == $this->request->controller()) {
                $this->archivesData = $this->get_archivesData($this->aid);
            }
        }
        $this->archivesData = !empty($this->archivesData) ? $this->archivesData : [];
    }

    //查询虎皮椒支付有没有配置相应的(微信or支付宝)支付
    public function findHupijiaoIsExis($type = '')
    {
        $hupijiaoInfo = Db::name('weapp')->where(['code'=>'Hupijiaopay','status'=>1])->find();
        $HupijiaoPay = Db::name('pay_api_config')->where(['pay_mark'=>'Hupijiaopay'])->find();
        if (empty($HupijiaoPay) || empty($hupijiaoInfo)) return true;
        if (empty($HupijiaoPay['pay_info'])) return true;
        $PayInfo = unserialize($HupijiaoPay['pay_info'], ['allowed_classes' => false]);
        if (empty($PayInfo)) return true;
        if (!isset($PayInfo['is_open_pay']) || $PayInfo['is_open_pay'] == 1) return true;
        $type .= '_appid';
        if (!isset($PayInfo[$type]) || empty($PayInfo[$type])) return true;

        return false;
    }

    public function get_aid_typeid($aid = 0)
    {
        if (!empty($this->archivesData[$aid])) {
            $typeid = $this->archivesData[$aid];
        } else {
            $cacheKey = 'table-archives-aid-'.$aid;
            $archivesInfo = cache($cacheKey);
            if (empty($archivesInfo)) {
                $archivesInfo = Db::name('archives')->field('typeid')->where('aid', $aid)->find();
                cache($cacheKey, $archivesInfo, null, 'archives');
            }
            $typeid = !empty($archivesInfo['typeid']) ? intval($archivesInfo['typeid']) : 0;
        }

        return $typeid;
    }

    /**
     * 根据aid获取对应所在的缓存文件
     * @return [type] [description]
     */
    private function get_archivesData($aid = 0)
    {
        if (empty($aid)) {
            return [];
        }

        $pagesize = 15000;
        $start = intval($aid / $pagesize) * $pagesize;
        $end = $start + $pagesize;
        $cacheKey = "table_archives_{$start}_{$end}";
        $result = cache($cacheKey);

        return $result;
    }

    /*
     *  会员组相关文档查询条件补充
     */
    protected function diy_get_users_group_archives_query_builder($alias = '')
    {
        return ''; // Membership plugin execution is disabled.
    }

    /*
     *  会员组相关栏目查询条件补充
     */
    protected function diy_get_users_group_arctype_query_builder($alias = '')
    {
        return ''; // Membership plugin execution is disabled.
    }

    /**
     * 城市分站的特殊标签替换
     * @param  string $value [description]
     * @return [type]        [description]
     */
    protected function citysite_replace_string($value = '')
    {
        if (!$this->city_switch_on || empty($this->siteid)) {
            $value = str_ireplace(['{region}','{regionAll}','{parent}','{top}'], '', $value);
        }
        return $value;
    }

    /**
     * 多城市分站与全国的显示逻辑
     * @param  array  &$condition   [description]
     * @param  [type] $site_showall [description]
     * @param  [type] $siteall      [description]
     * @return [type]               [description]
     */
    protected function site_show_archives(&$condition = [], $logic_type = 'archives', $site_showall = null, $siteall = null)
    {
        // 多城市站点
        if ($this->city_switch_on) {
            $site_config = null;
            if (null === $site_config) {
                $site_config = tpCache('site');
            }
            if (!empty($this->site_info)) {
                if ($this->site_info['level'] == 1) { // 省份
                    // 包含全国、当前省份
                    if ($siteall != null){  //标签属性控制
                        $province_where = [$this->siteid];
                        if(!empty($siteall)){  //显示主站
                            $province_where[] = 0;
                        }
                        if (!empty($site_config['site_showsuball'])) {
                            $condition[] = Db::raw(" a.province_id IN (".implode(',', $province_where).") ");
                        } else {
                            $condition[] = Db::raw(" (a.province_id IN (".implode(',', $province_where).") AND a.city_id = 0) ");
                        }
                    }
                    else if(empty($this->site_info['showall'])){   //分站点配置信息不显示主站信息
                        if (!empty($site_config['site_showsuball'])) {
                            $condition[] = Db::raw(" a.province_id = ".$this->siteid." ");
                        } else {
                            $condition[] = Db::raw(" (a.province_id = ".$this->siteid." AND a.city_id = 0) ");
                        }
                    }
                    else{
                        $province_where = [$this->siteid,0];
                        if (!empty($site_config['site_showsuball'])) {
                            $condition[] = Db::raw(" a.province_id IN (".implode(',', $province_where).") ");
                        } else {
                            $condition[] = Db::raw(" (a.province_id IN (".implode(',', $province_where).") AND a.city_id = 0) ");
                        }
                    }
                } else if ($this->site_info['level'] == 2) { // 城市
                    // 包含全国、当前城市
                    if ($siteall != null){  //标签属性控制
                        $province_where = '';
                        if(!empty($siteall)){  //显示主站
                            $province_where = ' OR a.province_id = 0 ';
                        }
                        if (!empty($site_config['site_showsuball'])) {
                            $condition[] = Db::raw(" (a.city_id = ".$this->siteid." {$province_where} ) ");
                        } else {
                            $condition[] = Db::raw(" ((a.city_id = ".$this->siteid." AND a.area_id = 0) {$province_where} ) ");
                        }
                    }
                    else if(empty($this->site_info['showall'])){   //分站点配置信息,不显示主站信息
                        if (!empty($site_config['site_showsuball'])) {
                            $condition[] = Db::raw(" a.city_id = ".$this->siteid." ");
                        } else {
                            $condition[] = Db::raw(" (a.city_id = ".$this->siteid." AND a.area_id = 0) ");
                        }
                    }
                    else{  //分站点配置信息,显示主站信息
                        if (!empty($site_config['site_showsuball'])) {
                            $condition[] = Db::raw(" (a.city_id = ".$this->siteid." OR a.province_id = 0) ");
                        } else {
                            $condition[] = Db::raw(" ((a.city_id = ".$this->siteid." AND a.area_id = 0) OR a.province_id = 0 ) ");
                        }
                    }
                    // 包含全国、全省、当前城市
                    // $citysiteInfo = Db::name('citysite')->where(['id'=>$this->siteid])->cache(true, EYOUCMS_CACHE_TIME, 'citysite')->find();
                    // $condition[] = Db::raw(" ((a.city_id IN (".$this->siteid.",0) AND a.province_id = ".$citysiteInfo['parent_id'].") OR (a.province_id = 0)) ");
                } else { // 区域
                    // 包含全国、当前区域
                    if ($siteall != null){  //标签属性控制
                        $province_where = '';
                        if(!empty($siteall)){  //显示主站
                            $province_where = ' OR a.province_id = 0 ';
                        }
                        $condition[] = Db::raw(" (a.area_id = ".$this->siteid." {$province_where} ) ");
                    }
                    else if(empty($this->site_info['showall'])){   //分站点配置信息,不显示主站信息
                        $condition[] = Db::raw("a.area_id = ".$this->siteid);
                    }
                    else{  //分站点配置信息,显示主站信息
                        $condition[] = Db::raw(" (a.area_id = ".$this->siteid." OR a.province_id = 0 ) ");
                    }
                    // 包含全国、全省、全城市
                    // $citysiteInfo = Db::name('citysite')->where(['id'=>$this->siteid])->cache(true, EYOUCMS_CACHE_TIME, 'citysite')->find();
                    // $condition[] = Db::raw(" ((a.area_id IN (".$this->siteid.",0) AND a.city_id = ".$citysiteInfo['parent_id'].") OR (a.province_id = ".$citysiteInfo['topid']." AND a.city_id = 0) OR (a.province_id = 0)) ");
                }
            }
            else { //以下为主站内容展示
                if (null === $site_showall) {
                    $site_showall = !empty($site_config['site_showall']) ? intval($site_config['site_showall']) : 0; // 多城市站点 - 全国是否显示分站的文档
                }
                if ($siteall != null){    //标签属性控制
                    if (empty($siteall)){      //不显示分站文档
                        $condition[] = Db::raw("a.province_id = 0");
                    }else{    //显示分站文档

                    }
                }
                else if (empty($site_showall)) {    //主页不显示分站文档
                    $condition[] = Db::raw("a.province_id = 0");
                }
            }
        }
        return $condition;
    }

    // 如果存在分销插件则处理分销商商品URL(携带分销商参数，用于绑定分销商上下级)
    public function handleDealerGoodsURL($result = [], $usersInfo = [], $isFind = false)
    {
        // 是否存在分销插件
        $weappInfo = model('ShopPublicHandle')->getWeappInfo('DealerPlugin');
        // 是否开启分销插件
        if (!empty($weappInfo['status']) && 1 === intval($weappInfo['status'])) {
            // URL模式(仅支持动态、伪静态)
            $seoPseudo = tpCache('seo.seo_pseudo');
            if (in_array($seoPseudo, [1, 3])) {
                // 分销商用户信息
                $usersInfo = !empty($usersInfo) ? $usersInfo : GetUsersLatestData();
                $dealerInfo = !empty($usersInfo['dealer']) ? $usersInfo['dealer'] : [];
                if (!empty($dealerInfo['users_id']) && !empty($dealerInfo['dealer_id']) && !empty($dealerInfo['dealer_status']) && 1 === intval($dealerInfo['dealer_status'])) {
                    if (!empty($isFind)) $result[0] = $result;
                    foreach ($result as $key => $value) {
                        if (!empty($value['arcurl']) && !empty($value['current_channel']) && 2 === intval($value['current_channel'])) {
                            // 分销商参数
                            $dealerParam = base64_encode(json_encode($dealerInfo['users_id'] . '_eyoucms_' . $dealerInfo['dealer_id']));
                            // 动态
                            if (!empty($seoPseudo) && 1 === intval($seoPseudo)) {
                                $result[$key]['arcurl'] = $value['arcurl'] . '&dealerParam=' . $dealerParam;
                            }
                            // 伪静态
                            else if (!empty($seoPseudo) && 3 === intval($seoPseudo)) {
                                $result[$key]['arcurl'] = $value['arcurl'] . '?dealerParam=' . $dealerParam;
                            }
                        }
                    }
                    if (!empty($isFind)) $result = $result[0];
                }
            }
        }
        // 返回参数
        return $result;
    }

    // 处理商品数据信息
    public function handleGoodsInfo($goodsList = [])
    {
        // Ordinary enterprise article lists do not require a member session.
        $hasGoods = false;
        foreach ($goodsList as $goods) {
            if (2 === intval($goods['current_channel'] ?? 0)) { $hasGoods = true; break; }
        }
        if (!$hasGoods) return $goodsList;
        $shopConfig = getUsersConfigData('shop');
        $goodsSpecArr = [];
        if (!empty($shopConfig['shop_open']) && !empty($shopConfig['shop_open_spec'])) {
            // 查询商品规格并分类处理
            $where = [
                'aid' => ['IN', get_arr_column($goodsList, 'aid')],
                'spec_stock' => ['gt', 0],
            ];
            $goodsSpecArr = Db::name('product_spec_value')->where($where)->order('spec_price asc')->select();
            $goodsSpecArr = !empty($goodsSpecArr) ? group_same_key($goodsSpecArr, 'aid') : [];
        }

        // 会员折扣率
        $usersInfo = !empty($shopConfig['shop_open']) ? GetUsersLatestData() : [];
        $discountRate = !empty($usersInfo['level_discount']) ? floatval($usersInfo['level_discount']) / floatval(100) : 1;

        // 处理价格及库存
        foreach ($goodsList as $key => $value) {
            if (!empty($value['current_channel']) && 2 === intval($value['current_channel'])) {
                $specArr = [];
                if (!empty($goodsSpecArr[$value['aid']][0])) {
                    // 产品规格
                    $specArr = $goodsSpecArr[$value['aid']][0];
                    // 规格价格
                    if (!empty($specArr) && $specArr['spec_price'] >= 0) {
                        $goodsList[$key]['old_price'] = unifyPriceHandle($specArr['spec_price']);
                        $goodsList[$key]['users_price'] = unifyPriceHandle($specArr['spec_price'] * $discountRate);
                        $goodsList[$key]['crossed_price'] = unifyPriceHandle($specArr['spec_crossed_price'] * $discountRate);
                    }
                    // 规格库存
                    if (!empty($specArr) && $specArr['spec_stock'] >= 0) $goodsList[$key]['stock_count'] = $specArr['spec_stock'];
                } else {
                    // 无规格
                    $goodsList[$key]['old_price'] = unifyPriceHandle($value['users_price']);
                    $goodsList[$key]['users_price'] = unifyPriceHandle($value['users_price'] * $discountRate);
                    $goodsList[$key]['crossed_price'] = unifyPriceHandle(($value['crossed_price'] ?? 0) * $discountRate);
                }

                // 加入购物车 onclick
                $specValueID = !empty($specArr['spec_value_id']) ? trim($specArr['spec_value_id']) : '';
                $goodsList[$key]['ShopAddCart'] = " onclick=\"ShopAddCart1625194556('{$value['aid']}', '{$specValueID}', 1, '{$this->root_dir}');\" ";

                // 查询会员折扣价
                if (empty($specValueID) && !empty($usersInfo['level_id']) && 1 === intval($value['users_discount_type'])) {
                    $goodsList[$key]['users_price'] = model('ShopPublicHandle')->handleUsersDiscountPrice($value['aid'], $usersInfo['level_id']);
                }
            }
        }

        // 如果存在分销插件则处理分销商商品URL(携带分销商参数，用于绑定分销商上下级)
        return $this->handleDealerGoodsURL($goodsList, $usersInfo);
    }

}
