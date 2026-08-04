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
- CI resolves dependencies with `composer update` (the plugin keeps `composer.lock` gitignored,
  per library/plugin convention). A modern Composer blocks Sylius 1.12's unavoidable transitive
  dependencies (`api-platform/core ^2.6`, `enshrined/svg-sanitize ^0.15.4||^0.16`) because they
  carry security advisories with no advisory-free version in range, so `composer.json` sets
  `config.policy.advisories.ignore: ["api-platform/core", "enshrined/svg-sanitize"]` - a scoped
  opt-out that keeps advisory blocking on for every other package. (`config` is root-only, so it affects only this repo's
  CI/dev installs, never downstream consumers.) Composer downloads are cached on the
  `composer.json` hash; Behat logs (`etc/build/`) are uploaded on failure.
- A new `make backend-test` target creates the **test-environment** database/schema (the
  existing `make backend` targets the dev database), filling a gap the Behat/CI flow needed.

`bitbucket-pipelines.yml` is removed.

## Update (2026-08): Sylius version matrix

Since the plugin supports `sylius/sylius: >=1.12,<1.14`, a single `composer update` run only ever
proved the version the solver happened to pick (1.13), leaving 1.12 - the line most existing
installs are on - untested. Every job therefore runs in a **`sylius: ["1.12", "1.13"]` matrix**
with `fail-fast: false`, so one broken line does not hide the state of the other.

- The per-job preamble (setup-php, Sylius pinning, Composer cache, `composer update`) lives in a
  reusable **composite action**, `.github/actions/setup`, because GitHub Actions has no YAML
  anchors and the four jobs would otherwise repeat it eight times. Jobs that do not need the
  Composer scripts pass `composer-options: "--no-scripts"`, matching the previous behaviour.
- Pinning is `composer require --no-update "sylius/sylius:<line>.*"` before the install, which
  narrows the root constraint for that job only (the checkout is ephemeral). The Composer cache
  key includes the Sylius line so the two legs do not fight over one cache entry, and the action
  prints the resolved version so a run can be checked at a glance.
- Supporting both lines from one branch needs two version-conditional bits, both of which the
  matrix now guards:
  - `phpstan.neon` ignores `generics.notGeneric` for the Order/Channel/ProductVariant
    repositories. Those interfaces became generic in 1.13 and the type parameters are required
    there, while on 1.12 the same annotations are reported as errors. `reportUnmatchedIgnoredErrors:
    false` (already set) keeps the entry silent on 1.13.
  - `tests/Application/config/bundles.php` registers
    `Sylius\Abstraction\StateMachine\SyliusStateMachineAbstractionBundle` behind a `class_exists()`
    check: 1.13's `sylius.fixture.order` requires it, and it does not exist on 1.12.

## Consequences

- CI status is reported on GitHub pull requests; the pipeline stays in lock-step with local
  development because it calls the same Make targets.
- **The `@javascript` Behat suite is intentionally not in CI yet**, keeping parity with the
  previous pipeline. It remains runnable locally (`make docker-up-all` + `make serve-test` +
  `make behat-js`); adding a dedicated CI job (headless Chrome on 9222 + the test server on
  8081) is a follow-up that would warrant its own update to this ADR.
- A single PHP version (8.2) is exercised, matching the project's supported stack; the matrix
  can be widened later if more versions are supported.
- The matrix doubles the job count (four jobs x two Sylius lines) and therefore the CI minutes;
  that is the price of proving the `>=1.12,<1.14` constraint rather than asserting it. Adding a
  Sylius line means adding one matrix entry - and dropping 1.12 means removing the two
  version-conditional workarounds above.
