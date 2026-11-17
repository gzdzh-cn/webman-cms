<?php
declare(strict_types=1);
namespace app\cms\extension;

/** Code-owned catalogue: no filesystem discovery, user supplied classes or hooks. */
final class Registry
{
    public static function get(string $code): Extension
    {
        return match ($code) {
            'AliyunOss' => new AliyunOss(),
            default => throw new \InvalidArgumentException('插件不存在或尚未适配'),
        };
    }

    public static function catalogue(): array
    {
        return [(new AliyunOss())->manifest()];
    }
}
