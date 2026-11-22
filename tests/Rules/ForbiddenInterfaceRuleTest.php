<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\ForbiddenInterfaceRule;

/**
 * @extends RuleTestCase<ForbiddenInterfaceRule>
 */
final class ForbiddenInterfaceRuleTest extends RuleTestCase
{
    private const BASE = 'TournayreLabs\PHPStanRules\Tests\Rules\data\Forbidden\\';

    protected function getRule(): Rule
    {
        return new ForbiddenInterfaceRule([
            [
                'namespaces' => [self::BASE . 'Domain\*'],
                'interfaces' => [self::BASE . 'Domain\Foo'],
            ],
            [
                'namespaces' => [self::BASE . 'Domain\*', self::BASE . 'Ui\*'],
                'interfaces' => [self::BASE . 'Domain\Bar'],
                'message' => 'Use composition.',
            ],
            [
                'namespaces' => [self::BASE . 'Domain\*'],
                'preset' => 'clock',
            ],
        ]);
    }

    public function testRule(): void
    {
        $domain = self::BASE . 'Domain\\';

        $this->analyse([__DIR__ . '/data/clock-stubs.php', __DIR__ . '/data/forbidden-interfaces.php'], [
            [$this->message('Class', $domain . 'ImplementsFoo', 'implement', $domain . 'Foo'), 12],
            [$this->message('Class', $domain . 'ImplementsBoth', 'implement', $domain . 'Foo'), 16],
            [
                $this->message('Class', $domain . 'ImplementsBar', 'implement', $domain . 'Bar')
                    . "\n    💡 Use composition.",
                20,
            ],
            [$this->message('Interface', $domain . 'ExtendsFoo', 'extend', $domain . 'Foo'), 24],
            [$this->message('Enum', $domain . 'EnumImplementsFoo', 'implement', $domain . 'Foo'), 28],
            [$this->message('Class', $domain . 'ImplementsClockPsr', 'implement', 'Psr\Clock\ClockInterface'), 33],
            [
                $this->message(
                    'Class',
                    $domain . 'ImplementsClockSymfony',
                    'implement',
                    'Symfony\Component\Clock\ClockInterface'
                ),
                41,
            ],
            [
                $this->message('Class', self::BASE . 'Ui\ImplementsFooAndBar', 'implement', $domain . 'Bar')
                    . "\n    💡 Use composition.",
                71,
            ],
        ]);
    }

    public function testUnknownPresetIsRejected(): void
    {
        $this->expectException(\PHPStan\ShouldNotHappenException::class);

        new ForbiddenInterfaceRule([['namespaces' => ['App\*'], 'preset' => 'nope']]);
    }

    private function message(string $kind, string $class, string $verb, string $interface): string
    {
        return sprintf('%s %s must not %s forbidden interface %s.', $kind, $class, $verb, $interface);
    }
}
