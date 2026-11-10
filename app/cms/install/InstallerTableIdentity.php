<?php
declare(strict_types=1);
namespace app\cms\install;

/** Locate table options without touching quoted comments, defaults or column attributes. */
final class InstallerTableIdentity
{
    public static function normalize(string $create): string
    {
        return preg_replace('/^CREATE TABLE `[^`]+`/', 'CREATE TABLE `__owned_table__`', $create, 1);
    }

    public static function counter(string $create): ?string
    {
        [$mask, $end] = self::options($create);
        if (!preg_match('/\bAUTO_INCREMENT\b/i', substr($mask, 0, $end))) return null;
        $tail = substr($mask, $end + 1);
        if (!preg_match('/\bAUTO_INCREMENT\s*=\s*([0-9]+)\b/i', $tail, $match)) return '1';
        return self::decimal($match[1]);
    }

    /** The old checkpoint hash, rather than the source SQL alone, proves the desired value. */
    public static function sourceCounter(string $create): ?string
    {
        [$mask, $end] = self::options($create);
        return preg_match('/\bAUTO_INCREMENT\s*=\s*([0-9]+)\b/i', substr($mask, $end + 1), $m) ? self::decimal($m[1]) : null;
    }

    public static function isInnoDb(string $create): bool
    {
        [$mask, $end] = self::options($create);
        return preg_match('/\bENGINE\s*=\s*InnoDB\b/i', substr($mask, $end + 1)) === 1;
    }

    public static function greater(string $a, string $b): bool
    {
        return self::decimal($a) === $a && self::decimal($b) === $b
            && (strlen($a) > strlen($b) || (strlen($a) === strlen($b) && strcmp($a, $b) > 0));
    }

    public static function decimal(mixed $value): ?string
    {
        if (!is_string($value) || !preg_match('/^[1-9][0-9]{0,19}$/D', $value)) return null;
        if (strlen($value) === 20 && strcmp($value, '18446744073709551615') > 0) return null;
        return $value;
    }

    public static function withCounter(string $create, string $counter): ?string
    {
        if (self::decimal($counter) !== $counter || self::counter($create) === null) return null;
        [$mask, $end] = self::options($create);
        $tail = substr($mask, $end + 1);
        $count = preg_match_all('/\bAUTO_INCREMENT\s*=\s*[0-9]+\b/i', $tail, $matches, PREG_OFFSET_CAPTURE);
        if ($count > 1) return null;
        if ($count === 1) {
            [$option, $offset] = $matches[0][0];
            return substr_replace($create, 'AUTO_INCREMENT=' . $counter, $end + 1 + $offset, strlen($option));
        }
        if (!preg_match('/\bENGINE\s*=\s*InnoDB\b/i', $tail, $engine, PREG_OFFSET_CAPTURE)) return null;
        $offset = $end + 1 + $engine[0][1] + strlen($engine[0][0]);
        return substr_replace($create, ' AUTO_INCREMENT=' . $counter, $offset, 0);
    }

    private static function options(string $sql): array
    {
        $mask = $sql; $length = strlen($sql);
        for ($i = 0; $i < $length; ++$i) {
            if (substr($sql, $i, 2) === '/*') {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) throw new \RuntimeException('安装表结构注释未闭合');
                while ($i <= $end + 1) $mask[$i++] = ' ';
                --$i;
                continue;
            }
            if ($sql[$i] === '#' || (substr($sql, $i, 2) === '--'
                && (isset($sql[$i + 2]) && ord($sql[$i + 2]) <= 32))) {
                while ($i < $length && $sql[$i] !== "\n" && $sql[$i] !== "\r") $mask[$i++] = ' ';
                --$i;
                continue;
            }
            if (!in_array($sql[$i], ["'", '"', '`'], true)) continue;
            $quote = $sql[$i]; $mask[$i] = ' ';
            while (++$i < $length) {
                $mask[$i] = ' ';
                if ($sql[$i] === '\\' && $i + 1 < $length) { $mask[++$i] = ' '; continue; }
                if ($sql[$i] === $quote) {
                    if (($sql[$i + 1] ?? '') === $quote) { $mask[++$i] = ' '; continue; }
                    break;
                }
            }
        }
        $end = strrpos($mask, ')');
        if ($end === false) throw new \RuntimeException('安装表结构无效');
        return [$mask, $end];
    }
}
