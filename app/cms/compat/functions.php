<?php
namespace app\cms\compat;

function config($name = '', $value = null, $range = '') { return Runtime::config($name,$value,$range); }
function input($name = '', $default = '', $filter = '') { return Runtime::input($name,$default,$filter); }
function request() { return Runtime::request(); }
function model($name = '', ...$args) { return Runtime::model($name); }
function M($name = '', ...$args) { return Db::name($name); }
function db($name = '', $config = [], $force = false) { return Db::connect($config, $force)->name($name); }
function cache($name, $value = '', $options = null, $tag = null) {
    if ($value === '') return Cache::get($name);
    if ($value === null) return Cache::rm($name);
    return Cache::set($name,$value,$options);
}
function cookie($name = '', $value = '', $option = null) {
    if ($value==='') return Cookie::get($name);
    return $value===null ? Cookie::delete($name) : Cookie::set($name,$value,$option);
}
function session($name = '', $value = '', $prefix = null) {
    if (is_array($name)) return Session::init($name);
    if ($name===null) return Session::clear($value==='' ? null : $value);
    if ($value==='') return str_starts_with((string)$name,'?') ? Session::has(substr($name,1),$prefix) : Session::get($name,$prefix);
    return $value===null ? Session::delete($name,$prefix) : Session::set($name,$value,$prefix);
}
function lang($name, $vars = [], $lang = '') { return function_exists('lang') ? \lang($name,$vars,$lang) : Lang::get($name,$vars,$lang); }
