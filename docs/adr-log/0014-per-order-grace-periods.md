# 0014 - Per-order return grace periods: order-linked storage and a grace-aware deadline checker

- **Status:** Accepted
- **Date:** 2026-10-07

## Context

Issue [#67](https://github.com/mad-coders/sylius-rma-plugin/issues/67) asks for a way to extend a
return reason's deadline for one order (support-agreed exceptions) without raising the deadline for
every customer, with an audit trail and the ability to change or revoke the extension.

Two things in the existing design shaped the solution:

- The plugin had never stored data per order. `OrderReturn` refers to its order by the
  `orderNumber` string, and the change log belongs to a return, not an order.
- The deadline rule sits behind `ReturnDeadlineCheckerInterface::isWithinDeadline($reason,
  $shippedAt)`, an aliased, replaceable service that receives no order. Adding a parameter to it
  would break every application that replaced it.

## Decision

- Grace periods are two new Sylius resources (ADR 0001, XML mapped superclasses per ADR 0002):
  `OrderReturnReasonGracePeriod` (unique per order and reason) and
  `OrderReturnReasonGracePeriodLog` (audit). Both have a real foreign key to `sylius_order` with
  `ON DELETE CASCADE` - the plugin's first FK to the order table - so grace periods and their
  history disappear with the order and cannot point at a renumbered or missing order. The log
  stores the reason by code, so it stays readable after a reason is deleted.
- The deadline extension goes through a new `GraceAwareReturnDeadlineCheckerInterface`, which
  extends `ReturnDeadlineCheckerInterface` with `isWithinDeadlineWithGrace($reason, $shippedAt,
  $graceDays)`. `ReturnReasonEligibilityChecker`, which already has the order, asks a
  `ReturnReasonGracePeriodResolverInterface` for the extra days and uses the grace-aware method
  only when there are extra days and the configured checker supports them.
- Grace days are added to the deadline, never to the shipped date: `DateInterval::$days` is
  unsigned, so a shipped date moved into the future would count as elapsed time.
- The admin panel is a `sylius_ui` block on `sylius.admin.order.show.summary` that embeds the panel
  controller with `render(controller())`, so it can build a real Symfony form while the core order
  page stays untouched. Grant and revoke are POST routes protected by CSRF tokens.

## Consequences

- New per-order data should follow the same pattern: its own resource with an FK to
  `sylius_order`, rather than a string order number or columns on the core order table.
- `ReturnDeadlineCheckerInterface` stays unchanged. A replaced checker keeps working and keeps the
  base deadline until it implements `GraceAwareReturnDeadlineCheckerInterface` (documented in
  UPGRADE.md).
- Deadline enforcement keeps a single seam: everything that offers reasons or the Return button
  goes through `ChoiceProvider::createAvailableReasons()` and `ReturnReasonEligibilityChecker`. New
  eligibility rules belong there, not in controllers or templates.
- The resolver loads an order's grace periods once per request and is reset between requests
  (`ResetInterface`), so the customer order list does not issue one query per order and reason.
- The 2.0 line needs its own admin panel (Twig hook instead of a `sylius_ui` block); the domain,
  services, controllers, forms and translations carry over.
