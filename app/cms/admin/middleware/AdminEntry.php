<?php
declare(strict_types=1);
namespace app\cms\admin\middleware;

use app\cms\admin\support\AdminRoutes;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/** Establish the original, nonempty permission identity before any authorization. */
final class AdminEntry implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $identity = AdminRoutes::identity((string)$request->controller, (string)$request->action);
        if ($identity === null) return response($request->uri() . ' not found', 404);
        $request->controller = $identity;
        $request->plugin = '';
        return (new AccessControl())->process($request, $handler);
    }
}
