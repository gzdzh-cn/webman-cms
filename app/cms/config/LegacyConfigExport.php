<?php
declare(strict_types=1);
namespace app\cms\config;

use PhpParser\Node;
use PhpParser\ParserFactory;

/** Read only recognized data expressions. Never execute a legacy deployment's PHP. */
final class LegacyConfigExport
{
    private function value(Node $node): mixed
    {
        if($node instanceof Node\Scalar\String_ || $node instanceof Node\Scalar\Int_ || $node instanceof Node\Scalar\Float_)return $node->value;
        if($node instanceof Node\Expr\ClassConstFetch && $node->class instanceof Node\Name && strtolower($node->class->toString())==='monolog\\logger' && $node->name instanceof Node\Identifier) return match($node->name->name) {'DEBUG'=>100,'INFO'=>200,'NOTICE'=>250,'WARNING'=>300,'ERROR'=>400,'CRITICAL'=>500,'ALERT'=>550,'EMERGENCY'=>600,default=>throw new \RuntimeException('旧日志级别常量无法迁移')};
        if($node instanceof Node\Expr\ConstFetch)return match(strtolower($node->name->toString())){'true'=>true,'false'=>false,'null'=>null,default=>throw new \RuntimeException('旧 PHP 配置含不支持的常量')};
        if($node instanceof Node\Expr\Array_){$out=[];foreach($node->items as$item){if(!$item||$item->unpack||$item->byRef||!$item->key)throw new \RuntimeException('旧 PHP 配置数组形式无法安全迁移');$out[$this->value($item->key)]=$this->value($item->value);}return$out;}
        if($node instanceof Node\Expr\Cast\String_)return(string)$this->value($node->expr);
        if($node instanceof Node\Expr\BinaryOp\Coalesce)return $this->value($node->left)??$this->value($node->right);
        if($node instanceof Node\Expr\ArrayDimFetch && $node->var instanceof Node\Expr\Variable && $node->var->name==='cmsRedisEnvironment' && $node->dim instanceof Node\Scalar\String_ && preg_match('/^CMS_REDIS_[A-Z_]+$/D',$node->dim->value))return null;
        if($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name && strtolower($node->class->toString())==='app\\cms\\config\\environment' && $node->name instanceof Node\Identifier && in_array($node->name->name,['value','integer','boolean'],true))return isset($node->args[1])?$this->value($node->args[1]->value):null;
        if($node instanceof Node\Expr\BinaryOp\Mul)return $this->value($node->left)*$this->value($node->right);
        if($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Expr\Variable && in_array($node->name->name,['getEnvBool','getEnvInt','getEnvString'],true))return $this->value($node->args[1]->value);
        throw new \RuntimeException('旧 PHP 配置含自定义表达式，需人工迁移');
    }
    private function nodes(string $file): array
    {
        Entry::assertPath($file);$s=lstat($file);
        if(!$s||!is_file($file)||$s['nlink']!==1||$s['size']>1048576)throw new \RuntimeException('旧 PHP 配置文件状态无效');
        try{
            $nodes=(new ParserFactory())->createForHostVersion()->parse(file_get_contents($file))??[];
            $traverser=new \PhpParser\NodeTraverser();$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
            return $traverser->traverse($nodes);
        }catch(\PhpParser\Error){throw new \RuntimeException('旧 PHP 配置语法无效：'.basename($file));}
    }
    private function returned(string $file): array
    {
        foreach($this->nodes($file)as$node)if($node instanceof Node\Stmt\Return_ && $node->expr instanceof Node\Expr\Array_){$out=[];foreach($node->expr->items as$item)if($item&&$item->key)$out[$this->value($item->key)]=$item->value;return$out;}
        throw new \RuntimeException('旧配置不再保存参数或不是固定数组：'.basename($file));
    }
    public function read(string $root): array
    {
        $root=Entry::absolute($root);Entry::assertPath($root);$flat=['version'=>1];
        foreach(LegacyDatabaseConfig::saved($root)as$key=>$value){$field=\app\cms\database\DatabaseSettings::FIELDS[$key];$flat['database.'.$field]=$key==='DB_PORT'?(int)$value:$value;}
        foreach(['cms_cache.php'=>'redis','cms_sms.php'=>'sms','cms_language_pack.php'=>'language']as$file=>$group){
            $path=$root.'/config/'.$file;if(!is_file($path))continue;
            foreach($this->returned($path)as$key=>$node){
                $target=match($group){'sms'=>$key==='code_lifetime'?'limits.sms_code_lifetime':null,'language'=>match($key){'max_bytes'=>'limits.language_pack_bytes','max_entries'=>'limits.language_pack_entries',default=>null},default=>$group.'.'.$key};
                if($target!==null)$flat[$target]=$this->value($node);
            }
        }
        foreach(['app.php'=>['debug'=>'app.debug','default_timezone'=>'app.timezone'],'server.php'=>['stop_timeout'=>'server.stop_timeout','max_package_size'=>'server.max_package_size'],'session.php'=>['session_name'=>'session.name','lifetime'=>'session.lifetime','cookie_lifetime'=>'session.cookie_lifetime','cookie_path'=>'session.cookie_path','domain'=>'session.domain','http_only'=>'session.http_only','secure'=>'session.secure','same_site'=>'session.same_site']]as$file=>$map){foreach($this->returned($root.'/config/'.$file)as$key=>$node)if(isset($map[$key]))$flat[$map[$key]]=$this->value($node);}
        $aliases=['logFileEnv'=>'logging.file','sqlLogDirEnv'=>'logging.sql.file'];
        foreach($this->nodes($root.'/config/log.php')as$node)if($node instanceof Node\Stmt\Expression&&$node->expr instanceof Node\Expr\Assign&&$node->expr->var instanceof Node\Expr\Variable){
            $assign=$node->expr;$name=$assign->var->name;
            if(isset($aliases[$name])&&!isset($flat[$aliases[$name]]))$flat[$aliases[$name]]=$this->value($assign->expr);
            if(in_array($name,['defaultLogConfig','sqlLogConfig'],true)&&$assign->expr instanceof Node\Expr\Array_)foreach($assign->expr->items as$item){if(!$item||!$item->key)continue;$key=$this->value($item->key);if(in_array($key,['enable','print_to_console','print_to_file','max_files','level'],true)){$target=($name==='sqlLogConfig'?'logging.sql.':'logging.').$key;$flat[$target]=$this->value($item->value);}}
        }
        // Preserve explicit connection-pool values without evaluating the driver array.
        foreach($this->nodes($root.'/config/database.php') as $statement) {
            if(!$statement instanceof Node\Stmt\Expression || !$statement->expr instanceof Node\Expr\Assign || !$statement->expr->var instanceof Node\Expr\Variable || $statement->expr->var->name!=='database') continue;
            $pool=$statement->expr->expr;
            foreach(['connections','mysql','pool'] as $part) {
                $next=null;
                if($pool instanceof Node\Expr\Array_)foreach($pool->items as $item)if($item&&$item->key instanceof Node\Scalar\String_&&$item->key->value===$part)$next=$item->value;
                $pool=$next;
            }
            if($pool!==null){if(!$pool instanceof Node\Expr\Array_)throw new \RuntimeException('旧数据库连接池含自定义表达式');foreach($this->value($pool) as $key=>$value)$flat['database.pool.'.$key]=$value;}
        }
        // The source process config's scalar environment fallbacks are exported individually.
        foreach($this->nodes($root.'/config/process.php')as$node)if($node instanceof Node\Stmt\Return_&&$node->expr instanceof Node\Expr\Array_)foreach($node->expr->items as$item)if($item&&$item->key&&$this->value($item->key)==='webman'&&$item->value instanceof Node\Expr\Array_)foreach($item->value->items as$setting)if($setting&&$setting->key){$key=$this->value($setting->key);if($key==='listen'||$key==='count')$flat[$key==='listen'?'server.listen':'server.workers']=$this->value($setting->value);}
        foreach($flat as$key=>$value)$flat[$key]=ConfigSchema::coerce($key,$value);
        return ConfigSchema::expand($flat);
    }
}
