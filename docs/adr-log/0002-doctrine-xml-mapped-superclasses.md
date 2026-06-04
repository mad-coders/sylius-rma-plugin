# 0002 - Doctrine mapping via XML mapped-superclasses

- **Status:** Accepted
- **Date:** pre-upgrade

## Context

Entities in a Sylius plugin must be overridable by the host application. Mapping can be done
with attributes/annotations on the entity classes or with external XML, and entities can be
concrete or mapped-superclasses.

## Decision

Map all entities as **XML mapped-superclasses** in `src/Resources/config/doctrine/*.orm.xml`.
No annotations or attributes on entity classes. `OrderReturn` is the root aggregate
(`OneToMany` -> `OrderReturnItem`, cascade persist/remove); other entities relate to it as
documented in `ai/architecture.md`.

## Consequences

- Entity classes stay free of persistence metadata, so the host app can extend/override them
  the Sylius way.
- Schema and relations are changed in the XML, not via attributes; keep mappings and entity
  classes in sync.
- Schema evolution ships as migrations, not auto-mapping side effects
  (see [0007](0007-pdf-and-schema-migrations.md)).
- Do not introduce attribute/annotation mapping for new entities - it would split the mapping
  strategy.
