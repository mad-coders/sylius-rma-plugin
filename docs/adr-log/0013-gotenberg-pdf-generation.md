# 0013 - Return-form PDF rendering moves from wkhtmltopdf to Gotenberg

- **Status:** Accepted
- **Date:** 2026-08-03

## Context

Return-form PDF generation (opt-in via `madcoders_rma.return_form_pdf_enabled`, see
[0011](0011-return-form-pdf-feature-flag.md)) used `knp_snappy.pdf`, which shells out to the
**wkhtmltopdf** binary ([0007](0007-pdf-and-schema-migrations.md)). wkhtmltopdf is archived
upstream with no further security patches: it embeds an outdated QtWebKit with known unpatched
CVEs, including SSRF and local-file-disclosure vectors, and the plugin's Snappy configuration
additionally enabled `enable-local-file-access`. Rendering merchant- and customer-supplied return
data (items, addresses, reasons) through that engine is a real attack surface for something that
does not need one (see [issue #5](https://github.com/mad-coders/sylius-rma-plugin/issues/5)).

## Decision

Render the PDF via **Gotenberg**, a maintained, container-first HTTP API for document conversion
built on headless Chromium, instead of a local binary:

- `src/Services/Pdf/PdfGeneratorInterface.php` - a single-method interface
  (`generateFromHtml(string $html): string`), following the same interface + default-implementation
  + XML alias pattern already used for the eligibility checkers, so integrators can swap or
  decorate the implementation.
- `src/Services/Pdf/GotenbergPdfGenerator.php` - the default implementation. POSTs the rendered
  HTML as `multipart/form-data` to `{gotenberg_url}/forms/chromium/convert/html` using
  `Symfony\Contracts\HttpClient\HttpClientInterface` (via `symfony/http-client`) and
  `symfony/mime`'s `DataPart`/`FormDataPart` to build the request body, and returns the response
  body (the PDF bytes) directly. Both packages are now explicit `require` dependencies (they were
  already present transitively).
- A **direct HTTP call** was chosen over the official `gotenberg/gotenberg-php` client library:
  that library requires a PSR-18 HTTP client plus PSR-17 message factories as production
  dependencies for every consumer of this plugin, whereas `symfony/http-client` +
  `symfony/mime` are idiomatic for a Symfony plugin and impose no extra dependencies on installs
  that already run a Symfony app.
- `Generator/OrderReturnFormPdfFileGenerator` is otherwise unchanged: same channel lookup, same
  Twig template, same `OrderReturnFormPdf` return value - only the injected generator and the
  method it calls changed, so PDF content/layout is unaffected.
- New config key `gotenberg_url` (`GOTENBERG_URL` env var, default `http://127.0.0.1:3000` -
  Gotenberg's default Docker port), only consulted when `return_form_pdf_enabled` is true.
  `docker-compose.yml` gained a `gotenberg` service (`gotenberg/gotenberg:8`) for local
  development, picked up automatically by `make docker-up-all`.
- `knplabs/knp-snappy-bundle` is removed from `composer.json`, along with its bundle
  registration, package config, and `WKHTMLTOPDF_PATH`/`WKHTMLTOIMAGE_PATH` env vars in the test
  sandbox app.

This supersedes the rendering-engine choice in [0007](0007-pdf-and-schema-migrations.md); 0007's
Doctrine-migrations decision is unaffected and still stands.

## Consequences

- No host machine or CI runner needs the wkhtmltopdf binary (it already didn't - PDF generation
  was off by default per 0011); enabling PDFs now means pointing `GOTENBERG_URL` at a reachable
  Gotenberg instance instead of installing a binary.
- `GotenbergPdfGenerator` is covered by
  `tests/Unit/Services/Pdf/GotenbergPdfGeneratorTest.php`, using `Symfony\Component\HttpClient\MockHttpClient`
  - no real Gotenberg instance is needed to test the HTTP contract (method, URL, response
    passthrough).
- `tests/Unit/Email/ReturnFormEmailSenderTest.php` needed no changes: it mocks
  `OrderReturnFormPdfFileGeneratorInterface` directly and is unaffected by what generates the PDF
  underneath.
- Rendering now depends on network reachability to Gotenberg rather than a local binary; timeouts
  or connection errors surface as the same kind of exception `OrderReturnFormPdfFileGenerator::generate()`
  already declared it could throw.
