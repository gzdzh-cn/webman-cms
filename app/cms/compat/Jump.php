<?php
namespace app\cms\compat;
trait Jump
{
    protected function error($message='',...$args) {throw new \RuntimeException((string)$message);}
    protected function success($message='',...$args) {return ['code'=>1,'msg'=>$message];}
}
