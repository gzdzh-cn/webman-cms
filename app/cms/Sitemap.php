<?php
namespace app\cms;

use app\cms\service\SitemapService;
use support\Response;

/** Public and administrative maps share configuration and original URL helpers. */
final class Sitemap
{
    public static function response(CmsContext $context, string $format = 'xml'): Response
    {
        return (new SitemapService())->live($context, $format);
    }
}
