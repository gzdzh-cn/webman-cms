<?php

namespace think\template\taglib\engine;

use support\Db;
use support\Log;
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
        
        // 从数据库获取对应配置值
        if (array_key_exists($name, $globalData)) {
            $value = $globalData[$name];

            switch ($name) {
                case 'web_title':
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
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https" : "http";
                        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                        $value = $scheme . '://' . $host . '/index.php';
                        if (!empty($filteredParam)) {
                            $value .= '?' . http_build_query($filteredParam);
                        }
                    } else {
                        // 基础URL构建，与EyouCMS保持一致
                        if (empty($globalData['web_basehost'])) {
                            $value = !empty($this->root_dir) ? $this->root_dir : '/';
                        } else {
                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https" : "http";
                            $host = preg_replace('/^(([^\/]*)\/\/)?([^\/]+)(.*)$/i', '${3}', $globalData['web_basehost']);
                            $value = $scheme . '://' . $host . $this->root_dir;
                        }

                        // SEO伪静态和入口设置处理
                        $seo_pseudo = !empty($globalData['seo_pseudo']) ? $globalData['seo_pseudo'] : 0;
                        $separate_mobile = !empty($globalData['separate_mobile']) ? $globalData['separate_mobile'] : 0;
                        if (1 == $seo_pseudo || 1 == $separate_mobile) {
                            if (!empty($filteredParam)) {
                                /*是否隐藏小尾巴 index.php*/
                                $seo_inlet = !empty($globalData['seo_inlet']) ? $globalData['seo_inlet'] : 0;
                                if (0 == intval($seo_inlet) || 2 == $seo_pseudo) {
                                    $value .= '/index.php';
                                } else {
                                    $value .= '/';
                                }
                                /*--end*/
                                if (!stristr($value, '?')) {
                                    $value .= '?';
                                } else {
                                    $value .= '&';
                                }
                                $value .= http_build_query($filteredParam);
                            }
                        }

                        // 域名特殊处理
                        if (stristr($host, '.yiyocms.com')) {
                            $value = preg_replace('/^(http:|https:)/i', '', $value);
                        }
                    }
                    break;

                case 'web_root_dir':
                case 'web_cmspath':
                    // 与EyouCMS保持一致，返回网站根目录路径
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
                $rows = Db::table('config')
                    ->where('lang', 'cn')
                    ->get(['name', 'value']);
                
                // 构建配置数组
                $row = [];
                foreach ($rows as $item) {
                    $row[$item->name] = $item->value;
                }
                
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
                // 如果数据库获取失败，记录日志
                Log::error('获取全局配置失败: ' . $e->getMessage());
                $globalData = [];
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
        // 从数据库获取多城市站点信息
        try {
            // 检查是否开启多城市站点
            $city_switch_on = Db::table('config')
                ->where('name', 'city_switch_on')
                ->where('lang', 'cn')
                ->first(['value']);
            $city_switch_on = $city_switch_on ? $city_switch_on->value : 0;
            
            if (empty($city_switch_on) || $city_switch_on != 1) {
                // 未开启多城市站点，直接返回
                return $value;
            }
            
            // 获取当前站点信息
            $site_info = [];
            $home_site = request()->get('site', '');
            if (!empty($home_site)) {
                $site_info = Db::table('citysite')
                    ->where('domain', $home_site)
                    ->find();
            }
            
            if (empty($site_info)) {
                // 没有站点信息，直接返回
                return $value;
            }
            
            // 处理SEO
            $seoset = !empty($site_info['seoset']) ? intval($site_info['seoset']) : 0;
            if (empty($seoset)) { // 当前分站启用分站的SEO
                if (!empty($globalData['site_seoset'])) { // 启用分站SEO
                    if ('web_title' == $name) {
                        $value = !empty($globalData['site_seo_title']) ? $globalData['site_seo_title'] : '';
                    } else if ('web_keywords' == $name) {
                        $value = !empty($globalData['site_seo_keywords']) ? $globalData['site_seo_keywords'] : '';
                    } else if ('web_description' == $name) {
                        $value = !empty($globalData['site_seo_description']) ? $globalData['site_seo_description'] : '';
                    }
                }
            } else if (1 == $seoset) { // 当前分站启用自定义SEO
                if ('web_title' == $name) {
                    $value = $site_info['seo_title'];
                } else if ('web_keywords' == $name) {
                    $value = $site_info['seo_keywords'];
                } else if ('web_description' == $name) {
                    $value = $site_info['seo_description'];
                }
            }
        } catch (\Exception $e) {
            // 如果数据库获取失败，直接返回原始值
        }
        
        return $value;
    }

    /**
     * 获取属性名称
     * @param string $name
     * @return string
     */
    protected function getAttributeName($name)
    {
        static $config_attribute = null;
        if (null === $config_attribute) {
            try {
                // 从数据库获取配置属性
                $row = Db::table('config_attribute')
                    ->where('lang', 'cn')
                    ->get(['attr_id', 'attr_name', 'attr_var_name']);
                
                // 构建配置属性数组
                $config_attribute = [];
                foreach ($row as $val) {
                    $attr_id = str_replace('web_attr_', '', $val['attr_var_name']);
                    $config_attribute['web_attrname_' . $attr_id] = $val;
                }
            } catch (\Exception $e) {
                // 如果数据库获取失败，返回空数组
                $config_attribute = [];
            }
        }
        
        // 返回对应的属性名称
        return !empty($config_attribute[$name]) ? $config_attribute[$name]['attr_name'] : '';
    }
}