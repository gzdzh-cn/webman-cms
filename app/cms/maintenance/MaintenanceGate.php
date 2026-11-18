<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Cross-process exclusion: generation/install/restart hold shared, upgrade holds exclusive. */
final class MaintenanceGate
{
    private static int $depth=0;
    public static function read(callable $operation, callable $unavailable): mixed
    {
        if (self::$depth) return $operation();
        $handle=fopen(self::file(),'c+b');
        if (!$handle) return $unavailable();
        if (!chmod(self::file(),0600) || !flock($handle,LOCK_SH|LOCK_NB)) { fclose($handle);return $unavailable(); }
        self::$depth++;
        try { return $operation(); } finally { self::$depth--;flock($handle,LOCK_UN);fclose($handle); }
    }
    public static function file(): string
    {
        \app\cms\Paths::ensureDirectory(\app\cms\Paths::runtime('locks'),0700);
        $path=\app\cms\Paths::runtime('locks/program-switch.lock');
        \app\cms\config\Entry::assertPath($path);return $path;
    }
    public static function shared(callable $operation): mixed
    {
        if (self::$depth) return $operation();
        $handle=fopen(self::file(),'c+b');
        if (!$handle) throw new \RuntimeException('升级互斥不可用');
        if (!chmod(self::file(),0600) || !flock($handle,LOCK_SH|LOCK_NB)) { fclose($handle);throw new \RuntimeException('程序正在升级，请稍后重试'); }
        self::$depth++;
        try { return $operation(); } finally { self::$depth--;flock($handle,LOCK_UN);fclose($handle); }
    }
}
