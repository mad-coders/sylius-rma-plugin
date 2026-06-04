# 0001 - Build as a Sylius plugin using the resource model

- **Status:** Accepted
- **Date:** pre-upgrade

## Context

The product is an RMA (Return Merchandise Authorization) capability for Sylius shops. It could
be built as a bespoke bundle with hand-rolled controllers and persistence, or it could lean on
Sylius' own resource/grid/factory machinery.

## Decision

Build it as a first-class **Sylius plugin** and express every domain object through the
**Sylius resource model**:

- The bundle uses `SyliusPluginTrait` (`src/MadcodersSyliusRmaPlugin.php`).
- Resources are declared in `src/DependencyInjection/Configuration.php` under the
  `madcoders_rma` root, each with model / interface / controller / factory / repository.
- Use the stock `ResourceController` + `Factory` unless a custom one is genuinely needed
  (e.g. `OrderReturnRepository`, `RmaConfigurationController`).
- Translatable resources (`order_return_reason`, `order_return_consent`) use
  `TranslatableFactory` plus a translation entity.

## Consequences

- New domain objects follow the same resource shape; do not bypass it with ad-hoc controllers
  or repositories that reinvent CRUD, routing, or grids.
- We inherit Sylius CRUD, grids, and routing conventions for free, at the cost of staying
  within Sylius' abstractions and version constraints.
- See also [0003](0003-xml-service-wiring.md) (wiring) and
  [0006](0006-presentation-conventions.md) (routing/grids/forms).
