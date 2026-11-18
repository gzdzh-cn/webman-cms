<?php
namespace app\cms\compat;

use app\cms\service\LanguageDictionary;
use app\cms\service\LanguagePackService;
use app\cms\service\LocalLanguageCatalog;

/** Request-scoped language lookup. No uploaded or arbitrary PHP is executed. */
final class Lang
{
    private static function &dictionary(string $range): array
    {
        $context = Runtime::context();
        if (!isset($context->memo['compat.language.packs'][$range])) {
            if ($context->global) {
                $resolved = (new LanguageDictionary())->cached($range);
                $values = $resolved['values'];
                $context->memo['compat.language.status'][$range] = $resolved['entries'];
            } else {
                $main = (string)($context->getConfig('main_lang') ?: $range);
                $values = LocalLanguageCatalog::read($range) + LocalLanguageCatalog::read($main);
            }
            $context->memo['compat.language.packs'][$range] = $values;
        }
        return $context->memo['compat.language.packs'][$range];
    }
    public static function get($name = null, $vars = [], $range = '')
    {
        $values = self::dictionary($range ?: self::range());
        if (empty($name)) return $values;
        $value = $values[strtolower((string)$name)] ?? '';
        if (is_array($vars) && $vars) {
            if (key($vars) === 0) $value = sprintf((string)$value,...$vars);
            else foreach ($vars as $key=>$replacement) $value=str_replace('{:'.$key.'}',(string)$replacement,(string)$value);
        }
        return $value;
    }
    public static function has($name,$range=''): bool { return array_key_exists(strtolower((string)$name),self::dictionary($range?:self::range())); }
    public static function set($name,$value=null,$range='')
    {
        $values=&self::dictionary($range?:self::range());
        if(is_array($name))return $values=array_change_key_case($name)+$values;
        return $values[strtolower((string)$name)]=$value;
    }
    public static function load($files,$range='')
    {
        $range=$range?:self::range();
        foreach((array)$files as$file) {
            if(!is_string($file)||!is_file($file))continue;
            $actual=realpath($file);
            $trusted=realpath(__DIR__.'/lang');
            if($actual&&$trusted&&str_starts_with($actual,$trusted.'/')&&strtolower(pathinfo($actual,PATHINFO_EXTENSION))==='php') {
                // Frozen compatibility filename: read its converted private JSON, never include PHP.
                $values=LocalLanguageCatalog::read(basename($actual,'.php'));
            } else {
                if(is_link($file)||strtolower(pathinfo($file,PATHINFO_EXTENSION))!=='json')throw new \InvalidArgumentException('语言文件仅支持本地JSON，不能执行PHP');
                $values=(new LanguagePackService())->parse((string)file_get_contents($file),$range);
            }
            // Loading a baseline must never replace database values (including empty)
            // or an explicit request-local override made through Lang::set.
            $current =& self::dictionary($range);
            $current += array_change_key_case($values);
        }
        return self::dictionary($range);
    }
    public static function range($range='')
    {
        $context=Runtime::context();
        if($range!=='')$context->memo['compat.language.range']=trim($range,'/');
        return $context->memo['compat.language.range']??$context->lang;
    }
}
