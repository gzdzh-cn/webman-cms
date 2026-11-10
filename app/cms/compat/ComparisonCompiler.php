<?php
namespace app\cms\compat;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Arg;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

/** Lower only compiler-marked tag comparisons; raw PHP keeps its native semantics. */
final class ComparisonCompiler
{
    private int $temporary = 0;
    private string $prefix;

    public static function rewrite(string $php): string
    {
        if (!str_contains($php, '::tagCondition(') && !str_contains($php, '::tagSwitch(')) return $php;
        $parser = (new \PhpParser\ParserFactory())->createForHostVersion();
        try { $old = $parser->parse($php); } catch (\PhpParser\Error $error) { return $php; }
        if (!$old) return $php;
        $compiler = new self(); $compiler->prefix = '__eyou_compare_' . substr(hash('sha256', $php), 0, 16) . '_';
        while (str_contains($php, '$' . $compiler->prefix)) $compiler->prefix .= '_';
        $clone = new \PhpParser\NodeTraverser(new \PhpParser\NodeVisitor\CloningVisitor(), new \PhpParser\NodeVisitor\NameResolver(null, ['replaceNodes'=>false]));
        $new = $clone->traverse($old);
        $visitor = new class($compiler) extends \PhpParser\NodeVisitorAbstract {
            public function __construct(private ComparisonCompiler $compiler) {}
            public function leaveNode(Node $node) {
                if ($this->compiler->marker($node, 'tagCondition')) return $this->compiler->condition($node->args[0]->value);
                if ($node instanceof Stmt\Switch_ && $this->compiler->marker($node->cond, 'tagSwitch')) return $this->compiler->switchNode($node);
                if ($node instanceof Stmt\If_ || $node instanceof Stmt\Echo_ || $node instanceof Stmt\Return_ || $node instanceof Stmt\Expression) return $this->compiler->cleanStatement($node);
                return null;
            }
        };
        $new = (new \PhpParser\NodeTraverser($visitor))->traverse($new);
        return (new \PhpParser\PrettyPrinter\Standard())->printFormatPreserving($new, $old, $parser->getTokens());
    }

    public function marker(Node $node, string $method): bool
    {
        return $node instanceof Expr\StaticCall && $node->class instanceof Name && $node->class->toString() === Runtime::class
            && $node->name instanceof Node\Identifier && $node->name->toString() === $method && count($node->args) === 1;
    }
    private function runtime(string $name, array $values): Expr\StaticCall
    {
        return new Expr\StaticCall(new Name\FullyQualified(Runtime::class), $name, array_map(static fn($value)=>new Arg($value), $values));
    }
    private function temporary(): Expr\Variable { return new Expr\Variable($this->prefix . ++$this->temporary); }
    private function copy(Node $node): Node
    {
        $copy = clone $node; $copy->setAttribute('origNode', null);
        foreach ($copy->getSubNodeNames() as $name) {
            if ($copy->$name instanceof Node) $copy->$name = $this->copy($copy->$name);
            elseif (is_array($copy->$name)) foreach ($copy->$name as $index=>$child) if ($child instanceof Node) $copy->{$name}[$index]=$this->copy($child);
        }
        return $copy;
    }

    public function condition(Node $node): Node
    {
        // Function bodies retain their own language boundary. Their call result
        // participates in the tag comparison, without rewriting their internals.
        if ($node instanceof Node\FunctionLike || $node instanceof Stmt\Class_) return $node;
        foreach ($node->getSubNodeNames() as $name) {
            if ($node->$name instanceof Node) $node->$name = $this->condition($node->$name);
            elseif (is_array($node->$name)) foreach ($node->$name as $index=>$child) if ($child instanceof Node) $node->{$name}[$index]=$this->condition($child);
        }
        if ($node instanceof Expr\FuncCall && $node->name instanceof Name && strtolower($node->name->toString()) === 'get_defined_vars' && !$node->args) return $this->runtime('comparisonScope', [$node, new String_($this->prefix)]);
        $operators = [Expr\BinaryOp\Equal::class=>'==',Expr\BinaryOp\NotEqual::class=>'!=',Expr\BinaryOp\Smaller::class=>'<',Expr\BinaryOp\SmallerOrEqual::class=>'<=',Expr\BinaryOp\Greater::class=>'>',Expr\BinaryOp\GreaterOrEqual::class=>'>=',Expr\BinaryOp\Spaceship::class=>'<=>'];
        if (isset($operators[get_class($node)])) {
            $operator = new String_($operators[get_class($node)]);
            // A compiled-variable operand is fetched by the native opcode only
            // after the other expression has run. Reading it last also avoids
            // creating an otherwise undefined variable through a reference.
            if ($node->left instanceof Expr\Variable && is_string($node->left->name)) return $this->runtime('compareCapturedRightFirst', [$this->operand($node->right, true), $this->runtime('comparisonValue', [$node->left]), $operator]);
            return $this->runtime('compareCaptured', [$this->operand($node->left), $this->operand($node->right, true), $operator]);
        }
        return $node;
    }

