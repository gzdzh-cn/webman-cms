<?php
namespace app\cms\compat;

use app\cms\CmsContext;
use app\cms\ThemeCatalog;

/** Bridges the isolated Eyou compiler to the current Webman request. */
final class Runtime
{
    public const FUNCTION_NAMES = ['config','input','request','cache','cookie','session','model','M','lang','db'];
    public const DYNAMIC_CONSTANTS = ['ROOT_DIR','TPL_THEME','THEME_STYLE','THEME_STYLE_PATH','MODULE_NAME','CONTROLLER_NAME','ACTION_NAME'];

    public static function boot(?CmsContext $context = null): void
    {
        // Upstream get_weapp_class builds the extension namespace from this
        // process-stable directory name, even when the plugin class is absent.
        if (!defined('WEAPP_DIR_NAME')) define('WEAPP_DIR_NAME', 'weapp');
        require_once __DIR__ . '/functions.php';
        if ($context !== null && method_exists(CmsContext::class, 'set')) CmsContext::set($context);
        foreach (['DS'=>DIRECTORY_SEPARATOR,'EXT'=>'.php','THINK_PATH'=>__DIR__.DIRECTORY_SEPARATOR,'VENDOR_PATH'=>base_path('vendor/'),'ROOT_PATH'=>base_path().DIRECTORY_SEPARATOR,'UPLOAD_PATH'=>'uploads/','DATA_PATH'=>\app\cms\Paths::legacyData().DIRECTORY_SEPARATOR,'APP_PATH'=>base_path('app/'),'EXTEND_PATH'=>base_path('extend/'),'WEAPP_PATH'=>base_path('weapp/'),'RUNTIME_PATH'=>runtime_path('cms/'),'CACHE_PATH'=>runtime_path('cms/cache/'),'TEMP_PATH'=>runtime_path('cms/templates/'),'EYOUCMS_CACHE_TIME'=>3600,'THINK_VERSION'=>'5.0-compat','HTML_ROOT'=>''] as $name=>$value) {
            if (!defined($name)) define($name, $value);
        }
    }

    public static function context(): ?CmsContext
    {
        return class_exists(CmsContext::class) ? CmsContext::current() : null;
    }

    public static function config($name = '', $value = null, $range = '')
    {
        $context = self::context();
        if (is_array($name)) {
            foreach ($name as $key=>$item) $context?->setConfig($key, $item);
            return null;
        }
        if ($value !== null) {
            $context?->setConfig((string)$name, $value);
            return $value;
        }
        return $context?->getConfig((string)$name) ?? null;
    }

    public static function input($name = '', $default = '', $filter = '')
    {
        return Input::get($name, $default, $filter);
    }

    public static function request(): Request { return Request::instance(); }

    public static function templateRequest(string $source, string $key): mixed { return \app\cms\template\TemplateRuntime::request($source, $key); }
    public static function templateThumb($image = '', $width = '', $height = '', $mode = ''): string { return \app\cms\template\TemplateRuntime::thumb($image, $width, $height, $mode); }
    public static function templateUrl($route = '', $parameters = [], $suffix = true, $domain = false, $mode = null, $format = null, $inlet = null): string { return \app\cms\template\TemplateRuntime::url($route, $parameters, $suffix, $domain, $mode, $format, $inlet); }
    public static function templateHtmlSubstr($value = '', $start = 0, $length = null, $suffix = false, $charset = 'utf-8'): string { if ($value !== null && !is_scalar($value)) throw new \InvalidArgumentException('文本截取值无效'); return (string)\html_msubstr((string)$value, $start, $length, $suffix, $charset); }
    public static function templateFeature(string $key): int { if ($key !== 'shop.shop_open') throw new \InvalidArgumentException('模板功能开关不受支持'); return 0; }
    public static function templateEmpty($type = 'default'): string { return ''; }


    /** The enterprise application keeps upstream data but disables transactional features. */
    public static function commerceEnabled(): bool
    {
        $global=self::context()?->global ?? [];
        return !empty($global['web_users_switch']) && !empty($global['shop_open']);
    }

