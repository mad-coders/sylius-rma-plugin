# Coding rules - madcoders/sylius-rma-plugin

Conventions to follow when working in this repository, especially during the Sylius 1.12
upgrade. The guiding principle for the upgrade is **minimal, targeted diffs**: make the code
compatible, do not modernize for its own sake.

## PHP

- Every PHP file starts with `declare(strict_types=1);` and keeps the existing license
  docblock header.
- Target runtime is PHP `^8.2`. New or changed code may use 8.x features (constructor
  property promotion, typed properties, `match`, nullable types), but **do not mass-rewrite**
  existing classes to adopt them. Touch only what the upgrade requires.
- Keep explicit parameter and return type hints. Add types where the upgrade surfaces missing
  ones, but prefer the smallest change that satisfies the analysers.
- PHP 8.2: avoid dynamic (undeclared) properties - declare any property you assign to.

## Sylius / Symfony conventions

- Doctrine mapping stays **XML mapped-superclasses** in
  `src/Resources/config/doctrine/`. Do not convert to attributes/annotations.
- Service wiring stays **XML** under `src/Resources/config/services/`, kept modular by concern
  (controllers, forms, emails, generators, providers, menu, auth_code, security_voter, ...).
  Prefer `autowire`/`autoconfigure` as already used.
- Resources are registered through `src/DependencyInjection/Configuration.php`; new resources
  follow the existing model/interface/controller/factory/repository shape.
- Forms use `buildForm()`, `configureOptions(OptionsResolver)`, and `getBlockPrefix()`. No
  controller route annotations - routing stays in YAML under
  `src/Resources/config/routing/`.
- Translations live in `src/Resources/translations/*.yaml`; add keys there, never hardcode
  user-facing strings in templates or PHP.
- Twig extensions extend `AbstractExtension` and register `TwigFunction`/`TwigFilter`.

## Quality gates (must stay green)

Run the relevant subset after every change; run the full set before considering a task done.
Always drive them through the **Make targets** (`make help` lists them), not the raw binaries -
the targets pin the right `APP_ENV`, config files and flags:

```
make phpunit     # unit/component tests
make phpstan     # static analysis
make ecs         # code style (sylius-labs/coding-standard); make fix to auto-apply
make static      # phpstan + ecs
make behat       # non-JS behaviour suite (after test app rebuild)
make verify      # full gate: static + phpunit
```

The `@javascript` Behat suite runs against a test-env server: `make docker-up-all`, then
`make serve-test` (test app on https://127.0.0.1:8081), then `make behat-js`.

- Code style is defined by `sylius-labs/coding-standard` via the ECS config. Run `make fix`
  locally before committing; never hand-format against it.
- Do not lower the PHPStan level or add broad ignores to pass. If a deprecation is external
  (vendor) and unavoidable, add a narrow, commented `phpstan-baseline.neon` entry.

## Workflow

- **Run tests after each change.** Make one logical change, run the relevant gate, confirm
  green, then proceed. This is the core requirement for the 1.12 upgrade.
- Keep commits scoped to a single task from `ai/tasks/`.
- When an analyser reports many issues, fix the root cause once rather than suppressing each
  call site.

## Writing style (docs, comments, commit messages)

- No em dashes (`-` U+2014) or en dashes (`-` U+2013). Use a plain hyphen, colon, parentheses,
  or a sentence break.

See `ai/architecture.md` for the structure these rules apply to.
