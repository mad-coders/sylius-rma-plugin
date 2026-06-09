# Task 12 - Make PHPStan baseline-free

**Goal:** Drive `phpstan-baseline.neon` to empty by fixing the real level-max errors. Status:
**in progress** (293 -> 253 entries). ADR:
[`docs/adr-log/0012`](../../docs/adr-log/0012-rector-and-php82-modernization.md).

## Done
- Config aligned to the Sylius 2.x standard: added the `phpstan-doctrine` `objectManagerLoader`
  (`tests/object-manager.php`) so analysis reads the real ORM mapping, and the
  `missingType.iterableValue` ignore.
- Entities: nullable columns aligned to nullable property types.
- Fixtures: `mixed` `OptionsResolver` values narrowed with `Webmozart\Assert` (not casts -
  strict-rules forbid casting `mixed`); item factory repository typed concretely.

## Remaining (~253 errors), with the established fix patterns
- **`argument.type` / `method.nonObject` (mixed or nullable):** narrow with `Assert::*` or guard
  for null and early-return before the call. Do **not** cast `mixed`.
- **`booleanNot.exprNotBoolean` / `if.condNotBoolean`:** replace `!$x` / `if ($x)` on non-bool
  with explicit `null !==` / `'' !==` comparisons.
- **`missingType.generics`:** add `@extends`/`@implements`/`@var` generics (repositories,
  `Voter<...>`, `Collection<int, X>`).
- **`offsetAccess.nonOffsetAccessible` / `cast.string`:** type decoded JSON payloads with
  `/** @var array{...} */` (or `Assert`), then access/convert.
- **`doctrine.associationType`:** align the association property type to the mapped target
  (e.g. `RmaConfiguration::$channel`).
- Hot files (by count): `Controller/ReturnController`, `Controller/RmaConfigurationController`,
  `Controller/AdminManagementController`, `Controller/AuthController`,
  `Services/Configuration/ReturnAddressConfigurator`, `Services/ReturnRequestBuilder`,
  `Controller/ShopManagementController`.
- A small number of entity cases (property nullable but DB notnull) cannot be made non-nullable
  without breaking hydration; if unavoidable they stay as narrow, commented baseline entries
  (per ADR 0008), not a bulk baseline.

## Workflow
Fix file-by-file; after each batch run `make phpstan`, `make fix`, `make phpunit`, re-run
`make behat` after hydration-sensitive (entity) changes, then regenerate the shrunken baseline
so the pre-commit hook stays green, and commit (Conventional Commits).

## Verify (definition of done)
- `git grep -c 'message:' phpstan-baseline.neon` == 0 (or only narrow commented vendor entries).
- `make phpstan`, `make ecs`, `make phpunit` green; `make behat` 27/27.
