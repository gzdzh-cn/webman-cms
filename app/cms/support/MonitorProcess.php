<?php
declare(strict_types=1);
namespace app\cms\support;

/** Monitoring needs neither HTTP/session bootstrap nor task ORM connections. */
final class MonitorProcess
{
    public static function start(array $config): void
    {
        if (empty($config['enable'])) return;
        $worker = new \Workerman\Worker();
        $worker->name = 'monitor';
        $worker->count = 1;
        $worker->reloadable = false;
        $worker->onWorkerStart = static function () use ($config): void {
            new \app\process\Monitor(...$config['constructor']);
        };
    }
}
