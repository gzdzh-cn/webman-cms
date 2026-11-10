<?php
declare(strict_types=1);
namespace app\cms\config;

use Dotenv\Exception\InvalidFileException;
use Dotenv\Loader\Resolver;
use Dotenv\Parser\Parser;
use Dotenv\Repository\RepositoryInterface;

/** Parse local or proposed dotenv bytes without modifying the process environment. */
final class EnvironmentParser
{
    public static function parse(string $text, array $process): array
    {
        if (strlen($text) > 1048576 || str_contains($text, "\0")) throw new InvalidFileException('.env 文件无效');
        // phpdotenv silently drops an unterminated multiline double-quoted
        // assignment; reject it instead of treating it as an absent key.
        $quoted = false; $escaped = false;
        foreach (preg_split('/\r\n|\n|\r/', $text) as $line) {
            if (!$quoted) {
                if (!preg_match('/^\s*(?:export\s+)?[A-Za-z_][A-Za-z0-9_.]*\s*=\s*"/', $line, $m)) continue;
                $line = substr($line, strlen($m[0])); $quoted = true; $escaped = false;
            }
            for ($i=0, $n=strlen($line); $i<$n; $i++) {
                if ($escaped) { $escaped=false; continue; }
                if ($line[$i] === "\\") { $escaped=true; continue; }
                if ($line[$i] === '"') { $quoted=false; break; }
            }
            $escaped=false;
        }
        if ($quoted) throw new InvalidFileException('.env 引号未闭合');
        $values = [];
        $repo = new class($values) implements RepositoryInterface {
            public function __construct(public array $data) {}
            public function has(string $name) { return array_key_exists($name, $this->data); }
            public function get(string $name) { return $this->data[$name] ?? null; }
            public function set(string $name, string $value) { $this->data[$name] = $value; return true; }
            public function clear(string $name) { unset($this->data[$name]); return true; }
        };
        $repo->data = $process;
        try { $entries = (new Parser())->parse($text); }
        catch (InvalidFileException $error) {
            // Parser messages can include entire credential lines. Never expose them.
            throw new InvalidFileException('.env 格式无效，请检查配置文件');
        }
        foreach ($entries as $entry) {
            $key = $entry->getName();
            if (array_key_exists($key, $values)) throw new InvalidFileException('.env 存在重复键');
            $value = $entry->getValue()->getOrElse(null);
            if ($value === null) throw new InvalidFileException('.env 键缺少值');
            $values[$key] = Resolver::resolve($repo, $value);
            $repo->set($key, $values[$key]);
        }
        return $values;
    }
}
