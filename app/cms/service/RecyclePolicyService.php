<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Legacy flag: 0 (including absent) enables recycling; 1 bypasses it for new deletions. */
final class RecyclePolicyService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function enabled(): bool
    {
        $main = (new LanguagePolicy($this->repository))->main();
        $rows = $this->repository->table('config')->where(['name'=>'web_recycle_switch','lang'=>$main,'is_del'=>0])->select()->toArray();
        if (count($rows) > 1) throw new InvalidArgumentException('回收站配置重复，请先处理');
        return (string)($rows[0]['value'] ?? '0') !== '1';
    }

    public static function expected(mixed $value): ?bool
    {
        if ($value === null) return null;
        if (!is_scalar($value) || !in_array((string)$value, ['0','1'], true)) throw new InvalidArgumentException('回收站确认参数无效');
        return (string)$value === '1';
    }

    /** Reuse the established cleanup and access checks atomically; never sweep existing trash. */
    public function delete(string $module, array $ids, ?bool $expected = null): bool
    {
        return $this->repository->transaction(function (CmsRepository $r) use ($module, $ids, $expected): bool {
            $enabled = $this->enabled();
            if ($expected !== null && $expected !== $enabled) throw new InvalidArgumentException('回收站设置已改变，请刷新列表后重新确认删除');
            $service = match ($module) {
                'archives' => new ContentService($r),
                'arctype' => new CategoryService($r),
                'config_attribute' => new CustomVariableService($r),
                'guestbook_attribute' => new FormFieldService($r),
                default => throw new InvalidArgumentException('此模块不支持回收站开关'),
            };
            // Variable metadata and recycle-bin entries use different primary keys.
            $purgeIds = $ids;
            if (!$enabled && $module === 'config_attribute') {
                $metadata = $r->table('config_attribute')->whereIn('attr_id', $ids)->lock(true)->select()->toArray();
                $names = array_unique(array_column($metadata, 'attr_var_name'));
                $purgeIds = $names ? $r->table('config')->whereIn('name', $names)->column('id') : [];
            }
            // Ordinary deletion accepts only live records. Existing recycled selections
            // continue to require the explicit recycle-bin purge action/permission.
            if (in_array($module, ['archives', 'arctype'], true)) {
                $primary = $module === 'archives' ? 'aid' : 'id';
                $rows = $r->table($module)->whereIn($primary, $ids)->lock(true)->select()->toArray();
                if (!$ids || count($rows) !== count(array_unique($ids))) throw new InvalidArgumentException('请选择有效记录');
                foreach ($rows as $row) if (!empty($row['is_del'])) throw new InvalidArgumentException('记录已在回收站，请从回收站操作');
            }
            $service->recycle($ids);
            if (!$enabled) $service->purge($purgeIds);
            return $enabled;
        });
    }
}
