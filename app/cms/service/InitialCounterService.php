<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

final class InitialCounterService
{
    public function __construct(private CmsRepository $repository) {}
    public static function canonicalRange(string $value):string
    {
        if(!preg_match('/^\d+(?:\|\d+)?$/D',$value))throw new InvalidArgumentException('默认计数请填写非负整数或最小值|最大值');
        $values=array_map('intval',explode('|',$value));foreach($values as$number)if($number>2147483647)throw new InvalidArgumentException('默认计数不能超过2147483647');sort($values,SORT_NUMERIC);return implode('|',$values);
    }
    public function draw(string $name,string $lang):int
    {
        if(!in_array($name,['other_arcclick','other_arcdownload'],true))throw new InvalidArgumentException('计数设置类型无效');
        $row=$this->repository->table('config')->where(['name'=>$name,'inc_type'=>'other','lang'=>$lang,'is_del'=>0])->find();
        if(!isset($row['value']))return 0;$value=self::canonicalRange(trim((string)$row['value']));$parts=array_map('intval',explode('|',$value));return count($parts)===1?$parts[0]:random_int($parts[0],$parts[1]);
    }
}
