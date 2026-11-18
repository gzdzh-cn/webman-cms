<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\repository\CmsRepository;
use app\cms\support\CronExpression;

/** Database state survives restarts; a durable claim prevents duplicate minute submissions. */
final class BaiduPushScheduleService
{
    private CmsRepository $repository;
    private BaiduPushService $push;
    public function __construct(?CmsRepository $repository=null, ?BaiduPushService $push=null)
    {
        $this->repository=$repository??new CmsRepository();
        $this->push=$push??new BaiduPushService($this->repository);
    }

    private function store(string $lang): LanguageStateStore { return new LanguageStateStore('baidu-schedule.'.$lang,$this->repository); }
    private function language(array $input): string
    {
        $lang=$input['lang']??CmsContext::current()->lang;
        if (!is_string($lang)||!preg_match('/^[a-z][a-z0-9_-]{1,49}$/iD',$lang)) throw new \InvalidArgumentException('语言参数错误');
        (new LanguageAccessService($this->repository))->assertLanguages([$lang]);
        if (!$this->repository->table('language')->where('mark',$lang)->find()) throw new \InvalidArgumentException('语言不存在');
        return $lang;
    }
    private function defaults(): array { return ['enabled'=>false,'cron'=>'0 9 * * *','timezone'=>'Asia/Shanghai','limit'=>500,'site'=>'','confirm_http'=>false,'cursor'=>0,'last_slot'=>0,'lease_until'=>0,'history'=>[]]; }
    public function status(array $input=[],?int $now=null): array
    {
        $lang=$this->language($input);$state=$this->store($lang)->read('schedule',$this->defaults());
        return $this->present($lang,$state,$now??time());
    }
    private function present(string $lang,array $state,int $now): array
    {
        $next=null;
        if ($state['enabled']) $next=(new CronExpression($state['cron']))->next((new \DateTimeImmutable('@'.$now))->setTimezone(new \DateTimeZone($state['timezone'])))->format(DATE_ATOM);
        return array_intersect_key($state,array_flip(['enabled','cron','timezone','limit','site','cursor','history']))+['lang'=>$lang,'next_run'=>$next,'running'=>($state['lease_until']??0)>$now];
    }
    public function save(array $input): array
    {
        $lang=$this->language($input);$enabled=$input['enabled']??'0';
        if (!is_string($enabled)||!in_array($enabled,['0','1'],true)) throw new \InvalidArgumentException('定时开关无效');
        $cron=$input['cron']??'0 9 * * *';$zone=$input['timezone']??'Asia/Shanghai';$limit=$input['limit']??'500';
        if (!is_string($cron)||!is_string($zone)||!in_array($zone,\DateTimeZone::listIdentifiers(),true)) throw new \InvalidArgumentException('Cron或时区格式无效');
        if (!is_scalar($limit)||!preg_match('/^\d{1,4}$/D',(string)$limit)||(int)$limit<1||(int)$limit>2000) throw new \InvalidArgumentException('每次推送数量须为1至2000');
        $expression=new CronExpression($cron);$now=time();$expression->next((new \DateTimeImmutable('@'.$now))->setTimezone(new \DateTimeZone($zone)));
        $site='';
        if ($enabled==='1') {
            $status=$this->push->status(['lang'=>$lang]);
            if (!$status['configured']||!$status['site']) throw new \InvalidArgumentException('请先保存当前语言的推送Token及公开网站根网址');
            if (($input['confirm_http']??'')!=='1') throw new \InvalidArgumentException('启用定时推送须确认百度HTTP传输Token和网址');
            $site=$status['site'];
        }
        $store=$this->store($lang);
        return $store->locked(function()use($store,$lang,$enabled,$expression,$zone,$limit,$site,$now){
            $state=$store->read('schedule',$this->defaults());
            if ($enabled === '0') {
                // Keep in-flight identity, cursor and site. Its receipt cannot re-enable us.
                $state['enabled'] = false;
                $state['confirm_http'] = false;
                $state['updated_at'] = $now;
                $state['revision'] = bin2hex(random_bytes(12));
                $store->write('schedule', $state);
                return $this->present($lang, $state, $now);
            }
            if (($state['lease_until']??0)>$now) throw new \InvalidArgumentException('推送正在执行，请稍后保存');
            $changed=$state['site']!==$site;
            $state=array_replace($state,['enabled'=>$enabled==='1','cron'=>$expression->expression,'timezone'=>$zone,'limit'=>(int)$limit,'site'=>$site,'confirm_http'=>$enabled==='1','updated_at'=>$now,'revision'=>bin2hex(random_bytes(12))]);
            // Saving never immediately submits, and cannot re-arm the current minute.
            $state['last_slot']=max((int)$state['last_slot'],intdiv($now,60)*60);
            if ($changed) { $state['cursor']=0; unset($state['quota_day'],$state['quota_site'],$state['remaining']); }
            $store->write('schedule',$state);
            return $this->present($lang,$state,$now);
        });
    }

    /** Only worker/CLI calls this method; there is no public trigger route. */
    public function tick(?int $now=null): array
    {
        $results = [];
        $count = count((new LanguagePolicy($this->repository))->publicLanguages());
        for ($i = 0; $i < $count; $i++) {
            $next = $this->tickOne($now);
            if (!$next) break;
            $results += $next;
        }
        return $results;
    }

