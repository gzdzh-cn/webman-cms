<?php

namespace app\cms\admin\controller;

use plugin\admin\app\common\Auth;
use plugin\admin\app\common\Tree;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 角色管理
 */
class RoleController extends \plugin\admin\app\controller\Crud
{
    /**
     * 不需要鉴权的方法
     * @var array
     */
    protected $noNeedAuth = ['select'];

    public function languageScope(Request $request): Response
    {
        $id=(int)$request->input('id',0);
        if (!Auth::isSuperAdmin() && !in_array($id,Auth::getScopeRoleIds(),true)) return $this->json(2,'无权管理此角色');
        $role=Role::find($id);if(!$role)return $this->json(1,'角色不存在');
        $kind=$request->input('scope_kind','language');
        if($kind==='content')return $this->contentScope($request,$role);
        if($kind!=='language')return $this->json(1,'权限范围类型无效');
        $service=new \app\cms\service\LanguageAccessService();
        if($request->method()==='GET') return $this->json(0,'ok',['languages'=>(new \app\cms\service\LanguagePolicy())->all(),'selected'=>$service->roleLanguages($id),'allowed'=>array_values(array_intersect($service->roleLanguages((int)$role->pid),$service->allowedLanguages())),'super'=>$role->rules==='*']);
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        try{\app\cms\admin\support\LanguageAdminSecurity::assertWrite($request);$marks=$request->post('languages',[]);if(!is_array($marks))throw new \InvalidArgumentException('语言范围格式无效');(new \app\cms\service\LanguageStateStore('language-role-scopes'))->locked(function() use ($service,$id,$marks) { $current=Role::find($id);if(!$current)throw new \InvalidArgumentException('角色不存在');$service->saveRole($id,(int)$current->pid,$marks); });return $this->json(0,'语言范围已保存');}catch(\InvalidArgumentException $error){return $this->json(1,$error->getMessage());}
    }

    /** Reuses the original role-update identity; no new permission node is introduced. */
    private function contentScope(Request $request, Role $role): Response
    {
        $repository=new \app\cms\repository\CmsRepository();$service=new \app\cms\service\ContentAccessService($repository);
        if($request->method()==='GET')return $this->json(0,'ok',$service->metadata((int)$role->id,(int)$role->pid)+['super'=>$role->rules==='*']);
        if($request->method()!=='POST')return response('Method Not Allowed',405);
        try {
            \app\cms\admin\support\LanguageAdminSecurity::assertWrite($request);
            $raw=$request->post('grants');
            if(!is_string($raw)||strlen($raw)>262144)throw new \InvalidArgumentException('内容范围格式无效');
            $grants=json_decode($raw,true,64,JSON_THROW_ON_ERROR);
            if(!is_array($grants))throw new \InvalidArgumentException('内容范围格式无效');
            $connection=$this->model->getConnection();$this->assertScopeStorage($repository,$connection);
            return (new \app\cms\service\LanguageStateStore(\app\cms\service\ContentAccessService::SCOPE,$repository))->locked(function()use($role,$service,$grants,$connection,$repository){
                return $connection->transaction(function()use($role,$service,$grants,$connection,$repository){
                    $roles=Role::query()->orderBy('id')->lockForUpdate()->get();$current=$roles->firstWhere('id',$role->id);
                    if(!$current||(int)$current->pid!==(int)$role->pid||(string)$current->rules!==(string)$role->rules)throw new \InvalidArgumentException('角色关系已改变，请刷新后重试');
                    $selected=$service->validatedRoleScope((int)$role->id,(int)$current->pid,$grants);
                    if($selected!==null)$this->writeScope($connection,$repository,\app\cms\service\ContentAccessService::SCOPE,(int)$role->id,['version'=>1,'grants'=>$selected,'updated_at'=>time()]);
                    return $this->json(0,'内容范围已保存');
                });
            });
        }catch(\InvalidArgumentException|\JsonException $error){return $this->json(1,$error->getMessage());}
    }

