<?php
namespace app\cms\service;

use app\cms\compat\Cache;
use app\cms\repository\CmsRepository;
use Webman\Http\Request;

/** Shared effects after a successful administrator operation has committed. */
class AdminOperationService
{
    public function invalidateCache(): void
    {
        Cache::clear();
    }

    public function scheduleMaps(Request $request): void
    {
        (new SitemapService())->scheduleAutomatic($request);
    }

    public function writeOperationLog(Request $request): void
    {
        try {
            (new CmsRepository())->table('admin_log')->insert([
                'admin_id' => (int)admin_id(),
                'log_info' => mb_substr(class_basename((string)$request->controller) . '/' . $request->action, 0, 255),
                'log_ip' => substr($request->getRealIp(), 0, 30),
                'log_url' => substr($request->path(), 0, 255),
                'log_time' => time(),
            ]);
        } catch (\Throwable $error) {
            try { \support\Log::warning('CMS operation log could not be saved'); } catch (\Throwable) {}
        }
    }
}
