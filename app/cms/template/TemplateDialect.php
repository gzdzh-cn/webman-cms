<?php
declare(strict_types=1);
namespace app\cms\template;

/** Source-only dialect adapter. Business tag compilation remains shared. */
final class TemplateDialect
{
    public const VERSION = 'dialect-v3';
    private string $mode;
    private string $path;
    private string $source;
    private array $seen = [];
    private bool $detectOnly;
    private string $outputRoot = 'eyou';

    private function __construct(string $mode, string $path, string $source, bool $detectOnly = false)
    {
        if (!in_array($mode, ['eyou', 'webman'], true)) throw new \InvalidArgumentException('未知模板模式');
        $this->mode = $mode; $this->path = self::relativePath($path); $this->source = $source; $this->detectOnly = $detectOnly;
    }

    public static function normalize(string $source, string $mode, string $path = '[string]'): string
    {
        return (new self($mode, $path, $source))->scan();
    }

    /** Restore an internally compiled expression when rebuilding a server-owned tag. */
    public static function externalizeInternalExpression(string $expression, string $mode): string
    {
        $adapter = new self('eyou', '[stored tag expression]', $expression);
        if (!in_array($mode, ['eyou', 'webman'], true)) throw new \InvalidArgumentException('未知模板模式');
        $adapter->outputRoot = $mode;
        return $adapter->php($expression, 0);
    }

    public static function detect(string $source): string
    {
        $adapter = new self('eyou', '[inspection]', $source, true);
        $adapter->scan();
        return count($adapter->seen) > 1 ? 'mixed' : (array_key_first($adapter->seen) ?? 'unknown');
    }

    private static function relativePath(string $path): string
    {
        if (function_exists('base_path')) {
            $root = rtrim(base_path(), '/');
            if (str_starts_with($path, $root . '/')) return substr($path, strlen($root) + 1);
        }
        return str_starts_with($path, '/') ? basename($path) : $path;
    }

    private function encounter(string $namespace, int $offset, string $kind): void
    {
        $namespace = strtolower($namespace); $this->seen[$namespace] = true;
        if (!$this->detectOnly && $namespace !== $this->mode) {
            $line = substr_count(substr($this->source, 0, $offset), "\n") + 1;
            throw new TemplateModeException($this->path, $line,
                '当前为 ' . ($this->mode === 'webman' ? 'Webman' : 'EyouCMS') . ' 模式，不支持 ' . $namespace . ' ' . $kind);
        }
    }

    private function scan(): string
    {
        $source = $this->source; $length = strlen($source); $output = ''; $i = 0;
        while ($i < $length) {
            if (preg_match('/\G<\?(?:php\b|=)/Ai', $source, $php, 0, $i)) {
                $end = $i;
                foreach (token_get_all(substr($source, $i)) as $part) {
                    $end += strlen(is_array($part) ? $part[1] : $part);
                    if (is_array($part) && $part[0] === T_CLOSE_TAG) break;
                }
                $raw = substr($source, $i, $end - $i);
                $output .= $this->php($raw, $i, true); $i = $end; continue;
            }
            if ($source[$i] !== '{' || !$this->isTemplateStart(substr($source, $i))) {
                $output .= $source[$i++]; continue;
            }
            $end = $this->braceEnd($i);
            if ($end === null) { $output .= $source[$i++]; continue; }
            $raw = substr($source, $i, $end - $i + 1);
            if (preg_match('/^\{\h*(\/\h*)?(eyou|webman)\h*:\h*([a-z_]\w*)\b/i', $raw, $tag)) {
                $namespace = strtolower($tag[2]); $name = strtolower($tag[3]); $closing = !empty($tag[1]);
                $this->encounter($namespace, $i, '标签');
                if (!$closing && in_array($name, ['literal', 'php'], true)) {
                    $closePattern = '/\{\h*\/\h*' . preg_quote($namespace, '/') . '\h*:\h*' . $name . '\h*\}/i';
                    if (preg_match($closePattern, $source, $close, PREG_OFFSET_CAPTURE, $end + 1)) {
                        $bodyOffset = $end + 1; $body = substr($source, $bodyOffset, $close[0][1] - $bodyOffset);
                        $canonical = $this->detectOnly ? $raw : '{eyou:' . $name . '}';
                        $output .= $canonical . ($name === 'php' ? $this->php($body, $bodyOffset) : $body)
                            . ($this->detectOnly ? $close[0][0] : '{/eyou:' . $name . '}');
                        $i = $close[0][1] + strlen($close[0][0]); continue;
                    }
                }
                // Validate attributes at their original offsets before the namespace
                // becomes shorter; diagnostics must retain the source line number.
                $raw = $this->attributes($raw, $i);
                if (!$this->detectOnly) $raw = '{' . ($closing ? '/' : '') . 'eyou:' . $name . substr($raw, strlen($tag[0]));
            } elseif (preg_match('/^\{\h*literal\b/i', $raw)) {
                if (preg_match('/\{\h*\/literal\h*\}/i', $source, $close, PREG_OFFSET_CAPTURE, $end + 1)) {
                    $output .= substr($source, $i, $close[0][1] + strlen($close[0][0]) - $i);
                    $i = $close[0][1] + strlen($close[0][0]); continue;
                }
            } elseif (preg_match('/^\{\h*php\h*\}/i', $raw)) {
                if (preg_match('/\{\h*\/php\h*\}/i', $source, $close, PREG_OFFSET_CAPTURE, $end + 1)) {
                    $output .= $raw . $this->php(substr($source, $end + 1, $close[0][1] - $end - 1), $end + 1) . $close[0][0];
                    $i = $close[0][1] + strlen($close[0][0]); continue;
                }
            } elseif (preg_match('/^\{\h*(?:\$|[:~]|\+\+|--)/', $raw)) {
                $raw = $this->php($raw, $i);
            } elseif (!preg_match('/^\{\h*\/(?:\*|\/)/', $raw)) {
                $raw = $this->attributes($raw, $i);
            }
            $output .= $raw; $i = $end + 1;
        }
        return $output;
    }

