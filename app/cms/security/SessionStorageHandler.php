<?php
declare(strict_types=1);
namespace app\cms\security;

use app\cms\Paths;
use app\cms\cache\CachePolicy;
use app\cms\cache\RedisConnection;
use Workerman\Protocols\Http\Session;
use Workerman\Protocols\Http\Session\SessionHandlerInterface;

/** Webman serialization with request-pinned file/Redis storage; no signing key. */
final class SessionStorageHandler implements SessionHandlerInterface
{
    private string $directory;
    public function __construct(array $config = [])
    {
        self::requireSerializer();
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
        return $this->directory . '/session_v2_' . hash('sha256', (string)$policy['namespace'] . ':' . $generation) . '_' . $id;
    }
    private function redisKey(string $id, array $policy): string
    {
        $this->validId($id);
        return RedisConnection::key('session-v2:' . hash('sha256', (string)$policy['namespace'] . ':' . (string)$policy['session_generation']) . ':' . $id);
    }
    private function bootstrapPath(string $id): string
    {
        $this->validId($id);
        return $this->directory . '/session_v2_install_' . hash('sha256', Paths::root()) . '_' . $id;
    }
    private function installerState(array $state): bool
    {
        if (!$state) return false;
        $fields = ['cms_install_csrf'=>48, 'cms_install_handoff_owner'=>64, 'cms_install_upload_owner'=>64];
        foreach ($state as $name=>$value) if (!isset($fields[$name]) || !is_string($value)
            || !preg_match('/^[a-f0-9]{'.$fields[$name].'}$/D', $value)) return false;
        return true;
    }
    private function fileRead(string $path): ?string
    {
        $bytes = RuntimeFiles::read($path);
        if ($bytes === null || filemtime($path) + Session::$lifetime <= time()) return null;
        return $bytes;
    }
    private function load(string $id, array $policy): ?string
    {
        if ($policy['mode'] !== '3') return $this->fileRead($this->path($id, $policy));
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
        $bootstrap = false;
        if ($raw === null && $policy['mode'] !== '3') {
            $raw = $this->fileRead($this->bootstrapPath($sessionId));
            $bootstrap = true;
        }
        if ($raw === null) return false;
        if (strlen($raw)>RuntimeFiles::MAX_BYTES || str_starts_with($raw,"CMSSEC1\n") || preg_match('/^[AG]\nCMSSEC1\n/',$raw)) return false;
        try { $state=@unserialize($raw,['allowed_classes'=>false]); } catch(\Throwable) { return false; }
        return is_array($state)&&$this->plain($state)&&(!$bootstrap||$this->installerState($state)) ? $raw : false;
    }
    private function plain(mixed $value,int $depth=0):bool
    {
        if($depth>128||is_object($value)||is_resource($value))return false;
        if(is_array($value))foreach($value as$item)if(!$this->plain($item,$depth+1))return false;
        return true;
    }
    public function write(string $sessionId, string $sessionData): bool
    {
        if(strlen($sessionData)>RuntimeFiles::MAX_BYTES)throw new \InvalidArgumentException('会话数据超过大小限制');
        try{$state=@unserialize($sessionData,['allowed_classes'=>false]);}catch(\Throwable){$state=null;}
        if(!is_array($state)||!$this->plain($state))throw new \InvalidArgumentException('会话数据格式无效');
        $policy = CachePolicy::snapshot();
        if ($policy['mode'] !== '3') {
            if ($this->installerState($state)) {
                RuntimeFiles::write($this->bootstrapPath($sessionId), $sessionData);
                RuntimeFiles::remove($this->path($sessionId, $policy));
                return true;
            }
            RuntimeFiles::remove($this->bootstrapPath($sessionId));
        }
        $this->store($sessionId,$sessionData,$policy);
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
        if ($policy['mode'] !== '3') {
            $bootstrap = RuntimeFiles::remove($this->bootstrapPath($sessionId));
            return RuntimeFiles::remove($this->path($sessionId, $policy)) && $bootstrap;
        }
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
        foreach (glob($this->directory . '/session_v2_*') ?: [] as $path) {
            if (is_link($path) || !is_file($path)) continue;
            if (time() - filemtime($path) > $maxLifetime) RuntimeFiles::remove($path);
        }
        return true;
    }
}
