<?php
namespace app\cms\routing;

use app\cms\CmsContext;

/** Read-only Eyou route registry, shared by URL reversal and frontend dispatch. */
final class Route
{
    public static function registry(): array
    {
        $context = CmsContext::current();
        if (!isset($context->memo['routing.registry'])) $context->memo['routing.registry'] = require __DIR__ . '/upstream-routes.php';
        return $context->memo['routing.registry'];
    }
    public static function rules($type = ''): array { return self::registry()['__' . $type . '__'] ?? []; }
    public static function getBind($type) { return null; }
    public static function name($name = ''): ?array
    {
        $matches = [];
        foreach (self::registry() as $pattern => $definition) {
            if (str_starts_with($pattern, '__') || !is_array($definition) || !is_string($definition[0] ?? null) || strcasecmp($name, $definition[0]) !== 0) continue;
            preg_match_all('/<([A-Za-z_]\w*)(\?)?>/', $pattern, $variables, PREG_SET_ORDER);
            $parameters = [];
            foreach ($variables as $variable) $parameters[$variable[1]] = isset($variable[2]) ? 2 : 1;
            $matches[] = [rtrim($pattern, '$'), $parameters, null, $definition[1]['ext'] ?? null];
        }
        return $matches ?: null;
    }
    /** Return module/controller/action and path variables for a public route. */
    public static function match(string $path): ?array
    {
        $registry = self::registry();
        // Parent-directory rules precede the generic <dirname>/<aid> rule;
        // aid's upstream (.*) constraint must not consume parent segments.
        uksort($registry, static fn($left, $right) => substr_count($right, '/') <=> substr_count($left, '/'));
        foreach ($registry as $pattern => $definition) {
            if (str_starts_with($pattern, '__') || !is_array($definition) || !is_string($definition[0] ?? null)) continue;
            $suffix = $definition[1]['ext'] ?? 'html';
            $pattern = rtrim($pattern, '$');
            $names = []; $position = 0; $regex = '';
            preg_match_all('/<([A-Za-z_]\w*)(\?)?>/', $pattern, $parts, PREG_OFFSET_CAPTURE);
            foreach ($parts[0] as $i => [$placeholder, $offset]) {
                $name = $parts[1][$i][0]; $names[] = $name;
                $regex .= preg_quote(substr($pattern, $position, $offset - $position), '#');
                $constraint = $definition[2][$name] ?? self::registry()['__pattern__'][$name] ?? '[^/]+';
                $regex .= '(?P<' . $name . '>' . str_replace('#', '\\#', $constraint) . ')';
                $position = $offset + strlen($placeholder);
            }
            $regex .= preg_quote(substr($pattern, $position), '#');
            $regex .= $suffix !== '' ? '\\.' . preg_quote($suffix, '#') : '/?';
            if (!preg_match('#^' . $regex . '$#iu', $path, $values)) continue;
            [$module, $controller, $action] = array_pad(explode('/', explode('?', $definition[0], 2)[0]), 3, 'index');
            $parameters = ['m' => $module, 'c' => $controller, 'a' => $action];
            foreach ($names as $name) $parameters[$name] = rawurldecode($values[$name]);
            return $parameters;
        }
        return null;
    }
}
