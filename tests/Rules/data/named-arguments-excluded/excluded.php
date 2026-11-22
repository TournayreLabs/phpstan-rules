<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules\data\NamedArgumentsExcluded;

function pair(int $first, int $second): int
{
    return $first + $second;
}

pair(1, 2);
