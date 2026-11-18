<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace app\cms\compat;

/** No retained Webman request: each call resolves the active request context. */
final class Request
{
    private $fallbackFilter = 'strip_sql,htmlspecialchars';
    private $fallbackEnv = [];
    private $fallbackSession = [];
    public static function instance(): self { return new self(); }
    private function current() { return Runtime::context()?->request ?? (function_exists('request') ? \request() : null); }
    public function param($name='', $default=null, $filter='') {
        $ctx=Runtime::context(); $req=$this->current();
        $all=$req ? $req->all() : [];
        if ($ctx) $all=array_replace($all,$ctx->parameters,['tid'=>$ctx->tid,'aid'=>$ctx->aid]);
        return $this->input($all,$name,$default,$filter);
    }
    public function get($name='', $default=null, $filter='') { return $this->input($this->current()?->get()??(Runtime::context()?->parameters??[]),$name,$default,$filter); }
    public function post($name='', $default=null, $filter='') { return $this->input($this->current()?->post()??[],$name,$default,$filter); }
    public function request($name='', $default=null, $filter='') { return $this->param($name,$default,$filter); }
    public function server($name='', $default=null, $filter='') {
        $req=$this->current();
        $data=$req?[]:$_SERVER;
        foreach($req?->header()??[] as $key=>$value)$data['HTTP_'.strtoupper(str_replace('-','_',$key))]=$value;
        $data=array_replace($data,[
            'REQUEST_TIME'=>time(),'REQUEST_URI'=>$req?->uri()??'/',
            'REQUEST_METHOD'=>$req?->method()??'GET','HTTP_HOST'=>$this->host(),
            'SERVER_NAME'=>$req?->host(true)??'localhost','REQUEST_SCHEME'=>$this->scheme(),
            'HTTPS'=>$this->isSsl()?'on':'off','SCRIPT_NAME'=>Runtime::constant('ROOT_DIR').'/index.php',
        ]);
        return $this->input($data,is_string($name)?strtoupper($name):$name,$default,$filter);
    }
    public function cookie($name='', $default=null, $filter='') { return $this->input(array_replace($this->current()?->cookie()??[],Runtime::context()?->memo['response_cookies']??[]),$name,$default,$filter); }
    public function session($name='', $default=null, $filter='') {
        $context=Runtime::context();
        if($context!==null)$data=&$context->memo['request.session'];
        else $data=&$this->fallbackSession;
        // This is the upstream Request snapshot, not a Session write. A later
        // native session change is only reloaded while this snapshot is empty.
        if(empty($data))$data=Session::get();
        if(is_array($name))return $data=array_merge($data,$name);
        return $this->input($data,$name,$default,$filter);
    }
    public function route($name='', $default=null, $filter='') {return $this->input(Runtime::context()?->parameters??[],$name,$default,$filter);}
    public function put($name='', $default=null, $filter='') {return $this->input($this->current()?->post()??[],$name,$default,$filter);}
    public function patch($name='', $default=null, $filter='') {return $this->put($name,$default,$filter);}
    public function delete($name='', $default=null, $filter='') {return $this->put($name,$default,$filter);}
    public function path($name='', $default=null, $filter='') {return $this->route($name,$default,$filter);}
    public function env($name='', $default=null, $filter='') {
        $context=Runtime::context();
        if($context!==null)$data=&$context->memo['request.env'];
        else $data=&$this->fallbackEnv;
        // The original source is $_ENV, not the worker process environment.
        // Keep setters within this request and preserve explicit empty/zero values.
        if(empty($data))$data=$_ENV;
        if(is_array($name))return $data=array_merge($data,$name);
        return $this->input($data,false===$name?false:strtoupper((string)$name),$default,$filter);
    }
    public function module() { return Runtime::context()?->module??'home'; }
    public function controller() { return Runtime::context()?->controller??'Index'; }
    public function action() { return Runtime::context()?->action??'index'; }
    public function host($strict=false) { return $this->current()?->host((bool)$strict)??'localhost'; }
    public function domain() { return $this->scheme().'://'.$this->host(); }
    public function scheme() { return $this->current()?->header('x-forwarded-proto')?:'http'; }
    public function isSsl() { return $this->scheme()==='https'; }
    public function url($domain=false) { $u=$this->current()?->uri()??'/'; return $domain?$this->domain().$u:$u; }
    public function baseUrl($domain=false) { $u=$this->current()?->path()??'/'; return $domain?$this->domain().$u:$u; }
    public function baseFile($domain=false) { return ($domain?$this->domain():'').Runtime::constant('ROOT_DIR').'/index.php'; }
    public function root($domain=false) { return ($domain?$this->domain():'').Runtime::constant('ROOT_DIR'); }
    public function rootDomain($domain='', $tq_suffix=false) {
        $configured=Runtime::config('url_domain_root');
        if($configured && $domain==='')return $configured;
        $host=$domain!==''?(string)$domain:$this->host(true);
        if($host==='' || filter_var(trim($host,'[]'),FILTER_VALIDATE_IP))return $tq_suffix?'':$host;
        // Upstream preserves the caller's spelling and tests regional suffixes
        // case-sensitively. Host validation above remains independent of that.
        $parts=explode('.',$host);$count=count($parts);
        if($count<2)return $tq_suffix?'':$host;
        $regional=['cn','tw','hk','nz','au','uk','co','jp','hj','ai','de','za','br','th'];
        $second=['ac','ah','bj','cn','com','co','cq','fj','edu','gd','gs','gov','goho','gx','gz','ha','hb','he','hi','hl','hk','hn','jl','js','jx','ln','lt','me','mo','net','nm','nx','org','plc','qh','sc','sd','sh','sn','sx','tj','tw','xj','xz','yn','zj','bbhj'];
        $suffixLength=$count>=3 && in_array($parts[$count-1],$regional,true) && in_array($parts[$count-2],$second,true)?2:1;
        return $tq_suffix?'.'.implode('.',array_slice($parts,-$suffixLength)):implode('.',array_slice($parts,-($suffixLength+1)));
    }
    public function domainSuffix($domain='') {return $this->rootDomain($domain,true);}
    public function subDomain($domainValue='', $ignoreIP=true, $host='') {
        if($domainValue!=='')return $this->scheme().'://'.$domainValue.'.'.$this->rootDomain();
        $host=$host!==''?$host:$this->host(true);
        if($ignoreIP && filter_var(trim($host,'[]'),FILTER_VALIDATE_IP))return '';
        $root=$this->rootDomain($host);
        return strcasecmp($host,$root)===0?'':substr($host,0,-strlen($root)-1);
    }
    public function port() {return (int)(parse_url($this->domain(),PHP_URL_PORT)?:($this->isSsl()?443:80));}
    public function pathinfo() {return trim((string)($this->current()?->path()??'/'),'/');}
    public function ip($type=0,$adv=false) {$ip=$this->current()?->getRealIp()??'127.0.0.1';return $type?sprintf('%u',ip2long($ip)):$ip;}
    public function isValidIP($ip,$type=''):bool {return filter_var($ip,FILTER_VALIDATE_IP,match(strtolower($type)) {'ipv4'=>FILTER_FLAG_IPV4,'ipv6'=>FILTER_FLAG_IPV6,default=>0})!==false;}
    public function has($name,$type='param',$checkEmpty=false):bool
    {
        if ($type === 'env' || $type === 'session') {
            // Upstream filters the whole source only while its lazy storage is
            // empty. Later existence checks inspect raw keys and values.
            $context = Runtime::context();
            $stored = $context !== null ? ($context->memo['request.'.$type] ?? []) : ($type === 'env' ? $this->fallbackEnv : $this->fallbackSession);
            $data = $this->$type(empty($stored) ? '' : false);
            foreach (explode('.', $name) as $part) {
                if (!isset($data[$part])) return false;
                $data = $data[$part];
            }
            return !$checkEmpty || $data !== '';
        }
        if (!in_array($type, ['get','post','param','request','route','put','session','cookie','server','path','patch','delete'], true)) {
            $value=$this->$type($name);
            return $value!==null && (!$checkEmpty || $value!=='');
        }
        // Original stored sources are inspected before input filters/casts.
        // patch/delete have no upstream storage property and use its getter fallback.
        $data=$this->$type(in_array($type, ['patch','delete'], true) ? '' : false);
        foreach (explode('.', $name) as $part) {
            if (!isset($data[$part])) return false;
            $data=$data[$part];
        }
        return !$checkEmpty || $data!=='';
    }
    public function file($name='') {return $this->current()?->file($name===''?null:$name);}
    public function token($name='__token__',$type='md5') { $v=bin2hex(random_bytes(16)); Session::set($name,$v); return $v; }
    public function method() {return $this->current()?->method()??'GET';}
    public function isGet():bool {return $this->method()==='GET';}
    public function isPost():bool {return $this->method()==='POST';}
    public function isAjax():bool {return (bool)($this->current()?->isAjax()??false);}
    public function isMobile():bool {return Runtime::context()?->device==='mobile';}
    public function langset($lang='') {if($lang!=='')Lang::range($lang);return Lang::range();}
    public function input($data = [], $name = '', $default = null, $filter = '')
    {
        if (false === $name) {
            // 获取原始数据
            return $data;
        }
        $name = (string) $name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $type) = explode('/', $name);
            } else {
                $type = 's';
            }
            // 按.拆分成多维数组进行判断
            foreach (explode('.', $name) as $val) {
                if (isset($data[$val])) {
                    $data = $data[$val];
                } else {
                    // 无输入数据，返回默认值
                    return $default;
                }
            }
            if (is_object($data)) {
                return $data;
            }
        }

        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }
        return $data;
    }

    /**
     * 设置或获取当前的过滤规则
     * @param mixed $filter 过滤规则
     * @return mixed
     */
    public function filter($filter = null)
    {
        $context = Runtime::context();
        if ($context !== null) {
            if (!array_key_exists('request.filter', $context->memo)) {
                $context->memo['request.filter'] = array_key_exists('default_filter', $context->config) ? $context->config['default_filter'] : 'strip_sql,htmlspecialchars';
            }
            if ($filter === null) return $context->memo['request.filter'];
            $context->memo['request.filter'] = $filter;
            return null;
        }
        if ($filter === null) return $this->fallbackFilter;
        $this->fallbackFilter = $filter;
        return null;
    }

    protected function getFilter($filter, $default)
    {
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter();
            if (is_string($filter) && false === strpos($filter, '/')) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array) $filter;
            }
        }

        $filter[] = $default;
        return $filter;
    }

    /**
     * 递归过滤给定的值
     * @param mixed $value   键值
     * @param mixed $key     键名
     * @param array $filters 过滤方法+默认值
     * @return mixed
     */
    private function filterValue(&$value, $key, $filters)
    {
        $default = array_pop($filters);
        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                if (is_string($filter) && strtolower(ltrim($filter, '\\')) === 'htmlspecialchars') {
                    // PHP7 default flags are ENT_COMPAT, without ENT_SUBSTITUTE.
                    // Unsupported array/resource/plain-object warning behavior is
                    // outside this finite candidate's diagnostic parity contract.
                    if (is_scalar($value) || $value === null || (is_object($value) && method_exists($value, '__toString'))) {
                        $value = htmlspecialchars((string)$value, ENT_COMPAT, ini_get('default_charset'));
                    } else {
                        $value = null;
                    }
                } else {
                    $value = call_user_func($filter, $value);
                }
            } elseif (is_scalar($value)) {
                if (false !== strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }
        return $this->filterExp($value);
    }

    /**
     * 过滤表单中的表达式
     * @param string $value
     * @return void
     */
    public function filterExp(&$value)
    {
        // 过滤查询特殊字符
        if (is_string($value) && preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT LIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOT EXISTS|NOTEXISTS|EXISTS|NOT NULL|NOTNULL|NULL|BETWEEN TIME|NOT BETWEEN TIME|NOTBETWEEN TIME|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
        // TODO 其他安全过滤
        // Existing compat Request does not run checkedUrlParams; outside T16 filter scope.
    }
    
    /**
     * 强制类型转换
     * @param string $data
     * @param string $type
     * @return mixed
     */
    private function typeCast(&$data, $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array) $data;
                break;
            // 数字
            case 'd':
                $data = (int) $data;
                break;
            // 浮点
            case 'f':
                $data = (float) $data;
                break;
            // 布尔
            case 'b':
                $data = (boolean) $data;
                break;
            // 字符串
            case 's':
            default:
                if (is_scalar($data)) {
                    $data = (string) $data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
        }
    }

}
