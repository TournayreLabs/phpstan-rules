<?php

declare(strict_types=1);

function noNamespaceCheck(object $value): bool
{
    return $value instanceof \Vendor\Forbidden\Foo;
}
