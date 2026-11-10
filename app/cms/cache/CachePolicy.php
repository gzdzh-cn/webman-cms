<?php
declare(strict_types=1);
namespace app\cms\cache;

use app\cms\CmsContext;
use app\cms\persistence\StateStore;
use app\cms\repository\CmsRepository;

/** Database-authoritative epochs. A request retains its original storage selection. */
final class CachePolicy
{
    private const SCOPE = 'cache-policy';
    private const RECORD = 'epochs';

    public static function snapshot(): array
    {
        $pinned = \Webman\Context::get('cms.cache.policy');
        if (is_array($pinned)) return $pinned;
        $namespace = self::namespace();
        $installation = (new \app\cms\install\InstallerService())->status();
        if (!empty($installation['blocked'])) throw new \RuntimeException('安装状态无效');
        if (!$installation['installed']) {
            $policy = ['mode'=>'1', 'cache_generation'=>'legacy', 'session_generation'=>'legacy', 'namespace'=>$namespace];
            \Webman\Context::set('cms.cache.policy', $policy);
            return $policy;
        }
        $repository = new CmsRepository();
        $language = $repository->fullName('language'); $config = $repository->fullName('config'); $records = $repository->fullName('cms_state_records');
        // One statement observes the mode and epochs from the same committed snapshot.
        $rows = $repository->connection()->query("SELECT c.value AS mode,s.payload,s.sha256 FROM (SELECT mark FROM `$language` ORDER BY id ASC LIMIT 1) l LEFT JOIN `$config` c ON c.lang=l.mark AND c.name='web_cmsmode' AND c.is_del=0 LEFT JOIN `$records` s ON s.scope=? AND s.name=? LIMIT 2", [self::SCOPE, self::RECORD]);
        if (count($rows) > 1) throw new \RuntimeException('网站模式配置重复');
        $row = $rows[0] ?? [];
        $mode = self::validate($row['mode'] ?? '1');
        $epochs = ['cache_generation'=>'legacy', 'session_generation'=>'legacy'];
        if (isset($row['payload'])) {
            if (!hash_equals((string)$row['sha256'], hash('sha256', $row['payload']))) throw new \RuntimeException('缓存代际记录校验失败');
            $epochs = json_decode($row['payload'], true, 16, JSON_THROW_ON_ERROR);
            self::validateEpochs($epochs);
        }
        $policy = ['mode'=>$mode, 'namespace'=>$namespace] + $epochs;
        \Webman\Context::set('cms.cache.policy', $policy);
        return $policy;
    }

    private static function validateEpochs(mixed $epochs): void
    {
        foreach (['cache_generation', 'session_generation'] as $name) {
            if (!is_array($epochs) || !is_string($epochs[$name] ?? null)
                || !preg_match('/^(?:legacy|[a-f0-9]{32})$/D', $epochs[$name])) throw new \RuntimeException('缓存代际记录无效');
        }
    }

    public static function namespace(): string
    {
        $db = \app\cms\database\DatabaseSettings::values();
        return hash('sha256', json_encode([\app\cms\Paths::root(), $db['DB_HOST'], $db['DB_PORT'], $db['DB_NAME'],
            \app\cms\database\TableNames::cms(), \app\cms\database\TableNames::admin()], JSON_THROW_ON_ERROR));
    }
    public static function validate(mixed $mode): string
    {
        if (is_string($mode) && preg_match('/^[sib]:/', $mode)) $mode = @unserialize($mode, ['allowed_classes'=>false]);
        if (!is_scalar($mode) || !in_array((string)$mode, ['1','2','3'], true)) throw new \InvalidArgumentException('网站模式必须为1、2或3');
        return (string)$mode;
    }
    public static function isRedis(): bool { return self::snapshot()['mode'] === '3'; }
    public static function cacheGeneration(): string { return \Webman\Context::get('cms.cache.generation') ?? self::snapshot()['cache_generation']; }
    public static function isOperational(?int $mode = null): bool { return in_array($mode ?? (int)self::snapshot()['mode'], [1,3], true); }
    public static function prepareTransition(string $mode): void { if (self::validate($mode) === '3') RedisConnection::probe(); }

