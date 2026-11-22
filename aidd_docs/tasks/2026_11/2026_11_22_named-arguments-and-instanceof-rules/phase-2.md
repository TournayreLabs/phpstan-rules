---
status: done
---

# Instruction: NoInstanceofOnForbiddenDependencyRule and final checks

## Architecture projection

> Tree of the final files from the project root, each folder ending in `/`. ✅ create · ✏️ modify · ❌ delete

```txt
.
├── extension.neon                                              ✏️
├── LICENSE                                                     ✅
├── README.md                                                   ✏️
├── src/
│   └── Rules/
│       └── NoInstanceofOnForbiddenDependencyRule.php           ✅
└── tests/
    ├── Config/
    │   ├── DefaultConfigTest.php                               ✏️
    │   ├── EnabledConfigTest.php                               ✏️
    │   └── enabled.neon                                        ✏️
    └── Rules/
        ├── NoInstanceofOnForbiddenDependencyRuleTest.php       ✅
        └── data/
            ├── instanceof-forbidden.php                        ✅
            └── instanceof-excluded/
                └── excluded.php                                ✅
```

## Test Scope

| Scenario     | Action                  | Observable outcome   | Actor                           |
| ------------ | ----------------------- | -------------------- | ------------------------------- |
| Setup        | Fixtures with a denied namespace, an allowed namespace, imported and no-namespace files | Fixtures analysable by `RuleTestCase` | system |
| Happy path   | `$x instanceof \Vendor\Foo` written fully qualified in a denied namespace | Error `instanceof %s is forbidden in %s: %s.`, identifier `tournayreLabs.noInstanceofOnForbiddenDependency` | system |
| Edge case    | Same code in an allowed namespace | No error | system |
| Edge case    | Imported name (`use` + `instanceof Foo`) | No error | system |
| Edge case    | Two restrictions matching | Two errors | system |
| Edge case    | Excluded path, or file without namespace | No error | system |
| Edge case    | Default config | Rule unregistered | system |

## Tasks to do

### `1)` Implement the rule

> Report `instanceof` on a fully qualified forbidden dependency.

1. Create `src/Rules/NoInstanceofOnForbiddenDependencyRule.php`: `final`, `@implements Rule<Instanceof_>`, constructor `(array $restrictions, array $excludedPaths)` with `list<array{dependency: string, denyFrom: string, reason: string}>`.
2. Report when `$node->class` is a `Name` whose `originalName` attribute is `FullyQualified`, the scope has a namespace, the file is not excluded, and for a restriction the resolved name starts with `dependency` and `<namespace>\` starts with `denyFrom`; one error per matching restriction.
3. Message `instanceof %s is forbidden in %s: %s.` (resolved class, current namespace, `reason`), identifier `tournayreLabs.noInstanceofOnForbiddenDependency`.

### `2)` Test the rule

> One fixture, one test class, covering the issue test cases.

1. Create `tests/Rules/data/instanceof-forbidden.php` (distinct namespaces for denied, allowed, imported, no-namespace; no class name collision with other fixtures) and `tests/Rules/data/instanceof-excluded/excluded.php`.
2. Create `tests/Rules/NoInstanceofOnForbiddenDependencyRuleTest.php` with two overlapping restrictions and an excluded-path run.

### `3)` Wire the configuration and docs

> Opt-in through `extension.neon`, documented in the README.

1. In `extension.neon`: parameters `noInstanceofOnForbiddenDependency: false`, `noInstanceofOnForbiddenDependencyRestrictions: []`, `noInstanceofOnForbiddenDependencyExcludedPaths: []`; schema `bool()`, `listOf(structure([dependency: string(), denyFrom: string(), reason: string()]))`, `listOf(string())`; service with arguments; `conditionalTags` entry; extend the header comment.
2. In `tests/Config/enabled.neon`: enable it with one restriction; add one test to `EnabledConfigTest` and one to `DefaultConfigTest`.
3. In `README.md`: enabling example, rules table row, description with the restriction format.

### `4)` Complete package metadata and run contributor checks

> The issue lists a LICENSE; composer.json already declares MIT.

1. Create `LICENSE` (MIT, `TournayreLabs`).
2. Run `make run-all-tests-php` (Makefile: phpstan then phpunit) and fix until green.

## Test acceptance criteria

| Task | Acceptance criteria              |
| ---- | -------------------------------- |
| 1 | `vendor/bin/phpstan analyse` passes on the new rule at level max |
| 2 | `vendor/bin/phpunit --filter NoInstanceofOnForbiddenDependencyRuleTest` passes; FQCN in denied namespace, allowed namespace, imported name, two restrictions, excluded path and no-namespace file each have an assertion |
| 3 | `vendor/bin/phpunit tests/Config` passes; rule absent by default, present when enabled; README table lists `noInstanceofOnForbiddenDependency` |
| 4 | `LICENSE` exists; `make run-all-tests-php` exits 0 |
