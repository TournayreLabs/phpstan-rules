---
status: done
---

# Instruction: NamedArgumentsRule

## Architecture projection

> Tree of the final files from the project root, each folder ending in `/`. ✅ create · ✏️ modify · ❌ delete

```txt
.
├── extension.neon                                  ✏️
├── README.md                                       ✏️
├── src/
│   └── Rules/
│       └── NamedArgumentsRule.php                  ✅
└── tests/
    ├── Config/
    │   ├── DefaultConfigTest.php                   ✏️
    │   ├── EnabledConfigTest.php                   ✏️
    │   └── enabled.neon                            ✏️
    └── Rules/
        ├── NamedArgumentsRuleTest.php              ✅
        └── data/
            ├── named-arguments.php                 ✅
            └── named-arguments-excluded/
                └── excluded.php                    ✅
```

## Test Scope

| Scenario     | Action                  | Observable outcome   | Actor                           |
| ------------ | ----------------------- | -------------------- | ------------------------------- |
| Setup        | Fixture `named-arguments.php` with one case per skip rule | Fixture analysable by `RuleTestCase` | system |
| Happy path   | Call with 2+ arguments, at least one positional | One error with the French message and the `name: …` suggestion, identifier `tournayreLabs.namedArguments` | system |
| Edge case    | All positional on a function, method, static call, `new`, nullsafe call | One error each; nullsafe reported once | system |
| Edge case    | All named, single argument, spread, first-class callable, builtin function, `@no-named-arguments`, magic method, dynamic callee, variadic positional | No error | system |
| Edge case    | File under an excluded path | No error | system |
| Edge case    | Default config | `DefaultConfigTest` shows the rule unregistered | system |

## Tasks to do

### `1)` Implement the rule

> Report calls mixing positional arguments with 2+ arguments, suggesting named arguments.

1. Create `src/Rules/NamedArgumentsRule.php`: `final`, `@implements Rule<CallLike>`, constructor `(ReflectionProvider $reflectionProvider, array $excludedPaths)`, `getNodeType()` returns `CallLike::class`.
2. Skip: fewer than 2 arguments or all named; any `Arg::$unpack`; `isFirstClassCallable()`; `MethodCall` whose `var` is `PHPStan\Node\Expr\TypeExpr`; excluded path (`str_starts_with` on `$scope->getFile()`).
3. Resolve the callee for `FuncCall` (Name only, via `ReflectionProvider`), `MethodCall`, `NullsafeMethodCall` (arrives as `MethodCall`), `StaticCall`, `New_`; skip dynamic name, dynamic class, unresolvable class/method.
4. Skip: `isBuiltin()` function, `acceptsNamedArguments()->no()`, magic method (not native on the declaring class).
5. Select parameters with `ParametersAcceptorSelector::selectFromArgs` over the callee variants and named-arguments variants; skip a positional argument feeding a variadic parameter or without matching parameter.
6. Build the suggestion `name: …, name: …` (existing names kept, parameter names filled in) and the message `Appel à %s avec %s arguments dont au moins un positionnel : utiliser les arguments nommés PHP 8 (%s).` with `Class::method()` or `function()`, identifier `tournayreLabs.namedArguments`.

### `2)` Test the rule

> One fixture, one test class, covering every skip and report case of the issue.

1. Create `tests/Rules/data/named-arguments.php` in a dedicated namespace (classmap-autoloaded, names must not collide with other fixtures) and `tests/Rules/data/named-arguments-excluded/excluded.php`.
2. Create `tests/Rules/NamedArgumentsRuleTest.php` (`RuleTestCase`, rule built with `self::createReflectionProvider()`), one `analyse` for the main fixture and one with the excluded path configured.

### `3)` Wire the configuration and docs

> Opt-in through `extension.neon`, documented in the README.

1. In `extension.neon`: parameters `namedArguments: false` and `namedArgumentsExcludedPaths: []`, schema entries `bool()` and `listOf(string())`, service with `excludedPaths` argument, `conditionalTags` entry; extend the header comment.
2. In `tests/Config/enabled.neon`: `namedArguments: true`; add one test to `EnabledConfigTest` and one to `DefaultConfigTest`.
3. In `README.md`: enabling example, rules table row, short rule description with the skip list and `namedArgumentsExcludedPaths`.

## Test acceptance criteria

| Task | Acceptance criteria              |
| ---- | -------------------------------- |
| 1 | `vendor/bin/phpstan analyse` passes on `src/Rules/NamedArgumentsRule.php` at level max |
| 2 | `vendor/bin/phpunit --filter NamedArgumentsRuleTest` passes; each issue test case (mixed, all positional, single argument, spread, variadic, builtin, `@no-named-arguments`, magic method, `new`, static call, nullsafe, first-class callable, excluded path) has an assertion |
| 3 | `vendor/bin/phpunit tests/Config` passes; rule absent by default, present with `namedArguments: true`; README table lists `namedArguments` |
