<?php
declare(strict_types=1);
namespace app\cms\admin\support;

use app\cms\Paths;
use app\cms\admin\http\NotFoundController;
use app\cms\admin\middleware\AdminEntry;
use Webman\Route;
use Webman\Http\Request;

/** Fixed HTTP surface, separate from physical layout and authorization identities. */
final class AdminRoutes
{
    public static function definitions(): array
    {
        static $registry = null;
        if ($registry !== null) return $registry;
        $routes = require Paths::cms('admin/config/routes.php');
        $file = Paths::cms('admin/config/generated-routes.php');
        $generated = is_file($file) ? require $file : [];
        foreach ($generated as $identity => $definition) {
            if (isset($routes[$identity])) throw new \LogicException('Generated backend route conflicts with a built-in controller');
            $routes[$identity] = $definition;
        }
        foreach (['PluginController', 'DevController', 'UisetController'] as $disabled) unset($routes['plugin\\admin\\app\\controller\\' . $disabled]);
        if (isset($routes['plugin\\admin\\app\\controller\\TableController'])) {
            $routes['plugin\\admin\\app\\controller\\TableController']['actions'] = array_values(array_diff($routes['plugin\\admin\\app\\controller\\TableController']['actions'], ['crud']));
        }
        return $registry = $routes;
    }

    public static function implementation(string $identity): string
    {
        $prefix = 'plugin\\admin\\app\\controller\\';
        if (!str_starts_with($identity, $prefix) || !preg_match('/^[A-Za-z][A-Za-z0-9_]*Controller$/D', substr($identity, strlen($prefix)))) {
            throw new \LogicException('Invalid backend permission identity');
        }
        return 'app\\cms\\admin\\controller\\' . substr($identity, strlen($prefix));
    }

    public static function identity(string $controller, string $action): ?string
    {
        foreach (self::definitions() as $identity => $definition) {
            if ($controller === $identity && in_array($action, $definition['actions'], true)) return $identity;
        }
        return null;
    }

    public static function isBackend(Request $request): bool
    {
        return preg_match('#^/app/admin(?:/|$)#', $request->path()) === 1;
    }

    public static function register(): void
    {
        Route::disableDefaultRoute('admin');
        $seen = [];
        $add = static function (string $path, string $class, string $action) use (&$seen): void {
            if (isset($seen[$path])) throw new \LogicException('Duplicate backend path: ' . $path);
            $seen[$path] = true;
            Route::any($path, [$class, $action])->middleware([AdminEntry::class]);
        };
        foreach (self::definitions() as $identity => $definition) {
            self::implementation($identity); // Validate the stable identity.
            $class = $identity;
            if (!preg_match('#^/app/admin/[a-z][a-z0-9-]*$#D', $definition['path'])) throw new \LogicException('Invalid backend route prefix');
            foreach ($definition['actions'] as $action) {
                $method = new \ReflectionMethod($class, $action);
                if (!$method->isPublic() || str_starts_with($action, '__')) throw new \LogicException('Invalid backend action');
                $add($definition['path'] . '/' . strtolower($action), $class, $action);
            }
            if (in_array('index', $definition['actions'], true)) $add($definition['path'], $class, 'index');
        }
        $index = 'plugin\\admin\\app\\controller\\IndexController';
        $add('/app/admin', $index, 'index');
        $add('/app/admin/account/captcha/{type}', 'plugin\\admin\\app\\controller\\AccountController', 'captcha');
        $add('/app/admin/dict/get/{name}', 'plugin\\admin\\app\\controller\\DictController', 'get');
    }

    public static function registerFallback(): void
    {
        // Explicit named response: unregistered paths cannot reach the public CMS fallback.
        Route::any('/app/admin/{missing:.+}', [NotFoundController::class, 'index']);
    }

    /** Preserve conventional case/dash/trailing-slash aliases only for registered actions. */
    public static function normalizePath(string $path): string
    {
        if (!str_starts_with($path, '/app/admin')) return $path;
        $clean = rtrim(preg_replace('#/+#', '/', $path), '/');
        $parts = explode('/', $clean);
        if (count($parts) === 3 && $clean === '/app/admin') return $clean;
        if (count($parts) < 4 || ($parts[1] ?? '') !== 'app' || ($parts[2] ?? '') !== 'admin') return $path;
        $module = strtolower(str_replace('-', '', $parts[3]));
        foreach (self::definitions() as $definition) {
            if (str_replace('-', '', $definition['path']) !== '/app/admin/' . $module) continue;
            $action = strtolower(str_replace('-', '', $parts[4] ?? 'index'));
            if (!in_array($action, array_map('strtolower', $definition['actions']), true)) return $path;
            if (count($parts) <= 5) return $definition['path'] . '/' . $action;
            if (count($parts) === 6 && (($module === 'account' && $action === 'captcha') || ($module === 'dict' && $action === 'get'))) {
                return $definition['path'] . '/' . $action . '/' . $parts[5];
            }
        }
        return $path;
    }
}
