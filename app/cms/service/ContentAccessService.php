<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\repository\CmsRepository;
use InvalidArgumentException;
use plugin\admin\app\model\Role;

/** Backend content scopes supplement, and never replace, existing action/language permissions.
 * A grant is one complete (categories, ownership) pair. Multiple roles union pairs;
 * merging their category and ownership dimensions independently would grant too much.
 */
class ContentAccessService
{
    public const SCOPE = 'content-role-scopes';
    private CmsRepository $repository;
    private array $actor;
    private ?array $effective = null;

    public function __construct(?CmsRepository $repository = null, ?array $actor = null)
    {
        $this->repository = $repository ?? new CmsRepository();
        if ($actor !== null) { $this->actor = $actor; return; }
        $context = CmsContext::current();
        // Public rendering and trusted CLI/install jobs use their own access policy.
        // Tests/maintenance can explicitly pass ['trusted'=>true]; [] is deny-all.
        $backend = $context->module === 'admin' && $context->request !== null;
        $this->actor = $backend ? (function_exists('admin') ? (admin() ?: []) : []) : ['trusted' => true];
    }

    protected function role(int $id): ?array { return Role::find($id)?->toArray(); }
    protected function readRole(int $id): array { return (new LanguageStateStore(self::SCOPE, $this->repository))->read('role.'.$id); }
    protected function writeRole(int $id, array $grants): void
    {
        (new LanguageStateStore(self::SCOPE, $this->repository))->write('role.'.$id, ['version'=>1, 'grants'=>self::normalize($grants), 'updated_at'=>time()]);
        $this->effective = null;
    }

    /** null categories means all present/future categories; [] means no grant. */
    public static function normalize(array $grants): array
    {
        $sets = ['all'=>[], 'own'=>[]];
        foreach ($grants as $grant) {
            if (!is_array($grant) || !in_array($grant['ownership'] ?? null, ['all','own'], true) || !array_key_exists('categories', $grant)) throw new InvalidArgumentException('内容范围格式无效');
            $kind = $grant['ownership']; $categories = $grant['categories'];
            if ($categories === null) { $sets[$kind] = null; continue; }
            if (!is_array($categories) || count($categories)>10000) throw new InvalidArgumentException('栏目范围格式无效');
            $ids = [];
            foreach ($categories as $id) {
                if ((!is_int($id) && !is_string($id)) || !preg_match('/^[1-9][0-9]{0,9}$/D', (string)$id) || (int)$id>4294967295) throw new InvalidArgumentException('栏目编号格式无效');
                $ids[]=(int)$id;
            }
            if ($sets[$kind] !== null) $sets[$kind] = array_values(array_unique(array_merge($sets[$kind],$ids)));
        }
        if ($sets['all'] === null) return [['categories'=>null, 'ownership'=>'all']];
        sort($sets['all']);
        if ($sets['own'] !== null) { $sets['own']=array_values(array_diff($sets['own'],$sets['all'])); sort($sets['own']); }
        $result=[]; foreach ($sets as $kind=>$ids) if ($ids===null || $ids) $result[]=['categories'=>$ids, 'ownership'=>$kind];
        return $result;
    }

    public static function intersect(array $left, array $right): array
    {
        $pairs=[];
        foreach (self::normalize($left) as $a) foreach (self::normalize($right) as $b) {
            $ids=$a['categories']===null?$b['categories']:($b['categories']===null?$a['categories']:array_values(array_intersect($a['categories'],$b['categories'])));
            if ($ids===null || $ids) $pairs[]=['categories'=>$ids,'ownership'=>$a['ownership']==='own'||$b['ownership']==='own'?'own':'all'];
        }
        return self::normalize($pairs);
    }

    public static function contains(array $limit, array $selected): bool
    {
        $limit=self::normalize($limit);
        foreach (self::normalize($selected) as $grant) {
            $matching=array_filter($limit,static fn($allowed)=>$grant['ownership']==='own'||$allowed['ownership']==='all');
            if (array_filter($matching,static fn($allowed)=>$allowed['categories']===null)) continue;
            if ($grant['categories']===null) return false;
            $ids=[];foreach($matching as $allowed)$ids=array_merge($ids,$allowed['categories']);
            if(array_diff($grant['categories'],$ids))return false;
        }
        return true;
    }

    public function roleScope(int $id, array $seen = []): array
    {
        $all=[['categories'=>null,'ownership'=>'all']];
        if ($id===0) return $all;
        if (in_array($id,$seen,true)) throw new InvalidArgumentException('角色层级循环');
        $role=$this->role($id); if (!$role) return [];
        if (($role['rules']??'')==='*') return $all;
        $record=$this->readRole($id);
        if ($record && (($record['version']??null)!==1 || !is_array($record['grants']??null))) throw new InvalidArgumentException('角色内容范围记录无效');
        $grants=$record?self::normalize($record['grants']):$all;
        return (int)($role['pid']??0)>0?self::intersect($grants,$this->roleScope((int)$role['pid'],[...$seen,$id])):$grants;
    }

