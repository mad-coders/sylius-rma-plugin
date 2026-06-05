# Task 08 - Finalize README / UPGRADE docs

**Goal:** Public docs reflect the 1.12 / Symfony 6.4 / PHP 8.2 support.

## Steps
- `README.md` Requirements table: PHP `8.2`, Sylius `1.12` (drop 7.3/7.4, 1.8/1.9).
- `UPGRADE.md`: add an entry for the move to 1.12 (composer constraints, removed api-platform
  conflict, test-app rebuild, tooling bumps).

## Verify
- README requirements match `composer.json`.
- UPGRADE entry is accurate and dash-clean (plain hyphens only).
