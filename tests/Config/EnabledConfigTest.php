<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Config;

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
}
