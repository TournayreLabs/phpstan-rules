<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules\data\NamedArguments;

function sum(int $first, int $second): int
{
    return $first + $second;
}

function collect(int $first, int ...$rest): int
{
    return $first + array_sum($rest);
}

/**
 * @no-named-arguments
 */
function positionalOnly(int $first, int $second): int
{
    return $first + $second;
}

final class Subject
{
    public function __construct(public readonly int $first = 0, public readonly int $second = 0)
    {
    }

    public function join(string $left, string $right): string
    {
        return $left . $right;
    }

    public static function make(int $first, int $second): self
    {
        return new self($first, $second);
    }

    public function __call(string $name, array $arguments): void
    {
    }
}

final class Calls
{
    public function run(Subject $subject, ?Subject $maybe): void
    {
        sum(1, 2);
        sum(1, second: 2);
        sum(first: 1, second: 2);
        sum(1);
        sum(...[1, 2]);
        $callable = sum(...);
        collect(1, 2, 3);
        positionalOnly(1, 2);
        str_replace('a', 'b', 'abc');
        $name = 'sum';
        $name(1, 2);

        $subject->join('a', 'b');
        $subject->join('a', right: 'b');
        $subject->{'join'}('a', 'b');
        $subject->undefinedMagic('a', 'b');
        $maybe?->join('a', 'b');

        Subject::make(1, 2);
        new Subject(1, 2);
        new Subject(first: 1, second: 2);
    }
}
