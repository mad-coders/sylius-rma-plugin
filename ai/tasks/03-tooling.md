# Task 03 - Migrate tooling configs

**Goal:** Make static analysis and code style run on the new tool versions.

## Steps
- `phpstan.neon`: migrate from 0.12 to 1.x. With `phpstan/extension-installer`, drop manual
  `includes` of the doctrine/strict/webmozart extensions. Keep excludes for
  `src/DependencyInjection/Configuration.php` and `tests/Application/**`. Adjust deprecated
  keys; set an explicit `level`.
- `psalm.xml`: bump to the Psalm 5 schema. Regenerate the baseline if one is used.
- `easy-coding-standard.yml`: confirm compatibility with `sylius-labs/coding-standard ^4.0`
  (it may ship an `ecs.php`; switch if the YAML import path changed).

## Verify
- `vendor/bin/phpstan analyse -c phpstan.neon src` runs (no config errors).
- `vendor/bin/psalm` runs.
- `vendor/bin/ecs check src` runs.
