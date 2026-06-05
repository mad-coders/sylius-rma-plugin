# Architecture Decision Log

This folder records the **architectural decisions** for `madcoders/sylius-rma-plugin` - the
load-bearing choices about how the plugin is built, and why. It is the canonical home for
those decisions; `ai/architecture.md` describes the resulting structure, and `ai/tasks/`
records the upgrade work that produced it.

Read these **on demand**, when you need the rationale behind a decision before changing the
area it covers - not as upfront reading for every task.

## Format

Each decision is one file, `NNNN-short-title.md`, using a lightweight template:

```
# NNNN - Title

- **Status:** Accepted | Superseded by [NNNN] | Deprecated
- **Date:** YYYY-MM-DD (or "pre-upgrade" for decisions inherited from the original plugin)

## Context
Why a decision was needed.

## Decision
What we chose.

## Consequences
What this implies for new code (the rules to honour) and any trade-offs.
```

To add one: copy the next number, fill the template, and add a row to the index below.
Decisions are immutable once Accepted - to change one, add a new ADR that supersedes it and
flip the old one's status.

## Index

| ADR | Title | Status |
| :--- | :--- | :--- |
| [0001](0001-sylius-plugin-resource-model.md) | Build as a Sylius plugin using the resource model | Accepted |
| [0002](0002-doctrine-xml-mapped-superclasses.md) | Doctrine mapping via XML mapped-superclasses | Accepted |
| [0003](0003-xml-service-wiring.md) | Service wiring in XML, modular by concern | Accepted |
| [0004](0004-return-lifecycle-state-machine.md) | Return lifecycle via winzou state machine | Accepted |
| [0005](0005-guest-auth-code-and-voter-authorization.md) | Guest auth-code flow + voter-based authorization | Accepted |
| [0006](0006-presentation-conventions.md) | Presentation conventions (YAML routing/grids, form idioms, translation keys) | Accepted |
| [0007](0007-pdf-and-schema-migrations.md) | PDFs via knp_snappy; schema via Doctrine migrations | Accepted |
| [0008](0008-quality-tooling.md) | Quality tooling: PHPStan + ECS + PHPUnit + Behat via Make | Accepted |
| [0009](0009-conventional-commits.md) | Adopt Conventional Commits | Accepted |
| [0010](0010-github-actions-ci.md) | CI on GitHub Actions (replacing Bitbucket Pipelines) | Accepted |
| [0011](0011-return-form-pdf-feature-flag.md) | Return-form PDF generation is opt-in (feature flag, default off) | Accepted |
| [0012](0012-rector-and-php82-modernization.md) | Rector for PHP 8.2 modernization and a baseline-free PHPStan target | Accepted |
