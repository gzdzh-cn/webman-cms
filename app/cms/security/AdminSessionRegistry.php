<?php
declare(strict_types=1);
namespace app\cms\security;

use app\cms\persistence\StateStore;
use Workerman\Protocols\Http\Session;

/** Durable revocation state is outside the replayable runtime session files. */
final class AdminSessionRegistry
{
    private const SCOPE = 'security:admin-sessions-v1';
    public function __construct(private ?StateStore $store = null) {}
    private function store(): StateStore { return $this->store ??= new StateStore(self::SCOPE); }
    private function name(string $id): string { return hash('sha256', $id); }
    public function register(string $id, int $adminId, string $nonce): void
    {
        if ($id === '' || $adminId < 1 || !preg_match('/^[a-f0-9]{64}$/D', $nonce)) throw new \InvalidArgumentException('管理员会话参数无效');
        $this->store()->write($this->name($id), ['session_hash'=>$this->name($id), 'admin_id'=>$adminId,
            'nonce_hash'=>hash('sha256', $nonce), 'expires'=>time() + Session::$lifetime]);
    }
    public function valid(string $id, array $identity): bool
    {
        $nonce = $identity['session_nonce'] ?? null;
        $adminId = $identity['id'] ?? null;
        if ($id === '' || !is_string($nonce) || !preg_match('/^[a-f0-9]{64}$/D', $nonce) || !is_numeric($adminId) || (int)$adminId < 1) return false;
        $record = $this->store()->read($this->name($id));
        return ($record['session_hash'] ?? null) === $this->name($id)
            && ($record['admin_id'] ?? null) === (int)$adminId
            && is_string($record['nonce_hash'] ?? null) && hash_equals($record['nonce_hash'], hash('sha256', $nonce))
            && is_int($record['expires'] ?? null) && $record['expires'] > time();
    }
    public function revoke(string $id): void
    {
        if ($id !== '') $this->store()->remove($this->name($id));
    }
}
