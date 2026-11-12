<?php
declare(strict_types=1);
namespace app\cms\support;

use app\cms\config\{DeploymentConfig,Entry};
use app\cms\Paths;

/** Runs before Workerman parses restart/-d or opens listeners. */
final class BinaryStartup
{
    /** Mutating preparation is restricted to actual starts and explicit --fix. */
    public static function prepare(): void
    {
        if (!EmbeddedResources::binary()) return;
        fwrite(STDERR, 'CMS 正在检查部署文件权限…'.PHP_EOL);
        $result = (new \app\cms\maintenance\DeploymentPermissions(
            static fn(string $message) => fwrite(STDERR, 'CMS 权限：'.$message.PHP_EOL)
        ))->run(true);
        if (!$result['ok']) throw new \RuntimeException(implode('；', $result['errors']));
        fwrite(STDERR, 'CMS 权限就绪：检查 '.$result['checked'].' 项，调整 '.$result['changed'].' 项'.PHP_EOL);
    }
    /** Read-only checks, also used by deployment preflight without permission repair. */
    public static function check(bool $database=true): void
    {
        if(!EmbeddedResources::binary())return;
        $file=DeploymentConfig::file();
        if(!is_file($file))throw new \RuntimeException('二进制缺少 YAML 配置：'.$file.'；默认查找 config.yaml、config/config.yaml、config/manifest/config.yaml（相对 '.Entry::directory().'）');
        DeploymentConfig::initialize();
        $templates=Paths::templates();
        if(!is_dir($templates)||!is_readable($templates))throw new \RuntimeException('模板根目录不存在、不可读或类型无效：'.$templates);
        if(!EmbeddedResources::has('template'))Entry::assertPath($templates);
        $assets=Paths::assets();
        if(!is_dir($assets)||!is_readable($assets))throw new \RuntimeException('系统资源目录不存在或不可读：'.$assets);
        $report=(new \app\cms\maintenance\AssetMaintenance())->inspect(true);
        if(!$report['ok']) {
            $first=$report['errors'][0];
            throw new \RuntimeException('系统资源缺失或清单不匹配：'.Paths::logical($first['target']).'（'.$first['reason'].'）');
        }
        if($database)self::installedDatabase();
    }
    private static function installedDatabase(): void
    {
        try {
            $settings=\app\cms\database\DatabaseSettings::values();
            if($settings['DB_NAME']===''||$settings['DB_USER']==='')throw new \RuntimeException();
            $pdo=\app\cms\database\Connection::environment();
            $cms=\app\cms\database\TableNames::cms();$admin=\app\cms\database\TableNames::admin();
            foreach([$cms.'config',$cms.'language',$admin.'admins']as$table) {
                if(!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/D',$table))throw new \RuntimeException();
                if(!$pdo->query('SELECT 1 FROM `'.$table.'` LIMIT 1')->fetchColumn())throw new \RuntimeException();
            }
        }catch(\Throwable){throw new \RuntimeException('二进制部署需要有效的已安装数据库；请核对数据库连接和表前缀，并先在源码版完成安装。二进制不会初始化数据库。');}
    }
    public static function report(\Throwable $error): void
    {
        $message=$error instanceof \PDOException?'数据库连接失败，请检查部署配置':$error->getMessage();
        fwrite(STDERR,'CMS 启动失败：'.$message.PHP_EOL);
        $file=Entry::directory().'/runtime/logs/bootstrap.log';
        try {
            Entry::assertPath($file);Paths::ensureDirectory(dirname($file),0750);
            if(is_link($file)||(file_exists($file)&&(!is_file($file)||!is_writable($file)||(fileperms($file)&0222)===0)))throw new \RuntimeException();
            if(@file_put_contents($file,gmdate('c').' '.$message.PHP_EOL,FILE_APPEND|LOCK_EX)===false)throw new \RuntimeException();
        }catch(\Throwable){fwrite(STDERR,'启动错误日志写入失败：'.$file.PHP_EOL);}
    }
}
