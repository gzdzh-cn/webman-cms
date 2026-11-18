<?php
declare(strict_types=1);
namespace app\cms\maintenance;

/** Strict release contract shared by the publisher, web check and frozen executor. */
final class ReleaseManifest
{
    public const REPOSITORY = 'gzdzh-cn/webman-cms';
    public const NAME = 'cms-upgrade.json';
    public static function version(string $tag): string
    {
        if (!preg_match('/^v?([0-9]+\.[0-9]+\.[0-9]+)$/D', $tag, $match)) throw new \RuntimeException('正式版本号必须为 x.y.z');
        return $match[1];
    }
    public static function validate(array $manifest, string $tag): array
    {
        $version = self::version($tag);
        if (($manifest['format'] ?? null) !== 1 || ($manifest['repository'] ?? '') !== self::REPOSITORY || ($manifest['version'] ?? '') !== $version || ($manifest['migration'] ?? '') !== 'backward-compatible-additive') throw new \RuntimeException('升级清单、标签或兼容迁移声明不一致');
        foreach (['min_version','max_version','min_php'] as $key) self::version((string)($manifest[$key] ?? ''));
        if (!is_array($manifest['packages'] ?? null) || !$manifest['packages'] || count($manifest['packages']) > 16) throw new \RuntimeException('升级包清单无效');
        $names = [];
        foreach ($manifest['packages'] as $package) {
            $name = $package['name'] ?? '';
            if (!is_string($name) || !preg_match('/^webman-cms-[a-z0-9._-]+\.tar\.gz$/D', $name) || isset($names[$name])) throw new \RuntimeException('升级包名称无效或重复');
            $names[$name] = true;
            if (!in_array($package['kind'] ?? '', ['source','binary'], true) || !is_int($package['size'] ?? null) || $package['size'] < 1 || $package['size'] > 536870912 || !preg_match('/^[a-f0-9]{64}$/D', $package['sha256'] ?? '')) throw new \RuntimeException('升级包摘要或大小无效');
            if ($package['kind'] === 'binary' && (!in_array($package['platform'] ?? '', RuntimeProvider::TARGETS, true) || !is_bool($package['embed_admin_assets'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $package['build_id'] ?? ''))) throw new \RuntimeException('二进制平台或构建身份无效');
            if (!is_array($package['files'] ?? null) || !$package['files'] || count($package['files']) > 30000) throw new \RuntimeException('受管文件清单无效');
            $total = 0;
            foreach ($package['files'] as $path => $file) {
                self::managed($path, $package['kind']);
                if (!is_array($file) || !is_int($file['size'] ?? null) || $file['size'] < 0 || $file['size'] > 268435456 || !preg_match('/^[a-f0-9]{64}$/D', $file['sha256'] ?? '') || !in_array($file['mode'] ?? null, [0644,0755], true)) throw new \RuntimeException('受管文件身份无效');
                $total += $file['size'];
            }
            if ($total > 1073741824) throw new \RuntimeException('升级包展开大小超限');
            if ($package['kind'] === 'source' && (!isset($package['files']['webman'], $package['files']['composer.lock'], $package['files']['vendor/autoload.php'], $package['files']['app/cms/resources/backend-provenance.json']))) throw new \RuntimeException('源码包缺少程序、版本或锁定依赖');
            if ($package['kind'] === 'binary' && !isset($package['files']['program'])) throw new \RuntimeException('二进制包缺少程序');
        }
        return $manifest;
    }
    public static function managed(string $path, string $kind): void
    {
        AssetArchive::relative($path);
        if ($kind === 'binary' && $path === 'program') return;
        if ($kind === 'binary' && !str_starts_with($path, 'app/cms/resources/admin-assets/')) throw new \RuntimeException('二进制包包含非受管文件');
        if ($kind === 'source' && !preg_match('#^(?:app/|support/|vendor/|config/(?:[a-z][a-z_-]*\.php|plugin/webman/(?:console|event)/[a-z]+\.php)$|webman$|start\.php$|composer\.(?:json|lock)$|LICENSE$|MIT-LICENSE\.txt$)#D', $path)) throw new \RuntimeException('升级包包含非受管路径');
        if (preg_match('#(?:^|/)(?:\.[^/]*|node_modules)(?:/|$)|\.(?:sql|env|key|pem|bak|log|ya?ml)$#i', $path) || str_starts_with($path,'config/cms-install') || str_ends_with($path,'binary-build.json')) throw new \RuntimeException('升级包包含站点资料或构建残留');
        if (str_starts_with($path,'config/') && !preg_match('#^config/plugin/webman/(?:console|event)/[a-z]+\.php$#D',$path) && !in_array(basename($path,'.php'),BinaryBuilder::CONFIG,true)) throw new \RuntimeException('升级包包含私有配置');
    }
    public static function package(array $manifest, array $deployment): ?array
    {
        if (!empty($deployment['custom'])) return null;
        foreach ($manifest['packages'] as $package) {
            if ($package['kind'] !== $deployment['kind']) continue;
            if ($package['kind'] === 'source' || [$package['platform'],$package['embed_admin_assets']] === [$deployment['platform'],$deployment['embed_admin_assets']]) return $package;
        }
        return null;
    }
}
