<?php

namespace app\common;

// use support\Db;
// use Illuminate\Database\Events\QueryExecuted;
// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;
// use Monolog\Formatter\LineFormatter;

/**
 * SQL 日志监听器
 */
// class SqlLogListener
// {
//     /**
//      * SQL 日志监听器是否已注册
//      * @var bool
//      */
//     private static $registered = false;

//     /**
//      * SQL 日志 Logger 实例
//      * @var Logger|null
//      */
//     private static $logger = null;

//     /**
//      * 注册 SQL 日志监听器
//      */
//     public static function register(): void
//     {
//         // 如果已注册，直接返回
//         if (self::$registered) {
//             return;
//         }
        
//         try {
//             // 从主配置读取
//             $runtimePath = runtime_path();
//             $basePath = dirname($runtimePath);
//             $mainLogConfigFile = $basePath . '/config/log.php';
            
//             if (!file_exists($mainLogConfigFile)) {
//                 return;
//             }
            
//             $logConfig = include $mainLogConfigFile;
//             $sqlLogConfig = $logConfig['sql_log'] ?? [];
            
//             // 检查是否启用 SQL 日志
//             if (empty($sqlLogConfig['enable'])) {
//                 return;
//             }
            
//             $printToConsole = !empty($sqlLogConfig['print_to_console']);
//             $printToFile = !empty($sqlLogConfig['print_to_file']);
            
//             // 如果都不启用，直接返回
//             if (!$printToConsole && !$printToFile) {
//                 return;
//             }
            
//             // 如果需要写入文件，初始化 Logger
//             if ($printToFile) {
//                 self::initLogger($sqlLogConfig);
//             }
            
//             // 注册 SQL 监听器（全局监听所有数据库连接）
//             Db::listen(function (QueryExecuted $queryExecuted) use ($printToConsole, $printToFile) {
//                 // 跳过心跳查询
//                 if (isset($queryExecuted->sql) && $queryExecuted->sql === "select 1") {
//                     return;
//                 }
                
//                 $sql = $queryExecuted->sql;
//                 $bindings = $queryExecuted->bindings ?? [];
//                 $time = $queryExecuted->time ?? 0;
                
//                 // 替换绑定参数
//                 if (!empty($bindings)) {
//                     foreach ($bindings as $binding) {
//                         $value = is_numeric($binding) ? $binding : "'" . addslashes($binding) . "'";
//                         $sql = preg_replace('/\?/', $value, $sql, 1);
//                     }
//                 }
                
//                 $logMessage = $sql . " ({$time}ms)";
                
//                 // 打印到终端
//                 if ($printToConsole) {
//                     echo "\n[SQL] " . $logMessage . "\n";
//                 }
                
//                 // 写入日志文件
//                 if ($printToFile && self::$logger) {
//                     self::$logger->info($sql . " ({$time}ms)");
//                 }
//             });
            
//             // 标记已注册
//             self::$registered = true;
//         } catch (\Exception $e) {
//             // 静默失败，不影响应用运行
//         }
//     }

//     /**
//      * 初始化 SQL Logger
//      */
//     private static function initLogger(array $config): void
//     {
//         try {
//             if (self::$logger !== null) {
//                 return;
//             }
            
//             // 使用配置文件中指定的日志文件路径（已包含日期，直接使用）
//             $logFile = $config['log_file'] ?? runtime_path() . '/logs/sql.log';
            
//             // 确保日志目录存在
//             $logDir = dirname($logFile);
//             if (!is_dir($logDir)) {
//                 @mkdir($logDir, 0755, true);
//             }
            
//             // 创建 Logger
//             self::$logger = new Logger('sql');
            
//             // 使用 StreamHandler 直接写入指定文件，不自动添加日期后缀
//             // 因为配置文件中已经包含了日期，使用 RotatingFileHandler 会导致重复日期
//             $handler = new StreamHandler(
//                 $logFile,
//                 Logger::INFO
//             );
            
//             // 设置格式化器
//             $formatter = new LineFormatter(
//                 "[%datetime%] %message%\n",
//                 'Y-m-d H:i:s',
//                 true
//             );
//             $handler->setFormatter($formatter);
            
//             self::$logger->pushHandler($handler);
//         } catch (\Exception $e) {
//             // 静默失败
//         }
//     }
// }

