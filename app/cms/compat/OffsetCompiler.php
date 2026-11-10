<?php
namespace app\cms\compat;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Arg;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

/**
 * Lower PHP 7 offset operations without changing fetch context or evaluating a
 * parent/key twice. Existence probes stay native. Writable operations still end
 * in a native ArrayDimFetch, including runtime-selected reference arguments.
 */
final class OffsetCompiler
{
    private int $temporary = 0;
    private string $temporaryPrefix;
    private string $namespace = '';
    private bool $returnsReference = false;
    private array $constantAliases = [];

    public static function rewrite(string $php): string
    {
        $parser = (new \PhpParser\ParserFactory())->createForHostVersion();
        try { $old = $parser->parse($php); }
        catch (\PhpParser\Error $error) {
            // Let PHP report invalid template PHP as its normal ParseError.
            return $php;
        }
        if (!$old) return $php;
        $clone = new \PhpParser\NodeTraverser(new \PhpParser\NodeVisitor\CloningVisitor(),
            new \PhpParser\NodeVisitor\NameResolver(null, ['replaceNodes' => false]));
        $new = $clone->traverse($old);
        $compiler = new self();
        $compiler->temporaryPrefix = '__eyou_offset_' . substr(hash('sha256', $php), 0, 16) . '_';
        while (str_contains($php, '$' . $compiler->temporaryPrefix)) $compiler->temporaryPrefix .= '_';
        // Resolve legacy bare keys before lowering offsets, so key lookup is
        // evaluated at its original position relative to later key/RHS calls.
        foreach ($new as &$node) $node = $compiler->node($compiler->offsetConstants($node));
        unset($node);
        return (new \PhpParser\PrettyPrinter\Standard())->printFormatPreserving($new, $old, $parser->getTokens());
    }

    private function runtime(string $method, array $arguments = []): Expr\StaticCall
    {
        return new Expr\StaticCall(new Name\FullyQualified(Runtime::class), $method,
            array_map(static fn ($value) => new Arg($value), $arguments));
    }

    private function temporary(): Expr\Variable
    {
        return new Expr\Variable($this->temporaryPrefix . ++$this->temporary);
    }

    /** Only array-key expressions receive PHP7's undefined-name fallback. */
    private function offsetConstants(Node $node, bool $key = false, string $namespace = ''): Node
    {
        $previousAliases = null;
        if ($node instanceof Stmt\Namespace_) {
            $namespace = $node->name?->toString() ?? '';
            $previousAliases = $this->constantAliases; $this->constantAliases = [];
        }
        if ($node instanceof Stmt\Use_ || $node instanceof Stmt\GroupUse) foreach ($node->uses as $use)
            if (($use->type ?: $node->type) === Stmt\Use_::TYPE_CONSTANT)
                $this->constantAliases[$use->getAlias()->toString()] = true;
        if ($node instanceof Stmt\Const_ || $node instanceof Stmt\ClassConst || $node instanceof Stmt\Property
            || $node instanceof Node\Param || $node instanceof Node\Attribute) return $node;
        if ($key && $node instanceof Expr\ConstFetch && $node->name->isUnqualified()
            && !isset($this->constantAliases[$node->name->toString()])
            && !in_array(strtolower($node->name->toString()), ['true', 'false', 'null'], true))
            return $this->runtime('offsetConstant', [new String_($node->name->toString()), new String_($namespace)]);
        // Literal define() names are already rewritten by the token adapter.
        // They still need the legacy fallback when read before definition.
        if ($key && $node instanceof Expr\StaticCall && $node->class instanceof Name
            && $node->class->toString() === Runtime::class && $node->name instanceof Node\Identifier
            && $node->name->toString() === 'declaredConstant' && count($node->args) === 1
            && $node->args[0]->value instanceof String_)
            return $this->runtime('offsetConstant', [$node->args[0]->value, new String_($namespace)]);
        foreach ($node->getSubNodeNames() as $name) {
            if ($node->$name instanceof Node)
                $node->$name = $this->offsetConstants($node->$name, $key || ($node instanceof Expr\ArrayDimFetch && $name === 'dim'), $namespace);
            elseif (is_array($node->$name)) foreach ($node->$name as $index => $child)
                if ($child instanceof Node) $node->{$name}[$index] = $this->offsetConstants($child, $key, $namespace);
        }
        if ($previousAliases !== null) $this->constantAliases = $previousAliases;
        return $node;
    }

