<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\ControllerEntityManagerRule;

/**
 * @extends RuleTestCase<ControllerEntityManagerRule>
 */
final class ControllerEntityManagerRuleTest extends RuleTestCase
{
    private const DATA = 'TournayreLabs\PHPStanRules\Tests\Rules\data\\';

    protected function getRule(): Rule
    {
        return new ControllerEntityManagerRule(['*\Controller\*', '*Controller']);
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/data/doctrine-stubs.php',
                __DIR__ . '/data/symfony-stubs.php',
                __DIR__ . '/data/controller-entity-manager.php',
            ],
            [
                [$this->message('Controller\ConstructorController'), 14],
                [$this->message('Controller\ActionArgumentController'), 21],
                [$this->message('Controller\PropertyController'), 28],
                [$this->message('Controller\SubtypeController'), 33],
                [$this->message('Controller\UnionController'), 40],
                [$this->message('SuffixedController'), 60],
                [$this->message('ExtendingAbstractController'), 67],
                [$this->message('AttributedAction'), 75],
            ]
        );
    }

    private function message(string $class): string
    {
        return sprintf(
            'Controller %s must not depend on Doctrine\ORM\EntityManagerInterface:'
            . ' delegate to a service or a repository.',
            self::DATA . $class
        );
    }
}
