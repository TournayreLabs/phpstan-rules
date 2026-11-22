<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\DoctrinePersistenceCallRule;

/**
 * @extends RuleTestCase<DoctrinePersistenceCallRule>
 */
final class DoctrinePersistenceCallRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DoctrinePersistenceCallRule(['TournayreLabs\PHPStanRules\Tests\Rules\data\Allowed*']);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/doctrine-stubs.php', __DIR__ . '/data/doctrine-persistence.php'], [
            [$this->message('Doctrine\Persistence\ObjectManager', 'persist'), 41],
            [$this->message('Doctrine\Persistence\ObjectManager', 'remove'), 42],
            [$this->message('Doctrine\Persistence\ObjectManager', 'flush'), 43],
            [$this->message('Doctrine\ORM\EntityManagerInterface', 'beginTransaction'), 48],
            [$this->message('Doctrine\ORM\EntityManagerInterface', 'commit'), 49],
        ]);
    }

    private function message(string $type, string $method): string
    {
        return sprintf(
            'Native Doctrine persistence method %s::%s() cannot be called here: use a business method of a'
            . ' repository or of an allowed service.',
            $type,
            $method
        );
    }
}
