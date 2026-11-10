<?php
declare(strict_types=1);

// A short-lived launcher forks a detached executor; HTTP workers never wait for restart.
if (PHP_SAPI !== 'cli' || count($argv) !== 3 || !preg_match('/^[a-f0-9]{24}$/D',$argv[1]) || !ctype_digit($argv[2])) exit(1);
$root = dirname(__DIR__, 3);
require $root.'/vendor/autoload.php';
if (!function_exists('pcntl_fork') || !function_exists('posix_setsid')) exit(1);
$pid = pcntl_fork();
if ($pid < 0) exit(1);
if ($pid > 0) exit(0);
if (posix_setsid() < 0) exit(1);
$pid = pcntl_fork();
if ($pid < 0) exit(1);
if ($pid > 0) exit(0);
try { (new \app\cms\install\InstallerHandoff($root))->execute($argv[1],(int)$argv[2]); }
catch (\Throwable $error) { fwrite(STDERR,"Installer restart executor failed.\n"); exit(1); }
