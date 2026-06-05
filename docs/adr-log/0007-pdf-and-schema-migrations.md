# 0007 - PDFs via knp_snappy; schema via Doctrine migrations

- **Status:** Accepted
- **Date:** pre-upgrade

## Context

Two infrastructure concerns need a single, consistent approach: producing the printable return
form (a PDF), and evolving the database schema for a distributable plugin.

## Decision

- **PDF generation** uses **`knp_snappy.pdf`** (wkhtmltopdf), wrapped by
  `Generator/OrderReturnFormPdfFileGenerator`. The host provides `WKHTMLTOPDF_PATH` /
  `WKHTMLTOIMAGE_PATH`.
- **Schema changes** ship as **Doctrine migrations** in `src/Migrations/`
  (initial schema `Version20211117090222.php`), registered via
  `PrependDoctrineMigrationsTrait` in `MadcodersSyliusRmaExtension`.

## Consequences

- New PDF output goes through the existing generator/templating path rather than a new PDF
  library.
- Every schema change is a versioned migration the host app can run - not an ad-hoc
  `schema:update` or auto-mapping side effect. This pairs with the XML mapping in
  [0002](0002-doctrine-xml-mapped-superclasses.md).
- PDF rendering depends on a wkhtmltopdf binary being available in the environment.
