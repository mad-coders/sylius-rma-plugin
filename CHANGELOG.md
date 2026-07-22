# Changelog

All notable changes to `madcoders/sylius-rma-plugin` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), this project
adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html), and commits follow
[Conventional Commits](https://www.conventionalcommits.org/) (see
[docs/adr-log/0009-conventional-commits.md](docs/adr-log/0009-conventional-commits.md)).

## [Unreleased]

### Fixed

- **A return reason can be created in the admin again**: the code field on the create form was
  rendered disabled and required at the same time, so the form could not be completed. Sylius's
  `AddCodeFormSubscriber` locks the field whenever `getCode()` is not null, but `OrderReturnReason`
  initialises its code to an empty string, so the lock also applied to a brand new reason. The field
  is now added by the form type itself and only locked once the reason actually has a code, keeping
  the code immutable on the update form. Submitting the create form previously produced a reason
  with an empty code.
- **Admin return reason form labels and messages are translated**: the form type asked for
  `madcoders_rma.admin.reason.form.*` while the catalogue defines `madcoders_rma.admin.reasons.form.*`,
  so every field label on the create/edit page rendered as a raw translation key. The code field's
  `NotBlank` message also pointed at a key copied from another project
  (`vsf_navi.admin.vsf_navi_item.form.code.not_blank`) and is now
  `madcoders_rma.validator.code.not_blank`. Adds the missing page headers
  (`madcoders_rma.ui.new_order_return_reason`, `madcoders_rma.ui.edit_order_return_reason`) and
  fixes the misspelled `descriptin` key.

## [1.3.0-rc.4] - 2026-07-05

Fourth release candidate for the 1.3 line, a security hardening pass over the return and
withdrawal flows on top of rc.3.

### Security

