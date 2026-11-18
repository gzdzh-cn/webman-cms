<?php
/**
 * Here is your custom functions.
 */

// 定义MyDate函数，用于模板中的日期格式化
if (!function_exists('MyDate')) {
    function MyDate($format, $timestamp) {
        return date($format, $timestamp);
    }
}

// Keep Webman globals intact and use request-scoped Eyou compatibility helpers.
require_once __DIR__ . '/cms/compat/functions.php';
require_once __DIR__ . '/cms/support/helpers.php';
