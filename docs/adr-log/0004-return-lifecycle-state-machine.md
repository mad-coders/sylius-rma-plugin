# 0004 - Return lifecycle via winzou state machine

- **Status:** Superseded by [0013](0013-symfony-workflow-state-machine.md) (Sylius 2 uses Symfony Workflow)
- **Date:** pre-upgrade

## Context

An `OrderReturn` moves through distinct stages (created as a draft, submitted, then resolved).
Status could be managed with ad-hoc setter calls or with an explicit state machine.

## Decision

Model the lifecycle with a **`winzou_state_machine`** (`src/Resources/config/config.yml`):
graph `order_return` / `return_status`, property `orderReturnStatus`.

- States: `draft`, `new`, `completed`, `canceled`.
- Transitions: `new` (draft -> new), `complete` (new -> completed),
  `cancel` (draft/new -> canceled).
- Admin transitions run through Sylius `applyStateMachineTransitionAction` routes
  (`src/Resources/config/routing/admin_routing.yml`).
- The `cancel` / `complete` after-callbacks
  (`Services/Callbacks/UpdatedChangelogOn{Cancel,Complete}`) write the `OrderReturnChangeLog`
  audit trail.

## Consequences

- **Never set `orderReturnStatus` directly.** Drive every status change through a transition so
  guards and after-callbacks (audit logging) always run.
- New lifecycle behaviour is added as states/transitions/callbacks in the state machine config,
  not as conditionals scattered in controllers or services.
- The status constants on `OrderReturn` must stay aligned with the state names in config.
