<?php
declare(strict_types=1);
namespace app\cms\install;

use app\cms\database\SqlImporter;
use RuntimeException;

/** Keeps uploaded CREATE statements but never carries uploaded content into an empty installation. */
final class StructureSeeder
{
    public static function apply(array $source, array $trusted): array
    {
        $base=[];foreach($trusted as $table)$base[$table['name']]=$table;
        foreach($source as &$table){
            $table['inserts']=[];
            if(!isset($base[$table['name']]))continue;
            $definitions=self::definitions($table['create']);
            foreach(SqlImporter::rows($base[$table['name']]) as $row){
                foreach($row as $column=>$value){
                    if(!isset($definitions[$column]))throw new RuntimeException('源结构缺少空站基础字段：'.$table['name'].'.'.$column);
                    if(preg_match('/\b(?:GENERATED|AS\s*\()/i',$definitions[$column]))throw new RuntimeException('空站基础字段不能为生成列：'.$table['name'].'.'.$column);
                    self::value($table['name'].'.'.$column,$definitions[$column],$value);
                }
                foreach($definitions as $column=>$definition){
                    if(array_key_exists($column,$row))continue;
                    if(preg_match('/\bNOT\s+NULL\b/i',self::mask($definition))&&!preg_match('/\b(?:DEFAULT|AUTO_INCREMENT|GENERATED)\b/i',self::mask($definition)))throw new RuntimeException('源结构新增必填字段没有默认值，不能建立空站基础数据：'.$table['name'].'.'.$column);
                }
                $columns=array_keys($row);
                $table['inserts'][]='INSERT INTO `'.$table['name'].'` (`'.implode('`,`',$columns).'`) VALUES ('.implode(',',array_map(self::literal(...),array_values($row))).')';
            }
        }unset($table);
        return $source;
    }

    /** Split only the CREATE body's top-level fields; commas inside types/defaults are preserved. */
    private static function definitions(string $sql): array
    {
        $start=strpos($sql,'(');if($start===false)throw new RuntimeException('源建表语句没有字段定义');
        $depth=1;$quote=null;$part='';$parts=[];$length=strlen($sql);
        for($i=$start+1;$i<$length;++$i){
            $char=$sql[$i];
            if($quote!==null){$part.=$char;if($char==='\\'&&$i+1<$length){$part.=$sql[++$i];continue;}if($char===$quote){if(($sql[$i+1]??'')===$quote)$part.=$sql[++$i];else $quote=null;}continue;}
            if(in_array($char,["'",'"','`'],true)){$quote=$char;$part.=$char;continue;}
            if($char==='(')++$depth;elseif($char===')'){--$depth;if($depth===0){$parts[]=$part;break;}}
            if($char===','&&$depth===1){$parts[]=$part;$part='';continue;}$part.=$char;
        }
        $result=[];foreach($parts as $definition)if(preg_match('/^\s*`([A-Za-z0-9_]+)`\s+(.+)$/s',$definition,$m))$result[$m[1]]=$m[2];
        if(!$result)throw new RuntimeException('无法识别源 SQL 字段定义');return $result;
    }
    private static function value(string $field,string $definition,mixed $value): void
    {
        $masked=self::mask($definition);
        if($value===null){if(preg_match('/\bNOT\s+NULL\b/i',$masked))throw new RuntimeException('源结构不允许基础数据的空值：'.$field);return;}
        if(preg_match('/^(?:var)?(?:char|binary)\s*\(\s*(\d+)\s*\)/i',$definition,$m)){
            $length=preg_match('/binary/i',$m[0])?strlen((string)$value):mb_strlen((string)$value,'UTF-8');
            if($length>(int)$m[1])throw new RuntimeException('源字段长度不足以保存空站基础数据：'.$field);return;
        }
        if(preg_match('/^(tinytext|tinyblob|text|blob|mediumtext|mediumblob|longtext|longblob)\b/i',$definition,$m)){
            $max=match(strtolower($m[1])){'tinytext','tinyblob'=>255,'text','blob'=>65535,'mediumtext','mediumblob'=>16777215,default=>4294967295};
            if(strlen((string)$value)>$max)throw new RuntimeException('源字段容量不足以保存空站基础数据：'.$field);return;
        }
        if(preg_match('/^(tinyint|smallint|mediumint|int|integer|bigint)\b/i',$definition,$m)){
            if(!preg_match('/^-?\d+$/D',(string)$value))throw new RuntimeException('源数值字段不能保存空站基础数据：'.$field);
            $bits=match(strtolower($m[1])){'tinyint'=>8,'smallint'=>16,'mediumint'=>24,'int','integer'=>32,default=>64};$unsigned=(bool)preg_match('/\bUNSIGNED\b/i',$masked);
            $number=(float)$value;$min=$unsigned?0:-(2**($bits-1));$max=$unsigned?(2**$bits)-1:(2**($bits-1))-1;
            if($number<$min||$number>$max)throw new RuntimeException('源数值字段范围不足以保存空站基础数据：'.$field);return;
        }
        if(preg_match('/^(?:decimal|numeric)\s*\(\s*(\d+)\s*,\s*(\d+)\s*\)/i',$definition,$m)){
            if(!preg_match('/^-?(\d+)(?:\.(\d+))?$/D',(string)$value,$v)||strlen(ltrim($v[1],'0'))>(int)$m[1]-(int)$m[2]||strlen(rtrim($v[2]??'','0'))>(int)$m[2]||(str_starts_with((string)$value,'-')&&preg_match('/\bUNSIGNED\b/i',$masked)))throw new RuntimeException('源精度不足以保存空站基础数据：'.$field);return;
        }
        if(preg_match('/^(enum|set)\s*\((.*?)\)/is',$definition,$m)){
            preg_match_all('/\'(?:\\\\.|\'\'|[^\'])*\'/s',$m[2],$options);$allowed=[];
            foreach($options[0] as $option){$decoded=iterator_to_array(SqlImporter::rows(['name'=>'__cms_choice','create'=>'CREATE TABLE `__cms_choice` (`v` text)','inserts'=>['INSERT INTO `__cms_choice` VALUES ('.$option.')']]),false);$allowed[]=$decoded[0]['v'];}
            $actual=strtolower($m[1])==='set'?explode(',',(string)$value):[(string)$value];
            if(array_diff($actual,$allowed))throw new RuntimeException('源枚举字段不能保存空站基础数据：'.$field);return;
        }
        if(preg_match('/^(?:float|double|real)\b/i',$definition)){if(!is_numeric($value)||!is_finite((float)$value))throw new RuntimeException('源浮点字段不能保存空站基础数据：'.$field);return;}
        // Fail closed for a changed or unrecognized seed-bearing type; table creation
        // without seeds is still allowed and checked by the normal staging importer.
        throw new RuntimeException('空站基础字段使用暂不支持的源类型：'.$field);
    }
    private static function literal(mixed $value): string
    {
        if($value===null)return 'NULL';
        return "'".strtr((string)$value,["\\"=>"\\\\","\0"=>"\\0","\n"=>"\\n","\r"=>"\\r","\x1a"=>"\\Z","'"=>"\\'"])."'";
    }
    private static function mask(string $text): string
    {
        return preg_replace('/\'(?:\\\\.|\'\'|[^\'])*\'|"(?:\\\\.|""|[^"])*"/s',' ', $text);
    }
}
