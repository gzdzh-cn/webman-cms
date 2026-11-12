<?php
declare(strict_types=1);
namespace app\cms\template;

use app\cms\CmsContext;
use app\cms\compat\template\taglib\eyou\TagArclist;
use app\cms\compat\template\taglib\eyou\TagTagarclist;

/** Versioned query inputs. Stored SQL is never accepted or executed. */
final class PaginationRecord
{
    private static function scope(): array
    {
        $context=CmsContext::current();
        return [\app\cms\cache\CachePolicy::namespace(), TemplateMode::siteSignature(), TemplateMode::current(), TemplateDialect::VERSION,
            TemplateCapabilities::COMPILE_VERSION, $context->lang, $context->theme, $context->device, $context->site];
    }
    public static function encode(string $kind,string $tagid,array $arguments,string $attributes,int $size): string
    {
        self::arguments($kind,$arguments);
        return json_encode(['format'=>2,'kind'=>$kind,'tagid'=>$tagid,'scope'=>self::scope(),
            'arguments'=>$arguments,'attributes_sha256'=>hash('sha256',$attributes),'pagesize'=>$size],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    }
    public static function decode(string $tagid,array $record): ?array
    {
        $raw=$record['querysql']??null;
        if(!is_string($raw)||strlen($raw)>131072)return null;
        try {
            $data=json_decode($raw,true,16,JSON_THROW_ON_ERROR);
            if(!is_array($data)||($data['format']??null)!==2||($data['tagid']??null)!==$tagid
                ||($data['scope']??null)!==self::scope()||!is_string($data['attributes_sha256']??null)
                ||!hash_equals($data['attributes_sha256'],hash('sha256',(string)($record['attstr']??'')))
                ||!is_int($data['pagesize']??null)||$data['pagesize']!==(int)($record['pagesize']??0)
                ||($record['tagname']??$data['kind']??null)!==($data['kind']??null))return null;
            self::arguments($data['kind']??'', $data['arguments']??[]);
            return $data;
        } catch(\Throwable) { return null; }
    }
    private static function scalarMap(array $values,array $keys): void
    {
        foreach($values as$key=>$value)if(!is_string($key)||!in_array($key,$keys,true)
            ||(!is_scalar($value)&&$value!==null)||strlen((string)$value)>8192)throw new \InvalidArgumentException('分页条件格式无效');
    }
    private static function arguments(string $kind,array $args): void
    {
        $length=match($kind){'arclist'=>12,'tagarclist'=>10,default=>throw new \InvalidArgumentException('分页标签无效')};
        if(!array_is_list($args)||count($args)!==$length||!is_array($args[0])||!is_array($args[6]))throw new \InvalidArgumentException('分页参数格式无效');
        $keys=$kind==='arclist'?['typeid','notypeid','flag','noflag','channel','joinaid','keyword','release','idlist','idrange','aid']:['flag','noflag','keyword'];
        self::scalarMap($args[0],$keys);
        foreach($args as$i=>$value)if(!in_array($i,[0,6],true)&&((!is_scalar($value)&&$value!==null)||strlen((string)$value)>8192))throw new \InvalidArgumentException('分页参数格式无效');
        foreach(['typeid','notypeid','idlist','aid','joinaid']as$key)if(!empty($args[0][$key]))SqlConditions::ids((string)$args[0][$key]);
        if(!empty($args[0]['channel'])&&!preg_match('/^-?\d+$/D',(string)$args[0]['channel']))throw new \InvalidArgumentException('分页模型无效');
        if(!empty($args[0]['idrange'])&&!preg_match('/^\d+-\d+$/D',(string)$args[0]['idrange']))throw new \InvalidArgumentException('分页范围无效');
        if(!preg_match('/^\d+(?:,\d+)?$/D',(string)$args[1]))throw new \InvalidArgumentException('分页行数无效');
        foreach(explode(',',(string)$args[1])as$n)if((int)$n>1000000)throw new \InvalidArgumentException('分页行数超限');
        if(!preg_match('/^[A-Za-z0-9_-]*$/D',(string)$args[5]))throw new \InvalidArgumentException('分页标签编号无效');
        if(count($args[6])>128)throw new \InvalidArgumentException('分页属性过多');
        foreach($args[6]as$key=>$value)if(!is_string($key)||!preg_match('/^\w+$/D',$key)||(!is_scalar($value)&&$value!==null)||strlen((string)$value)>65536)throw new \InvalidArgumentException('分页属性无效');
        if(isset($args[6]['url_params'])) {
            $json=base64_decode((string)$args[6]['url_params'],true);
            if($json===false)throw new \InvalidArgumentException('分页筛选无效');
            $filters=json_decode($json,true,8,JSON_THROW_ON_ERROR);
            if(!is_array($filters)||count($filters)>128)throw new \InvalidArgumentException('分页筛选无效');
            foreach($filters as$key=>$value)if(!is_string($key)||!preg_match('/^\w+$/D',$key)||(!is_scalar($value)&&$value!==null)||strlen((string)$value)>8192)throw new \InvalidArgumentException('分页筛选无效');
        }
    }
    public static function count(array $descriptor): int
    {
        self::arguments($descriptor['kind'],$descriptor['arguments']);
        $args=$descriptor['arguments'];
        // Counting never needs addon fields, thumbnail processing or member metadata.
        $args[3]='';$args[8]='';$args[9]='';
        return match($descriptor['kind']) {
            'arclist'=>(new TagArclist())->paginationCount($args),
            'tagarclist'=>(new TagTagarclist())->paginationCount($args),
        };
    }
}
