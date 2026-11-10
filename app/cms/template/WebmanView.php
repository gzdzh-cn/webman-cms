<?php
namespace app\cms\template;

use app\cms\CmsContext;

final class WebmanView
{
    public static function render(string $template, array $vars, ?string $app = null, ?string $plugin = null): string
    {
        return (new TemplateRenderer(CmsContext::current()))->render($template, $vars);
    }
}
