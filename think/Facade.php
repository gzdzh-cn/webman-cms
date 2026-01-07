<?php
namespace think;

/**
 * A simple Facade base class to provide compatibility for EyouCMS taglibs.
 * It mimics the behavior of ThinkPHP's Facade by resolving an instance
 * and proxying static calls to it.
 */
abstract class Facade
{
    protected static $instances = [];

    /**
     * Get the actual class name for the facade.
     *
     * @return string
     */
    abstract protected static function getFacadeClass();

    /**
     * Handle dynamic static method calls into the facade.
     *
     * @param  string  $method
     * @param  array   $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        $class = static::getFacadeClass();
        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new $class();
        }
        $instance = static::$instances[$class];
        return $instance->$method(...$params);
    }
}
