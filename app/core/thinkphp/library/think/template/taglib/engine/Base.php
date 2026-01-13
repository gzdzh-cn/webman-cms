<?php

namespace think\template\taglib\engine;

use support\Request;
use support\Db;
/**
 * 基类
 */
class Base
{
    /**
     * 子目录
     */
    public $root_dir = '';

    static $request = null;

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
    static $lang_switch_on = null;

    /**
     * 前台当前语言
     */
    public $lang = null;

    /**
     * 主体语言（语言列表中最早一条）
     */
    static $main_lang = null;

    /**
     * 前台当前语言
     */
    static $home_lang = null;

    /**
     * 是否开启多城市站点
     */
    static $city_switch_on = null;

    /**
     * 前台当前城市站点
     */
    static $home_site = '';

    /**
     * 当前城市站点ID
     */
    static $siteid = null;

    /**
     * 当前城市站点信息
     */
    static $site_info = null;
    
    public $php_sessid = '';

    /**
     * 多语言分离
     */
    static $language_split = null;

    /**
     * 全部文档的数据
     * @var array
     */
    static $archivesData = null;

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
        $this->php_sessid = !empty($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
        if (null == self::$request) {
            self::$request = request();
        }

        $this->root_dir = ROOT_DIR; // 子目录安装路径

        // 获取当前tid和aid
        $this->tid = request()->get('tid', 0);
        $this->aid = request()->get('aid', 0);
    }



    /**
     * 查询虎皮椒支付有没有配置相应的(微信or支付宝)支付
     * @param string $type
     * @return bool
     */
    public function findHupijiaoIsExis($type = '')
    {
        // 简化实现，返回false
        return false;
    }

    /**
     * 根据aid获取对应所在的缓存文件
     * @return array
     */
    private function get_archivesData($aid = 0)
    {
        return [];
    }

    /**
     * 根据aid获取栏目ID
     * @param int $aid
     * @return int
     */
    public function get_aid_typeid($aid = 0)
    {
        if (empty($aid)) {
            return 0;
        }
        
        try {
            $archivesInfo = Db::table('wa_archives')
                ->where('aid', $aid)
                ->value('typeid');
            
            return intval($archivesInfo);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /*
     *  会员组相关文档查询条件补充
     */
    protected function diy_get_users_group_archives_query_builder($alias = '')
    {
        return "";
    }

    /*
     *  会员组相关栏目查询条件补充
     */
    protected function diy_get_users_group_arctype_query_builder($alias = '')
    {
        return "";
    }

    /**
     * 城市分站的特殊标签替换
     * @param  string $value
     * @return string
     */
    protected function citysite_replace_string($value = '')
    {
        return $value;
    }

    /**
     * 多城市分站与全国的显示逻辑
     * @param  array  &$condition
     * @param  string $logic_type
     * @param  mixed  $site_showall
     * @param  mixed  $siteall
     * @return array
     */
    protected function site_show_archives(&$condition = [], $logic_type = 'archives', $site_showall = null, $siteall = null)
    {
        return $condition;
    }

    // 如果存在分销插件则处理分销商商品URL(携带分销商参数，用于绑定分销商上下级)
    public function handleDealerGoodsURL($result = [], $usersInfo = [], $isFind = false)
    {
        return $result;
    }

    // 处理商品数据信息
    public function handleGoodsInfo($goodsList = [])
    {
        return $goodsList;
    }
 
}
