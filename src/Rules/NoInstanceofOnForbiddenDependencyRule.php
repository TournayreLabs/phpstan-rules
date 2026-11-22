<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Complements namespace-boundary tools that see imports ("use") but not an "instanceof"
 * written with an inline fully qualified name ("$x instanceof \Vendor\Foo").
 *
 * @implements Rule<Instanceof_>
 */
final class NoInstanceofOnForbiddenDependencyRule implements Rule
{
    /**
     * @param list<array{dependency: string, denyFrom: string, reason: string}> $restrictions
     *        "dependency" is the forbidden namespace, "denyFrom" the namespace that must not use it,
     *        both ending with a backslash
     * @param list<string> $excludedPaths A file is skipped when its path starts with one of these entries
     */
    public function __construct(
        private readonly array $restrictions = [],
        private readonly array $excludedPaths = [],
    ) {
    }

    public function getNodeType(): string
    {
        return Instanceof_::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->class;
        $namespace = $scope->getNamespace();
        if (!$class instanceof Name || $namespace === null || $this->isExcluded($scope->getFile())) {
            return [];
        }

        if (!$class->getAttribute('originalName') instanceof FullyQualified) {
            return [];
        }

        $resolved = $class->toString();
        $errors = [];
        foreach ($this->restrictions as $restriction) {
            if (
                !$this->startsWith($resolved, $restriction['dependency'])
                || !$this->startsWith($namespace . '\\', $restriction['denyFrom'])
            ) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf('instanceof %s is forbidden in %s: %s.', $resolved, $namespace, $restriction['reason'])
            )
                ->identifier('tournayreLabs.noInstanceofOnForbiddenDependency')
                ->build();
        }

        return $errors;
    }

    private function isExcluded(string $file): bool
    {
        foreach ($this->excludedPaths as $path) {
            if ($path !== '' && str_starts_with($file, $path)) {
                return true;
            }
        }

        return false;
    }

    private function startsWith(string $name, string $prefix): bool
    {
        return str_starts_with(strtolower($name), strtolower($prefix));
    }
}
