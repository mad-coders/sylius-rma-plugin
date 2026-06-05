# 0011 - Return-form PDF generation is opt-in (feature flag, default off)

- **Status:** Accepted
- **Date:** 2026-06

## Context

The return-form PDF is produced with `knp_snappy.pdf`, which shells out to the **wkhtmltopdf**
binary (see [0007](0007-pdf-and-schema-migrations.md)). wkhtmltopdf is heavy and archived
(no longer maintained), and it is not present on a default CI runner. Because
`ReturnFormEmailSender` generated the PDF *before* sending the confirmation email, the two
"confirmation email is sent" Behat scenarios failed in CI: PDF generation threw, the email was
never sent. The same coupling forces every environment (CI, local, simple deployments) to
install wkhtmltopdf just to send a return confirmation.

## Decision

Make return-form PDF generation **opt-in** behind a single feature flag, **off by default**:

- `madcoders_rma.return_form_pdf_enabled` (boolean, default `false`) - defined in the plugin's
  semantic config (`Configuration.php`), set as a container parameter by the extension, and
  exposed to templates via a Twig function `madcoders_rma_return_form_pdf_enabled()`
  (`RmaConfigExtension`). A Twig function is used rather than a `twig.globals` parameter
  reference, because a global referencing `%madcoders_rma...%` is resolved during
  `MergeExtensionConfigurationPass` before the plugin extension sets the parameter.
- When **off** (default):
  - the confirmation email is sent **without** the PDF attachment (no wkhtmltopdf needed);
  - the print/download endpoints (`ReturnController::printIndex`,
    `ShopManagementController::printAction`) redirect with a flash
    (`madcoders_rma.ui.return.pdf_disabled`) instead of generating;
  - the "Print return form" links/buttons are hidden in the shop templates.
- When **on**, the previous behaviour is restored (PDF generated, attached, downloadable);
  the integrator must provide a working wkhtmltopdf.

## Consequences

- Default installs and CI no longer require wkhtmltopdf; the email scenarios pass with the flag
  off. CI does not install the archived binary.
- Enabling PDFs is a deliberate, documented opt-in:
  ```yaml
  madcoders_rma:
      return_form_pdf_enabled: true
  ```
- Behaviour is covered by `tests/Unit/Email/ReturnFormEmailSenderTest.php` (attachment present
  only when the flag is on), so neither flag state needs wkhtmltopdf to test.
- The confirmation email template wording is unchanged for now; if it references "the attached
  form", that copy may want revisiting when the flag is off.
