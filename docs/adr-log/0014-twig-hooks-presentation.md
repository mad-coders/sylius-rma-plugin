# 0014 - Admin presentation via sylius/twig-hooks and Tabler (Sylius 2)

- **Status:** Accepted
- **Date:** 2026-07-13
- **Supersedes:** the `sylius_ui.events` / template-event parts of [0006](0006-presentation-conventions.md)

## Context

Sylius 2 replaces the SemanticUI admin with a Bootstrap/Tabler admin built on Symfony UX, and
removes the Sylius 1 presentation extension points the plugin relied on: `sylius_ui.events`
template blocks, `sylius_template_event()`, and the Sonata block bridges. Their replacement is
the Sylius Stack **`sylius/twig-hooks`** component. This is part of the Sylius 2.2 upgrade
(docs/upgrade-sylius-2/ROADMAP.md phase 6).

## Decision

Present all admin UI through **sylius/twig-hooks** with **Tabler** markup.

- **Custom (non-crud) admin pages** - the order-return show page and the RMA configuration
  page - are modelled on the admin **dashboard**: the page template extends
  `@SyliusAdmin/shared/layout/base.html.twig` and renders a single
  `{% hook 'sylius_admin.<page>.show' with { ... } %}`. The plugin owns that hook tree in
  `src/Resources/config/twig_hooks.yaml`: it points the `sidebar`/`navbar`/`content`/
  `flashes`/`header` hookables at the shared admin chrome
  (`@SyliusAdmin/shared/crud/common/*`), sets `breadcrumbs: { enabled: false }` (the shared
  breadcrumbs need crud `metadata`/`configuration` a custom page does not have), and adds the
  plugin's own `title`/`actions`/`content` hookables. Hookable templates read their data from
  `hookable_metadata.context.*`, which propagates down the hook tree from the top-level `with`.
- **Injecting into Sylius core pages** uses named vendor hooks, discovered by reading the
  vendor templates (never guessed): the per-product non-returnable checkbox is a hookable on
  `sylius_admin.product.{create,update}.content.form.sections.general`.
- **Resource CRUD** (order-return index, reason and consent CRUD) uses the standard Sylius 2
  crud templates via `templates: "@SyliusAdmin\shared\crud"`; the default Tabler crud form
  renders the resource form (the Sylius 1 `vars.templates.form` override is a no-op in
  Sylius 2 and was removed).
- **Markup** follows Tabler/Bootstrap house style: `card` / `card-header` / `card-title` /
  `card-body`, `badge bg-*-lt` for state labels, `ux_icon('tabler:...')` for icons, and the
  `row`/`row-cards`/`col-*` grid. The admin menu keeps its `sylius.menu.admin.main` listener
  with Tabler icon names.

## Consequences

- New admin content is added as a twig-hooks hookable (template or Symfony UX component), not
  as a `sylius_ui.events` block or a full template override. Integrators extend or disable the
  plugin's hookables by referencing the same hook + name.
- Every Sylius hook name the plugin targets must be verified against the installed vendor
  templates (there is no `debug:twig-hooks` command in this build; use the web profiler
  "Twig Hooks" panel). The names are Sylius-version-sensitive.
- Hookable templates depend on context propagation; the top-level `{% hook %}` must pass the
  data (resource, form, ...) the descendant hookables read.
- The Twig extension services must be tagged `twig.attribute_extension` + `twig.runtime`
  (Symfony's autoconfiguration for `#[AsTwigFunction]`), not `twig.extension`.
