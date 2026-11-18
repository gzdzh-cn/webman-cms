<?php
namespace app\cms\compat;

/** An expression-local parent container; only weak references enter request memo. */
final class OffsetTarget
{
    public $container;
    public $key;

    public function __construct(&$container, $key)
    {
        $this->container =& $container;
        $this->key = is_string($container) ? (is_object($key) ? 1 : (int) $key) : $key;
    }
}
