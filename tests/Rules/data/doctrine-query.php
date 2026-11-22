<?php

namespace TournayreLabs\PHPStanRules\Tests\Rules\data {
    use Doctrine\ORM\AbstractQuery;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\EntityRepository;
    use Doctrine\Persistence\ObjectRepository;

    final class UserRepository extends EntityRepository
    {
        public function getByEmail(string $email): object
        {
            return $this->findOneBy(['email' => $email]) ?? throw new \RuntimeException('Not found');
        }

        public function countActive(): int
        {
            return count($this->createQueryBuilder('u')->getQuery()->getResult());
        }
    }

    final class AllowedQueryReader
    {
        public function __construct(private ObjectRepository $repository)
        {
        }

        public function read(): ?object
        {
            return $this->repository->findOneBy([]);
        }
    }

    final class ForbiddenQueryCaller
    {
        public function __construct(
            private ObjectRepository $repository,
            private EntityRepository $entityRepository,
            private UserRepository $users,
            private EntityManagerInterface $em,
            private AbstractQuery $query,
        ) {
        }

        public function nativeRepository(): void
        {
            $this->repository->find(1);
            $this->repository->findBy([]);
        }

        public function nativeEntityRepository(): void
        {
            $this->entityRepository->count([]);
            $this->entityRepository->findByName('foo');
        }

        public function inheritedOnApplicationRepository(): void
        {
            $this->users->findBy([]);
        }

        public function businessMethodIsFine(): object
        {
            return $this->users->getByEmail('a@b.c');
        }

        public function entityManager(): void
        {
            $this->em->getRepository(\stdClass::class);
            $this->em->createQuery('SELECT 1');
        }

        public function queryExecution(): void
        {
            $this->query->getResult();
        }

        public function nullsafe(?ObjectRepository $repository): void
        {
            $repository?->findAll();
        }

        public function insideClosure(): void
        {
            $fn = fn () => $this->repository->findAll();
            $fn();
        }
    }

    final class NotDoctrine
    {
        public function findBy(): void
        {
        }

        public function run(): void
        {
            $this->findBy();
            (new self())->findBy();
        }
    }
}
