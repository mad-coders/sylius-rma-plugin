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
- Add a pre-shipment eligibility path: order `paymentState === 'paid'` and not yet shipped
  (`shippingState !== 'shipped'`). Pre-shipment is always allowed (no deadline). Keep the
  post-shipment `shippedAt` + deadline rule for already-shipped orders.
- Add a `cancellation_request` state to the `return_status` state machine and the matching
  `OrderReturnInterface` constant + label/translation.
- Pre-shipment requests land in `cancellation_request`. Admin resolves by:
  - confirm cancellation -> `canceled`, or
  - fall back to the return process -> existing `new` flow.
- Send a confirmation e-mail on request creation and a follow-up e-mail on admin
  resolution, reusing the `sylius_mailer` pattern in `src/Email/`.

## Implementation outline
- Eligibility: relax the `STATE_FULFILLED` gate in `Controller/ShopManagementController.php`
  and `Controller/AuthController.php`; add a paid + not-shipped branch. Factor the
  pre/post-shipment decision into a dedicated checker rather than inlining payment/shipping
  checks in controllers.
- Reasons: pre-shipment withdrawal needs no reason; `ChoiceProvider::createAvailableReasons`
  must not short-circuit to `[]` for unshipped-but-paid orders.
- State machine: extend `winzou_state_machine.order_return` in
  `src/Resources/config/config.yml` (new state + transitions + `after` callbacks for
  changelog and e-mail). Add constants to `Entity/OrderReturnInterface.php` and a state
  label template under `Resources/views/Admin/Return/Label/State/`.
- Admin: add resolution buttons in
  `Resources/views/Admin/Return/Show/_headerWidget.html.twig` and routes in
  `Resources/config/routing/admin_routing.yml` (mirror the existing
  `applyStateMachineTransitionAction` cancel/complete routes).
- E-mail: add a sender (pattern of `src/Email/ReturnFormEmailSender.php`), keys in
  `src/Email/Emails.php`, `sylius_mailer.emails` config, templates under
  `Resources/views/Email/`, and subjects in `Resources/translations/messages.en.yaml`.

## Verify
- `make phpstan`, `make ecs`, `make phpunit` green.
- `make behat` covers a new scenario: paid + unshipped order can submit a withdrawal,
  lands in `cancellation_request`, customer gets the confirmation mail; admin confirm ->
  `canceled` and fallback -> `new`, each sending the follow-up mail.
- Already-shipped orders keep the current return behaviour and deadline.
