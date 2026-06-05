# 0003 - Service wiring in XML, modular by concern

- **Status:** Accepted
- **Date:** pre-upgrade

## Context

Symfony services can be wired in XML, YAML, or PHP attributes. The plugin has a sizeable set
of services (business logic, email, generators, providers, menu, auth-code, security voters).

## Decision

Wire services in **XML** under `src/Resources/config/services/`, split into files by concern
(controllers, forms, emails, generators, providers, menu, auth_code, security_voter, ...).
Prefer constructor injection with `autowire`/`autoconfigure` as already used.

## Consequences

- New services are registered in the matching concern file; no service definitions live in PHP
  attributes, and no second wiring format is introduced.
- Dependencies are explicit and injected via the constructor, which keeps services unit-testable
  (see [0008](0008-quality-tooling.md)).
- The modular layout keeps definitions discoverable as the plugin grows.
