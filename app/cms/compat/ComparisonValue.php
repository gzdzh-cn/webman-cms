<?php
namespace app\cms\compat;

/** One expression-lifetime value/reference; never retained in runtime context. */
final class ComparisonValue
{
    public function __construct(public mixed $value = null) {}
}
