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
- pre-shipment withdrawal: a not-yet-shipped order can be withdrawn (cancelled) instead of returned -
  instantly when unpaid, or via admin approval with item selection (partial withdrawals) when paid -
  see [Returns state machine](#returns-state-machine)
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

## Configuration

All settings live under the `madcoders_rma` key (`config/packages/madcoders_rma.yaml`) and are
optional; the defaults below match the plugin's out-of-the-box behaviour.

| Setting | Env var | Type | Default | Effect |
| :--- | :--- | :--- | :--- | :--- |
| `return_form_pdf_enabled` | - | bool | `false` | Generate the return-form PDF (email attachment + print/download links); requires wkhtmltopdf. See [below](#optional-enable-the-return-form-pdf). |
| `allow_unpaid_withdrawal` | `MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL` | bool | `true` | Offer instant withdrawal of unpaid, not-yet-shipped orders (cancels the Sylius order). When `false`, unpaid orders are not offered withdrawal. See [below](#optional-instant-withdrawal-of-unpaid-orders). |
| `require_additional_information` | `MADCODERS_RMA_REQUIRE_ADDITIONAL_INFORMATION` | bool | `false` | Show and require the return form's "Additional information" section (bank account number, account holder name, bank name / BIC-SWIFT) for refund handling. When `false`, the section is hidden and not required. See [below](#optional-require-additional-information-on-the-return-form). |
| `resources.*` | - | map | Sylius defaults | Standard Sylius ResourceBundle overrides (model / interface / controller / factory / repository / form) for the plugin's entities. |

Store data managed in the **Sylius admin** rather than config files: the return address per channel,
return reasons (with optional time-since-shipment deadlines), and the consents a customer must accept.

### Optional: enable the return-form PDF

PDF generation (the confirmation-email attachment and the print/download links) is **off by
default** and requires a working [wkhtmltopdf](https://wkhtmltopdf.org/) binary. To enable it:

```yaml
# config/packages/madcoders_rma.yaml
madcoders_rma:
    return_form_pdf_enabled: true
```

See [ADR 0011](docs/adr-log/0011-return-form-pdf-feature-flag.md).

### Optional: instant withdrawal of unpaid orders

When a not-yet-shipped order is **unpaid**, it is withdrawn instantly (the Sylius order is cancelled
and the return resolves straight to `withdrawn`, with no admin step). This is **on by default**.
Paid orders are always withdrawable via admin approval regardless of this flag.

Toggle it with the `MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL` environment variable:

```dotenv
# .env
MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL=false
```

or override the parameter directly:

```yaml
# config/packages/madcoders_rma.yaml
madcoders_rma:
    allow_unpaid_withdrawal: false
```

When disabled, an unpaid order is not offered the withdrawal flow at all.

### Optional: require additional information on the return form

The return form can collect the bank details needed to handle a refund: **bank account number**
(validated as an IBAN), **account holder name**, and **bank name / BIC-SWIFT**. This "Additional
information" section is **off by default** - the section is hidden and none of its fields are
required, so a customer can submit a return without bank details.

Enable it with the `MADCODERS_RMA_REQUIRE_ADDITIONAL_INFORMATION` environment variable:

```dotenv
# .env
MADCODERS_RMA_REQUIRE_ADDITIONAL_INFORMATION=true
```

or override the parameter directly:

```yaml
# config/packages/madcoders_rma.yaml
madcoders_rma:
    require_additional_information: true
```

When enabled, the section is rendered on the return form and all three fields are required. The
values are persisted on the `OrderReturn` and shown (when present) in the admin and shop account
return views. This flag does not affect the withdrawal flow, which always collects the bank account
number regardless of the setting.

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

A withdrawal always ends in the terminal `withdrawn` state. Whether it gets there instantly (with
the whole Sylius order cancelled) or via admin approval is decided by two checkers:

- [`WithdrawalEligibilityChecker::isWithdrawable()`](src/Services/Withdrawal/WithdrawalEligibilityChecker.php)
  - is the withdrawal flow offered at all? (placed, not shipped, not a cart; unpaid only when the
  `allow_unpaid_withdrawal` flag is on).
- [`InstantCancellationEligibilityChecker::isEligible()`](src/Services/Withdrawal/InstantCancellationEligibilityChecker.php)
  - true when the order is **not paid**, so it can be withdrawn instantly with nothing to refund.

When instant-eligible (unpaid), the customer confirms in one click and the form fast-forwards
straight to `withdrawn` via the `withdraw` transition, cancelling the whole Sylius order.

When paid/authorized, the customer instead sees the **same item-selection screen as a standard
return** ([`WithdrawalReturnFormType`](src/Form/Type/WithdrawalReturnFormType.php) over
`Return/view.html.twig`) and can choose which items/quantities to withdraw - a **partial withdrawal**
is allowed. Submitting raises a `withdrawal_request` that an admin resolves: confirming it
(`withdraw` -> `withdrawn`) records the withdrawal but **does not** cancel the Sylius order, because
the request may be partial - the refund and any order cancellation stay manual admin actions; or
handling it as a normal return (`fallback_to_return` -> `new`). Either way the withdrawn quantities
are claimed and cannot be returned again (`MaxQtyCalculator` counts every non-`draft` return).

```mermaid
stateDiagram-v2
    [*] --> draft: customer requests withdrawal
    state instant_eligible <<choice>>
    draft --> instant_eligible
    instant_eligible --> withdrawn: withdraw\n(unpaid, whole order cancelled)
    instant_eligible --> withdrawal_request: request_withdrawal\n(paid, item selection, needs approval)
    withdrawal_request --> withdrawn: withdraw (admin confirm, order NOT cancelled - manual refund)
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

## Customizations

Which RMA path an order is offered - return, withdrawal, or instant withdrawal - is decided by small
single-method **eligibility checkers**. Each one is bound to an interface (with a default service
alias) and is consumed everywhere through that interface (controllers, the
[`rma_order_can_start_rma()`](#returns-state-machine) Twig function, the reason provider), so you can
change a rule for the whole plugin by pointing the alias at your own implementation.

| Interface (`Madcoders\SyliusRmaPlugin\Services\...`) | Method | Decides | Default service id (`madcoders.sylius_rma_plugin.services...`) |
| :--- | :--- | :--- | :--- |
| `ReturnEligibilityCheckerInterface` | `isReturnable(OrderInterface)` | whether a (post-shipment) order can be returned | `.return_eligibility_checker` |
| `Withdrawal\WithdrawalEligibilityCheckerInterface` | `isWithdrawable(OrderInterface)` | whether a (pre-shipment) order is offered withdrawal at all | `.withdrawal.eligibility_checker` |
| `Withdrawal\InstantCancellationEligibilityCheckerInterface` | `isEligible(OrderInterface)` | within withdrawal, instant (unpaid) vs admin approval (paid) | `.withdrawal.instant_cancellation_eligibility_checker` |
| `Reason\ReturnReasonEligibilityCheckerInterface` | `isEligible(OrderInterface, OrderReturnReasonInterface)` | whether a given return reason is offered (deadline since shipment) | `.reason.return_reason_eligibility_checker` |

To replace one, implement its interface and alias it in your application:

```yaml
# config/services.yaml
services:
    App\Rma\MyWithdrawalEligibilityChecker: ~

    # take over the rule everywhere it is used
    Madcoders\SyliusRmaPlugin\Services\Withdrawal\WithdrawalEligibilityCheckerInterface:
        alias: App\Rma\MyWithdrawalEligibilityChecker
```

Prefer to keep the default behaviour and only add to it? Decorate the default service instead:

```yaml
services:
    App\Rma\MyWithdrawalEligibilityChecker:
        decorates: madcoders.sylius_rma_plugin.services.withdrawal.eligibility_checker
        arguments: ['@.inner']
```

Note that the default `WithdrawalEligibilityChecker` receives the
[`allow_unpaid_withdrawal`](#configuration) flag as a constructor argument; a full replacement is
responsible for honouring that flag itself if it still applies.

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
