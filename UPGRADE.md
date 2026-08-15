# UPGRADE FROM `1.x` TO `2.0`

This release moves the plugin to Sylius 2.2. It is a major upgrade: the template-event system,
the state machine and the PDF renderer all changed. Read this whole section before upgrading a
live shop.

### Platform requirements

- PHP `^8.2`
- Sylius `^2.2`
- Symfony `^6.4 || ^7.x`

### Composer

- `composer require madcoders/sylius-rma-plugin:^2.0`
- `knplabs/knp-snappy-bundle` is no longer required by the plugin. If your application uses it
  for its own PDFs, require it directly.
- New requirements: `symfony/http-client`, `symfony/mime` and `twig/twig: ^3.21`.

### Template events are replaced by Twig hooks

Sylius 2 removed the `sylius_ui.events` / `sylius_template_event()` system. The plugin now
configures [sylius/twig-hooks](https://github.com/Sylius/TwigHooks) in
`src/Resources/config/twig_hooks.yaml`, imported by `Resources/config/config.yml`. If you added
your own blocks to any of the plugin's events, move them to the corresponding hook.

| Sylius 1 event (and block) | Sylius 2.2 hook |
|---|---|
| `sylius.admin.product.tab_details`, block `madcoders_rma_non_returnable` | `sylius_admin.product.create.content.form.sections.general` **and** `sylius_admin.product.update.content.form.sections.general`, hookable `madcoders_rma_non_returnable` |
| `sylius.shop.layout.footer`, block `madcoders_rma_return_link` | `sylius_shop.base.footer.content`, hookable `madcoders_rma_return_link` |
| `madcoders_rma.shop.account.order_return.show.subcontent`, blocks `header` / `summary` | `sylius_shop.madcoders_rma_account_return.show.content.main`, hookables `header` / `details` |
| (no equivalent; the account index was a plain template) | `sylius_shop.madcoders_rma_account_return.index.content` and `.index.content.main` |
| `madcoders_rma.admin.order_return.show.content`, blocks `header` / `breadcrumb` / `content` | `sylius_admin.madcoders_rma_order_return.show.content` (hookable `details`), with the title and actions under `sylius_admin.madcoders_rma_order_return.show.content.header.title_block` |
| `madcoders_rma.admin.configuration.content` | `sylius_admin.madcoders_rma_configuration.show.content` (hookable `details`), title and actions under `sylius_admin.madcoders_rma_configuration.show.content.header.title_block` |

Hook names are verifiable in the running application with `bin/console debug:twig-hooks`; prefer
that over copying names from documentation.

### Removed legacy Sonata bridge events

The `@SyliusUi/Block/_legacySonataEvent.html.twig` bridges are gone along with the events they
re-dispatched. Nothing listens to these any more, and no replacement event is dispatched:

- `madcoders_rma.shop.account.order_return.show.after_content_header`
- `madcoders_rma.shop.account.order_return.show.after_summary`
- `sylius.admin.order_return.show.before_header`
- `sylius.admin.order_return.show.after_header`
- `sylius.admin.order_return.show.after_breadcrumb`
- `sylius.admin.order.show.after_content`
- `sylius.admin.configuration.before_header`
- `sylius.admin.configuration.after_header`
- `sylius.admin.configuration.after_breadcrumb`
- `sylius.admin.configuration.after_content`

If you rendered anything through these, re-attach it as a hookable on the corresponding hook in
the table above.

### State machine: winzou is replaced by symfony/workflow

The `return_status` graph moved from `winzou/state-machine` to `symfony/workflow`, behind Sylius
2's state-machine abstraction. **The graph name, places and transition names are unchanged**, so
`OrderReturnInterface::GRAPH`, the `TRANSITION_*` constants and the `_sylius.state_machine` routes
keep working.

What changes for integrator code is how you obtain and drive the machine:

```php
// 1.x
use SM\Factory\FactoryInterface;

$sm = $this->stateMachineFactory->get($orderReturn, OrderReturnInterface::GRAPH);
if ($sm->can(OrderReturnInterface::TRANSITION_COMPLETE)) {
    $sm->apply(OrderReturnInterface::TRANSITION_COMPLETE);
}

// 2.0
use Sylius\Abstraction\StateMachine\StateMachineInterface;

if ($this->stateMachine->can($orderReturn, OrderReturnInterface::GRAPH, OrderReturnInterface::TRANSITION_COMPLETE)) {
    $this->stateMachine->apply($orderReturn, OrderReturnInterface::GRAPH, OrderReturnInterface::TRANSITION_COMPLETE);
}
```

Inject `Sylius\Abstraction\StateMachine\StateMachineInterface` where you injected
`SM\Factory\FactoryInterface`.

The `winzou_state_machine.callbacks` the plugin used to declare are now Symfony Workflow event
listeners in `Workflow\OrderReturnWorkflowSubscriber`. If you registered your own callbacks
against the `return_status` graph, re-register them as listeners:

| 1.x winzou callback on transition | 2.0 event |
|---|---|
| `cancel` | `workflow.return_status.completed.cancel` |
| `complete` | `workflow.return_status.completed.complete` |
| `request_withdrawal` | `workflow.return_status.completed.request_withdrawal` |
| `withdraw` | `workflow.return_status.completed.withdraw` |
| `fallback_to_return` | `workflow.return_status.completed.fallback_to_return` |

Note that both withdrawal transitions (instant from `draft`, and admin-approved from
`withdrawal_request`) dispatch the same `withdraw` event; distinguish them by the transition's
from-place, as the plugin's own subscriber does.

### Template overrides

Admin templates were rewritten for Sylius 2's Bootstrap/Tabler admin, and the shop account
screens now compose Sylius' own `@SyliusShop/account/common/...` templates through hooks. Template
paths under `@MadcodersSyliusRmaPlugin/` are unchanged in name, but their markup is not. Any
override you carried from 1.x will need re-checking against the new markup, and overrides that
targeted SemanticUI classes will need rewriting.

### Return-form PDF: wkhtmltopdf is replaced by Gotenberg

PDF generation no longer shells out to a local `wkhtmltopdf` binary. It POSTs the rendered HTML to
a [Gotenberg](https://gotenberg.dev/) instance over HTTP.

- Set `madcoders_rma.gotenberg_url` (or the `GOTENBERG_URL` env var, default
  `http://127.0.0.1:3000`) to a reachable Gotenberg instance.
- The feature remains opt-in via `madcoders_rma.return_form_pdf_enabled`, still `false` by
  default. If you never enabled it, there is nothing to do.
- To swap the renderer, decorate or replace
  `Madcoders\SyliusRmaPlugin\Services\Pdf\PdfGeneratorInterface`. Failures now raise
  `Madcoders\SyliusRmaPlugin\Services\Pdf\PdfGenerationException`.

### Twig extensions no longer implement `ExtensionInterface`

The seven `Madcoders\SyliusRmaPlugin\Twig\*` classes declare their functions with the
`#[Twig\Attribute\AsTwigFunction]` attribute instead of extending `Twig\Extension\AbstractExtension`.
All 11 function names and signatures are unchanged, so templates need no edits. Only code that
extended, decorated or type-hinted these classes as Twig extensions is affected.

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