    public static function assertTransactional(CmsRepository $repository): void
    {
        foreach (['language','config','cms_state_records'] as $table) {
            $rows = $repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$repository->fullName($table)]);
            if (strtolower((string)($rows[0]['ENGINE'] ?? '')) !== 'innodb') throw new \RuntimeException('模式切换要求配置和状态表使用 InnoDB，请先完成数据库维护');
        }
    }

    /** Use the content transaction's PDO so settings and epochs commit together. */
    public static function rotate(bool $sessions = false, ?CmsRepository $repository = null): array
    {
        $repository ??= new CmsRepository();
        return $repository->connection()->transaction(function () use ($repository, $sessions): array {
            $table = $repository->fullName('cms_state_records');
            $initial = json_encode(['cache_generation'=>'legacy','session_generation'=>'legacy'], JSON_THROW_ON_ERROR);
            $repository->connection()->execute("INSERT IGNORE INTO `$table` (`scope`,`name`,`payload`,`sha256`,`updated_at`) VALUES (?,?,?,?,?)", [self::SCOPE,self::RECORD,$initial,hash('sha256',$initial),time()]);
            $repository->table('cms_state_records')->where(['scope'=>self::SCOPE,'name'=>self::RECORD])->lock(true)->find();
            $pdo = $repository->connection()->getPdo();
            if (!$pdo instanceof \PDO) throw new \RuntimeException('缓存代际数据库连接不可用');
            $store = new StateStore(self::SCOPE, $pdo, substr($table, 0, -strlen('cms_state_records')));
            $epochs = $store->read(self::RECORD);
            self::validateEpochs($epochs);
            $epochs['cache_generation'] = bin2hex(random_bytes(16));
            if ($sessions) $epochs['session_generation'] = bin2hex(random_bytes(16));
            $store->write(self::RECORD, $epochs);
            return $epochs;
        });
    }

    public static function transition(string $mode): array
    {
        $mode = self::validate($mode);
        self::prepareTransition($mode);
        $repository = new CmsRepository();
        self::assertTransactional($repository);
        return (new \app\cms\service\PublicStaticPublisher())->configurationChange(fn() => $repository->connection()->transaction(function () use ($repository, $mode): array {
            $languages = $repository->table('language')->order('id asc')->lock(true)->select()->toArray();
            if (!$languages) throw new \RuntimeException('请先完成网站安装');
            $rows = $repository->table('config')->where(['name'=>'web_cmsmode','is_del'=>0])->whereIn('lang',array_column($languages,'mark'))->lock(true)->select()->toArray();
            $records = [];
            foreach ($rows as $row) { if (isset($records[$row['lang']])) throw new \RuntimeException('网站模式配置重复'); $records[$row['lang']]=$row; }
            $old = self::validate($records[$languages[0]['mark']]['value'] ?? '1');
            foreach ($languages as $language) {
                $payload = ['value'=>$mode,'inc_type'=>'web','is_del'=>0,'update_time'=>time()];
                if (isset($records[$language['mark']])) $repository->table('config')->where('id',$records[$language['mark']]['id'])->update($payload);
                else $repository->table('config')->insert($payload + ['name'=>'web_cmsmode','lang'=>$language['mark'],'desc'=>'网站模式']);
            }
            $reset = $old !== $mode && ($old === '3' || $mode === '3');
            if ($old !== $mode) self::rotate($reset, $repository);
            return ['mode'=>$mode,'session_reset_required'=>$reset];
        }));
    }

    public static function health(): array
    {
        $result = ['mode'=>self::snapshot()['mode'], 'redis_extension'=>extension_loaded('redis'), 'redis_available'=>false];
        try { RedisConnection::probe(); $result['redis_available']=true; }
        catch (\Throwable $error) { $result['message']='Redis 检查失败，请核对扩展、私有配置及服务权限'; }
        return $result;
    }
}
