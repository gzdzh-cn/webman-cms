<?php
declare(strict_types=1);
namespace app\cms\template;

use InvalidArgumentException;

/** Validate editable HTM as tokens, preserving literal examples and ordinary text. */
final class TemplateSourcePolicy
{
    /** Check unsupported executable tags independently of legacy raw-PHP checks. */
    public static function validateUnsupportedTags(string $source, string $path = '[string]'): void
    {
        $source = preg_replace_callback('/\{(?:(?:eyou|webman):)?literal\}.*?\{\/(?:(?:eyou|webman):)?literal\}/is',
            static fn ($m) => preg_replace('/[^\r\n]/', ' ', $m[0]), $source);
        if (preg_match('/\{((?:eyou|webman):)(uiartlist|uimodels|uiupload|uichannel|uitype|uiarclist|uihtml|uimap|uicode|uitxt|uitext|uibackground|weapplist|weapptaglib)\b/i', $source, $tag, PREG_OFFSET_CAPTURE)) {
            $line = substr_count(substr($source, 0, $tag[0][1]), "\n") + 1;
            throw new InvalidArgumentException($path.' 第'.$line.'行不支持标签：'.$tag[1][0].$tag[2][0]);
        }
        if (preg_match_all('/\{((?:eyou|webman):)weapp\b([^}]*)\}/i', $source, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($tags as $tag) {
                $attrs = $tag[2][0];
                preg_match_all('/\btype\s*=\s*(["\'])(.*?)\1/is', $attrs, $types);
                $type = strtolower((string)(end($types[2]) ?: 'default'));
                $empty = true;
                if (!preg_match('/\/\s*$/', $attrs) && preg_match('/\{\/\s*'.preg_quote($tag[1][0], '/').'weapp\s*\}/i', $source, $close, PREG_OFFSET_CAPTURE, $tag[0][1] + strlen($tag[0][0]))) {
                    $empty = trim(substr($source, $tag[0][1] + strlen($tag[0][0]), $close[0][1] - $tag[0][1] - strlen($tag[0][0]))) === '';
                }
                if ($type !== 'default' || !$empty) {
                    $line = substr_count(substr($source, 0, $tag[0][1]), "\n") + 1;
                    throw new InvalidArgumentException($path.' 第'.$line.'行不支持插件内容标签：'.$tag[1][0].'weapp');
                }
            }
        }
    }

    public static function validate(string $source, string $mode, string $path = '[string]'): void
    {
        $offset = 0;
        try {
            if (preg_match('/<\?/i', $source, $match, PREG_OFFSET_CAPTURE)
                || preg_match('/<[^>]*\blanguage\s*=\s*(?:"php"|\'php\'|php(?=[\s>]))/i', $source, $match, PREG_OFFSET_CAPTURE)) {
                $offset = (int)$match[0][1];
                throw new InvalidArgumentException('禁止嵌入PHP代码');
            }
            self::validateUnsupportedTags($source, $path);
            $length = strlen($source);
            while ($offset < $length) {
                if ($source[$offset] !== '{') { ++$offset; continue; }
                if (preg_match('/\G\{\h*(?:(eyou|webman)\h*:\h*)?literal\h*\}/Ai', $source, $opening, 0, $offset)) {
                    $namespace = strtolower($opening[1] ?? '');
                    if ($namespace === '' || $namespace === $mode) {
                        $close = '/\{\h*\/\h*'.($namespace !== '' ? preg_quote($namespace, '/').'\h*:\h*' : '').'literal\h*\}/i';
                        if (preg_match($close, $source, $closing, PREG_OFFSET_CAPTURE, $offset + strlen($opening[0]))) {
                            $offset = $closing[0][1] + strlen($closing[0][0]); continue;
                        }
                    }
                }
                $end = self::tagEnd($source, $offset);
                if ($end === null) { ++$offset; continue; }
                $token = substr($source, $offset, $end - $offset + 1);
                if (preg_match('/^\{\h*(?:\/\h*)?(?:(?:eyou|webman)\h*:\h*)?php\b/i', $token)) throw new InvalidArgumentException('禁止嵌入PHP代码');
                if (preg_match('/^\{(?:\$|[:~][\$a-zA-Z_]|\+\+\$|--\$)/', $token)) {
                    self::validateCompiledTag($token);
                    $offset = $end + 1; continue;
                }
                if (preg_match('/^\{(?:(?:eyou|webman):)?([a-zA-Z_][\w-]*)\b(.*)\}$/s', $token, $tag)) {
                    $name = strtolower($tag[1]); $text = $tag[2];
                    if (in_array($name, ['if','elseif'], true)) {
                        if (preg_match('/\b(?:condition|expression)\s*=\s*(["\'])(.*?)\1/is', $text, $condition)) $expression = $condition[2];
                        else $expression = trim(rtrim(trim($text), '/'));
                        if ($expression === '') throw new InvalidArgumentException('模板条件格式无效');
                        $lib = new \app\cms\compat\template\TagLib(self::compiler());
                        self::validateExpression($lib->parseCondition($expression));
                    } else self::validateAttributes($name, $text);
                }
                // Unknown JavaScript/CSS braces must not swallow template expressions.
                ++$offset;
            }
            TemplateDialect::normalize($source, $mode, $path);
        } catch (\Throwable $error) {
            if ($error instanceof TemplateModeException || str_starts_with($error->getMessage(), $path.' 第')) throw $error;
            throw new InvalidArgumentException($path.' 第'.(substr_count(substr($source, 0, $offset), "\n") + 1).'行：'.$error->getMessage(), 0, $error);
        }
    }

    private static function tagEnd(string $source, int $start): ?int
    {
        $quote = null; $depth = 0; $length = strlen($source);
        for ($i = $start + 1; $i < $length; ++$i) {
            $char = $source[$i];
            if ($quote !== null) {
                if ($char === '\\') { ++$i; continue; }
                if ($char === $quote) $quote = null;
            } elseif ($char === '"' || $char === "'") $quote = $char;
            elseif ($char === '{') ++$depth;
            elseif ($char === '}') { if ($depth === 0) return $i; --$depth; }
        }
        return null;
    }

    private static function validateAttributes(string $name, string $text): void
    {
        if (in_array($name, ['function','for','define'], true)) throw new InvalidArgumentException('模板标签不受支持：'.$name);
        if ($name === 'foreach' && !preg_match('/\bname\s*=/i', $text)) throw new InvalidArgumentException('foreach 标签仅支持变量名称');
        $attributes = [];
        preg_match_all('/\b([\w-]+)\s*=\s*(["\'])(.*?)\2/is', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $attribute) $attributes[strtolower($attribute[1])] = $attribute[3];
        $lib = new \app\cms\compat\template\TagLib(self::compiler());
        if ($name === 'assign') { TemplateCapabilities::assignment($attributes, $lib); return; }
        if ($name === 'sql' && (!isset($attributes['sql']) || str_contains($attributes['sql'], '$') || preg_match('/^\h*[$:]/', $attributes['sql']))) throw new InvalidArgumentException('SQL标签必须使用静态SQL属性');
        foreach ($attributes as $key => $value) {
            if (TemplateCapabilities::textAttribute($name, $key)) continue;
            if (preg_match('/[;`\\\\]/', $value) || str_starts_with(ltrim($value), ':')) throw new InvalidArgumentException('模板标签属性包含不允许的语法');
            if (in_array($key, ['id','key','item','index'], true) && $value !== '' && !preg_match('/^[a-zA-Z_]\w*$/D', $value)) throw new InvalidArgumentException('模板标签变量名无效');
            if (str_starts_with($value, '$')) self::validateExpression($lib->autoBuildVar($value));
            elseif (str_contains($value, '$')) throw new InvalidArgumentException('模板标签属性包含不允许的变量');
        }
    }

    public static function validateExpression(string $expression): void
    {
        self::validatePhp('<?php echo ('.TemplateCapabilities::rewriteFunctions($expression).');');
    }

    private static function compiler(): \app\cms\compat\Template
    {
        // Rendering defines these constants during bootstrap; validation can also
        // be used by offline tooling before bootstrap.
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        if (!defined('TEMP_PATH')) define('TEMP_PATH', sys_get_temp_dir().DIRECTORY_SEPARATOR);
        $template = (new \ReflectionClass(\app\cms\compat\Template::class))->newInstanceWithoutConstructor();
        $config = new \ReflectionProperty($template, 'config');
        $values = $config->getValue($template);
        $values['tpl_begin'] = preg_quote($values['tpl_begin'], '/');
        $values['tpl_end'] = preg_quote($values['tpl_end'], '/');
        $config->setValue($template, $values);
        return $template;
    }

    private static function validateCompiledTag(string $tag): void
    {
        if (preg_match('/^\{(?:~|\+\+|--)/', $tag)) throw new InvalidArgumentException('模板表达式不能执行语句');
        $template = self::compiler();
        $method = new \ReflectionMethod($template, 'parseTag');
        $method->invokeArgs($template, [&$tag]);
        self::validatePhp($tag);
    }

    /** Validate the compiler output, not a guess at PHP syntax in the source. */
    private static function validatePhp(string $code): void
    {
        try {
            $parser = (new \PhpParser\ParserFactory())->createForHostVersion();
            $nodes = $parser->parse($code);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('模板表达式格式无效', 0, $e);
        }
        foreach ($nodes ?? [] as $node) self::validateNode($node);
    }

    private static function validateNode(\PhpParser\Node $node): void
    {
        $allowed = [
            'Stmt_Echo','Stmt_If','Stmt_Else','Stmt_ElseIf',
            'Expr_Variable','Expr_ArrayDimFetch','Expr_ConstFetch','Expr_FuncCall','Expr_StaticCall',
            'Expr_BinaryOp_BooleanAnd','Expr_BinaryOp_BooleanOr','Expr_BinaryOp_LogicalAnd','Expr_BinaryOp_LogicalOr',
            'Expr_BinaryOp_Equal','Expr_BinaryOp_NotEqual','Expr_BinaryOp_Identical','Expr_BinaryOp_NotIdentical',
            'Expr_BinaryOp_Greater','Expr_BinaryOp_GreaterOrEqual','Expr_BinaryOp_Smaller','Expr_BinaryOp_SmallerOrEqual',
            'Expr_BinaryOp_Plus','Expr_BinaryOp_Minus','Expr_BinaryOp_Mul','Expr_BinaryOp_Div','Expr_BinaryOp_Mod',
            'Expr_BinaryOp_Concat','Expr_BinaryOp_Coalesce','Expr_Ternary','Expr_BooleanNot','Expr_UnaryMinus','Expr_UnaryPlus',
            'Expr_Isset','Expr_Empty','Scalar_String','Scalar_Int','Scalar_Float','Name','Name_FullyQualified','Identifier','Arg',
        ];
        if (!in_array($node->getType(), $allowed, true)) throw new InvalidArgumentException('模板表达式包含不允许的语法：'.$node->getType());
        if ($node instanceof \PhpParser\Node\Expr\Variable && (!is_string($node->name) || in_array(strtoupper($node->name), ['GLOBALS','_SERVER','_GET','_POST','_FILES','_COOKIE','_SESSION','_REQUEST','_ENV'], true))) {
            throw new InvalidArgumentException('模板变量不受支持');
        }
        if ($node instanceof \PhpParser\Node\Expr\FuncCall || $node instanceof \PhpParser\Node\Expr\StaticCall) TemplateCapabilities::validateCall($node);
        if ($node instanceof \PhpParser\Node\Expr\ConstFetch && !in_array(strtolower($node->name->toString()), ['true','false','null'], true)) throw new InvalidArgumentException('模板常量不受支持');
        foreach ($node->getSubNodeNames() as $key) {
            $value = $node->$key;
            foreach (is_array($value) ? $value : [$value] as $child) if ($child instanceof \PhpParser\Node) self::validateNode($child);
        }
    }

}
