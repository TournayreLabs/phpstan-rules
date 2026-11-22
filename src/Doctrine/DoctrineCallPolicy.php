<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Doctrine;

use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Reports calls of forbidden Doctrine methods, unless the calling class matches one of the
 * allowed class patterns or, when enabled, is an "ObjectRepository".
 */
final class DoctrineCallPolicy
{
    private const REPOSITORY = 'Doctrine\Persistence\ObjectRepository';

    /**
     * @param array<class-string|string, list<string>> $forbidden Doctrine type => method names ("*" wildcard allowed)
     * @param list<string> $allowedClasses Class name patterns ("*" wildcard allowed) allowed to call them
     */
    public function __construct(
        private readonly array $forbidden,
        private readonly array $allowedClasses,
        private readonly string $message,
        private readonly string $identifier,
        private readonly bool $allowObjectRepositories = true,
    ) {
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function errors(CallLike $call, Scope $scope): array
    {
        // PHPStan also visits a "?->" call as a plain MethodCall, so nullsafe calls are covered too.
        if (!$call instanceof MethodCall) {
            return [];
        }

        if (!$call->name instanceof Identifier || $this->callerIsAllowed($scope)) {
            return [];
        }

        $callee = $scope->getType($call->var);
        $method = $call->name->name;

        foreach ($this->forbidden as $type => $methods) {
            if (!(new ObjectType($type))->isSuperTypeOf($callee)->yes()) {
                continue;
            }

            foreach ($methods as $pattern) {
                if (fnmatch(strtolower($pattern), strtolower($method))) {
                    return [
                        RuleErrorBuilder::message(sprintf($this->message, $type, $method))
                            ->identifier($this->identifier)
                            ->build(),
                    ];
                }
            }
        }

        return [];
    }

    private function callerIsAllowed(Scope $scope): bool
    {
        $caller = $scope->getClassReflection();
        if ($caller === null) {
            return false;
        }

        if ($this->allowObjectRepositories && $caller->is(self::REPOSITORY)) {
            return true;
        }

        foreach ($this->allowedClasses as $pattern) {
            if (fnmatch($pattern, $caller->getName(), FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }
}
