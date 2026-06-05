# Task 09 - Docker-compose + runnable tests (FINAL PRIORITY)

**Goal (user-requested, do at the very end):** A working `docker-compose` setup and a test
suite - PHPUnit, PHPSpec, and Behat (including the `@javascript` suite) - that is runnable
end to end.

## Steps
- Add/refresh `docker-compose.yml` based on Sylius-Standard 1.12 services: PHP 8.2 (FPM/CLI),
  MySQL 8.0, and a headless Chrome (e.g. `zenika/alpine-chrome` or selenium/chrome) plus Node
  for asset builds, wired for the Behat JavaScript suite.
- Provide a database URL and env wiring that matches `tests/Application/.env.test`.
- Ensure asset build (`yarn install && yarn encore`/webpack) works in the container so Behat UI
  scenarios render.
- Document the full run sequence (compose up, composer install, db create/schema, asset build,
  server start, behat) in `docs/CONTRIBUTING.md` and/or `AGENTS.md`.

## Verify
- `docker compose up -d` starts PHP + MySQL (+ chrome).
- Inside the stack: `vendor/bin/phpunit`, `vendor/bin/phpspec run`,
  `vendor/bin/behat --strict --tags="~@javascript"`, and `--tags="@javascript"` all run.
- The documented commands reproduce a green suite from a clean checkout.