    /** Read the same definitions/aliases used by the compiler; no parallel tag list. */
    private static function definitions(bool $namespaced): array
    {
        static $catalogs = [];
        $kind = $namespaced ? 'Eyou' : 'Cx';
        if (!isset($catalogs[$kind])) {
            $class = $namespaced ? \app\cms\compat\template\taglib\Eyou::class : \app\cms\compat\template\taglib\Cx::class;
            $properties = (new \ReflectionClass($class))->getDefaultProperties();
            $catalog = [];
            foreach ($properties['tags'] as $name => $definition) {
                $definition['canonical'] = $name;
                $catalog[$name] = $definition;
                if (!empty($definition['alias'])) {
                    $aliases = is_array($definition['alias']) ? $definition['alias'][0] : $definition['alias'];
                    foreach (explode(',', $aliases) as $alias) $catalog[$alias] = $definition;
                }
            }
            $catalogs[$kind] = $catalog;
        }
        return $catalogs[$kind];
    }

    private function isTemplateStart(string $tail): bool
    {
        static $pattern;
        $pattern ??= '/^\{\h*(?:\/?\h*(?:eyou|webman)\h*:|\$|[:~]|\+\+|--|\/(?:\*|\/)|\/?(?:literal|include|extend|layout|taglib|block|' . implode('|', array_keys(self::definitions(false))) . ')\b)/i';
        return (bool)preg_match($pattern, $tail);
    }

    private function braceEnd(int $start): ?int
    {
        $quote = null; $depth = 0; $length = strlen($this->source);
        for ($i = $start + 1; $i < $length; ++$i) {
            $char = $this->source[$i];
            if ($quote !== null) {
                if ($char === '\\') { ++$i; continue; }
                if ($char === $quote) $quote = null;
                continue;
            }
            if ($char === '"' || $char === "'") { $quote = $char; continue; }
            if ($char === '{') ++$depth;
            if ($char === '}') { if ($depth === 0) return $i; --$depth; }
        }
        return null;
    }

