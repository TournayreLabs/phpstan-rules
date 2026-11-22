<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A repository must not extend "Doctrine\ORM\EntityRepository" (nor "ServiceEntityRepository"):
 * it keeps the native Doctrine API public. It should wrap an EntityManager instead (composition).
 *
 * @implements Rule<InClassNode>
 */
final class DoctrineRepositoryNoInheritanceRule implements Rule
{
    private const ENTITY_REPOSITORY = 'Doctrine\ORM\EntityRepository';

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();

        if (!$class->isSubclassOf(self::ENTITY_REPOSITORY)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Class %s must not extend %s: inject the EntityManager instead (composition).',
                    $class->getDisplayName(),
                    self::ENTITY_REPOSITORY
                )
            )
                ->identifier('tournayreLabs.doctrineRepositoryNoInheritance')
                ->build(),
        ];
    }
}
