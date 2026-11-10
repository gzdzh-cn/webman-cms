<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\Paths;

/** Trusted, private baseline dictionaries. Uploaded JSON is never made executable. */
final class LocalLanguageCatalog
{
    public static function read(string $language): array
    {
        if (!preg_match('/^[a-z][a-z0-9_-]{1,49}$/D', $language)) return [];
        $file = Paths::resource('languages/'.$language.'.json');
        if (!is_file($file)) return [];
        if (is_link($file)) throw new \RuntimeException('本地语言基础包不能是符号链接');
        $pack = LanguageJson::decode((string)file_get_contents($file));
        if (($pack['format'] ?? null) !== 1 || ($pack['language'] ?? null) !== $language || !is_array($pack['core'] ?? null) || !is_array($pack['template'] ?? null)) throw new \RuntimeException('本地语言基础包格式错误：'.$language);
        $values = [];
        foreach ([$pack['core'], $pack['template']] as $section) foreach ($section as $name => $value) {
            if (!is_string($name) || !is_string($value)) throw new \RuntimeException('本地语言基础包条目错误：'.$language);
            $values[strtolower($name)] = $value;
        }
        return $values;
    }

    public static function variables(string $language): array
    {
        return array_filter(self::read($language), static fn($name) => (bool)preg_match('/^[a-z0-9_-]{1,50}$/D', (string)$name), ARRAY_FILTER_USE_KEY);
    }
}
