<?php

namespace TournayreLabs\PHPStanRules\Tests\Rules\data {
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\EntityRepository;

    final class OrderRepository extends EntityRepository
    {
        public function __construct(private EntityManagerInterface $em)
        {
        }

        public function save(object $order): void
        {
            $this->em->persist($order);
            $this->em->flush();
        }
    }

    final class AllowedPersister
    {
        public function __construct(private EntityManagerInterface $em)
        {
        }

        public function save(object $entity): void
        {
            $this->em->persist($entity);
            $this->em->flush();
        }
    }

    final class ForbiddenPersistenceCaller
    {
        public function __construct(private EntityManagerInterface $em)
        {
        }

        public function write(object $entity): void
        {
            $this->em->persist($entity);
            $this->em->remove($entity);
            $this->em->flush();
        }

        public function transaction(): void
        {
            $this->em->beginTransaction();
            $this->em->commit();
        }

        public function readsAreNotPersistence(): void
        {
            $this->em->find(\stdClass::class, 1);
        }
    }
}
