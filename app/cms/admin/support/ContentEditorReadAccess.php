<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use app\cms\repository\CmsRepository;
use app\cms\service\ContentAccessService;
use plugin\admin\api\Auth;
use support\Request;

/** Read only metadata needed by an authorized content editor, never field management. */
final class ContentEditorReadAccess
{
    public static function allows(Request $request): bool
    {
        if ($request->method() !== 'GET') return false;
        $controller = (string)$request->controller;
        $action = (string)$request->action;
        $fields = $controller === \plugin\admin\app\controller\ChannelfieldController::class && $action === 'select';
        $categories = $controller === \plugin\admin\app\controller\ChannelfieldController::class && $action === 'getArctypeList';
        $templates = $controller === \plugin\admin\app\controller\ArctypeController::class && $action === 'getTemplates';
        if (!$fields && !$categories && !$templates) return false;
        $allowed = $fields ? ['channel_id','typeid','limit','only_published','is_release','lang','_']
            : ($categories ? ['channeltype','channelId','format','limit','lang','_'] : ['channel_id','type','lang','_']);
        foreach ($request->get() as $key => $value) if (!in_array($key, $allowed, true) || !is_scalar($value)) return false;
        $channel = self::number($request->get($categories ? 'channeltype' : 'channel_id', $categories ? $request->get('channelId', 0) : 0), 0, 2147483647);
        if ($channel === null || (!$categories && $channel === 0)) return false;
        $editor = false;
        foreach (['insert','update', ...($channel === 6 ? ['singlePage'] : [])] as $permission) {
            $code = 0; $message = '';
            if (Auth::canAccess(\plugin\admin\app\controller\ArchiveController::class, $permission, $code, $message)) { $editor = true; break; }
        }
        if (!$editor) return false;
        $r = new CmsRepository();
        if ($channel > 0 && !$r->table('channeltype')->where(['id'=>$channel,'is_del'=>0])->find()) return false;
        if ($categories) return $request->get('format', 'tree') === 'tree' && self::number($request->get('limit', 2000), 1, 2000) !== null;
        if ($templates) return $request->get('type') === 'view';
        if (self::number($request->get('limit', 1000), 1, 2000) === null
            || !in_array((string)$request->get('only_published', $request->get('is_release', '')), ['1'], true)) return false;
        $typeId = self::number($request->get('typeid'), 1, 4294967295);
        if ($typeId === null) return false;
        $category = $r->table('arctype')->where(['id'=>$typeId,'is_del'=>0])->find();
        if (!$category || (int)$category['current_channel'] !== $channel || $category['lang'] !== LanguageAdminContext::state($request)['lang']) return false;
        try { (new ContentAccessService($r, admin() ?: []))->assertCategory($category); }
        catch (\InvalidArgumentException) { return false; }
        return true;
    }

    private static function number(mixed $value, int $minimum, int $maximum): ?int
    {
        if ((!is_int($value) && !is_string($value)) || !preg_match('/^\d{1,10}$/D', (string)$value)) return null;
        $number = (int)$value;
        return $number >= $minimum && $number <= $maximum ? $number : null;
    }
}
