# Run: NamedArgumentsRule and NoInstanceofOnForbiddenDependencyRule

## Run 1

- Head: `66f5ebf`
- Left: tag, distribution and consumer adoption (out of scope); the `@phpstan-ignore phpstanApi.instanceofAssumption` on the `TypeExpr` check; no test for the synthetic `TypeExpr` call (needs phpstan-phpunit, not required)

| Task | Validation | Result |
| ---- | ---------- | ------ |
| 1.1 | `vendor/bin/phpstan analyse` at level max | No errors |
| 1.2 | `vendor/bin/phpunit --filter NamedArgumentsRuleTest` | 3 tests pass: mixed, named, single, spread, first-class callable, variadic, builtin, @no-named-arguments, dynamic, magic, nullsafe, static, new, excluded path |
| 1.3 | `vendor/bin/phpunit tests/Config` | Rule absent by default, registered with `namedArguments: true`; README row present |
| 2.1 | `vendor/bin/phpstan analyse` at level max | No errors |
| 2.2 | `vendor/bin/phpunit --filter NoInstanceofOnForbiddenDependencyRuleTest` | 4 tests pass: FQCN in denied namespace, allowed namespace, imported name, two restrictions, excluded path, no namespace |
| 2.3 | `vendor/bin/phpunit tests/Config` | Rule absent by default, registered when enabled; README row present |
| 2.4 | `ls LICENSE`, `make run-all-tests-php` | LICENSE exists; exit 0, 27 tests |
