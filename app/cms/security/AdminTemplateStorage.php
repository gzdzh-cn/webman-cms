<?php
declare(strict_types=1);
namespace app\cms\security;

use think\template\contract\DriverInterface;

final class AdminTemplateStorage implements DriverInterface
{
    private TemplateCache $cache;
    public function __construct()
    {
        $scope = json_encode(['admin-views-v1', \app\cms\support\BuildIdentity::version(), \app\cms\template\TemplateDialect::VERSION,
            \app\cms\template\TemplateMode::current(), \app\cms\template\TemplateMode::siteSignature()], JSON_THROW_ON_ERROR);
        $this->cache = new TemplateCache('template.admin', $scope, 3600);
    }
    public function write(string $cacheFile, string $content): void { $this->cache->write($cacheFile, $content); }
    public function read(string $cacheFile, array $vars = []): void
    {
        $bytes = $this->cache->read($cacheFile);
        if ($bytes === null) throw new \RuntimeException('后台模板缓存完整性校验失败');
        CompiledSnapshot::render($bytes, $vars);
    }
    public function check(string $cacheFile, int $cacheTime): bool { return false; }
}
