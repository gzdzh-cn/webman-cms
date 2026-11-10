<?php
namespace app\cms\compat;

final class Db
{
    public static function connection() { return self::connect()->native(); }
    public static function connect($config = [], $force = false): Connection
    {
        if (empty($config)) $config = 'mysql';
        elseif (is_string($config)) {
            if (str_contains($config, '/')) {
                $parts = parse_url($config);
                if (!$parts || empty($parts['scheme'])) throw new \InvalidArgumentException('Invalid database DSN');
                $config = [
                    'type' => $parts['scheme'], 'username' => rawurldecode($parts['user'] ?? ''),
                    'password' => rawurldecode($parts['pass'] ?? ''), 'hostname' => $parts['host'] ?? '',
                    'hostport' => $parts['port'] ?? '', 'database' => ltrim($parts['path'] ?? '', '/'),
                    'charset' => $parts['fragment'] ?? 'utf8', 'prefix' => '',
                ];
                if (isset($parts['query'])) parse_str($parts['query'], $config['params']);
            } else {
                // Eyou accepted a configuration key as well as a DSN/array;
                // modern registered connection names continue to work too.
                $legacy = Runtime::config($config);
                if (is_array($legacy) && isset($legacy['type'])) $config = $legacy;
            }
        }
        $context = Runtime::context();
        if (is_string($config) && !$force) {
            $native = $context->memo['db.named_connections'][$config] ?? \support\think\Db::connect($config);
        } else {
            // Webman's pooled DbManager accepts array in its signature but
            // internally treats it as a string pool key, and ignores force.
            // Keep explicit legacy connections in the request, using the same
            // maintained ORM manager without modifying vendor/pool state.
            if (!isset($context->memo['db.explicit_manager'])) $context->memo['db.explicit_manager'] = new \think\DbManager();
            $settings = is_array($config) ? $config : \config('think-orm.connections.' . $config);
            if (!is_array($settings)) throw new \InvalidArgumentException('Undefined database connection');
            $native = $context->memo['db.explicit_manager']->connect($settings, (bool)$force);
            if (is_string($config)) $context->memo['db.named_connections'][$config] = $native;
        }
        return new Connection($native);
    }
    public static function normalizeName(string $name): string { return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)); }
    public static function name(string $name): Query { return self::connect()->name($name); }
    public static function table($name): Query { return self::connect()->table($name); }
    public static function raw(string $value,array $bind=[]): \think\db\Raw { return new \think\db\Raw(self::expand($value),$bind); }
    public static function expand($value, ?string $prefix = null) {
        $prefix ??= (string)\config('think-orm.connections.mysql.prefix', 'ey_');
        if (is_array($value)) {
            $expanded = [];
            foreach ($value as $key => $item) $expanded[is_string($key) ? self::expand($key, $prefix) : $key] = self::expand($item, $prefix);
            return $expanded;
        }
        if (!is_string($value)) return $value;
        return preg_replace_callback('/__([A-Z0-9_]+)__/',fn($m)=>$prefix.strtolower($m[1]),$value);
    }
    public static function __callStatic($method,$args) { return self::connection()->$method(...$args); }
}
