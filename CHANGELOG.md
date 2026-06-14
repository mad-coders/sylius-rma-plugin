# Changelog

All notable changes to `madcoders/sylius-rma-plugin` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), this project
adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html), and commits follow
[Conventional Commits](https://www.conventionalcommits.org/) (see
[docs/adr-log/0009-conventional-commits.md](docs/adr-log/0009-conventional-commits.md)).

## [Unreleased]

## [1.2.1] - 2026-06-14

### Fixed

- Make the bundled default fixtures suite loadable via `sylius:fixtures:load`. The
  `madcoders_rma_order_return` fixture required an integer `customer_number` but asserted and stored
  it as a string, so no value satisfied both gates and the shipped fixture could never load; it now
  accepts an integer or string and casts to string before the setter
  ([#9](https://github.com/mad-coders/sylius-rma-plugin/issues/9)).
- Allow a null `description` in the return-reason and return-consent fixtures
  (`Assert::nullOrString`), matching their option definitions (default null, `string|null`). Loading
  either fixture without a description previously threw "Expected a string. Got: NULL".

### Added

- CI "fixtures runnable" gate that loads the default Sylius + RMA fixtures suite end-to-end, plus a
  `make fixtures-test` target.

## [1.2.0] - 2026-06-09

### Changed

- Modernize `src/` to PHP 8.2 standards with Rector (typed properties, constructor property
  promotion) and remove unused injected dependencies. Reduce static-analysis debt by driving the
  PHPStan baseline down (293 -> 253 entries and counting; see
  [docs/adr-log/0012](docs/adr-log/0012-rector-and-php82-modernization.md) and
  [ai/tasks/12-phpstan-baseline-free.md](ai/tasks/12-phpstan-baseline-free.md)).

### Fixed

- Enforce the return deadline by the total number of days elapsed since shipment
  (`DateInterval::$days`) instead of the day-of-month component (`DateInterval::$d`). Previously a
  reason's deadline stopped being enforced once more than a calendar month had passed and
  eligibility flipped on/off around month and year boundaries
  ([#8](https://github.com/mad-coders/sylius-rma-plugin/issues/8)). The deadline decision now lives
  in a dedicated, injectable `ReturnDeadlineCheckerInterface`, wrapped by a
  `ReturnReasonEligibilityCheckerInterface` that resolves the order's shipment.

## [1.1.0] - 2026-06-07

### Changed

- Upgrade the plugin to Sylius `~1.12`, PHP `^8.2`, and Symfony `^6.4` (from Sylius
  `~1.8 || ~1.9`). Migrated the bundled test application to the Sylius 1.12 configuration.
- Return-form PDF generation is now **opt-in and off by default**
  (`madcoders_rma.return_form_pdf_enabled`): the confirmation email is sent without the PDF
  attachment and the print/download endpoints and links are disabled unless enabled. Removes the
  hard dependency on `wkhtmltopdf` from the default and CI paths.

### Added

- `madcoders_rma.return_form_pdf_enabled` feature flag (default `false`) gating all return-form
  PDF generation; exposed to templates via the Twig function
  `madcoders_rma_return_form_pdf_enabled()`.

- Make-based development workflow (`Makefile`) wrapping setup, tests, and static analysis.
- `make serve-test` target serving the test app in the `test` environment on
  `https://127.0.0.1:8081`, so the `@javascript` Behat suite runs against the test database
  instead of the dev server.
- `docker-compose.yml` providing MySQL 8 (host port 3307) and headless Chrome (port 9222) for
  the test suite.
- Architecture Decision Log under `docs/adr-log/`, project guides `AGENTS.md` and `ai/`.
- This `CHANGELOG.md`, adoption of Conventional Commits, and a `.gitmessage` template.
- GitHub Actions CI (`.github/workflows/ci.yml`): static analysis, unit tests, and the
  non-JavaScript Behat suite, driven through the Make targets.
- `make backend-test` target to create the test-environment database/schema.
- Ignore the unavoidable `api-platform/core` and `enshrined/svg-sanitize` security advisories
  for CI installs (`config.policy.advisories.ignore`); both are pinned with no advisory-free
  version by the Sylius 1.12 dependency tree.

### Removed

- Psalm and PhpSpec from the toolchain; static analysis is PHPStan `^2.0` (with
  `phpstan-baseline.neon`) and unit tests are PHPUnit.
- `easy-coding-standard.yml`, replaced by `ecs.php` (`sylius-labs/coding-standard ^4.0`).
- Bitbucket Pipelines (`bitbucket-pipelines.yml`), replaced by GitHub Actions.

## [1.0.0] - 2021-11-17

### Added

- Initial release of the RMA plugin for Sylius `~1.8 || ~1.9`.

[Unreleased]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.2.0...HEAD
[1.2.0]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/mad-coders/sylius-rma-plugin/releases/tag/1.0.0
