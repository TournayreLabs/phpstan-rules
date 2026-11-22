<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\DoctrineRepositoryNoInheritanceRule;

/**
 * @extends RuleTestCase<DoctrineRepositoryNoInheritanceRule>
 */
final class DoctrineRepositoryNoInheritanceRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DoctrineRepositoryNoInheritanceRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/doctrine-stubs.php', __DIR__ . '/data/doctrine-inheritance.php'], [
            [$this->message('LegacyUserRepository'), 8],
            [$this->message('ServiceBackedRepository'), 12],
            // The stub of ServiceEntityRepository itself extends EntityRepository.
            [$this->message('ServiceEntityRepository', 'Doctrine\\Bundle\\DoctrineBundle\\Repository'), 142],
        ]);
    }

    private function message(string $class, string $namespace = 'TournayreLabs\\PHPStanRules\\Tests\\Rules\\data'): string
    {
        return sprintf(
            'Class %s\\%s must not extend Doctrine\ORM\EntityRepository:'
            . ' inject the EntityManager instead (composition).',
            $namespace,
            $class
        );
    }
}