    private function attributes(string $token, int $offset): string
    {
        $isTaglib = (bool)preg_match('/^\{\h*taglib\b/i', $token);
        preg_match('/^\{\h*(?:(eyou|webman):)?([a-z_]\w*)/i', $token, $tagName);
        $definition = self::definitions(!empty($tagName[1]))[strtolower($tagName[2] ?? '')] ?? [];
        $canonicalName = $definition['canonical'] ?? strtolower($tagName[2] ?? '');
        // TagLib::parseAttr treats supported tags with no quoted attributes as
        // direct PHP-like expressions (if/foreach/switch/case/url and aliases).
        // Scan those expressions with PHP's lexer too, preserving quoted text.
        if (!empty($definition['expression']) && !preg_match('/\s+[\w-]+\s*=\s*([\'\"])(?:(?!\1).)*\1/s', $token)) {
            $head = $tagName[0];
            return $head . $this->php(substr($token, strlen($head)), $offset + strlen($head));
        }
        $expressionName = in_array($canonicalName, ['foreach','volist','empty','notempty','present','notpresent','compare','range','assign','switch','defined','notdefined','diyfield','for'], true);
        return preg_replace_callback('/\b([\w-]+)\s*=\s*([\'\"])((?:\\\\.|(?!\2).)*)\2/s', function (array $attribute) use ($offset, $isTaglib, $expressionName, $token): string {
            $name = strtolower($attribute[1]); $value = $attribute[3];
            $valueOffset = $offset + (int)strpos($token, $attribute[0]) + (int)strpos($attribute[0], $attribute[2]) + 1;
            if (str_contains($value, '{')) {
                $nested = new self($this->mode, $this->path, $value, $this->detectOnly);
                try { $value = $nested->scan(); }
                catch (TemplateModeException $error) {
                    throw new TemplateModeException($this->path, substr_count(substr($this->source, 0, $valueOffset), "\n") + $error->templateLine, $error->reason);
                }
                $this->seen += $nested->seen;
            }
            if ($isTaglib && $name === 'name') {
                foreach (explode(',', $value) as $library) {
                    $short = strtolower(basename(str_replace('\\', '/', trim($library))));
                    if (in_array($short, ['eyou', 'webman'], true)) $this->encounter($short, $valueOffset, '标签库');
                }
                if (!$this->detectOnly) $value = preg_replace('/(^|,)\s*(?:\\\\?app\\\\cms\\\\compat\\\\template\\\\taglib\\\\)?webman\s*(?=,|$)/i', '$1eyou', $value);
            } elseif (in_array($name, ['condition','use','vars','call'], true) || preg_match('/^\s*(?:\$|:)/', $value)) {
                $value = $this->php($value, $valueOffset);
            } elseif ($name === 'name' && $expressionName && preg_match('/^(eyou|webman)(?=\.|\[|$)/i', $value, $root)) {
                $this->encounter($root[1], $valueOffset, '变量');
                if (!$this->detectOnly && $this->mode === 'webman') $value = 'eyou' . substr($value, strlen($root[1]));
            }
            return $attribute[1] . '=' . $attribute[2] . $value . $attribute[2];
        }, $token);
    }

    /** A quoted name is a variable root only in ${'name'}, never in ordinary strings. */
    private function isStaticVariableName(array $tokens, int $index): bool
    {
        $significant = static function (array $tokenList, int $start, int $step): mixed {
            for ($i = $start; isset($tokenList[$i]); $i += $step) {
                $part = $tokenList[$i];
                if (is_array($part) && in_array($part[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
                return [$i, $part];
            }
            return null;
        };
        $before = $significant($tokens, $index - 1, -1);
        $after = $significant($tokens, $index + 1, 1);
        if (!$before || !$after || $after[1] !== '}') return false;
        if (is_array($before[1]) && $before[1][0] === T_DOLLAR_OPEN_CURLY_BRACES) return true;
        if ($before[1] !== '{') return false;
        $dollar = $significant($tokens, $before[0] - 1, -1);
        return $dollar && $dollar[1] === '$';
    }

    /** PHP's lexer preserves quoted strings/comments and identifies interpolated variables. */
    private function php(string $expression, int $offset, bool $hasOpenTag = false): string
    {
        $prefix = $hasOpenTag ? '' : '<?php '; $tokens = token_get_all($prefix . $expression); $result = ''; $position = -strlen($prefix);
        foreach ($tokens as $index => $token) {
            $text = is_array($token) ? $token[1] : $token;
            if (!$hasOpenTag && is_array($token) && $token[0] === T_OPEN_TAG) { $position += strlen($text); continue; }
            $root = null; $prefixText = ''; $suffixText = '';
            if (is_array($token)) {
                if ($token[0] === T_VARIABLE && in_array(strtolower($text), ['$eyou', '$webman'], true)) {
                    $root = substr($text, 1); $prefixText = '$';
                } elseif ($token[0] === T_STRING_VARNAME && in_array(strtolower($text), ['eyou', 'webman'], true)) {
                    // PHP emits ${name} inside interpolated strings/heredocs as
                    // T_STRING_VARNAME, not T_VARIABLE. It is still a root access.
                    $root = $text;
                } elseif ($token[0] === T_CONSTANT_ENCAPSED_STRING
                    && preg_match('/^([\'\"])(eyou|webman)\\1$/iD', $text, $name)
                    && $this->isStaticVariableName($tokens, $index)) {
                    // Only statically named ${'root'} accesses are adapted;
                    // ordinary quoted examples and object property names stay intact.
                    $root = $name[2]; $prefixText = $suffixText = $name[1];
                }
            }
            if ($root !== null) {
                $this->encounter($root, $offset + max(0, $position), '变量');
                if (!$this->detectOnly && ($this->mode === 'webman' || $this->outputRoot === 'webman')) $text = $prefixText . $this->outputRoot . $suffixText;
            }
            $result .= $text; $position += strlen(is_array($token) ? $token[1] : $token);
        }
        return $result;
    }
}
