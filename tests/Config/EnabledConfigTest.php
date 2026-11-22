<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Config;

use TournayreLabs\PHPStanRules\Rules\ControllerEntityManagerRule;
use TournayreLabs\PHPStanRules\Rules\NamedArgumentsRule;
use TournayreLabs\PHPStanRules\Rules\NoInstanceofOnForbiddenDependencyRule;

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

    public function testNoInstanceofOnForbiddenDependencyRuleCanBeEnabled(): void
    {
        self::assertContains(NoInstanceofOnForbiddenDependencyRule::class, $this->registeredRuleClasses());
    }

    public function testForbiddenInterfaceRuleIsConfiguredSeveralTimes(): void
    {
        $parameters = self::getContainer()->getParameter('tournayreLabsPhpstanRules');
        self::assertIsArray($parameters);
        $configurations = $parameters['forbiddenInterfaces'];
        self::assertIsArray($configurations);
        self::assertCount(2, $configurations);
        self::assertIsArray($configurations[1]);
        self::assertSame('clock', $configurations[1]['preset']);
        self::assertContains($this->forbiddenInterfaceRule(), $this->registeredRuleClasses());
    }
}
