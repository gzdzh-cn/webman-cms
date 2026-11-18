<?php
declare(strict_types=1);
namespace app\cms\persistence;

use PDO;

/** Durable JSON records and immutable SHA objects. Runtime is never authoritative. */
final class StateStore
{
    private PDO $pdo;
    private string $prefix;
    private int $lockDepth = 0;

    public function __construct(private string $scope, ?PDO $pdo = null, ?string $prefix = null)
    {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9:_.-]{0,95}$/D', $scope)) throw new \InvalidArgumentException('持久化范围无效');
        $this->pdo = $pdo ?? Connection::open();
        $this->prefix = $prefix ?? Connection::prefix();
        Schema::prefix($this->prefix);
    }

    public function scope(): string { return $this->scope; }
    public function connection(): PDO { return $this->pdo; }
    public function prefix(): string { return $this->prefix; }

    /** Advisory locks span individual committed writes, preserving per-page progress. */
    public function locked(callable $operation): mixed
    {
        if ($this->lockDepth > 0) return $operation($this);
        $database = (string)$this->pdo->query('SELECT DATABASE()')->fetchColumn();
        $name = 'cms-state-' . substr(hash('sha256', $database . ':' . $this->prefix . ':' . $this->scope), 0, 53);
        $query = $this->pdo->prepare('SELECT GET_LOCK(?,10)'); $query->execute([$name]);
        if ((int)$query->fetchColumn() !== 1) throw new \RuntimeException('持久化状态正在更新，请稍后重试');
        $this->lockDepth++;
        try { return $operation($this); }
        finally { $this->lockDepth--; $release = $this->pdo->prepare('SELECT RELEASE_LOCK(?)'); $release->execute([$name]); }
    }

    public function read(string $name, array $default = []): array
    {
        $row = $this->record($name);
        if ($row === null) return $default;
        if (!hash_equals($row['sha256'], hash('sha256', $row['payload']))) throw new \RuntimeException('持久化记录校验失败');
        try { $data = json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR); }
        catch (\JsonException $error) { throw new \RuntimeException('持久化记录损坏', 0, $error); }
        if (!is_array($data)) throw new \RuntimeException('持久化记录格式无效');
        return $data;
    }

    public function has(string $name): bool { return $this->record($name) !== null; }

    public function write(string $name, array $value): void
    {
        $this->name($name);
        $bytes = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $query = $this->pdo->prepare('INSERT INTO `' . $this->prefix . 'cms_state_records` (`scope`,`name`,`payload`,`sha256`,`updated_at`) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE `payload`=VALUES(`payload`),`sha256`=VALUES(`sha256`),`updated_at`=VALUES(`updated_at`)');
        $query->execute([$this->scope, $name, $bytes, hash('sha256', $bytes), time()]);
    }

    public function remove(string $name): void
    {
        $this->name($name);
        $query = $this->pdo->prepare('DELETE FROM `' . $this->prefix . 'cms_state_records` WHERE `scope`=? AND `name`=?'); $query->execute([$this->scope, $name]);
    }

    public function names(): array
    {
        $query = $this->pdo->prepare('SELECT `name` FROM `' . $this->prefix . 'cms_state_records` WHERE `scope`=? ORDER BY `name`'); $query->execute([$this->scope]);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function putObject(string $bytes, string $extension): array
    {
        if (!in_array($extension, ['html','xml','txt'], true)) throw new \InvalidArgumentException('生成文件类型无效');
        $hash = hash('sha256', $bytes); $name = $hash . '.' . $extension;
        return $this->transaction(function () use ($bytes, $hash, $name): array {
            $query = $this->pdo->prepare('INSERT IGNORE INTO `' . $this->prefix . 'cms_state_objects` (`sha256`,`payload`,`bytes`,`created_at`) VALUES (?,?,?,?)');
            $query->execute([$hash, $bytes, strlen($bytes), time()]);
            $query = $this->pdo->prepare('SELECT `payload`,`bytes` FROM `' . $this->prefix . 'cms_state_objects` WHERE `sha256`=? FOR UPDATE'); $query->execute([$hash]); $stored = $query->fetch(PDO::FETCH_ASSOC);
            if (!$stored || (int)$stored['bytes'] !== strlen($bytes) || !hash_equals($hash, hash('sha256', $stored['payload']))) throw new \RuntimeException('生成内容快照校验失败');
            $query = $this->pdo->prepare('INSERT INTO `' . $this->prefix . 'cms_state_object_refs` (`scope`,`name`,`sha256`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `sha256`=VALUES(`sha256`)'); $query->execute([$this->scope, $name, $hash]);
            return ['object'=>$name,'sha256'=>$hash,'bytes'=>strlen($bytes)];
        });
    }

    public function objectBytes(array $entry): ?string
    {
        if (!isset($entry['object'], $entry['sha256'])) return null;
        $this->objectName((string)$entry['object']);
        $query = $this->pdo->prepare('SELECT o.`payload`,o.`bytes`,o.`sha256` FROM `' . $this->prefix . 'cms_state_object_refs` r JOIN `' . $this->prefix . 'cms_state_objects` o ON o.`sha256`=r.`sha256` WHERE r.`scope`=? AND r.`name`=?');
        $query->execute([$this->scope, $entry['object']]); $row = $query->fetch(PDO::FETCH_ASSOC);
        if (!$row || !hash_equals((string)$entry['sha256'], $row['sha256']) || (int)$row['bytes'] !== strlen($row['payload']) || !hash_equals($row['sha256'], hash('sha256', $row['payload']))) return null;
        return $row['payload'];
    }

    /** Remove this namespace's reference; immutable bodies are retained for recovery/other namespaces. */
    public function removeObject(array $entry): bool
    {
        $this->objectName((string)($entry['object'] ?? ''));
        $query = $this->pdo->prepare('SELECT `sha256` FROM `' . $this->prefix . 'cms_state_object_refs` WHERE `scope`=? AND `name`=?'); $query->execute([$this->scope, $entry['object']]);
        if ($query->fetchColumn() === false) return true;
        if ($this->objectBytes($entry) === null) return false;
        $query = $this->pdo->prepare('DELETE FROM `' . $this->prefix . 'cms_state_object_refs` WHERE `scope`=? AND `name`=? AND `sha256`=?'); $query->execute([$this->scope, $entry['object'], $entry['sha256']]);
        return $query->rowCount() === 1;
    }

    public function transaction(callable $operation): mixed
    {
        $owned = !$this->pdo->inTransaction();
        if ($owned) $this->pdo->beginTransaction();
        try { $result = $operation(); if ($owned) $this->pdo->commit(); return $result; }
        catch (\Throwable $error) { if ($owned && $this->pdo->inTransaction()) $this->pdo->rollBack(); throw $error; }
    }

    private function record(string $name): ?array
    {
        $this->name($name);
        $query = $this->pdo->prepare('SELECT `payload`,`sha256` FROM `' . $this->prefix . 'cms_state_records` WHERE `scope`=? AND `name`=?'); $query->execute([$this->scope, $name]);
        return $query->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    private function name(string $name): void { if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.:-]{0,190}$/D', $name)) throw new \InvalidArgumentException('持久化记录名称无效'); }
    private function objectName(string $name): void { if (!preg_match('/^[a-f0-9]{64}\.(?:html|xml|txt)$/D', $name)) throw new \RuntimeException('生成快照名称无效'); }
}