    public static function constant(string $name)
    {
        $context = self::context();
        if (array_key_exists($name, $context?->memo['template.constants'] ?? [])) {
            return $context->memo['template.constants'][$name];
        }
        return match($name) {
            'ROOT_DIR'=>(string)($context?->global['web_cmspath'] ?? ''),
            'TPL_THEME'=>($context && str_contains($context->theme, '/')) ? trim(dirname($context->theme), '/').'/' : '',
            'THEME_STYLE'=>(string)($context?->device ?? 'pc'),
            'THEME_STYLE_PATH'=>$context?->theme ?? 'pc',
            'MODULE_NAME'=>$context?->module ?? 'home',
            'CONTROLLER_NAME'=>$context?->controller ?? 'Index',
            'ACTION_NAME'=>$context?->action ?? 'index',
            default=>defined($name)?constant($name):null,
        };
    }

    /** PHP7 falls back to the spelling of an undefined unqualified array key. */
    public static function offsetConstant(string $name, string $namespace = '')
    {
        if ($namespace !== '' && self::isDefined($namespace . '\\' . $name))
            return self::constant($namespace . '\\' . $name);
        return self::isDefined($name) ? self::constant($name) : $name;
    }

    /** Marker for a bare token whose literal define() name occurs in this file. */
    public static function declaredConstant(string $name)
    {
        return self::constant($name);
    }

    /** PHP7 membership for range/in/notin; native application in_array stays untouched. */
    public static function rangeContains($needle, array $values, bool $strict = false): bool
    {
        if ($strict) return in_array($needle, $values, true);
        foreach ($values as $value) {
            if (self::looseCompare($needle, $value, '==')) return true;
        }
        return false;
    }

    /** Preserve the original comparison opcode, including unordered/NaN results. */
    public static function looseCompare($left, $right, string $operator): bool|int
    {
        if ($operator === '===') return $left === $right;
        if ($operator === '!==') return $left !== $right;
        $objects = [];
        return match ($operator) {
            '==' => self::looseOrder($left, $right, $objects) === 0,
            '!=', '<>' => self::looseOrder($left, $right, $objects) !== 0,
            '<' => self::looseOrder($left, $right, $objects) < 0,
            '<=' => self::looseOrder($left, $right, $objects) <= 0,
            '>' => self::looseOrder($right, $left, $objects) < 0,
            '>=' => self::looseOrder($right, $left, $objects) <= 0,
            '<=>' => self::looseOrder($left, $right, $objects),
            default => throw new \InvalidArgumentException('Unsupported template comparison'),
        };
    }

    private static function looseOrder($left, $right, array &$objects): int
    {
        if ($left === $right) return 0;
        if ((is_int($left) || is_float($left)) && is_string($right)) return $left <=> self::rangeNumber($right);
        if (is_string($left) && (is_int($right) || is_float($right))) return self::rangeNumber($left) <=> $right;
        if (is_string($left) && is_string($right)) {
            // PHP8 also accepts trailing whitespace as part of a numeric string.
            // PHP7 instead compares those strings byte for byte.
            $numeric = '/^[\x09-\x0d ]*[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?$/D';
            return preg_match($numeric, $left) && preg_match($numeric, $right) ? $left <=> $right : strcmp($left, $right) <=> 0;
        }
        if (is_array($left) && is_array($right)) {
            if (count($left) !== count($right)) return count($left) <=> count($right);
            foreach ($left as $key => $value) {
                if (!array_key_exists($key, $right)) return 1;
                $comparison = self::looseOrder($value, $right[$key], $objects);
                if ($comparison !== 0) return $comparison;
            }
            return 0;
        }
        if (is_object($left) && is_object($right) && get_class($left) === get_class($right)) {
            $class = new \ReflectionClass($left);
            // Extension classes may supply their own comparison handlers.
            // Keep those handlers, including inherited internal behavior.
            $standard = $class->getName() === \stdClass::class;
            if (!$class->isInternal()) {
                $standard = true;
                while ($class = $class->getParentClass()) {
                    if ($class->isInternal() && $class->getName() !== \stdClass::class) { $standard = false; break; }
                }
            }
            if ($standard) {
                $pair = spl_object_id($left) . ':' . spl_object_id($right);
                // Let PHP retain its native recursive-comparison failure.
                if (isset($objects[$pair])) return $left <=> $right;
                $objects[$pair] = true;
                try { return self::looseOrder((array)$left, (array)$right, $objects); }
                finally { unset($objects[$pair]); }
            }
        }
        if (is_string($left) && is_object($right) && method_exists($right, '__toString')) return self::looseOrder($left, (string)$right, $objects);
        if (is_object($left) && is_string($right) && method_exists($left, '__toString')) return self::looseOrder((string)$left, $right, $objects);
        return $left <=> $right;
    }

