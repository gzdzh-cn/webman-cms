<?php
/**
 * Helper functions
 */

/**
 * 生成栏目URL
 * @param string $url
 * @param array $param
 * @param bool $suffix
 * @param bool $domain
 * @param mixed $seo_pseudo
 * @param mixed $seo_pseudo_format
 * @return string
 */
function typeurl($url = '', $param = '', $suffix = true, $domain = false, $seo_pseudo = null, $seo_pseudo_format = null)
{
    // 简化实现，仅返回基本URL
    // 实际项目中应该使用更完整的实现
    if (is_array($param)) {
        $param = http_build_query($param);
    }
    if (!empty($param)) {
        $url .= '?' . $param;
    }
    return $url;
}