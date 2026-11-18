<?php
declare(strict_types=1);
namespace app\cms;

/** One interpretation of the public goto parameter, including the legacy m alias. */
final class DeviceSelector
{
    public static function forced(mixed $value): ?string
    {
        if (!is_string($value)) return null;
        return match (strtolower(trim($value, "/ \t\r\n"))) {
            'pc' => 'pc', 'm', 'mobile' => 'mobile', default => null,
        };
    }

    /** Upstream URL helpers compare against m; keep their remaining logic intact. */
    public static function legacyGoto(mixed $value): string
    {
        return match (self::forced($value)) { 'mobile' => 'm', 'pc' => 'pc', default => '' };
    }
}