    public static function comparisonTouch($value): void {}
    public static function comparisonValue($value): ComparisonValue { return new ComparisonValue($value); }
    public static function comparisonReference(&$value): ComparisonValue
    {
        $captured = new ComparisonValue(); $captured->value =& $value; return $captured;
    }
    public static function compareCaptured(ComparisonValue $left, ComparisonValue $right, string $operator): bool|int
    {
        return self::looseCompare($left->value, $right->value, $operator);
    }
    public static function compareCapturedRightFirst(ComparisonValue $right, ComparisonValue $left, string $operator): bool|int
    {
        return self::looseCompare($left->value, $right->value, $operator);
    }
    public static function releaseComparison(ComparisonValue $value, &...$temporaries): ComparisonValue
    {
        foreach ($temporaries as &$temporary) $temporary = null;
        return $value;
    }
    public static function comparisonScope(array $variables, string $prefix): array
    {
        foreach ($variables as $name => $_) if (str_starts_with($name, $prefix)) unset($variables[$name]);
        return $variables;
    }
    public static function returnsReference($callable, string $scope = ''): bool
    {
        try {
            if (is_array($callable) && count($callable) === 2 && (is_object($callable[0]) || is_string($callable[0])) && is_string($callable[1])) {
                $method = $callable[1];
                if (method_exists($callable[0], $method)) {
                    $reflection = new \ReflectionMethod($callable[0], $method);
                    if ($scope !== '' && is_a($callable[0], $scope, true) && method_exists($scope, $method)) {
                        $scoped = new \ReflectionMethod($scope, $method);
                        if ($scoped->isPrivate() && $scoped->getDeclaringClass()->getName() === $scope) $reflection = $scoped;
                    }
                    $declaring = $reflection->getDeclaringClass()->getName();
                    $visible = $reflection->isPublic() || $scope === $declaring || ($reflection->isProtected() && $scope !== '' && (is_a($scope, $declaring, true) || is_a($declaring, $scope, true)));
                    if ($visible) return $reflection->returnsReference();
                }
                $method = is_object($callable[0]) ? '__call' : '__callStatic';
                return (new \ReflectionMethod($callable[0], $method))->returnsReference();
            }
            if (is_string($callable) && str_contains($callable, '::')) return self::returnsReference(explode('::', $callable, 2), $scope);
            if ($callable instanceof \Closure || is_string($callable)) {
                if (is_string($callable) && !function_exists($callable) && str_contains($callable, '\\')) $callable = substr($callable, strrpos($callable, '\\') + 1);
                return (new \ReflectionFunction($callable))->returnsReference();
            }
            if (is_object($callable)) return (new \ReflectionMethod($callable, '__invoke'))->returnsReference();
        } catch (\ReflectionException $error) { /* Let the actual native call report errors. */ }
        return false;
    }
    public static function referenceOffset($parent, $key): bool
    {
        if (is_array($parent)) {
            if (!is_scalar($key) && $key !== null) return false;
            if (is_float($key) || is_bool($key)) $key = (int)$key;
            if ($key === null) $key = '';
            return array_key_exists($key, $parent) && \ReflectionReference::fromArrayElement($parent, $key) !== null;
        }
        return $parent instanceof \ArrayAccess && self::returnsReference([$parent, 'offsetGet']);
    }
    public static function referenceProperty($parent, $key, string $scope = ''): bool
    {
        if (!is_object($parent)) return false;
        $visible = static fn($object) => get_object_vars($object);
        if ($scope !== '') $visible = \Closure::bind($visible, null, $scope);
        $properties = $visible($parent); $key = (string)$key;
        if (array_key_exists($key, $properties)) return \ReflectionReference::fromArrayElement($properties, $key) !== null;
        return method_exists($parent, '__get') && self::returnsReference([$parent, '__get']);
    }

    /** PHP7 number/string comparisons convert the numeric prefix, or zero. */
    private static function rangeNumber(string $value): int|float
    {
        if (!preg_match('/^[\x09-\x0d ]*[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?/', $value, $match)) return 0;
        return $match[0] + 0;
    }

