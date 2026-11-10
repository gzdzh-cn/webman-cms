<?php
namespace app\cms;

use app\cms\template\TemplateRenderer;
use support\Request;
use support\Response;

/** Preserve the original AJAX result and regular form's dispatch page. */
final class JumpResponse
{
    public static function make(Request $request, CmsContext $context, array $result): Response
    {
        $ajax = strtolower((string)$request->header('x-requested-with')) === 'xmlhttprequest' || (bool)$request->input('_ajax', false);
        $result += ['code' => 0, 'msg' => '', 'data' => '', 'url' => $ajax ? '' : 'javascript:history.back(-1);', 'wait' => 3, 'target' => '_self'];
        if ($ajax) return json($result, JSON_UNESCAPED_UNICODE);
        if (!$result['code'] && $result['url'] === '') $result['url'] = 'javascript:history.back(-1);';
        $root = $context->global['web_cmspath'] ?? '';
        $template = strtr(file_get_contents(Paths::root('app/view/cms/legacy-template/dispatch_jump.htm')), [
            '__STATIC__/admin/images/ico_right_wrong.png' => AssetManifest::url($root . '/public/static/admin/images/ico_right_wrong.png'),
            "__STATIC__/common/js/jquery.min.js?v={\$version|default='v1.6.4'}" => AssetManifest::url($root . '/public/static/common/js/jquery.min.js'),
            '__PUBLIC__/plugins/bootstrap/css/bootstrap.min.css' => AssetManifest::url($root . '/public/plugins/bootstrap/css/bootstrap.min.css'),
            '__STATIC__' => $root . '/public/static', '__PUBLIC__' => $root . '/public',
        ]);
        return response((new TemplateRenderer($context))->renderString($template, $result), 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
