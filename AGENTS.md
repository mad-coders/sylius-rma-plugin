# AGENTS.md

Entry point for AI agents and contributors working on **madcoders/sylius-rma-plugin**, a
Sylius RMA (Return Merchandise Authorization) plugin that lets shop customers create and
submit return requests and lets admins manage them.

## Current focus: upgrade to Sylius 1.12

The plugin is being upgraded from Sylius `~1.8 || ~1.9` to **Sylius `~1.12.0`** on
**PHP `^8.2`** and **Symfony `^6.4`**. Work is tracked in `ai/tasks/` (files `01`..`08`).

## Read first

- `ai/coding-rules.md` - conventions to follow; the quality gates that must stay green.
- This file (`AGENTS.md`) - orientation, commands, and the testing policy.

## Deeper context (read on demand)

Pull these in **only when a change needs deeper understanding** of the area you are touching -
not as upfront reading for every task:

- `docs/adr-log/` - the **architectural decisions** and their rationale (the load-bearing
  choices). Open the relevant ADR before changing the area it governs; index in
  `docs/adr-log/README.md`. A glance-level list is under "Architectural decisions" below.
- `ai/architecture.md` - the full structural map (entities, resources, grids, state machine,
  controllers, services, routing, templates).
- `ai/tasks/` - the ordered Sylius 1.12 upgrade task breakdown (a record of the upgrade work;
  `10-qa-tooling-decision.md` tracks the decision to drop Psalm and PhpSpec).

## Looking things up (Sylius docs & code examples)

Don't guess at Sylius/Symfony/Doctrine APIs from memory - they shift between versions and
this project pins specific ones (Sylius `~1.12`, Symfony `^6.4`, PHP `^8.2`). When you need
framework behaviour, configuration, or a usage example, use the MCP tools:

- **context7** (`mcp__context7__*`) - fetch current, version-correct documentation for
  Sylius, Symfony, Doctrine, and any other library before relying on an API. Resolve the
  library id, then query the docs. Prefer this over training-memory for anything
  version-sensitive (resource config, grids, state machine, mailer, forms, security voters).
- **grep-app** (`mcp__grep-app__*`) - search real-world code across public repositories for
  concrete usage patterns (e.g. how other Sylius plugins wire a resource, a grid, or a
  `winzou_state_machine` callback). Use it to confirm idioms before writing new code.

Reach for these whenever you're unsure about an API or want a worked example; cite what you
found in the change rationale.

## Golden rule

**Run the relevant tests after each change.** Make one logical change, run the matching
gate, confirm green, then continue. Keep diffs minimal and targeted - this is an upgrade,
not a rewrite.

## Layout

