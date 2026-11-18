<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Both module switches use this database lock, including across HTTP workers. */
final class SiteModulePolicy
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function locked(callable $operation): mixed
    {
        return (new LanguageStateStore('site-module-policy', $this->repository))->locked(fn() => $operation());
    }

    public function status(): array
    {
        $language = new LanguagePolicy($this->repository);
        $main = $language->main();
        $rows = $this->repository->table('config')->where(['lang'=>$main,'name'=>'web_citysite_open','is_del'=>0])->order('id')->select()->toArray();
        if (count($rows) > 1) throw new InvalidArgumentException('城市分站开关配置重复，请先核对');
        $city = (string)($rows[0]['value'] ?? '0') !== '0' && !empty($rows[0]['value']);
        $multilingual = $language->enabled();
        return ['main_language'=>$main,'city_enabled'=>$city,'multilingual_enabled'=>$multilingual,'conflict'=>$city && $multilingual];
    }

    /** Historic conflicts remain visible and can always be resolved by disabling. */
    public function assertEnable(string $module, bool $enabled): void
    {
        if (!$enabled) return;
        $status = $this->status();
        if ($module === 'city' && $status['multilingual_enabled']) throw new InvalidArgumentException('多语言已开启，请先在功能地图关闭多语言，再开启城市分站');
        if ($module === 'multilingual' && $status['city_enabled']) throw new InvalidArgumentException('城市分站已开启，请先在城市分站设置中关闭，再开启多语言');
    }
}
