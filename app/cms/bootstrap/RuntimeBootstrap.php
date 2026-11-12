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
        // Keep the public destination available even before the first generation job.
        Paths::ensureDirectory(Paths::publicHtml(), 0755);
    }
}
