<?php
declare(strict_types=1);
namespace app\cms\admin\support;

/** The CMS owns its backend; upstream provenance is no longer a runtime package. */
final class BackendVersion
{
    public static function metadata(): array
    {
        return json_decode((string)file_get_contents(\app\cms\Paths::cms('resources/backend-provenance.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function version(): string
    {
        $data = self::metadata();
        return (string)$data['version'];
    }

    public static function description(): string
    {
        $upstream = self::metadata()['upstream'];
        return 'CMS 自维护后台；源自 ' . $upstream['package'] . ' ' . $upstream['version'] . '（' . $upstream['license'] . '）';
    }

    public static function marketVersion(): string
    {
        return (string)self::metadata()['application_compatibility_version'];
    }
}