    /** At most one request. $after is this worker's last observed instant, never offline history. */
    public function tickOne(?int $now = null, ?int $after = null): array
    {
        if (CmsContext::current()->request || !in_array(PHP_SAPI,['cli','micro'],true)) throw new \RuntimeException('定时调度仅允许后台进程执行');
        $instant = $now ?? time();
        $after ??= intdiv($instant, 60) * 60 - 1;
        $candidates = [];
        foreach ((new LanguagePolicy($this->repository))->publicLanguages() as $language) {
            $lang = (string)$language['mark'];
            $state = $this->store($lang)->read('schedule', $this->defaults());
            if (!$state['enabled'] || empty($state['confirm_http']) || ($state['lease_until'] ?? 0) > $instant) continue;
            $date = (new \DateTimeImmutable('@'.max($after, (int)$state['last_slot'])))->setTimezone(new \DateTimeZone($state['timezone']));
            $slot = (new CronExpression($state['cron']))->next($date)->getTimestamp();
            if ($slot <= $instant) $candidates[] = ['lang'=>$lang, 'slot'=>$slot, 'revision'=>$state['revision'] ?? null];
        }
        usort($candidates, static fn($a, $b) => [$a['slot'],$a['lang']] <=> [$b['slot'],$b['lang']]);
        foreach ($candidates as $candidate) {
            $lang = $candidate['lang']; $slot = $candidate['slot']; $store = $this->store($lang);
            $claim=$store->locked(function()use($store,$instant,$slot,$candidate){
                $state=$store->read('schedule',$this->defaults());
                if (!$state['enabled'] || empty($state['confirm_http']) || $state['last_slot'] >= $slot || ($state['lease_until']??0)>$instant || ($state['revision']??null) !== $candidate['revision']) return null;
                $state['last_slot']=$slot;$state['lease_until']=$instant+900;$state['run_id']=bin2hex(random_bytes(12));
                $store->write('schedule',$state);
                return $state;
            });
            if (!$claim) continue;
            $result=['at'=>$instant,'status'=>'failed','message'=>'定时推送失败'];$cursor=$claim['cursor'];$quota=[];
            try {
                $status=$this->push->status(['lang'=>$lang]);
                if (!$status['configured']||$status['site']!==$claim['site']) throw new \InvalidArgumentException('Token已清除或网站根网址已变更，请核对并重新保存定时设置');
                $day=(new \DateTimeImmutable('@'.$instant))->setTimezone(new \DateTimeZone('Asia/Shanghai'))->format('Y-m-d');
                if (($claim['quota_day']??'')===$day&&($claim['quota_site']??'')===$claim['site']&&($claim['remaining']??null)===0) {
                    $result=['at'=>$instant,'status'=>'skipped','message'=>'百度返回的当日额度已用完，次日计划再执行'];
                } else {
                    $limit=isset($claim['remaining'])&&($claim['quota_day']??'')===$day ? min($claim['limit'],$claim['remaining']) : $claim['limit'];
                    $preview=$this->push->preview(['lang'=>$lang,'offset'=>$cursor,'limit'=>$limit]);
                    if (!$preview['selected']&&$preview['total']>0) $cursor=0;
                    $pushed=$this->push->push(['lang'=>$lang,'offset'=>$cursor,'limit'=>$limit,'confirm_http'=>'1'], function () use ($store, $claim): bool {
                        return $store->locked(function () use ($store, $claim): bool {
                            $current = $store->read('schedule', $this->defaults());
                            if (empty($current['enabled']) || empty($current['confirm_http']) || ($current['run_id']??'') !== $claim['run_id'] || ($current['revision']??null) !== ($claim['revision']??null)) return false;
                            $current['dispatching'] = true;
                            $store->write('schedule', $current);
                            return true;
                        });
                    });
                    $cursor=$pushed['next_offset']>=$pushed['total']?0:$pushed['next_offset'];
                    $result=['at'=>$instant,'status'=>$pushed['unaccepted']?'partial':'success','message'=>'提交'.$pushed['submitted'].'条，接收'.$pushed['accepted'].'条，未接收'.$pushed['unaccepted'].'条，剩余额度'.$pushed['remaining'],'submitted'=>$pushed['submitted'],'accepted'=>$pushed['accepted'],'unaccepted'=>$pushed['unaccepted'],'remaining'=>$pushed['remaining']];
                    $quota=['quota_day'=>$day,'quota_site'=>$claim['site'],'remaining'=>$pushed['remaining']];
                }
            } catch (\Throwable $error) {
                if ($error instanceof PushCancelled) {
                    $result['status'] = 'cancelled';
                    $result['message'] = '自动推送已关闭，本次请求未发送';
                } else {
                // Known public service diagnostics contain no provider response or credential.
                $safe=($error instanceof \InvalidArgumentException||$error instanceof \RuntimeException) && preg_match('/^(?:Token已清除|连接百度|百度|请先保存百度|当前范围|请先在基本设置)/u',$error->getMessage());
                $result['message']=$safe ? $error->getMessage() : '定时推送发生内部错误，请检查服务与数据库状态';
                }
            }
            $store->locked(function()use($store,$claim,$result,$cursor,$quota){
                $state=$store->read('schedule',$this->defaults());
                if (($state['run_id']??'')!==$claim['run_id']) return;
                $state=array_replace($state,$quota,['cursor'=>$cursor,'lease_until'=>0,'dispatching'=>false,'history'=>array_slice([$result,...$state['history']],0,20)]);
                $store->write('schedule',$state);
            });
            return [$lang=>$result];
        }
        return [];
    }
}
