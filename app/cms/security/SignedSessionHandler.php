<?php
declare(strict_types=1);
namespace app\cms\security;

use app\cms\Paths;
use app\cms\cache\CachePolicy;
use app\cms\cache\RedisConnection;
use Workerman\Protocols\Http\Session;
use Workerman\Protocols\Http\Session\SessionHandlerInterface;

/** Webman owns transport/serialization; the handler authenticates its opaque bytes first. */
final class SignedSessionHandler implements SessionHandlerInterface
{
    private string $directory;
    public function __construct(array $config = [])
    {
        self::requireSerializer();
        SignedEnvelope::requireKey();
        $this->directory = rtrim($config['save_path'] ?? Paths::runtime('sessions'), '/');
    }
    public static function requireSerializer(): void
    {
        if (extension_loaded('igbinary') && ini_get('session.serialize_handler') === 'igbinary') {
            throw new \RuntimeException('安全会话仅支持 Webman 默认 PHP 序列化，请关闭 session.serialize_handler=igbinary');
        }
    }
    private function validId(string $id): void
    {
        if (!preg_match('/^[A-Za-z0-9,-]{16,128}$/D', $id)) throw new \InvalidArgumentException('会话标识无效');
    }
    private function path(string $id, array $policy): string
    {
        $this->validId($id);
        $generation = (string)$policy['session_generation'];
        return $this->directory . '/session_' . ($generation === 'legacy' ? '' : hash('sha256', $generation) . '_') . $id;
    }
    private function context(string $id, array $policy): string
    {
        $this->validId($id);
        if ($policy['mode'] !== '3' && $policy['session_generation'] === 'legacy') return $id;
        return $id . ':' . hash('sha256', (string)$policy['namespace'] . ':' . (string)$policy['session_generation']);
    }
    private function redisKey(string $id, array $policy): string
    {
        $this->validId($id);
        return RedisConnection::key('session:' . hash('sha256', (string)$policy['namespace'] . ':' . (string)$policy['session_generation']) . ':' . $id);
    }
    private function load(string $id, array $policy): ?string
    {
        if ($policy['mode'] !== '3') return RuntimeFiles::read($this->path($id, $policy));
        try {
            $client = RedisConnection::client();
            $client->clearLastError();
            $raw = $client->get($this->redisKey($id, $policy));
            if ($raw === false || $raw === null) {
                if ($client->getLastError() !== null) throw new \RuntimeException();
                return null;
            }
            if (!is_string($raw)) throw new \RuntimeException();
            return $raw;
        } catch (\Throwable) {
            RedisConnection::disconnect();
            throw SessionUnavailable::signal();
        }
    }
    private function store(string $id, string $bytes, array $policy): void
    {
        if ($policy['mode'] !== '3') { RuntimeFiles::write($this->path($id, $policy), $bytes); return; }
        try {
            if (Session::$lifetime <= 0 || RedisConnection::client()->setex($this->redisKey($id, $policy), Session::$lifetime, $bytes) !== true) throw new \RuntimeException();
        } catch (\Throwable) {
            RedisConnection::disconnect();
            throw SessionUnavailable::signal(true);
        }
    }
    public function open(string $savePath, string $name): bool { return true; }
    public function close(): bool { return true; }
    public function read(string $sessionId): string|false
    {
        $policy = CachePolicy::snapshot();
        $raw = $this->load($sessionId, $policy);
        if ($raw === null) return false;
        // The fixed selector is untrusted. Changing it selects a different HMAC
        // key purpose, so it cannot turn authenticated bytes into guest bytes.
        $purpose = match (substr($raw, 0, 2)) { "A\n" => 'session.auth', "G\n" => 'session.bootstrap.v2', default => null };
        if ($purpose === null) return false;
        return SignedEnvelope::open($purpose, $this->context($sessionId, $policy), substr($raw, 2)) ?? false;
    }
    public function write(string $sessionId, string $sessionData): bool
    {
        // Write-side bytes are serialized by Webman from its trusted in-memory
        // session, never loaded from disk here. Disable class construction.
        $state = unserialize($sessionData, ['allowed_classes' => false]);
        if (!is_array($state)) throw new \InvalidArgumentException('会话数据格式无效');
        // Only the exact installer transport may survive its intentional DB
        // switch. Every other state (including legacy identity fields) is bound
        // to the current deployment, even when it has no modern admin field.
        $bootstrap = true;
        $installerFields = ['cms_install_csrf' => 48, 'cms_install_handoff_owner' => 64, 'cms_install_upload_owner' => 64];
        foreach ($state as $name => $value) {
            if (!isset($installerFields[$name]) || !is_string($value)
                || !preg_match('/^[a-f0-9]{' . $installerFields[$name] . '}$/D', $value)) {
                $bootstrap = false;
                break;
            }
        }
        $authenticated = !$bootstrap;
        $purpose = $authenticated ? 'session.auth' : 'session.bootstrap.v2';
        $selector = $authenticated ? "A\n" : "G\n";
        $policy = CachePolicy::snapshot();
        $this->store($sessionId, $selector . SignedEnvelope::seal($purpose, $this->context($sessionId, $policy), $sessionData, time()+Session::$lifetime), $policy);
        return true;
    }
    public function updateTimestamp(string $sessionId, string $data = ''): bool
    {
        $verified = $this->read($sessionId);
        return $verified === false ? false : $this->write($sessionId, $verified);
    }
    public function destroy(string $sessionId): bool
    {
        $policy = CachePolicy::snapshot();
        if ($policy['mode'] !== '3') return RuntimeFiles::remove($this->path($sessionId, $policy));
        try {
            $result = RedisConnection::client()->del($this->redisKey($sessionId, $policy));
            if (!is_int($result)) throw new \RuntimeException();
            return true;
        } catch (\Throwable) {
            RedisConnection::disconnect();
            throw SessionUnavailable::signal(true);
        }
    }
    public function gc(int $maxLifetime): bool
    {
        if (CachePolicy::snapshot()['mode'] === '3') return true; // Redis expiry owns GC.
        foreach (glob($this->directory . '/session_*') ?: [] as $path) {
            if (is_link($path) || !is_file($path)) continue;
            if (time() - filemtime($path) > $maxLifetime) RuntimeFiles::remove($path);
        }
        return true;
    }
}
