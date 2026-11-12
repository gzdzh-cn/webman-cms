<?php
use app\cms\config\DeploymentConfig;
use app\cms\Paths;

$settings=DeploymentConfig::group('logging');$handlers=[];
if($settings['enable']&&$settings['print_to_file']){
    $file=$settings['file'];if(!preg_match('/\.(log|txt)$/i',$file))$file=rtrim($file,'/').'/webman.log';
    $file=Paths::runtime($file);Paths::ensureDirectory(dirname($file));
    $handlers[]=['class'=>\Monolog\Handler\RotatingFileHandler::class,'constructor'=>[$file,$settings['max_files'],$settings['level']],
        'formatter'=>['class'=>\Monolog\Formatter\LineFormatter::class,'constructor'=>[null,'Y-m-d H:i:s',true]]];
}
if($settings['enable']&&$settings['print_to_console'])$handlers[]=['class'=>\Monolog\Handler\StreamHandler::class,'constructor'=>['php://stdout',$settings['level']],
    'formatter'=>['class'=>\Monolog\Formatter\LineFormatter::class,'constructor'=>["[%datetime%] %level_name%: %message%\n",'Y-m-d H:i:s',true]]];
$sql=$settings['sql'];$directory=Paths::runtime($sql['file']);
if($sql['enable']&&$sql['print_to_file'])Paths::ensureDirectory($directory);
return ['default'=>['handlers'=>$handlers],'sql_log'=>$sql+['log_file'=>$directory.'/sql-'.date('Y-m-d').'.log']];
