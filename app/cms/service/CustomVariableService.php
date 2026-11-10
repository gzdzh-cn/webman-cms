<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Original custom variables recycle by name across their language records. */
final class CustomVariableService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    private function editingLanguage(): string
    {
        $policy = new LanguagePolicy($this->repository);
        $context = \app\cms\CmsContext::current();
        return $policy->enabled() && $context->request && $context->lang !== '' ? $context->lang : $policy->main();
    }

    public function list(array $input, bool $recycled = false): array
    {
        $r = $this->repository;
        $input['lang'] = $input['lang'] ?? '';
        if (!is_string($input['lang'])) throw new InvalidArgumentException('语言格式错误');
        $input['lang'] = $input['lang'] ?: $this->editingLanguage();
        (new LanguageAccessService($r))->assertLanguages([$input['lang']]);
        $query = $r->table('config_attribute')->alias('a')->join($r->fullName('config') . ' c', 'c.name=a.attr_var_name AND c.lang=a.lang')->where('c.is_del', $recycled ? 1 : 0);
        foreach (['lang', 'inc_type', 'type_id'] as $key) if (isset($input[$key]) && $input[$key] !== '') $query->where('a.' . $key, $input[$key]);
        $search = trim((string)($input['q'] ?? ''));
        if ($search !== '') $query->whereLike('a.attr_name|a.attr_var_name', '%' . addcslashes($search, '%_') . '%');
        $count = (clone $query)->count();
        $rows = $query->field($recycled ? 'a.*,c.id,c.value' : 'a.*,c.id config_id,c.value')->order($recycled ? 'a.update_time desc,a.attr_id desc' : 'a.sort_order asc,a.attr_id desc')
            ->page(max(1, (int)($input['page'] ?? 1)), min(100, max(1, (int)($input['limit'] ?? 20))))->select()->toArray();
        foreach ($rows as &$row) { $row['attr_value'] = (string)($row['value'] ?? ''); $row['tag'] = \app\cms\template\TemplateMode::tag('global', ['name' => $row['attr_var_name']]); }
        unset($row);
        return ['code' => 0, 'msg' => 'ok', 'count' => $count, 'data' => $rows];
    }

    /** Validate metadata and the real config value before the shared writer mutates either. */
    public function prepare(array $data, ?array $old, array $input): array
    {
        $lang=$data['lang']??$old['lang']??$this->editingLanguage();
        if(!is_string($lang)||($old&&$lang!==$old['lang']))throw new InvalidArgumentException('不能修改自定义变量所属语言');
        (new LanguageAccessService($this->repository))->assertLanguages([$lang]);
        if(!$this->repository->table('language')->where('mark',$lang)->lock(true)->find())throw new InvalidArgumentException('变量语言不存在');
        $data['lang']=$lang;$type=$data['attr_input_type']??$old['attr_input_type']??0;
        if(!is_scalar($type)||!in_array((string)$type,['0','1','2','3','4','5'],true))throw new InvalidArgumentException('自定义变量类型无效');$data['attr_input_type']=(int)$type;
        $group=$data['type_id']??$old['type_id']??0;if(!is_scalar($group)||!ctype_digit((string)$group))throw new InvalidArgumentException('变量分组无效');$data['type_id']=(int)$group;
        if($data['type_id']&&!$this->repository->table('config_type')->where('id',$data['type_id'])->lock(true)->find())throw new InvalidArgumentException('变量分组不存在');
        if(isset($data['sort_order'])&&(!is_scalar($data['sort_order'])||!preg_match('/^\d{1,8}$/D',(string)$data['sort_order'])))throw new InvalidArgumentException('变量排序格式错误');
        $data['sort_order']=(int)($data['sort_order']??$old['sort_order']??100);$data['inc_type']=$data['inc_type']??$old['inc_type']??'web';
        if(!is_string($data['inc_type'])||!preg_match('/^[a-z][a-z0-9_]{0,29}$/D',$data['inc_type']))throw new InvalidArgumentException('变量配置组格式错误');
        if(array_key_exists('attr_value',$input)||array_key_exists('value',$input))$this->value($data['attr_input_type'],array_key_exists('attr_value',$input)?$input['attr_value']:$input['value']);
        return $data;
    }

    public function value(int $type,mixed $value):string
    {
        if(!is_scalar($value)&&$value!==null)throw new InvalidArgumentException('变量值必须是文本');$value=(string)$value;
        if(strlen($value)>1000000||str_contains($value,"\0"))throw new InvalidArgumentException('变量值过长或格式错误');
        if($type===4&&!in_array($value,['0','1'],true))throw new InvalidArgumentException('开关变量只接受0或1');
        if(in_array($type,[3,5],true)&&$value!==''&&!preg_match('#^(?:/(?!/)|https?://)[^\x00-\x20]*$#iD',$value))throw new InvalidArgumentException('图片或媒体变量地址无效');
        return $value;
    }

    public function assertEditable(int $id): void
    {
        $meta = $this->repository->table('config_attribute')->where('attr_id', $id)->find();
        if (!$meta) throw new InvalidArgumentException('自定义变量不存在');
        (new LanguageAccessService($this->repository))->assertLanguages([(string)$meta['lang']]);
        $config = $this->repository->table('config')->where(['name' => $meta['attr_var_name'], 'lang' => $meta['lang']])->find();
        if (!$config || !empty($config['is_del'])) throw new InvalidArgumentException('请先从回收站恢复自定义变量');
    }

    /** The ordinary variable editor uses metadata attr_id; recycle uses config id. */
    public function recycle(array $attributeIds): void
    {
        $this->repository->transaction(function (CmsRepository $r) use ($attributeIds) {
            $ids = $this->ids($attributeIds);
            $meta = $r->table('config_attribute')->whereIn('attr_id', $ids)->lock(true)->select()->toArray();
            if (count($meta) !== count($ids)) throw new InvalidArgumentException('自定义变量不存在');
            foreach ($meta as $row) $this->assertEditable((int)$row['attr_id']);
            $names = array_values(array_unique(array_column($meta, 'attr_var_name')));
            $this->group($names);
            $r->table('config')->whereIn('name', $names)->update(['is_del' => 1, 'update_time' => time()]);
            $r->table('config_attribute')->whereIn('attr_var_name', $names)->update(['update_time' => time()]);
        });
    }

    public function restore(array $configIds): void { $this->change($configIds, false); }
    public function purge(array $configIds): void { $this->change($configIds, true); }

    private function change(array $configIds, bool $purge): void
    {
        $this->repository->transaction(function (CmsRepository $r) use ($configIds, $purge) {
            $ids = $this->ids($configIds);
            $selected = $r->table('config')->whereIn('id', $ids)->lock(true)->select()->toArray();
            if (count($selected) !== count($ids)) throw new InvalidArgumentException('回收站变量不存在');
            foreach ($selected as $row) if (empty($row['is_del'])) throw new InvalidArgumentException('只能操作回收站内的自定义变量');
            $names = array_values(array_unique(array_column($selected, 'name')));
            $configs = $this->group($names);
            if ($purge) {
                foreach ($configs as $row) if (empty($row['is_del'])) throw new InvalidArgumentException('同名变量仍有启用语言，请先回收整个变量组');
                $r->table('config')->whereIn('name', $names)->delete();
                $r->table('config_attribute')->whereIn('attr_var_name', $names)->delete();
            } else {
                $r->table('config')->whereIn('name', $names)->update(['is_del' => 0, 'update_time' => time()]);
            }
        });
    }

    /** Refuse collisions and ordinary settings before mutating any group row. */
    private function group(array $names): array
    {
        $r = $this->repository;
        $configs = $r->table('config')->whereIn('name', $names)->lock(true)->select()->toArray();
        $metadata = $r->table('config_attribute')->whereIn('attr_var_name', $names)->lock(true)->select()->toArray();
        $definitions = []; $seen = [];
        foreach ($metadata as $row) {
            $key = $row['attr_var_name'] . "\0" . $row['lang'];
            if (isset($definitions[$key])) throw new InvalidArgumentException('变量字段定义重复，请先处理名称冲突');
            $definitions[$key] = $row;
        }
        foreach ($configs as $row) {
            $key = $row['name'] . "\0" . $row['lang'];
            if (!isset($definitions[$key])) throw new InvalidArgumentException('此记录不是自定义变量，不能操作普通网站设置');
            if (isset($seen[$key])) throw new InvalidArgumentException('变量名称与现有配置冲突，请先处理重复名称');
            if ($definitions[$key]['inc_type'] !== $row['inc_type']) throw new InvalidArgumentException('变量分组与网站设置冲突');
            $seen[$key] = true;
        }
        if (count($seen) !== count($definitions) || !$configs) throw new InvalidArgumentException('变量值或字段定义缺失，请先修复数据');
        // Recycle, restore and purge mutate the complete same-name family.
        (new LanguageAccessService($r))->assertLanguages(array_values(array_unique(array_merge(array_column($configs, 'lang'), array_column($metadata, 'lang')))));
        return $configs;
    }

    private function ids(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            if ((!is_int($value) && (!is_string($value) || !ctype_digit($value))) || (int)$value < 1) throw new InvalidArgumentException('变量ID无效');
            $ids[(int)$value] = (int)$value;
        }
        if (!$ids) throw new InvalidArgumentException('请选择自定义变量');
        return array_values($ids);
    }
}
