<?php
namespace app\cms\compat;

/** Shared anonymous playback policy while member/order workflows are disabled. */
final class MediaAccess
{
    public static function allowsGuest(array $archive, array $file, ?string $language = null): bool
    {
        if (!$archive) return false;
        if (array_key_exists('is_del', $archive) && (int)$archive['is_del'] !== 0) return false;
        if (array_key_exists('status', $archive) && (int)$archive['status'] !== 1) return false;
        if (array_key_exists('arcrank', $archive) && (int)$archive['arcrank'] <= -1) return false;
        if ($language !== null && (string)($archive['lang'] ?? '') !== $language) return false;
        if (!empty($archive['restric_type']) || (int)($archive['arc_level_id'] ?? 0) > 0) return false;
        return (float)($archive['users_price'] ?? 0) <= 0
            || !empty($archive['users_free']) || !empty($file['gratis']);
    }
}
