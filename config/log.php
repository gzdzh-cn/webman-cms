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

// 辅助函数：从环境变量读取布尔值
$getEnvBool = function($key, $default = false) {
    // 优先使用 $_ENV，因为 webman 使用 Dotenv 加载 .env 文件到 $_ENV
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    // 移除可能的注释（# 及其后面的内容）
    if (($pos = strpos($value, '#')) !== false) {
        $value = trim(substr($value, 0, $pos));
    }
    return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
};

// 辅助函数：从环境变量读取整数值
$getEnvInt = function($key, $default = 0) {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return $value !== false && $value !== null ? (int)trim($value) : $default;
};

// 辅助函数：从环境变量读取字符串值
$getEnvString = function($key, $default = '') {
    // 优先使用 $_ENV，因为 webman 使用 Dotenv 加载 .env 文件到 $_ENV
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    // 移除可能的注释（# 及其后面的内容）
    if (($pos = strpos($value, '#')) !== false) {
        $value = trim(substr($value, 0, $pos));
    }
    return trim($value);
};

/**
 * 默认日志配置项（从 .env 读取，提供默认值）
 * 
 * 环境变量说明：
 * - LOG_ENABLE: 是否启用日志（true/false，默认：true）
 * - LOG_PRINT_TO_CONSOLE: 是否打印到终端（true/false，默认：false）
 * - LOG_PRINT_TO_FILE: 是否写入日志文件（true/false，默认：true）
 * - LOG_FILE: 日志文件路径（相对 runtime 目录，默认：'logs/webman.log'）
 *   如果只指定目录（如 'logs'），会自动添加 'webman.log' 文件名
 *   注意：路径必须位于 runtime 目录下，不能使用绝对路径或超出 runtime 目录
 * - LOG_MAX_FILES: 日志文件保留天数（整数，默认：7）
 * - LOG_LEVEL: 日志级别（整数，默认：\Monolog\Logger::DEBUG）
 *   日志级别常量：
 *   - DEBUG = 100
 *   - INFO = 200
 *   - NOTICE = 250
 *   - WARNING = 300
 *   - ERROR = 400
 *   - CRITICAL = 500
 *   - ALERT = 550
 *   - EMERGENCY = 600
 */
// 读取日志文件路径（相对 runtime 目录）
$logFileEnv = $getEnvString('LOG_FILE', 'logs/webman.log');
// 规范化路径（统一使用正斜杠，移除开头的斜杠）
$logFileEnv = ltrim(str_replace('\\', '/', trim($logFileEnv)), '/');
// 确保路径在 runtime 目录下
$runtimePath = runtime_path();
// 如果路径是目录（不以 .log 等扩展名结尾），自动添加文件名
if (!preg_match('/\.(log|txt)$/i', $logFileEnv)) {
    $logFileEnv = rtrim($logFileEnv, '/') . '/webman.log';
}
$logFile = $runtimePath . '/' . $logFileEnv;
// 验证路径是否在 runtime 目录下（防止路径遍历攻击）
$realRuntimePath = realpath($runtimePath);
if ($realRuntimePath === false) {
    $realRuntimePath = $runtimePath;
}
// 如果目录不存在，先创建
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$realLogDir = realpath($logDir);
if ($realLogDir === false || strpos($realLogDir, $realRuntimePath) !== 0) {
    // 如果路径无效或不在 runtime 目录下，使用默认路径
    $logFile = $runtimePath . '/logs/webman.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
}

$defaultLogConfig = [
    'enable' => $getEnvBool('LOG_ENABLE', true), // 是否启用日志
    'print_to_console' => $getEnvBool('LOG_PRINT_TO_CONSOLE', true), // 是否打印到终端
    'print_to_file' => $getEnvBool('LOG_PRINT_TO_FILE', true), // 是否写入日志文件
    'log_file' => $logFile, // 日志文件完整路径
    'max_files' => $getEnvInt('LOG_MAX_FILES', 7), // 日志文件保留天数
    'level' => $getEnvInt('LOG_LEVEL', \Monolog\Logger::DEBUG), // 日志级别
];

