<?php

namespace app\process;

use Webman\App;
use Webman\Context;
use Webman\Http\Response;
use Workerman\Timer;
use Workerman\Protocols\Http\Session;
use app\cms\security\SessionUnavailable;

class Http extends App
{
    /** Legacy classes are owned by this application, not a discovered plugin. */
    public static function getPluginByClass(string $controllerClass)
    {
        if (str_starts_with(ltrim($controllerClass, '\\'), 'plugin\\admin\\')) return '';
        return parent::getPluginByClass($controllerClass);
    }

    /** Keep historical backend URL aliases without exposing implementation namespaces. */
    protected static function findRoute(\Workerman\Connection\TcpConnection $connection, string $path, string $key, $request, &$status): bool
    {
        return parent::findRoute($connection, \app\cms\admin\support\AdminRoutes::normalizePath($path), $key, $request, $status);
    }

    /** Close file responses only after Workerman finishes its asynchronous read. */
    protected static function send($connection, $response, $request)
    {
        // Webman destroys Context before releasing the request session. Finish
        // the session while the request's cache-policy snapshot is still pinned.
        $session = $request->context['session'] ?? null;
        $sessionFailed = (bool) Context::get(SessionUnavailable::CONTEXT_KEY, false);
        if ($session instanceof Session) {
            try {
                if (!$sessionFailed) {
                    $session->save();
                    if (random_int(1, Session::$gcProbability[1]) <= Session::$gcProbability[0]) $session->gc();
                }
            } catch (\Throwable) {
                $sessionFailed = true;
            } finally {
                // Workerman's public unserialize hook marks the object unsafe
                // for destruction, preventing an implicit second save or GC.
                $session->__unserialize([]);
                unset($request->context['session'], $session);
            }
        }
        if ($sessionFailed || Context::get(SessionUnavailable::CONTEXT_KEY, false)) {
            // Do not return a successful operation or issue a new session cookie
            // when its backing state could not be saved.
            if (isset($connection->headers['Set-Cookie']) && is_array($connection->headers['Set-Cookie'])) {
                $cookieName = Session::$name . '=';
                $connection->headers['Set-Cookie'] = array_values(array_filter(
                    $connection->headers['Set-Cookie'], static fn($cookie) => !str_starts_with((string)$cookie, $cookieName)
                ));
            }
            $response = new Response(503, ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'no-store'], '会话服务暂时不可用，请稍后重试');
        }
        $keepAlive = $request->header('connection');
        $persistent = ($keepAlive === null && $request->protocolVersion() === '1.1')
            || $keepAlive === 'keep-alive' || $keepAlive === 'Keep-Alive'
            || ($response instanceof Response && $response->getHeader('Transfer-Encoding') === 'chunked');
        if ($persistent || !($response instanceof Response) || !isset($response->file['file'])) {
            parent::send($connection, $response, $request);
            return;
        }

        Context::destroy();
        unset($request->context['session']);
        $response->header('Connection', 'close');
        $connection->send($response);
        $drain = $connection->onBufferDrain;
        if ($drain === null) {
            $connection->context->streamSending = false;
            $connection->close();
            return;
        }

        // The protocol clears onBufferDrain when either EOF or the requested
        // range is reached. Close() earlier would discard chunks read later.
        // Capture no request/response: a slow download must not retain them.
        $connection->onBufferDrain = static function ($connection) use ($drain): void {
            $drain($connection);
            if ($connection->onBufferDrain === null) {
                // Workerman does not clear this flag at a range boundary.
                $connection->context->streamSending = false;
                // baseWrite() invokes us after draining its previous buffer.
                // The stream may just have queued a new tail; changing to
                // CLOSING inside that stack would make it discard that tail.
                Timer::delay(0, static function () use ($connection): void { $connection->close(); });
            }
        };
    }
}
