<?php
declare(strict_types=1);
namespace app\cms\support;

/** A binary may have no 'php' substring or mutable title in /proc/cmdline. */
class Worker extends \Workerman\Worker
{
    protected static function checkMasterIsAlive(int $masterPid): bool
    {
        if($masterPid<2||$masterPid===getmypid()||!posix_kill($masterPid,0))return false;
        try{return ProcessLauncher::master()===$masterPid;}catch(\Throwable){return false;}
    }
}