/**
 * SQL 查询日志配置项（从 .env 读取，提供默认值）
 * 
 * 环境变量说明：
 * - SQL_LOG_ENABLE: 是否启用 SQL 日志（true/false，默认：true）
 * - SQL_LOG_PRINT_TO_CONSOLE: 是否打印到终端（true/false，默认：true）
 * - SQL_LOG_PRINT_TO_FILE: 是否写入日志文件（true/false，默认：true）
 * - SQL_LOG_FILE: SQL 日志文件目录路径（相对 runtime 目录，默认：'logs'，文件名会自动添加日期后缀）
 *   注意：路径必须位于 runtime 目录下，不能使用绝对路径或超出 runtime 目录
 * - SQL_LOG_MAX_FILES: 日志文件保留天数（整数，默认：7）
 */
// 读取日志目录路径（相对 runtime 目录）
$sqlLogDirEnv = $getEnvString('SQL_LOG_FILE', 'logs');
// 规范化路径（移除开头的斜杠，统一使用正斜杠）
$sqlLogDirEnv = ltrim(str_replace('\\', '/', trim($sqlLogDirEnv)), '/');
// 确保路径在 runtime 目录下
$runtimePath = runtime_path();
$sqlLogDir = $runtimePath . '/' . $sqlLogDirEnv;
// 验证路径是否在 runtime 目录下（防止路径遍历攻击）
$realRuntimePath = realpath($runtimePath);
if ($realRuntimePath === false) {
    $realRuntimePath = $runtimePath;
}
// 如果目录不存在，先创建（用于验证）
if (!is_dir($sqlLogDir)) {
    @mkdir($sqlLogDir, 0755, true);
}
$realLogDir = realpath($sqlLogDir);
if ($realLogDir === false || strpos($realLogDir, $realRuntimePath) !== 0) {
    // 如果路径无效或不在 runtime 目录下，使用默认路径
    $sqlLogDir = $runtimePath . '/logs';
}
// 拼接日期文件名
$sqlLogFile = rtrim($sqlLogDir, '/\\') . '/sql-' . date('Y-m-d') . '.log';

$sqlLogConfig = [
    'enable' => $getEnvBool('SQL_LOG_ENABLE', true), // 是否启用 SQL 日志
    'print_to_console' => $getEnvBool('SQL_LOG_PRINT_TO_CONSOLE', false), // 是否打印到终端
    'print_to_file' => $getEnvBool('SQL_LOG_PRINT_TO_FILE', true), // 是否写入日志文件
    'log_file' => $sqlLogFile, // SQL 日志文件完整路径（包含日期文件名）
    'max_files' => $getEnvInt('SQL_LOG_MAX_FILES', 7), // 日志文件保留天数
];

// 构建默认日志 handlers
$handlers = [];

// 只有在启用日志时才添加处理器
if (!empty($defaultLogConfig['enable'])) {
    // 文件日志处理器
    if (!empty($defaultLogConfig['print_to_file'])) {
        $handlers[] = [
            'class' => \Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                $defaultLogConfig['log_file'],
                $defaultLogConfig['max_files'],
                $defaultLogConfig['level'],
            ],
            'formatter' => [
                'class' => \Monolog\Formatter\LineFormatter::class,
                'constructor' => [null, 'Y-m-d H:i:s', true],
            ],
        ];
    }
    
    // 终端输出处理器
    if (!empty($defaultLogConfig['print_to_console'])) {
        $handlers[] = [
            'class' => \Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'php://stdout',
                $defaultLogConfig['level'],
            ],
            'formatter' => [
                'class' => \Monolog\Formatter\LineFormatter::class,
                'constructor' => ["[%datetime%] %level_name%: %message%\n", 'Y-m-d H:i:s', true],
            ],
        ];
    }
}

return [
    'default' => [
        'handlers' => $handlers,
    ],
    'sql_log' => $sqlLogConfig,
];
