<?php
declare(strict_types=1);

namespace support;

use Illuminate\Database\Events\QueryExecuted;
use Webman\Bootstrap;
use support\Db;

class SqlDebug implements Bootstrap
{
    /**
     * SQL 日志 Logger 实例
     * @var \Monolog\Logger|null
     */
    private static $logger = null;

    public static function start($worker)
    {
        // 从配置读取 SQL 日志配置
        $sqlLogConfig = config('log.sql_log', []);
        
        // 检查是否启用 SQL 日志
        if (empty($sqlLogConfig['enable'])) {
            return;
        }
        
        $printToConsole = !empty($sqlLogConfig['print_to_console']);
        $printToFile = !empty($sqlLogConfig['print_to_file']);
        
        // 如果都不启用，直接返回
        if (!$printToConsole && !$printToFile) {
            return;
        }
        
        // 如果需要写入文件，初始化 Logger
        if ($printToFile) {
            self::initLogger($sqlLogConfig);
        }
        
        // 使用 Db::listen() 监听所有数据库连接（全局监听）
        Db::listen(function (QueryExecuted $queryExecuted) use ($printToConsole, $printToFile) {
            // 跳过心跳查询
            if (isset($queryExecuted->sql) && $queryExecuted->sql === "select 1") {
                return;
            }
            
            $sql = $queryExecuted->sql;
            $bindings = $queryExecuted->bindings ?? [];
            $time = $queryExecuted->time ?? 0;
            
            // 替换绑定参数
            if (!empty($bindings)) {
                foreach ($bindings as $binding) {
                    $value = is_numeric($binding) ? (string)$binding : "'" . addslashes((string)$binding) . "'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }
            }
            
            $logMessage = "[{$time} ms] {$sql}";
            
            // 打印到终端
            if ($printToConsole) {
                dump($logMessage);
            }
            
            // 写入日志文件
            if ($printToFile && self::$logger) {
                self::$logger->info($sql . " ({$time}ms)");
            }
        });
    }

    /**
     * 初始化 SQL Logger
     */
    private static function initLogger(array $config): void
    {
        try {
            if (self::$logger !== null) {
                return;
            }
            
            // 使用配置文件中指定的日志文件路径（已包含日期，直接使用）
            $logFile = $config['log_file'] ?? runtime_path() . '/logs/sql.log';
            
            // 确保日志目录存在
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            
            // 创建 Logger
            self::$logger = new \Monolog\Logger('sql');
            
            // 使用 StreamHandler 直接写入指定文件，不自动添加日期后缀
            // 因为配置文件中已经包含了日期，使用 RotatingFileHandler 会导致重复日期
            $handler = new \Monolog\Handler\StreamHandler(
                $logFile,
                \Monolog\Logger::INFO
            );
            
            // 设置格式化器
            $formatter = new \Monolog\Formatter\LineFormatter(
                "[%datetime%] %message%\n",
                'Y-m-d H:i:s',
                true
            );
            $handler->setFormatter($formatter);
            
            self::$logger->pushHandler($handler);
        } catch (\Exception $e) {
            // 静默失败
        }
    }
}

