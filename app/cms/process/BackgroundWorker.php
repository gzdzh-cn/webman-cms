<?php
declare(strict_types=1);
namespace app\cms\process;

use app\cms\CmsContext;
use app\cms\service\{BaiduPushScheduleService,StaticGenerationService,SitemapService};
use Workerman\Timer;
use Webman\Context;

/** Both process modes share bounded services and durable state. */
class BackgroundWorker
{
    private ?int $timer = null;
    private bool $stopping = false;
    private TaskScheduler $scheduler;
    private int $observed;

    public function __construct(private string $role = 'all')
    {
        if (!in_array($role, ['all','push','generation'], true)) throw new \InvalidArgumentException('Unknown task role');
        $this->observed = intdiv(time(), 60) * 60 - 1;
    }
    public function onWorkerStart(): void
    {
        $push = function (): bool {
            $instant = time();
            $completed = false;
            $result = $this->unit(fn() => (new BaiduPushScheduleService())->tickOne($instant, $this->observed), $completed);
            if ($completed && !$result) $this->observed = $instant;
            return $result;
        };
        $this->scheduler = new TaskScheduler(
            $this->role === 'generation' ? null : $push,
            $this->role === 'push' ? null : fn() => $this->unit(fn() => (new StaticGenerationService())->advancePending(1)),
            $this->role === 'push' ? null : fn() => $this->unit(fn() => (new SitemapService())->advanceAutomatic())
        );
        $this->schedule(1);
    }
    private function schedule(float $delay): void
    {
        if ($this->stopping) return;
        $this->timer = Timer::add($delay, function (): void {
            $this->timer = null;
            if ($this->stopping) return;
            $busy = false;
            try { $busy = $this->scheduler->turn(); } catch (\Throwable) { $this->warning(); }
            $this->schedule($busy ? 0.01 : 1);
        }, [], false);
    }
    private function unit(\Closure $operation, ?bool &$completed = null): bool
    {
        Context::destroy();
        CmsContext::set(new CmsContext());
        $busy = false;
        try {
            if (\app\cms\maintenance\DatabaseBackup::maintenance() || !(new \app\cms\install\InstallerService())->status()['installed']) return false;
            $busy = (bool)\app\cms\maintenance\DatabaseTraffic::read(function () use ($operation, &$completed) {
                $result = \app\cms\maintenance\MaintenanceGate::shared($operation);
                $completed = true;
                return $result;
            }, static fn() => false);
            return $busy;
        } catch (\Throwable) {
            $this->warning(); return false;
        } finally {
            Context::destroy();
            if ($busy) { gc_collect_cycles(); gc_mem_caches(); }
        }
    }
    private function warning(): void
    {
        try { \support\Log::warning('CMS background task unavailable; check task receipts, installation and database state'); } catch (\Throwable) {}
    }
    public function onWorkerStop(): void
    {
        $this->stopping = true;
        if ($this->timer !== null) { Timer::del($this->timer); $this->timer = null; }
    }
}
