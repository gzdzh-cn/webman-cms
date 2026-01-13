<?php

/**
 * 定义常量
 * 基础常量已在support/bootstrap.php中通过app/base.php加载
 * 为了兼容直接引用common.php的情况，添加了常量检查
 */
if (!defined('UPLOAD_PATH')) {
    require_once __DIR__ . '/base.php';
}

/**
 * 获取配置（简化版）
 * @param string $name 配置名称
 * @return mixed
 */
function tpCache($name = '')
{
    // 简化版：返回默认配置
    $configs = [
        'web' => [
            'absolute_path_open' => false
        ],
        'thumb' => [
            'thumb_open' => true,
            'thumb_width' => 200,
            'thumb_height' => 150,
            'thumb_mode' => 6,
            'thumb_color' => '#ffffff'
        ]
    ];
    
    $parts = explode('.', $name);
    $value = $configs;
    foreach ($parts as $part) {
        if (!isset($value[$part])) {
            return null;
        }
        $value = $value[$part];
    }
    
    return $value;
}


/**
 * 判断是否为http url
 * @param string $url
 * @return bool
 */
function is_http_url($url)
{
    if (empty($url)) {
        return false;
    }
    return (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '//') === 0);
}

/**
 * 处理子目录图片
 * @param string $pic_url
 * @param string $type
 * @param bool $domain
 * @return string
 */
function handle_subdir_pic($pic_url, $type = 'img', $domain = false)
{
    if (empty($pic_url) || $pic_url == '/') {
        return '';
    }
    
    // 如果是http url，则直接返回
    if (is_http_url($pic_url)) {
        return $pic_url;
    }
    
    // 去除开头的/
    $pic_url = ltrim($pic_url, '/');
    
    // 返回处理后的路径
    return '/' . $pic_url;
}

/**
 * 判断是否为本地图片
 * @param string $pic_url
 * @return bool
 */
function is_local_images($pic_url)
{
    if (empty($pic_url) || $pic_url == '/') {
        return false;
    }
    
    // 如果是http url，则不是本地图片
    if (is_http_url($pic_url)) {
        return false;
    }
    
    return true;
}

/**
 * 获取绝对URL
 * @param string $path
 * @return string
 */
function get_absolute_url($path)
{
    if (empty($path)) {
        return '';
    }
    
    // 如果已经是http url，则直接返回
    if (is_http_url($path)) {
        return $path;
    }
    
    // 去除开头的/
    $path = ltrim($path, '/');
    
    // 返回绝对URL
    return '/' . $path;
}


/**
 * 获取默认图片
 * @param string $pic_url
 * @return string
 */
function get_default_pic($pic_url = '')
{
    if (empty($pic_url) || $pic_url == '/') {
        // 返回默认图片
        return '/static/images/default_pic.jpg';
    }
    return $pic_url;
}

/**
 * 缩略图处理函数
 * @param string $original_img 原始图片路径
 * @param string $width 宽度
 * @param string $height 高度
 * @param string $thumb_mode 缩略图模式
 * @return string
 */
function thumb_img($original_img = '', $width = '', $height = '', $thumb_mode = '')
{
    // 简化版：直接返回原图
    return $original_img;
}

/**
 * 获取文章详情页URL
 * @param array $arcview_info 文章信息
 * @return string 文章详情页URL
 */
function arcurl($arcview_info = array())
{
    // 简化版：返回动态URL格式
    $aid = $arcview_info['aid'] ?? 0;
    $channel = $arcview_info['channel'] ?? 1;
    
    // 根据模型类型确定控制器名称
    $ctl_name = '';
    switch ($channel) {
        case 1: // 文章模型
            $ctl_name = 'Article';
            break;
        case 2: // 图片模型
            $ctl_name = 'Images';
            break;
        case 3: // 下载模型
            $ctl_name = 'Download';
            break;
        case 4: // 产品模型
            $ctl_name = 'Product';
            break;
        case 5: // 视频模型
            $ctl_name = 'Media';
            break;
        default: // 默认使用View控制器
            $ctl_name = 'View';
            break;
    }
    
    // 构建URL
    if ($ctl_name == 'View') {
        return "/index.php?m=home&c=View&a=index&aid={$aid}";
    } else {
        return "/index.php?m=home&c={$ctl_name}&a=view&aid={$aid}";
    }
}

/**
 * 生成栏目URL
 * @param string $route 路由规则，如'home/Article/lists'
 * @param array $params 路由参数
 * @return string 栏目URL
 */
function typeurl($route, $params = array())
{
    // 简化版：返回动态URL格式
    $typeid = $params['typeid'] ?? $params['id'] ?? 0;
    $dir_id = $params['dir_id'] ?? 0;
    
    // 解析路由规则
    $route_parts = explode('/', $route);
    $ctl_name = $route_parts[1] ?? 'Article';
    $act_name = $route_parts[2] ?? 'lists';
    
    // 构建URL
    $url = "/index.php?m=home&c={$ctl_name}&a={$act_name}";
    if ($typeid > 0) {
        $url .= "&tid={$typeid}";
    }
    if ($dir_id > 0) {
        $url .= "&dir_id={$dir_id}";
    }
    
    return $url;
}