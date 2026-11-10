<?php
declare(strict_types=1);
namespace app\cms\service;

/** JSON-only reader: decoding must not silently discard duplicate object keys. */
final class LanguageJson
{
    public static function decode(string $json): array
    {
        if (strlen($json) > (int)config('cms_language_pack.max_bytes', 2097152)) throw new \InvalidArgumentException('语言包超过2 MiB大小限制');
        try { $result = json_decode($json, true, 64, JSON_THROW_ON_ERROR); }
        catch (\JsonException $error) { throw new \InvalidArgumentException('语言包必须是有效JSON，不能上传PHP或其他代码'); }
        if (!is_array($result)) throw new \InvalidArgumentException('语言包必须是JSON对象');
        $offset = 0;
        self::value($json, $offset);
        return $result;
    }

    private static function whitespace(string $json, int &$offset): void
    {
        while (isset($json[$offset]) && str_contains(" \n\r\t", $json[$offset])) $offset++;
    }

    private static function string(string $json, int &$offset): string
    {
        $start = $offset++;
        while (isset($json[$offset])) {
            if ($json[$offset] === '\\') { $offset += 2; continue; }
            if ($json[$offset++] === '"') return (string)json_decode(substr($json, $start, $offset - $start), true, 64, JSON_THROW_ON_ERROR);
        }
        throw new \InvalidArgumentException('JSON字符串未结束');
    }

    private static function value(string $json, int &$offset): void
    {
        self::whitespace($json, $offset);
        $char = $json[$offset] ?? '';
        if ($char === '"') { self::string($json, $offset); return; }
        if ($char === '{') {
            $offset++; $keys = [];
            self::whitespace($json, $offset);
            if (($json[$offset] ?? '') === '}') { $offset++; return; }
            do {
                self::whitespace($json, $offset);
                $key = self::string($json, $offset);
                if (array_key_exists($key, $keys)) throw new \InvalidArgumentException('JSON包含重复键：'.$key);
                $keys[$key] = true;
                self::whitespace($json, $offset); $offset++;
                self::value($json, $offset);
                self::whitespace($json, $offset);
                $delimiter = $json[$offset++] ?? '';
            } while ($delimiter === ',');
            return;
        }
        if ($char === '[') {
            $offset++; self::whitespace($json, $offset);
            if (($json[$offset] ?? '') === ']') { $offset++; return; }
            do { self::value($json, $offset); self::whitespace($json, $offset); $delimiter = $json[$offset++] ?? ''; } while ($delimiter === ',');
            return;
        }
        while (isset($json[$offset]) && !str_contains(",]} \t\r\n", $json[$offset])) $offset++;
    }
}
