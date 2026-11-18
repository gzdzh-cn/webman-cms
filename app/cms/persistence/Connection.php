<?php
declare(strict_types=1);
namespace app\cms\persistence;

use PDO;

/** A dedicated connection keeps durable publication records outside content transactions. */
final class Connection
{
    public static function open(): PDO
    {
        return \app\cms\database\Connection::environment();
    }

    public static function prefix(): string
    {
        $prefix = function_exists('config') ? (string)config('think-orm.connections.mysql.prefix', \app\cms\database\TableNames::cms()) : (\app\cms\database\TableNames::cms());
        Schema::prefix($prefix);
        return $prefix;
    }
}
