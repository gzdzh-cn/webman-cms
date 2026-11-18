<?php
declare(strict_types=1);
namespace app\cms\config;
final class SiteSettings
{
    public static function get(string $key): string
    {
        return match($key) {
            'base_path', 'backup_path'=>'',
            'theme'=>'default',
            // Bootstrap fallback only. Public SEO and CLI contexts prefer the backend domain.
            'base_url'=>'http://127.0.0.1:'.parse_url(DeploymentConfig::listener(),PHP_URL_PORT),
            default=>throw new \InvalidArgumentException('未知站点配置键'),
        };
    }
    public static function origin(mixed $configured): string
    {
        if(is_string($configured) && !preg_match('/[\x00-\x20\x7f\\\\]/',$configured)) {
            $parts=parse_url($configured);
            if($parts && in_array(strtolower($parts['scheme']??''),['http','https'],true) && !empty($parts['host'])
                && !isset($parts['user']) && !isset($parts['pass'])
                && !isset($parts['query']) && !isset($parts['fragment'])) {
                return strtolower($parts['scheme']).'://'.strtolower($parts['host']).(isset($parts['port'])?':'.$parts['port']:'');
            }
        }
        return self::get('base_url');
    }
}
