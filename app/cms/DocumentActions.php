<?php
namespace app\cms;

use app\cms\compat\Cookie;
use app\cms\compat\MediaAccess;
use support\Request;
use support\Response;
use support\think\Db;

/** Original enterprise download/video HTTP contracts, using the CMS repository. */
final class DocumentActions
{
    public static function download(Request $request, CmsContext $context): Response
    {
        $action = strtolower((string)$request->input('a', 'downfile'));
        $aid = (int)$request->input('aid', 0);
        if ($action === 'custom_download_file') {
            $archive = self::archive($aid, $context);
            $field = mchStrCode((string)$request->input('field', ''), 'DECODE', $aid . get_auth_code('bcrypt', $context->lang));
            if (!$archive || !preg_match('/^[A-Za-z_]\w*$/', $field)) return self::error($request, '下载地址出错！');
            $definition = Db::name('channelfield')->where(['name' => $field, 'channel_id' => $archive['channel'], 'dtype' => 'file'])->find();
            $table = Db::name('channeltype')->where('id', $archive['channel'])->value('table');
            if (!$definition || !preg_match('/^[A-Za-z_]\w*$/', (string)$table)) return self::error($request, '下载地址出错！');
            $value = (string)Db::name($table . '_content')->where('aid', $aid)->value($field);
            [$url, $name] = array_pad(explode('|', $value, 2), 2, '');
            if ($url === '') return self::error($request, '下载地址出错！');
            return self::file($request, $url, $name);
        }
        if ($action === 'download_media_file') {
            $archive = self::archive($aid, $context);
            if (!$archive) return self::error($request, '附件文件不存在！');
            if (!empty($archive['restric_type'])) return self::error($request, '请先登录');
            $url = (string)Db::name('media_content')->where('aid', $aid)->value('courseware');
            return self::file($request, $url);
        }
        $fileId = (int)$request->input('file_id', $request->input('id', 0));
        $record = Db::name('download_file')->where('file_id', $fileId)->find();
        // Original attachment links identify the file across languages. Their
        // second navigation has no lang parameter and may use the home default.
        $archive = $record ? self::archive((int)$record['aid'], $context, false) : null;
        if (!$record || !$archive) return self::error($request, '下载文件不存在！');
        $hash = (string)$request->input('uhash', '');
        if ($action === 'download_file') {
            if ($hash === '' || !hash_equals((string)$record['uhash'], (string)mchStrCode($hash, 'DECODE'))) return self::error($request, '下载地址出错！');
            if (!$request->cookie($fileId . $hash)) return self::error($request, '下载地址已失效，请在下载详情页进行下载！');
            if ($context->device === 'mobile' && !$request->cookie($fileId . $hash . 'first')) Cookie::set($fileId . $hash . 'first', 1);
            else { Cookie::delete($fileId . $hash); Cookie::delete($fileId . $hash . 'first'); }
            return self::file($request, $record['file_url'], $record['file_name'], $record['file_mime']);
        }
        if ($hash === '' || !hash_equals((string)$record['uhash'], $hash)) return self::error($request, '下载地址出错！');
        if (!empty($archive['restric_type']) || !empty($archive['arc_level_id'])) return self::error($request, '请登录后下载！');
        $url = trim((string)$record['file_url']);
        if (preg_match('#^https?://#i', $url)) {
            if (!hash_equals((string)$record['uhash'], md5($url))) return self::error($request, '下载地址出错！');
            self::countDownload($record, $request);
            return self::success($request, '正在跳转中……', htmlspecialchars_decode($url), $record);
        }
        $file = AssetResolver::resolve($url);
        if (!$file) return self::error($request, '下载文件不存在！');
        if ($record['md5file'] !== '' && !hash_equals((string)$record['md5file'], md5_file($file))) return self::error($request, '下载文件包已损坏！');
        self::countDownload($record, $request);
        $encoded = mchStrCode($hash);
        Cookie::set($fileId . $encoded, 1);
        $url = ($context->global['web_cmspath'] ?? '') . '/index.php?' . http_build_query(['m' => 'home', 'c' => 'View', 'a' => 'download_file', 'file_id' => $fileId, 'uhash' => $encoded]);
        return self::success($request, '开始下载中……', $url);
    }