    private function copy(Node $node, bool $detach = false): Node
    {
        $copy = clone $node;
        if ($detach) $copy->setAttribute('origNode', null);
        foreach ($copy->getSubNodeNames() as $name) {
            if ($copy->$name instanceof Node) $copy->$name = $this->copy($copy->$name, $detach);
            elseif (is_array($copy->$name)) foreach ($copy->$name as $index => $child)
                if ($child instanceof Node) $copy->{$name}[$index] = $this->copy($child, $detach);
        }
        return $copy;
    }

    private function nativeTarget(Expr $parent, ?Expr $key, bool $reference): Expr
    {
        if ($key === null) return new Expr\ArrayDimFetch($parent);
        return new Expr\ArrayDimFetch(
            new Expr\PropertyFetch($this->runtime($reference ? 'offsetReferenceTarget' : 'offsetValueTarget', [$parent, $key]), 'container'),
            $this->runtime('offsetTargetKey')
        );
    }

    private function delayedOffset(Expr\ArrayDimFetch $path): bool
    {
        $node = $path;
        while ($node instanceof Expr\ArrayDimFetch || $node instanceof Expr\PropertyFetch) {
            $key = $node instanceof Expr\ArrayDimFetch ? $node->dim : $node->name;
            if ($key instanceof Expr && !$key instanceof Node\Scalar && !$key instanceof Expr\ConstFetch
                && !($key instanceof Expr\Variable && is_string($key->name))) return true;
            $node = $node->var;
        }
        return false;
    }

    /**
     * Native PHP delays CV/property/offset fetches until dimension expressions
     * have run. A nested helper call must not snapshot those containers first.
     * Evaluate expression operands into closure parameters, retain CV operands
     * by reference, then fetch the path. The closure exists for this expression
     * only; neither it nor its captures enter request/process caches.
     */
    private function delayedPath(Expr $node, array &$parameters, array &$arguments, array &$uses, array &$late): Expr
    {
        if ($node instanceof Expr\ArrayDimFetch || $node instanceof Expr\PropertyFetch) {
            $node->var = $this->delayedPath($node->var, $parameters, $arguments, $uses, $late);
            $name = $node instanceof Expr\ArrayDimFetch ? 'dim' : 'name';
            if ($node->$name instanceof Expr) $node->$name = $this->delayedOperand($node->$name, $parameters, $arguments, $uses);
            return $node;
        }
        if ($node instanceof Expr\Variable && is_string($node->name)) {
            if (!in_array($node->name, ['GLOBALS', 'this'], true)) $uses[$node->name] = new Expr\ClosureUse(clone $node, true);
            return $node;
        }
        if ($node instanceof Expr\Variable || $node instanceof Expr\StaticPropertyFetch) {
            $temporary = $this->temporary();
            $late[] = [new Node\Param(clone $temporary, null, null, true), new Arg($this->node($node))];
            return $temporary;
        }
        $temporary = $this->temporary();
        $parameters[] = new Node\Param(clone $temporary, null, null, $node instanceof Expr\FuncCall
            || $node instanceof Expr\MethodCall || $node instanceof Expr\StaticCall);
        $arguments[] = new Arg($this->node($node));
        return $temporary;
    }

    private function delayedOperand(Expr $node, array &$parameters, array &$arguments, array &$uses): Expr
    {
        if ($node instanceof Node\Scalar || $node instanceof Expr\ConstFetch) return $node;
        if ($node instanceof Expr\Variable && is_string($node->name)) {
            if (!in_array($node->name, ['GLOBALS', 'this'], true)) $uses[$node->name] = new Expr\ClosureUse(clone $node, true);
            return $node;
        }
        $temporary = $this->temporary();
        $parameters[] = new Node\Param(clone $temporary);
        $arguments[] = new Arg($this->node($node));
        return $temporary;
    }

