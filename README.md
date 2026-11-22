# tournayrelabs/phpstan-rules

**Opt-in** PHPStan rules: install them anywhere, enable only the ones you want through the project configuration.

## Installation

```bash
composer require --dev tournayrelabs/phpstan-rules
```

- With [`phpstan/extension-installer`](https://github.com/phpstan/phpstan-extension-installer): `extension.neon` is included automatically.
- Without it, add this to `phpstan.neon`:

```neon
includes:
    - vendor/tournayrelabs/phpstan-rules/extension.neon
```

## Enabling rules

All rules are **disabled by default**. Enable the ones you need:

```neon
parameters:
    tournayreLabsPhpstanRules:
        interfaceNameSuffix: true
        loggerInCatch: true
        doctrineQueryCall: true
        doctrineQueryAllowedClasses: ['App\Service\Search\*']
        doctrinePersistenceCall: true
        doctrinePersistenceAllowedClasses: ['App\Service\UnitOfWork']
        doctrineAllowObjectRepositories: true
        doctrineRepositoryNoInheritance: false
        symfonyAutowireEnv: true
        symfonyAutowireEnvAllowedClasses: ['App\Config\*']
        controllerEntityManager: true
        controllerEntityManagerControllerClasses: ['*\Controller\*', '*Controller']
```

## Rules

| Parameter             | Identifier                       | Description                              |
|-----------------------|----------------------------------|------------------------------------------|
| `interfaceNameSuffix` | `tournayreLabs.interfaceNameSuffix` | Interfaces must end with `Interface`     |
| `loggerInCatch`       | `tournayreLabs.loggerInCatch`       | A `catch` without `throw` must call a `Psr\Log\LoggerInterface` |
| `doctrineQueryCall`   | `tournayreLabs.doctrineQueryCall`   | Native Doctrine query methods only in repositories / allowed services |
| `doctrinePersistenceCall` | `tournayreLabs.doctrinePersistenceCall` | Native Doctrine persistence methods only in repositories / allowed services |
| `doctrineRepositoryNoInheritance` | `tournayreLabs.doctrineRepositoryNoInheritance` | Repositories must not extend `EntityRepository` |
| `symfonyAutowireEnv` | `tournayreLabs.symfonyAutowireEnv` | No `#[Autowire(env: ...)]`: use configuration objects |
| `controllerEntityManager` | `tournayreLabs.controllerEntityManager` | Controllers must not depend on `Doctrine\ORM\EntityManagerInterface` |

### Symfony rules

- `symfonyAutowireEnv` (`tournayreLabs.symfonyAutowireEnv`): a method/constructor parameter must not use `#[Autowire(env: '...')]` (nor `#[Autowire('%env(...)%')]` / `value: '%env(...)%'`) — controllers, services, actions. Inject a configuration object instead.
- `symfonyAutowireEnvAllowedClasses`: class name patterns (`*` wildcard) of the configuration objects allowed to read env variables (e.g. `App\Config\*`). Without it, no class is exempt.
- Not covered: `#[Autowire(expression: "env('X')")]`, `#[Autowire(param: ...)]` pointing to a parameter built from an env variable.

- `controllerEntityManager` (`tournayreLabs.controllerEntityManager`): a controller must not use `Doctrine\ORM\EntityManagerInterface` (or a subtype such as `EntityManager`) as a constructor / method parameter type (promoted properties and action arguments included) or a property type. Delegate to a service or a repository.
- `controllerEntityManagerControllerClasses`: class name patterns (`*` wildcard) identifying controllers. Default `['*\Controller\*', '*Controller']`. A class extending `Symfony\Bundle\FrameworkBundle\Controller\AbstractController` or carrying `#[Symfony\Component\HttpKernel\Attribute\AsController]` is a controller too, whatever its name.
- Not covered: `$this->container->get(EntityManagerInterface::class)`, types declared only in PHPDoc.

### Doctrine rules

Force the rest of the code through business methods (returning a null object or throwing when nothing is found).

- `doctrineQueryCall` (`tournayreLabs.doctrineQueryCall`): `find*`, `count`, `createQueryBuilder`, `getRepository`, `createQuery`, query `getResult()`/`getOneOrNullResult()`... — also on application repositories called from outside.
- `doctrinePersistenceCall` (`tournayreLabs.doctrinePersistenceCall`): `persist`, `remove`, `flush`, `refresh`, `detach`, `clear`, transactions.
- Allowed callers: any class implementing `Doctrine\Persistence\ObjectRepository` (so `EntityRepository`/`ServiceEntityRepository` subclasses), plus the class name patterns (`*` wildcard) of `doctrineQueryAllowedClasses` / `doctrinePersistenceAllowedClasses`.
- `doctrineAllowObjectRepositories: false` (default `true`): only the class name patterns define the allowed callers (DDD, e.g. `App\Infrastructure\Persistence\*`). Applies to both call rules.
- `doctrineRepositoryNoInheritance` (`tournayreLabs.doctrineRepositoryNoInheritance`): a class must not extend `Doctrine\ORM\EntityRepository` (so neither `ServiceEntityRepository`); inject the `EntityManager` instead. Use it with `doctrineAllowObjectRepositories: false`, otherwise composition-based repositories are not recognised as allowed callers.
- Limitation: a repository extending `EntityRepository` keeps its native methods public (PHP cannot narrow visibility), so without `doctrineRepositoryNoInheritance` they are only caught at call sites outside allowed callers.

## Adding a rule

1. Create a `final` class in `src/Rules/` implementing `PHPStan\Rules\Rule`, with an `identifier()`.
2. In `extension.neon`:
   - add a parameter defaulting to `false` and its entry in `parametersSchema`
   - add the service and a `conditionalTags` entry bound to that parameter
3. Add a `RuleTestCase` test in `tests/Rules/` (fixture in `tests/Rules/data/`).
4. Add a row to the table above.

## Development

```bash
make install
make run-all-tests-php
```
