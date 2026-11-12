<?php
declare(strict_types=1);

// Load before Composer helpers: code belongs to the archive, data to the deployment.
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
function runtime_path(string $path = ''): string { return \app\cms\Paths::runtime($path); }
function public_path(string $path = '', ?string $plugin = null): string
{
    if ($plugin !== null && $plugin !== '') throw new \InvalidArgumentException('Business plugins are disabled');
    return \app\cms\Paths::publicRoot($path);
}
