<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Config;

use TournayreLabs\PHPStanRules\Rules\DoctrinePersistenceCallRule;
use TournayreLabs\PHPStanRules\Rules\DoctrineQueryCallRule;
use TournayreLabs\PHPStanRules\Rules\DoctrineRepositoryNoInheritanceRule;
use TournayreLabs\PHPStanRules\Rules\InterfaceNameSuffixRule;
use TournayreLabs\PHPStanRules\Rules\SymfonyAutowireEnvRule;
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

    protected function symfonyAutowireEnvRule(): string
    {
        return SymfonyAutowireEnvRule::class;
    }

    /**
     * @return list<class-string>
     */
    protected function doctrineRules(): array
    {
        return [
            DoctrineQueryCallRule::class,
            DoctrinePersistenceCallRule::class,
            DoctrineRepositoryNoInheritanceRule::class,
        ];
    }
}
