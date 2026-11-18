<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;

/** Actual target values (including empty strings) always outrank fallback. */
final class LanguageDictionary
{
    private CmsRepository $repository;
    public function __construct(?CmsRepository $repository = null) { $this->repository = $repository ?? new CmsRepository(); }

    public function resolve(string $language): array
    {
        $main = (new LanguagePolicy($this->repository))->main();
        $targetLocal = LocalLanguageCatalog::read($language);
        $mainLocal = LocalLanguageCatalog::read($main);
        $target = $this->actual($language); $source = $language === $main ? $target : $this->actual($main);
        $definitions = (new LanguageStateStore('language-definitions', $this->repository))->read('catalog', ['entries'=>[]]);
        $names = array_unique(array_merge(array_keys($target), array_keys($targetLocal), array_keys($source), array_keys($mainLocal), array_keys($definitions['entries'] ?? [])));
        sort($names, SORT_STRING); $entries = []; $values = [];
        foreach ($names as $name) {
            if (array_key_exists($name, $target)) { $value = $target[$name]['value']; $origin = 'database'; $pending = false; }
            elseif (array_key_exists($name, $targetLocal)) { $value = $targetLocal[$name]; $origin = 'local'; $pending = false; }
            elseif (array_key_exists($name, $source)) { $value = $source[$name]['value']; $origin = 'main_database'; $pending = $language !== $main; }
            elseif (array_key_exists($name, $mainLocal)) { $value = $mainLocal[$name]; $origin = 'main_local'; $pending = $language !== $main; }
            else { $value = ''; $origin = 'undefined'; $pending = true; }
            $values[$name] = $value;
            $entries[$name] = ['name'=>$name, 'value'=>$value, 'origin'=>$origin, 'pending'=>$pending, 'status'=>$pending?'pending':($origin==='database'?'translated':'baseline'), 'id'=>(int)($target[$name]['id']??0), 'is_syn'=>(int)($target[$name]['is_syn']??($source[$name]['is_syn']??(preg_match('/^sys\d+$/D', $name)?1:0)))];
        }
        return ['values'=>$values,'entries'=>$entries,'main'=>$main,'language'=>$language,'revision'=>hash('sha256',json_encode([$main,$entries],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR))];
    }

    public function actual(string $language): array
    {
        $values = [];
        foreach ($this->repository->table('language_pack')->where('lang',$language)->order('id asc')->select()->toArray() as $row) {
            $name = strtolower((string)$row['name']);
            if (array_key_exists($name, $values)) throw new \InvalidArgumentException('语言变量存在重复记录：'.$language.'/'.$name.'，请先核对');
            $row['value'] = (string)$row['value']; $values[$name] = $row;
        }
        return $values;
    }

    /** Read-only vocabulary signature; never touches editing context or admin sessions. */
    public function revision(string $language): string { return $this->resolve($language)['revision']; }

    /** Includes disabled languages so enabling one cannot reuse another vocabulary's output. */
    public function aggregateRevision(): string
    {
        $revisions = [];
        foreach ((new LanguagePolicy($this->repository))->all() as $language) {
            $mark = (string)$language['mark'];
            $revisions[$mark] = $this->revision($mark);
        }
        ksort($revisions, SORT_STRING);
        return hash('sha256', json_encode([1, $revisions], JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
    }

    /** The digest includes actual values, so another worker cannot reuse a stale translation. */
    public function cached(string $language, bool $force = false): array
    {
        $resolved = $this->resolve($language);
        $key = hash('sha256', serialize([\app\cms\template\TemplateMode::siteSignature(), \app\cms\template\TemplateMode::current(), $language, $resolved['revision']]));
        $path = \app\cms\Paths::runtime('cms/languages/'.$key.'.json');
        if (!$force) {
            try {
                $raw = \app\cms\cache\CacheStorage::read($path);
                $cached = $raw === null ? null : json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                if ($cached === $resolved) return $cached;
            } catch (\Throwable) { /* A broken derived cache is recreated from authoritative values. */ }
        }
        $bytes = json_encode($resolved,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        \app\cms\cache\CacheStorage::write($path, $bytes);
        return $resolved;
    }
}