    private function assertScopeStorage(\app\cms\repository\CmsRepository $repository,$connection): void
    {
        $cmsDatabase=$repository->connection()->query('SELECT DATABASE() AS name')[0]['name']??null;
        $adminDatabase=$connection->selectOne('SELECT DATABASE() AS name')->name??null;
        if(!$cmsDatabase||$cmsDatabase!==$adminDatabase)throw new BusinessException('角色与数据范围必须保存于同一数据库');
        foreach([$connection->getTablePrefix().$this->model->getTable(),$repository->fullName('cms_state_records')]as$table){
            $row=$connection->selectOne('SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$table]);
            if(strtolower((string)($row->engine??''))!=='innodb')throw new BusinessException('角色与数据范围需要 InnoDB 事务支持');
        }
    }
    private function writeScope($connection,\app\cms\repository\CmsRepository $repository,string $scope,int $id,array $value): void
    {
        $payload=json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        $connection->statement('INSERT INTO `'.$repository->fullName('cms_state_records').'` (`scope`,`name`,`payload`,`sha256`,`updated_at`) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE `payload`=VALUES(`payload`),`sha256`=VALUES(`sha256`),`updated_at`=VALUES(`updated_at`)',[$scope,'role.'.$id,$payload,hash('sha256',$payload),time()]);
    }

    /**
     * @var Role
     */
    protected $model = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Role;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return \app\cms\admin\support\AdminView::raw('role/index');
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        $id = $request->get('id');
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $limit = 100000;
        $role_ids = Auth::getScopeRoleIds(true);
        if (!$id) {
            $where['id'] = ['in', $role_ids];
        } elseif (!in_array($id, $role_ids)) {
            throw new BusinessException('无权限');
        }
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     * @throws Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $data = $this->insertInput($request);
            $pid = $data['pid'] ?? null;
            // 允许顶级角色 pid = 0，只有在 pid 为空时才提示未选择
            if ($pid === null || $pid === '') {
                return $this->json(1, '请选择父级角色组');
            }
            // pid=0 为顶级角色，不做范围校验，其余须在可见范围内
            if ($pid && !Auth::isSuperAdmin() && !in_array($pid, Auth::getScopeRoleIds(true))) {
                return $this->json(1, '父级角色组超出权限范围');
            }
            $this->checkRules($pid, $data['rules'] ?? '');

            $repository=new \app\cms\repository\CmsRepository();$connection=$this->model->getConnection();
            $this->assertScopeStorage($repository,$connection);
            return $connection->transaction(function()use($data,$pid,$repository,$connection){
                Role::query()->orderBy('id')->lockForUpdate()->get();
                $language=new \app\cms\service\LanguageAccessService($repository);
                $languages=array_values(array_intersect($language->roleLanguages((int)$pid),$language->allowedLanguages()));
                $content=(new \app\cms\service\ContentAccessService($repository))->limitForRole((int)$pid);
                $id=$this->doInsert($data);
                $this->writeScope($connection,$repository,'language-role-scopes',(int)$id,['languages'=>$languages,'updated_at'=>time()]);
                $this->writeScope($connection,$repository,\app\cms\service\ContentAccessService::SCOPE,(int)$id,['version'=>1,'grants'=>$content,'updated_at'=>time()]);
                return $this->json(0,'ok',['id'=>$id]);
            });
        }
        return \app\cms\admin\support\AdminView::raw('role/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return \app\cms\admin\support\AdminView::raw('role/update');
        }
        [$id, $data] = $this->updateInput($request);
        $is_supper_admin = Auth::isSuperAdmin();
        $descendant_role_ids = Auth::getScopeRoleIds();
        if (!$is_supper_admin && !in_array($id, $descendant_role_ids)) {
            return $this->json(1, '无数据权限');
        }

        $role = Role::find($id);
        if (!$role) {
            return $this->json(1, '数据不存在');
        }
        $is_supper_role = $role->rules === '*';

        // 超级角色组不允许更改rules pid 字段
        if ($is_supper_role) {
            unset($data['rules'], $data['pid']);
        }

