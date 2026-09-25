# UPGRADE FROM 1.2.x TO 1.3.0

This section covers upgrading the plugin itself from `1.2.x` to `1.3.0`. See
[CHANGELOG.md](CHANGELOG.md) for the full list of changes.

### Platform requirements

- PHP `^8.2`
- Sylius `>=1.12,<1.14` (Sylius 1.13 is now supported)
- `twig/twig` `^3.21` (new explicit requirement)

### Composer

- `composer require madcoders/sylius-rma-plugin:^1.3`
- `knplabs/knp-snappy-bundle` is no longer required by the plugin. If your application does not
  use it elsewhere and it was registered in your app (for example by its Flex recipe: an entry in
  `config/bundles.php` and `config/packages/knp_snappy.yaml`), remove those so the kernel does not
  reference a bundle that is no longer installed.

### Database

Run the plugin migrations:

```bash
php bin/console doctrine:migrations:migrate
```

Four migrations ship in 1.3.0:

- `Version20260612000000` renames the stored return status `cancellation_request` to
  `withdrawal_request`. Update any application code, templates or state machine callbacks that
  reference `cancellation_request`.
- `Version20260615000000` adds `non_returnable` to `sylius_product` (default `0`). To use the
  feature, make your `Product` implement `NonReturnableProductInterface` and use
  `NonReturnableProductTrait` (see README).
- `Version20260621000000` adds `account_holder_name` and `bank_name` to
  `madcoders_rma_order_return`.
- `Version20260723000000` adds `field_type` to `madcoders_rma_order_return_consent` (default
  `external_page`, so existing consents are unchanged).

### Return-form PDF: wkhtmltopdf replaced by Gotenberg

