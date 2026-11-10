<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\Paths;

/** The original shared foreign-language bundle, composed without changing vendor JS. */
final class LanguageAssetService
{
    public const GENERATED = Paths::COMMON_ASSETS . '/generated/lang/foreign_global.js';
    private const BASE = Paths::COMMON_ASSETS . '/static/common/js/ey_global.js';
    private const START = '/*------------------外贸助手的JS多语言变量 start------------------*/';
    private const END = '/*------------------外贸助手的JS多语言变量 end------------------*/';

    public static function resolve(string $baseFile): string
    {
        if ($baseFile !== Paths::root(self::BASE)) throw new \InvalidArgumentException('Unexpected language bundle source');
        self::assertSafeFile($baseFile);
        $generated = self::generated();
        $base = self::read($baseFile);
        $language = self::read($generated);
        $pattern = '/' . preg_quote(self::START, '/') . '.*?' . preg_quote(self::END, '/') . '/s';
        $content = preg_replace_callback($pattern, static fn() => self::START . PHP_EOL . $language . self::END, $base, -1, $count);
        if ($content === null || $count !== 1) throw new \RuntimeException('Language bundle marker is missing or ambiguous');
        $key = hash('sha256', serialize([\app\cms\template\TemplateMode::siteSignature(), \app\cms\template\TemplateMode::current(), self::selectedLanguage(), hash('sha256', $base), hash('sha256', $language)]));
        $cache = Paths::runtime('cache/assets/ey-global-' . $key . '.js');
        self::atomicWrite($cache, $content);
        return $cache;
    }

    /** Legacy entry point writes derived cache only; public/theme files are immutable. */
    public static function write(string $content): void
    {
        self::atomicWrite(Paths::runtime('cache/assets/foreign-'.hash('sha256', $content).'.js'), $content);
    }

    public static function selectedLanguage(): string
    {
        $main = (new LanguagePolicy())->main();
        return !empty((new \app\cms\repository\CmsRepository())->table('setting')->where(['name'=>'foreign_is_status','inc_type'=>'foreign','lang'=>$main])->value('value')) ? 'en' : $main;
    }

    /** DB rows are authoritative. Deleting runtime simply regenerates this asset. */
    public static function generated(): string
    {
        $main = (new LanguagePolicy())->main(); $language = self::selectedLanguage();
        $repository = new \app\cms\repository\CmsRepository();
        $values = [];
        foreach (array_unique([$main, $language]) as $mark) {
            foreach ($repository->table('foreign_pack')->where('lang', $mark)->order('type asc,id asc')->select()->toArray() as $row) {
                $name = (string)$row['name'];
                if (!preg_match('/^[A-Za-z0-9_]{1,150}$/D', $name)) throw new \RuntimeException('外贸语言变量名不能生成安全的JS标识符');
                $values[$name] = (string)$row['value'];
            }
        }
        $content = "/** Derived foreign-language variables; source: database. */\nfunction ey_foreign(string, ...args) { return string.replace(/%([a-zA-Z0-9]{1,1})/g, function() { return args.shift(); }); }\n";
        foreach ($values as $name => $value) $content .= 'var ey_foreign_'.$name.' = '.json_encode($value, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).";\n";
        $identity = [\app\cms\template\TemplateMode::siteSignature(), \app\cms\template\TemplateMode::current(), $main, $language, hash('sha256', $content)];
        $path = Paths::runtime('cache/assets/foreign-'.hash('sha256', serialize($identity)).'.js');
        self::atomicWrite($path, $content);
        return $path;
    }

    private static function read(string $path): string
    {
        if (str_starts_with($path, Paths::runtime('cache/assets/'))) return self::readDerived($path);
        $content = file_get_contents($path);
        if ($content === false) throw new \RuntimeException('Unable to read language asset');
        return $content;
    }

    private static function assertSafeFile(string $path, bool $optional = false): void
    {
        if (!str_starts_with($path, '/') || str_contains($path, "\0") || str_contains($path, '\\')) throw new \RuntimeException('Invalid language asset path');
        $current = '';
        foreach (explode('/', ltrim($path, '/')) as $part) {
            if ($part === '' || $part === '.' || $part === '..') throw new \RuntimeException('Invalid language asset path');
            $current .= '/' . $part;
            if (is_link($current)) throw new \RuntimeException('Language asset path contains a symbolic link');
        }
        if (file_exists($path) && !is_file($path)) throw new \RuntimeException('Language asset is not a regular file');
        if (!$optional && !is_file($path)) throw new \RuntimeException('Language asset is missing');
    }

    private static function assetScope(string $path): string
    {
        return $path . ':' . \app\cms\template\TemplateMode::siteSignature() . ':' . \app\cms\template\TemplateMode::current();
    }

    public static function readDerived(string $path): string
    {
        if (!str_starts_with($path, Paths::runtime('cache/assets/'))) throw new \RuntimeException('语言资源路径无效');
        $raw = \app\cms\cache\CacheStorage::read($path);
        $bytes = $raw === null ? null : \app\cms\security\SignedEnvelope::open('cache.language-asset', self::assetScope($path), $raw);
        if ($bytes === null) throw new \RuntimeException('语言资源完整性校验失败');
        return $bytes;
    }

    private static function atomicWrite(string $path, string $content): void
    {
        $signed = \app\cms\security\SignedEnvelope::seal('cache.language-asset', self::assetScope($path), $content);
        \app\cms\cache\CacheStorage::write($path, $signed);
    }
}
