<?php
namespace app\cms;

use app\cms\compat\Lang;
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
        $escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $url = (string)$result['url'];
        $back = !preg_match('#^(?:/(?!/)|https?://)#i', $url) || preg_match('/[\x00-\x20\x7f\\\\]/', $url);
        $url = $back ? '#' : $url;
        $chinese = in_array($context->lang, ['cn', 'zh'], true);
        $jump = Lang::get('gbook12', [], $context->lang) ?: ($chinese ? '页面自动%s跳转%s，等待时间：' : 'Redirecting %snow%s. Please wait: ');
        $link = '<a id="href" href="' . $escape($url) . '">' ;
        try { $jump = sprintf($escape($jump), $link, '</a>'); }
        catch (\ValueError | \ArgumentCountError) { $jump = ''; }
        if (!str_contains($jump, 'id="href"')) $jump = $link . ($chinese ? '返回' : 'Continue') . '</a>';
        $root = (string)($context->global['web_cmspath'] ?? '');
        $view = [
            'lang' => $escape($context->lang), 'title' => $chinese ? '跳转提示' : 'Redirect',
            'success' => (int)$result['code'] === 1,
            'message' => $escape(strip_tags(str_replace('__html__', '', (string)$result['msg']))),
            'jump' => $jump, 'wait' => max(1, min(86400, (int)$result['wait'])),
            'script' => json_encode(['back' => (bool)$back, 'url' => $url, 'data' => (string)$result['data'],
                'email' => $root . '/index.php?m=api&c=Ajax&a=send_email&lang=' . rawurlencode($context->lang),
                'sms' => $root . '/index.php?m=api&c=Ajax&a=SendMobileCode&lang=' . rawurlencode($context->lang)],
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR),
        ];
        // This fixed application view is trusted code, independent of either theme dialect.
        ob_start();
        try {
            (static function (array $view): void { include Paths::code('app/view/cms/system/dispatch_jump.php'); })($view);
            $html = (string)ob_get_clean();
        } catch (\Throwable $error) { ob_end_clean(); throw $error; }
        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8', 'Cache-Control' => 'no-store']);
    }
}
