<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A parameter must not be autowired from an environment variable
 * ("#[Autowire(env: '...')]" or "#[Autowire('%env(...)%')]"): inject a configuration object instead.
 * Classes matching an allowed pattern (the configuration objects themselves) are ignored.
 *
 * @implements Rule<InClassMethodNode>
 */
final class SymfonyAutowireEnvRule implements Rule
{
    private const AUTOWIRE = 'Symfony\Component\DependencyInjection\Attribute\Autowire';

    /**
     * @param list<string> $allowedClasses Class name patterns ("*" wildcard allowed) allowed to autowire env variables
     */
    public function __construct(private readonly array $allowedClasses)
    {
    }

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();

        foreach ($this->allowedClasses as $pattern) {
            if (fnmatch($pattern, $class->getName(), FNM_NOESCAPE)) {
                return [];
            }
        }

        $errors = [];
        foreach ($node->getOriginalNode()->getParams() as $param) {
            foreach ($param->attrGroups as $group) {
                foreach ($group->attrs as $attribute) {
                    if (!$this->readsEnv($attribute) || !$param->var instanceof Node\Expr\Variable) {
                        continue;
                    }

                    $errors[] = RuleErrorBuilder::message(
                        sprintf(
                            'Parameter $%s of %s::%s() must not be autowired from an environment variable'
                            . ' (#[Autowire] with env): inject a configuration object instead.',
                            is_string($param->var->name) ? $param->var->name : '?',
                            $class->getName(),
                            $node->getMethodReflection()->getName()
                        )
                    )
                        ->identifier('tournayreLabs.symfonyAutowireEnv')
                        ->line($attribute->getStartLine())
                        ->build();
                }
            }
        }

        return $errors;
    }

    private function readsEnv(Attribute $attribute): bool
    {
        if (strcasecmp($attribute->name->toString(), self::AUTOWIRE) !== 0) {
            return false;
        }

        foreach ($attribute->args as $position => $arg) {
            $name = $arg->name?->toString();

            if ($name === 'env') {
                return true;
            }

            // "value" is the first constructor argument, named or positional.
            if (
                ($name === 'value' || ($name === null && $position === 0))
                && $arg->value instanceof String_
                && str_contains($arg->value->value, '%env(')
            ) {
                return true;
            }
        }

        return false;
    }
}
