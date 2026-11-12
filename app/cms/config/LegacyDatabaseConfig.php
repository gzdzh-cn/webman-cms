<?php
declare(strict_types=1);
namespace app\cms\config;

use app\cms\config\Environment;
use PhpParser\Node;
use PhpParser\ParserFactory;

final class LegacyDatabaseConfig
{
    private static array $savedCache = [];
    private const BEGIN = '// BEGIN CMS DATABASE SETTINGS';
    private const END = '// END CMS DATABASE SETTINGS';
    private const KEYS = ['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASSWORD','DB_PREFIX','DB_TABLE_LAYOUT','CMS_DB_PREFIX','ADMIN_DB_PREFIX'];
    private const DEFAULTS = ['DB_HOST'=>'127.0.0.1','DB_PORT'=>'3306','DB_NAME'=>'','DB_USER'=>'','DB_PASSWORD'=>'','DB_PREFIX'=>'wa_','DB_TABLE_LAYOUT'=>'','CMS_DB_PREFIX'=>'ey_','ADMIN_DB_PREFIX'=>'wa_'];
    private static function path(?string $root): string
    {
        $base = realpath($root ?? dirname(__DIR__, 3));
        if (!$base || !is_dir($base) || realpath($base.'/config') !== $base.'/config') throw new \RuntimeException('配置目录无效');
        $path = $base.'/config/database.php';
        clearstatcache(true, $path);
        if (is_link($path) || !is_file($path) || !is_readable($path)) throw new \RuntimeException('数据库配置文件无效');
        $stat = lstat($path);
        if (!$stat || $stat['nlink'] !== 1 || $stat['size'] > 1048576) throw new \RuntimeException('数据库配置文件状态无效');
        return $path;
    }
    private static function parse(string $contents): array
    {
        // Locate only standalone PHP comment tokens; marker-looking text in
        // a quoted password is data and never defines the block boundary.
        $markers = []; $offset = 0;
        foreach (token_get_all($contents) as $token) {
            $text = is_array($token) ? $token[1] : $token;
            if (is_array($token) && $token[0] === T_COMMENT) {
                $comment = rtrim($text, " \t\r");
                if ($comment === self::BEGIN || $comment === self::END) {
                    $prefix = substr($contents, 0, $offset);
                    $line = strrpos($prefix, "\n");
                    $indent = substr($prefix, $line === false ? 0 : $line + 1);
                    $rest = substr($contents, $offset + strlen($text));
                    $newline = strpos($rest, "\n");
                    $suffix = $newline === false ? $rest : substr($rest, 0, $newline);
                    if (!preg_match('/^[ \t]*$/D', $indent) || !preg_match('/^[ \t\r]*$/D', $suffix)) throw new \RuntimeException('数据库配置标记必须独占一行');
                    $markers[$comment][] = [$offset, $offset + strlen($text)];
                }
            }
            $offset += strlen($text);
        }
        if (count($markers[self::BEGIN] ?? []) !== 1 || count($markers[self::END] ?? []) !== 1) throw new \RuntimeException('数据库配置受管块无效');
        $begin = $markers[self::BEGIN][0][1];
        $end = $markers[self::END][0][0];
        if ($end < $begin) throw new \RuntimeException('数据库配置受管块无效');
        $parser = (new ParserFactory())->createForHostVersion();
        try {
            $parser->parse($contents);
            $nodes = $parser->parse('<?php '.substr($contents, $begin, $end - $begin));
        } catch (\PhpParser\Error $e) { throw new \RuntimeException('数据库配置语法无效', 0, $e); }
        if (count($nodes) !== 1 || !$nodes[0] instanceof Node\Stmt\Expression || !$nodes[0]->expr instanceof Node\Expr\Assign
            || !$nodes[0]->expr->var instanceof Node\Expr\Variable || $nodes[0]->expr->var->name !== 'cmsDatabaseSettings'
            || !$nodes[0]->expr->expr instanceof Node\Expr\Array_) throw new \RuntimeException('数据库配置受管块结构无效');
        $out = [];
        foreach ($nodes[0]->expr->expr->items as $item) {
            if (!$item || $item->byRef || $item->unpack || !$item->key instanceof Node\Scalar\String_ || !$item->value instanceof Node\Scalar\String_
                || !in_array($item->key->value, self::KEYS, true) || array_key_exists($item->key->value, $out)) throw new \RuntimeException('数据库配置仅允许固定字段及字符串字面量');
            $out[$item->key->value] = $item->value->value;
        }
        return [$out, $begin, $end];
    }
    public static function saved(?string $root = null): array
    {
        $path = self::path($root);
        $bytes = file_get_contents($path);
        if (!is_string($bytes)) throw new \RuntimeException('读取数据库配置失败');
        $digest = hash('sha256', $bytes);
        if ((self::$savedCache[$path]['digest'] ?? null) !== $digest) self::$savedCache[$path] = ['digest'=>$digest, 'values'=>self::parse($bytes)[0]];
        return self::$savedCache[$path]['values'];
    }
}