    /** PHP7 count()/sizeof() return values for compiler-generated filters. */
    public static function filterCount(...$arguments)
    {
        if (!$arguments || count($arguments) > 2) return null;
        $mode = $arguments[1] ?? COUNT_NORMAL;
        if (is_string($mode)) {
            // PHP7 internal integer parameters accept a numeric prefix, even
            // when trailing text would have emitted a hidden warning.
            if (!preg_match('/^[\x09-\x0d ]*[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?/', $mode)) return null;
        } elseif (!is_int($mode) && !is_float($mode) && !is_bool($mode) && $mode !== null) return null;
        $value = $arguments[0];
        if ($value === null) return 0;
        if (!is_array($value) && !$value instanceof \Countable) return 1;
        // User Countable methods execute normally and may throw real errors.
        return count($value, (int)$mode === COUNT_RECURSIVE ? COUNT_RECURSIVE : COUNT_NORMAL);
    }

    /** PHP7 strlen() return values without swallowing Stringable exceptions. */
    public static function filterStrlen(...$arguments)
    {
        if (count($arguments) !== 1) return null;
        $value = $arguments[0];
        if (is_array($value) || is_resource($value) || gettype($value) === 'resource (closed)') return null;
        if (is_object($value)) return $value instanceof \Stringable ? strlen($value) : null;
        return strlen((string)$value);
    }

    /** Template constants belong to one request in a persistent worker. */
    public static function define(string $name, $value): bool
    {
        if (self::isDefined($name)) return false;
        $context = self::context();
        if ($context === null) throw new \RuntimeException('Template constants require a CMS context');
        $context->memo['template.constants'][$name] = $value;
        return true;
    }

    public static function isDefined(string $name): bool
    {
        return in_array($name, self::DYNAMIC_CONSTANTS, true)
            || array_key_exists($name, self::context()?->memo['template.constants'] ?? [])
            || defined($name);
    }

    public static function model(string $name)
    {
        $name = basename(str_replace('\\','/', $name));
        $class = __NAMESPACE__ . '\\model\\' . str_replace(' ', '', ucwords(str_replace('_',' ', $name)));
        if (class_exists($class)) return new $class();
        $context = self::context();
        $key = 'content.model.' . strtolower($name);
        if (!array_key_exists($key, $context->memo)) {
            $context->memo[$key] = Db::name('channeltype')->where('ctl_name', $name)->find() ?: null;
        }
        $channel = $context->memo[$key];
        return is_array($channel) ? new \app\cms\compat\model\CustomContent($channel) : new Model($name);
    }

    /** PHP 7 value-read semantics; callers must establish a read-only context. */
    public static function readOffset($container, $key)
    {
        if (is_string($container)) {
            // PHP 7 coerces even a nonnumeric string key to its integer offset.
            // Do not catch TypeError: user ArrayAccess/helpers execute normally.
            $offset = is_object($key) ? 1 : (int) $key;
            $length = strlen($container);
            if ($offset < 0) $offset += $length;
            return $offset >= 0 && $offset < $length ? $container[$offset] : '';
        }
        if (is_array($container)) {
            if (is_array($key) || is_object($key)) return null;
            if (is_float($key) || is_bool($key) || is_resource($key)) $key = (int) $key;
            return $container[$key] ?? null;
        }
        if (is_object($container)) return $container[$key];
        return null;
    }

    public static function offsetReferenceTarget(&$container, $key): OffsetTarget
    {
        return self::rememberOffsetTarget(new OffsetTarget($container, $key));
    }

    public static function offsetValueTarget($container, $key): OffsetTarget
    {
        return self::rememberOffsetTarget(new OffsetTarget($container, $key));
    }

    private static function rememberOffsetTarget(OffsetTarget $target): OffsetTarget
    {
        $context = self::context();
        if (!$context) throw new \LogicException('Offset targets require a template context');
        $stack =& $context->memo['template.offsetTargets'];
        if (!is_array($stack)) $stack = [];
        // A failed native fetch may abandon its temporary before reading a key.
        // Weak entries never retain request values; prune abandoned entries.
        while ($stack && end($stack)->get() === null) array_pop($stack);
        $stack[] = \WeakReference::create($target);
        return $target;
    }

    public static function offsetSelectedTarget(OffsetTarget $target): OffsetTarget { return $target; }

    public static function offsetTargetKey()
    {
        $context = self::context();
        $stack =& $context->memo['template.offsetTargets'];
        while ($stack) {
            $target = array_pop($stack)->get();
            if ($target) return $target->key;
        }
        throw new \LogicException('Offset target expired before native fetch');
    }

