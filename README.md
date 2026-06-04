# [Madcoders](https://www.madcoders.co) Sylius RMA Plugin

Sylius RMA (Return Merchandise Authorization) plugin by Madcoders enables customer to create return form and submit return request.

## Features
- return form for guest customers
- return form for signed in customers
- customer is able to select items and quantities from 
- customer selects reason of return
- customer is notified by e-mail about each step in the process
- creates pdf form
- merchant can define return reasons
- return reasons can be limited in time since shipment
- merchant can define terms and conditions that needs to be accepted by customer prior submitting the form
- return management area in admin

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
