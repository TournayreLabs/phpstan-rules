<?php

declare(strict_types=1);

namespace Vendor\Forbidden {
    final class Foo
    {
    }
}

namespace App\Domain {
    use Vendor\Forbidden\Foo;

    final class Denied
    {
        public function check(object $value): bool
        {
            return $value instanceof \Vendor\Forbidden\Foo;
        }

        public function imported(object $value): bool
        {
            return $value instanceof Foo;
        }

        public function twice(object $value): bool
        {
            return $value instanceof \Vendor\Forbidden\Foo && $value instanceof \App\Shared\Bar;
        }
    }
}

namespace App\Shared {
    final class Bar
    {
    }
}

namespace App\Infrastructure {
    final class Allowed
    {
        public function check(object $value): bool
        {
            return $value instanceof \Vendor\Forbidden\Foo;
        }
    }
}
