<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use TournayreLabs\PHPStanRules\Doctrine\DoctrineCallPolicy;

/**
 * Native Doctrine query methods (find*, count, createQueryBuilder, getResult...)
 * can only be called from a repository or from an allowed service, so that the
 * rest of the code relies on business methods.
 *
 * @implements Rule<CallLike>
 */
final class DoctrineQueryCallRule implements Rule
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
                'Doctrine\Persistence\ObjectRepository' => ['find', 'findAll', 'findBy', 'findOneBy'],
                'Doctrine\ORM\EntityRepository' => [
                    'count',
                    'matching',
                    'createQueryBuilder',
                    'createResultSetMappingBuilder',
                    'findBy*',
                    'findOneBy*',
                    'countBy*',
                ],
                'Doctrine\Persistence\ObjectManager' => ['find', 'getRepository'],
                'Doctrine\ORM\EntityManagerInterface' => [
                    'getReference',
                    'createQuery',
                    'createQueryBuilder',
                    'createNativeQuery',
                ],
                'Doctrine\ORM\AbstractQuery' => [
                    'getResult',
                    'getSingleResult',
                    'getOneOrNullResult',
                    'getArrayResult',
                    'getScalarResult',
                    'getSingleScalarResult',
                    'toIterable',
                    'execute',
                ],
            ],
            $allowedClasses,
            'Native Doctrine query method %s::%s() cannot be called here: use a business method of a repository'
            . ' or of an allowed service (null object or exception when nothing is found).',
            'tournayreLabs.doctrineQueryCall',
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
