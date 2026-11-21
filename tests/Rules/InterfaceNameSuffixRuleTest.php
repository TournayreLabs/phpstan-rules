<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use TournayreLabs\PHPStanRules\Rules\InterfaceNameSuffixRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<InterfaceNameSuffixRule>
 */
final class InterfaceNameSuffixRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new InterfaceNameSuffixRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/interfaces.php'], [
            [
                'Interface TournayreLabs\PHPStanRules\Tests\Rules\data\Bad must have the "Interface" suffix.',
                9,
            ],
        ]);
    }
}
