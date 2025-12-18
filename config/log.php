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

// 日志输出开关配置
// 修改这两个开关来控制日志输出方式
$logToFile = false;      // true: 输出到文件, false: 不输出到文件
$logToConsole = true; // true: 输出到终端, false: 不输出到终端

$handlers = [];

// 文件日志处理器
if ($logToFile) {
    $handlers[] = [
        'class' => Monolog\Handler\RotatingFileHandler::class,
        'constructor' => [
            runtime_path() . '/logs/webman.log',
            7, //$maxFiles
            Monolog\Logger::DEBUG,
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [null, 'Y-m-d H:i:s', true],
        ],
    ];
}

// 终端输出处理器
if ($logToConsole) {
    $handlers[] = [
        'class' => Monolog\Handler\StreamHandler::class,
        'constructor' => [
            'php://stdout', // 输出到标准输出（终端）
            Monolog\Logger::DEBUG,
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                true,
            ],
        ],
    ];
}

return [
    'default' => [
        'handlers' => $handlers,
    ],
];
