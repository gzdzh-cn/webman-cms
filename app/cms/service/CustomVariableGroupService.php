<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** config_attribute.type_id refers to original shared config_type IDs. */
class CustomVariableGroupService
{
    public function __construct(private ?CmsRepository $repository=null){$this->repository??=new CmsRepository();}
    private function scope(array $input,bool $lock=false):array
    {
        $query=$this->repository->table('language')->order('id asc');if($lock)$query->lock(true);$languages=$query->select()->toArray();if(!$languages)throw new InvalidArgumentException('语言不存在');$lang=$input['lang']??$languages[0]['mark'];foreach($languages as$selected)if($selected['mark']===$lang)return compact('languages','lang','selected')+['main_lang'=>$languages[0]['mark']];throw new InvalidArgumentException('语言不存在');
    }
    public function read(array $input=[]):array
    {
        $scope=$this->scope($input);return ['lang'=>$scope['lang'],'main_lang'=>$scope['main_lang'],'languages'=>$scope['languages'],'groups'=>$this->repository->table('config_type')->order('sort_order asc,id asc')->select()->toArray()];
    }
    public function save(array $input):int
    {
        return $this->repository->transaction(function(CmsRepository $r)use($input):int{
            $scope=$this->scope($input,true);if($scope['lang']!==$scope['main_lang'])throw new InvalidArgumentException('共享分组请切换到主语言修改');
            $id=$input['id']??0;if(!is_scalar($id)||!ctype_digit((string)$id))throw new InvalidArgumentException('分组ID无效');$id=(int)$id;
            $old=$id?$r->table('config_type')->where('id',$id)->lock(true)->find():null;if($id&&!$old)throw new InvalidArgumentException('分组不存在');
            $name=$input['type_name']??$old['type_name']??'';if(!is_string($name)||trim($name)===''||mb_strlen(trim($name))>100)throw new InvalidArgumentException('分组名称需为1至100个字符');$name=trim($name);
            $sort=$input['sort_order']??$old['sort_order']??100;if(!is_scalar($sort)||!preg_match('/^\d{1,8}$/D',(string)$sort))throw new InvalidArgumentException('分组排序无效');
            $duplicates=$r->table('config_type')->where('type_name',$name);if($id)$duplicates->where('id','<>',$id);if($duplicates->lock(true)->find())throw new InvalidArgumentException('分组名称已存在');
            $data=['type_name'=>$name,'sort_order'=>(int)$sort,'update_time'=>time()];if($id){$r->table('config_type')->where('id',$id)->update($data);return $id;}
            return (int)$r->table('config_type')->insertGetId($r->filter('config_type',$data+['lang'=>$scope['main_lang'],'add_time'=>time()]));
        });
    }
    public function delete(array $input):void
    {
        $this->repository->transaction(function(CmsRepository $r)use($input):void{
            $scope=$this->scope($input,true);if($scope['lang']!==$scope['main_lang'])throw new InvalidArgumentException('共享分组请切换到主语言修改');
            $ids=$input['ids']??[];if(!is_array($ids)||!$ids)throw new InvalidArgumentException('请选择分组');foreach($ids as$id)if(!is_scalar($id)||!ctype_digit((string)$id)||(int)$id<1)throw new InvalidArgumentException('分组ID无效');$ids=array_values(array_unique(array_map('intval',$ids)));
            $groups=$r->table('config_type')->whereIn('id',$ids)->lock(true)->select()->toArray();if(count($groups)!==count($ids))throw new InvalidArgumentException('分组不存在');
            if($r->table('config_attribute')->whereIn('type_id',$ids)->lock(true)->find())throw new InvalidArgumentException('分组内仍有变量（含回收站），请先移动或处理变量');
            $r->table('config_type')->whereIn('id',$ids)->delete();
        });
    }
}
