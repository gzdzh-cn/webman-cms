<?php
declare(strict_types=1);

// Standalone provisioning runs before application bootstrap, which requires a key.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 3);
chdir($root);
require $root . '/vendor/autoload.php';
$options = getopt('', ['create:', 'check:']);
$path = $options['create'] ?? $options['check'] ?? '';
if (!is_string($path) || $path === '' || $path[0] !== '/' || str_contains($path, "\0")) {
    fwrite(STDERR, "用法: php app/cms/maintenance/security-key.php --create=/项目外私有目录/cms.key 或 --check=/绝对路径\n"); exit(1);
}
try {
    if (isset($options['create'])) {
        $parent = realpath(dirname($path));
        $application = realpath($root);
        if (!$parent || $parent !== dirname($path) || $parent === $application || str_starts_with($parent, $application . '/')) {
            throw new RuntimeException('请先创建项目外私有目录，并使用不含符号链接的绝对路径');
        }
        $stat = stat($parent);
        if (($stat['mode'] & 0022) !== 0) throw new RuntimeException('密钥目录不能被组或其他用户写入');
        $oldMask = umask(0077);
        try {
            $handle = @fopen($path, 'xb');
            if (!$handle) throw new RuntimeException('密钥文件已存在或无法创建，拒绝覆盖');
            try {
                $bytes = bin2hex(random_bytes(32)) . "\n";
                if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) throw new RuntimeException('密钥写入失败');
            } finally { fclose($handle); }
        } finally { umask($oldMask); }
    }
    \app\cms\security\SignedEnvelope::validateKeyFile($path);
    echo "安全密钥校验通过；未输出密钥内容。\n";
} catch (Throwable $error) { fwrite(STDERR, $error->getMessage() . "\n"); exit(1); }
