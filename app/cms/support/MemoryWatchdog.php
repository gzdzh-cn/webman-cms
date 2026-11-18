<?php
declare(strict_types=1);
namespace app\cms\support;

/** Bounded /proc reads shared by the master and its independent monitor. */
final class MemoryWatchdog
{
    /** One outstanding recycle request per child identity, including a busy child. */
    private static array $recycling = [];
    public static function limit(string $value): int
    {
        if ($value === '-1' || $value === '0') return 0;
        $unit = strtolower(substr($value, -1));
        $number = (int)$value;
        $mb = match ($unit) { 'g' => $number * 1024, 'k' => $number / 1024, 'm' => $number, default => $number / 1048576 };
        return (int)(max(50, $mb) * 0.8);
    }

    public static function identity(int $pid): ?array
    {
        $raw = @file_get_contents('/proc/'.$pid.'/stat');
        if (!is_string($raw) || ($end = strrpos($raw, ')')) === false) return null;
        $fields = preg_split('/\s+/', trim(substr($raw, $end + 1)));
        if (!isset($fields[19])) return null;
        return ['parent' => (int)$fields[1], 'start' => $fields[19]];
    }

    /** Only the master publishes permission to recycle; reload/stop revoke it. */
    public static function publishMasterState(bool $running): void
    {
        $identity = self::identity(getmypid());
        if (!$identity) throw new \RuntimeException('无法核验内存监护主进程身份');
        $path = \app\cms\Paths::runtime('memory-watchdog.json');
        \app\cms\config\Entry::assertPath($path);
        $temporary = $path.'.'.bin2hex(random_bytes(8));
        try {
            $bytes = json_encode(['master'=>getmypid(), 'identity'=>$identity, 'running'=>$running], JSON_THROW_ON_ERROR);
            $handle = fopen($temporary, 'x');
            if ($handle === false) throw new \RuntimeException('无法保存内存监护状态');
            try {
                if (!chmod($temporary, 0600) || fwrite($handle, $bytes) !== strlen($bytes)) throw new \RuntimeException('无法保存内存监护状态');
            } finally {
                fclose($handle);
            }
            if (!rename($temporary, $path)) throw new \RuntimeException('无法发布内存监护状态');
        } finally {
            if (is_file($temporary)) unlink($temporary);
        }
    }

    private static function delegated(int $master, array $identity): bool
    {
        $self = self::identity(getmypid());
        $current = self::identity($master);
        // A daemon master can be reparented after fork; its start time stays fixed.
        if (!$self || $self['parent'] !== $master || !$current || $current['start'] !== $identity['start']) return false;
        $path = \app\cms\Paths::runtime('memory-watchdog.json');
        \app\cms\config\Entry::assertPath($path);
        $bytes = @file_get_contents($path, false, null, 0, 4096);
        $state = is_string($bytes) ? json_decode($bytes, true) : null;
        return is_array($state) && ($state['master'] ?? null) === $master
            && ($state['identity']['start'] ?? null) === $identity['start'] && ($state['running'] ?? false) === true;
    }

    public static function inspect(array $pids, int $master, int $limit, ?array $masterIdentity = null): void
    {
        if (PHP_OS_FAMILY !== 'Linux' || $limit <= 0) return;
        $delegated = $master !== getmypid();
        if ($delegated && ($masterIdentity === null || !self::delegated($master, $masterIdentity))) return;
        self::$recycling = array_intersect_key(self::$recycling, array_fill_keys($pids, true));
        foreach ($pids as $pid) {
            $pid = (int)$pid;
            if ($pid < 2 || $pid === $master || $pid === getmypid()) continue;
            $identity = self::identity($pid);
            if (!$identity || $identity['parent'] !== $master) continue;
            if ((self::$recycling[$pid]??null) === $identity) continue;
            $status = @file_get_contents('/proc/'.$pid.'/status');
            if (!is_string($status) || !preg_match('/^VmRSS:\s+(\d+)\s+kB/m', $status, $match)) continue;
            if ((int)$match[1] < $limit * 1024 || self::identity($pid) !== $identity) continue;
            if ($delegated && !self::delegated($master, $masterIdentity)) return;
            if (@posix_kill($pid, SIGINT)) self::$recycling[$pid] = $identity;
        }
    }
}
