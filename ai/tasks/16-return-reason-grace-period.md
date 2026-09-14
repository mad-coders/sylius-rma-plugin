# Task 16 - Per-order grace period for return reason deadlines

**Goal:** Let an admin grant extra days on a specific return reason for a specific order, so a
support-agreed extension reopens the return window for that one order without raising the reason's
global `deadlineToReturn`. Grants are editable, revocable and fully audited. Status: **planned.**
Tracking issue: [mad-coders/sylius-rma-plugin#67](https://github.com/mad-coders/sylius-rma-plugin/issues/67).

## Background
Each `OrderReturnReason` has a single global `deadlineToReturn` (days since the order's first
shipment, `src/Entity/OrderReturnReason.php`, column `deadline_to_return`). Nothing is stored per order
today: `OrderReturn` links to orders only by the `orderNumber` string and `OrderReturnChangeLog` is per
return, not per order.

Deadline enforcement is a single chain, identical on 1.3 and 2.0:
- `ReturnReasonEligibilityChecker::isEligible($order, $reason)`
  (`src/Services/Reason/ReturnReasonEligibilityChecker.php`) resolves the first shipment's `shippedAt`
  (false when missing) and delegates to
- `ElapsedDaysReturnDeadlineChecker::isWithinDeadline($reason, $shippedAt)`
  (`src/Services/Reason/ElapsedDaysReturnDeadlineChecker.php`): `deadlineToReturn >= $shippedAt->diff(now)->days`.
  Its interface `ReturnDeadlineCheckerInterface` receives no order.
- `ChoiceProvider::createAvailableReasons()` (`src/Services/Reason/ChoiceProvider.php:62-86`) keeps the
  enabled reasons that pass `isEligible`. It feeds:
  - the shop reason `ChoiceType` (`src/Form/Type/ReturnFormType.php:103-123`, an expired reason is
    rejected on submit because it is not in the choices);
  - `RmaVerificationPossibilityOfReturn::verificationForButtonRender()`
    (`src/Services/RmaVerificationPossibilityOfReturn.php`), used by the guest form gate
    (`src/Controller/ReturnController.php:96`), the account "create return" gate
    (`src/Controller/ShopManagementController.php:102`) and the Twig function `rma_order_can_start_rma`
    (`BulkAction/createNewReturn.html.twig`).

The withdrawal flow (`WithdrawalChoiceProvider`) ignores deadlines and is not affected. On 1.3 the plugin
has no block on the Sylius admin order show page; Sylius 1.12/1.13 exposes the
`sylius.admin.order.show.summary` and `sylius.admin.order.show.sidebar` template events for one.

## Scope
Confirmed decisions: grace is per order **and** per reason; it is expressed as extra days on top of
`deadlineToReturn`; audit trail and edit/revoke are in. Assumed: extra days are a whole number 1-365, the
note is optional.

- Admin order show page panel: every enabled reason with base deadline, granted extra days (or none) and
  the resulting last return day from `shippedAt` ("not shipped" when absent).
- Grant (days + optional note), edit and revoke per order + reason; one grace period per pair (granting
  again edits it).
- Reason R is available for order O while elapsed days since `shippedAt` <= `deadlineToReturn + G`
  (deadline 14, G 10: available on day 24, not on day 25). No effect on other orders or other reasons.
- Grace never bypasses the other rules (`fulfilled` state, `shippedAt` present, returnable quantity,
  reason enabled).
- Revocation falls back to the base deadline immediately.
- Audit entry per grant/edit/revoke: action, previous and new extra days, note, admin first/last name,
  timestamp; listed newest first and kept after revocation.
- Admin + CSRF protected; migration only adds storage (existing orders unchanged); copy in en, pl, de,
  fr, it, es, sv, da.

## Implementation outline
- **Entities** (mapped superclasses + XML mapping + Sylius resources, following the existing
  `OrderReturnChangeLog` / `OrderReturnReason` pattern):
  - `OrderReturnReasonGracePeriod` + interface: `order` (FK `sylius_order`), `reason` (FK
    `madcoders_rma_order_return_reason`), `extraDays` int, nullable `note`, timestamps; unique
    `(order_id, reason_id)`; repository method `findOneByOrderAndReason()`.
  - `OrderReturnReasonGracePeriodLog` + interface: `order` FK, `reasonCode` string (survives reason
    deletion), `action` (`granted` / `updated` / `revoked`), `previousExtraDays` nullable, `extraDays`
    nullable, `note`, author first/last name (same shape as `OrderReturnChangeLogAuthor`), `createdAt`.
  - Migration after `src/Migrations/Version20260723000000.php`.
- **Deadline logic:**
  - `ReturnReasonGracePeriodResolverInterface::getExtraDays(OrderInterface, OrderReturnReasonInterface): int`
    (0 when none), Doctrine-backed implementation.
  - Inject it into `ReturnReasonEligibilityChecker`; compute `elapsed days <= deadline + extra` with
    `DateInterval::$days`. Keep `ReturnDeadlineCheckerInterface::isWithinDeadline()` signature unchanged
    (BC for apps that replaced the aliased service); add a grace-aware method/service rather than
    changing it. Wire in `src/Resources/config/services/forms.xml`.
  - Pitfall: do not shift `shippedAt` forward and reuse the existing checker. `DateInterval::$days` is
    never negative, so a future date gives a positive "elapsed" value and wrongly expires the reason.
- **Admin UI:**
  - Block on `sylius.admin.order.show.summary` (or `.sidebar`) under `sylius_ui.events` in
    `src/Resources/config/config.yml`, template under `src/Resources/views/Admin/Order/GracePeriod/`.
  - Form type for extra days (`Range` 1-365, integer) + optional note.
  - Controller + POST routes with CSRF in `src/Resources/config/routing/admin_routing.yml`, scoped under
    the order id (grant/edit, revoke); a service that writes the grace period and the log entry in one
    flush, author from the current admin user.
- **Translations:** keys under `madcoders_rma.admin.grace_period.*` in all 8 `messages.*.yaml` and
  `validators.*.yaml`.
- **Tests:**
  - Unit: resolver; grace-aware check boundaries (`deadline + G` passes, `deadline + G + 1` fails, no
    grace, revoked); extend `tests/Unit/Services/Reason/ReturnReasonEligibilityCheckerTest.php` and
    `ChoiceProviderTest.php`; form type; grant/edit/revoke service writes the right log entries.
  - Behat: admin grants, edits and revokes on the order page with audit entries and the 1-365
    validation; on an order past its deadline a customer regains the Return button and reason after the
    grant (account + guest form) and loses them after revocation.

## Out of scope
- Customer e-mail about the extension and showing the extended deadline in the shop.
- Bulk granting across orders; API endpoints (the plugin defines none).
- Absolute "returnable until" dates or a whole-order grace across all reasons.
- The 2.0 forward-port (reason services are identical; the admin panel becomes a Twig hook, roadmap
  suggests `sylius_admin.order.show.content.sections#right`), tracked separately.

## Verify
- `make phpunit` green, including the new resolver/checker boundary tests.
- `make behat` covers admin grant/edit/revoke + audit list, the 1-365 validation, and the customer
  regaining then losing the Return button and reason on an expired order.
- Test app: `bin/console doctrine:migrations:migrate` then `bin/console doctrine:schema:validate` pass;
  an order without a grace period behaves as before.
- `make phpstan`, `make ecs`, `make rector` green; `bin/console lint:yaml src/Resources/translations`.
