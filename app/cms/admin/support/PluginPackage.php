<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use RuntimeException;

/** Legacy plugin package compatibility; publication is disabled. */
final class PluginPackage
{
    public static function name(mixed $name): string
    {
        if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_]+$/D', $name)) throw new RuntimeException('插件名称无效');
        if (strcasecmp($name, 'admin') === 0) throw new RuntimeException('admin 为 CMS 保留名称，不能通过插件市场安装、更新或卸载');
        return $name;
    }

    public static function assertTree(string $path): void
    {
        self::noLinks($path);
        if (!is_dir($path)) return;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $entry) if ($entry->isLink() || !$entry->isFile()) throw new RuntimeException('插件目录包含不支持的文件类型');
    }

    /** Reject legacy plugin publication. */
    public static function publish(string $archive, string $name, string $pluginRoot, ?callable $beforePublish = null): mixed
    {
        throw new RuntimeException('业务插件已停用');
    }

    private static function noLinks(string $path): void
    {
        if (!str_starts_with($path, '/')) throw new RuntimeException('插件路径必须为绝对路径');
        $current = '';
        foreach (explode('/', ltrim($path, '/')) as $part) {
            if ($part === '' || $part === '.' || $part === '..') throw new RuntimeException('插件路径无效');
            $current .= '/' . $part;
            if (is_link($current)) throw new RuntimeException('插件路径不能包含符号链接');
        }
    }

}
