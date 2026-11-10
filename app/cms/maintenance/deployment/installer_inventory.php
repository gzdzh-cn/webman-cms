<?php
declare(strict_types=1);
/** Read-only installation-package inventory; no Webman bootstrap or database access. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('display_errors', '0');
ini_set('zend.exception_ignore_args', '1');
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, "Installation package inventory refused\n");
    exit(1);
});
$root = dirname(__DIR__, 4);
require $root . '/vendor/autoload.php';
$directory = (new \app\cms\install\InstallerArchive($root))->deploymentDirectory();
if ($directory !== null && $directory !== 'install' && !preg_match('/^install_[a-z]{12}$/D', $directory)) {
    throw new RuntimeException('Invalid installation archive directory');
}
echo json_encode(['directory' => $directory], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES), "\n";
