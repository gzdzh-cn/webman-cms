<?php
declare(strict_types=1);

namespace app\cms {
    final class CmsContext {
        public array $memo = [];
        public static function current(): self { static $context; return $context ??= new self(); }
    }
    final class AssetManifest {
        public static function url(string $url): string { return $url; }
        public static function entry(string $url): array { return []; }
    }
}
namespace app\cms\compat {
    final class Runtime { public static function constant(string $name): string { return ''; } }
    final class Request {
        public static function instance(): self { return new self(); }
        public function module(): string { return 'home'; }
        public function domain(): string { return 'https://example.test'; }
    }
}
namespace {
    define('EXT', '.php');
    $root = dirname(__DIR__, 4);
    $fixtureRoot = isset($argv[1]) ? realpath($argv[1]) : false;
    if (!$fixtureRoot || !is_dir($fixtureRoot) || $fixtureRoot === $root || str_starts_with($fixtureRoot . '/', $root . '/')) {
        throw new \InvalidArgumentException('Pass an existing fixture directory outside the project as the first argument.');
    }
    $fixture = $fixtureRoot . '/cms-plugin-test-' . bin2hex(random_bytes(8));
    mkdir($fixture . '/weapp/Fake', 0700, true);
    file_put_contents($fixture . '/weapp/Fake/function.php', '<?php $GLOBALS["legacy_plugin_executed"] = true;');
    define('ROOT_PATH', $fixture . '/');
    $oldDirectory = getcwd();
    chdir($fixture);
    function tpCache($key, $data = [], $lang = '', $options = null) { return false; }
    function is_http_url($url) { return (bool)preg_match('~^(?:\w+:)?//~', $url ?? ''); }
    require $root . '/vendor/autoload.php';
    require $root . '/app/cms/support/upstream/core.php';
    require $root . '/app/cms/support/upstream/common.php';
    require $root . '/app/cms/support/upstream/function.php';
    function check($condition, string $message): void { if (!$condition) throw new \RuntimeException($message); }
    $attempts = [];
    spl_autoload_register(static function (string $class) use (&$attempts): void { $attempts[] = $class; });
    function_1601946443();
    try { getWeappVersion('Fake'); throw new \RuntimeException('plugin config access was allowed'); }
    catch (\RuntimeException $error) { if ($error->getMessage() !== '业务插件已停用') throw $error; }
    check(empty($GLOBALS['legacy_plugin_executed']), 'plugin function file was included');
    check(weapp_vendor('anything', 'Fake') === false, 'weapp_vendor must be disabled');
    check(import_weapp_vendor('anything', 'Fake') === false, 'import_weapp_vendor must be disabled');
    check(hookexec('Fake/Controller/action') === null, 'hook execution must be disabled');
    check(weapptaglib('Fake', 'render') === '', 'plugin tag must be disabled');
    check(equal_pop_login() === '', 'plugin login must be disabled');
    check(SynchronizeQiniu('/uploads/a.jpg') === [], 'legacy cloud upload must be disabled');
    check(SynImageObjectBucket('/uploads/a.jpg', ['Qiniuyun'=>['status'=>1]]) === [], 'object upload must be disabled');
    $remote = 'https://cdn.example.test/uploads/p.jpg?x=1';
    check(handle_subdir_pic($remote) === $remote, 'remote image URL changed');
    check(handle_subdir_pic('/uploads/p.jpg') === '/uploads/p.jpg', 'local image URL changed');
    $html = '<p>text<img src="https://cdn.example.test/uploads/p.jpg"></p>';
    check(handle_subdir_pic($html, 'html') === $html, 'remote HTML image changed');
    check($attempts === [], 'a plugin class was loaded');
    try {
        \app\cms\admin\support\PluginPackage::publish($fixture . '/missing.zip', 'Fake', $fixture . '/plugin', static function (): void { $GLOBALS['plugin_publish_called'] = true; });
        throw new \RuntimeException('plugin package publication was allowed');
    } catch (\RuntimeException $error) {
        if ($error->getMessage() !== '业务插件已停用') throw $error;
    }
    check(empty($GLOBALS['plugin_publish_called']) && !file_exists($fixture . '/plugin'), 'plugin publication had side effects');
    $event = new class { public function getOperation() { throw new \RuntimeException('Composer event must not be inspected'); } };
    \app\cms\support\ComposerScripts::install($event);
    \app\cms\support\ComposerScripts::uninstall($event);
    check(!str_contains(file_get_contents($root . '/app/cms/support/ComposerScripts.php'), '\support\Plugin::'), 'Composer package hook delegates to a plugin installer');
    $controller = (new \ReflectionClass(\app\cms\admin\controller\TableController::class))->newInstanceWithoutConstructor();
    foreach (['GET', 'POST'] as $method) {
        $response = $controller->crud(new \support\Request($method . " / HTTP/1.1\r\nHost: example.test\r\n\r\n"));
        check($response->getStatusCode() === 403, 'Web code generation endpoint must reject requests');
    }
    $target = new \ReflectionMethod($controller, 'generationTarget');
    foreach (['plugin/Fake/app/controller/FakeController.php', 'plugins/Fake/controller/FakeController.php', 'weapp/Fake/controller/FakeController.php', 'app/plugins/controller/FakeController.php', 'app/weapp/controller/FakeController.php'] as $path) {
        try { $target->invoke($controller, $path, $path); throw new \RuntimeException('plugin generation was allowed'); }
        catch (\InvalidArgumentException $error) { check($error->getMessage() === '业务插件目录已停用', 'wrong plugin generation rejection'); }
    }
    $allowed = $target->invoke($controller, 'app/cms/admin/controller/ExampleController.php', 'app/cms/admin/model/Example.php');
    check($allowed['cms'] === true, 'Internal fixed CMS directory mapping changed');
    $pathCheck = new \ReflectionMethod($controller, 'assertGenerationPath');
    foreach (['plugin/Fake/a.php', 'app/plugins/a.php', 'weapp/Fake/a.php'] as $path) {
        try { $pathCheck->invoke($controller, rtrim(base_path(), '/') . '/' . $path); throw new \RuntimeException('plugin output path was allowed'); }
        catch (\InvalidArgumentException $error) { check(str_contains($error->getMessage(), '停用目录'), 'wrong output path rejection'); }
    }
    $paths = [
        'app/cms/routing/upstream-routes.php', 'app/cms/support/upstream/common.php',
        'app/cms/support/upstream/helper.php', 'app/cms/support/upstream/function.php',
        'app/cms/support/upstream/core.php', 'app/cms/compat/model/Article.php',
    ];
    foreach ($paths as $path) {
        $source = file_get_contents($root . '/' . $path);
        check(!preg_match('~new\s+\\\\weapp\\\\|plugins/route\.php|weapp/\*/function\.php~i', $source), "plugin entry remains in $path");
    }
    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/app', \FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') continue;
        $source = file_get_contents($file->getPathname());
        check(!preg_match('~new\s+\\\\(?:weapp\\\\|app\\\\plugins\\\\)~i', $source), 'Plugin class instantiation remains: ' . $file->getPathname());
    }
    chdir($oldDirectory);
    unlink($fixture . '/weapp/Fake/function.php');
    rmdir($fixture . '/weapp/Fake'); rmdir($fixture . '/weapp'); rmdir($fixture);
    echo "Legacy plugin entrypoints disabled; image URLs preserved.\n";
}