    private function deferredOffset(Expr\ArrayDimFetch $path, string $mode): Expr
    {
        $parameters = []; $arguments = []; $uses = []; $late = [];
        $path = $this->delayedPath($this->copy($path, true), $parameters, $arguments, $uses, $late);
        foreach ($late as [$parameter, $argument]) { $parameters[] = $parameter; $arguments[] = $argument; }
        if ($mode === 'argument') $body = $this->nativeTarget($this->node($path->var), $this->node($path->dim), false);
        else $body = $this->node($path, $mode);
        if ($mode !== 'value') $body = $body->var->var;
        $call = new Expr\FuncCall(new Expr\Closure(['params' => $parameters, 'uses' => array_values($uses), 'stmts' => [new Stmt\Return_($body)]]), $arguments);
        if ($mode === 'value') return $call;
        return new Expr\ArrayDimFetch(new Expr\PropertyFetch($call, 'container'), $this->runtime('offsetTargetKey'));
    }

    private function deferredAssignment(Expr $assignment): Expr
    {
        $parameters = []; $arguments = []; $uses = []; $late = [];
        $path = $this->delayedPath($this->copy($assignment->var, true), $parameters, $arguments, $uses, $late);
        $right = $this->temporary();
        $parameters[] = new Node\Param(clone $right, null, null, $assignment instanceof Expr\AssignRef);
        $arguments[] = new Arg($this->node($assignment->expr, $assignment instanceof Expr\AssignRef ? 'write' : 'value'));
        foreach ($late as [$parameter, $argument]) { $parameters[] = $parameter; $arguments[] = $argument; }
        $body = $this->copy($assignment, true);
        $body->var = $this->node($path, 'write'); $body->expr = $right;
        $statements = $assignment instanceof Expr\AssignRef
            ? [new Stmt\Expression($body), new Stmt\Return_(clone $right)] : [new Stmt\Return_($body)];
        return new Expr\FuncCall(new Expr\Closure(['byRef' => $assignment instanceof Expr\AssignRef,
            'params' => $parameters, 'uses' => array_values($uses), 'stmts' => $statements]), $arguments);
    }