        if (key_exists('pid', $data)) {
            $pid = $data['pid'];
            // 允许 pid = 0 作为顶级角色
            if ($pid === null || $pid === '') {
                return $this->json(1, '请选择父级角色组');
            }
            if ($pid == $id) {
                return $this->json(1, '父级不能是自己');
            }
            // pid=0 不做范围校验，其余须在可见范围内
            if ($pid && !$is_supper_admin && !in_array($pid, Auth::getScopeRoleIds(true))) {
                return $this->json(1, '父级超出权限范围');
            }
        } else {
            $pid = $role->pid;
        }

        if (!$is_supper_role) {
            $this->checkRules($pid, $data['rules'] ?? '');
        }

        $repository = new \app\cms\repository\CmsRepository();
        $state = new \app\cms\service\LanguageStateStore('language-role-scopes', $repository);
        return $state->locked(function () use ($id, $data, $pid, $role, $is_supper_role, $repository) {
            $connection = $this->model->getConnection();
            $stateTable = $repository->fullName('cms_state_records');
            $roleTable = $connection->getTablePrefix().$this->model->getTable();
            // Both ORM facades must use the same schema; only one PDO transaction writes both rows.
            $cmsDatabase = $repository->connection()->query('SELECT DATABASE() AS name')[0]['name'] ?? null;
            $adminDatabase = $connection->selectOne('SELECT DATABASE() AS name')->name ?? null;
            if (!$cmsDatabase || $cmsDatabase !== $adminDatabase) {
                throw new BusinessException('角色与语言范围必须保存于同一数据库');
            }
            foreach ([$roleTable, $stateTable] as $table) {
                $engine = $connection->selectOne('SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table]);
                if (strtolower((string)($engine->engine ?? '')) !== 'innodb') {
                    throw new BusinessException('角色与语言范围需要 InnoDB 事务支持');
                }
            }
            return $connection->transaction(function () use ($id, $data, $pid, $role, $is_supper_role, $repository, $connection, $stateTable) {
                // Freeze the hierarchy used by language inheritance, including descendants.
                $roles = Role::query()->orderBy('id')->lockForUpdate()->get();
                $current = $roles->firstWhere('id', $id);
                if (!$current || (int)$current->pid !== (int)$role->pid || (string)$current->rules !== (string)$role->rules) {
                    throw new BusinessException('角色关系已变化，请刷新后重试');
                }
                if (!$is_supper_role && (int)$pid !== (int)$current->pid) {
                    $tree = new Tree($roles);
                    $descendantIds = array_map('intval', array_column($tree->getDescendant([$id]), 'id'));
                    if (in_array((int)$pid, $descendantIds, true)) throw new BusinessException('父级不能是下级角色');
                    $access = new \app\cms\service\LanguageAccessService($repository);
                    // Moving to a broader parent (including root) never grants additional languages.
                    $languages = array_values(array_intersect(
                        $access->roleLanguages((int)$id),
                        $access->roleLanguages((int)$pid),
                        $access->allowedLanguages()
                    ));
                    $payload = json_encode(['languages'=>$languages, 'updated_at'=>time()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
                    $connection->statement('INSERT INTO `'.$stateTable.'` (`scope`,`name`,`payload`,`sha256`,`updated_at`) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE `payload`=VALUES(`payload`),`sha256`=VALUES(`sha256`),`updated_at`=VALUES(`updated_at`)', [
                        'language-role-scopes', 'role.'.$id, $payload, hash('sha256', $payload), time(),
                    ]);
                    $content=new \app\cms\service\ContentAccessService($repository);
                    $grants=\app\cms\service\ContentAccessService::intersect($content->roleScope((int)$id),$content->limitForRole((int)$pid));
                    $this->writeScope($connection,$repository,\app\cms\service\ContentAccessService::SCOPE,(int)$id,['version'=>1,'grants'=>$grants,'updated_at'=>time()]);
                }
                $this->doUpdate($id, $data);

                // Preserve existing descendant permission trimming inside the same transaction.
                if (!$is_supper_role) {
                    $tree = new Tree(Role::select(['id', 'pid'])->get());
                    $descendant_roles = $tree->getDescendant([$id]);
                    $descendant_role_ids = array_column($descendant_roles, 'id');
                    foreach ($descendant_role_ids as $role_id) {
                        $tmp_role = Role::find($role_id);
                        $tmp_rule_ids = $role->getRuleIds();
                        $tmp_role_rules = $tmp_role->rules ?? '';
                        $tmp_role_ids = $tmp_role_rules === '' ? [] : explode(',', $tmp_role_rules);
                        $tmp_role->rules = implode(',', array_intersect($tmp_role_ids, $tmp_rule_ids));
                        $tmp_role->save();
                    }
                }
                return $this->json(0);
            });
        });
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        if (in_array(1, $ids)) {
            return $this->json(1, '无法删除超级管理员角色');
        }
        if (!Auth::isSuperAdmin() && array_diff($ids, Auth::getScopeRoleIds())) {
            return $this->json(1, '无删除权限');
        }
        $tree = new Tree(Role::get());
        $descendants = $tree->getDescendant($ids);
        if ($descendants) {
            $ids = array_merge($ids, array_column($descendants, 'id'));
        }
        $this->doDelete($ids);
        return $this->json(0);
    }

    /**
     * 获取角色权限
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function rules(Request $request): Response
    {
        $role_id = $request->get('id');
        if (empty($role_id)) {
            return $this->json(0, 'ok', []);
        }
        if (!Auth::isSuperAdmin() && !in_array($role_id, Auth::getScopeRoleIds(true))) {
            return $this->json(1, '角色组超出权限范围');
        }
        $rule_id_string = Role::where('id', $role_id)->value('rules');
        if ($rule_id_string === '') {
            return $this->json(0, 'ok', []);
        }
        $rules = Rule::get();
        $include = [];
        if ($rule_id_string !== '*') {
            $include = explode(',', $rule_id_string);
        }
        $items = [];
        foreach ($rules as $item) {
            $items[] = [
                'name' => $item->title ?? $item->name ?? $item->id,
                'value' => (string)$item->id,
                'id' => $item->id,
                'pid' => $item->pid,
                // Tree adds ancestors for presentation even when the parent cannot grant them.
                'assignable' => $rule_id_string === '*' || in_array((string)$item->id, $include, true),
            ];
        }
        $tree = new Tree($items);
        return $this->json(0, 'ok', $tree->getTree($include));
    }

    /**
     * 检查权限字典是否合法
     * @param int $role_id
     * @param $rule_ids
     * @return void
     * @throws BusinessException
     */
    protected function checkRules(int $role_id, $rule_ids)
    {
        // 无权限数据直接通过
        if (!$rule_ids) {
            return;
        }

        // 兼容数组或字符串输入，过滤空值
        if (is_array($rule_ids)) {
            $rule_ids = array_values(array_filter($rule_ids, static fn($v) => $v !== '' && $v !== null));
        } else {
            $rule_ids = array_values(array_filter(explode(',', (string)$rule_ids), static fn($v) => $v !== '' && $v !== null));
        }

        // 过滤后无权限数据直接通过
        if (empty($rule_ids)) {
            return;
        }

        // 顶级角色（pid=0）不做范围校验
        if ($role_id === 0) {
            return;
        }

        if (in_array('*', $rule_ids)) {
            throw new BusinessException('非法数据');
        }

        $rule_exists = Rule::whereIn('id', $rule_ids)->pluck('id');
        if (count($rule_exists) != count($rule_ids)) {
            throw new BusinessException('权限不存在');
        }

        $rule_id_string = Role::where('id', $role_id)->value('rules');
        if ($rule_id_string === '' || $rule_id_string === null) {
            throw new BusinessException('数据超出权限范围');
        }
        if ($rule_id_string === '*') {
            return;
        }

        $legal_rule_ids = explode(',', $rule_id_string);
        if (array_diff($rule_ids, $legal_rule_ids)) {
            throw new BusinessException('数据超出权限范围');
        }
    }


}
