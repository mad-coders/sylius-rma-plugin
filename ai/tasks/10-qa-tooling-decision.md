# Task 10 - QA tooling decision: drop Psalm + PhpSpec

**Goal:** Record and reconcile the decision taken during the Sylius 1.12 upgrade to **drop
Psalm and PhpSpec** rather than upgrade them. This task exists so the earlier tasks (02, 03,
05, 09) stay as a faithful record of the original plan while the actual outcome is tracked
here. The architectural decision itself is logged in
[`docs/adr-log/0008-quality-tooling.md`](../../docs/adr-log/0008-quality-tooling.md).

## Decision

- **Psalm** (`vimeo/psalm`, `psalm.xml`) - **dropped.** Static analysis is covered by PHPStan
  alone. Tasks 03 and 05 originally called for a Psalm 5 schema bump and a clean
  `vendor/bin/psalm`; that work was not done because the tool was removed.
- **PhpSpec** (`phpspec/phpspec`, `phpspec.yml.dist`) - **dropped.** Specs are replaced by
  PHPUnit unit tests under `tests/Unit/` (behaviour-focused). Tasks 02 and 09 referencing
  `phpspec/phpspec ^7.0` and `vendor/bin/phpspec run` are superseded. Note:
  `phpspec/prophecy-phpunit` is kept - it is the Prophecy mocking bridge for PHPUnit, not the
  PhpSpec framework.
- **Code style** - `easy-coding-standard.yml` replaced by `ecs.php`
  (`sylius-labs/coding-standard ^4.0`).
- **Static analysis** - PHPStan upgraded to `^2.0` with `phpstan-baseline.neon`.

## Resulting quality gates

Driven through the Make targets (see `Makefile`, `ai/coding-rules.md`):

```
make phpstan   # static analysis (PHPStan 2.x)
make ecs       # code style (ecs.php); make fix to auto-apply
make phpunit   # unit/component tests
make behat     # non-JS behaviour suite ; make serve-test + make behat-js for @javascript
make verify    # full gate: static + phpunit
```

## Verify

- `composer.json` `require-dev` contains no `vimeo/psalm` and no `phpspec/phpspec`.
- `psalm.xml`, `phpspec.yml.dist`, and `easy-coding-standard.yml` are removed from the repo.
- `make verify` is green; `make behat` passes the documented scenarios.
- `docs/adr-log/0008-quality-tooling.md` exists and matches this outcome.
