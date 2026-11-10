<?php
declare(strict_types=1);

// The HTTP worker launches this fixed script; no command or path comes from the client.
if (PHP_SAPI !== 'cli' || count($argv) !== 2 || !preg_match('/^[a-f0-9]{32}$/D', $argv[1])) exit(1);
$root=dirname(__DIR__,3);
require $root.'/vendor/autoload.php';
if (!function_exists('pcntl_fork') || !function_exists('posix_setsid')) exit(1);
$pid=pcntl_fork();
if ($pid < 0) exit(1);
if ($pid > 0) exit(0);
if (posix_setsid() < 0) exit(1);
$pid=pcntl_fork();
if ($pid < 0) exit(1);
if ($pid > 0) exit(0);
try { (new \app\cms\service\AdminRestartService($root))->execute($argv[1]); }
catch (\Throwable) { fwrite(STDERR, "Admin restart executor failed.\n"); exit(1); }
