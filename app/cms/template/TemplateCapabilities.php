<?php
declare(strict_types=1);
namespace app\cms\template;

use PhpParser\Node;
use InvalidArgumentException;

/** Shared source/compiled capabilities. Runtime adapters never expose generic dispatch. */
final class TemplateCapabilities
{
    public const COMPILE_VERSION = 'enterprise-syntax-v1';
    public const FUNCTIONS = ['array_slice','count','gettoptype','html_msubstr','htmlspecialchars_decode','intval','is_array','mydate','text_msubstr','date','hex2rgba','get_head_pic','get_filename','msubstr','strlen','floatval','strval','trim','substr','strtolower','strtoupper','in_array','is_numeric'];
    public const ADAPTERS = ['html_msubstr'=>'templateHtmlSubstr', 'thumb_img'=>'templateThumb', 'url'=>'templateUrl', 'equal_pop_login'=>'templateEmpty', 'getusersconfigdata'=>'templateFeature'];
    public const RUNTIME_METHODS = ['tagcondition','tagswitch','rangecontains','filtercount','filterstrlen','templaterequest','templatethumb','templateurl','templateempty','templatefeature','templatehtmlsubstr'];
    public const DISABLED_TAGS = ['user','usermenu','memberlist','memberinfos','collect','collectnum','comment','commentlist','articlepay','downloadpay','spaddress','spcart','sporder','sporderlist','spordergoods','spstatus','sppageorder','sppayapilist','sppurchase','spsearch','spsubmitorder','freebuynum'];


    private const EMPTY_TEXT_TAGS = ['sql','channel','models','channelartlist','modelsartlist','arclist','artlist','list','searchform','type','arcview','tag','tags','tagarclist','tagsartlist','flink','links','language','adv','prenext','beafter','guestbookform','form','foreach','volist','iterate','diyfield','videoplay','videolist','attribute','usermenu','spcart','sporder','spsubmitorder','sporderlist','spordergoods','spaddress','spsearch','sppayapilist','screening','spstatus','memberlist','userslist','memberinfos','usersinfo','likearticle','relevarticle','hotwords','hotkeywords','specnodelist','specnode','specialnode','asklist','commentlist','citysite','navigation','formreply','faq'];

    /** Only compiler sites using encoded PHP literals can admit display text. */
    public static function textAttribute(string $tag, string $attribute): bool
    {
        return ($attribute === 'empty' && in_array($tag, self::EMPTY_TEXT_TAGS, true))
            || (in_array($tag, ['position','crumb'], true) && in_array($attribute, ['symbol','style'], true))
            || ($tag === 'collect' && in_array($attribute, ['collect','cancel'], true));
    }

