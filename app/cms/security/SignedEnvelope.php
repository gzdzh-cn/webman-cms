<?php
declare(strict_types=1);
namespace app\cms\security;

use app\cms\Paths;
use RuntimeException;

/** Authenticates bytes before any cache metadata, PHP or serialized value is read. */
final class SignedEnvelope
{
    private const VERSION = 'CMSSEC1';
    public const MAX_BYTES = 33554432;
    private static ?string $key = null;
    private static array $lastWarning = [];

    public static function requireKey(): void { self::key(); }

    private static function key(): string
    {
        if (self::$key !== null) return self::$key;
        $settings = require Paths::root('config/cms_security.php');
        return self::$key = self::readKey((string)$settings['key_file']);
    }

    /** Standalone CLI provisioning validates the explicitly requested file. */
    public static function validateKeyFile(string $path): void { self::readKey($path); }

    private static function readKey(string $path): string
    {
        if ($path === '' || $path[0] !== '/' || str_contains($path, "\0")) {
            throw new RuntimeException('请配置项目外私有 CMS_SECURITY_KEY_FILE，禁止使用未签名缓存');
        }
        clearstatcache(true);
        $actual = realpath($path);
        $root = realpath(Paths::root());
        if ($actual !== $path || !$root || str_starts_with($actual, $root . '/') || $actual === $root
            || !is_file($actual) || is_link($actual)) throw new RuntimeException('安全密钥必须是项目外的独立普通文件');
        $stat = stat($actual);
        if (!$stat || ($stat['mode'] & 0022) !== 0 || $stat['nlink'] !== 1 || $stat['size'] > 128) {
            throw new RuntimeException('安全密钥权限或文件类型不安全');
        }
        $parent = dirname($actual);
        $parentStat = stat($parent);
        if (!$parentStat || ($parentStat['mode'] & 0022) !== 0) throw new RuntimeException('安全密钥目录不能被组或其他用户写入');
        $raw = file_get_contents($actual);
        if (!is_string($raw) || !preg_match('/^[a-f0-9]{64}\n?$/D', $raw)) throw new RuntimeException('安全密钥必须是32字节随机密钥的64位小写十六进制');
        return hex2bin(rtrim($raw, "\n"));
    }

    private static function site(string $purpose): string
    {
        // Anonymous installer transport must survive the intentional database
        // switch. The writer admits only exact installer transport fields to
        // this purpose; all other session state remains deployment-bound.
        if ($purpose === 'session.bootstrap.v2') return hash('sha256', Paths::root() . ':anonymous-transport-v2');
        $mode = '';
        if ($purpose === 'session.auth') {
            $path = Paths::root('config/cms-installed.json');
            if (is_link($path)) throw new RuntimeException('安装身份配置不能是链接');
            if (is_file($path)) {
                if (filesize($path) > 65536) throw new RuntimeException('安装身份配置过大');
                $installed = json_decode((string)file_get_contents($path), true, 16, JSON_THROW_ON_ERROR);
                $mode = $installed['template_mode'] ?? 'legacy';
                if (!in_array($mode, ['eyou', 'webman', 'legacy'], true)) throw new RuntimeException('安装模板模式无效');
            }
        }
        // No database or runtime-cache reads during session authentication.
        $database = \app\cms\database\DatabaseSettings::values();
        return hash('sha256', json_encode(['effective-config-v2', Paths::root(), $database['DB_HOST'], $database['DB_PORT'],
            $database['DB_NAME'], \app\cms\database\TableNames::cms(), \app\cms\database\TableNames::admin(), $mode], JSON_THROW_ON_ERROR));
    }

    public static function seal(string $purpose, string $context, string $payload, int $expires = 0): string
    {
        if (strlen($payload) > self::MAX_BYTES) throw new RuntimeException('安全缓存超过大小限制');
        $header = json_encode(['v'=>1, 'purpose'=>$purpose, 'site'=>self::site($purpose), 'context'=>hash('sha256', $context),
            'issued'=>time(), 'expires'=>$expires, 'size'=>strlen($payload)], JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        $key = hash_hmac('sha256', $purpose, self::key(), true);
        return self::VERSION . "\n" . $header . "\n" . hash_hmac('sha256', $header . "\n" . $payload, $key) . "\n" . $payload;
    }

    public static function open(string $purpose, string $context, string $raw): ?string
    {
        $key = hash_hmac('sha256', $purpose, self::key(), true);
        if (strlen($raw) > self::MAX_BYTES + 4096) return self::rejected($purpose);
        $parts = explode("\n", $raw, 4);
        if (count($parts) !== 4 || $parts[0] !== self::VERSION || strlen($parts[1]) > 2048
            || !preg_match('/^[a-f0-9]{64}$/D', $parts[2])) return self::rejected($purpose);
        if (!hash_equals(hash_hmac('sha256', $parts[1] . "\n" . $parts[3], $key), $parts[2])) return self::rejected($purpose);
        try { $meta = json_decode($parts[1], true, 16, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { return self::rejected($purpose); }
        if (!is_array($meta) || ($meta['v'] ?? null) !== 1 || ($meta['purpose'] ?? null) !== $purpose
            || ($meta['site'] ?? null) !== self::site($purpose) || ($meta['context'] ?? null) !== hash('sha256', $context)
            || ($meta['size'] ?? null) !== strlen($parts[3]) || !is_int($meta['issued'] ?? null)
            || $meta['issued'] > time() + 30 || !is_int($meta['expires'] ?? null)
            || ($meta['expires'] !== 0 && $meta['expires'] <= time())) return self::rejected($purpose);
        return $parts[3];
    }

    private static function rejected(string $purpose): null
    {
        // Bounded per-process warnings contain no payload, identifiers or secrets.
        if ((self::$lastWarning[$purpose] ?? 0) + 60 <= time()) {
            self::$lastWarning[$purpose] = time();
            try {
                if (\config('log.default') !== null) \support\Log::warning('CMS security: rejected unauthenticated or expired runtime data (' . $purpose . ')');
            }
            catch (\Throwable) { /* Offline tooling can run before logger bootstrap. */ }
        }
        return null;
    }
}
