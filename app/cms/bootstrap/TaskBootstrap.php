<?php
declare(strict_types=1);
namespace app\cms\bootstrap;

/** Background tasks deliberately have no HTTP middleware, routing or session bootstrap. */
final class TaskBootstrap
{
    public static function start($worker): void
    {
        require_once \app\cms\Paths::code('app/base.php');
        set_error_handler(static function ($level, $message, $file = '', $line = 0): void {
            if (error_reporting() & $level) throw new \ErrorException($message, 0, $level, $file, $line);
        });
        \Webman\Config::clear();
        \app\cms\secure\App::loadAllConfig(['route']);
        foreach (config('autoload.files', []) as $file) require_once $file;
        date_default_timezone_set((string)\app\cms\config\DeploymentConfig::get('app.timezone'));
        RuntimeBootstrap::start($worker);
        \Webman\ThinkOrm\ThinkOrm::start($worker);
        \support\SqlDebug::start($worker);
    }
}