    public static function requestKey(string $source, string $key): void
    {
        if ($source !== 'param' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z0-9_]+)*$/D', $key)) {
            throw new InvalidArgumentException('请求变量仅支持 Request.param 的固定键路径');
        }
    }

    public static function assignmentTarget(string $name): string
    {
        $name = str_starts_with($name, '$') ? substr($name, 1) : $name;
        if (!preg_match('/^[A-Za-z_]\w*(?:\.[A-Za-z0-9_]+|\[(?:[0-9]+|\'[A-Za-z0-9_]+\'|"[A-Za-z0-9_]+")\])*$/D', $name)) {
            throw new InvalidArgumentException('assign 目标必须是普通变量或固定数组路径');
        }
        preg_match('/^\w+/', $name, $root);
        $variable = strtolower($root[0]);
        if (str_starts_with($variable, '_') || str_starts_with($variable, 'tag') || in_array($variable, ['this','globals','request','think','eyou','webman','cachefile','snapshoturi','snapshotvars'], true)) {
            throw new InvalidArgumentException('assign 不允许修改保留变量');
        }
        return '$'.$name;
    }

    public static function assignment(array $tag, \app\cms\compat\template\TagLib $lib): string
    {
        if (!isset($tag['name'], $tag['value'])) throw new InvalidArgumentException('assign 必须指定 name 和 value');
        $target = self::assignmentTarget((string)$tag['name']);
        $target = $lib->autoBuildVar($target);
        $raw = (string)$tag['value'];
        if (str_starts_with($raw, '$') || str_starts_with($raw, ':')) {
            $value = $lib->autoBuildVar($raw);
            $value = self::rewriteFunctions($value);
            TemplateSourcePolicy::validateExpression($value);
        } else $value = var_export($raw, true);
        return '<?php '.$target.' = '.$value.'; ?>';
    }

    /** Edit only unqualified function callees, never strings, methods or HTML. */
    public static function rewriteFunctions(string $expression): string
    {
        return substr(self::rewritePhp('<?php '.$expression), 6);
    }

    public static function rewritePhp(string $php): string
    {
        $tokens = token_get_all($php); $result = ''; $previous = null;
        foreach ($tokens as $i => $token) {
            $value = is_array($token) ? $token[1] : $token;
            if (is_array($token) && $token[0] === T_STRING && isset(self::ADAPTERS[strtolower($value)])
                && !in_array($previous, [T_OBJECT_OPERATOR,T_NULLSAFE_OBJECT_OPERATOR,T_DOUBLE_COLON,T_NEW,T_FUNCTION], true)) {
                $next = $i + 1;
                while (isset($tokens[$next]) && is_array($tokens[$next]) && in_array($tokens[$next][0], [T_WHITESPACE,T_COMMENT,T_DOC_COMMENT], true)) ++$next;
                if (($tokens[$next] ?? null) === '(') $value = '\\app\\cms\\compat\\Runtime::'.self::ADAPTERS[strtolower($value)];
            }
            $result .= $value;
            if (!is_array($token) || !in_array($token[0], [T_WHITESPACE,T_COMMENT,T_DOC_COMMENT], true)) $previous = is_array($token) ? $token[0] : $token;
        }
        return $result;
    }

    public static function validateCall(Node $node): void
    {
        foreach ($node->args as $arg) {
            if (!$arg instanceof Node\Arg || $arg->byRef || $arg->unpack || $arg->name !== null) throw new InvalidArgumentException('模板调用不支持引用、命名参数或参数展开');
        }
        if ($node instanceof Node\Expr\FuncCall) {
            if (!$node->name instanceof Node\Name || !$node->name->isUnqualified() || !in_array(strtolower($node->name->toString()), self::FUNCTIONS, true)) throw new InvalidArgumentException('模板函数不受支持：'.($node->name instanceof Node\Name ? $node->name->toString() : '[动态函数]'));
            return;
        }
        if (!$node instanceof Node\Expr\StaticCall || !$node->class instanceof Node\Name || strtolower($node->class->toString()) !== 'app\\cms\\compat\\runtime'
            || !$node->name instanceof Node\Identifier || !in_array(strtolower($node->name->toString()), self::RUNTIME_METHODS, true)) throw new InvalidArgumentException('模板静态调用不受支持');
        $method = strtolower($node->name->toString());
        $range = ['templaterequest'=>[2,2], 'templatethumb'=>[1,4], 'templateurl'=>[0,7], 'templateempty'=>[0,1], 'templatefeature'=>[1,1], 'templatehtmlsubstr'=>[1,5]][$method] ?? null;
        if ($range && (count($node->args) < $range[0] || count($node->args) > $range[1])) throw new InvalidArgumentException('模板辅助函数参数数量无效');
        if ($method === 'templatefeature' && (!$node->args[0]->value instanceof Node\Scalar\String_ || $node->args[0]->value->value !== 'shop.shop_open')) throw new InvalidArgumentException('模板仅支持读取已禁用的商城开关');
        if ($method === 'templaterequest') {
            foreach ($node->args as $arg) if (!$arg->value instanceof Node\Scalar\String_) throw new InvalidArgumentException('请求变量必须使用固定键');
            self::requestKey($node->args[0]->value->value, $node->args[1]->value->value);
        }
    }
}
