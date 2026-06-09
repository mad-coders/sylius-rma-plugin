# Task 11 - Rector integration + PHP 8.2 modernization

**Goal:** Integrate Rector and modernize `src/` to PHP 8.2 standards. Status: **done.**
ADR: [`docs/adr-log/0012`](../../docs/adr-log/0012-rector-and-php82-modernization.md).

## Steps (done)
- `composer require --dev rector/rector:^2.0`; add `rector.php`
  (`withPhpSets(php82: true)` + `withPreparedSets(deadCode: true, typeDeclarations: true)`,
  paths `src`, skip `src/Migrations`).
- `make rector` (dry-run) / `make rector-fix`; CI runs the dry-run gate.
- Applied Rector: typed properties, constructor property promotion, removal of unused injected
  dependencies.
- Reconciled the service XML where Rector dropped unused constructor params
  (`Admin/Shop/RmaConfiguration` controllers, `ReturnConsentFormType`, `ReturnRequestBuilder`).
- Typed `OrderReturn::$items` as the `Collection` interface (Doctrine hydrates a
  `PersistentCollection`); aligned entity property nullability to the ORM mapping for the
  nullable columns; reverted non-nullable changes that broke hydration (kept nullable).

## Verify
- `make rector` reports no pending changes.
- `make phpunit`, `make ecs` green; `doctrine:schema:validate` clean.
- `make behat` 27/27 (non-JS).
