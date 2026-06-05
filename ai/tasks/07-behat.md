# Task 07 - Get the Behat suite green

**Goal:** The non-JavaScript Behat suite passes on Sylius 1.12.

## Steps
- Update Behat contexts/pages under `tests/Behat/` for any Sylius 1.12 UI/API context or
  page-object changes.
- Check `behat.yml.dist` and `tests/Behat/Resources/suites.yml` extension config against the
  bumped friends-of-behat versions.

## Verify
- `vendor/bin/behat --strict --tags="~@javascript"` passes.
- JavaScript suite (`--tags="@javascript"`) is optional and may be run separately with a
  headless Chrome + `symfony server`.

## Result
- 26/27 non-JS scenarios pass. Fixes applied during the upgrade:
  - Symfony 6 controller notation: routing changed from `service:method` to `service::method`.
  - Twig 3: `{% for x in y if cond %}` replaced with `|filter(...)`.
  - Behat contexts: `session` -> `request_stack` + `session.factory`; `EmailCheckerInterface`
    moved to `Sylius\Behat\Service\Checker`; `hook.email_spool` -> `hook.mailer`;
    dropped selenium2 driver and the imagick-dependent screenshot extension.
  - URL generation strips the `#` order-number prefix (consistent with
    `OrderByNumberProvider::ORDER_PREFIX_SIGN`).
- Remaining failure: the configuration "successfully edited" notification assertion is a
  pre-existing message/step mismatch (see AGENTS.md), not an upgrade regression.
