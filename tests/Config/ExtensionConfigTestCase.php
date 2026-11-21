<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Config;

use TournayreLabs\PHPStanRules\Rules\InterfaceNameSuffixRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\PHPStanTestCase;

abstract class ExtensionConfigTestCase extends PHPStanTestCase
{
    /**
     * @return list<class-string>
     */
    protected function registeredRuleClasses(): array
    {
        $classes = [];
        foreach (self::getContainer()->getServicesByTag('phpstan.rules.rule') as $service) {
            if ($service instanceof Rule) {
                $classes[] = $service::class;
            }
        }

        return $classes;
    }

    protected function interfaceRule(): string
    {
        return InterfaceNameSuffixRule::class;
    }
}
