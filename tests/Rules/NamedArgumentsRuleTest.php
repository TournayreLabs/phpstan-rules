<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\NamedArgumentsRule;

/**
 * @extends RuleTestCase<NamedArgumentsRule>
 */
final class NamedArgumentsRuleTest extends RuleTestCase
{
    private const DATA = 'TournayreLabs\PHPStanRules\Tests\Rules\data\NamedArguments\\';

    /** @var list<string> */
    private array $excludedPaths = [];

    protected function getRule(): Rule
    {
        return new NamedArgumentsRule(self::createReflectionProvider(), $this->excludedPaths);
    }

    public function testRule(): void
    {
        $this->analyse(
            [__DIR__ . '/data/named-arguments.php'],
            [
                [$this->message('Subject::__construct', 2, 'first: …, second: …'), 38],
                [$this->message('sum', 2, 'first: …, second: …'), 50],
                [$this->message('sum', 2, 'first: …, second: …'), 51],
                [$this->message('Subject::join', 2, 'left: …, right: …'), 62],
                [$this->message('Subject::join', 2, 'left: …, right: …'), 63],
                [$this->message('Subject::join', 2, 'left: …, right: …'), 66],
                [$this->message('Subject::make', 2, 'first: …, second: …'), 68],
                [$this->message('Subject::__construct', 2, 'first: …, second: …'), 69],
            ]
        );
    }

    public function testExcludedPathIsSkipped(): void
    {
        $this->excludedPaths = [__DIR__ . '/data/named-arguments-excluded'];

        $this->analyse([__DIR__ . '/data/named-arguments-excluded/excluded.php'], []);
    }

    public function testNotExcludedPathIsReported(): void
    {
        $this->excludedPaths = [__DIR__ . '/data/other'];

        $this->analyse(
            [__DIR__ . '/data/named-arguments-excluded/excluded.php'],
            [
                [
                    'Call to TournayreLabs\PHPStanRules\Tests\Rules\data\NamedArgumentsExcluded\pair()'
                    . ' with 2 arguments, at least one of them positional:'
                    . ' use PHP 8 named arguments (first: …, second: …).',
                    12,
                ],
            ]
        );
    }

    private function message(string $callee, int $count, string $suggestion): string
    {
        return sprintf(
            'Call to %s%s() with %d arguments, at least one of them positional:'
            . ' use PHP 8 named arguments (%s).',
            self::DATA,
            $callee,
            $count,
            $suggestion
        );
    }
}
