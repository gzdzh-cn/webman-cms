<?php
namespace app\cms\compat;

/** The legacy data API uses Webman's session transport, never native PHP sessions. */
final class Session
{
    public static function prefix($prefix = '')
    {
        $context = Runtime::context();
        if (empty($prefix) && $prefix !== null) return self::currentPrefix();
        if ($context) $context->memo['session.prefix'] = $prefix;
    }

    public static function init(array $config = []): void
    {
        // Native PHP session transport/ID changes cannot safely run in a
        // long-lived Webman worker. The supported legacy setting is its scope.
        if (array_diff(array_keys($config), ['prefix'])) {
            throw new \InvalidArgumentException('原生PHP会话传输配置不可用于Webman会话');
        }
        $prefix = self::currentPrefix();
        if (isset($config['prefix']) && ($prefix === '' || $prefix === null)) self::prefix($config['prefix']);
    }

    public static function get($name = '', $prefix = null)
    {
        // SearchActions historically supplies [] as a project-local default.
        $default = is_array($prefix) ? $prefix : null;
        if (is_array($prefix)) $prefix = null;
        $prefix = $prefix !== null ? $prefix : self::currentPrefix();
        $data = self::all();
        if ($prefix) $data = $data[$prefix] ?? [];
        // PHP7 considers numeric zero equal to the empty-name sentinel.
        if ($name === '' || $name === null || $name === false || $name === 0 || $name === 0.0) return $data;
        if (strpos($name, '.')) {
            [$first, $second] = explode('.', $name);
            return $data[$first][$second] ?? $default;
        }
        return $data[$name] ?? $default;
    }

    public static function set($name, $value = '', $prefix = null): void
    {
        $prefix = $prefix !== null ? $prefix : self::currentPrefix();
        $data = self::all();
        if (strpos($name, '.')) {
            [$first, $second] = explode('.', $name);
            if ($prefix) {
                $data[$prefix][$first][$second] = $value;
                self::write($prefix, $data[$prefix]);
            } else {
                $data[$first][$second] = $value;
                self::write($first, $data[$first]);
            }
        } elseif ($prefix) {
            $data[$prefix][$name] = $value;
            self::write($prefix, $data[$prefix]);
        } else {
            self::write($name, $value);
        }
    }

    public static function delete($name, $prefix = null): void
    {
        if (is_array($name)) {
            foreach ($name as $key) self::delete($key, $prefix);
            return;
        }
        $prefix = $prefix !== null ? $prefix : self::currentPrefix();
        $data = self::all();
        if (strpos($name, '.')) {
            [$first, $second] = explode('.', $name);
            if ($prefix) {
                unset($data[$prefix][$first][$second]);
                if (isset($data[$prefix])) self::write($prefix, $data[$prefix]);
            } else {
                unset($data[$first][$second]);
                if (isset($data[$first])) self::write($first, $data[$first]);
            }
        } elseif ($prefix) {
            unset($data[$prefix][$name]);
            if (isset($data[$prefix])) self::write($prefix, $data[$prefix]);
        } else {
            self::forget($name);
        }
    }

    public static function clear($prefix = null): void
    {
        $prefix = $prefix !== null ? $prefix : self::currentPrefix();
        if ($prefix) self::forget($prefix);
        else foreach (array_keys(self::all()) as $name) self::forget($name);
    }

    public static function has($name, $prefix = null): bool
    {
        $prefix = $prefix !== null ? $prefix : self::currentPrefix();
        $data = self::all();
        if ($prefix) $data = $data[$prefix] ?? [];
        if (strpos($name, '.')) {
            [$first, $second] = explode('.', $name);
            return isset($data[$first][$second]);
        }
        return isset($data[$name]);
    }

    private static function currentPrefix()
    {
        $context = Runtime::context();
        if (array_key_exists('session.prefix', $context?->memo ?? [])) return $context->memo['session.prefix'];
        return $context?->getConfig('session.prefix', '') ?? '';
    }

    private static function all(): array
    {
        $context = Runtime::context();
        return $context?->request ? $context->request->session()->all() : ($context?->memo['session'] ?? []);
    }

    private static function write($name, $value): void
    {
        $context = Runtime::context();
        if ($context?->request) $context->request->session()->set($name, $value);
        elseif ($context) $context->memo['session'][$name] = $value;
    }

    private static function forget($name): void
    {
        $context = Runtime::context();
        if ($context?->request) $context->request->session()->forget($name);
        elseif ($context) unset($context->memo['session'][$name]);
    }
}
