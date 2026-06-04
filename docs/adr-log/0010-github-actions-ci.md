# 0010 - CI on GitHub Actions (replacing Bitbucket Pipelines)

- **Status:** Accepted
- **Date:** 2026-06

## Context

The repository's `origin` is on GitHub (`mad-coders/sylius-rma-plugin`), but CI ran on
**Bitbucket Pipelines** (`bitbucket-pipelines.yml`) - a leftover from when the project lived on
Bitbucket. That split means CI is not visible on pull requests, and the pipeline invokes the
raw binaries directly, duplicating commands that are otherwise expressed as Make targets
(see [0008](0008-quality-tooling.md)).

The Bitbucket pipeline ran: install dependencies, then in parallel PHPUnit and PHPStan + ECS,
then the non-JavaScript Behat suite against MySQL 8. It did **not** run the `@javascript`
suite.

## Decision

Replace Bitbucket Pipelines with **GitHub Actions** (`.github/workflows/ci.yml`), inspired by
the Sylius plugin CI but driven through this project's **Make targets**.

- Three parallel jobs on `ubuntu-latest`, PHP 8.2 via `shivammathur/setup-php@v2`:
  - `static` - `make phpstan` + `make ecs`.
  - `unit` - `make phpunit`.
  - `behat` - `make frontend` (build assets) + `make backend-test` (test DB) + `make behat`
    (non-JavaScript), with a MySQL 8 **service** that mirrors `docker-compose.yml` (host port
    3307, root password `rma`) so `tests/Application/.env` and the Make targets work unchanged.
- Triggers: pushes to release branches (`1.0`, `1.1`, ...), all pull requests, and manual
  dispatch; in-progress runs for the same ref are cancelled.
- `composer.lock` is committed so CI installs the exact, known-good dependency set
  (`composer install` from the lock). Without it, CI does a full `composer update`, which a
  modern Composer rejects because Sylius 1.12's transitive `api-platform/core ^2.6` carries
  security advisories. Composer downloads are cached on the lock hash; Behat logs
  (`etc/build/`) are uploaded on failure.
- A new `make backend-test` target creates the **test-environment** database/schema (the
  existing `make backend` targets the dev database), filling a gap the Behat/CI flow needed.

`bitbucket-pipelines.yml` is removed.

## Consequences

- CI status is reported on GitHub pull requests; the pipeline stays in lock-step with local
  development because it calls the same Make targets.
- **The `@javascript` Behat suite is intentionally not in CI yet**, keeping parity with the
  previous pipeline. It remains runnable locally (`make docker-up-all` + `make serve-test` +
  `make behat-js`); adding a dedicated CI job (headless Chrome on 9222 + the test server on
  8081) is a follow-up that would warrant its own update to this ADR.
- A single PHP version (8.2) is exercised, matching the project's supported stack; the matrix
  can be widened later if more versions are supported.
