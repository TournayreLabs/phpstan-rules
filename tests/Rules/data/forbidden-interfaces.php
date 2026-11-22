<?php

namespace TournayreLabs\PHPStanRules\Tests\Rules\data\Forbidden\Domain {
    interface Foo
    {
    }

    interface Bar
    {
    }

    final class ImplementsFoo implements Foo
    {
    }

    final class ImplementsBoth implements Foo, \Countable
    {
    }

    final class ImplementsBar implements Bar
    {
    }

    interface ExtendsFoo extends Foo
    {
    }

    enum EnumImplementsFoo implements Foo
    {
        case A;
    }

    final class ImplementsClockPsr implements \Psr\Clock\ClockInterface
    {
        public function now(): \DateTimeImmutable
        {
            return new \DateTimeImmutable();
        }
    }

    final class ImplementsClockSymfony implements \Symfony\Component\Clock\ClockInterface
    {
        public function now(): \DateTimeImmutable
        {
            return new \DateTimeImmutable();
        }
    }

    final class ChildOfForbidden extends ImplementsFoo
    {
    }
}

namespace TournayreLabs\PHPStanRules\Tests\Rules\data\Forbidden\Infrastructure {
    final class AllowedImplementsFoo implements \TournayreLabs\PHPStanRules\Tests\Rules\data\Forbidden\Domain\Foo
    {
    }

    final class AllowedImplementsClock implements \Psr\Clock\ClockInterface
    {
        public function now(): \DateTimeImmutable
        {
            return new \DateTimeImmutable();
        }
    }
}

namespace TournayreLabs\PHPStanRules\Tests\Rules\data\Forbidden\Ui {
    final class ImplementsFooAndBar implements
        \TournayreLabs\PHPStanRules\Tests\Rules\data\Forbidden\Domain\Foo,
        \TournayreLabs\PHPStanRules\Tests\Rules\data\Forbidden\Domain\Bar
    {
    }
}