    public static function video(Request $request, CmsContext $context): Response
    {
        $action = strtolower((string)$request->input('a', 'pay_video_url'));
        $aid = (int)$request->input('aid', 0);
        $id = (int)$request->input('id', $request->input('fid', 0));
        $record = Db::name('media_file')->where('file_id', $id)->find();
        $bound = $record && (!$aid || (int)$record['aid'] === $aid);
        $archive = $bound ? self::archive((int)$record['aid'], $context) : null;
        if ($action === 'check_auth') {
            if (!$archive || trim((string)$record['file_url']) === '') return json(['status' => 0, 'msg' => '视频不存在', 'is_mobile' => $context->device === 'mobile' ? 1 : 0]);
            $allowed = MediaAccess::allowsGuest($archive, $record);
            return json(['status' => $allowed ? 2 : 0, 'msg' => $allowed ? 'success!' : '请先登录', 'is_mobile' => $context->device === 'mobile' ? 1 : 0]);
        }
        if (!$bound) return self::error($request, '视频播放链接出错！');
        if (!$archive) return self::error($request, '视频文件不存在！');
        if (!MediaAccess::allowsGuest($archive, $record)) return self::error($request, '请先登录');
        if ($action === 'pay_video_url' && !hash_equals((string)$record['uhash'], (string)$request->input('uhash', ''))) return self::error($request, '视频播放链接出错！');
        $url = trim((string)$record['file_url']);
        if (preg_match('/^txy_video_([A-Za-z0-9_-]+)$/D', $url, $cloud)) {
            if (!self::ajax($request)) return self::error($request, '腾讯云点播视频不支持跳转播放');
            self::countVideo($record, $request);
            $element = 'eyoucms-video-id-' . time();
            $videoId = json_encode($cloud[1]);
            $html = '<video id="' . $element . '" preload="auto" width="600" height="400" playsinline webkit-playsinline x5-playsinline></video>'
                . '<script type="text/javascript">var txy_video_id=' . $videoId . ';var app_id=$("#appID").val();TxyVideo();function TxyVideo(){var player=TCPlayer("' . $element . '",{fileID:txy_video_id,appID:app_id});}</script>';
            // Retain the original HTML contract; the scoped player bridge uses
            // structured metadata without evaluating returned script markup.
            return self::success($request, '准备播放中……', '', ['txy_video_html' => $html, 'txy_video_id' => $cloud[1]]);
        }
        if (!preg_match('#^https?://#i', $url)) {
            $file = AssetResolver::resolve($url);
            if (!$file) return self::error($request, '视频文件不存在！');
            if ($record['md5file'] !== '' && !hash_equals((string)$record['md5file'], md5_file($file))) return self::error($request, '视频文件已损坏！');
            $path = '/' . ltrim($url, '/');
            $basePath = (string)($context->global['web_cmspath'] ?? '');
            if ($basePath !== '' && !str_starts_with($path, $basePath . '/')) $path = $basePath . $path;
            $url = $context->origin() . $path;
        }
        self::countVideo($record, $request);
        return self::success($request, '准备播放中……', $url);
    }

    private static function countVideo(array $record, Request $request): void
    {
        Db::transaction(function () use ($record, $request) {
            Db::name('media_log')->insert(['users_id' => 0, 'aid' => $record['aid'], 'file_id' => $record['file_id'], 'ip' => $request->getRealIp(), 'add_time' => time()]);
            Db::name('media_file')->where('file_id', $record['file_id'])->inc('playcount')->update();
            Db::name('archives')->where('aid', $record['aid'])->inc('downcount')->update();
        });
    }

    private static function archive(int $aid, CmsContext $context, bool $sameLanguage = true): ?array
    {
        $query = Db::name('archives')->where(['aid' => $aid, 'is_del' => 0, 'status' => 1]);
        if ($sameLanguage) $query->where('lang', $context->lang);
        return $query->where('arcrank', '>', -1)->find() ?: null;
    }
    private static function countDownload(array $record, Request $request): void
    {
        Db::transaction(function () use ($record, $request) {
            Db::name('download_log')->insert(['users_id' => 0, 'file_id' => $record['file_id'], 'aid' => $record['aid'], 'ip' => $request->getRealIp(), 'add_time' => time()]);
            Db::name('download_file')->where('file_id', $record['file_id'])->inc('downcount')->update();
            Db::name('archives')->where('aid', $record['aid'])->inc('downcount')->update();
        });
    }
    private static function file(Request $request, string $url, string $name = '', string $mime = ''): Response
    {
        if (preg_match('#^https?://#i', $url)) return redirect(htmlspecialchars_decode($url));
        $file = AssetResolver::resolve($url);
        if (!$file) return self::error($request, '下载文件不存在！');
        $downloadName = basename(str_replace('\\', '/', $name ?: basename($file)));
        $fallbackName = preg_replace('/[^\x20-\x7E]|["\\\\]/', '_', $downloadName);
        $response = FileResponse::make($request, $file)->header('Content-Disposition', 'attachment; filename="' . $fallbackName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
        if ($mime !== '') $response->header('Content-Type', $mime);
        return $response;
    }
    private static function ajax(Request $request): bool { return strtolower((string)$request->header('x-requested-with')) === 'xmlhttprequest' || (bool)$request->input('_ajax', false); }
    private static function success(Request $request, string $message, string $url = '', array $data = []): Response
    {
        return self::ajax($request) ? json(['code' => 1, 'msg' => $message, 'url' => $url, 'data' => $data, 'wait' => 3], JSON_UNESCAPED_UNICODE) : redirect($url);
    }
    private static function error(Request $request, string $message): Response
    {
        return self::ajax($request) ? json(['code' => 0, 'msg' => $message, 'url' => '', 'data' => [], 'wait' => 3], JSON_UNESCAPED_UNICODE) : response('<!doctype html><meta charset="utf-8"><title>提示信息</title><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>');
    }
}
