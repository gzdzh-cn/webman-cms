<?php
/**
 * Startup adapted from workerman/webman-framework support\App (MIT).
 * Original author/copyright: walkor <walkor@workerman.net>.
 * License: the project's LICENSE (MIT).
 */
namespace app\cms\secure;

use Dotenv\Dotenv;
use RuntimeException;
use support\Log;
use support\Request;
use Workerman\Connection\TcpConnection;
use app\cms\support\Worker;

/** Application startup and Composer configuration, without business plugin discovery. */
class App extends \support\App
{
    /** Keep Webman's startup lifecycle without creating its unused views directory. */
    public static function run()
    {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);

        \app\cms\config\DeploymentConfig::initialize();
        ini_set('memory_limit', \app\cms\config\DeploymentConfig::get('app.memory_limit'));
        ini_set('display_errors', \app\cms\config\DeploymentConfig::get('app.debug') ? '1' : '0');

        if (!$appConfigFile = config_path('app.php')) {
            throw new RuntimeException('Config file not found: app.php');
        }
        $appConfig = require $appConfigFile;
        if ($timezone = $appConfig['default_timezone'] ?? '') {
            date_default_timezone_set($timezone);
        }

        static::loadAllConfig(['route', 'container']);

        if (!is_phar() && DIRECTORY_SEPARATOR === '\\' && empty(config('server.listen'))) {
            echo "Please run 'php windows.php' on windows system." . PHP_EOL;
            exit;
        }

        $errorReporting = config('app.error_reporting');
        if (isset($errorReporting)) {
            error_reporting($errorReporting);
        }

        $runtimeLogsPath = runtime_path() . DIRECTORY_SEPARATOR . 'logs';
        if (!file_exists($runtimeLogsPath) || !is_dir($runtimeLogsPath)) {
            if (!@mkdir($runtimeLogsPath, 0750, true) && !is_dir($runtimeLogsPath)) {
                throw new RuntimeException("Failed to create runtime logs directory. Please check the permission.");
            }
        }

        Worker::$onMasterReload = function () {
            if (function_exists('opcache_get_status')) {
                if ($status = opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            opcache_invalidate($file, true);
                        }
                    }
                }
            }
        };

        $config = config('server');
        Worker::$pidFile = $config['pid_file'];
        Worker::$stdoutFile = $config['stdout_file'];
        Worker::$logFile = $config['log_file'];
        Worker::$eventLoopClass = $config['event_loop'] ?? '';
        TcpConnection::$defaultMaxPackageSize = $config['max_package_size'] ?? 10 * 1024 * 1024;
        if (property_exists(Worker::class, 'statusFile')) {
            Worker::$statusFile = $config['status_file'] ?? '';
        }
        if (property_exists(Worker::class, 'stopTimeout')) {
            Worker::$stopTimeout = $config['stop_timeout'] ?? 2;
        }

        if ($config['listen'] ?? false) {
            $worker = new Worker($config['listen'], $config['context']);
            $propertyMap = [
                'name',
                'count',
                'user',
                'group',
                'reusePort',
                'transport',
                'protocol'
            ];
            foreach ($propertyMap as $property) {
                if (isset($config[$property])) {
                    $worker->$property = $config[$property];
                }
            }

            $worker->onWorkerStart = function ($worker) {
                require_once base_path() . '/support/bootstrap.php';
                $app = new \Webman\App(config('app.request_class', Request::class), Log::channel('default'), app_path(), \app\cms\Paths::publicRoot());
                $worker->onMessage = [$app, 'onMessage'];
                call_user_func([$app, 'onWorkerStart'], $worker);
            };
        }

        $windowsWithoutServerListen = is_phar() && DIRECTORY_SEPARATOR === '\\' && empty($config['listen']);
        $process = config('process', []);
        if ($windowsWithoutServerListen && $process) {
            $processName = isset($process['webman']) ? 'webman' : key($process);
            worker_start($processName, $process[$processName]);
        } else if (DIRECTORY_SEPARATOR === '/') {
            foreach (config('process', []) as $processName => $config) {
                if (in_array($processName, ['cms-background','cms-baidu-push','cms-generation'], true)) {
                    \app\cms\support\TaskProcess::start($processName, $config);
                } elseif ($processName === 'monitor') {
                    \app\cms\support\MonitorProcess::start($config);
                } else worker_start($processName, $config);
            }
            foreach (config('plugin', []) as $firm => $projects) {
                foreach ($projects as $name => $project) {
                    if (!is_array($project)) {
                        continue;
                    }
                    foreach ($project['process'] ?? [] as $processName => $config) {
                        worker_start("plugin.$firm.$name.$processName", $config);
                    }
                }
                foreach ($projects['process'] ?? [] as $processName => $config) {
                    worker_start("plugin.$firm.$processName", $config);
                }
            }
        }

        Worker::runAll();
    }

    public static function loadAllConfig(array $excludes = [])
    {
        \app\cms\config\Environment::initialize();
        \app\cms\config\Environment::refresh();
        \Webman\Config::load(config_path(), $excludes);
    }
}
