<?php
namespace app\cms\compat;
final class Cookie
{
    public static function get($name='',$default=null) {
        $context=Runtime::context();$pending=$context?->memo['response_cookies']??[];
        if($name==='') return array_replace(Request::instance()->cookie('',[])?:[],$pending);
        $name=($context?->getConfig('cookie.prefix','')??'').$name;
        $value=array_key_exists($name,$pending)?$pending[$name]:Request::instance()->cookie($name,$default);
        if(is_string($value)&&str_starts_with($value,'think:')) {
            $decoded=json_decode(substr($value,6),true);
            if(is_array($decoded)) {array_walk_recursive($decoded,static function(&$item){if(!empty($item)&&$item!==true)$item=urldecode((string)$item);});return $decoded;}
        }
        return $value;
    }
    public static function set($name,$value,$options=null) {
        $context=Runtime::context();if(!$context)return;
        if(is_numeric($options))$options=['expire'=>(int)$options];
        elseif(is_string($options))parse_str($options,$options);
        $options=array_replace((array)$context->getConfig('cookie',[]),(array)$options);
        $name=($options['prefix']??'').$name;
        if(is_array($value)) {array_walk_recursive($value,static function(&$item){if(!empty($item)&&$item!==true)$item=urlencode((string)$item);});$value='think:'.json_encode($value,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);}
        $context->memo['response_cookies'][$name]=$value;
        $context->memo['response_cookie_options'][$name]=$options;
    }
    public static function delete($name) {self::set($name,null);}
}