- `src/` - plugin source (`Madcoders\SyliusRmaPlugin\`, PSR-4).
- `src/Resources/config/` - services (XML), doctrine (XML), routing (YAML), grids, config.yml.
- `tests/` - `Application/` (Sylius test app), `Behat/` (contexts + pages), `Unit/` (PHPUnit).
- `features/` - Behat feature files.

## Architectural decisions

The load-bearing decisions are recorded as ADRs in `docs/adr-log/` (each with context,
rationale, and the rules to honour). **Read the relevant ADR on demand** before changing the
area it governs, and don't introduce a second way of doing the same thing. At a glance:

- [0001](docs/adr-log/0001-sylius-plugin-resource-model.md) - Sylius plugin built on the
  resource model (resources in `Configuration.php`; stock `ResourceController`/`Factory`)
- [0002](docs/adr-log/0002-doctrine-xml-mapped-superclasses.md) - Doctrine mapping as XML
  mapped-superclasses (no attributes/annotations)
- [0003](docs/adr-log/0003-xml-service-wiring.md) - service wiring in XML, modular by concern
- [0004](docs/adr-log/0004-return-lifecycle-state-machine.md) - return lifecycle via a
  `winzou_state_machine` (drive status through transitions, never set it directly)
- [0005](docs/adr-log/0005-guest-auth-code-and-voter-authorization.md) - guest auth-code flow
  plus voter/authorizer-based authorization
- [0006](docs/adr-log/0006-presentation-conventions.md) - presentation conventions (YAML
  routing/grids, form idioms, translation keys; no hardcoded strings)
- [0007](docs/adr-log/0007-pdf-and-schema-migrations.md) - PDFs via `knp_snappy`; schema
  changes via Doctrine migrations
- [0008](docs/adr-log/0008-quality-tooling.md) - quality tooling: PHPStan + ECS + PHPUnit +
  Behat via Make (Psalm and PhpSpec dropped)

When you make a new load-bearing decision, add an ADR (copy the next number; see
`docs/adr-log/README.md`).

## Testing policy

Tests are part of "done", not an afterthought. Run them through the Make targets (below).

- **Every new feature gets a Behat feature.** User-visible behaviour (a new flow, page,
  admin action, or state transition) is specified in `features/*.feature` with contexts/pages
  under `tests/Behat/`. Prefer the non-JS suite; reserve `@javascript` for behaviour that
  genuinely needs a browser. Run with `make behat` (and `make serve-test` + `make behat-js`
  for the JS suite).
- **Every new service gets unit tests.** Anything added under `src/Services/`, `src/Email/`,
  `src/Generator/`, `src/Provider/`, `src/Security/`, etc. gets a matching test in
  `tests/Unit/`. Run with `make phpunit`.
- **Prefer testing behaviour over implementation.** Assert on observable outcomes and public
  contracts (what the service returns/does for given inputs, including edge cases and error
  paths), not private internals or call sequences. Avoid over-mocking; mock only the
  collaborators at the boundary. A test should survive a refactor that preserves behaviour.
- A change is not finished until `make verify` (static + unit) is green and any new/affected
  Behat scenarios pass.

## Commands

Always drive the toolchain through the **Make targets** (`make help` lists them all).
Do not call the underlying binaries (`vendor/bin/phpunit`, `vendor/bin/phpstan`,
`vendor/bin/ecs`, `vendor/bin/behat`, `symfony serve`) directly - the targets pin the
right `APP_ENV`, config files and flags.

Install / update dependencies:
```
make install                     # or: make update  (after editing constraints)
```

Bring up the test services (MySQL 8 on host port 3307, headless Chrome on 9222):
```
make docker-up                   # database only (unit + non-JS Behat)
make docker-up-all               # database + chrome (for @javascript Behat)
```
The test app reads `DATABASE_URL=mysql://root:rma@127.0.0.1:3307/...` from
`tests/Application/.env`. Port 3307 avoids clashing with a host MySQL on 3306.

Build the test application assets once (Encore; the suite renders Encore-based layouts):
```
make frontend
```

Quality gates (use the Make targets, not the raw binaries):
```
make phpstan     # static analysis
make ecs         # code style check  (make fix to auto-apply)
make static      # phpstan + ecs
make phpunit     # unit/component tests
make behat       # non-JS behaviour suite (after the test app is built)
make test        # phpunit + non-JS behat
make verify      # full gate: static + phpunit (no fixes)
```

The `@javascript` Behat suite needs a running **test-env** server. Use `make serve-test`
(test app on https://127.0.0.1:8081, test database) - never `make serve`, which runs the
**dev** app on 8080 against the dev database. `behat.yml.dist` points the browser at 8081,
so the two can run side by side without the JS suite touching dev data:
```
make docker-up-all   # MySQL + headless Chrome
make serve-test      # test server on 8081 (separate terminal, keep running)
make behat-js        # JavaScript Behat suite
```

Test application setup (one-time, run inside `tests/Application/`):
```
APP_ENV=test bin/console doctrine:database:create
APP_ENV=test bin/console doctrine:schema:create
APP_ENV=test bin/console assets:install public
```

## Test status (Sylius 1.12)

- PHPUnit, PHPStan (with `phpstan-baseline.neon`), and ECS: all green.
- Non-JS Behat: 26/27 scenarios pass. The one remaining failure
  (`features/managing_address_in_configuration.feature` - "successfully edited"
  notification) is a pre-existing mismatch: the plugin shows its own flash message
  ("Configuration updated") while the generic Sylius step asserts the core text
  "has been successfully updated.". It is unrelated to the 1.12 upgrade.

## Toolchain (verified locally)

PHP 8.2, Composer 2.x, Symfony CLI available. Behat JavaScript scenarios additionally need a
headless Chrome (`make docker-up-all`) and a running test-env server (`make serve-test`);
see `docs/CONTRIBUTING.md`.
