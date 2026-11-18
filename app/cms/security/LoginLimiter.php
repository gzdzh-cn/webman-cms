<?php
declare(strict_types=1);
namespace app\cms\security;
use app\cms\persistence\StateStore;
use support\exception\BusinessException;
final class LoginLimiter
{
    public function attempt(string $username):void
    {
        $store=new StateStore('security:login');
        $blocked=$store->locked(function()use($store,$username):bool{
            $key=md5($username).'.limit';$time=date('YmdH').ceil((int)date('i')/5);
            $record=$store->read($key);
            if(!$record||($record['time']??null)!=$time)$record=['username'=>$username,'count'=>0,'time'=>$time];
            $record['count']=(int)$record['count']+1;$store->write($key,$record);
            return $record['count']>=5;
        });
        if($blocked)throw new BusinessException('登录失败次数过多，请5分钟后再试');
    }
    public function clear(string $username):void
    {
        $store=new StateStore('security:login');
        $store->locked(fn()=>$store->remove(md5($username).'.limit'));
    }
}
