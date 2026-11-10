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
 * 视频播放
 */
class TagVideoplay extends Base
{
    public $fid = '';

    //初始化
    protected function _initialize()
    {
        parent::_initialize();
        $this->fid = input('param.fid/d', 0);
    }

    /**
     * 获取每篇文章的属性
     * @author wengxianhu by 2018-4-20
     */
    public function getVideoplay($aid = '', $autoplay = '')
    {
        $aid = !empty($aid) ? $aid : $this->aid;
        if (empty($aid)) {
            echo '标签videoplay报错：缺少属性 aid 值。';
            return false;
        }

        //当前文章的视频列表
        $where = [
            'aid'   => $aid,
        ];
        !empty($this->fid) && $where['file_id'] = $this->fid;
        $row = Db::name('media_file')
            ->where($where)
            ->order('sort_order asc, file_id asc')
            ->cache(true,EYOUCMS_CACHE_TIME,"media_file")
            ->select();
        /*--end*/

        if (empty($row)) {
            return false;
        } else {
            // 获取文档数据
            $archives  = Db::name('archives')->where(['aid' => $aid])->field('users_price, users_free, arc_level_id, restric_type, is_del, status, arcrank, lang')->find();
            // 处理视频数据
            $result = [];
            foreach ($row as $key => $val) {
                if (!empty($val['file_url'])) {
                    $val['txy_video_id'] = '';
                    $FileUrl = explode('txy_video_', $val['file_url']);
                    if (empty($FileUrl[0]) && !empty($FileUrl[1])) {
                        $val['txy_video_id'] = $FileUrl[1];// 腾讯云视频ID
                    }

                    if (empty($val['txy_video_id']) && !is_http_url($val['file_url'])) {
                        $val['file_url'] = handle_subdir_pic($val['file_url'], 'media', true);
                    }

                    if (!\app\cms\compat\MediaAccess::allowsGuest($archives ?: [], $val, Runtime::context()->lang)) {
                        $val['file_url'] = '';
                        $val['txy_video_id'] = '';
                    }

                    $result = $val;
                    break;
                }
            }
            
            if (!empty($result)) {
                // 腾讯云点播视频所需内置JS及CSS样式
                $result['txy_video_file'] = <<<EOF
<link href="https://imgcache.qq.com/open/qcloud/video/tcplayer/tcplayer.css" rel="stylesheet">
<script src="https://imgcache.qq.com/open/qcloud/video/tcplayer/libs/hls.min.0.13.2m.js"></script>
<script src="https://imgcache.qq.com/open/qcloud/video/tcplayer/tcplayer.v4.1.min.js"></script>
EOF;
                $result = \app\cms\compat\MediaPlayer::playback((int)$aid, (string)$autoplay, $result);
            }

            return $result;
        }

    }
}
