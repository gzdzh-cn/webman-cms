<?php
declare(strict_types=1);
namespace app\cms\process;
use app\cms\service\StaticGenerationService;
use app\cms\service\SitemapService;
use Workerman\Timer;

/** Durable bounded jobs continue without keeping an administrator's browser open. */
 class StaticGenerationWorker
{
    public function onWorkerStart(): void
    {
        if (!(new \app\cms\install\InstallerService())->status()['installed']) return;
        $database = \app\cms\database\DatabaseSettings::values();
        if ($database['DB_HOST'] === '' || $database['DB_NAME'] === '') return;
        // A new installation can start its guide without any CMS tables.
        try { if (!\app\cms\persistence\Schema::exists(\app\cms\persistence\Connection::open(), \app\cms\persistence\Connection::prefix())) return; }
        catch (\Throwable) { return; }
        Timer::add(2, function (): void {
            \app\cms\CmsContext::set(new \app\cms\CmsContext());
            \Webman\Context::set('cms.cache.policy', null);
            \Webman\Context::set('cms.cache.generation', null);
            \Webman\Context::set('cms.cache.memory', null);
            \Webman\Context::set('cms.cache.redis.failed', null);
            \Webman\Context::set('cms.cache.redis.invalidated', null);
            try { if (\app\cms\maintenance\DatabaseBackup::maintenance()) return; if (!(new \app\cms\install\InstallerService())->status()['installed']) return; \app\cms\maintenance\DatabaseTraffic::read(function(){(new StaticGenerationService())->advancePending(3);(new SitemapService())->advanceAutomatic();},static fn()=>null); }
            catch (\Throwable $error) { try { \support\Log::warning('CMS generation worker: ' . $error->getMessage()); } catch (\Throwable) {} }
        });
    }
}
