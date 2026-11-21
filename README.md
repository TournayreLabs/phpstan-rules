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
```

## Rules

| Parameter             | Identifier                       | Description                              |
|-----------------------|----------------------------------|------------------------------------------|
| `interfaceNameSuffix` | `tournayreLabs.interfaceNameSuffix` | Interfaces must end with `Interface`     |

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
