<?php

namespace app\cms\compat\paginator;
use function app\cms\compat\config;
use function app\cms\compat\input;
use function app\cms\compat\request;
use function app\cms\compat\cache;
use function app\cms\compat\cookie;
use function app\cms\compat\session;
use function app\cms\compat\model;
use function app\cms\compat\M;
use function app\cms\compat\lang;
use app\cms\compat\Runtime;


use Exception;
use app\cms\compat\Paginator;

/**
 * Class Collection
 * @package app\cms\compat\paginator
 * @method integer total()
 * @method integer listRows()
 * @method integer currentPage()
 * @method string render()
 * @method Paginator fragment($fragment)
 * @method Paginator appends($key, $value)
 * @method integer lastPage()
 * @method boolean hasPages()
 */
class Collection extends \app\cms\compat\Collection
{

    /** @var Paginator */
    protected $paginator;

    public function __construct($items = [], ?Paginator $paginator = null)
    {
        $this->paginator = $paginator;
        parent::__construct($items);
    }

    public static function make($items = [], ?Paginator $paginator = null)
    {
        return new static($items, $paginator);
    }
    
    public function toArray()
    {
        if ($this->paginator) {
            try {
                $total = $this->total();
            } catch (Exception $e) {
                $total = null;
            }

            return [
                'total'        => $total,
                'per_page'     => $this->listRows(),
                'current_page' => $this->currentPage(),
                'data'         => parent::toArray()
            ];
        } else {
            return parent::toArray();
        }
    }

    public function __call($method, $args)
    {
        if ($this->paginator && method_exists($this->paginator, $method)) {
            return call_user_func_array([$this->paginator, $method], $args);
        } else {
            throw new Exception('method not exists:' . __CLASS__ . '->' . $method);
        }
    }
}
