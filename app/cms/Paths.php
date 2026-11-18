<?php
declare(strict_types=1);
namespace app\cms;

/** Application-owned paths. Public URLs must never be inferred from private paths. */
final class Paths
{
    public const PUBLIC_ASSET_URL = '/admin-assets';
    public const COMMON_ASSET_URL = self::PUBLIC_ASSET_URL . '/common';
    public const PUBLIC_ASSETS = 'app/cms/resources/admin-assets';
    public const COMMON_ASSETS = self::PUBLIC_ASSETS . '/common';
    /** Fixed deployment layout, relative to the source entry or executable directory. */
    public const DIRECTORIES = [
        'site_root'=>'',
        'config_dir'=>'config',
        'public_dir'=>'public',
        'assets_dir'=>self::PUBLIC_ASSETS,
        'template_dir'=>'template',
        'uploads_dir'=>'uploads',
        'html_dir'=>'public/html',
        'install_dir'=>'install',
        'runtime_dir'=>'runtime',
    ];

    public static function assets(string $path = ''): string { return self::root(self::PUBLIC_ASSETS . ($path === '' ? '' : '/' . $path)); }
    public static function commonAssets(string $path = ''): string { return self::root(self::COMMON_ASSETS . ($path === '' ? '' : '/' . $path)); }
    public static function root(string $path = ''): string { return $path === '' ? self::directory('site_root') : self::logical($path); }
    public static function code(string $path = ''): string { return self::join(dirname(__DIR__, 2), $path); }
    public static function config(string $path = ''): string { return self::join(self::directory('config_dir'), $path); }
    public static function templates(string $path = ''): string { return self::join(self::directory('template_dir'), $path); }
    public static function uploads(string $path = ''): string { return self::join(self::directory('uploads_dir'), $path); }
    public static function publicRoot(string $path = ''): string { return $path === '' ? self::directory('public_dir') : self::logical('public/'.$path); }
    public static function directory(string $name): string
    {
        static $paths = null;
        if ($paths === null) {
            $site = \app\cms\config\Entry::directory();
            $paths = [];
            foreach (self::DIRECTORIES as $key=>$relative) $paths[$key]=$site.($relative===''?'':'/'.$relative);
            // Replace whole immutable roots before inspecting filesystem identities.
            // An unused external copy must not affect an embedded resource root.
            if (\app\cms\support\EmbeddedResources::has('public')) {
                $paths['public_dir']=self::code('public');
            }
            if (\app\cms\support\EmbeddedResources::hasAssets()) $paths['assets_dir']=self::code(self::PUBLIC_ASSETS);
            if (\app\cms\support\EmbeddedResources::has('template')) $paths['template_dir']=self::code('template');
            foreach ($paths as $value) if (!str_starts_with($value,'phar://')) \app\cms\config\Entry::assertPath($value);
            foreach (array_filter([\app\cms\config\DeploymentConfig::file(), \app\cms\support\EmbeddedResources::yaml()?null:\app\cms\config\Environment::path()],static fn($p)=>is_string($p)&&!str_starts_with($p,'phar://')) as $privateFile) foreach (['public_dir','assets_dir','uploads_dir','html_dir','template_dir','runtime_dir','install_dir'] as $key) if ($privateFile === $paths[$key] || str_starts_with($privateFile, $paths[$key].'/')) throw new \RuntimeException('配置文件不能位于公开或临时目录');
        }
        if($name==='public_dir'&&\app\cms\support\EmbeddedResources::has('public'))return self::code('public');
        if($name==='assets_dir'&&\app\cms\support\EmbeddedResources::hasAssets())return self::code(self::PUBLIC_ASSETS);
        if($name==='template_dir'&&\app\cms\support\EmbeddedResources::has('template'))return self::code('template');
        return $paths[$name] ?? throw new \InvalidArgumentException('未知目录映射');
    }
    public static function logical(string $path): string
    {
        $mounts=[self::PUBLIC_ASSETS=>'assets_dir','public/uploads'=>'uploads_dir','uploads'=>'uploads_dir','public/html'=>'html_dir','public'=>'public_dir','template'=>'template_dir','runtime'=>'runtime_dir','config'=>'config_dir','install'=>'install_dir'];
        foreach($mounts as $prefix=>$key)if($path===$prefix||str_starts_with($path,$prefix.'/'))return self::join(self::directory($key),ltrim(substr($path,strlen($prefix)),'/'));
        if(preg_match('#^install_[a-z]{12}(?:/|$)#',$path))return self::join(dirname(self::directory('install_dir')),$path);
        return self::code($path);
    }
    public static function relative(string $file): ?string
    {
        foreach(['assets_dir'=>self::PUBLIC_ASSETS,'uploads_dir'=>'public/uploads','html_dir'=>'public/html','template_dir'=>'template','config_dir'=>'config','runtime_dir'=>'runtime','public_dir'=>'public']as$key=>$logical){$root=self::directory($key);if($file===$root||str_starts_with($file,$root.'/'))return $logical.substr($file,strlen($root));}
        $root=self::code();return str_starts_with($file,$root.'/')?substr($file,strlen($root)+1):null;
    }
    public static function cms(string $path = ''): string { return self::join(__DIR__, $path); }
    public static function resources(string $path = ''): string { return self::cms('resources' . ($path === '' ? '' : '/' . $path)); }
    public static function resource(string $path = ''): string { return self::resources($path); }
    public static function legacyData(string $path = ''): string { return self::resources('legacy/data' . ($path === '' ? '' : '/' . $path)); }
    public static function runtime(string $path = ''): string { return self::join(self::directory('runtime_dir'), $path); }
    public static function install(string $path = ''): string { return self::root('install' . ($path === '' ? '' : '/' . $path)); }
    /** Maintenance-only original SQL lookup; never used to reopen the Web installer. */
    public static function installationBaseline(string $filename): string { return (new \app\cms\install\InstallerArchive(self::root()))->baselinePath($filename); }
    public static function publicHtml(string $path = ''): string { return self::join(self::directory('html_dir'), $path); }

    public static function ensureDirectory(string $path, int $mode = 0750): void
    {
        if (!str_starts_with($path, '/') || str_contains($path, "\0")) throw new \RuntimeException('Directory must be absolute');
        $current = '';
        foreach (explode('/', trim($path, '/')) as $part) {
            if ($part === '' || $part === '.' || $part === '..') throw new \RuntimeException('Invalid directory');
            $current .= '/' . $part;
            if (is_link($current) || (file_exists($current) && !is_dir($current))) throw new \RuntimeException('Directory contains a link or file');
        }
        // Several workers may create the same cold runtime path together.
        // EEXIST is successful only after checking the resulting real directory.
        if (!is_dir($path) && !@mkdir($path, $mode, true) && !is_dir($path)) throw new \RuntimeException('Unable to create runtime directory');
        clearstatcache(true, $path);
        if (is_link($path) || !is_dir($path)) throw new \RuntimeException('Invalid runtime directory');
    }

    private static function join(string $base, string $path): string
    {
        if (str_contains($path, "\0") || str_contains($path, '\\') || str_starts_with($path, '/') || preg_match('#(?:^|/)\.\.(?:/|$)#', $path)) throw new \InvalidArgumentException('Invalid application-relative path');
        return rtrim($base, '/') . ($path === '' ? '' : '/' . $path);
    }
}
