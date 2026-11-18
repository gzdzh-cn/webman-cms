<?php
namespace app\cms;

/** Retain upstream URL formats with a request-scoped route registry. */
final class UrlGenerator
{
    public static function build($route = '', $parameters = [], $suffix = true, $domain = false, $mode = null, $format = null, $inlet = null): string
    {
        $context = CmsContext::current();
        $context->memo += ['routing.root' => null, 'routing.bind' => null];
        return routing\Url::build($route, $parameters, $suffix, $domain, $mode, $format, $inlet);
    }
}
