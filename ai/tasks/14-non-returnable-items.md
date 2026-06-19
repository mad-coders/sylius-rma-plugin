# Task 14 - Flag selected products/variants as non-returnable (item-level eligibility)

**Goal:** Let merchants mark selected products/variants as **non-returnable** so those items
are excluded from the customer return request, layered on top of the existing order-level
eligibility. Status: **planned.**
Tracking issue: [mad-coders/sylius-rma-plugin#18](https://github.com/mad-coders/sylius-rma-plugin/issues/18).

## Background
Return eligibility today is decided **per order**, never per product. Whether an order can be
returned depends on order state, shipment, and the per-reason deadline window
(`src/Services/ReturnEligibilityChecker.php:33`,
`src/Services/Reason/ReturnReasonEligibilityChecker.php:38`), and each order item is offered
up to its remaining quantity (`src/Services/MaxQtyCalculator.php:29`, used in
`src/Services/ReturnRequestBuilder.php:110-126`). There is no way to say "this product can
never be returned". Merchants selling perishables, hygiene/sealed goods, made-to-order items
or gift cards must reject such returns manually after the fact.

## Scope
Add an **item-level returnability** check that sits alongside (does not replace) the
order-level checks. A non-returnable variant is removed from the return flow entirely,
regardless of remaining quantity. Default behaviour is unchanged: with nothing configured,
every variant is returnable (backward compatible).

- A non-returnable item is **not** rendered as a selectable line on the customer return form
  (shop and account flows) and is **not** persisted onto an `OrderReturn`.
- An order made up **only** of non-returnable items yields the same "nothing to return"
  outcome as an ineligible order (no empty/partial return created).
- Returnability is independent of quantity - the flag removes the line outright.

## Implementation outline
- **Checker interface:** add `ProductReturnabilityCheckerInterface::isReturnable(ProductVariantInterface): bool`
  with a default implementation; alias the interface to the default in DI so integrators can
  replace/decorate it without editing plugin code (mirror the overridable eligibility-checker
  pattern in the README "Customizations" chapter and the interface-backed service proposed in
  #15).
- **Builder integration:** consult the checker inside `ReturnRequestBuilder::build()` while
  iterating `$order->getItems()` (`src/Services/ReturnRequestBuilder.php:110`), skipping
  non-returnable variants before `new OrderReturnItem()` (`:126`); this also drops them from
  the form's `items` collection (`src/Form/Type/ReturnFormType.php:44`).
- **Flag storage** (pick one in implementation; default = returnable):
  - preferred: a Sylius **product attribute / boolean** (no core entity change,
    merchant-configurable in admin), read by the default checker; or
  - a dedicated boolean on product/variant via a plugin extension.
  Document the chosen mechanism.
- **Server-side guard:** reject a non-returnable variant injected into a submitted form
  (validate in the builder / form handling, not only UI hiding) so it cannot be persisted.
- **Migration:** if storage adds columns, add `src/Migrations/Version<YYYYMMDDHHMMSS>.php`
  that runs cleanly on an existing schema (namespace `Madcoders\SyliusRmaPlugin\Migrations`).
- **Optional messaging:** expose the rule to templates via
  `src/Twig/RmaConfigExtension.php` only if needed to message "some items can't be returned".

## Out of scope
- Reason-specific returnability (returnable for some reasons, not others).
- Per-channel returnability configuration.
- Bulk admin tooling for tagging many products at once (single product/variant flag only).

## Verify
- `make phpstan`, `make ecs`, `make phpunit` green.
- `make behat` covers: a returnable-only order behaves as today; a mixed order shows only the
  returnable lines; an all-non-returnable order offers nothing to return; a submission
  referencing a non-returnable variant is rejected and not persisted.
