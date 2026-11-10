<?php
declare(strict_types=1);
namespace app\cms\security;

/** One storage instance pins exactly the bytes authenticated or compiled for a render. */
final class TemplateCache
{
    private array $verified = [];
    public function __construct(private string $purpose, private string $scope, private int $lifetime = 0) {}
    private function context(string $path): string { return $this->scope . "\n" . $path; }
    public function write(string $path, string $bytes): void
    {
        $signed = SignedEnvelope::seal($this->purpose, $this->context($path), $bytes, $this->lifetime > 0 ? time()+$this->lifetime : 0);
        \app\cms\cache\CacheStorage::write($path, $signed, preg_match('/\.render-[a-f0-9]{32}$/D', $path) ? 60 : $this->lifetime);
        $this->verified[$path] = $bytes;
    }
    public function read(string $path): ?string
    {
        SignedEnvelope::requireKey();
        if (isset($this->verified[$path])) return $this->verified[$path];
        $raw = \app\cms\cache\CacheStorage::read($path);
        $bytes = $raw === null ? null : SignedEnvelope::open($this->purpose, $this->context($path), $raw);
        if ($bytes !== null) $this->verified[$path] = $bytes;
        return $bytes;
    }
}
