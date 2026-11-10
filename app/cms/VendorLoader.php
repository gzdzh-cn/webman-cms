<?php
namespace app\cms;

/** Legacy vendor() imports without booting or loading the ThinkPHP framework. */
final class VendorLoader
{
    public static function import(string $class, string $extension = '.php'): bool
    {
        $image = 'topthink.think-image.src.';
        if (str_starts_with($class, $image)) {
            $name = 'app\\cms\\compat\\' . str_replace('.', '\\', substr($class, strlen($image)));
            return class_exists($name);
        }
        if ($extension !== '.php' || !preg_match('/^[A-Za-z0-9_-]+(?:\.[A-Za-z0-9_-]+)*$/D', $class)) return false;
        $root = realpath(base_path('vendor'));
        $file = realpath(base_path('vendor/' . str_replace('.', '/', $class) . $extension));
        if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) return false;
        require_once $file;
        return true;
    }
}
