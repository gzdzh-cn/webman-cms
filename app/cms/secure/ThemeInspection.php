<?php
declare(strict_types=1);
namespace app\cms\secure;

final class ThemeInspection
{
    public static function issues(string $root, string $label): array
    {
        return self::inspect($root, $label)['issues'];
    }

    public static function inspect(string $root, string $label): array
    {
        $issues = []; $blocking = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isLink() || !$file->isFile() || !preg_match('/\.html?$/i', $file->getFilename())) continue;
            $relative = substr($file->getPathname(), strlen($root) + 1);
            $source = file_get_contents($file->getPathname());
            if ($source === false) {
                $issue = $label . '/' . $relative . ': 无法读取';
                $issues[] = $issue;
                if (!self::legacyPage($relative)) $blocking[] = $issue;
                continue;
            }
            if (preg_match('~^(?:pc|mobile)/(?:lists_|view_)[^/]+\.html?$~i', str_replace('\\', '/', $relative))
                && (trim($source) === '' || preg_match('~^<p>网站不用到该模板，需要的请自行编写</p>$~u', trim($source)))) {
                $issues[] = $label . '/' . $relative . ' 第1行：模板为空或占位，尚未提供此页面';
            }
            try {
                \app\cms\template\TemplateSourcePolicy::validateUnsupportedTags($source, $label . '/' . $relative);
            } catch (\Throwable $error) {
                $issue = str_starts_with($error->getMessage(), $label . '/' . $relative . ' ') ? $error->getMessage() : $label . '/' . $relative . ': ' . $error->getMessage();
                $issues[] = $issue;
                $blocking[] = $issue;
            }
            if (preg_match('/\{(?:eyou|webman):(?:'.implode('|', \app\cms\template\TemplateCapabilities::DISABLED_TAGS).')\b/i', $source, $disabled, PREG_OFFSET_CAPTURE)) {
                $issues[] = $label . '/' . $relative . ' 第' . (substr_count(substr($source, 0, $disabled[0][1]), "\n") + 1) . '行：官网可用，会员及支付功能未提供';
            }
            try {
                $dialect = \app\cms\template\TemplateDialect::detect($source);
                $mode = $dialect === 'webman' ? 'webman' : 'eyou';
                \app\cms\template\TemplateSourcePolicy::validate($source, $mode, $label . '/' . $relative);
            } catch (\Throwable $error) {
                $issue = str_starts_with($error->getMessage(), $label . '/' . $relative . ' ') ? $error->getMessage() : $label . '/' . $relative . ': ' . $error->getMessage();
                if (!in_array($issue, $issues, true)) $issues[] = $issue;
                if ((!self::legacyPage($relative) || str_contains($error->getMessage(), '不支持标签：') || str_contains($error->getMessage(), '不支持插件内容标签：')) && !in_array($issue, $blocking, true)) $blocking[] = $issue;
            }
        }
        return ['issues' => $issues, 'blocking_issues' => $blocking];
    }

    private static function legacyPage(string $relative): bool
    {
        return (bool) preg_match('~^(?:pc|mobile)/(?:users/|system/(?:article_pay|download_pay)\.html?$)~i', str_replace('\\', '/', $relative));
    }
}
