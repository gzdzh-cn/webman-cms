<?php
declare(strict_types=1);
namespace app\cms\database;
use PDO;
final class Connection
{
    public static function environment(bool $database=true): PDO
    {
        $v = DatabaseSettings::values();
        $host=(string)$v['DB_HOST']; $port=filter_var($v['DB_PORT'], FILTER_VALIDATE_INT); $name=(string)$v['DB_NAME'];
        $user=(string)$v['DB_USER']; $password=(string)$v['DB_PASSWORD'];
        if (!preg_match('/^[A-Za-z0-9_.:-]{1,253}$/D',$host) || $port === false || $port < 1 || $port > 65535
            || ($database && !preg_match('/^[A-Za-z0-9_]{1,64}$/D',$name)) || $user === '') throw new \InvalidArgumentException('Invalid database configuration');
        return new PDO('mysql:host='.$host.';port='.$port.($database?';dbname='.$name:'').';charset=utf8mb4',$user,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
    }
    public static function prefixes(): array
    {
        $cms=(string)TableNames::cms();$admin=(string)TableNames::admin();
        foreach([$cms,$admin]as$p)if(!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,55}$/D',$p))throw new \InvalidArgumentException('Invalid table prefix');
        if($cms===$admin)throw new \InvalidArgumentException('CMS and admin prefixes must differ');return [$cms,$admin];
    }
}
