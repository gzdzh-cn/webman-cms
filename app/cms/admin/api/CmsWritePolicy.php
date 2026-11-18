<?php
declare(strict_types=1);
namespace app\cms\admin\api;

use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use support\Request;
use support\Response;

/** Explicit CMS write actions are shared by dedicated and content-save entrypoints. */
final class CmsWritePolicy
{
    /** The normal edit action may retain an old rank, but may not grant review access. */
    public static function assertArchiveReviewChange(int $before, mixed $after, array $roles): void
    {
        if ((!is_int($after)&&!is_string($after)) || !preg_match('/^-?[0-9]{1,10}$/D',(string)$after) || (int)$after>2147483647 || (int)$after < -2147483648) throw new \InvalidArgumentException('审核状态格式错误');
        if ((int)$after===$before) return;
        if (!self::archiveReviewAllowed($roles)) throw new \InvalidArgumentException('无文档审核权限；可保存原审核状态，退回稿请修改后重新提交');
    }

    public static function archiveReviewAllowed(array $roles): bool
    {
        if (!$roles) return false;
        $ids=[];
        foreach (Role::whereIn('id',$roles)->pluck('rules') as $rules) $ids=array_merge($ids,array_map('trim',explode(',',(string)$rules)));
        if (in_array('*',$ids,true)) return true;
        $controller=\plugin\admin\app\controller\ArchiveController::class;
        return Rule::whereIn('id',array_filter($ids,'ctype_digit'))->where(function($query)use($controller){$query->where('key',$controller.'@updateStatus')->orWhere('key',$controller);})->exists();
    }

    public static function allowed(string $controller,string $action,array $roles):bool
    {
        if(!$roles)return false;$ids=[];foreach(Role::whereIn('id',$roles)->pluck('rules') as $rules)$ids=array_merge($ids,array_map('trim',explode(',',(string)$rules)));if(in_array('*',$ids,true))return true;return Rule::whereIn('id',array_filter($ids,'ctype_digit'))->where('key',$controller.'@'.$action)->exists();
    }
    public static function authorize(Request $request,string $controller,string $action,array $roles):?Response
    {
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        if(!self::allowed($controller,$action,$roles))return json(['code'=>2,'msg'=>'无独立的产品参数操作权限','data'=>[]])->withStatus(403);
        $origin=rtrim((string)$request->header('origin',''),'/');if($origin===''||strcasecmp($origin,\app\cms\CmsContext::current()->origin())!==0)return json(['code'=>1,'msg'=>'请从当前后台页面提交操作','data'=>[]])->withStatus(403);return null;
    }
}
