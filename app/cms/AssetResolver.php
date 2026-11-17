<?php
namespace app\cms;

final class AssetResolver
{
    private const EXTENSIONS = ['css','js','map','json','png','jpg','jpeg','gif','webp','avif','svg','ico','cur','bmp','woff','woff2','ttf','otf','eot','mp4','webm','ogg','mp3','wav','m4a','m4v','flv','swf','m3u8','ts','vtt','srt','pdf','zip','rar','7z','gz','tar','bz2','iso','wps','doc','docx','xls','xlsx','ppt','pptx','csv','txt','xml','ra','ram','vqf','wma','mid','cd','wave','aiff','au','mpeg-4','midi','amr','mov','rm','dat','mpeg','mpg','avi','wmv','rmvb','asf','3gp','mkv','f4v','ogv'];

    private static function hasPrivateSegment(string $path): bool
    {
        return str_contains($path, "\0") || str_contains($path, '\\') || (bool)preg_match('#(^|/)\.[^/]*(/|$)#', $path);
    }

    /** Reserved generated HTML must never become a raw asset after a reinstall. */
    public static function isGeneratedHtml(string $file): bool
    {
        if (!in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['html','htm'], true)) return false;
        $root = rtrim(\app\cms\Paths::publicRoot('html'), DIRECTORY_SEPARATOR);
        if (str_starts_with($file, $root . DIRECTORY_SEPARATOR)) return true;
        $actual = \app\cms\support\EmbeddedResources::realpath($file); $actualRoot = \app\cms\support\EmbeddedResources::realpath($root);
        return $actual && $actualRoot && str_starts_with($actual, $actualRoot . DIRECTORY_SEPARATOR);
    }

    /** Apply the same boundary to Webman's native and CMS alias file routes. */
    public static function isPublicFile(string $file, string $root, bool $allowHtml = true): bool
    {
        $lexicalRoot = rtrim($root, DIRECTORY_SEPARATOR);
        $relative = str_starts_with($file, $lexicalRoot . DIRECTORY_SEPARATOR) ? substr($file, strlen($lexicalRoot)) : $file;
        if (self::hasPrivateSegment($relative) || self::isGeneratedHtml($file)) return false;
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['html', 'htm'], true)
            && (new \app\cms\service\PublicStaticPublisher())->ownsFile($file)) return false;
        try { if(str_contains($file,'://')) { if(!\app\cms\support\EmbeddedResources::realpath($file))return false; } else \app\cms\config\Entry::assertPath($file); } catch(\Throwable) { return false; }
        $actual = \app\cms\support\EmbeddedResources::realpath($file);
        $root = \app\cms\support\EmbeddedResources::realpath($root);
        if (!$actual || !$root || !is_file($actual) || !str_starts_with($actual, $root . DIRECTORY_SEPARATOR)) return false;
        if (in_array(strtolower(pathinfo($actual, PATHINFO_EXTENSION)), ['html', 'htm'], true)
            && (new \app\cms\service\PublicStaticPublisher())->ownsFile($actual)) return false;
        if (str_starts_with($actual, Paths::assets() . DIRECTORY_SEPARATOR) && !AssetManifest::isPublicTarget($actual)) return false;
        if (self::hasPrivateSegment(substr($actual, strlen($root))) || self::isTemplateSource($actual)) return false;
        $extension = strtolower(pathinfo($actual, PATHINFO_EXTENSION));
        return in_array($extension, self::EXTENSIONS, true) || ($allowHtml && in_array($extension, ['html', 'htm'], true));
    }

    public static function isTemplateSource(string $file): bool
    {
        if (!in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['htm', 'html'], true)) return false;
        $file = \app\cms\support\EmbeddedResources::realpath($file);
        if (!$file) return false;
        if (AssetManifest::isPublicTarget($file, true)) return false;
        foreach (['html/ai-content-index.htm', 'html/empty_view.htm', 'html/sitemap.htm', 'static/common/dispatch_jump.htm'] as $private) {
            if ($file === \app\cms\support\EmbeddedResources::realpath(\app\cms\Paths::publicRoot($private))) return true;
        }
        foreach ([\app\cms\Paths::templates(''), base_path('app/view/cms'), \app\cms\Paths::publicRoot('html/template'), \app\cms\Paths::publicRoot('static/template')] as $directory) {
            $root = \app\cms\support\EmbeddedResources::realpath($directory);
            if ($root && str_starts_with($file, $root . DIRECTORY_SEPARATOR)) return true;
        }
        return false;
    }

    public static function resolve(string $url): ?string
    {
        if ($mapped = AssetManifest::resolve($url)) return $mapped;
        if (AssetManifest::entry($url)) return null;
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) return null;
        $path = rawurldecode($path);
        $base = rtrim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        if ($base !== '' && str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
        if (self::hasPrivateSegment($path)) return null;
        // Registered system assets were resolved above. Never fall back to a
        // stale public tree or expose their private implementation path.
        if (preg_match('#^/(?:public/)?admin-assets(?:/|$)#', $path)
            || preg_match('#^/app/(?:cms|view)(?:/|$)#', $path)) return null;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        // Public widget HTML (including Ueditor's map iframe) is an asset;
        // files in the private template tree are still never served as HTML.
        $publicPage = in_array($extension, ['html', 'htm'], true) && !preg_match('#^/?template/#', $path);
        if (!$publicPage && !in_array($extension, self::EXTENSIONS, true)) return null;
        $path = ltrim($path, '/');
        // Existing administrator avatars keep their stored plugin URL.
        $path = preg_replace('#^(?:app/admin|web-admin)/uploads?/avatar/#', 'uploads/avatar/', $path);
        $path = preg_replace('#^(?:public/)?upload/#', 'uploads/', $path);
        $prefix = explode('/', $path)[0];
        try {
            $candidates = match ($prefix) {
                'template' => [ThemeCatalog::physical(\app\cms\Paths::logical($path))],
                'uploads' => [Paths::uploads(substr($path,8))],
                'public' => [\app\cms\Paths::publicRoot(substr($path, 7))],
                default => [\app\cms\Paths::publicRoot($path)],
            };
        // Some official packages use fonts/ in CSS while shipping the same EOT
        // in font/. Resolve that historical path without editing templates.
            if ($prefix === 'template' && $extension === 'eot' && str_contains($path, '/skin/fonts/')) $candidates[] = ThemeCatalog::physical(\app\cms\Paths::logical(str_replace('/skin/fonts/', '/skin/font/', $path)));
        } catch (\InvalidArgumentException $error) {
            return null;
        }
        $roots = array_filter([\app\cms\support\EmbeddedResources::realpath(\app\cms\Paths::templates('')), \app\cms\support\EmbeddedResources::realpath(\app\cms\Paths::publicRoot()), \app\cms\support\EmbeddedResources::realpath(Paths::uploads()), \app\cms\support\EmbeddedResources::realpath(Paths::assets())]);
        if ($publicPage) $roots = array_filter([\app\cms\support\EmbeddedResources::realpath(\app\cms\Paths::publicRoot()), \app\cms\support\EmbeddedResources::realpath(Paths::uploads()), \app\cms\support\EmbeddedResources::realpath(Paths::assets())]);
        foreach ($candidates as $candidate) {
            $file = \app\cms\support\EmbeddedResources::realpath($candidate);
            if (!$file || !is_file($file)) continue;
            foreach ($roots as $root) {
                if (self::isPublicFile($candidate, $root, $publicPage)) return AssetCompatibility::resolve($file);
            }
        }
        return null;
    }
}
