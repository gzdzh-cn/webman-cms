<?php
declare(strict_types=1);
namespace app\cms\security;

/** A request-scoped failure marker survives controllers which catch the error. */
final class SessionUnavailable extends \RuntimeException
{
    public const CONTEXT_KEY = 'cms.session.unavailable';
    public static function signal(bool $mutation = false): self
    {
        \Webman\Context::set(self::CONTEXT_KEY, true);
        \app\cms\cache\RedisConnection::warning('session');
        if ($mutation && !\Webman\Context::get('cms.session.invalidated')) {
            \app\cms\cache\CachePolicy::rotate(true);
            \Webman\Context::set('cms.session.invalidated', true);
        }
        return new self('Redis 会话存储不可用');
    }
}
