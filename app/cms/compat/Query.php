<?php
namespace app\cms\compat;

/** Converts ThinkPHP 5 query contracts into the maintained Think ORM connection. */
final class Query
{
    public function __construct(private object $query, private ?string $tablePrefix = null) {}
    public function __clone() { $this->query=clone $this->query; }
    private function expand($value) { return Db::expand($value, $this->tablePrefix); }
    public function join($join, $condition = null, $type = 'INNER'): self {
        if (empty($condition) && is_array($join)) {
            foreach ($join as $item) if (is_array($item) && count($item) >= 2) $this->join($item[0], $item[1], $item[2] ?? $type);
        } elseif (!empty($condition)) $this->query->join($this->expand($join), $condition, $type);
        return $this;
    }
    public function where($field,$op=null,$value=null): self { return $this->condition('where',$field,$op,$value,func_num_args()); }
    public function whereOr($field,$op=null,$value=null): self { return $this->condition('whereOr',$field,$op,$value,func_num_args()); }
    private function condition($method,$field,$op,$value,$argc): self {
        if ($field===[] || $field==='' || $field===null) return $this;
        if (is_array($field)) {
            foreach($field as $key=>$condition) {
                if (is_int($key)) { if(is_array($condition)) $this->$method(...$condition); else $this->$method($condition); }
                elseif(is_array($condition)) {
                    if (isset($condition[0]) && is_string($condition[0])) {
                        $operator=self::operator($condition[0]);
                        if ($operator==='exp') $this->query->whereExp($key,$condition[1]??'');
                        else $this->query->$method($key,$operator,$condition[1]??null);
                    } else { foreach($condition as $item) if(is_array($item)) $this->$method($key,...$item); }
                } else $this->query->$method($key,'=',$condition);
            }
        } elseif ($argc===1) {
            if ($field instanceof \Closure) {
                // Native grouped builders must preserve the legacy adapter's
                // raw expressions and condition-array semantics inside them.
                $this->query->$method(fn ($query) => $field(new self($query, $this->tablePrefix)));
            } elseif ($field instanceof \think\db\Raw) {
                $rawMethod=$method==='whereOr'?'whereOrRaw':'whereRaw';
                $this->query->$rawMethod($this->expand($field->getValue()), $field->getBind());
            } elseif (is_string($field)) { $rawMethod=$method==='whereOr'?'whereOrRaw':'whereRaw';$this->query->$rawMethod($this->expand($field)); } else $this->query->$method($field);
        } elseif ($argc===2) $this->query->$method($field,'=',$op);
        else $this->query->$method($field,self::operator($op),$value);
        return $this;
    }
    private static function operator($op) { return match(strtolower((string)$op)) {'eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notin'=>'not in','notlike'=>'not like',default=>strtolower((string)$op)}; }
    public function cache(...$args): self { return $this; }
    /** ThinkPHP 5 consumed query options after each terminal operation. */
    private function consume(callable $operation) {
        $table=$this->query->getOption('table');
        try { return $operation(); }
        finally { $this->query->removeOption();if($table!==null)$this->query->table($table); }
    }
    public function select($data=null) { return $this->consume(function()use($data){$v=$data===false?$this->query->fetchSql(true)->select():($data===null?$this->query->select():$this->query->select($data));return is_object($v)&&method_exists($v,'toArray')?$v->toArray():$v;}); }
    public function find($data=null) { return $this->consume(function()use($data){$v=$data===null?$this->query->find():$this->query->find($data);return is_object($v)&&method_exists($v,'toArray')?$v->toArray():$v;}); }
    public function getField($field,$separator=null) {
        if($separator==true)return $this->column($field);
        if(str_contains($field,','))return $this->column($field,trim(explode(',',$field)[0]));
        return $this->value($field);
    }
    public function getAllWithIndex($key='', $data=null) { $items=$this->select($data);return $data===false?$items:($key!==''?array_column($items,null,$key):$items); }
    public function limit($offset,$length=null):self {
        if(is_string($offset)&&str_contains($offset,','))[$offset,$length]=array_pad(explode(',',$offset,2),2,null);
        if($length===null)$this->query->limit((int)$offset);else $this->query->limit((int)$offset,(int)$length);
        return $this;
    }
    public function add(array $data) {return $this->consume(fn()=>$this->query->insertGetId($data));}
    public function save(array $data) {return $this->consume(fn()=>$this->query->getOption('where')?$this->query->update($data):$this->query->insert($data));}
    public function allowField($fields):self {return $this;}
    public function getSqlWithIndex($sql,$key='', $bind=[],$master=false,$class=false) {$rows=$this->query->getConnection()->query($this->expand($sql),$bind);return $key!==''?array_column($rows,null,$key):$rows;}
    public function paginate($rows=15,$simple=false,$config=[]) {
        $config=array_merge(['var_page'=>'page','path'=>Request::instance()->baseUrl(),'query'=>[]],$config);
        $page=max(1,(int)($config['page']??input('param.'.$config['var_page'].'/d',1)));
        if (is_array($rows)) { $config=array_merge($config,$rows); $rows=$rows['list_rows']??15; }
        $rows=max(1,(int)$rows); $count=clone $this;
        $count->query->removeOption('field')->removeOption('order')->removeOption('limit');
        $total=$simple?null:$count->query->count();
        $items=$this->consume(fn()=>$this->query->page($page,$rows+($simple?1:0))->select()->toArray());
        $driver=($config['type']??'eyou')==='mobile'?'Mobile':'Eyou';
        $class=__NAMESPACE__.'\\paginator\\driver\\'.$driver;
        return new $class($items,$rows,$page,$total,$simple,$config);
    }
    public function __call($method,$args) {
        if (in_array($method,['join','leftJoin','rightJoin','table','orderRaw','whereRaw'],true) && isset($args[0])) $args[0]=$this->expand($args[0]);
        if ($method==='order' && isset($args[0]) && is_string($args[0]) && count($args)===1) $method='orderRaw';
        if($method==='getTableFields' && isset($args[0])) $args[0]=$this->expand($args[0]);
        if($method==='setInc') return $this->consume(fn()=>$this->query->inc($args[0],$args[1]??1)->update());
        if($method==='setDec') return $this->consume(fn()=>$this->query->dec($args[0],$args[1]??1)->update());
        if($method==='setField') return $this->consume(fn()=>$this->query->update([$args[0]=>$args[1]]));
        if(in_array($method,['value','column','count','sum','avg','min','max','insert','insertGetId','insertAll','update','delete','execute','query'],true))return $this->consume(fn()=>$this->query->$method(...$args));
        $value=$this->query->$method(...$args);
        return $value===$this->query?$this:$value;
    }
}
