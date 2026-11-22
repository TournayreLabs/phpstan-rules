<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use TournayreLabs\PHPStanRules\Doctrine\DoctrineCallPolicy;

/**
 * Native Doctrine persistence methods (persist, remove, flush, transactions...)
 * can only be called from a repository or from an allowed service, so that the
 * rest of the code relies on business methods.
 *
 * @implements Rule<CallLike>
 */
final class DoctrinePersistenceCallRule implements Rule
{
    private readonly DoctrineCallPolicy $policy;

    /**
     * @param list<string> $allowedClasses Class name patterns ("*" wildcard allowed)
     * @param bool $allowObjectRepositories Also allow every class implementing "ObjectRepository"
     */
    public function __construct(array $allowedClasses = [], bool $allowObjectRepositories = true)
    {
        $this->policy = new DoctrineCallPolicy(
            [
                'Doctrine\Persistence\ObjectManager' => ['persist', 'remove', 'flush', 'refresh', 'detach', 'clear'],
                'Doctrine\ORM\EntityManagerInterface' => [
                    'wrapInTransaction',
                    'beginTransaction',
                    'commit',
                    'rollback',
                ],
            ],
            $allowedClasses,
            'Native Doctrine persistence method %s::%s() cannot be called here: use a business method of a'
            . ' repository or of an allowed service.',
            'tournayreLabs.doctrinePersistenceCall',
            $allowObjectRepositories,
        );
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return $this->policy->errors($node, $scope);
    }
}
