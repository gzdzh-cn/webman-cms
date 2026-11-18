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
 * 视频列表
 */
class TagVideolist extends Base
{
    //初始化
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取每篇文章的属性
     * @author wengxianhu by 2018-4-20
     */
    public function getVideolist($aid = '', $autoplay = '', $player = '')
    {
        $aid = !empty($aid) ? $aid : $this->aid;
        if (empty($aid)) {
            echo '标签videolist报错：缺少属性 aid 值。';
            return false;
        }

        //当前文章的视频列表
        $row = Db::name('media_file')
            ->where(['aid' => $aid])
            ->order('sort_order asc, file_id asc')
            ->cache(true,EYOUCMS_CACHE_TIME,"media_file")
            ->select();
        /*--end*/

        if (empty($row)) {
            return false;
        } else {
            $result = [];
            $archive = Db::name('archives')->where(['aid' => $aid])->field('users_price, users_free, arc_level_id, restric_type, is_del, status, arcrank, lang')->find();
            foreach ($row as $key => $val) {
                if (!empty($val['file_url'])) {
                    // Keep the episode and onclick even when guest playback is
                    // denied; never publish its private URL/cloud identifier.
                    $row[$key]['__eyou_media_has_source'] = true;
                    if (!\app\cms\compat\MediaAccess::allowsGuest($archive ?: [], $val, Runtime::context()->lang)) {
                        $row[$key]['file_url'] = '';
                        $row[$key]['txy_video_id'] = '';
                    } elseif (!is_http_url($val['file_url']) && !str_starts_with($val['file_url'], 'txy_video_')) {
                        $row[$key]['file_url'] = handle_subdir_pic($val['file_url'], 'media', true);
                    }
                    
                    $row[$key]['file_time']  = gmSecondFormat(intval($val['file_time']), ':');
                    $result[] = $row[$key];
                } else {
                    unset($row[$key]);
                }
            }
            return \app\cms\compat\MediaPlayer::playlist((int)$aid, (string)$autoplay, (string)$player, $result);
        }
    }
}
