<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TournayreLabs\PHPStanRules\Rules\SymfonyAutowireEnvRule;

/**
 * @extends RuleTestCase<SymfonyAutowireEnvRule>
 */
final class SymfonyAutowireEnvRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SymfonyAutowireEnvRule(['TournayreLabs\PHPStanRules\Tests\Rules\data\Allowed*']);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/symfony-stubs.php', __DIR__ . '/data/symfony-autowire-env.php'], [
            [$this->message('EnvAutowiredService', '__construct', 'dsn'), 9],
            [$this->message('EnvAutowiredService', '__construct', 'secret'), 11],
            [$this->message('EnvAutowiredService', '__construct', 'port'), 13],
            [$this->message('EnvAutowiredController', '__invoke', 'env'), 37],
        ]);
    }

    private function message(string $class, string $method, string $parameter): string
    {
        return sprintf(
            'Parameter $%s of %s::%s() must not be autowired from an environment variable'
            . ' (#[Autowire] with env): inject a configuration object instead.',
            $parameter,
            'TournayreLabs\PHPStanRules\Tests\Rules\data\\' . $class,
            $method
        );
    }
}
