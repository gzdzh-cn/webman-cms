<?php
declare(strict_types=1);
namespace app\cms\support;

/** A binary may have no 'php' substring or mutable title in /proc/cmdline. */
class Worker extends \Workerman\Worker
{
    protected static function monitorWorkersForLinux(): void
    {
        if (PHP_OS_FAMILY === 'Linux') {
            if (\app\cms\config\DeploymentConfig::get('performance.separate_task_processes')) {
                MemoryWatchdog::publishMasterState(true);
            } else {
                $limit = MemoryWatchdog::limit((string)\app\cms\config\DeploymentConfig::get('app.memory_limit'));
                \Workerman\Timer::add(60, static function () use ($limit): void {
                    if (static::$status !== static::STATUS_RUNNING || \app\process\Monitor::isPaused()) return;
                    $pids = [];
                    foreach (static::$pidMap as $children) foreach ($children as $pid => $_) $pids[] = (int)$pid;
                    MemoryWatchdog::inspect($pids, getmypid(), $limit);
                });
            }
        }
        parent::monitorWorkersForLinux();
    }

    protected static function reload(): void
    {
        $publish = static::hasIndependentMonitor();
        if ($publish) MemoryWatchdog::publishMasterState(false);
        try {
            parent::reload();
        } finally {
            if ($publish) MemoryWatchdog::publishMasterState(static::$status === static::STATUS_RUNNING);
        }
    }

    public static function stopAll(int $code = 0, mixed $log = ''): void
    {
        if (static::hasIndependentMonitor()) MemoryWatchdog::publishMasterState(false);
        parent::stopAll($code, $log);
    }

    private static function hasIndependentMonitor(): bool
    {
        return PHP_OS_FAMILY === 'Linux' && static::$masterPid === getmypid()
            && \app\cms\config\DeploymentConfig::get('performance.separate_task_processes');
    }

    protected static function checkMasterIsAlive(int $masterPid): bool
    {
        if($masterPid<2||$masterPid===getmypid()||!posix_kill($masterPid,0))return false;
        try{return ProcessLauncher::master()===$masterPid;}catch(\Throwable){return false;}
    }
}
