<?php
declare(strict_types=1);
namespace app\cms\database;

/** One configurable prefix for new installations; legacy sites keep their layout. */
final class TableNames
{
    public static function unified(): bool { return DatabaseSettings::values()['DB_TABLE_LAYOUT'] === 'unified-backend-v1'; }
    public static function cms(): string
    {
        return self::validate(self::unified() ? (DatabaseSettings::values()['DB_PREFIX']) : (DatabaseSettings::values()['CMS_DB_PREFIX']));
    }
    public static function admin(): string
    {
        return self::validate(self::unified() ? self::cms().'backend_' : (DatabaseSettings::values()['ADMIN_DB_PREFIX']));
    }
    private static function validate(string $prefix): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,47}$/D', $prefix)) throw new \InvalidArgumentException('数据表前缀无效');
        return $prefix;
    }
}
