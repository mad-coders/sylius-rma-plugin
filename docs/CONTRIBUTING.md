# Contribution guides

Please note that before we can accept your merge requests we need you to accept contribution terms and conditions before contributing to this project. Contact us for details floss@madcoders.co 

[comment]: <> (Visit: &#40;https://contribution.madcoders.co&#41;)

All common operations are wrapped as `make` targets. Run `make help` to list every available command with a short description.

## Quickstart Installation

1. Fork the project: https://github.com/mad-coders/sylius-rma-plugin

2. Configure your database credentials in `tests/Application/.env` and `tests/Application/.env.test`.

3. From the plugin root directory, run the one-shot setup (installs dependencies, starts the MySQL container, builds the test application assets and creates the database):

    ```bash
    make setup
    ```

    If you run MySQL yourself and don't want Docker, use `make init` instead (deps + db + assets, no Docker).

## Usage

### Opening Sylius with the plugin

The local server runs over HTTPS (`https://127.0.0.1:8080`). Install the local CA once so the certificate is trusted:

```bash
symfony server:ca:install
```

Load the fixtures and start the server:

```bash
make fixtures
make serve
```

`make app` is a convenience target that brings up Docker, rebuilds the assets, resets the schema and reloads fixtures in one step; follow it with `make serve`.

### Running tests

Run the whole suite (PHPUnit + non-JS Behat):

```bash
make test
```

Or run each type individually:

- **PHPUnit** - unit and component tests:

    ```bash
    make phpunit
    ```

- **Behat (non-JavaScript scenarios)** - functional scenarios that don't need a browser:

    ```bash
    make behat
    ```

- **Behat (JavaScript scenarios)** - scenarios driven through a real browser. They need a headless Chrome and a running web server:

    1. [Install the Symfony CLI command](https://symfony.com/download) (only once needed).

    2. Start MySQL and headless Chrome:

        ```bash
        make docker-up-all
        ```

    3. Start the test application's web server in the `test` environment (in a separate
       terminal, keep it running). This serves on `https://127.0.0.1:8081` against the test
       database, which is where the JavaScript suite points (`behat.yml.dist`), so it does not
       touch your `make serve` dev database:

        ```bash
        make serve-test
        ```

    4. Run the JavaScript suite:

        ```bash
        make behat-js
        ```

### Static analysis & code style

Run all static analysis and code style checks at once:

```bash
make static
```

Or run each tool individually:

- **PHPStan** - static analysis:

    ```bash
    make phpstan
    ```

- **ECS (Easy Coding Standard)** - code style check (reports violations, makes no changes):

    ```bash
    make ecs
    ```

    To automatically fix the reported style violations:

    ```bash
    make fix
    ```

### Quality gate & git hooks

Before pushing, run the full quality gate (static analysis + unit tests, no fixes):

```bash
make verify
```

To auto-fix style first and then verify, use `make pre-commit`. You can wire this into git so it runs on every commit (this also sets the commit-message template, see below):

```bash
make install-hooks
```

`make ci` runs the complete pipeline (install, static analysis and all test suites) the same way CI does.

## Commit messages

This project uses [Conventional Commits](https://www.conventionalcommits.org/) - see
[ADR 0009](adr-log/0009-conventional-commits.md). Each commit subject is
`<type>(<optional scope>): <subject>`, in the imperative mood and without a trailing period.

- **type**: one of `feat`, `fix`, `docs`, `refactor`, `test`, `build`, `ci`, `chore`, `perf`,
  `style`, `revert`.
- **breaking changes**: add `!` after the type/scope (`feat!: ...`) and/or a
  `BREAKING CHANGE:` footer.
- Record notable changes in [`CHANGELOG.md`](../CHANGELOG.md) under `[Unreleased]` as part of
  the same change.

`make install-hooks` sets the `.gitmessage` template as your commit template, so `git commit`
opens with the format and a type cheat-sheet. Enforcement is by convention and review; there is
no validating hook yet.

Examples:

```
feat(return): add bulk cancel action to the admin grid
fix(auth): reject expired auth codes
docs: document the commit convention
```
