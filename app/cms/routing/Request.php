<?php
namespace app\cms\routing;

use app\cms\CmsContext;

/** Resolve the current request on every call, including root-directory deployments. */
final class Request
{
    public static function instance(): self { return new self(); }
    public function __call(string $name, array $arguments) { return \app\cms\compat\Request::instance()->$name(...$arguments); }
    public function baseFile($domain = false): string
    {
        return ($domain ? CmsContext::current()->origin() : '') . (CmsContext::current()->global['web_cmspath'] ?? '') . '/index.php';
    }
    public function root($value = false): string
    {
        if (is_string($value)) CmsContext::current()->memo['routing.request_root'] = rtrim($value, '/');
        return ($value === true ? CmsContext::current()->origin() : '') . (CmsContext::current()->memo['routing.request_root'] ?? (CmsContext::current()->global['web_cmspath'] ?? ''));
    }
}