If you enable `return_form_pdf_enabled`, the PDF is now rendered by a
[Gotenberg](https://gotenberg.dev/) instance over HTTP instead of a local wkhtmltopdf binary:

- run Gotenberg as its own service/container and set `GOTENBERG_URL` (or the `gotenberg_url` config
  key) to its base URL; the default is `http://127.0.0.1:3000`;
- the wkhtmltopdf binary is no longer used;
- rendering failures now raise `Madcoders\SyliusRmaPlugin\Services\Pdf\PdfGenerationException`.

See [ADR 0013](docs/adr-log/0013-gotenberg-pdf-generation.md).

### Twig extensions (BC break)

The seven `Madcoders\SyliusRmaPlugin\Twig\*` extension classes no longer extend
`Twig\Extension\AbstractExtension` or implement `getFunctions()`; they expose their functions via
`#[Twig\Attribute\AsTwigFunction]`. Twig function names are unchanged, so templates need no change.
Code that extends, decorates or type-hints these classes as `AbstractExtension` /
`ExtensionInterface` must be updated.

### Behaviour changes

- **Withdrawal form refund details**: the withdrawal form now follows
  `MADCODERS_RMA_REQUIRE_ADDITIONAL_INFORMATION` like the return form. It no longer always asks for
  a bank account number; set the variable to `true` to collect the account number, account holder
  name and bank name on both forms.
- **Auth-code rate limiting**: the code request and verification endpoints are rate limited per
  client IP and order number (backed by `cache.app`) and return HTTP 429 with `Retry-After` when the
  limit is exceeded. Set `MADCODERS_RMA_LIMIT_AUTH_ATTEMPTS=false` to disable it, for example when
  the application already has its own rate limiter.
- **Return-form e-mail recipient**: the return document is always sent to the order's customer; the
  e-mail address submitted on the return form is no longer used as the recipient.
- **Pre-shipment withdrawal**: not-yet-shipped orders are offered withdrawal instead of a return.
  Unpaid orders are withdrawn instantly (the Sylius order is cancelled); set
  `MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL=false` to disable that.

### E-mail templates

All RMA e-mail templates were rewritten and now render through shared `_header` / `_footer` /
`_returnSummary` partials. If you override any plugin e-mail template under
`templates/bundles/MadcodersSyliusRmaPlugin/Email/`, re-check your overrides; for branding, prefer
overriding only `_header.html.twig` and `_footer.html.twig`.

### Translations

Every shipped locale (en, pl, de, fr, it, es, sv, da) now has full translation parity. If you
override plugin translation keys in your application, check them against the new catalogues.

---

# UPGRADE TO Sylius 1.12 (PHP 8.2 / Symfony 6.4)

This release moves the plugin to Sylius 1.12 on PHP 8.2 and Symfony 6.4.

### Platform requirements

- PHP `^8.2`
- Sylius `~1.12.0`
- Symfony `^6.4`

### Composer

- `composer require sylius/sylius:~1.12.0`
- The previous `conflict` on `api-platform/core` was removed (Sylius 1.12 requires it).
- The legacy admin API stack (`friendsofsymfony/oauth-server-bundle`,
  `SyliusAdminApiBundle`) is gone in 1.12; it has been removed from the plugin and its
  test application.
- Tooling was bumped: PHPStan `^2.0` (with `phpstan-baseline.neon`),
  `sylius-labs/coding-standard` `^4.0` (ECS config is now `ecs.php`).

### Code changes required by Symfony 6

- `Voter::supports()` / `voteOnAttribute()` now use typed signatures
  (`string $attribute, mixed $subject): bool`).
- `UserInterface::getUsername()` was replaced by `getUserIdentifier()`.
- The removed `session` service is replaced by `RequestStack`; services that need the
  session now inject `request_stack` and call `getSession()`.
- `AbstractController::getDoctrine()` was removed; controllers inject `ManagerRegistry`
  (the `doctrine` service) instead.

### Test application

The `tests/Application` skeleton was regenerated from Sylius-Standard 1.12 (new Kernel,
security config, `symfony/mailer` + `symfony/messenger`, Webpack Encore). A
`docker-compose.yml` provides MySQL 8 (host port 3307) and headless Chrome for the Behat
JavaScript suite. See `AGENTS.md` and `Makefile` for run commands.

#### Schema update

Run `(cd tests/Application && APP_ENV=test bin/console doctrine:schema:create)` (or
`doctrine:schema:update --force`) to (re)create the test application's database schema.

---

# Legacy Sylius PluginSkeleton upgrade notes

> The sections below are inherited from the Sylius PluginSkeleton and describe upgrading the
> **Sylius** version of the test application (Sylius 1.2 to 1.4). They do not refer to versions
> of this plugin; for upgrading the plugin see [UPGRADE FROM 1.2.x TO 1.3.0](#upgrade-from-12x-to-130).

# UPGRADE FROM `v1.3.X` TO `v1.4.0`

First step is upgrading Sylius with composer

- `composer require sylius/sylius:~1.4.0`

### Test application database

#### Migrations

If you provide migrations with your plugin, take a look at following changes:

* Change base `AbstractMigration` namespace to `Doctrine\Migrations\AbstractMigration`
* Add `: void` return types to both `up` and `down` functions

#### Schema update

If you don't use migrations, just run `(cd tests/Application && bin/console doctrine:schema:update --force)` to update the test application's database schema.

### Dotenv

* `composer require symfony/dotenv:^4.2 --dev`
* Follow [Symfony dotenv update guide](https://symfony.com/doc/current/configuration/dot-env-changes.html) to incorporate required changes in `.env` files structure. Remember - they should be done on `tests/Application/` level! Optionally, you can take a look at [corresponding PR](https://github.com/Sylius/PluginSkeleton/pull/156/) introducing these changes in **PluginSkeleton** (this PR also includes changes with Behat - see below)

Don't forget to clear the cache (`tests/Application/bin/console cache:clear`) to be 100% everything is loaded properly.

### Test application kernel

The kernel of the test application needs to be replaced with this [file](https://github.com/Sylius/PluginSkeleton/blob/1.4/tests/Application/Kernel.php).
The location of the kernel is: `tests/Application/Kernel.php` (replace the content with the content of the file above).
The container cleanup method is removed in the new version and keeping it will cause problems with for example the `TagAwareAdapter` which will call `commit()` on its pool from its destructor. If its pool is `TraceableAdapter` with pool `ArrayAdapter`, then the pool property of `TraceableAdapter` will be nullified before the destructor is executed and cause an error.

---

### Behat

If you're using Behat and want to be up-to-date with our configuration

* Update required extensions with `composer require friends-of-behat/symfony-extension:^2.0 friends-of-behat/page-object-extension:^0.3 --dev`
* Remove extensions that are not needed yet with `composer remove friends-of-behat/context-service-extension friends-of-behat/cross-container-extension friends-of-behat/service-container-extension --dev`
* Update your `behat.yml` - look at the diff [here](https://github.com/Sylius/Sylius-Standard/pull/322/files#diff-7bde54db60a6e933518d8b61b929edce)
* Add `SymfonyExtensionBundle` to your `tests/Application/config/bundles.php`:
    ```php
    return [
        //...
        FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test' => true, 'test_cached' => true],
    ];
    ```
* If you use our Travis CI configuration, follow [these changes](https://github.com/Sylius/PluginSkeleton/pull/156/files#diff-354f30a63fb0907d4ad57269548329e3) introduced in `.travis.yml` file
* Create `tests/Application/config/services_test.yaml` file with the following code and add these your own Behat services as well:
    ```yaml
    imports:
        - { resource: "../../../vendor/sylius/sylius/src/Sylius/Behat/Resources/config/services.xml" }
    ```
* Remove all `__symfony__` prefixes in your Behat services
* Remove all `<tag name="fob.context_service" />` tags from your Behat services
* Make your Behat services public by default with `<defaults public="true" />`
* Change `contexts_services ` in your suite definitions to `contexts`
* Take a look at [SymfonyExtension UPGRADE guide](https://github.com/FriendsOfBehat/SymfonyExtension/blob/master/UPGRADE-2.0.md) if you have any more problems

### Phpstan

* Fix the container XML path parameter in the `phpstan.neon` file as done [here](https://github.com/Sylius/PluginSkeleton/commit/37fa614dbbcf8eb31b89eaf202b4bd4d89a5c7b3)

# UPGRADE FROM `v1.2.X` TO `v1.4.0`

Firstly, check out the [PluginSkeleton 1.3 upgrade guide](https://github.com/Sylius/PluginSkeleton/blob/1.4/UPGRADE-1.3.md) to update Sylius version step by step.
To upgrade to Sylius 1.4 follow instructions from [the previous section](https://github.com/Sylius/PluginSkeleton/blob/1.4/UPGRADE-1.4.md#upgrade-from-v13x-to-v140) with following changes:

### Doctrine migrations

* Change namespaces of copied migrations to `Sylius\Migrations`

### Dotenv

* These changes are not required, but can be done as well, if you've changed application directory structure in `1.2.x` to `1.3` update

### Behat

* Add `\FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle()` to your bundles lists in `tests/Application/AppKernel.php` (preferably only in `test` environment)
* Import Sylius Behat services in `tests/Application/config/config_test.yml` and your own Behat services as well:
    ```yaml
    imports:
        - { resource: "../../../../vendor/sylius/sylius/src/Sylius/Behat/Resources/config/services.xml" }
    ```
* Specify test application's kernel path in `behat.yml`:
    ```yaml
     FriendsOfBehat\SymfonyExtension:
        kernel:
          class: AppKernel
          path: tests/Application/app/AppKernel.php
    ```


# UPGRADE FROM `v1.2.X` TO `v1.3.0`

## Application

* Run `composer require sylius/sylius:~1.3.0 --no-update`

* Add the following code in your `behat.yml(.dist)` file:

    ```yaml
    default:
        extensions:
            FriendsOfBehat\SymfonyExtension:
                env_file: ~  
    ```
    
* Incorporate changes from the following files into plugin's test application:

    * [`tests/Application/package.json`](https://github.com/Sylius/PluginSkeleton/blob/1.3/tests/Application/package.json) ([see diff](https://github.com/Sylius/PluginSkeleton/pull/134/files#diff-726e1353c14df7d91379c0dea6b30eef)) 
    * [`tests/Application/.babelrc`](https://github.com/Sylius/PluginSkeleton/blob/1.3/tests/Application/.babelrc) ([see diff](https://github.com/Sylius/PluginSkeleton/pull/134/files#diff-a2527d9d8ad55460b2272274762c9386))
    * [`tests/Application/.eslintrc.js`](https://github.com/Sylius/PluginSkeleton/blob/1.3/tests/Application/.eslintrc.js) ([see diff](https://github.com/Sylius/PluginSkeleton/pull/134/files#diff-396c8c412b119deaa7dd84ae28ae04ca))
     
* Update PHP and JS dependencies by running `composer update` and `(cd tests/Application && yarn upgrade)`

* Clear cache by running `(cd tests/Application && bin/console cache:clear)`

* Install assets by `(cd tests/Application && bin/console assets:install web)` and `(cd tests/Application && yarn build)`

* optionally, remove the build for PHP 7.1. in `.travis.yml`
