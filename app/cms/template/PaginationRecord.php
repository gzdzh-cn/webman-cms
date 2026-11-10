<?php
declare(strict_types=1);
namespace app\cms\template;

use app\cms\security\SignedEnvelope;

/** Authenticate saved pagination SQL and attributes before they are reused. */
final class PaginationRecord
{
    public static function seal(string $tagid, string $sql, string $attributes, int $size): string
    {
        return SignedEnvelope::seal('pagination-sql-v1', $tagid, json_encode([$sql, $attributes, $size], JSON_THROW_ON_ERROR));
    }

    public static function sql(string $tagid, array $record): ?string
    {
        $payload = SignedEnvelope::open('pagination-sql-v1', $tagid, (string)($record['querysql'] ?? ''));
        if ($payload === null) return null;
        try { $data = json_decode($payload, true, 8, JSON_THROW_ON_ERROR); }
        catch (\JsonException $error) { return null; }
        if (!is_array($data) || count($data) !== 3 || !is_string($data[0]) || !is_string($data[1]) || !is_int($data[2])
            || !hash_equals($data[1], (string)($record['attstr'] ?? '')) || $data[2] !== (int)($record['pagesize'] ?? 0)) return null;
        return $data[0];
    }
}
