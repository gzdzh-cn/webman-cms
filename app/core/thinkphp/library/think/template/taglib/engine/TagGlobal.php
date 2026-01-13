<?php

namespace think\template\taglib\engine;

use support\Db;
use support\Request;
use think\Config;

class TagGlobal extends Base
{
    //初始化
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取全局变量
     * @param string $name 变量名称
     * @return string
     */
    public function getGlobal($name = '')
    {
        if (empty($name)) {
            return '标签global报错：缺少属性 name 。';
        }

        $param = explode('|', $name);
        $name = trim($param[0], '$');
        $value = '';

        // 获取请求参数
        $uiset = request()->get('uiset', 'off');
        $uiset = trim($uiset, '/');

        /*PC端与手机端的变量名自适应，可彼此通用*/
        if (in_array($name, ['web_thirdcode_pc','web_thirdcode_wap'])) {
            $name = 'web_thirdcode_' . (isMobile() ? 'wap' : 'pc');
        }
        /*--end*/

        // 获取全局配置
        $globalData = $this->getGlobalData();
        
        // 特殊处理web_title，避免显示斜杠
        if ($name == 'web_title') {
            $value = '';
        } else if (!empty($globalData[$name])) {
            $value = $globalData[$name];

            switch ($name) {
                case 'web_keywords':
                case 'web_description':
                    $value = $this->site_seo($name, $value, $globalData);
                    break;

                case 'web_basehost':
                case 'web_cmsurl':
                    /*URL全局参数*/
                    $urlParam = request()->all();
                    $parse_url_param = ['uiset', 'v', 'lang', 'site'];
                    $filteredParam = [];
                    foreach ($urlParam as $key => $val) {
                        if (in_array($key, $parse_url_param)) {
                            $filteredParam[$key] = trim($val, '/');
                        }
                    }
                    /*--end*/

                    if ('on' == $uiset) {
                        $scheme = request()->scheme();
                        $host = request()->host();
                        $value = $scheme . '://' . $host . '/index.php';
                        if (!empty($filteredParam)) {
                            $value .= '?' . http_build_query($filteredParam);
                        }
                    } else {
                        $scheme = request()->scheme();
                        $host = request()->host();
                        $value = $scheme . '://' . $host . $this->root_dir;
                    }
                    break;

                case 'web_root_dir':
                    $value = $this->root_dir;
                    break;
                
                case 'web_recordnum':
                    if (!empty($value) && empty($globalData['web_recordnum_mode'])) {
                        $value = '<a href="https://beian.miit.gov.cn/" rel="nofollow" target="_blank">' . $value . '</a>';
                    }
                    break;
                
                case 'web_garecordnum':
                    if (!empty($value) && empty($globalData['web_garecordnum_mode'])) {
                        $recordcode = preg_replace('/([^\d\-]+)/i', '', $value);
                        $value = '<a href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=' . $recordcode . '" rel="nofollow" target="_blank">' . $value . '</a>';
                    }
                    break;

                case 'web_templets_pc':
                case 'web_templets_m':
                    $value = $this->root_dir . $value;
                    break;

                case 'web_thirdcode_pc':
                case 'web_thirdcode_wap':
                    $value = '';
                    break;

                case 'ey_common_hidden':
                    $baseFile = request()->path();
                    $value = <<<EOF
    <script type="text/javascript">
        var __eyou_basefile__ = '{$baseFile}';
        var __root_dir__ = '{$this->root_dir}';
    </script>
EOF;
                    break;

                case 'web_ico':
                    /*支持子目录*/
                    $value = preg_replace('#^(/[/\w\-]+)?(/)#i', '$2', $value); // 支持子目录
                    // 避免路径重复
                    if (substr($this->root_dir, -1) === '/' && substr($value, 0, 1) === '/') {
                        $value = $this->root_dir . substr($value, 1);
                    } else {
                        $value = $this->root_dir . $value;
                    }
                    /*--end*/
                    break;

                default:
                    if (preg_match('/^web_attrname_(\d+)$/i', $name)) {
                        // 处理属性名称
                        $value = $this->getAttributeName($name);
                    } else {
                        /*支持子目录 - 只对图片或URL类型的配置项调用*/
                        $pic_configs = ['web_logo', 'web_ico', 'web_qrcode', 'web_bgimg'];
                        if (in_array($name, $pic_configs)) {
                            $value = handle_subdir_pic($value, 'html');
                            $value = handle_subdir_pic($value);
                        }
                        /*--end*/
                    }
                    break;
            }

            foreach ($param as $key => $val) {
                if ($key == 0) continue;
                // 支持函数调用
                if (function_exists($val)) {
                    $value = $val($value);
                }
            }
            
            $value = htmlspecialchars_decode($value);
        }

        return $value;
    }

    /**
     * 获取全局配置数据
     * @return array
     */
    protected function getGlobalData()
    {
        static $globalData = null;
        if (null === $globalData) {
            // 从数据库获取配置
            try {
                $row = Db::table('wa_config')
                    ->where('lang', 'zh-cn')
                    ->column('value', 'name');
                
                // 转换json格式的配置
                foreach ($row as $key => $val) {
                    if (is_string($val) && (0 === strpos($val, '{') || 0 === strpos($val, '['))) {
                        $jsonVal = json_decode($val, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $row[$key] = $jsonVal;
                        }
                    }
                }
                
                $globalData = $row;
            } catch (\Exception $e) {
                // 如果数据库获取失败，使用默认配置
                $globalData = [
                    'web_name' => '我的 Webman CMS',
                    'web_keywords' => 'webman, cms, eyoucms',
                    'web_description' => '这是一个基于 Webman 重构的 CMS 系统。',
                    'web_basehost' => 'http://' . request()->host(),
                    'web_cmsurl' => 'http://' . request()->host(),
                    'web_thirdcode_pc' => '',
                    'web_thirdcode_wap' => '',
                    'web_recordnum' => '',
                    'web_garecordnum' => '',
                    'web_templets_pc' => '/template/pc/',
                    'web_templets_m' => '/template/mobile/',
                    'web_attrname_1' => '默认属性',
                    'web_ico' => '/favicon.ico',
                    'seo_pseudo' => 1,
                    'seo_inlet' => 1,
                ];
            }
        }
        return $globalData;
    }

    /**
     * 多城市站点SEO逻辑
     * @param string $name
     * @param string $value
     * @param array $globalData
     * @return string
     */
    private function site_seo($name, $value = '', $globalData = [])
    {
        // 简化实现，确保不返回斜杠
        return $value !== '/' ? $value : '';
    }

    /**
     * 获取属性名称
     * @param string $name
     * @return string
     */
    protected function getAttributeName($name)
    {
        // 简化实现，直接返回空值
        return '';
    }
}