- **Harden the RMA auth-code against brute force**: the emailed code is the single secret gating
  the return/withdrawal flow, and a successful guess can trigger an irreversible order cancellation
  via the instant-withdrawal path. The attempt limit is now a real lockout (verification hard-stops
  once attempts reach the maximum, before evaluating the guess), the code is invalidated on lockout,
  expiry and successful one-time use (so its hash cannot be replayed), it is generated with
  `random_int()` over an 8-digit keyspace and compared with `hash_equals()` (constant time). The
  code-request and verification endpoints are rate limited per client IP and order number by an
  in-plugin PSR-6 cache-backed throttler (no extra Composer dependency, only a cache pool),
  returning HTTP 429 with a `Retry-After` header when exceeded; the limiter sits behind the
  `madcoders_rma.limit_auth_attempts` feature flag (env `MADCODERS_RMA_LIMIT_AUTH_ATTEMPTS`,
  default on) ([#26](https://github.com/mad-coders/sylius-rma-plugin/issues/26)).
- **Send the return document to the order's customer, not a customer-supplied address**: the
  editable `customerEmail` form field was used verbatim as the recipient of the return-form e-mail
  and its attached PDF (which carries the customer's name, address and order lines), so a customer
  could redirect their own return document to an arbitrary address. The recipient is now re-derived
  from the order's customer and the submitted value is never read for the recipient; `NotBlank` and
  `Email` constraints are added on the field for input hygiene
  ([#28](https://github.com/mad-coders/sylius-rma-plugin/issues/28)).
- **Authorize the withdrawal success page**: `WithdrawalController::successIndex` rendered an
  `OrderReturn` looked up by its predictable return number (`RMA-{orderNumber}-{n}`) with no
  authorization check, an enumeration oracle for return existence and status. It is now gated on the
  same `OrderReturnVoter` check as the sibling withdrawal actions
  ([#27](https://github.com/mad-coders/sylius-rma-plugin/issues/27)).

## [1.3.0-rc.3] - 2026-06-23

Third release candidate for the 1.3 line, making the RMA e-mails self-contained, branded and
localized on top of rc.2.

### Changed

- **Self-contained, branded, localized RMA e-mails**: every customer e-mail (return confirmation,
  withdrawal requested/confirmed/cancelled/fallback, and the verification code) now renders the
  return (RMA) number, order number, the related items (name, SKU, quantity), a state-appropriate
  message and - when present - the full refund/bank details, so the e-mail is a complete record even
  when the return-form PDF is off (the default). The return-confirmation e-mail branches its
  instructions on `return_form_pdf_enabled` (print the attached form vs. "this e-mail is your
  confirmation"), and the verification e-mail now shows its order number. The shared `_returnSummary`
  partial renders the details, and every e-mail is wrapped by overridable `_header`/`_footer`
  partials (override at `templates/bundles/MadcodersSyliusRmaPlugin/Email/`) as the integration point
  for shop branding; the withdrawal e-mails now also receive the channel. E-mail copy is provided in
  8 locales (en, pl, de, fr, it, es, sv, da)
  ([#23](https://github.com/mad-coders/sylius-rma-plugin/issues/23)).

## [1.3.0-rc.2] - 2026-06-21

Second release candidate for the 1.3 line, adding item-level return control, configurable refund
details, and a pluggable return-number format on top of rc.1.

### Added

- **Non-returnable products**: an item-level rule that excludes specific products (perishables,
  hygiene/sealed goods, made-to-order items, gift cards) from the return flow even on an otherwise
  returnable order. Opt in by having the Sylius `Product` implement `NonReturnableProductInterface`
  and apply `NonReturnableProductTrait` (supplies the `non_returnable` column/accessors); the admin
  product form then gains a "Non-returnable" checkbox. A flagged product is never offered for return
  or persisted onto an `OrderReturn`, and an order whose items are all non-returnable shows "nothing
  to return". The decision lives behind an injectable `ProductReturnabilityCheckerInterface`. Ships a
  Doctrine migration adding `non_returnable` to `sylius_product`
  ([#18](https://github.com/mad-coders/sylius-rma-plugin/issues/18)).
- **Configurable "Additional information" on the return form**: an optional section collecting the
  refund bank details (bank account number validated as an IBAN, account holder name, and bank name
  / BIC-SWIFT), gated behind the `MADCODERS_RMA_REQUIRE_ADDITIONAL_INFORMATION` environment variable
  (default `false`). When off the section is hidden and not required; when on the three fields are
  rendered and required. Implemented as a `ReturnFormTypeExtension` with the flag behind
  `AdditionalInformationCheckerInterface` (shared with the Twig visibility check); values persist on
  `OrderReturn` and show in the admin and shop return views. The withdrawal flow always collects the
  bank account number regardless of the flag. Ships a Doctrine migration (`Version20260621000000`).
- **Pluggable return-number format** via `ReturnNumberGeneratorInterface`, with a default format of
  `RMA-{orderNumber}-{n}` (the sequence increments until the number is unique). Override the
  interface to customize how return numbers are generated.

### Changed

- Ignore the `guzzlehttp/guzzle` security advisories in the Composer audit policy to unblock
  `composer update` in CI; every `^6.5` release required transitively by Sylius 1.12 is flagged and
  there is no advisory-free version in range.

## [1.3.0-rc.1] - 2026-06-15

First release candidate for the 1.3 line, headlined by EU "right of withdrawal" support for
pre-shipment orders.

### Added

- **Pre-shipment withdrawal** (EU right of withdrawal): a not-yet-shipped order can be withdrawn
  (cancelled) instead of returned. Unpaid orders are withdrawn **instantly** (cancelling the Sylius
  order); paid orders raise a `withdrawal_request` that an admin resolves, with customer-side
  **item selection for partial withdrawals** ([#7](https://github.com/mad-coders/sylius-rma-plugin/issues/7)).
  The `return_status` state machine gains `withdrawal_request` and `withdrawn` states and the
  `request_withdrawal`, `withdraw`, and `fallback_to_return` transitions, each wired to its own
  customer/admin e-mail (requested, confirmed, fallback, cancelled). Ships the
  `WithdrawalController`/`AdminWithdrawalConfirmController`, state-machine notifiers, and a Doctrine
  migration (`Version20260612000000`).
- `allow_unpaid_withdrawal` configuration, backed by the `MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL`
  environment variable (default `true`): when `false`, unpaid not-yet-shipped orders are not offered
  the withdrawal flow.
- Injectable RMA-path eligibility checkers that decide whether an order is offered a return,
  withdrawal, or instant withdrawal, each behind an interface for overriding:
  `ReturnEligibilityCheckerInterface`, `Withdrawal\WithdrawalEligibilityCheckerInterface`, and
  `Withdrawal\InstantCancellationEligibilityCheckerInterface`. Orders still in checkout (cart) are
  rejected by the eligibility checkers.
- README documentation for the withdrawal flow, the returns state machine (with Mermaid diagrams),
  a configuration reference table, and a Customizations chapter on overriding the eligibility
  checkers.
- CI "fixtures runnable" gate that loads the default Sylius + RMA fixtures suite end-to-end, plus a
  `make fixtures-test` target.

### Fixed

- Make the bundled default fixtures suite loadable via `sylius:fixtures:load`. The
  `madcoders_rma_order_return` fixture required an integer `customer_number` but asserted and stored
  it as a string, so no value satisfied both gates and the shipped fixture could never load; it now
  accepts an integer or string and casts to string before the setter
  ([#9](https://github.com/mad-coders/sylius-rma-plugin/issues/9)).
- Allow a null `description` in the return-reason and return-consent fixtures
  (`Assert::nullOrString`), matching their option definitions (default null, `string|null`). Loading
  either fixture without a description previously threw "Expected a string. Got: NULL".

### Changed

- Ignore the `guzzlehttp/psr7` security advisory in the Composer audit policy to unblock
  `composer update` in CI.

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

[Unreleased]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.3.0-rc.3...HEAD
[1.3.0-rc.3]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.3.0-rc.2...1.3.0-rc.3
[1.3.0-rc.2]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.3.0-rc.1...1.3.0-rc.2
[1.3.0-rc.1]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.2.0...1.3.0-rc.1
[1.2.0]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/mad-coders/sylius-rma-plugin/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/mad-coders/sylius-rma-plugin/releases/tag/1.0.0
