<?php
declare(strict_types=1);
namespace app\cms\service;

/** A render-local anonymous session; never reads or writes the browser session store. */
final class GenerationSession extends \Workerman\Protocols\Http\Session
{
    public function __construct() { $this->sessionId = bin2hex(random_bytes(16)); }
    public function save(): void { $this->needSave = false; }
    public function refresh(): bool { return true; }
    public function __destruct() {}
}
