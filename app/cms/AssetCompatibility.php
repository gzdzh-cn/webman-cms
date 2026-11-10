<?php
namespace app\cms;

/** Supplemental fixes for positively identified copied third-party assets. */
final class AssetCompatibility
{
    /** This exact shipped mobile template omits its MagicThumb stylesheet. */
    public static function mobileGalleryControls(string $html, CmsContext $context, string $template): string
    {
        if ($context->device !== 'mobile'
            || !is_file($template)
            || hash_file('sha256', $template) !== 'ab845b48d4d80ec8f980f11f53c71d0c22d2f68e2e72e86aabfab28aafa5ca16'
            || !preg_match('~<a\b[^>]*\bclass\s*=\s*(["\'])[^"\']*\bMagicThumb\b[^"\']*\1~i', $html)
            || str_contains($html, 'data-cms-magicthumb-controls')) return $html;
        $css = AssetManifest::resolve('/public/static/cms/magicthumb-controls.css');
        if (!$css || !is_file($css)) return $html;
        $url = AssetManifest::url('/public/static/cms/magicthumb-controls.css');
        $link = '<link rel="stylesheet" data-cms-magicthumb-controls href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
        return preg_match('~</head\s*>~i', $html) ? preg_replace('~</head\s*>~i', $link . '$0', $html, 1) : $link . $html;
    }

    /** Stable copied URLs may already have an upstream response in browser cache. */
    public static function versionScripts(string $html, CmsContext $context): string
    {
        $origin = parse_url($context->origin());
        $attributes = '(?:[^>"\']|"[^"]*"|\'[^\']*\')*';
        // Consume complete non-script tags, including quoted attributes, before
        // looking for their children. Raw-text elements contain no script nodes.
        $pattern = '~<!--[\s\S]*?-->(*SKIP)(*F)|<(?<raw>textarea|title|xmp|style|noscript|iframe|noembed|noframes)\b' . $attributes . '>[\s\S]*?(?:</\k<raw>\s*>|$)(*SKIP)(*F)|<plaintext\b' . $attributes . '>[\s\S]*$(*SKIP)(*F)|(?<opening><script\b' . $attributes . '>)(?<body>[\s\S]*?)(?<closing></script\s*>|$)|</?[a-z][a-z0-9:-]*\b' . $attributes . '>(*SKIP)(*F)~i';
        return preg_replace_callback($pattern, static function (array $tag) use ($origin): string {
            $opening = preg_replace_callback('~(?:"[^"]*"|\'[^\']*\')(*SKIP)(*F)|(\ssrc\s*=\s*)(?:(["\'])(.*?)\2|([^\s"\'=<>`]+))~is', static function (array $attribute) use ($origin): string {
                $quote = $attribute[2] ?? '';
                $url = html_entity_decode($quote !== '' ? $attribute[3] : $attribute[4], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $parts = parse_url($url);
                if (!is_array($parts) || empty($parts['path']) || !str_starts_with($parts['path'], '/') || isset($parts['user']) || isset($parts['pass'])) return $attribute[0];
                if (isset($parts['scheme']) && !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) return $attribute[0];
                if (isset($parts['host'])) {
                    $scheme = strtolower($parts['scheme'] ?? ($origin['scheme'] ?? 'http'));
                    $originScheme = strtolower($origin['scheme'] ?? 'http');
                    $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
                    $originPort = $origin['port'] ?? ($originScheme === 'https' ? 443 : 80);
                    if (strtolower($parts['host']) !== strtolower($origin['host'] ?? '') || $scheme !== $originScheme || $port !== $originPort) return $attribute[0];
                }
                if (!in_array(basename($parts['path']), ['mzp-packed.js', 'navbarscroll.js'], true)) return $attribute[0];
                $file = AssetResolver::resolve($parts['path']);
                $root = realpath(Paths::commonAssets('compat'));
                if (!$file || !$root || !str_starts_with($file, $root . DIRECTORY_SEPARATOR)) return $attribute[0];
                $query = [];
                foreach (explode('&', $parts['query'] ?? '') as $entry) {
                    if ($entry !== '' && rawurldecode(explode('=', $entry, 2)[0]) !== '_cms_asset') $query[] = $entry;
                }
                $query[] = '_cms_asset=' . substr(hash_file('sha256', $file), 0, 16);
                $question = strpos($url, '?');
                $fragment = strpos($url, '#');
                $end = min($question === false ? strlen($url) : $question, $fragment === false ? strlen($url) : $fragment);
                $versioned = substr($url, 0, $end) . '?' . implode('&', $query) . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
                $outputQuote = $quote !== '' ? $quote : '"';
                return $attribute[1] . $outputQuote . htmlspecialchars($versioned, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . $outputQuote;
            }, $tag['opening'], 1);
            return $opening . $tag['body'] . $tag['closing'];
        }, $html);
    }

    public static function resolve(string $source): string
    {
        $asset = basename($source);
        if (!in_array($asset, ['mzp-packed.js', 'navbarscroll.js'], true)) return $source;
        $root = realpath(base_path());
        if (!$root || !str_starts_with($source, $root . DIRECTORY_SEPARATOR)) return $source;
        $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($source, strlen($root) + 1));
        // Never use a supplied filename or theme name as a supplemental path.
        if ($asset === 'mzp-packed.js') {
            if (!preg_match('#^template/(?:(?:default|official)/)?(?:pc|mobile)/skin/Lib/MagicZoom/mzp-packed\.js$#D', $relative)) return $source;
            $family = 'magiczoom-2008';
            $namePattern = '/^mzp-packed-[a-f0-9]{12}\.js$/D';
        } else {
            if (!preg_match('#^template/(?:(?:default|official)/)?mobile/skin/menu/navbarscroll\.js$#D', $relative)) return $source;
            $family = 'navbar-scroll';
            $namePattern = '/^navbarscroll-[a-f0-9]{12}\.js$/D';
        }
        $directory = \app\cms\Paths::resource('compat-assets/' . $family);
        $manifestPath = $directory . '/manifest.json';
        if (!is_file($manifestPath)) return $source;
        $manifest = json_decode((string)file_get_contents($manifestPath), true);
        $entry = is_array($manifest) ? ($manifest['sources'][$relative] ?? null) : null;
        if (!is_array($entry) || hash_file('sha256', $source) !== ($entry['source_sha256'] ?? null)) return $source;
        $name = (string)($entry['compat_file'] ?? '');
        if (!preg_match($namePattern, $name)) return $source;
        $target = realpath(Paths::commonAssets('compat/' . $family . '/' . $name));
        $directory = realpath(Paths::commonAssets('compat/' . $family));
        if (!$target || !$directory || !is_file($target) || !str_starts_with($target, $directory . DIRECTORY_SEPARATOR)) return $source;
        return hash_file('sha256', $target) === ($entry['compat_sha256'] ?? null) ? $target : $source;
    }
}
