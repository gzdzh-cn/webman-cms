<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use app\cms\CmsContext;
use support\Request;

final class LanguageAdminSecurity
{
    public static function token(Request $request): string
    {
        $token=$request->session()->get('cms_language_csrf');
        if(!is_string($token)||strlen($token)!==64){$token=bin2hex(random_bytes(32));$request->session()->set('cms_language_csrf',$token);}
        return $token;
    }
    public static function assertWrite(Request $request): void
    {
        if($request->method()!=='POST')throw new \InvalidArgumentException('请使用POST提交');
        $origin=rtrim((string)$request->header('origin',''),'/');
        if($origin===''||strcasecmp($origin,CmsContext::current()->origin())!==0)throw new \InvalidArgumentException('请从当前后台页面提交操作');
        $token=(string)$request->post('csrf',$request->header('x-cms-csrf',''));
        if($token===''||!hash_equals(self::token($request),$token))throw new \InvalidArgumentException('页面验证已失效，请刷新后重试');
    }
}
