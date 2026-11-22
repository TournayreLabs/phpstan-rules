<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Expr\TypeExpr;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;

/**
 * A call passing at least two arguments, at least one of them positional, must use PHP 8 named arguments.
 *
 * @implements Rule<CallLike>
 */
final class NamedArgumentsRule implements Rule
{
    /**
     * @param list<string> $excludedPaths A file is skipped when its path starts with one of these entries
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly array $excludedPaths = [],
    ) {
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->isFirstClassCallable() || $this->isExcluded($scope->getFile())) {
            return [];
        }

        $args = array_values($node->getArgs());
        if (count($args) < 2 || !$this->hasPositionalArgument($args)) {
            return [];
        }

        $callee = $this->callee($node, $scope, $args);
        if ($callee === null) {
            return [];
        }

        $suggestion = $this->suggestion($args, $callee['parameters']);
        if ($suggestion === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Call to %s with %d arguments, at least one of them positional:'
                    . ' use PHP 8 named arguments (%s).',
                    $callee['label'],
                    count($args),
                    $suggestion
                )
            )
                ->identifier('tournayreLabs.namedArguments')
                ->build(),
        ];
    }

    private function isExcluded(string $file): bool
    {
        foreach ($this->excludedPaths as $path) {
            if ($path !== '' && str_starts_with($file, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<Arg> $args
     */
    private function hasPositionalArgument(array $args): bool
    {
        $positional = false;
        foreach ($args as $arg) {
            if ($arg->unpack) {
                return false;
            }

            if ($arg->name === null) {
                $positional = true;
            }
        }

        return $positional;
    }

    /**
     * @param list<Arg> $args
     *
     * @return array{label: string, parameters: list<\PHPStan\Reflection\ParameterReflection>}|null
     */
    private function callee(CallLike $node, Scope $scope, array $args): ?array
    {
        if ($node instanceof FuncCall) {
            return $this->functionCallee($node, $scope, $args);
        }

        if ($node instanceof MethodCall) {
            if (!$node->name instanceof Identifier) {
                return null;
            }

            // Synthetic call emitted by phpstan-phpunit for each data provider row, absent from the source.
            // @phpstan-ignore phpstanApi.instanceofAssumption
            if ($node->var instanceof TypeExpr) {
                return null;
            }

            return $this->method($scope->getType($node->var), $node->name->toString(), $scope, $args);
        }

        if ($node instanceof StaticCall) {
            if (!$node->class instanceof Name || !$node->name instanceof Identifier) {
                return null;
            }

            return $this->method($scope->resolveTypeByName($node->class), $node->name->toString(), $scope, $args);
        }

        if ($node instanceof New_) {
            if (!$node->class instanceof Name) {
                return null;
            }

            return $this->method($scope->resolveTypeByName($node->class), '__construct', $scope, $args);
        }

        return null;
    }

    /**
     * @param list<Arg> $args
     *
     * @return array{label: string, parameters: list<\PHPStan\Reflection\ParameterReflection>}|null
     */
    private function functionCallee(FuncCall $node, Scope $scope, array $args): ?array
    {
        if (!$node->name instanceof Name || !$this->reflectionProvider->hasFunction($node->name, $scope)) {
            return null;
        }

        $function = $this->reflectionProvider->getFunction($node->name, $scope);
        if ($function->isBuiltin() || $function->acceptsNamedArguments()->no()) {
            return null;
        }

        $acceptor = ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $args,
            $function->getVariants(),
            $function->getNamedArgumentsVariants()
        );

        return ['label' => $function->getName() . '()', 'parameters' => $acceptor->getParameters()];
    }

    /**
     * @param list<Arg> $args
     *
     * @return array{label: string, parameters: list<\PHPStan\Reflection\ParameterReflection>}|null
     */
    private function method(Type $type, string $name, Scope $scope, array $args): ?array
    {
        if (count($type->getObjectClassNames()) !== 1) {
            return null;
        }

        $method = $scope->getMethodReflection($type, $name);
        if ($method === null || $method->acceptsNamedArguments()->no()) {
            return null;
        }

        $declaringClass = $method->getDeclaringClass();
        if (!$declaringClass->hasNativeMethod($name)) {
            return null;
        }

        $acceptor = ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $args,
            $method->getVariants(),
            $method->getNamedArgumentsVariants()
        );

        return [
            'label' => $declaringClass->getName() . '::' . $method->getName() . '()',
            'parameters' => $acceptor->getParameters(),
        ];
    }

    /**
     * @param list<Arg> $args
     * @param list<\PHPStan\Reflection\ParameterReflection> $parameters
     */
    private function suggestion(array $args, array $parameters): ?string
    {
        $parts = [];
        foreach ($args as $position => $arg) {
            if ($arg->name !== null) {
                $parts[] = $arg->name->toString() . ': …';
                continue;
            }

            $parameter = $parameters[$position] ?? null;
            if ($parameter === null || $parameter->isVariadic()) {
                return null;
            }

            $parts[] = $parameter->getName() . ': …';
        }

        return implode(', ', $parts);
    }
}
