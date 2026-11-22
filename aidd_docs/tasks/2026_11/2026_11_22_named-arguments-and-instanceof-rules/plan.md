---
objective: "NamedArgumentsRule and NoInstanceofOnForbiddenDependencyRule exist as opt-in rules, tested, configured in extension.neon and documented."
status: implemented
---

# Plan: NamedArgumentsRule and NoInstanceofOnForbiddenDependencyRule

Source: GitHub issue atournayre/phpstan-rules#1 (rules 1 and 2 only; rule 3 dropped by the user)

## Phases

| #   | Phase        | File                         | Depends on |
| --- | ------------ | ---------------------------- | ---------- |
| 1   | NamedArgumentsRule | [`phase-1.md`](./phase-1.md) | none |
| 2   | NoInstanceofOnForbiddenDependencyRule and final checks | [`phase-2.md`](./phase-2.md) | 1 |

## External sources

| URL   | What it settled   |
| ----- | ----------------- |
| none | none |

## Impact

| Category                                           | Verdict and evidence | Handled in         |
| -------------------------------------------------- | -------------------- | ------------------ |
| runtime | none: `TypeExpr`, `ReflectionProvider`, `ParametersAcceptorSelector` exist in the installed `vendor/phpstan/phpstan` (class_exists checked) | none |
| regression | low: `extension.neon`, README and config tests are edited; `tests/Config/DefaultConfigTest.php` and `EnabledConfigTest.php` lock the opt-in behavior; no safety-net phase needed | 1, 2 |
| architecture | two rules added through the existing opt-in pattern (service, `conditionalTags`, `parametersSchema`, bool parameter); no structural change | 1, 2 |
| documentation | README rules table and parameters example, `extension.neon` header comment, missing `LICENSE` file (composer.json declares MIT) | 1, 2 |

## Rules

| Rule | Why it applies |
| ---- | -------------- |
| Elegant Objects (`~/.claude/rules/php/elegant-objects.md`) | PHP code: final classes, readonly constructor injection, no static methods |
| Definition of Done (`~/.claude/rules/core/definition-of-done.md`) | `make run-all-tests-php` must pass before done |
| README "Adding a rule" checklist | Every new rule follows the repo's own 4 steps |

## Decisions

| Decision | Why |
| -------- | --- |
| Error identifiers use the `tournayreLabs.` prefix (`tournayreLabs.namedArguments`, `tournayreLabs.noInstanceofOnForbiddenDependency`) | Consistent with the 7 existing rules; user choice |
| Rule 3 `NoStaticMethodInServiceRule` is out of the plan | Overlaps `DontUseStaticMethodsRule` of `phpstan-elegant-object`; user choice |
| Namespace `TournayreLabs\PHPStanRules`, package `tournayrelabs/phpstan-rules`, PHP `>=8.2`, `phpstan/phpstan ^2` as sole runtime dependency | Already in place in composer.json; closes the open decisions of the issue |
