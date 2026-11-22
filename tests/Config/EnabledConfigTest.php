<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Config;

use TournayreLabs\PHPStanRules\Rules\ControllerEntityManagerRule;
use TournayreLabs\PHPStanRules\Rules\NamedArgumentsRule;

final class EnabledConfigTest extends ExtensionConfigTestCase
{
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../extension.neon',
            __DIR__ . '/enabled.neon',
        ];
    }

    public function testRuleCanBeEnabled(): void
    {
        self::assertContains($this->interfaceRule(), $this->registeredRuleClasses());
    }

    public function testControllerEntityManagerRuleCanBeEnabled(): void
    {
        self::assertContains(ControllerEntityManagerRule::class, $this->registeredRuleClasses());
    }

    public function testSymfonyAutowireEnvRuleCanBeEnabled(): void
    {
        self::assertContains($this->symfonyAutowireEnvRule(), $this->registeredRuleClasses());
    }

    public function testDoctrineRulesCanBeEnabled(): void
    {
        foreach ($this->doctrineRules() as $rule) {
            self::assertContains($rule, $this->registeredRuleClasses());
        }
    }

    public function testNamedArgumentsRuleCanBeEnabled(): void
    {
        self::assertContains(NamedArgumentsRule::class, $this->registeredRuleClasses());
    }
}
