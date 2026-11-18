<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Retains the existing language-operation callback while UI data lives in its DB transaction. */
class UiLanguageDeletionCache
{
    private CmsRepository $repository;
    public function __construct(?CmsRepository $repository=null, ?string $runtimeRoot=null, ?string $templateRoot=null)
    {
        $this->repository=$repository??new CmsRepository();
    }
    public function run(array $marks, callable $operation): mixed
    {
        $initial=$this->marks($marks,true);
        $themes=$this->repository->table('ui_config')->distinct(true)->column('theme_style');
        $names=[];
        foreach($themes as $theme)$names[]='cms-ui-'.substr(hash('sha256',(string)$theme),0,56);
        $names=array_values(array_unique($names));sort($names,SORT_STRING);$locks=[];$prepared=null;
        try {
            foreach($names as $name){
                $result=$this->repository->connection()->query('SELECT GET_LOCK(?,5) acquired',[$name]);
                if((int)($result[0]['acquired']??0)!==1)throw new InvalidArgumentException('页面配置正在更新，请稍后重试');
                $locks[]=$name;
            }
            $prepare=function(array $actual=[])use($initial,&$prepared):void{
                $selected=$this->marks($actual?:$initial);
                if($prepared!==null&&$prepared!==$selected)throw new InvalidArgumentException('待删除语言范围已改变');
                $prepared=$selected;
            };
            $result=$operation($prepare);
            return $result;
        } finally {
            foreach(array_reverse($locks) as $name)$this->repository->connection()->query('SELECT RELEASE_LOCK(?)',[$name]);
        }
    }
    private function marks(array $marks,bool $allowEmpty=false):array
    {
        foreach($marks as $mark)if(!is_string($mark)||!preg_match('/^[A-Za-z0-9_-]{2,50}$/D',$mark))throw new InvalidArgumentException('语言标识格式不正确');
        $marks=array_values(array_unique($marks));sort($marks,SORT_STRING);
        if(!$marks&&!$allowEmpty)throw new InvalidArgumentException('待删除语言不能为空');
        return $marks;
    }
}
