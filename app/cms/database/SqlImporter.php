<?php
declare(strict_types=1);

namespace app\cms\database;

use PDO;
use RuntimeException;

/** Imports only new tables. Original SQL is never edited or executed wholesale. */
final class SqlImporter
{
    public static function statements(string $sql): array
    {
        $statements = []; $buffer = ''; $quote = null; $length = strlen($sql);
        for ($i = 0; $i < $length; ++$i) {
            $char = $sql[$i];
            if ($quote !== null) {
                $buffer .= $char;
                if ($char === '\\' && $i + 1 < $length) { $buffer .= $sql[++$i]; continue; }
                if ($char === $quote) {
                    if (($sql[$i + 1] ?? '') === $quote) { $buffer .= $sql[++$i]; } else { $quote = null; }
                }
                continue;
            }
            // MySQL accepts whitespace/control after --, including a bare dump separator line.
            $dashComment = $char === '-' && ($sql[$i + 1] ?? '') === '-'
                && isset($sql[$i + 2]) && (ctype_space($sql[$i + 2]) || ctype_cntrl($sql[$i + 2]));
            if ($dashComment || $char === '#') {
                while ($i < $length && $sql[$i] !== "\n" && $sql[$i] !== "\r") { ++$i; }
                $buffer .= "\n"; continue;
            }
            if ($char === '/' && ($sql[$i + 1] ?? '') === '*') {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) { throw new RuntimeException('Unclosed SQL comment'); }
                if (($sql[$i + 2] ?? '') === '!') {
                    $body = preg_replace('/^\d+\s*/', '', trim(substr($sql, $i + 3, $end - $i - 3)));
                    if (!preg_match('/^(?:SET\s|ALTER TABLE\s+`?[A-Za-z0-9_]+`?\s+(?:ENABLE|DISABLE) KEYS$)/i', $body)) throw new RuntimeException('SQL 含不支持的可执行注释');
                }
                $i = $end + 1; continue;
            }
            if (in_array($char, ["'", '"', '`'], true)) { $quote = $char; $buffer .= $char; continue; }
            if ($char === ';') { if (trim($buffer) !== '') { $statements[] = trim($buffer); } $buffer = ''; continue; }
            $buffer .= $char;
        }
        if ($quote !== null) { throw new RuntimeException('Unclosed SQL quote'); }
        if (trim($buffer) !== '') { $statements[] = trim($buffer); }
        return $statements;
    }

    public static function tables(string $file, string $sourcePrefix, string $targetPrefix, bool $convertInnoDB = true, bool $strict = false): array
    {
        $groups = [];
        foreach (self::statements((string)file_get_contents($file)) as $statement) {
            if (preg_match('/^CREATE TABLE(?: IF NOT EXISTS)?\s+`([^`]+)`/i', $statement, $matches)) {
                $sourceName = $matches[1];
                if (!str_starts_with($sourceName, $sourcePrefix)) { throw new RuntimeException('Unexpected source table'); }
                $name = $targetPrefix . substr($sourceName, strlen($sourcePrefix));
                $statement = preg_replace('/^CREATE TABLE(?: IF NOT EXISTS)?\s+`[^`]+`/i', 'CREATE TABLE `' . $name . '`', $statement, 1);
                $statement = self::createOptions($statement, $convertInnoDB, $strict);
                if (isset($groups[$sourceName])) throw new RuntimeException('SQL 中重复定义表：' . $sourceName);
                self::identifier($name);
                $groups[$sourceName] = ['name' => $name, 'create' => $statement, 'inserts' => []];
            } elseif (preg_match('/^INSERT INTO\s+`([^`]+)`/i', $statement, $matches)) {
                if (!isset($groups[$matches[1]])) { throw new RuntimeException('Insert has no source table'); }
                $name = $groups[$matches[1]]['name'];
                if ($strict) self::validateInsert($statement);
                $groups[$matches[1]]['inserts'][] = preg_replace('/^INSERT INTO\s+`[^`]+`/i', 'INSERT INTO `' . $name . '`', $statement, 1);
            } elseif (!preg_match('/^(?:DROP TABLE(?: IF EXISTS)?\s+`[A-Za-z0-9_]+`$|SET\s|LOCK TABLES\s|UNLOCK TABLES$|BEGIN$|COMMIT$|START TRANSACTION$|USE\s+`?[A-Za-z0-9_]+`?$|CREATE DATABASE(?: IF NOT EXISTS)?\s+`?[A-Za-z0-9_]+`?(?:\s.*)?$|ALTER TABLE\s+`?[A-Za-z0-9_]+`?\s+(?:ENABLE|DISABLE) KEYS$)/i', $statement)) {
                throw new RuntimeException('Unsupported statement in source SQL');
            }
        }
        if (!$groups) throw new RuntimeException('SQL 文件未包含支持的表结构');
        return array_values($groups);
    }


    public static function detectPrefix(string $file): string
    {
        if (!is_file($file) || is_link($file) || filesize($file) > 50 * 1024 * 1024) throw new RuntimeException('SQL 文件无效或超过 50 MiB');
        $candidates = [];
        foreach (self::statements((string)file_get_contents($file)) as $sql) {
            if (preg_match('/^CREATE TABLE(?: IF NOT EXISTS)?\s+`([A-Za-z0-9_]+)`/i', $sql, $m)) {
                foreach (['config', 'arctype', 'archives', 'channeltype', 'language'] as $suffix) {
                    if (str_ends_with($m[1], '_' . $suffix)) $candidates[substr($m[1], 0, -strlen($suffix))][$suffix] = true;
                }
            }
        }
        $prefixes = array_keys(array_filter($candidates, static fn(array $tables): bool => count($tables) === 5));
        if (count($prefixes) !== 1) throw new RuntimeException('无法唯一识别 EyouCMS 源表前缀，请上传包含完整结构的单个 SQL');
        self::identifier($prefixes[0] . 'config');
        return $prefixes[0];
    }

    public static function renameTable(array $table, string $name): array
    {
        self::identifier($name);
        $table['name'] = $name;
        $table['create'] = preg_replace('/^CREATE TABLE(?: IF NOT EXISTS)?\s+`[^`]+`/i', 'CREATE TABLE `' . $name . '`', $table['create'], 1);
        $table['inserts'] = array_map(static fn(string $sql): string => preg_replace('/^INSERT INTO\s+`[^`]+`/i', 'INSERT INTO `' . $name . '`', $sql, 1), $table['inserts']);
        return $table;
    }

    private static function identifier(string $name): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/D', $name)) throw new RuntimeException('表名无效或转换后超过 64 字节：' . $name);
    }

    /** Keep string literals byte-for-byte; only table options outside the body change. */
    private static function createOptions(string $sql, bool $convert, bool $strict): string
    {
        $masked = self::maskQuoted($sql);
        if ($strict && preg_match('/\b(?:FOREIGN\s+KEY|REFERENCES|SELECT|TABLESPACE|PARTITION|DATA\s+DIRECTORY|INDEX\s+DIRECTORY|CONNECTION|UNION)\b/i', $masked)) throw new RuntimeException('暂不支持外键、分区或外部存储表定义');
        $end = strrpos($masked, ')');
        if ($end === false) throw new RuntimeException('建表语句不完整');
        $head = substr($sql, 0, $end + 1); $tail = substr($sql, $end + 1);
        if (!preg_match('/\bENGINE\s*=\s*([A-Za-z0-9_]+)/i', self::maskQuoted($tail), $engine)) throw new RuntimeException('建表语句必须明确指定存储引擎');
        if (!in_array(strtolower($engine[1]), ['innodb','myisam'], true)) throw new RuntimeException('不支持的存储引擎：' . $engine[1]);
        if ($strict && preg_match('/\b(?:GENERATED|CHECK|AS|SYSTEM|ENCRYPTION|PASSWORD)\b/i', self::maskQuoted($tail))) throw new RuntimeException('不支持该建表选项');
        if ($convert) foreach (['ENGINE'=>'InnoDB','ROW_FORMAT'=>'DYNAMIC'] as $option=>$value) {
            // Match offsets on the masked SQL; never change quoted comments/defaults.
            if (preg_match('/\b'.$option.'\s*=\s*\w+/i', self::maskQuoted($tail), $match, PREG_OFFSET_CAPTURE)) {
                $tail = substr_replace($tail, $option.'='.$value, $match[0][1], strlen($match[0][0]));
            }
        }
        return $head . $tail;
    }

    private static function maskQuoted(string $sql): string
    {
        $result = ''; $length = strlen($sql);
        for ($i = 0; $i < $length; ++$i) {
            $char = $sql[$i];
            if ($char !== "'" && $char !== '"') { $result .= $char; continue; }
            $quote = $char; $result .= ' ';
            while (++$i < $length) {
                $result .= ' ';
                if ($sql[$i] === '\\' && $i + 1 < $length) { ++$i; $result .= ' '; continue; }
                if ($sql[$i] === $quote) {
                    if (($sql[$i + 1] ?? '') === $quote) { ++$i; $result .= ' '; continue; }
                    break;
                }
            }
        }
        return $result;
    }

    /** Backups may contain values, never INSERT SELECT, SQL functions or clauses. */
    private static function validateInsert(string $sql): void
    {
        if (!preg_match('/^INSERT INTO\s+`[A-Za-z0-9_]+`\s*(?:\(\s*`[A-Za-z0-9_]+`(?:\s*,\s*`[A-Za-z0-9_]+`)*\s*\)\s*)?VALUES\s*/i', $sql, $match)) throw new RuntimeException('只支持 INSERT INTO ... VALUES 数据语句');
        $i = strlen($match[0]); $length = strlen($sql);
        $space = static function () use (&$i, $length, $sql): void { while ($i < $length && ctype_space($sql[$i])) ++$i; };
        do {
            $space(); if (($sql[$i++] ?? '') !== '(') throw new RuntimeException('SQL 数据行格式错误');
            do {
                $space();
                if (preg_match('/\G(?:_binary|N)(?=[\'\"])/iA', $sql, $prefix, 0, $i)) $i += strlen($prefix[0]);
                if (($sql[$i] ?? '') === "'" || ($sql[$i] ?? '') === '"') {
                    $quote = $sql[$i++]; $closed = false;
                    while ($i < $length) {
                        $char = $sql[$i++];
                        if ($char === '\\') { ++$i; continue; }
                        if ($char === $quote) { if (($sql[$i] ?? '') === $quote) { ++$i; continue; } $closed = true; break; }
                    }
                    if (!$closed) throw new RuntimeException('SQL 数据字符串未闭合');
                } elseif (preg_match('/\G(?:NULL|0x[0-9a-f]+|[-+]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][-+]?\d+)?)/iA', $sql, $literal, 0, $i)) { $i += strlen($literal[0]); }
                else throw new RuntimeException('SQL 数据只能包含字符串、数字或 NULL，不能包含表达式');
                $space(); $separator = $sql[$i++] ?? '';
                if ($separator !== ',' && $separator !== ')') throw new RuntimeException('SQL 数据含不支持的表达式或语法');
            } while ($separator === ',');
            $space(); if ($i === $length) return;
            if (($sql[$i++] ?? '') !== ',') throw new RuntimeException('SQL 数据行后包含不支持的语句');
        } while ($i < $length);
        throw new RuntimeException('SQL 数据语句不完整');
    }

    /** Decode already validated literal rows for schema/seed preflight; never eval SQL. */
    public static function rows(array $table): \Generator
    {
        $all = self::columns($table['create']);
        if (!$all) throw new RuntimeException('无法识别 SQL 字段定义');
        foreach ($table['inserts'] as $sql) {
            self::validateInsert($sql);
            preg_match('/^INSERT INTO\s+`[A-Za-z0-9_]+`\s*(\([^)]*\)\s*)?VALUES\s*/i', $sql, $head);
            $columns = $all;
            if (!empty($head[1])) {
                preg_match_all('/`([A-Za-z0-9_]+)`/', $head[1], $selected); $columns=$selected[1];
                if (count(array_unique($columns)) !== count($columns) || array_diff($columns,$all)) throw new RuntimeException('SQL 数据包含未知或重复字段');
            }
            $i = strlen($head[0]); $length = strlen($sql);
            while ($i < $length) {
                while ($i < $length && (ctype_space($sql[$i]) || $sql[$i] === ',')) ++$i;
                if ($i >= $length) break;
                if ($sql[$i++] !== '(') throw new RuntimeException('SQL 数据行格式错误');
                $values=[];
                while ($i < $length) {
                    while ($i < $length && ctype_space($sql[$i])) ++$i;
                    if (preg_match('/\G(?:_binary|N)(?=[\'\"])/iA', $sql, $prefix, 0, $i)) $i+=strlen($prefix[0]);
                    if ($sql[$i] === "'" || $sql[$i] === '"') {
                        $quote=$sql[$i++];$value='';
                        while ($i < $length) {
                            $char=$sql[$i++];
                            if ($char === '\\') {
                                $next=$sql[$i++];$value.=match($next){'0'=>"\0",'b'=>"\x08",'n'=>"\n",'r'=>"\r",'t'=>"\t",'Z'=>"\x1a",'_','%'=>'\\'.$next,default=>$next};continue;
                            }
                            if ($char===$quote) {if(($sql[$i]??'')===$quote){$value.=$quote;++$i;continue;}break;}
                            $value.=$char;
                        }
                    } else {
                        preg_match('/\G(?:NULL|0x[0-9a-f]+|[-+]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][-+]?\d+)?)/iA',$sql,$literal,0,$i);
                        $value=strcasecmp($literal[0],'NULL')===0?null:$literal[0];$i+=strlen($literal[0]);
                    }
                    $values[]=$value;while($i<$length&&ctype_space($sql[$i]))++$i;
                    $separator=$sql[$i++];if($separator===')')break;
                }
                if(count($values)!==count($columns))throw new RuntimeException('SQL 数据列数与结构不一致：'.$table['name']);
                yield array_combine($columns,$values);
            }
        }
    }

    /** Split CREATE body at its own comma depth, supporting single-line backups. */
    private static function columns(string $sql): array
    {
        $start=strpos($sql,'(');if($start===false)return [];
        $depth=1;$quote=null;$part='';$parts=[];$length=strlen($sql);
        for($i=$start+1;$i<$length;++$i){
            $char=$sql[$i];
            if($quote!==null){
                $part.=$char;
                if($char==='\\'&&$i+1<$length){$part.=$sql[++$i];continue;}
                if($char===$quote){if(($sql[$i+1]??'')===$quote)$part.=$sql[++$i];else $quote=null;}
                continue;
            }
            if(in_array($char,["'",'"','`'],true)){$quote=$char;$part.=$char;continue;}
            if($char==='(')++$depth;
            elseif($char===')'){--$depth;if($depth===0){$parts[]=$part;break;}}
            if($char===','&&$depth===1){$parts[]=$part;$part='';continue;}
            $part.=$char;
        }
        $columns=[];foreach($parts as $definition)if(preg_match('/^\s*`([A-Za-z0-9_]+)`\s+[A-Za-z]/',$definition,$match))$columns[]=$match[1];
        return $columns;
    }

    public static function import(PDO $pdo, array $tables, bool $dryRun): array
    {
        $created = []; $skipped = [];
        $check = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        foreach ($tables as $table) {
            $name = $table['name']; $check->execute([$name]);
            if ($check->fetchColumn()) { $skipped[] = $name; continue; }
            if (!$dryRun) {
                $pdo->exec($table['create']);
                try {
                    $pdo->beginTransaction();
                    foreach ($table['inserts'] as $statement) { $pdo->exec($statement); }
                    $pdo->commit();
                } catch (\Throwable $error) {
                    if ($pdo->inTransaction()) { $pdo->rollBack(); }
                    // Only the empty table created by this process is removed after failed import.
                    $pdo->exec('DROP TABLE `' . $name . '`');
                    throw new RuntimeException('Seed import failed for ' . $name . ' (SQLSTATE ' . $error->getCode() . ')');
                }
            }
            $created[] = $name;
        }
        return ['created' => $created, 'preserved_existing' => $skipped];
    }
}
