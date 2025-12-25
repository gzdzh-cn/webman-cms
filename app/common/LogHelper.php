<?php

namespace app\common;

/**
 * 日志配置辅助类
 */
// class LogHelper
// {
//     /**
//      * 构建日志 handlers 配置
//      * 
//      * @param array $config 日志配置项
//      * @return array handlers 配置数组
//      */
//     public static function buildHandlers(array $config): array
//     {
//         $handlers = [];

//         // 只有在启用日志时才添加处理器
//         if (empty($config['enable'])) {
//             return $handlers;
//         }

//         // 文件日志处理器
//         if (!empty($config['print_to_file'])) {
//             $logFile = $config['log_file'] ?? runtime_path() . '/logs/webman.log';
            
//             // 确保日志目录存在
//             $logDir = dirname($logFile);
//             if (!is_dir($logDir)) {
//                 @mkdir($logDir, 0755, true);
//             }
            
//             $handlers[] = [
//                 'class' => \Monolog\Handler\RotatingFileHandler::class,
//                 'constructor' => [
//                     $logFile,
//                     $config['max_files'] ?? 7,
//                     $config['level'] ?? \Monolog\Logger::DEBUG,
//                 ],
//                 'formatter' => [
//                     'class' => \Monolog\Formatter\LineFormatter::class,
//                     'constructor' => [null, 'Y-m-d H:i:s', true],
//                 ],
//             ];
//         }

//         // 终端输出处理器
//         if (!empty($config['print_to_console'])) {
//             $handlers[] = [
//                 'class' => \Monolog\Handler\StreamHandler::class,
//                 'constructor' => [
//                     'php://stdout',
//                     $config['level'],
//                 ],
//                 'formatter' => [
//                     'class' => \Monolog\Formatter\LineFormatter::class,
//                     'constructor' => ["[%datetime%] %level_name%: %message%\n", 'Y-m-d H:i:s', true],
//                 ],
//             ];
//         }

//         return $handlers;
//     }
// }

