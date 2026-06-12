# [Madcoders](https://www.madcoders.co) Sylius RMA Plugin

[![Latest Version](https://img.shields.io/packagist/v/madcoders/sylius-rma-plugin.svg)](https://packagist.org/packages/madcoders/sylius-rma-plugin)
[![CI](https://github.com/mad-coders/sylius-rma-plugin/actions/workflows/ci.yml/badge.svg)](https://github.com/mad-coders/sylius-rma-plugin/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/madcoders/sylius-rma-plugin.svg)](composer.json)
[![License](https://img.shields.io/badge/license-EUPL--1.2-blue.svg)](LICENSE)

Sylius RMA (Return Merchandise Authorization) plugin by Madcoders lets customers create a return
form and submit a return request from a delivered order.

## Features
- return form for both guest and signed-in customers
- customers select the items and quantities to return from a delivered order
- customers choose a return reason for the request
- customers are notified by e-mail at each step of the process
- optional PDF return form (opt-in, off by default - see [below](#optional-enable-the-return-form-pdf))
- merchant-defined return reasons, optionally limited by time since shipment
- merchant-defined terms and conditions the customer must accept before submitting the form
- return management area in the Sylius admin

## Requirements
| | Version |
| :--- | :--- |
| PHP  | 8.2 |
| Sylius | 1.12 |
| Symfony | 6.4 |

## Installation

1. Add as dependency in `composer.json`
```shell
composer require madcoders/sylius-rma-plugin
```

2. Enable plugin in `config/bundles.php`:
```php
Madcoders\SyliusRmaPlugin\MadcodersSyliusRmaPlugin::class => ['all' => true],
```    

3. Import required config in `config/packages/_sylius.yaml` file:
```yaml
imports:
    - { resource: "@MadcodersSyliusRmaPlugin/Resources/config/config.yml" }
```  

4. Import routes `config/routes.yaml` file:
```yaml
madcoders_sylius_rma_plugin:
    resource: "@MadcodersSyliusRmaPlugin/Resources/config/routing.yml"
```
5. Run migrations:
```bash
php bin/console doctrine:migrations:migrate
```

### Optional: enable the return-form PDF

PDF generation (the confirmation-email attachment and the print/download links) is **off by
default** and requires a working [wkhtmltopdf](https://wkhtmltopdf.org/) binary. To enable it:

```yaml
# config/packages/madcoders_rma.yaml
madcoders_rma:
    return_form_pdf_enabled: true
```

See [ADR 0011](docs/adr-log/0011-return-form-pdf-feature-flag.md).

## Returns state machine

Every return form is an `OrderReturn` entity driven by a single
[winzou state machine](src/Resources/config/config.yml) (graph `return_status`,
property `orderReturnStatus`). A form is created in `draft` and then moves through the
graph depending on whether the customer is filing a **return** or **withdrawing**
(cancelling) a pre-shipment order. The same graph also carries the admin-side resolution
of a withdrawal request.

States: `draft`, `new`, `completed`, `canceled`, `withdrawal_request`, `withdrawn`.

### Full graph

```mermaid
stateDiagram-v2
    [*] --> draft: form created

    draft --> new: new
    new --> completed: complete

    draft --> canceled: cancel
    new --> canceled: cancel

    draft --> withdrawn: withdraw
    draft --> withdrawal_request: request_withdrawal
    withdrawal_request --> withdrawn: withdraw
    withdrawal_request --> new: fallback_to_return

    completed --> [*]
    canceled --> [*]
    withdrawn --> [*]
```

### Standard return flow

A customer fills in the return form for a delivered order. On submit the `new` transition
moves the form out of `draft`; an admin then either completes or cancels it.

```mermaid
stateDiagram-v2
    [*] --> draft: customer starts return form
    draft --> new: new (form submitted)
    new --> completed: complete (admin)
    new --> canceled: cancel (admin)
    draft --> canceled: cancel
    completed --> [*]
    canceled --> [*]
```

### Withdrawal (pre-shipment) flow

A withdrawal is a single process that always ends in the terminal `withdrawn` state with the
underlying Sylius order cancelled. Whether it gets there instantly or via admin approval is the
only difference, decided by two checkers:

- [`WithdrawalEligibilityChecker::isWithdrawable()`](src/Services/Withdrawal/WithdrawalEligibilityChecker.php)
  - is the withdrawal flow offered at all? (placed, not shipped, not a cart; unpaid only when the
  `allow_unpaid_withdrawal` flag is on).
- [`InstantCancellationEligibilityChecker::isEligible()`](src/Services/Withdrawal/InstantCancellationEligibilityChecker.php)
  - true when the order is **not paid**, so it can be withdrawn instantly with nothing to refund.

When instant-eligible (unpaid), the form fast-forwards straight to `withdrawn` via the `withdraw`
transition and the Sylius order is cancelled. Otherwise (paid/authorized) the `request_withdrawal`
transition raises a `withdrawal_request` that an admin resolves, either confirming the withdrawal
(`withdraw` -> `withdrawn`, cancelling the Sylius order) or handling it as a normal return
(`fallback_to_return` -> `new`).

```mermaid
stateDiagram-v2
    [*] --> draft: customer requests withdrawal
    state instant_eligible <<choice>>
    draft --> instant_eligible
    instant_eligible --> withdrawn: withdraw\n(unpaid, order cancelled)
    instant_eligible --> withdrawal_request: request_withdrawal\n(paid, needs approval)
    withdrawal_request --> withdrawn: withdraw (admin confirm, order cancelled)
    withdrawal_request --> new: fallback_to_return (admin, handle as return)
    withdrawn --> [*]
```

### Transitions and notifications

Several transitions fire `after` callbacks (changelog updates and customer e-mails),
configured in [`config.yml`](src/Resources/config/config.yml). The `withdraw` transition uses
winzou `from`-filtered callbacks so the instant (customer) and admin-approved cases send different
notifications:

| Transition | From | To | After callback |
| :--- | :--- | :--- | :--- |
| `new` | `draft` | `new` | - |
| `complete` | `new` | `completed` | changelog update |
| `cancel` | `draft`, `new` | `canceled` | changelog update |
| `request_withdrawal` | `draft` | `withdrawal_request` | withdrawal-requested e-mail |
| `withdraw` | `draft` | `withdrawn` | instant withdrawal e-mail (customer) |
| `withdraw` | `withdrawal_request` | `withdrawn` | resolution (confirmed) e-mail (admin) |
| `fallback_to_return` | `withdrawal_request` | `new` | resolution (fallback) e-mail |

## Development

Requires PHP 8.2, Composer, Docker (for the database) and Node/Yarn (for the test
application assets). Run `make help` to list every available command.

### Quick start

```bash
make setup        # composer install + start MySQL (docker) + build assets + create schema
make test         # PHPUnit + non-JS Behat
make static       # PHPStan + ECS
```

`make setup` is a one-shot bootstrap. It is equivalent to:

```bash
make install      # composer install
make docker-up    # start the MySQL 8 container on host port 3307
make frontend     # yarn install + encore build + assets:install
make backend      # create the test database and schema
```

### Docker

The bundled `docker-compose.yml` provides the services the test suite needs:

```bash
make docker-up        # MySQL 8 only (host port 3307, to avoid a local MySQL on 3306)
make docker-up-all    # MySQL 8 + headless Chrome (for the @javascript Behat suite)
make docker-down      # stop and remove the containers
```

The test application reads `DATABASE_URL=mysql://root:rma@127.0.0.1:3307/...` from
`tests/Application/.env`.

### Tests

```bash
make phpunit          # unit / component tests
make behat            # non-JavaScript Behat suite
make behat-js         # JavaScript Behat suite (needs `make docker-up-all` + a running server)
```

### Static analysis & code style

```bash
make static           # phpstan + ecs (no changes)
make fix              # auto-fix code style with ECS
```

PHPStan runs against a committed baseline (`phpstan-baseline.neon`) so only new
issues fail the build.

### Pre-commit hook

A git hook auto-fixes code style and verifies static analysis and unit tests before each
commit. Install it once per clone:

```bash
make install-hooks    # sets git core.hooksPath to .githooks
```

On every `git commit` it runs `make pre-commit`, which:

1. runs `ecs --fix` on staged PHP files and re-stages the result,
2. runs PHPStan, ECS and PHPUnit, aborting the commit if any of them fail.

Bypass it in an emergency with `git commit --no-verify`.

### Commit messages & changelog

Commits follow [Conventional Commits](https://www.conventionalcommits.org/)
(see [ADR 0009](docs/adr-log/0009-conventional-commits.md)); `make install-hooks` also installs
the `.gitmessage` template. Notable changes are recorded in [CHANGELOG.md](CHANGELOG.md) under
`[Unreleased]`.

* See also [How to contribute](docs/CONTRIBUTING.md)

## License

This library is under the [EUPL 1.2](LICENSE) license.

## Credits

![madcoders logo](docs/img/madcoders-logo-slogan.png)

Developed by [MADCODERS](https://madcoders.co)    
Architects of this package:
- [Piotr Lewandowski](https://github.com/plewandowski)
- [Leonid Moshko](https://github.com/LeoMoshko)

<a href="https://www.buymeacoffee.com/madcoders" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>
