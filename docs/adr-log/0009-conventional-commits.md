# 0009 - Adopt Conventional Commits

- **Status:** Accepted
- **Date:** 2026-06

## Context

The repository's history is free-form: across ~282 commits, none follow a consistent
convention (messages range from `update license in composer.json` to Bitbucket merge messages
and `SYLRMA-XX` issue IDs). That makes history hard to scan, gives no machine-readable signal
of change type or breaking changes, and leaves no basis for an automated CHANGELOG or release
notes. As the project moves onto Sylius 1.12 and a GitHub-based workflow, we want a predictable
commit history.

## Decision

Adopt **[Conventional Commits 1.0.0](https://www.conventionalcommits.org/)** for all new
commits.

- Format: `<type>(<optional scope>): <subject>`, imperative mood, no trailing period.
- Allowed **types**: `feat`, `fix`, `docs`, `refactor`, `test`, `build`, `ci`, `chore`,
  `perf`, `style`, `revert`.
- **Scope** is optional and free-form (e.g. `feat(return): ...`, `ci: ...`).
- **Breaking changes** are flagged with `!` after the type/scope (`feat!: ...`) and/or a
  `BREAKING CHANGE:` footer.
- A human-curated **`CHANGELOG.md`** is maintained in
  [Keep a Changelog](https://keepachangelog.com/) format, with an `[Unreleased]` section that
  collects changes as they land.

**Enforcement is documentation-only for now:** this ADR, a "Commit messages" section in
`docs/CONTRIBUTING.md`, and a `.gitmessage` template (wired by `make install-hooks` via
`git config commit.template .gitmessage`). No `commit-msg` hook or commitlint is added yet to
keep the PHP-centric toolchain free of Node dependencies; a validating `commit-msg` hook may be
added later if discipline proves insufficient.

## Consequences

- New commits must follow the format; the existing pre-1.12 history is left untouched.
- Notable changes are recorded in `CHANGELOG.md` under `[Unreleased]` as part of the same
  change, so releases can be cut by promoting that section.
- Because enforcement is advisory, reviewers should check commit/PR titles during review.
- This pairs with [0008](0008-quality-tooling.md) (Make-driven quality gates) and the planned
  GitHub Actions migration, where commit/PR conventions can later feed automation.
