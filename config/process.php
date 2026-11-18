<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use support\Log;
use support\Request;
use app\process\Http;

global $argv;

$highPerformance = \app\cms\config\DeploymentConfig::get('performance.separate_task_processes');
$fileMonitor = !\Phar::running() && \app\cms\config\DeploymentConfig::get('app.debug') && !in_array('-d', $argv ?? [], true) && DIRECTORY_SEPARATOR === '/';

return [
    'webman' => [
        'handler' => Http::class,
        'listen' => \app\cms\config\DeploymentConfig::listener(),
        'count' => \app\cms\config\DeploymentConfig::get('server.workers'),
        'user' => '',
        'group' => '',
        'reusePort' => false,
        'eventLoop' => '',
        'context' => [],
        'constructor' => [
            'requestClass' => Request::class,
            'logger' => Log::channel('default'),
            'appPath' => app_path(),
            'publicPath' => public_path()
        ]
    ],
    'cms-baidu-push' => [
        'enable' => $highPerformance,
        'handler' => app\process\BaiduPushWorker::class,
        'count' => 1,
        'reloadable' => true,
    ],
    'cms-generation' => [
        'enable' => $highPerformance,
        'handler' => app\process\StaticGenerationWorker::class,
        'count' => 1,
        'reloadable' => true,
    ],
    'cms-background' => [
        'enable' => !$highPerformance,
        'handler' => app\cms\process\BackgroundWorker::class,
        'count' => 1,
        'reloadable' => true,
    ],
    // High-performance memory watchdog; source development also watches files.
    'monitor' => [
        'enable' => DIRECTORY_SEPARATOR === '/' && ($highPerformance || $fileMonitor),
        'handler' => app\process\Monitor::class,
        'count' => 1,
        'reloadable' => false,
        'constructor' => [
            // Monitor these directories
            'monitorDir' => [
                app_path(),
                config_path(),
                base_path() . '/process',
                base_path() . '/support',
                base_path() . '/resource',
                base_path() . '/.env',
            ],
            // Files with these suffixes will be monitored
            'monitorExtensions' => [
                'php', 'html', 'htm', 'env'
            ],
            'options' => [
                'enable_file_monitor' => $fileMonitor,
                'enable_memory_monitor' => $highPerformance,
                'memory_limit' => \app\cms\config\DeploymentConfig::get('app.memory_limit'),
            ]
        ]
    ]
];
