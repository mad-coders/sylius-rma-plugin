# 0012 - Rector for PHP 8.2 modernization and a baseline-free PHPStan target

- **Status:** Accepted
- **Date:** 2026-06-05

## Context

After the Sylius 1.12 upgrade the source still used pre-8.0 idioms (untyped
`private $x` properties with `@var` docblocks, no constructor promotion), and PHPStan
(`^2.0`, level max) was only green because `phpstan-baseline.neon` suppressed a large set
of level-10 errors. We want the code to follow modern PHP 8.2 standards and the static
analysis to reflect real type safety rather than a frozen baseline. Future PHP/Sylius
upgrades should also be cheaper to perform.

## Decision

- **Adopt Rector** (`rector/rector ^2.0`) as the automated modernization/upgrade tool, with a
  focused `rector.php`: `withPhpSets(php82: true)` + `withPreparedSets(deadCode: true,
  typeDeclarations: true)`, scoped to `src/` (Migrations skipped). It runs via `make rector`
  (dry-run) / `make rector-fix`, and a dry-run gate in CI.
- **Modernize `src/` to PHP 8.2**: typed properties, constructor property promotion, and
  removal of genuinely-unused injected dependencies (the controllers are `final`, so this is
  not a BC break). Doctrine entities are modernized too, but entity property nullability is
  aligned to the ORM mapping and verified with `doctrine:schema:validate` + Behat, because
  typed properties are hydration-sensitive (non-nullable typed properties that are read before
  hydration throw, so columns that are not guaranteed populated stay nullable).
- **Make PHPStan baseline-free**: drive `phpstan-baseline.neon` to empty by fixing the real
  errors. Analysis is Doctrine-aware via the `phpstan-doctrine` `objectManagerLoader`
  (`tests/object-manager.php`); the Sylius standard `missingType.iterableValue` ignore is kept.
  For `mixed` values (e.g. resolved `OptionsResolver` options) the fix is `Webmozart\Assert`
  narrowing, **not** casting - `phpstan-strict-rules` forbids casting `mixed`.

## Consequences

- New and changed `src/` code uses PHP 8.2 typed properties / promoted constructors. Run
  `make rector` before committing; the CI dry-run fails if pending changes exist.
- Do not "fix" PHPStan by casting `mixed` or widening types; narrow with `Assert` or guard for
  null, or fix the underlying type. The baseline is reserved (per ADR 0008) only for narrow,
  commented, genuinely-unfixable vendor cases - it is not a place to hide new errors.
- The baseline is being driven to empty in committed batches (each commit stays green). This
  is in progress: it has gone from 293 to 253 entries (Rector modernization + fixture typing).
  The remaining work is tracked in `ai/tasks/12-phpstan-baseline-free.md`.
- When Rector removes an unused promoted dependency, the matching `<argument>` in the service
  XML must be removed too (Rector does not touch XML); the test-container compile and Behat
  catch any mismatch.
