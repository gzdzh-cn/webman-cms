<?php
declare(strict_types=1);
namespace app\cms\template;

/** A comment-only preamble: cached bytes can never inject PHP through metadata. */
final class TemplateDependencies
{
    public static function decode(string $header): ?array
    {
        if (strlen($header) > 1048576 || !preg_match('~^<\?php /\*CMS-DEPS-2:([A-Za-z0-9+/]*={0,2})\*/ \?>$~D', $header, $match)) return null;
        $json = base64_decode($match[1], true);
        if ($json === false || base64_encode($json) !== $match[1]) return null;
        try { $dependencies = json_decode($json, true, 16, JSON_THROW_ON_ERROR); }
        catch (\Throwable) { return null; }
        if (!is_array($dependencies) || count($dependencies) > 2048) return null;
        foreach ($dependencies as $path => $entry) {
            if (!is_string($path) || strlen($path) > 8192 || str_contains($path, "\0")
                || !is_array($entry) || array_keys($entry) !== ['hash', 'aliases']
                || !is_string($entry['hash']) || !preg_match('/^[a-f0-9]{64}$/D', $entry['hash'])
                || !is_array($entry['aliases']) || !array_is_list($entry['aliases'])
                || !$entry['aliases'] || count($entry['aliases']) > 2048) return null;
            foreach ($entry['aliases'] as $alias) if (!is_string($alias) || $alias === '' || strlen($alias) > 8192 || str_contains($alias, "\0")) return null;
        }
        return $dependencies;
    }
}
