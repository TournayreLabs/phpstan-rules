<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Requires interface names to end with the "Interface" suffix.
 *
 * @implements Rule<Interface_>
 */
final class InterfaceNameSuffixRule implements Rule
{
    private const SUFFIX = 'Interface';

    public function getNodeType(): string
    {
        return Interface_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->namespacedName === null) {
            return [];
        }

        $name = $node->namespacedName->toString();

        if (str_ends_with($name, self::SUFFIX)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf('Interface %s must have the "%s" suffix.', $name, self::SUFFIX)
            )
                ->identifier('tournayreLabs.interfaceNameSuffix')
                ->build(),
        ];
    }
}