    public static function argumentByReference($callable, $position): bool
    {
        $signature = is_array($callable) && isset($callable['offset.signature']) ? $callable['offset.signature'] : self::offsetCallableSignature($callable);
        if (is_string($position)) {
            foreach ($signature as $parameter) if ($parameter['name'] === $position) return $parameter['reference'];
            $last = end($signature);
            return $last && $last['variadic'] && $last['reference'];
        }
        $parameter = $signature[$position] ?? null;
        if (!$parameter) {
            $last = end($signature);
            $parameter = $last && $last['variadic'] ? $last : null;
        }
        return $parameter && $parameter['reference'];
    }

    /** Snapshot only parameter metadata, so temporary callable objects can die normally. */
    public static function captureOffsetCallable($callable, &$signature)
    {
        $signature = ['offset.signature' => self::offsetCallableSignature($callable)];
        return $callable;
    }

    /** Snapshot class identity before earlier arguments can reassign a receiver variable. */
    public static function captureOffsetReceiver($receiver, &$class)
    {
        $class = is_object($receiver) ? get_class($receiver) : $receiver;
        return $receiver;
    }

    private static function offsetCallableSignature($callable): array
    {
        try {
            if (is_array($callable) && count($callable) === 2 && (is_object($callable[0]) || is_string($callable[0])) && is_string($callable[1]))
                $function = new \ReflectionMethod($callable[0], $callable[1]);
            elseif ($callable instanceof \Closure || is_string($callable)) {
                if (is_string($callable) && str_contains($callable, '::')) {
                    [$class, $method] = explode('::', $callable, 2);
                    $function = new \ReflectionMethod($class, $method);
                } else {
                    if (is_string($callable) && !function_exists($callable) && str_contains($callable, '\\')) $callable = substr($callable, strrpos($callable, '\\') + 1);
                    $function = new \ReflectionFunction($callable);
                }
            } elseif (is_object($callable) && method_exists($callable, '__invoke')) $function = new \ReflectionMethod($callable, '__invoke');
            else return [];
        } catch (\ReflectionException $error) { return []; }
        return array_map(static fn (\ReflectionParameter $parameter) => ['name' => $parameter->getName(),
            'reference' => $parameter->isPassedByReference(), 'variadic' => $parameter->isVariadic()], $function->getParameters());
    }

