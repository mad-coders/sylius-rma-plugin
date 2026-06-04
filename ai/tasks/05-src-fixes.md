# Task 05 - Fix source deprecations

**Goal:** Resolve PHP 8.2 / Symfony 6.4 / Sylius 1.12 breakages in `src/` with minimal diffs.

## Steps
- Drive fixes from `vendor/bin/phpstan`, `vendor/bin/psalm`, PHPSpec failures, and the Symfony
  deprecation log from booting the test app.
- Likely areas (small): nullable type hints, `ContainerInterface` typing, PHP 8.2
  dynamic-property deprecations (declare properties), event-listener signatures, Twig 3 edge
  cases, any changed Sylius mailer / state-machine service signatures.
- Apply targeted edits per `ai/coding-rules.md`. Do not mass-refactor. Optionally run
  `sylius/sylius-rector` rule sets, then review every change by hand.

## Verify
- `vendor/bin/phpstan analyse -c phpstan.neon src` clean.
- `vendor/bin/psalm` clean.
- `vendor/bin/phpspec run` green.
- `vendor/bin/ecs check src` clean.
