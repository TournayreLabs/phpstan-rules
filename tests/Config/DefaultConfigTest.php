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
}
