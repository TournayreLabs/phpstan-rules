<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\DoctrineQueryCallRule;

/**
 * @extends RuleTestCase<DoctrineQueryCallRule>
 */
final class DoctrineQueryCallRuleTest extends RuleTestCase
{
    private bool $allowObjectRepositories = true;

    protected function getRule(): Rule
    {
        return new DoctrineQueryCallRule(
            ['TournayreLabs\PHPStanRules\Tests\Rules\data\Allowed*'],
            $this->allowObjectRepositories
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/doctrine-stubs.php', __DIR__ . '/data/doctrine-query.php'], [
            [$this->message('Doctrine\Persistence\ObjectRepository', 'find'), 47],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findBy'), 48],
            [$this->message('Doctrine\ORM\EntityRepository', 'count'), 53],
            [$this->message('Doctrine\ORM\EntityRepository', 'findByName'), 54],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findBy'), 59],
            [$this->message('Doctrine\Persistence\ObjectManager', 'getRepository'), 69],
            [$this->message('Doctrine\ORM\EntityManagerInterface', 'createQuery'), 70],
            [$this->message('Doctrine\ORM\AbstractQuery', 'getResult'), 75],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findAll'), 80],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findAll'), 85],
        ]);
    }

    public function testRepositoriesAreNotAllowedByTypeWhenPatternsOnly(): void
    {
        $this->allowObjectRepositories = false;

        $this->analyse([__DIR__ . '/data/doctrine-stubs.php', __DIR__ . '/data/doctrine-query.php'], [
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findOneBy'), 13],
            [$this->message('Doctrine\ORM\EntityRepository', 'createQueryBuilder'), 18],
            [$this->message('Doctrine\ORM\AbstractQuery', 'getResult'), 18],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'find'), 47],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findBy'), 48],
            [$this->message('Doctrine\ORM\EntityRepository', 'count'), 53],
            [$this->message('Doctrine\ORM\EntityRepository', 'findByName'), 54],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findBy'), 59],
            [$this->message('Doctrine\Persistence\ObjectManager', 'getRepository'), 69],
            [$this->message('Doctrine\ORM\EntityManagerInterface', 'createQuery'), 70],
            [$this->message('Doctrine\ORM\AbstractQuery', 'getResult'), 75],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findAll'), 80],
            [$this->message('Doctrine\Persistence\ObjectRepository', 'findAll'), 85],
        ]);
    }

    private function message(string $type, string $method): string
    {
        return sprintf(
            'Native Doctrine query method %s::%s() cannot be called here: use a business method of a repository'
            . ' or of an allowed service (null object or exception when nothing is found).',
            $type,
            $method
        );
    }
}
