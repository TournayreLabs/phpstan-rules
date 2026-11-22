<?php

declare(strict_types=1);

namespace App\Domain\Excluded;

final class ExcludedCheck
{
    public function check(object $value): bool
    {
        return $value instanceof \Vendor\Forbidden\Foo;
    }
}