    public function grants(): array
    {
        if ($this->effective!==null) return $this->effective;
        if (($this->actor['trusted']??false)===true) return $this->effective=[['categories'=>null,'ownership'=>'all']];
        if ((int)($this->actor['id']??0)<=0) return $this->effective=[];
        $grants=[];
        foreach ((array)($this->actor['roles']??[]) as $id) $grants=array_merge($grants,$this->roleScope((int)$id));
        return $this->effective=self::normalize($grants);
    }

    public function unrestricted(): bool { return self::contains($this->grants(), [['categories'=>null,'ownership'=>'all']]); }
    public function canCategory(int $id): bool
    {
        foreach($this->grants() as $grant)if($grant['categories']===null||in_array($id,$grant['categories'],true))return true;
        return false;
    }
    public function canArchive(array $archive): bool
    {
        foreach($this->grants() as $grant) {
            if($grant['categories']!==null&&!in_array((int)($archive['typeid']??0),$grant['categories'],true))continue;
            if($grant['ownership']==='all'||((int)($this->actor['id']??0)>0&&(int)($archive['admin_id']??0)===(int)$this->actor['id']))return true;
        }
        return false;
    }
    public function assertCategory(int|array $category, bool $withDescendants = false): void
    {
        if (is_int($category)) $category=$this->repository->table('arctype')->where('id',$category)->find()?:[];
        if (!$category || !$this->canCategory((int)$category['id'])) throw new InvalidArgumentException('无权管理此栏目');
        if($withDescendants) foreach($this->descendants((int)$category['id']) as $row)if(!$this->canCategory((int)$row['id']))throw new InvalidArgumentException('操作涉及无权管理的下级栏目');
    }
    public function assertArchive(int|array $archive): void
    {
        if(is_int($archive))$archive=$this->repository->table('archives')->where('aid',$archive)->find()?:[];
        if(!$archive||!$this->canArchive($archive))throw new InvalidArgumentException('无权管理此文档或只能管理本人文档');
    }

    /** Query helpers preserve pagination/counts by filtering before execution. */
    public function filterArchiveQuery($query, string $alias = '')
    {
        $prefix=$this->prefix($alias);$grants=$this->grants();$actor=(int)($this->actor['id']??0);
        if($this->unrestricted())return $query;
        if(!$grants)return $query->where($prefix.'aid',0);
        return $query->where(function($union)use($grants,$prefix,$actor){
            $first=true;
            foreach($grants as $grant){
                $branch=function($part)use($grant,$prefix,$actor){
                    if($grant['categories']!==null)$part->whereIn($prefix.'typeid',$grant['categories']);
                    if($grant['ownership']==='own')$part->where($prefix.'admin_id',$actor);
                };
                if($first){$union->where($branch);$first=false;}
                elseif(method_exists($union,'orWhere'))$union->orWhere($branch);
                else $union->whereOr($branch);
            }
        });
    }
    public function filterCategoryQuery($query, string $alias = '')
    {
        $prefix=$this->prefix($alias);$ids=[];
        foreach($this->grants() as $grant){if($grant['categories']===null)return $query;$ids=array_merge($ids,$grant['categories']);}
        return $query->whereIn($prefix.'id',$ids?array_values(array_unique($ids)):[0]);
    }
    /** Tables carrying an archive owner use the same complete-pair predicate. */
    public function filterArchiveOwnerQuery($query, string $ownerField = 'aid', string $alias = '')
    {
        $this->repository->identifier($ownerField);$prefix=$this->prefix($alias);
        if($this->unrestricted())return $query;
        $sub=$this->filterArchiveQuery($this->repository->table('archives'))->field('aid')->buildSql();
        return $query->whereRaw($prefix.$ownerField.' IN '.$sub);
    }
    public function allowedArchiveIds(): array
    {
        return array_map('intval',$this->filterArchiveQuery($this->repository->table('archives'))->column('aid'));
    }
    private function prefix(string $alias): string
    {
        if($alias==='')return '';$this->repository->identifier($alias);return $alias.'.';
    }

