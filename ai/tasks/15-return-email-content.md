# Task 15 - Self-contained, branded, localized RMA e-mails

**Goal:** Make every customer e-mail a complete record of the return - return (RMA) number, order
number, related items, a state-appropriate description, and full refund details - wrapped in an
overridable header/footer for shop branding, in 8 locales. Critical because the return-form PDF is
**off by default**, so the e-mail must carry the information itself. Status: **planned.**
Tracking issue: [mad-coders/sylius-rma-plugin#23](https://github.com/mad-coders/sylius-rma-plugin/issues/23).

## Background
The plugin sends 6 customer e-mails, all defined as minimal `subject` + `body` templates under
`src/Resources/views/Email/` and wired in `src/Resources/config/config.yml:15-34`:
- `authcode_generated` - `authCodeGenerated.html.twig`, sender `src/Email/AuthCodeEmailSender.php`
  (context: `authCode`, `channel`; **no `OrderReturn` exists yet**).
- `return_generated` - `returnFormGenerated.html.twig`, sender `src/Email/ReturnFormEmailSender.php`
  (context: `orderReturn`, `channel`, `returnAddress`; PDF conditionally attached at `:56-72`).
- `withdrawal_{requested,confirmed,fallback,cancelled}` - `withdrawal*.html.twig`, sender
  `src/Email/WithdrawalEmailSender.php` (context: **only** `orderReturn`, `:56`).

Today the bodies render only a greeting + one info line; none show items, refund details, status, or
(except withdrawal) the order number. `returnFormGenerated.html.twig` still tells the customer to
"print attached form" even though `madcoders_rma.return_form_pdf_enabled` defaults to `false`
(`src/DependencyInjection/MadcodersSyliusRmaExtension.php:40`), so no attachment is present.

All the data needed is already on the `OrderReturn` passed to every return e-mail
(`src/Entity/OrderReturn.php`): `getReturnNumber()`, `getOrderNumber()`, `getOrderReturnStatus()`,
`getItems()` (each `OrderReturnItem` has `getProductName()`, `getProductSku()`, `getReturnQty()`),
`getReturnReason()`, `getBankAccountNumber()`, `getAccountHolderName()`, `getBankName()`. The
PDF-enabled flag is exposed to templates via the global Twig function
`madcoders_rma_return_form_pdf_enabled()` (`src/Twig/RmaConfigExtension.php:34`).

## Scope
Re-render all e-mail bodies from existing context (no new data sources); add overridable header/footer
and a shared return-summary partial; localize the e-mail copy. Default behaviour stays backward
compatible (existing greeting/info translation keys and their placeholders are preserved so current
Behat assertions stay valid).

- Every e-mail (all 6) is wrapped by an overridable header partial (top) and footer partial (bottom).
- Every **return** e-mail renders RMA number, order number, status, items (name + SKU + qty, filtered
  to `returnQty > 0`), reason (if set), and **full** refund details (IBAN, account holder, bank name;
  each shown only when present).
- `return_generated` branches its "what to do next" text on the PDF flag (print & return the attached
  form vs "this e-mail is your confirmation").
- `authcode_generated` adds the order number (`authCode.orderNumber`) alongside the code/link; it
  cannot show an RMA number or items (no return yet).
- E-mail copy provided in en, pl, de, fr, it, es, sv, da.

## Implementation outline
- **Partials** (`src/Resources/views/Email/`):
  - `_header.html.twig` + `_footer.html.twig` - overridable branding extension points (minimal
    defaults, guard `{% if channel is defined %}`), overridable at
    `templates/bundles/MadcodersSyliusRmaPlugin/Email/_header.html.twig` / `_footer.html.twig`.
  - `_returnSummary.html.twig` - renders RMA#, order#, status label, items table, reason, and refund
    block from `orderReturn`; optional blocks guarded; email-client-safe inline-styled HTML.
- **Templates:** rewrite each body as header -> content -> footer. `returnFormGenerated` adds the PDF
  branch + summary; the 4 withdrawal templates keep their greeting + existing `info` line + summary;
  `authCodeGenerated` adds the order-number line, no summary.
- **Channel for header/footer:** `WithdrawalEmailSender` resolves and passes `channel`
  (`sylius.repository.channel`->`findOneByCode($orderReturn->getChannelCode())`); update its DI def in
  `src/Resources/config/services/emails.xml:38`. `ReturnFormEmailSender` / `AuthCodeEmailSender` /
  `config.yml` mailer block unchanged.
- **Translations:** keep `src/Resources/translations/messages.en.yaml` as source of truth; add
  `messages.{pl,de,fr,it,es,sv,da}.yaml` for the `madcoders_rma.email.*` subtree - subjects, greetings,
  new `order_return_form.{info_pdf,info_no_pdf}`, `order_return_auth_email.order_info`, and a
  `summary.*` group (return_number, order_number, status, items, item, sku, quantity, reason,
  refund_details, bank_account_number, account_holder_name, bank_name, and `state.*` for
  draft/new/completed/canceled/withdrawal_request/withdrawn). Repoint/remove the misleading
  `order_return_form.info`.
- **Tests:** update `tests/Unit/Email/WithdrawalEmailSenderTest.php` for the new channel dependency and
  `channel` in the send context; extend Behat (`tests/Behat/Context/Ui/Shop/Rma/ReturnSuccessContext.php`,
  `WithdrawalContext.php`) to assert the body contains the return number + an item name/SKU, plus a
  `return_generated` PDF-on/off branch scenario.

## E-mail mockups
Shared summary block (return e-mails), wrapped by header/footer on every e-mail:
```
[ _header.html.twig ]   <- override target (shop branding)

Return number:  RMA-100123-1
Order number:   100123
Status:         New

Items
  Item                      SKU         Qty
  Awesome Mug               MUG-001      2
  Cool T-Shirt (M)          TS-M-007     1

Reason: Damaged on arrival

Refund details
  IBAN:            PL39 1160 0006 1780 0564 6461 8314
  Account holder:  Jan Kowalski
  Bank:            Bank Przykladowy / BREXPLPW

[ _footer.html.twig ]   <- override target (shop signature/links)
```
- **Verification code:** "This verification code is for order #100123." + code + link. No summary.
- **Return confirmation (New):** greeting + (PDF on: print & return the attached form / PDF off: keep
  this e-mail as your confirmation, nothing to print) + summary.
- **Withdrawal requested:** "request received for order #100123, we'll confirm shortly" + summary.
- **Withdrawal confirmed:** "withdrawal confirmed, order cancelled; refund to the details below" + summary.
- **Order withdrawn (unpaid instant):** "order withdrawn; unpaid, nothing further" + summary (refund
  block usually empty).
- **Handled as a return (fallback):** "handled through our standard return process" + summary.

## Out of scope
- Masking bank details (full details shown, per decision) and item prices / refund totals.
- Translating non-e-mail (UI/admin) strings into the new locales.
- Changing the withdrawal flow or PDF generation itself.
- Fixing the pre-existing `return_generated` greeting that renders the return number as `%name%`
  (kept to avoid breaking the current `ReturnSuccessContext` assertion; optional follow-up).

## Verify
- `make phpunit` green (`tests/Unit/Email/*`), `make behat` covers: return e-mail body contains the
  return number + an item name/SKU; PDF-on vs PDF-off instruction branch; withdrawal e-mails carry the
  summary; auth e-mail shows the order number.
- Header/footer override: drop `templates/bundles/MadcodersSyliusRmaPlugin/Email/_footer.html.twig` in
  the test app and confirm the override renders instead of the default.
- Body preview (Twig render / spooled message): header+footer wrap every e-mail; empty-items and
  null-refund degrade gracefully; RMA#/order#/items present in every return e-mail.
- `make phpstan`, `make ecs`, `make rector` green; `bin/console lint:yaml src/Resources/translations`.