    /** $mode: value, write/reference, presence/unset. */
    private function node(Node $node, string $mode = 'value'): Node
    {
        if ($node instanceof Node\Scalar\InterpolatedString || $node instanceof Expr\ShellExec) {
            // PHP's interpolation grammar cannot contain arbitrary calls.
            // Concatenation keeps text bytes and expression evaluation order.
            $value = new String_('');
            foreach ($node->parts as $part) $value = new Expr\BinaryOp\Concat($value,
                $part instanceof Node\InterpolatedStringPart ? new String_($part->value) : $this->node($this->copy($part, true)));
            return $node instanceof Expr\ShellExec ? new Expr\FuncCall(new Name\FullyQualified('shell_exec'), [new Arg($value)]) : $value;
        }
        if ($node instanceof Expr\ArrayDimFetch) {
            if ($mode === 'presence' || ($node->var instanceof Expr\Variable && $node->var->name === 'GLOBALS')) {
                $node->var = $this->node($node->var, 'presence');
                if ($node->dim) $node->dim = $this->node($node->dim);
                return $node;
            }
            if ($this->delayedOffset($node)) return $this->deferredOffset($node, $mode);
            $parent = $this->node($node->var, $mode);
            $key = $node->dim ? $this->node($node->dim) : null;
            if ($mode === 'write') return $this->nativeTarget($parent, $key, true);
            return $key ? $this->runtime('readOffset', [$parent, $key]) : $node;
        }
        if ($node instanceof Expr\Assign || $node instanceof Expr\AssignOp || $node instanceof Expr\AssignRef) {
            if (!$node instanceof Expr\AssignOp\Coalesce && $node->var instanceof Expr\ArrayDimFetch)
                return $this->deferredAssignment($node);
            $node->var = $this->node($node->var, 'write');
            $node->expr = $this->node($node->expr, $node instanceof Expr\AssignRef ? 'write' : 'value');
            return $node;
        }
        if ($node instanceof Expr\PreInc || $node instanceof Expr\PreDec || $node instanceof Expr\PostInc || $node instanceof Expr\PostDec) {
            $node->var = $this->node($node->var, 'write'); return $node;
        }
        if ($node instanceof Expr\Isset_) {
            foreach ($node->vars as &$var) $var = $this->node($var, 'presence');
            unset($var); return $node;
        }
        if ($node instanceof Expr\Empty_) {
            $node->expr = $this->node($node->expr, 'presence'); return $node;
        }
        if ($node instanceof Expr\BinaryOp\Coalesce) {
            $node->left = $this->node($node->left, 'presence'); $node->right = $this->node($node->right); return $node;
        }
        if ($node instanceof Stmt\Unset_) {
            foreach ($node->vars as &$var) $var = $this->node($var, 'presence');
            unset($var); return $node;
        }
        if ($node instanceof Stmt\Foreach_) {
            $node->expr = $this->node($node->expr, $node->byRef ? 'write' : 'value');
            if ($node->keyVar) $node->keyVar = $this->node($node->keyVar, 'write');
            $node->valueVar = $this->node($node->valueVar, 'write');
            foreach ($node->stmts as &$stmt) $stmt = $this->node($stmt);
            unset($stmt); return $node;
        }
        if ($node instanceof Expr\List_ || ($node instanceof Expr\Array_ && $mode === 'write')) {
            foreach ($node->items as $item) if ($item) {
                if ($item->key) $item->key = $this->node($item->key);
                $item->value = $this->node($item->value, 'write');
            }
            return $node;
        }
        if ($node instanceof Node\ArrayItem) {
            if ($node->key) $node->key = $this->node($node->key);
            $node->value = $this->node($node->value, $node->byRef ? 'write' : 'value'); return $node;
        }
        if ($node instanceof Stmt\Return_ && $node->expr) {
            $node->expr = $this->node($node->expr, $this->returnsReference ? 'write' : 'value'); return $node;
        }
        if ($node instanceof Expr\Yield_ && $node->value) {
            if ($node->key) $node->key = $this->node($node->key);
            $node->value = $this->node($node->value, $this->returnsReference ? 'write' : 'value'); return $node;
        }
        if ($node instanceof Expr\CallLike) return $this->call($node);
        if ($node instanceof Node\FunctionLike) {
            $previous = $this->returnsReference; $this->returnsReference = $node->returnsByRef();
            if ($node instanceof Expr\ArrowFunction) $node->expr = $this->node($node->expr, $this->returnsReference ? 'write' : 'value');
            elseif ($node->getStmts() !== null) foreach ($node->stmts as &$stmt) $stmt = $this->node($stmt);
            unset($stmt); $this->returnsReference = $previous; return $node;
        }
        if ($node instanceof Stmt\Namespace_) {
            $previous = $this->namespace; $this->namespace = $node->name ? $node->name->toString() : '';
            foreach ($node->stmts as &$stmt) $stmt = $this->node($stmt);
            unset($stmt); $this->namespace = $previous; return $node;
        }
        // Constant expressions must stay constant expressions.
        if ($node instanceof Stmt\Const_ || $node instanceof Stmt\ClassConst || $node instanceof Stmt\Property
            || $node instanceof Node\Param || $node instanceof Node\Attribute) return $node;
        // An object/variable property is a value read even when that property's
        // offset is writable; fetching the object does not require a reference.
        if ($node instanceof Expr\PropertyFetch || $node instanceof Expr\NullsafePropertyFetch) {
            $node->var = $this->node($node->var, $mode === 'presence' ? 'presence' : 'value');
            if ($node->name instanceof Expr) $node->name = $this->node($node->name);
            return $node;
        }
        foreach ($node->getSubNodeNames() as $name) {
            $value = $node->$name;
            if ($value instanceof Node) $node->$name = $this->node($value);
            elseif (is_array($value)) foreach ($value as $index => $child) {
                if ($child instanceof Node) $node->{$name}[$index] = $this->node($child);
            }
        }
        return $node;
    }

