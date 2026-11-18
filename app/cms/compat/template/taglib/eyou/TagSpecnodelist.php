<?php
/**
 * 易优CMS
 * ============================================================================
 * 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.eyoucms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 * Author: 陈风任 <491085389@qq.com>
 * Date: 2020-05-25
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


use app\cms\compat\Config;
use app\cms\compat\Request;
use app\cms\compat\Db;

/**
 * 专题文章节点列表
 */
class TagSpecnodelist extends Base
{ 
    use \app\cms\compat\Jump;

    /**
     * 会员ID
     */
    public $users_id = 0;
    public $users    = [];
    public $usersTplVersion    = '';
    
    //初始化
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取提交订单数据
     */
    public function getSpecnodelist()
    {
        $aid = input('param.aid/d', 0);
        $aid = !empty($aid) ? intval($aid) : $this->aid;
        if (empty($aid)) {
            echo '标签 specnodelist 报错：页面URL缺少属性 aid 值，请填写专题文档ID。';
            return false;
        }

        // 查询专题文章节点列表
        $where = [
            'aid'    => $aid,
            'status' => 1,
            'is_del' => 0,
            'lang'   => $this->home_lang,
        ];
        $result = Db::name('special_node')->where($where)->order('sort_order asc, node_id desc')->select();

        return $result;
    }
}