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

use app\cms\compat\logic\FieldLogic;

/**
 * 相关文章列表
 */
class TagLikearticle extends Base
{
    public $fieldLogic;
    public $archives_db;
    public $version = 'v1.0.0';

    //初始化
    protected function _initialize()
    {
        parent::_initialize();
        $this->fieldLogic  = new FieldLogic();
        $this->archives_db = Db::name('archives');
        $this->version = getCmsVersion();
    }

    /**
     *  likearticle解析函数
     *
     * @author wengxianhu by 2018-4-20
     * @access    public
     * @param     array $param 查询数据条件集合
     * @param     int $row 调用行数
     * @param     string $tagid 标签id
     * @return    array
     */
    public function getLikearticle($channelid = '', $typeid = '', $limit = 12, $byabs = 0, $thumb = '')
    {
        return false; // The related-article business plugin is disabled.
    }
}