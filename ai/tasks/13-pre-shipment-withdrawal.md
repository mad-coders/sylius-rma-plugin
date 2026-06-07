# Task 13 - Pre-shipment withdrawal (EU right of withdrawal)

**Goal:** Allow customers to withdraw from a **paid, not-yet-shipped** order, creating a
cancellation request that an admin resolves. Status: **planned.**
Tracking issue: [mad-coders/sylius-rma-plugin#7](https://github.com/mad-coders/sylius-rma-plugin/issues/7).

## Background
EU Consumer Rights Directive 2011/83/EU lets a consumer withdraw from the moment the
contract is concluded, before dispatch. Today returns are gated on
`OrderInterface::STATE_FULFILLED` in `ShopManagementController` / `AuthController` and on
shipment + `shippedAt` in `Services/Reason/ChoiceProvider::createAvailableReasons()`.

## Scope
Pre-shipment eligibility covers any not-yet-shipped order (`shippingState !== 'shipped'`).
Pre-shipment is always allowed (no deadline); already-shipped orders keep the post-shipment
`shippedAt` + deadline rule unchanged. Two pre-shipment paths, by payment state:

- **Paid order**: the withdrawal creates an `OrderReturn` in a new `cancellation_request`
  state; the admin resolves it by confirming cancellation (-> `canceled`) or falling back
  to the normal return process (-> existing `new` flow). Confirmation e-mail on request +
  follow-up e-mail on admin resolution.
- **Unpaid order** (behind feature flag `allow_unpaid_withdrawal`, default **on**): the
  withdrawal cancels the Sylius order and the `OrderReturn` resolves directly to a terminal
  `withdrawn` state - no admin step. One confirmation e-mail ("order cancelled upon
  withdrawal"). When the flag is off, unpaid orders cannot withdraw (current behaviour:
  the customer waits until the order is paid).

State machine: add a `cancellation_request` state and a terminal `withdrawn` state to the
`return_status` graph, with matching `OrderReturnInterface` constants + labels/translations.

## Implementation outline
- Feature flag: add `allow_unpaid_withdrawal` to `DependencyInjection/Configuration.php`
  (`booleanNode(...)->defaultTrue()`) and expose it as parameter
  `madcoders_rma.allow_unpaid_withdrawal` in `MadcodersSyliusRmaExtension`, mirroring
  `return_form_pdf_enabled`. Inject the param where eligibility is decided.
- Eligibility: relax the `STATE_FULFILLED` gate in `Controller/ShopManagementController.php`
  and `Controller/AuthController.php`; add a not-shipped branch that splits on payment state
  (paid -> request flow; unpaid -> auto-cancel flow when the flag is on). Factor the
  pre/post-shipment + paid/unpaid decision into a dedicated checker rather than inlining
  payment/shipping checks in controllers.
- Reasons: pre-shipment withdrawal needs no reason; `ChoiceProvider::createAvailableReasons`
  must not short-circuit to `[]` for unshipped orders.
- State machine: extend `winzou_state_machine.order_return` in
  `src/Resources/config/config.yml` with `cancellation_request` and terminal `withdrawn`
  states + transitions + `after` callbacks (changelog and e-mail). Add constants to
  `Entity/OrderReturnInterface.php` and state label templates under
  `Resources/views/Admin/Return/Label/State/`.
- Unpaid auto-cancel: on the unpaid path, cancel the Sylius order via the core
  `sylius_order` state machine (`cancel` transition) and drive the `OrderReturn` straight
  to `withdrawn` (no admin interaction).
- Admin (paid path only): add resolution buttons in
  `Resources/views/Admin/Return/Show/_headerWidget.html.twig` and routes in
  `Resources/config/routing/admin_routing.yml` (mirror the existing
  `applyStateMachineTransitionAction` cancel/complete routes).
- E-mail: add sender(s) (pattern of `src/Email/ReturnFormEmailSender.php`), keys in
  `src/Email/Emails.php`, `sylius_mailer.emails` config, templates under
  `Resources/views/Email/`, and subjects in `Resources/translations/messages.en.yaml`:
  request-received + resolution mails for the paid path, and a single
  "cancelled upon withdrawal" mail for the unpaid path.

## Verify
- `make phpstan`, `make ecs`, `make phpunit` green.
- `make behat` covers:
  - paid + unshipped order withdraws -> `cancellation_request`, confirmation mail; admin
    confirm -> `canceled` and fallback -> `new`, each sending the follow-up mail;
  - unpaid + unshipped order with `allow_unpaid_withdrawal` on -> Sylius order cancelled,
    `OrderReturn` -> `withdrawn`, single "cancelled upon withdrawal" mail, no admin step;
  - unpaid + unshipped order with the flag off -> withdrawal not offered.
- Already-shipped orders keep the current return behaviour and deadline.
