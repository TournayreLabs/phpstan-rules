<?php

namespace TournayreLabs\PHPStanRules\Tests\Rules\data {
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\EntityRepository;

    final class LegacyUserRepository extends EntityRepository
    {
    }

    final class ServiceBackedRepository extends ServiceEntityRepository
    {
    }

    final class CompositionRepository
    {
        public function __construct(private EntityManagerInterface $em)
        {
        }
    }
}