    /** Preserve CV/reference reads until both operands have finished evaluating. */
    private function operand(Expr $value, bool $finalRead = false): Expr
    {
        if ($value instanceof Expr\Variable && is_string($value->name)) {
            if ($finalRead || in_array($value->name,['this','GLOBALS'],true)) return $this->runtime('comparisonValue', [$value]);
            $defined = new Expr\FuncCall(new Name\FullyQualified('array_key_exists'), [new Arg(new String_($value->name)), new Arg(new Expr\FuncCall(new Name\FullyQualified('get_defined_vars')))]);
            return new Expr\Ternary($defined, $this->runtime('comparisonReference', [$this->copy($value)]), $this->runtime('comparisonValue', [$value]));
        }
        if ($value instanceof Expr\ArrayDimFetch || $value instanceof Expr\PropertyFetch) return $this->path($value);
        if ($value instanceof Expr\FuncCall || $value instanceof Expr\MethodCall || $value instanceof Expr\StaticCall) return $this->call($value);
        return $this->runtime('comparisonValue', [$value]);
    }

    /** Evaluate path keys once, retaining the root's native delayed fetch. */
    private function path(Expr $value): Expr
    {
        $steps=[];$root=$value;
        while ($root instanceof Expr\ArrayDimFetch || $root instanceof Expr\PropertyFetch) {
            array_unshift($steps,[$root instanceof Expr\ArrayDimFetch?'offset':'property',$root instanceof Expr\ArrayDimFetch?$root->dim:$root->name]);$root=$root->var;
        }
        $parent=$this->temporary();$parameters=[new Node\Param(clone $parent)];$arguments=[new Arg($this->operand($root))];$keys=[];
        foreach ($steps as [$kind,$key]) {
            if ($key instanceof Node\Identifier) $keys[]=new String_($key->toString());
            elseif ($key instanceof Node\Scalar || $key instanceof Expr\ConstFetch) $keys[]=$key;
            elseif ($key instanceof Expr) {$temporary=$this->temporary();$parameters[]=new Node\Param(clone $temporary);$arguments[]=new Arg($key);$keys[]=$temporary;}
            else return $this->runtime('comparisonValue',[$value]);
        }
        $statements=[];
        foreach ($steps as $index=>[$kind]) {
            $key=$keys[$index];$container=new Expr\PropertyFetch(clone $parent,'value');
            $read=$kind==='offset'?new Expr\ArrayDimFetch($this->copy($container),$this->copy($key)):new Expr\PropertyFetch($this->copy($container),$this->copy($key));
            $capture=new Expr\Ternary($this->runtime($kind==='offset'?'referenceOffset':'referenceProperty',$kind==='offset'?[$container,$key]:[$container,$key,new Node\Scalar\MagicConst\Class_()]),$this->runtime('comparisonReference',[$this->copy($read)]),$this->runtime('comparisonValue',[$read]));
            $next=$this->temporary();$statements[]=new Stmt\Expression(new Expr\Assign(clone $next,$capture));$statements[]=new Stmt\Unset_([clone $parent]);$parent=$next;
        }
        $statements[]=new Stmt\Return_($parent);
        return new Expr\FuncCall(new Expr\Closure(['params'=>$parameters,'stmts'=>$statements]),$arguments);
    }

