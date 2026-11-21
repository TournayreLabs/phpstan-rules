<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\LoggerInCatchRule;

/**
 * @extends RuleTestCase<LoggerInCatchRule>
 */
final class LoggerInCatchRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new LoggerInCatchRule();
    }

    public function testRule(): void
    {
        $message = 'Catch block that does not rethrow must log the exception with a Psr\Log\LoggerInterface.';

        $this->analyse([__DIR__ . '/data/logger-in-catch.php'], [
            [$message, 41],
            [$message, 49],
            [$message, 58],
        ]);
    }
}
