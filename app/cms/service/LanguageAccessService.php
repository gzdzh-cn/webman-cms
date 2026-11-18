<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use plugin\admin\app\model\Role;

/** Optional language scopes supplement existing rule identities. */
final class LanguageAccessService
{
    private CmsRepository $repository;
    public function __construct(?CmsRepository $repository=null) { $this->repository=$repository??new CmsRepository(); }
    public function allowedLanguages(?array $admin=null): array
    {
        $all=array_column((new LanguagePolicy($this->repository))->all(),'mark');
        if ($admin === null && in_array(PHP_SAPI,['cli','micro'],true) && !\app\cms\CmsContext::current()->request) return $all;
        $admin??=function_exists('admin')?(admin()?:null):null;
        if(!$admin) return in_array(PHP_SAPI,['cli','micro'],true)&&!\app\cms\CmsContext::current()->request?$all:[];
        $roles=Role::whereIn('id',$admin['roles']??[])->get();$allowed=[];
        foreach($roles as $role){if($role->rules==='*')return $all;$allowed=array_merge($allowed,$this->roleLanguages((int)$role->id));}
        return array_values(array_intersect($all,array_unique($allowed)));
    }
    public function assertLanguages(array $marks,?array $admin=null): void
    {
        if(array_diff($marks,$this->allowedLanguages($admin)))throw new \InvalidArgumentException('无权管理所选语言');
    }
    public function roleLanguages(int $id,array $seen=[]): array
    {
        if(in_array($id,$seen,true))throw new \RuntimeException('角色层级循环');
        $all=array_column((new LanguagePolicy($this->repository))->all(),'mark');
        if(!$id)return $all;
        $role=Role::find($id);if(!$role)return [];
        if($role->rules==='*')return $all;
        $record=(new LanguageStateStore('language-role-scopes',$this->repository))->read('role.'.$id);
        $allowed=isset($record['languages'])?array_values(array_intersect($all,$record['languages'])):$all;
        return (int)$role->pid?array_values(array_intersect($allowed,$this->roleLanguages((int)$role->pid,[...$seen,$id]))):$allowed;
    }
    public function saveRole(int $id,int $parent,?array $marks): void
    {
        $role=Role::find($id);if(!$role)throw new \InvalidArgumentException('角色不存在');
        if($role->rules==='*'){if($marks!==null)throw new \InvalidArgumentException('超级角色始终拥有全部语言');return;}
        $limit=array_values(array_intersect($this->roleLanguages($parent),$this->allowedLanguages()));
        $selected=$marks===null?$limit:array_values(array_unique(array_map('strval',$marks)));
        if(array_diff($selected,$limit))throw new \InvalidArgumentException('语言授权超出父角色或当前账号范围');
        (new LanguageStateStore('language-role-scopes',$this->repository))->write('role.'.$id,['languages'=>$selected,'updated_at'=>time()]);
    }
}
