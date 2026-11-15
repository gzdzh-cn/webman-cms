<?php
declare(strict_types=1);
namespace app\cms\support;

/** CMS-only variant of Webman's worker_start; preserves its binding lifecycle. */
final class TaskProcess
{
    public static function start(string $name, array $config): void
    {
        if (isset($config['enable']) && !$config['enable']) return;
        $worker = new \Workerman\Worker();
        $worker->name = $name;
        foreach (['count','user','group','reloadable','eventLoop'] as $key) if (isset($config[$key])) $worker->$key = $config[$key];
        $worker->onWorkerStart = static function ($worker) use ($config): void {
            \app\cms\bootstrap\TaskBootstrap::start($worker);
            $instance = \support\Container::make($config['handler'], $config['constructor'] ?? []);
            worker_bind($worker, $instance);
        };
    }
}
