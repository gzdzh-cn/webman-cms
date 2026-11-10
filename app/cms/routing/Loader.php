<?php
namespace app\cms\routing;

final class Loader
{
    public static function parseName($name, $type = 0): string
    {
        return $type ? str_replace(' ', '', ucwords(str_replace('_', ' ', $name))) : strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($name)));
    }
}
