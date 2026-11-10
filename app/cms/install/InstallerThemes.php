<?php
declare(strict_types=1);
namespace app\cms\install;

use app\cms\template\TemplateDialect;

/** Filesystem-only discovery: installation does not need an existing CMS database. */
final class InstallerThemes
{
    public function __construct(private string $root) {}

    public function choices(): array
    {
        $base = rtrim($this->root, '/') . '/template';
        if (!is_dir($base) || is_link($base)) return [];
        $choices = [];
        foreach (new \DirectoryIterator($base) as $entry) {
            $key = $entry->getFilename();
            if ($entry->isDot() || $entry->isLink() || !$entry->isDir()
                || !preg_match('/^[A-Za-z0-9_-]+$/D', $key)
                || in_array(strtolower($key), ['_shared', 'pc', 'mobile', 'plugins', 'official'], true)) continue;
            $path = $entry->getPathname();
            $devices = [];
            foreach (['pc', 'mobile'] as $device) {
                if (!is_link($path . '/' . $device) && is_dir($path . '/' . $device)
                    && !is_link($path . '/' . $device . '/index.htm') && is_file($path . '/' . $device . '/index.htm')) $devices[] = $device;
            }
            if (!$devices) continue;
            $seen = false; $files = []; $valid = true;
            try {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if ($file->isLink() || !$file->isFile() || !preg_match('/\.html?$/i', $file->getFilename())) continue;
                    $source = file_get_contents($file->getPathname());
                    if ($source === false) { $valid = false; break; }
                    $dialect = TemplateDialect::detect($source);
                    if ($dialect === 'webman' || $dialect === 'mixed') { $valid = false; break; }
                    $seen = $seen || $dialect === 'eyou';
                    $files[substr($file->getPathname(), strlen($path) + 1)] = hash('sha256', $source);
                }
            } catch (\Throwable $error) { $valid = false; }
            if (!$valid || !$seen) continue;
            ksort($files, SORT_STRING);
            $name = $key === 'default' ? 'EyouCMS 默认模板' : $key;
            $manifest = $path . '/theme.json';
            if ($key !== 'default' && !is_link($manifest) && is_file($manifest) && filesize($manifest) < 65536) {
                $meta = json_decode((string)file_get_contents($manifest), true);
                if (is_array($meta) && is_string($meta['name'] ?? null) && trim($meta['name']) !== '') $name = $meta['name'];
            }
            $inspection = \app\cms\secure\ThemeInspection::inspect($path, 'template/' . $key);
            $choices[] = ['key' => $key, 'name' => $name, 'path' => 'template/' . $key, 'devices' => $devices, 'supported' => !$inspection['blocking_issues'], 'issues' => $inspection['issues'], 'blocking_issues' => $inspection['blocking_issues'],
                'signature' => hash('sha256', json_encode(['theme' => $key, 'devices' => $devices, 'files' => $files], JSON_THROW_ON_ERROR))];
        }
        usort($choices, static fn(array $a, array $b): int => strcmp($a['key'], $b['key']));
        return $choices;
    }

    public function resolve(string $selected = ''): array
    {
        $choices = $this->choices();
        if (!$choices) throw new InstallerValidationException(['theme' => '未发现 EyouCMS 标签模板，请先将模板放入 template/<主题目录>/pc 或 mobile。']);
        if ($selected === '' && count($choices) === 1) $selected = $choices[0]['key'];
        if ($selected === '') throw new InstallerValidationException(['theme' => '发现多套 EyouCMS 标签模板，请选择安装后使用的模板。']);
        foreach ($choices as $choice) if ($choice['key'] === $selected) {
            if (!$choice['supported']) throw new InstallerValidationException(['theme' => '该模板包含不支持的功能：' . implode('；', $choice['blocking_issues'])]);
            return ['theme' => $choice['key'], 'signature' => $choice['signature']];
        }
        throw new InstallerValidationException(['theme' => '所选模板不存在或不再是有效的 EyouCMS 标签模板，请重新选择。']);
    }
}
