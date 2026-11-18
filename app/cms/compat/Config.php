<?php
namespace app\cms\compat;
final class Config
{
    public static function get($name='',$default=null) { return Runtime::context()?->getConfig($name,$default); }
    public static function set($name,$value=null) { Runtime::context()?->setConfig($name,$value); }
    public static function has($name): bool {return self::get($name)!==null;}
}
