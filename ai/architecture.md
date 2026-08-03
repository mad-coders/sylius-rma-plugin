# Architecture - madcoders/sylius-rma-plugin

RMA (Return Merchandise Authorization) plugin for Sylius. It lets shop customers create
and submit a return request for an order, and lets administrators manage those returns.
This document maps the moving parts so changes (including the Sylius 1.12 upgrade) stay
grounded in the real structure. Paths are relative to the repository root.

## Target stack

| | Version |
| :--- | :--- |
| PHP | `^8.2` |
| Symfony | `^6.4` |
| Sylius | `>=1.12,<1.14` (CI exercises both lines) |

(Pre-upgrade the plugin targeted PHP `^7.3`, Symfony `^4.4 || ^5.2`, Sylius `~1.8 || ~1.9`.)

## High-level flows

1. **Guest return (auth-code flow)** - A guest starts at `/rma-start`, receives a one-time
   auth code by email (`AuthCode` entity), verifies it at `/rma-start/{code}`, then fills the
   return form at `/rma-form/{orderNumber}`, reviews a summary, and submits. A PDF form is
   generated and a confirmation email is sent.
2. **Signed-in customer returns** - Under the shop account (`/account/return-history`), a
   customer lists past returns, opens one, downloads its PDF, or starts a new return from an
   existing order.
3. **Admin management** - Admins list/manage returns, return reasons, and consents via Sylius
   grids, view a return detail page, and drive the return through its state machine
   (cancel / complete). Per-channel RMA configuration (return address) lives under
   `/rma-configuration/{channelId}`.

## Domain entities (`src/Entity/`)

All entities are XML-mapped as **mapped-superclasses** in
`src/Resources/config/doctrine/*.orm.xml` (no annotations/attributes).

| Entity | Key relations / notes |
| :--- | :--- |
| `OrderReturn` | Root aggregate. `OneToMany` -> `OrderReturnItem` (cascade persist/remove). Has `orderReturnStatus` driven by the state machine. Implements `OrderReturnInterface`, `TimestampableInterface`. Status constants: `STATUS_DRAFT/NEW/COMPLETED/CANCELED`. |
| `OrderReturnItem` | `ManyToOne` -> `OrderReturn`. Holds sku, name, qty, maxQty, unitPrice. |
| `OrderReturnChangeLog` | Audit trail. `OneToOne` -> `OrderReturnChangeLogAuthor` (cascade). `TimestampableInterface`. |
| `OrderReturnChangeLogAuthor` | Author snapshot for a change-log entry. |
| `OrderReturnReason` (+ `OrderReturnReasonTranslation`) | Translatable. Admin-managed reasons; can be time-limited since shipment. |
| `OrderReturnConsent` (+ `OrderReturnConsentTranslation`) | Translatable. Terms the customer must accept before submitting. |
| `RmaConfiguration` | Per-channel key/value configuration (e.g. return address). `TimestampableInterface`. |
| `AuthCode` | One-time email verification code for guest flow. |

## Sylius resource registration

- `src/DependencyInjection/Configuration.php` - declares the 8 Sylius resources under the
  `madcoders_rma` root, each with model/interface/controller/factory/repository. Translatable
  resources (`order_return_reason`, `order_return_consent`) use `TranslatableFactory` and a
  `form`. `order_return` uses the custom `OrderReturnRepository`; `madcoders_rma_configuration`
  uses the custom `RmaConfigurationController`. All others use the stock `ResourceController` +
  `Factory`.
- `src/DependencyInjection/MadcodersSyliusRmaExtension.php` - `AbstractResourceExtension` +
  `PrependDoctrineMigrationsTrait`; loads the service XML and registers resources.
- `src/MadcodersSyliusRmaPlugin.php` - bundle class using `SyliusPluginTrait`.

## Grids (`src/Resources/config/grids/`)

- `..._admin_return_orders_grid.yml` - admin return list
- `..._admin_return_reason_grid.yml` - reason CRUD
- `..._admin_return_consent_grid.yml` - consent CRUD
- `..._shop_return_orders_grid.yml` - shop return history
- `..._shop_account_order_grid.yml` - order picker for new return

Imported via `src/Resources/config/grids.yaml`, which is imported from `config.yml`.

## State machine (`src/Resources/config/config.yml`)

`winzou_state_machine.order_return`, graph `return_status`, property `orderReturnStatus`:

- States: `draft`, `new`, `completed`, `canceled`
- Transitions: `new` (draft->new), `complete` (new->completed), `cancel` (draft/new->canceled)
- After-callbacks: `updated_changelog_on_cancel` and `updated_changelog_on_complete` invoke
  `src/Services/Callbacks/UpdatedChangelogOnCancel.php` / `UpdatedChangelogOnComplete.php`.

