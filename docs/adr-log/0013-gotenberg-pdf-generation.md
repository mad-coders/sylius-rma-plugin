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
  already present transitively). The request carries explicit `timeout` (15s) / `max_duration`
  (30s) options - PDF generation runs synchronously inside the return-submission request, so a
  hung Gotenberg must fail bounded rather than stall a customer-facing request the way a local
  binary never could. The response body is checked for the `%PDF-` magic bytes before being
  returned, since Gotenberg (or a misconfigured `GOTENBERG_URL` answered by something else
  entirely) can return HTTP 200 with a non-PDF body. Any HTTP-client failure (transport error,
  non-2xx response, or the magic-bytes check) is wrapped in
  `Madcoders\SyliusRmaPlugin\Services\Pdf\PdfGenerationException`, so `PdfGeneratorInterface`'s
  contract is "rendering failed" rather than a leak of Symfony HttpClient's exception hierarchy.
- The rendered HTML never references the logo by filesystem path. `OrderReturnFormPdfFileGenerator`
  inlines it as a base64 `data:` URI at render time (`Symfony\Component\Mime\MimeTypes` for the
  MIME type). Gotenberg renders the template in its own container: an absolute host path (what
  worked under wkhtmltopdf's local `enable-local-file-access`) does not resolve there and is
  additionally blocked by Gotenberg's default file-access deny list - Gotenberg still returns
  HTTP 200 with a structurally valid PDF in that case, silently dropping the logo.
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
- `composer.json` now requires `twig/twig: ^3.21` explicitly. The unrelated Twig-extension
  modernization that landed alongside this change (`#[AsTwigFunction]`, see the "modernize RMA
  Twig extensions" commit) needs `Twig\Attribute\AsTwigFunction` / `Twig\Extension\AttributeExtension`,
  both added in Twig 3.21.0; the transitive floor via `sylius/sylius >=1.12` is only
  `twig/twig: ^2.12 || ^3.3`, which fatals (`Class "Twig\Extension\AttributeExtension" not
  found`) building the `twig` service - taking down every page, not just RMA ones - on an
  existing lock below 3.21. CI cannot see a floor problem like this because every leg runs
  `composer update` (highest available).

This supersedes the rendering-engine choice in [0007](0007-pdf-and-schema-migrations.md); 0007's
Doctrine-migrations decision is unaffected and still stands.

## Consequences

- No host machine or CI runner needs the wkhtmltopdf binary (it already didn't - PDF generation
  was off by default per 0011); enabling PDFs now means pointing `GOTENBERG_URL` at a reachable
  Gotenberg instance instead of installing a binary.
- `GotenbergPdfGenerator` is covered by
  `tests/Unit/Services/Pdf/GotenbergPdfGeneratorTest.php`, using `Symfony\Component\HttpClient\MockHttpClient`
  - no real Gotenberg instance is needed to test the HTTP contract (method, URL, timeout/
    max_duration options, response passthrough, transport/HTTP-error/non-PDF-body wrapping into
    `PdfGenerationException`).
- `tests/Unit/Generator/OrderReturnFormPdfFileGeneratorTest.php` asserts the template context
  handed to Twig carries the logo as a `data:` URI and never a raw filesystem path or a `file://`
  value - a regression test for the silently-dropped logo that needs no container.
- `tests/Unit/Email/ReturnFormEmailSenderTest.php` needed no changes: it mocks
  `OrderReturnFormPdfFileGeneratorInterface` directly and is unaffected by what generates the PDF
  underneath.
- Rendering now depends on network reachability to Gotenberg rather than a local binary; failures
  (bounded by the timeout/max_duration options above) surface as `PdfGenerationException`, which
  `OrderReturnFormPdfFileGenerator::generate()` already declared it could throw (`@throws
  Exception`).