    /** Rewrite PHP tokens/expressions; preserve emitted JavaScript, HTML and text bytes. */
    public static function rewriteCompiled(string $php): string
    {
        $tokens = token_get_all($php); $result = ''; $previous = null;
        $significant = array_values(array_filter($tokens, static fn ($token) => !is_array($token) || !in_array($token[0], [T_WHITESPACE,T_COMMENT,T_DOC_COMMENT], true)));
        $declared = [];
        foreach ($significant as $index => $token) {
            if (!is_array($token) || !in_array($token[0], [T_STRING,T_NAME_FULLY_QUALIFIED], true) || strtolower(ltrim($token[1], '\\')) !== 'define') continue;
            $before = $significant[$index-1] ?? null;
            if (is_array($before) && $before[0] === T_OBJECT_OPERATOR) continue;
            if (is_array($before) && $before[0] === T_DOUBLE_COLON && strtolower(ltrim($significant[$index-2][1] ?? '', '\\')) !== strtolower(__CLASS__)) continue;
            $name = $significant[$index+2] ?? null;
            if (($significant[$index+1] ?? null) === '(' && is_array($name) && $name[0] === T_CONSTANT_ENCAPSED_STRING && preg_match('/^[\'\"]([A-Za-z_]\w*)[\'\"]$/D', $name[1], $match)) $declared[$match[1]] = true;
        }
        foreach ($tokens as $i=>$token) {
            if (!is_array($token)) { $result .= $token; if (trim($token)!=='') $previous=$token; continue; }
            [$id,$value]=$token;
            if ($id===T_STRING && in_array($value,self::DYNAMIC_CONSTANTS,true) && !in_array($previous,[T_OBJECT_OPERATOR,T_DOUBLE_COLON,T_FUNCTION,T_CONST],true)) {
                $value='\\'.__CLASS__."::constant('".$value."')";
            } elseif (in_array($id,[T_STRING,T_NAME_FULLY_QUALIFIED],true) && in_array(strtolower(ltrim($value,'\\')),['define','defined','constant'],true) && !in_array($previous,[T_OBJECT_OPERATOR,T_DOUBLE_COLON,T_FUNCTION,T_NEW,T_USE],true)) {
                $next=$i+1; while(isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0]===T_WHITESPACE) $next++;
                $function=strtolower(ltrim($value,'\\'));
                if (($tokens[$next]??null)==='(') $value='\\'.__CLASS__.'::'.($function==='defined'?'isDefined':$function);
            } elseif ($id===T_STRING && isset($declared[$value]) && !in_array($previous,[T_OBJECT_OPERATOR,T_DOUBLE_COLON,T_FUNCTION,T_CONST,T_NEW,T_USE,T_CLASS,T_INTERFACE,T_TRAIT,T_EXTENDS,T_IMPLEMENTS],true)) {
                $next=$i+1; while(isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0]===T_WHITESPACE) $next++;
                if (($tokens[$next]??null)!=='(' && !(is_array($tokens[$next]??null) && in_array($tokens[$next][0],[T_DOUBLE_COLON,T_VARIABLE],true))) $value='\\'.__CLASS__.'::declaredConstant('.var_export($value,true).')';
            } elseif (in_array($id,[T_STRING,T_NAME_FULLY_QUALIFIED],true) && in_array(strtolower(ltrim($value,'\\')),array_map('strtolower',self::FUNCTION_NAMES),true) && !in_array($previous,[T_OBJECT_OPERATOR,T_DOUBLE_COLON,T_FUNCTION,T_NEW,T_USE],true)) {
                $next=$i+1; while(isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0]===T_WHITESPACE) $next++;
                if (($tokens[$next]??null)==='(') $value='\\'.__NAMESPACE__.'\\'.ltrim($value,'\\');
            }
            $result.=$value;
            if (!in_array($id,[T_WHITESPACE,T_COMMENT,T_DOC_COMMENT],true)) $previous=$id;
        }
        return ResourceFilesystem::rewrite(OffsetCompiler::rewrite(ComparisonCompiler::rewrite($result)));
    }

    public static function resolveTemplate(string $file, ?string $viewPath = null, ?string $allowedRoot = null): string
    {
        // A long-lived worker must observe external file and parent-directory
        // symlink replacements, including a restored alias after rejection.
        // Clearing only the leaf retains stale parent entries in PHP's cache.
        clearstatcache(true);
        $root = realpath(ThemeCatalog::physical($allowedRoot ?? base_path('template')));
        if (!$root) throw new \RuntimeException('CMS template directory is missing');
        if (str_contains($file,"\0")) throw new \RuntimeException('Invalid template path');
        $file=str_replace('\\','/',$file);
        $viewPath ??= self::context()?->templateRoot() ?? ThemeCatalog::root('default', 'pc');
        if (!pathinfo($file,PATHINFO_EXTENSION)) $file.='.htm';
        if (!in_array(strtolower(pathinfo($file,PATHINFO_EXTENSION)),['htm','html'],true)) throw new \RuntimeException('Only .htm/.html template files are allowed');
        if (preg_match('#^(?:/|\./)template/(pc|mobile)/#', $file) && self::constant('TPL_THEME')!=='') $file=preg_replace('#^(/|\./)template/#','$1template/'.self::constant('TPL_THEME'),$file);
        if (str_starts_with($file,'/template/')) $candidate=base_path(ltrim($file,'/'));
        elseif (str_starts_with($file,'./template/')) $candidate=base_path(substr($file,2));
        elseif (str_starts_with($file,'/')) $candidate=$file;
        else $candidate=rtrim($viewPath,'/').'/'.$file;
        $candidate = ThemeCatalog::physical($candidate);
        $current = '';
        foreach (explode('/', trim($candidate, '/')) as $part) {
            if ($part === '..' || $part === '.') throw new \RuntimeException('Template traversal is forbidden');
            if ($part === '') continue;
            $current .= '/' . $part;
            if (is_link($current)) throw new \RuntimeException('Template links are forbidden');
        }
        $path=realpath($candidate);
        if (!$path || !str_starts_with($path,$root.DIRECTORY_SEPARATOR) || !is_file($path)) throw new \RuntimeException('Template does not exist or escapes template root: '.$file);
        $stat = stat($path);
        if (!$stat || $stat['nlink'] !== 1) throw new \RuntimeException('Template hard links are forbidden');
        return $path;
    }
}