    public function limitForRole(int $parent): array { return self::intersect($this->roleScope($parent),$this->grants()); }
    public function saveRole(int $id, int $parent, ?array $grants): void
    {
        $selected=$this->validatedRoleScope($id,$parent,$grants);
        if($selected!==null)$this->writeRole($id,$selected);
    }
    public function validatedRoleScope(int $id, int $parent, ?array $grants): ?array
    {
        $role=$this->role($id);if(!$role)throw new InvalidArgumentException('角色不存在');
        if(($role['rules']??'')==='*'){if($grants!==null)throw new InvalidArgumentException('超级角色始终拥有全部内容范围');return null;}
        $limit=$this->limitForRole($parent);$selected=$grants===null?$limit:self::normalize($grants);
        if(!self::contains($limit,$selected))throw new InvalidArgumentException('栏目或内容范围超出父角色或当前账号权限');
        $ids=[];foreach($selected as $grant)if($grant['categories']!==null)$ids=array_merge($ids,$grant['categories']);
        $ids=array_values(array_unique($ids));
        if($ids&&$this->repository->table('arctype')->whereIn('id',$ids)->count()!==count($ids))throw new InvalidArgumentException('部分授权栏目不存在，请刷新后重试');
        return $selected;
    }
    public function metadata(int $id, int $parent): array
    {
        $languages=(new LanguageAccessService($this->repository))->allowedLanguages();
        $query=$this->filterCategoryQuery($this->repository->table('arctype'))->whereIn('lang',$languages?:['']);
        $categories=$query->field('id,parent_id,typename,lang,is_del')->order('lang,sort_order,id')->select()->toArray();
        return ['version'=>1,'grants'=>$this->roleScope($id),'allowed'=>$this->limitForRole($parent),'categories'=>$categories];
    }
    public function descendants(int $id): array
    {
        $rows=$this->repository->table('arctype')->field('id,parent_id,lang')->select()->toArray();$result=[];$pending=[$id];$seen=[$id=>true];
        while($pending){$parent=array_shift($pending);foreach($rows as$row)if((int)$row['parent_id']===$parent&&!isset($seen[(int)$row['id']])){$seen[(int)$row['id']]=true;$pending[]=(int)$row['id'];$result[]=$row;}}
        return $result;
    }
    /** Existing synchronization works on a whole language, not a selected category subset. */
    public function assertLanguageContents(array $marks): void
    {
        if($this->unrestricted())return;
        foreach($this->repository->table('arctype')->whereIn('lang',$marks)->select()->toArray()as$row)$this->assertCategory($row);
        foreach($this->repository->table('archives')->whereIn('lang',$marks)->select()->toArray()as$row)$this->assertArchive($row);
    }
    /** Category edits can update descendants, translated peers, and passive document deletion.
     * Invoke again inside the business transaction after locking the source hierarchy.
     */
    public function assertCategoryMutation(array $ids, bool $restore = false): void
    {
        if($this->unrestricted())return;
        $rows=$this->repository->table('arctype')->select()->toArray();$byId=array_column($rows,null,'id');
        $selected=[];foreach($ids as$id){$id=(int)$id;if(!$id||!isset($byId[$id]))throw new InvalidArgumentException('操作栏目不存在');$selected[$id]=true;}
        $policy=new LanguagePolicy($this->repository);
        $shared=$policy->enabled()&&empty($policy->settings()['language_split']);
        $links=$shared?$this->repository->table('language_attr')->where('attr_group','arctype')->select()->toArray():[];
        do {
            $before=count($selected);
            foreach($rows as$row){
                $id=(int)$row['id'];$parent=(int)$row['parent_id'];
                if(isset($selected[$parent]))$selected[$id]=true;
                if($restore&&isset($selected[$id])&&$parent){if(!isset($byId[$parent]))throw new InvalidArgumentException('上级栏目缺失，不能恢复');$selected[$parent]=true;}
            }
            $names=[];foreach($links as$link){$id=(int)$link['attr_value'];if(isset($selected[$id])&&isset($byId[$id])&&$byId[$id]['lang']===$link['lang'])$names[$link['attr_name']]=true;}
            foreach($links as$link)if(isset($names[$link['attr_name']])){$id=(int)$link['attr_value'];if(!isset($byId[$id])||$byId[$id]['lang']!==$link['lang'])throw new InvalidArgumentException('栏目语言关联失效，请先修复');$selected[$id]=true;}
        }while(count($selected)!==$before);
        $marks=[];foreach(array_keys($selected)as$id){$this->assertCategory($byId[$id]);$marks[]=$byId[$id]['lang'];}
        if(($this->actor['trusted']??false)!==true)(new LanguageAccessService($this->repository))->assertLanguages(array_values(array_unique($marks)),$this->actor);
        foreach($this->repository->table('archives')->whereIn('typeid',array_keys($selected))->select()->toArray()as$row)$this->assertArchive($row);
    }
    /** New categories must not create records outside a finite authorized set. */
    public function assertCreateCategory(): void
    {
        foreach($this->grants() as $grant)if($grant['categories']===null)return;
        throw new InvalidArgumentException('当前角色仅可管理指定栏目，不能新增栏目');
    }
}
