<?php
declare(strict_types=1);
namespace app\cms\config;
final class SiteSettings
{
    public static function get(string $key): string
    {
        $map=['base_path'=>'site.base_path','base_url'=>'site.base_url','theme'=>'site.theme','backup_path'=>'site.backup_path'];
        return (string)DeploymentConfig::get($map[$key]??throw new \InvalidArgumentException('未知站点配置键'));
    }
}
