<?php
namespace app\cms\admin\http;
final class NotFoundController
{
    public function index(\support\Request $request): \support\Response
    {
        return response($request->uri() . ' not found', 404);
    }
}
