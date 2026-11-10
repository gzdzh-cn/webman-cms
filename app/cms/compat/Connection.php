<?php
namespace app\cms\compat;

/** Keep legacy connection-created builders inside the same query adapter. */
final class Connection
{
    public function __construct(private object $connection) {}

    /** Internal bridge for legacy static operations sharing this connection. */
    public function native(): object { return $this->connection; }

    public function transaction(callable $callback)
    {
        return $this->connection->transaction(fn () => $callback($this));
    }

    public function name(string $name): Query
    {
        return new Query($this->connection->name(Db::normalizeName($name)), $this->prefix());
    }

    public function table($table): Query
    {
        return new Query($this->connection->table(Db::expand($table, $this->prefix())), $this->prefix());
    }

    private function prefix(): string
    {
        return (string)$this->connection->getConfig('prefix');
    }

    public function __call($method, $arguments)
    {
        $result = $this->connection->$method(...$arguments);
        return $result instanceof \think\db\BaseQuery ? new Query($result, $this->prefix()) : $result;
    }
}