    /** Keep native arguments in the caller scope, including extract()/variable variables. */
    private function call(Expr $call): Expr
    {
        $call=$this->copy($call);$temporaries=[];
        $capture=function(Expr $expression)use(&$temporaries):array {
            $temporary=$this->temporary();$temporary->setAttribute('comparisonExternal',true);$temporaries[]=$temporary;
            return [clone $temporary,new Expr\Assign(clone $temporary,$expression)];
        };
        if ($call instanceof Expr\FuncCall) {
            if ($call->name instanceof Name) {$resolved=$call->name->getAttribute('resolvedName');$callable=new String_($resolved instanceof Name?$resolved->toString():$call->name->toString());}
            else {[$call->name,$callable]=$capture($call->name);}
        } elseif ($call instanceof Expr\MethodCall) {
            [$call->var,$receiver]=$capture($call->var);
            if ($call->name instanceof Node\Identifier) $method=new String_($call->name->toString());
            else {[$call->name,$method]=$capture($call->name);}
            $callable=new Expr\Array_([new Node\ArrayItem($receiver),new Node\ArrayItem($method)]);
        } else {
            if ($call->class instanceof Name) $class=new Expr\ClassConstFetch(clone $call->class,'class');
            else {[$call->class,$class]=$capture($call->class);}
            if ($call->name instanceof Node\Identifier) $method=new String_($call->name->toString());
            else {[$call->name,$method]=$capture($call->name);}
            $callable=new Expr\Array_([new Node\ArrayItem($class),new Node\ArrayItem($method)]);
        }
        $body=new Expr\Ternary($this->runtime('returnsReference',[$callable,new Node\Scalar\MagicConst\Class_()]),$this->runtime('comparisonReference',[$this->copy($call)]),$this->runtime('comparisonValue',[$call]));
        if (!$temporaries) return $body;
        return $this->runtime('releaseComparison',array_merge([$body],$temporaries));
    }

    /** Temporary storage is scoped to the native statement, never worker state. */
    public function cleanStatement(Stmt $statement): Node|array
    {
        $temporaries=[];$find=function(Node $node)use(&$find,&$temporaries):void {
            if ($node instanceof Node\FunctionLike || $node instanceof Stmt\Class_) return;
            if ($node instanceof Expr\Variable && $node->getAttribute('comparisonExternal') && is_string($node->name)) $temporaries[$node->name]=$this->copy($node);
            foreach($node->getSubNodeNames() as$name) {if($node->$name instanceof Node)$find($node->$name);elseif(is_array($node->$name))foreach($node->$name as$child)if($child instanceof Node)$find($child);}
        };$find($statement);
        if (!$temporaries) return $statement;
        if ($statement instanceof Stmt\If_) {
            array_unshift($statement->stmts,new Stmt\Unset_(array_values($temporaries)));
            foreach($statement->elseifs as$elseif)array_unshift($elseif->stmts,new Stmt\Unset_(array_values($temporaries)));
            if($statement->else)array_unshift($statement->else->stmts,new Stmt\Unset_(array_values($temporaries)));
        }
        return [new Stmt\TryCatch([$statement],[],new Stmt\Finally_([new Stmt\Unset_(array_values($temporaries))]))];
    }

    public function switchNode(Stmt\Switch_ $switch): array
    {
        $expression=$this->condition($switch->cond->args[0]->value);
        $live=$expression instanceof Expr\Variable && is_string($expression->name);
        $subject=$this->temporary();
        // A native switch CV is reread after each case expression. A reference
        // to its old storage would incorrectly survive unset()/rebinding.
        $assign=new Stmt\Expression($live?$this->runtime('comparisonTouch',[$this->copy($expression)]):new Expr\Assign(clone $subject,$this->operand($expression)));
        $switch->cond=new Expr\ConstFetch(new Name('true'));
        foreach ($switch->cases as $case) if ($case->cond) {
            $caseValue=$this->operand($this->condition($case->cond),true);
            $case->cond=$live?$this->runtime('compareCapturedRightFirst',[$caseValue,$this->runtime('comparisonValue',[$this->copy($expression)]),new String_('==')]):$this->runtime('compareCaptured',[clone $subject,$caseValue,new String_('==')]);
        }
        $body=new Stmt\TryCatch([$assign,$switch],[],new Stmt\Finally_([new Stmt\Unset_([$subject])]));
        $cleaned=$this->cleanStatement($body);
        return is_array($cleaned)?$cleaned:[$cleaned];
    }
}
