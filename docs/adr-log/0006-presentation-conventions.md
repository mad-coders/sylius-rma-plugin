# 0006 - Presentation conventions (YAML routing/grids, form idioms, translation keys)

- **Status:** Accepted
- **Date:** pre-upgrade

## Context

Sylius offers several entry points for the same concern (e.g. routing via annotations or YAML,
grids via YAML or PHP). A consistent house style keeps the plugin predictable and overridable.

## Decision

Fix the presentation layer to these conventions:

- **Routing** in YAML under `src/Resources/config/routing/` - no controller route annotations.
- **Grids** in YAML under `src/Resources/config/grids/`, imported via `grids.yaml` ->
  `config.yml`.
- **Forms** use `buildForm()` + `configureOptions(OptionsResolver)` + `getBlockPrefix()`.
- **Twig** extensions extend `AbstractExtension` and register `TwigFunction`/`TwigFilter`.
- **All user-facing strings** are translation keys in `src/Resources/translations/*.yaml`
  (`messages.*`, `validators.*`) - never hardcoded in templates or PHP.

## Consequences

- New pages, grids, and forms follow these idioms so the plugin stays uniform and the host app
  can override the standard Sylius way.
- Adding a label/message means adding a translation key, not inlining text.
- Do not introduce route annotations or PHP-defined grids - it would fragment the conventions.