Admin transition routes use Sylius `applyStateMachineTransitionAction`
(`src/Resources/config/routing/admin_routing.yml`).

`config.yml` also wires `sylius_mailer` email templates and `sylius_ui.events` block layouts
(the `_legacySonataEvent.html.twig` blocks are the Sylius 1.10+ event-bridge style).

## Controllers (`src/Controller/`)

- `AuthController` - auth code start + verification (guest)
- `ReturnController` - guest return form: view, accept (summary), success, print
- `ShopManagementController` - signed-in customer: print, create
- `AdminManagementController` - admin return detail view
- `RmaConfigurationController` - per-channel config view/change/save
- `CreditsController` - attribution page

## Forms (`src/Form/Type/`)

13 form types, all using `configureOptions()` + `getBlockPrefix()` (no controller
annotations). Notable: `ReturnFormType`, `ReturnItemFormType`, `ReturnConsentFormType`,
`ConsentFormType`, `ReturnReasonFormType`, `OrderReturnConsentFormType`,
`ConfigChannelSelectFormType`, `ConfigAddressToChannelFormType`, the auth forms
(`ReturnAuthStartType`, `ReturnAuthVerificationType`), and translation types.

## Services (`src/Services/`, `src/Email/`, `src/Generator/`, `src/Provider/`, `src/Security/`)

- Business: `ReturnRequestBuilder`, `RmaChangesLogger`, `RmaAdminUserData`,
  `RmaVerificationPossibilityOfReturn`, `MaxQtyCalculator`, `Reason/ChoiceProvider`,
  `Configuration/ReturnAddressConfigurator` (+ `ReturnAddressData` DTO),
  `Callbacks/UpdatedChangelogOn{Cancel,Complete}`.
- Auth: `Services/AuthCode/{AuthCodeFactory, AuthCodeHashGenerator, AuthCodeSecretGenerator,
  AuthCodeExpiryDateCalculator}`.
- Email: `Email/{AuthCodeEmailSender, AuthCodeChannelAwareEmailSender, ReturnFormEmailSender,
  Emails}` (Sylius mailer).
- Generators: `Generator/ReturnNumberGenerator`, `Generator/OrderReturnFormPdfFileGenerator`
  (uses `knp_snappy.pdf`).
- Provider: `Provider/OrderByNumberProvider`.
- Security: `Security/OrderReturnAuthorizer`, `OrderReturnAuthorizerStorage`,
  `Security/Voter/OrderReturnVoter` (modern `supports()`/`voteOnAttribute()`).
- Filesystem: `Filesystem/TemporaryFilesystem`.

## Twig, menus, fixtures

- Twig extensions (`src/Twig/`): `RmaOrderViewExtension`, `RmaProductViewExtension`,
  `RmaReasonChoiceExtension`, `RmaTimeAgoExtension`, `RmaTimeLineExtension`,
  `RmaVerificationPossibilityOfReturnExtension` - all `AbstractExtension` + `TwigFunction`.
- Menu listeners (`src/Ui/Menu/`): `AdminMenuListener` (sylius.menu.admin.main -> Return
  Manager submenu), `AccountMenuListener` (sylius.menu.shop.account -> return history).
- Fixtures (`src/Fixture/` + `src/Fixture/Factory/`): `OrderReturn`, `OrderReturnItem`,
  `OrderReturnReason`, `OrderReturnConsent`, all `AbstractResourceFixture`.

## Routing (`src/Resources/config/routing/`)

- `shop_routing.yml` - `/rma-start`, `/rma-start/{code}`, `/rma-form/{orderNumber}`,
  `/rma-summary/{returnNumber}`, `/rma-success/{returnNumber}`, `/rma-form-print/{returnNumber}`,
  and `/account/return-history*` routes.
- `admin_routing.yml` - Sylius resource routes for order returns, reasons, consents; the show
  page; `cancel`/`complete` state-machine transition routes; and RMA configuration routes.

## Templates & translations

- Templates under `src/Resources/views/{Admin,Shop,Auth,Return,Email,BulkAction}/`.
- Translations: `src/Resources/translations/messages.en.yaml`, `validators.en.yaml`.

## Doctrine migrations

- `src/Migrations/Version20211117090222.php` - initial schema. Registered via
  `PrependDoctrineMigrationsTrait` in the extension.

## Upgrade-sensitive areas (watch during the 1.12 work)

- State machine callbacks and `applyStateMachineTransitionAction` routes.
- Sylius mailer sender API and the `sylius_ui.events` legacy-event blocks.
- Doctrine ORM 2.13 schema validation of the XML mapped-superclasses.
- Sylius grid YAML and menu event names.
- `knp_snappy.pdf` PDF generation.
- PHP 8.2 dynamic-property and Symfony 6.4 deprecations across `src/`.

See `ai/coding-rules.md` for conventions and `ai/tasks/` for the upgrade task breakdown.