    private function call(Expr\CallLike $node): Node
    {
        $needsSignature = false;
        foreach ($node->getArgs() as $arg) if ($arg instanceof Arg && $arg->value instanceof Expr\ArrayDimFetch
            && !$arg->unpack && !$arg->byRef) $needsSignature = true;

        $callable = null;
        $knownParameters = null;
        if ($node instanceof Expr\FuncCall) {
            if ($node->name instanceof Name) {
                $resolved = $node->name->getAttribute('resolvedName');
                $name = $resolved instanceof Name ? $resolved->toString() : $node->name->toString();
                if (!$resolved && !$node->name instanceof Name\FullyQualified && $this->namespace !== '') $name = $this->namespace . '\\' . $name;
                $callable = new String_($name);
            } else {
                $node->name = $this->node($node->name);
                if ($needsSignature) {
                    $temporary = $this->temporary();
                    $node->name = $this->runtime('captureOffsetCallable', [$node->name, clone $temporary]);
                    $callable = $temporary;
                } else $callable = clone $node->name;
            }
        } elseif ($node instanceof Expr\MethodCall || $node instanceof Expr\NullsafeMethodCall) {
            $node->var = $this->node($node->var);
            $object = clone $node->var;
            if ($needsSignature) {
                $object = $this->temporary(); $node->var = $this->runtime('captureOffsetReceiver', [$node->var, clone $object]);
            }
            if ($node->name instanceof Node\Identifier) $method = new String_($node->name->toString());
            else {
                $node->name = $this->node($node->name); $method = clone $node->name;
                if ($needsSignature) {
                    $method = $this->temporary(); $node->name = new Expr\Assign(clone $method, $node->name);
                }
            }
            $callable = new Expr\Array_([new Node\ArrayItem($object), new Node\ArrayItem($method)]);
        } elseif ($node instanceof Expr\StaticCall || $node instanceof Expr\New_) {
            if ($node->class instanceof Name) $class = new Expr\ClassConstFetch(clone $node->class, 'class');
            elseif ($node->class instanceof Expr) {
                $node->class = $this->node($node->class); $class = clone $node->class;
                if ($needsSignature) {
                    $class = $this->temporary(); $node->class = $this->runtime('captureOffsetReceiver', [$node->class, clone $class]);
                }
            } else $class = null;
            if ($node instanceof Expr\New_) {
                $method = new String_('__construct');
                if ($node->class instanceof Stmt\Class_) {
                    $constructor = $node->class->getMethod('__construct');
                    if ($constructor) $knownParameters = $constructor->params;
                    elseif ($node->class->extends) $class = new Expr\ClassConstFetch(clone $node->class->extends, 'class');
                    else $knownParameters = [];
                    $node->class = $this->node($node->class);
                }
            } elseif ($node->name instanceof Node\Identifier) $method = new String_($node->name->toString());
            else {
                $node->name = $this->node($node->name); $method = clone $node->name;
                if ($needsSignature) {
                    $method = $this->temporary(); $node->name = new Expr\Assign(clone $method, $node->name);
                    // Regenerate the call so a replaced variable method name
                    // receives braces; the preserving printer lacks this fixup.
                    $node->setAttribute('origNode', null);
                }
            }
            if ($class) $callable = new Expr\Array_([new Node\ArrayItem($class), new Node\ArrayItem($method)]);
        }

        foreach ($node->args as $position => $arg) {
            if (!$arg instanceof Arg) continue;
            if ($arg->byRef || $arg->unpack) $arg->value = $this->node($arg->value, 'write');
            elseif ($arg->value instanceof Expr\ArrayDimFetch && $arg->value->var instanceof Expr\Variable
                && $arg->value->var->name === 'GLOBALS') $arg->value = $this->node($arg->value, 'presence');
            elseif ($arg->value instanceof Expr\ArrayDimFetch && $arg->value->dim && ($callable || $knownParameters !== null)) {
                // Do not turn a reference argument into a value-returning call.
                // Reflection happens at execution, after callable evaluation.
                if ($callable) $reference = $this->runtime('argumentByReference', [clone $callable,
                    $arg->name ? new String_($arg->name->toString()) : new Node\Scalar\Int_($position)]);
                else {
                    $parameter = $knownParameters[$position] ?? null;
                    if (!$parameter && $knownParameters && end($knownParameters)->variadic) $parameter = end($knownParameters);
                    $reference = new Expr\ConstFetch(new Name($parameter && $parameter->byRef ? 'true' : 'false'));
                }
                $path = $arg->value;
                if ($this->delayedOffset($path)) {
                    $writable = $this->deferredOffset($this->copy($path), 'write');
                    $readable = $this->deferredOffset($path, 'argument');
                } else {
                    $writable = $this->nativeTarget($this->node($this->copy($path->var), 'write'), $this->node($this->copy($path->dim)), true);
                    $readable = $this->nativeTarget($this->node($path->var), $this->node($path->dim), false);
                }
                // Select the target object before its final native offset fetch.
                $target = new Expr\Ternary($reference, $writable->var->var, $readable->var->var);
                $arg->value = new Expr\ArrayDimFetch(
                    new Expr\PropertyFetch($this->runtime('offsetSelectedTarget', [$target]), 'container'),
                    $this->runtime('offsetTargetKey'));
            } else $arg->value = $this->node($arg->value);
        }
        return $node;
    }
}
