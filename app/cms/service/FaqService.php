<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Original FAQ group/question IDs and HTML, with atomic and language-scoped writes. */
final class FaqService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    private function language(mixed $lang, bool $lock = false): string
    {
        if (!is_string($lang)) throw new InvalidArgumentException('语言格式错误');
        $query = $this->repository->table('language')->where('mark', $lang);
        if (!($lock ? $query->lock(true) : $query)->find()) throw new InvalidArgumentException('语言不存在');
        return $lang;
    }

    private function engines(): void
    {
        foreach (['language','faq_group','faq_asklist'] as $table) {
            $rows = $this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$this->repository->fullName($table)]);
            if (strtolower((string)($rows[0]['ENGINE'] ?? '')) !== 'innodb') throw new InvalidArgumentException('FAQ与语言表必须使用InnoDB事务引擎');
        }
    }

    private function text(mixed $value, string $name, int $max, bool $bytes = false): string
    {
        if (!is_string($value) || ($bytes ? strlen($value) : mb_strlen($value)) > $max) throw new InvalidArgumentException($name . '格式错误或长度超过限制');
        return trim($value);
    }

    private function id(mixed $id, bool $zero = false): int
    {
        if ((!is_int($id) && !is_string($id)) || !preg_match('/^\d{1,10}$/D', (string)$id) || (int)$id > 2147483647 || (int)$id < ($zero ? 0 : 1)) throw new InvalidArgumentException('FAQ ID格式错误');
        return (int)$id;
    }

    private function arrayInput(mixed $value, string $name): array
    {
        if (is_string($value)) {
            try { $value = json_decode($value, true, 32, JSON_THROW_ON_ERROR); }
            catch (\JsonException $error) { throw new InvalidArgumentException($name . 'JSON格式错误'); }
        }
        if (!is_array($value) || !array_is_list($value) || count($value) > 500) throw new InvalidArgumentException($name . '需为最多500条的数组');
        return $value;
    }

    public function list(array $filters = []): array
    {
        $lang = $this->language($filters['lang'] ?? 'cn');
        $query = $this->repository->table('faq_group')->where('lang', $lang);
        $search = $this->text($filters['q'] ?? '', '搜索词', 100);
        if ($search !== '') $query->whereLike('group_title', '%' . addcslashes($search, '%_') . '%');
        $count = (clone $query)->count();
        $rows = $query->order('group_id desc')->page(max(1,(int)($filters['page'] ?? 1)),min(100,max(1,(int)($filters['limit'] ?? 20))))->select()->toArray();
        foreach ($rows as &$row) $row['question_count'] = $this->repository->table('faq_asklist')->where(['group_id'=>$row['group_id'],'lang'=>$lang])->count();
        unset($row);
        return ['code'=>0,'count'=>$count,'data'=>$rows,'languages'=>$this->repository->table('language')->field('mark,title')->order('id')->select()->toArray()];
    }

    public function detail(int $groupId, string $lang = 'cn'): array
    {
        $lang = $this->language($lang); $this->id($groupId);
        $row = $this->repository->table('faq_group')->where(['group_id'=>$groupId,'lang'=>$lang])->find();
        if (!$row) throw new InvalidArgumentException('FAQ分组不存在或不属于当前语言');
        $row['items'] = $this->repository->table('faq_asklist')->where(['group_id'=>$groupId,'lang'=>$lang])->order('sort_order asc,asklist_id asc')->select()->toArray();
        return $row;
    }

    public function save(array $input, ?int $groupId = null): int
    {
        if ($groupId !== null) $this->id($groupId);
        $title = $this->text($input['group_title'] ?? '', '分组名称', 60);
        if ($title === '') throw new InvalidArgumentException('请填写分组名称');
        $status = $input['status'] ?? null;
        if ($status !== null && !in_array($status, [0,1,'0','1'], true)) throw new InvalidArgumentException('启用状态必须为0或1');
        $items = $this->arrayInput($input['items'] ?? [], '问答列表');
        $deletes = array_values(array_unique(array_map(fn($id) => $this->id($id), $this->arrayInput($input['delete_ids'] ?? [], '删除ID列表'))));
        $normalized = []; $seen = []; $position = 1;
        foreach ($items as $item) {
            if (!is_array($item)) throw new InvalidArgumentException('问答条目格式错误');
            $id = $this->id($item['asklist_id'] ?? 0, true);
            if ($id && (isset($seen[$id]) || in_array($id,$deletes,true))) throw new InvalidArgumentException('问答ID重复或同时更新和删除');
            if ($id) $seen[$id] = true;
            $question = $this->text($item['asklist_title'] ?? '', '问题', 255);
            $answer = $this->text($item['asklist_content'] ?? '', '回答', 65535, true);
            if (!$id && $question === '' && $answer === '') continue;
            $order = array_key_exists('sort_order',$item) ? $this->id($item['sort_order'],true) : $position;
            $normalized[] = ['asklist_id'=>$id,'asklist_title'=>$question,'asklist_content'=>$answer,'sort_order'=>$order];
            $position++;
        }
        if (!$groupId && (!$normalized || $seen || $deletes)) throw new InvalidArgumentException('新分组至少需要一条问题或回答，且不能引用已有问答ID');
        $this->engines();
        return $this->repository->transaction(function (CmsRepository $r) use ($input,$groupId,$title,$status,$normalized,$seen,$deletes): int {
            // Lock the language row to serialize same-language group-name uniqueness.
            $lang = $this->language($input['lang'] ?? 'cn', true);
            $old = $groupId ? $r->table('faq_group')->where(['group_id'=>$groupId,'lang'=>$lang])->lock(true)->find() : null;
            if ($groupId && !$old) throw new InvalidArgumentException('FAQ分组不存在或不属于当前语言');
            $duplicate = $r->table('faq_group')->where(['group_title'=>$title,'lang'=>$lang]);
            if ($groupId) $duplicate->where('group_id','<>',$groupId);
            if ($duplicate->lock(true)->find()) throw new InvalidArgumentException('分组名称已存在，请检查');
            $existing = $groupId ? $r->table('faq_asklist')->where('group_id',$groupId)->lock(true)->select()->toArray() : [];
            $byId = array_column($existing,null,'asklist_id');
            foreach ($existing as $item) if ($item['lang'] !== $lang) throw new InvalidArgumentException('分组存在语言归属异常的问答，请先检查');
            foreach (array_merge(array_keys($seen),$deletes) as $id) if (!isset($byId[$id])) throw new InvalidArgumentException('问答不存在或不属于当前分组及语言');
            $now = time(); $data = ['group_title'=>$title,'status'=>(int)($status ?? $old['status'] ?? 1),'update_time'=>$now];
            if ($groupId) $r->table('faq_group')->where(['group_id'=>$groupId,'lang'=>$lang])->update($data);
            else $groupId = (int)$r->table('faq_group')->insertGetId($data + ['lang'=>$lang,'add_time'=>$now]);
            if ($deletes) $r->table('faq_asklist')->where(['group_id'=>$groupId,'lang'=>$lang])->whereIn('asklist_id',$deletes)->delete();
            foreach ($normalized as $item) {
                $id = $item['asklist_id']; unset($item['asklist_id']); $item['update_time']=$now;
                if ($id) $r->table('faq_asklist')->where(['asklist_id'=>$id,'group_id'=>$groupId,'lang'=>$lang])->update($item);
                else $r->table('faq_asklist')->insert($item + ['group_id'=>$groupId,'lang'=>$lang,'add_time'=>$now]);
            }
            return $groupId;
        });
    }

    public function deleteGroups(array $ids, string $lang = 'cn'): void
    {
        $ids = array_values(array_unique(array_map(fn($id) => $this->id($id), $ids)));
        if (!$ids || count($ids)>500) throw new InvalidArgumentException('请选择最多500个分组');
        $this->engines();
        $this->repository->transaction(function (CmsRepository $r) use ($ids,$lang): void {
            $lang = $this->language($lang,true);
            $groups = $r->table('faq_group')->whereIn('group_id',$ids)->where('lang',$lang)->lock(true)->select()->toArray();
            if (count($groups)!==count($ids)) throw new InvalidArgumentException('分组不存在或不属于当前语言');
            $items = $r->table('faq_asklist')->whereIn('group_id',$ids)->lock(true)->select()->toArray();
            foreach ($items as $item) if ($item['lang']!==$lang) throw new InvalidArgumentException('分组存在语言归属异常的问答，请先检查');
            $r->table('faq_asklist')->whereIn('group_id',$ids)->where('lang',$lang)->delete();
            $r->table('faq_group')->whereIn('group_id',$ids)->where('lang',$lang)->delete();
        });
    }
}
