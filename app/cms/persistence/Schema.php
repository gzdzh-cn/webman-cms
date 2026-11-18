<?php
declare(strict_types=1);
namespace app\cms\persistence;

use PDO;

/** Explicit installation only; normal requests never create tables. */
final class Schema
{
    public const TABLES = ['cms_state_records', 'cms_state_objects', 'cms_state_object_refs'];

    public static function prefix(string $prefix): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,31}$/D', $prefix)) throw new \InvalidArgumentException('CMS数据库前缀无效');
    }

    public static function install(PDO $pdo, string $prefix): void
    {
        self::prefix($prefix);
        if ($pdo->inTransaction()) throw new \RuntimeException('持久化结构安装必须在数据事务之外执行');
        $pdo->exec('CREATE TABLE IF NOT EXISTS `' . $prefix . 'cms_state_records` (
            `scope` varchar(96) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `name` varchar(191) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `payload` longblob NOT NULL,
            `sha256` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `updated_at` bigint unsigned NOT NULL,
            PRIMARY KEY (`scope`,`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $pdo->exec('CREATE TABLE IF NOT EXISTS `' . $prefix . 'cms_state_objects` (
            `sha256` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `payload` longblob NOT NULL,
            `bytes` bigint unsigned NOT NULL,
            `created_at` bigint unsigned NOT NULL,
            PRIMARY KEY (`sha256`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $pdo->exec('CREATE TABLE IF NOT EXISTS `' . $prefix . 'cms_state_object_refs` (
            `scope` varchar(96) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `name` varchar(191) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            `sha256` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            PRIMARY KEY (`scope`,`name`), KEY `object_sha256` (`sha256`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    public static function exists(PDO $pdo, string $prefix): bool
    {
        self::prefix($prefix);
        $query = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN (?,?,?)');
        $query->execute(array_map(static fn($name) => $prefix . $name, self::TABLES));
        return (int)$query->fetchColumn() === count(self::TABLES);
    }
}
