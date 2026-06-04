# 0008 - Quality tooling: PHPStan + ECS + PHPUnit + Behat via Make

- **Status:** Accepted
- **Date:** 2026-06 (Sylius 1.12 upgrade)

## Context

The pre-upgrade plugin used a broad toolchain: PHPUnit, PhpSpec, PHPStan, Psalm, and ECS, each
invoked through its own binary. During the Sylius 1.12 upgrade we reassessed which gates to
carry forward (see `ai/tasks/02`, `03`, `05`, `09`, which still describe the original
upgrade-everything plan, and `ai/tasks/10-qa-tooling-decision.md`, which records this outcome).

## Decision

Standardise the quality gates on **PHPStan + ECS + PHPUnit + Behat**, driven through **Make
targets**, and **drop Psalm and PhpSpec**:

- **Dropped Psalm** (`vimeo/psalm`, `psalm.xml`) - PHPStan `^2.0` (with
  `phpstan-baseline.neon`) is the single static analyser.
- **Dropped PhpSpec** (`phpspec/phpspec`, `phpspec.yml.dist`) - unit testing is PHPUnit under
  `tests/Unit/`, behaviour-focused. (`phpspec/prophecy-phpunit`, the Prophecy bridge for
  PHPUnit, is kept; it is not the PhpSpec framework.)
- **Code style** is `sylius-labs/coding-standard ^4.0` via `ecs.php` (replacing
  `easy-coding-standard.yml`).
- All gates run through `make` (`make phpstan`, `make ecs`, `make phpunit`, `make behat`,
  `make static`, `make verify`); the targets pin `APP_ENV`, config files, and flags so the
  raw binaries are not called directly.

## Consequences

- Do not reintroduce Psalm or PhpSpec config/usage; do not lower the PHPStan level or add broad
  ignores - use a narrow, commented `phpstan-baseline.neon` entry for unavoidable vendor
  deprecations.
- New code is covered per the testing policy: a Behat feature for each new user-facing feature,
  PHPUnit tests for each new service, asserting behaviour over implementation.
- Contributors and agents use the Make targets, keeping local and CI runs identical. The
  `@javascript` Behat suite runs against the test-env server (`make serve-test` on port 8081),
  not the dev server.
