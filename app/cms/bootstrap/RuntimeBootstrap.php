<?php
declare(strict_types=1);
namespace app\cms\bootstrap;
use app\cms\Paths;
use Webman\Bootstrap;
final class RuntimeBootstrap implements Bootstrap
{
    public static function start($worker):void
    {
        \app\cms\security\SessionStorageHandler::requireSerializer();
        foreach(['','logs'] as $relative)Paths::ensureDirectory(Paths::runtime($relative));
        if ($worker !== null && ($worker->name ?? '') === 'webman') {
            \Workerman\Timer::add(0.2, static function(): void {
                try { \app\cms\service\AdminUpdateService::acknowledge(); }
                catch (\Throwable) { /* The executor treats missing startup acknowledgement as failure. */ }
            }, [], false);
        }
        // Keep the public destination available even before the first generation job.
        Paths::ensureDirectory(Paths::publicHtml(), 0755);
    }
}
