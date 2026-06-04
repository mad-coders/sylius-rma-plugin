# Task 02 - Update root composer.json constraints

**Goal:** Move dependency constraints to the 1.12 / Symfony 6.4 / PHP 8.2 target.

## require
- `php`: `^7.3` -> `^8.2`
- `sylius/sylius`: `~1.8.0 || ~1.9.0` -> `~1.12.0`
- `knplabs/knp-snappy-bundle`: keep `^1.8` (confirm PHP 8.2 compat; bump if needed)
- **Remove** the `conflict` block on `api-platform/core` (Sylius 1.12 requires it).

## require-dev
- `phpunit/phpunit`: keep `^9.5` (PHP 8.2 compatible)
- `phpspec/phpspec`: `^7.0`; `behat/behat`: `^3.7`
- `phpstan/phpstan`: `0.12.74` -> `^1.8`; `phpstan-doctrine`: `^1.3`;
  `phpstan-strict-rules`: `^1.0`; `phpstan-webmozart-assert`: `^1.1`
- `vimeo/psalm`: `4.4.1` -> `^5`
- `sylius-labs/coding-standard`: `^3.1` -> `^4.0`
- Symfony dev components (`browser-kit`, `debug-bundle`, `dotenv`, `intl`,
  `web-profiler-bundle`): `^4.4 || ^5.2` -> `^6.4`
- Replace `lakion/mink-debug-extension` -> `friends-of-behat/mink-debug-extension`
- Remove abandoned `sensiolabs/security-checker`
- Optionally add `sylius/sylius-rector` (dev) to assist mechanical fixes
- `extra.branch-alias.dev-master`: `1.9-dev` -> `1.12-dev`

## Verify
- `composer update` resolves with no conflicts on PHP 8.2.
