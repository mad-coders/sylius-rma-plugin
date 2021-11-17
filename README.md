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
| PHP  | 7.3, 7.4 |
| Sylius | 1.8, 1.9 |

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

* See [How to contribute](docs/CONTRIBUTING.md)

## License

This library is under the [EUPL 1.2](LICENSE) license.

## Credits

![madcoders logo](docs/img/madcoders-logo-slogan.png)

Developed by [MADCODERS](https://madcoders.co)    
Architects of this package:
- [Piotr Lewandowski](https://github.com/plewandowski)
- [Leonid Moshko](https://github.com/LeoMoshko)

<a href="https://www.buymeacoffee.com/madcoders" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>
