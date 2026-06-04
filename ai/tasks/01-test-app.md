# Task 01 - Rebuild tests/Application for Sylius 1.12

**Goal:** Replace the test application skeleton with the Sylius-Standard 1.12 one and re-wire
the plugin so the suite boots on Sylius 1.12 / Symfony 6.4 / PHP 8.2.

## Steps
- Regenerate `tests/Application/` from Sylius-Standard `1.12` (Kernel, `config/`, `bundles.php`,
  `.env*`, `webpack.config.js`, `package.json`, `public/`).
- Register the plugin and its deps in `tests/Application/config/bundles.php`:
  `Madcoders\SyliusRmaPlugin\MadcodersSyliusRmaPlugin` and the knp-snappy bundle.
- Import the plugin config and routing in the test app config (`config.yml` import +
  `routing.yml`).
- Preserve `KERNEL_CLASS='Tests\Madcoders\SyliusRmaPlugin\Application\Kernel'` and the existing
  `.env.test` `APP_SECRET`.
- Keep `autoload-dev.classmap` -> `tests/Application/Kernel.php` working.

## Verify
- `composer install` resolves.
- `(cd tests/Application && APP_ENV=test bin/console about)` runs.
- `(cd tests/Application && APP_ENV=test bin/console doctrine:schema:create)` succeeds.
