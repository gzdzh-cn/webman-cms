<?php
declare(strict_types=1);
namespace app\cms\process;

class BaiduPushWorker
{
    public function onWorkerStart(): void
    {
        \Workerman\Timer::add(15,function(): void {
            \Webman\Context::destroy();
            \app\cms\CmsContext::set(new \app\cms\CmsContext());
            try {
                if (\app\cms\maintenance\DatabaseBackup::maintenance() || !(new \app\cms\install\InstallerService())->status()['installed']) return;
                \app\cms\maintenance\DatabaseTraffic::read(static fn()=>(new \app\cms\service\BaiduPushScheduleService())->tick(),static fn()=>null);
            } catch (\Throwable) {
                // Never log exception text: transport/database errors may include private configuration.
                try { \support\Log::warning('CMS Baidu scheduler unavailable; check installation and database state'); } catch (\Throwable) {}
            } finally { \Webman\Context::destroy(); }
        });
    }
}
