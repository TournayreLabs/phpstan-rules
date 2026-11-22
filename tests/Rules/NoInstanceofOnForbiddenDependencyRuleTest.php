<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\NoInstanceofOnForbiddenDependencyRule;

/**
 * @extends RuleTestCase<NoInstanceofOnForbiddenDependencyRule>
 */
final class NoInstanceofOnForbiddenDependencyRuleTest extends RuleTestCase
{
    /** @var list<string> */
    private array $excludedPaths = [];

    protected function getRule(): Rule
    {
        return new NoInstanceofOnForbiddenDependencyRule(
            [
                ['dependency' => 'Vendor\Forbidden\\', 'denyFrom' => 'App\Domain\\', 'reason' => 'reason one'],
                ['dependency' => 'Vendor\\', 'denyFrom' => 'App\Domain\\', 'reason' => 'reason two'],
            ],
            $this->excludedPaths
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [__DIR__ . '/data/instanceof-forbidden.php'],
            [
                [$this->message('reason one'), 18],
                [$this->message('reason two'), 18],
                [$this->message('reason one'), 28],
                [$this->message('reason two'), 28],
            ]
        );
    }

    public function testFileWithoutNamespaceIsSkipped(): void
    {
        $this->analyse([__DIR__ . '/data/instanceof-no-namespace.php'], []);
    }

    public function testExcludedPathIsSkipped(): void
    {
        $this->excludedPaths = [__DIR__ . '/data/instanceof-excluded'];

        $this->analyse([__DIR__ . '/data/instanceof-excluded/excluded.php'], []);
    }

    public function testNotExcludedPathIsReported(): void
    {
        $this->excludedPaths = [__DIR__ . '/data/other'];

        $this->analyse(
            [__DIR__ . '/data/instanceof-excluded/excluded.php'],
            [
                [
                    'instanceof Vendor\Forbidden\Foo is forbidden in App\Domain\Excluded: reason one.',
                    11,
                ],
                [
                    'instanceof Vendor\Forbidden\Foo is forbidden in App\Domain\Excluded: reason two.',
                    11,
                ],
            ]
        );
    }

    private function message(string $reason): string
    {
        return sprintf('instanceof Vendor\Forbidden\Foo is forbidden in App\Domain: %s.', $reason);
    }
}
