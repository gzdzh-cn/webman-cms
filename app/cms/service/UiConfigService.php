<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;

/** Compatibility surface for the disabled visual editor. */
class UiConfigService
{
    public const TYPES = ['text','html','type','arclist','channel','upload','map','code','background'];

    public function __construct(?CmsRepository $repository = null) {}

    public function read(array $scope, string $type, string $id): array
    {
        throw new \RuntimeException('可视化编辑功能已关闭');
    }

    public function save(array $scope, array $input, array $files = []): array
    {
        throw new \RuntimeException('可视化编辑功能已关闭');
    }

    public function clear(array $scope, string $type = 'all'): array
    {
        throw new \RuntimeException('可视化编辑功能已关闭');
    }

    public function pageEntries(string $theme, string $page): array
    {
        return [];
    }
}
