<?php
declare(strict_types=1);
namespace app\cms\security;

/** PHP includes only a checked, render-scoped memory snapshot, never runtime paths. */
final class CompiledSnapshot
{
    public $context;
    private static array $snapshots = [];
    private static bool $registered = false;
    private string $bytes = '';
    private int $position = 0;

    public static function render(string $bytes, array $vars, ?callable $invoke = null): void
    {
        if (!self::$registered) {
            if (in_array('cmscompiled', stream_get_wrappers(), true)
                || !stream_wrapper_register('cmscompiled', self::class)) throw new \RuntimeException('安全模板流注册失败');
            self::$registered = true;
        }
        $uri = 'cmscompiled://' . bin2hex(random_bytes(32));
        self::$snapshots[$uri] = $bytes;
        try {
            $invoke ??= static function (string $__cms_snapshot_uri, array $__cms_snapshot_vars): void {
                extract($__cms_snapshot_vars, EXTR_SKIP);
                include $__cms_snapshot_uri;
            };
            $invoke($uri, $vars);
        } finally { unset(self::$snapshots[$uri]); }
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        if (!in_array($mode, ['r','rb'], true) || !isset(self::$snapshots[$path])) return false;
        $this->bytes = self::$snapshots[$path]; $this->position = 0; $openedPath = $path;
        return true;
    }
    public function stream_read(int $count): string { $value=substr($this->bytes,$this->position,$count);$this->position+=strlen($value);return $value; }
    public function stream_eof(): bool { return $this->position >= strlen($this->bytes); }
    public function stream_tell(): int { return $this->position; }
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        $next = match($whence) { SEEK_SET=>$offset, SEEK_CUR=>$this->position+$offset, SEEK_END=>strlen($this->bytes)+$offset, default=>-1 };
        if ($next < 0 || $next > strlen($this->bytes)) return false;
        $this->position=$next;return true;
    }
    public function stream_stat(): array { return ['mode'=>0100400,'size'=>strlen($this->bytes),'mtime'=>0]; }
    public function url_stat(string $path, int $flags): array|false { return isset(self::$snapshots[$path]) ? ['mode'=>0100400,'size'=>strlen(self::$snapshots[$path]),'mtime'=>0] : false; }
    public function stream_set_option(int $option, int $arg1, int $arg2): bool { return false; }
}
