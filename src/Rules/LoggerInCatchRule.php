<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * A catch block that does not rethrow must log the exception
 * through a "Psr\Log\LoggerInterface".
 *
 * @implements Rule<Catch_>
 */
final class LoggerInCatchRule implements Rule
{
    private const LOGGER = 'Psr\Log\LoggerInterface';

    public function getNodeType(): string
    {
        return Catch_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $loggerType = new ObjectType(self::LOGGER);

        $visitor = new class ($scope, $loggerType) extends NodeVisitorAbstract {
            private bool $rethrows = false;
            private bool $logs = false;

            public function __construct(
                private readonly Scope $scope,
                private readonly ObjectType $loggerType,
            ) {
            }

            public function rethrows(): bool
            {
                return $this->rethrows;
            }

            public function logs(): bool
            {
                return $this->logs;
            }

            public function enterNode(Node $node): ?int
            {
                // A throw or a log call inside a nested function/class is not part of the catch flow.
                if ($node instanceof Node\FunctionLike || $node instanceof Node\Stmt\ClassLike) {
                    return NodeVisitor::DONT_TRAVERSE_CHILDREN;
                }

                if ($node instanceof Throw_) {
                    $this->rethrows = true;
                }

                if (
                    $node instanceof MethodCall
                    && $this->loggerType->isSuperTypeOf($this->scope->getType($node->var))->yes()
                ) {
                    $this->logs = true;
                }

                return null;
            }
        };

        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($node->stmts);

        if ($visitor->rethrows() || $visitor->logs()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Catch block that does not rethrow must log the exception with a %s.',
                    self::LOGGER
                )
            )
                ->identifier('tournayreLabs.loggerInCatch')
                ->build(),
        ];
    }
}
