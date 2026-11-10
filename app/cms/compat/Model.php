<?php
namespace app\cms\compat;

/** Legacy model methods use the same query bridge as enterprise tag services. */
#[\AllowDynamicProperties]
class Model
{
    protected $name = '';
    public function __construct($name='') { if ($name!=='') $this->name=$name; if ($this->name==='') $this->name=(new \ReflectionClass($this))->getShortName(); $this->initialize(); }
    protected function initialize() {}
    public function __call($method,$args) { return Db::name($this->name)->$method(...$args); }
}
