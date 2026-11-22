<?php

declare(strict_types=1);

namespace TournayreLabs\PHPStanRules\Tests\Rules\data\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

final class ConstructorController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
}

final class ActionArgumentController
{
    public function __invoke(EntityManagerInterface $entityManager): void
    {
    }
}

final class PropertyController
{
    private ?EntityManagerInterface $entityManager = null;
}

final class SubtypeController
{
    public function __construct(EntityManager $entityManager)
    {
    }
}

final class UnionController
{
    public function show(int|EntityManagerInterface $entityManager): void
    {
    }
}

final class CleanController
{
    public function __construct(private \stdClass $service)
    {
    }
}

namespace TournayreLabs\PHPStanRules\Tests\Rules\data;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

final class SuffixedController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
    }
}

final class ExtendingAbstractController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
    }
}

#[AsController]
final class AttributedAction
{
    public function __invoke(EntityManagerInterface $entityManager): void
    {
    }
}

final class OrdinaryService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
}
