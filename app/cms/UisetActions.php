<?php
namespace app\cms;

use support\Request;
use support\Response;

/** Compatibility endpoint for the disabled visual editor. */
final class UisetActions
{
    public static function handle(Request $request, CmsContext $context): Response
    {
        return response('可视化编辑功能已关闭', 404);
    }
}
