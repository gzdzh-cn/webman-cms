<?php
declare(strict_types=1);
namespace plugin\admin\api;

use support\Request;
use support\Response;

/** Original final API identity; all policy decisions live in app/cms. */
final class CmsWritePolicy
{
    public static function allowed(string $controller, string $action, array $roles): bool
    {
        return \app\cms\admin\api\CmsWritePolicy::allowed($controller, $action, $roles);
    }

    public static function authorize(Request $request, string $controller, string $action, array $roles): ?Response
    {
        return \app\cms\admin\api\CmsWritePolicy::authorize($request, $controller, $action, $roles);
    }
}
