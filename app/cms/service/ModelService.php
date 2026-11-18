<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

class ModelService
{
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    public function save(array $input, ?int $id = null): int
    {
        $repository = $this->repository;
        $old = $id ? $repository->table('channeltype')->where('id', $id)->find() : null;
        if ($id && !$old) { throw new InvalidArgumentException('模型不存在'); }
        $title = trim((string)($input['title'] ?? $old['title'] ?? ''));
        if ($title === '') { throw new InvalidArgumentException('模型名称不能为空'); }
        $ntitle = trim((string)($input['ntitle'] ?? $old['ntitle'] ?? $title));
        if (mb_strlen($title) > 30 || mb_strlen($ntitle) > 30) { throw new InvalidArgumentException('模型名称和简称不能超过30个字符'); }
        if ($old) {
            if (isset($input['nid']) && $input['nid'] !== $old['nid']) { throw new InvalidArgumentException('模型创建后不能修改标识'); }
            $data = array_intersect_key($input, array_flip(['title', 'ntitle', 'is_repeat_title', 'sort_order', 'status']));
            $data['title'] = $title; $data['ntitle'] = $ntitle; $data['update_time'] = time();
            $repository->table('channeltype')->where('id', $id)->update($data);
            return $id;
        }
        $nid = (string)($input['nid'] ?? '');
        if (!preg_match('/^[a-z][a-z0-9_]{1,31}$/', $nid)) { throw new InvalidArgumentException('模型标识需为2至32位小写字母、数字或下划线，并以字母开头'); }
        if ($repository->table('channeltype')->where('nid', $nid)->find()) { throw new InvalidArgumentException('模型标识已存在'); }
        $table = $repository->fullName($nid . '_content');
        $exists = $repository->connection()->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table]);
        if ($exists) { throw new InvalidArgumentException('模型附表已经存在，不能覆盖已有数据'); }
        $repository->connection()->execute('CREATE TABLE `' . $table . '` (`id` int unsigned NOT NULL AUTO_INCREMENT, `aid` int unsigned NOT NULL DEFAULT 0, `content` longtext, `content_ey_m` longtext, `add_time` int NOT NULL DEFAULT 0, `update_time` int NOT NULL DEFAULT 0, PRIMARY KEY (`id`), UNIQUE KEY `aid` (`aid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC');
        try {
            return $repository->transaction(function (CmsRepository $repository) use ($input, $title, $ntitle, $nid) {
                $id = (int)$repository->table('channeltype')->insertGetId(['nid' => $nid, 'title' => $title, 'ntitle' => $ntitle, 'table' => $nid, 'ctl_name' => ucfirst($nid), 'status' => (int)($input['status'] ?? 1), 'ifsystem' => 0, 'is_repeat_title' => (int)($input['is_repeat_title'] ?? 1), 'sort_order' => (int)($input['sort_order'] ?? 100), 'add_time' => time(), 'update_time' => time()]);
                $fields = $repository->table('channelfield')->where('channel_id', 1)->select()->toArray();
                foreach ($fields as $field) {
                    unset($field['id']); $field['channel_id'] = $id; $field['add_time'] = time(); $field['update_time'] = time();
                    if (empty($field['ifmain']) && !in_array($field['name'], ['content', 'content_ey_m'], true)) { continue; }
                    $repository->table('channelfield')->insert($field);
                }
                return $id;
            });
        } catch (\Throwable $error) {
            // This table did not pre-exist and has no committed model or content.
            $repository->connection()->execute('DROP TABLE `' . $table . '`');
            throw $error;
        }
    }

    public function delete(array $ids): void
    {
        $this->repository->transaction(function (CmsRepository $repository) use ($ids) {
            $models = $repository->table('channeltype')->whereIn('id', $ids)->select()->toArray();
            foreach ($models as $model) {
                if (!empty($model['ifsystem'])) { throw new InvalidArgumentException('系统模型不能删除'); }
                if ($repository->table('arctype')->where('current_channel', $model['id'])->find() || $repository->table('archives')->where('channel', $model['id'])->find()) { throw new InvalidArgumentException('模型仍被栏目或文档使用，不能删除'); }
                $repository->table('channeltype')->where('id', $model['id'])->update(['is_del' => 1, 'status' => 0, 'update_time' => time()]);
            }
        });
    }
}
