<?php

namespace Doctrine\Persistence {
    interface ObjectRepository
    {
        public function find(mixed $id): ?object;

        /** @return list<object> */
        public function findAll(): array;

        /**
         * @param array<string, mixed> $criteria
         * @return list<object>
         */
        public function findBy(array $criteria): array;

        /** @param array<string, mixed> $criteria */
        public function findOneBy(array $criteria): ?object;
    }

    interface ObjectManager
    {
        public function find(string $className, mixed $id): ?object;

        public function persist(object $object): void;

        public function remove(object $object): void;

        public function flush(): void;

        public function refresh(object $object): void;

        public function detach(object $object): void;

        public function clear(): void;

        /** @param class-string $className */
        public function getRepository(string $className): ObjectRepository;
    }
}

namespace Doctrine\ORM {
    use Doctrine\Persistence\ObjectManager;
    use Doctrine\Persistence\ObjectRepository;

    class EntityRepository implements ObjectRepository
    {
        public function find(mixed $id): ?object
        {
            return null;
        }

        public function findAll(): array
        {
            return [];
        }

        public function findBy(array $criteria): array
        {
            return [];
        }

        public function findOneBy(array $criteria): ?object
        {
            return null;
        }

        public function count(array $criteria = []): int
        {
            return 0;
        }

        public function createQueryBuilder(string $alias): QueryBuilder
        {
            return new QueryBuilder();
        }

        /** @param array<mixed> $arguments */
        public function __call(string $method, array $arguments): mixed
        {
            return null;
        }
    }

    interface EntityManagerInterface extends ObjectManager
    {
        public function createQuery(string $dql): AbstractQuery;

        public function createQueryBuilder(): QueryBuilder;

        public function getReference(string $entityName, mixed $id): ?object;

        public function wrapInTransaction(callable $func): mixed;

        public function beginTransaction(): void;

        public function commit(): void;

        public function rollback(): void;
    }

    class QueryBuilder
    {
        public function getQuery(): AbstractQuery
        {
            return new AbstractQuery();
        }
    }

    class AbstractQuery
    {
        /** @return list<mixed> */
        public function getResult(): array
        {
            return [];
        }

        public function getSingleResult(): mixed
        {
            return null;
        }

        public function getOneOrNullResult(): mixed
        {
            return null;
        }

        public function getSingleScalarResult(): mixed
        {
            return null;
        }
    }
}

namespace Doctrine\Bundle\DoctrineBundle\Repository {
    use Doctrine\ORM\EntityRepository;

    class ServiceEntityRepository extends EntityRepository
    {
    }
}
