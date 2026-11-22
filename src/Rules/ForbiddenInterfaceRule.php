<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Forbids classes, enums and interfaces of given namespaces from declaring given interfaces
 * ("implements" / interface "extends"). Configurable any number of times.
 *
 * Only directly declared interfaces are reported: an interface inherited from a parent class
 * is the parent's responsibility.
 *
 * @phpstan-type Config array{namespaces: list<string>, interfaces?: list<string>, preset?: string, message?: string}
 *
 * @implements Rule<InClassNode>
 */
final class ForbiddenInterfaceRule implements Rule
{
    /**
     * Ready-made interface lists, usable through the "preset" key of a configuration.
     *
     * @var array<string, list<string>>
     */
    public const PRESETS = [
        'clock' => [
            'Psr\Clock\ClockInterface',
            'Symfony\Component\Clock\ClockInterface',
        ],
    ];

    /**
     * @param list<Config> $configurations Each one: namespaces ("*" wildcard) x interfaces ("*" wildcard)
     */
    public function __construct(
        private readonly array $configurations = [],
    ) {
        foreach ($configurations as $configuration) {
            $preset = $configuration['preset'] ?? null;
            if ($preset !== null && !isset(self::PRESETS[$preset])) {
                throw new ShouldNotHappenException(sprintf(
                    'Unknown forbiddenInterfaces preset "%s" (available: %s).',
                    $preset,
                    implode(', ', array_keys(self::PRESETS))
                ));
            }
        }
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->configurations === []) {
            return [];
        }

        $class = $node->getClassReflection();
        $original = $node->getOriginalNode();
        $errors = [];

        foreach ($this->declaredInterfaces($original) as $interface) {
            foreach ($this->configurations as $configuration) {
                if (
                    !$this->matchesAny($configuration['namespaces'], $class->getName())
                    || !$this->matchesAny($this->interfacesOf($configuration), $interface->toString())
                ) {
                    continue;
                }

                $builder = RuleErrorBuilder::message(sprintf(
                    '%s %s must not %s forbidden interface %s.',
                    $class->isInterface() ? 'Interface' : ($class->isEnum() ? 'Enum' : 'Class'),
                    $class->getDisplayName(),
                    $class->isInterface() ? 'extend' : 'implement',
                    $interface->toString()
                ))
                    ->identifier('tournayreLabs.forbiddenInterface')
                    ->line($interface->getStartLine());

                if (isset($configuration['message'])) {
                    $builder = $builder->tip($configuration['message']);
                }

                $errors[] = $builder->build();

                // One error per declared interface, even if several configurations match.
                continue 2;
            }
        }

        return $errors;
    }

    /**
     * @return list<Name>
     */
    private function declaredInterfaces(Node $original): array
    {
        if ($original instanceof Class_ || $original instanceof Enum_) {
            return array_values($original->implements);
        }

        if ($original instanceof Interface_) {
            return array_values($original->extends);
        }

        return [];
    }

    /**
     * @param Config $configuration
     * @return list<string>
     */
    private function interfacesOf(array $configuration): array
    {
        $preset = $configuration['preset'] ?? null;

        return [
            ...($configuration['interfaces'] ?? []),
            ...($preset !== null ? self::PRESETS[$preset] : []),
        ];
    }

    /**
     * @param list<string> $patterns
     */
    private function matchesAny(array $patterns, string $name): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch(strtolower($pattern), strtolower($name), FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }
}
