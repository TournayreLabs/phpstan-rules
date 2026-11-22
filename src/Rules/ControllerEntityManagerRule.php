<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * A controller must not depend on "Doctrine\ORM\EntityManagerInterface" (or a subtype):
 * constructor / method parameters (promoted properties and action arguments included) and property types.
 * A controller is a class matching a name pattern, extending "AbstractController" or carrying "#[AsController]".
 *
 * @implements Rule<InClassNode>
 */
final class ControllerEntityManagerRule implements Rule
{
    private const ENTITY_MANAGER = 'Doctrine\ORM\EntityManagerInterface';
    private const ABSTRACT_CONTROLLER = 'Symfony\Bundle\FrameworkBundle\Controller\AbstractController';
    private const AS_CONTROLLER = 'Symfony\Component\HttpKernel\Attribute\AsController';

    /**
     * @param list<string> $controllerClasses Class name patterns ("*" wildcard allowed) identifying controllers
     */
    public function __construct(private readonly array $controllerClasses)
    {
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getOriginalNode();
        if (!$class instanceof Class_) {
            return [];
        }

        $reflection = $node->getClassReflection();
        if (!$this->isController($class, $reflection->getName(), $reflection->is(self::ABSTRACT_CONTROLLER))) {
            return [];
        }

        $errors = [];
        foreach ($class->stmts as $statement) {
            foreach ($this->typesOf($statement) as $type) {
                if (!$this->isEntityManager($type)) {
                    continue;
                }

                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'Controller %s must not depend on %s: delegate to a service or a repository.',
                        $reflection->getName(),
                        self::ENTITY_MANAGER
                    )
                )
                    ->identifier('tournayreLabs.controllerEntityManager')
                    ->line($type->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    private function isController(Class_ $class, string $name, bool $extendsAbstractController): bool
    {
        if ($extendsAbstractController) {
            return true;
        }

        foreach ($this->controllerClasses as $pattern) {
            if (fnmatch($pattern, $name, FNM_NOESCAPE)) {
                return true;
            }
        }

        foreach ($class->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                if (strcasecmp($attribute->name->toString(), self::AS_CONTROLLER) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return list<Name|Identifier|ComplexType>
     */
    private function typesOf(Node $statement): array
    {
        if ($statement instanceof Property && $statement->type !== null) {
            return [$statement->type];
        }

        if (!$statement instanceof ClassMethod) {
            return [];
        }

        $types = [];
        foreach ($statement->params as $param) {
            if ($param->type !== null) {
                $types[] = $param->type;
            }
        }

        return $types;
    }

    private function isEntityManager(Name|Identifier|ComplexType $type): bool
    {
        if ($type instanceof NullableType) {
            return $this->isEntityManager($type->type);
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            foreach ($type->types as $member) {
                if ($this->isEntityManager($member)) {
                    return true;
                }
            }

            return false;
        }

        return $type instanceof Name
            && (new ObjectType(self::ENTITY_MANAGER))->isSuperTypeOf(new ObjectType($type->toString()))->yes();
    }
}
