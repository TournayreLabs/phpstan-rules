<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Config;

final class DefaultConfigTest extends ExtensionConfigTestCase
{
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon'];
    }

    public function testRulesAreDisabledByDefault(): void
    {
        self::assertNotContains($this->interfaceRule(), $this->registeredRuleClasses());
    }

    public function testSymfonyAutowireEnvRuleIsDisabledByDefault(): void
    {
        self::assertNotContains($this->symfonyAutowireEnvRule(), $this->registeredRuleClasses());
    }

    public function testDoctrineRulesAreDisabledByDefault(): void
    {
        foreach ($this->doctrineRules() as $rule) {
            self::assertNotContains($rule, $this->registeredRuleClasses());
        }
    }
}
