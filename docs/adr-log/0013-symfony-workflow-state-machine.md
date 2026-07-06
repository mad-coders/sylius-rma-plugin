# 0013 - Return lifecycle via Symfony Workflow (Sylius 2)

- **Status:** Accepted
- **Date:** 2026-07-07
- **Supersedes:** [0004](0004-return-lifecycle-state-machine.md)

## Context

Sylius 2 replaces the winzou state machine with the Symfony Workflow component behind the
`Sylius\Abstraction\StateMachine` layer (`SyliusStateMachineAbstractionBundle`), whose default
adapter is `symfony_workflow`. The `winzou/state-machine-bundle` is no longer installed by
default, so the `return_status` graph and its consumers (see [0004](0004-return-lifecycle-state-machine.md))
had to move. This is part of the Sylius 2.2 upgrade (docs/upgrade-sylius-2/ROADMAP.md phase 4).

## Decision

Model the `OrderReturn` lifecycle with a **Symfony Workflow state machine**, driven through the
**Sylius state-machine abstraction**.

- **Graph** (`src/Resources/config/config.yml`, `framework.workflows.return_status`): the graph
  name (`return_status`), places and transitions are unchanged from the winzou definition, so
  `OrderReturnInterface::GRAPH`, the status/transition constants and the admin
  `applyStateMachineTransitionAction` routes keep working. `marking_store` is `method` on the
  `orderReturnStatus` property; `supports` is `OrderReturnInterface`.
- **Two same-named `withdraw` transitions.** Symfony Workflow allows several transitions to share a
  name, so `withdraw` is declared twice (`from: draft` and `from: withdrawal_request`). Both
  dispatch the same `workflow.return_status.completed.withdraw` event; the listener distinguishes
  them by the completed transition's from-place, preserving the winzou from-scoped callbacks.
- **Callbacks become workflow listeners.** The winzou `after` callbacks are re-wired as Symfony
  Workflow `completed` event listeners in `Workflow\OrderReturnWorkflowSubscriber`
  (`kernel.event_subscriber`), which delegates to the unchanged `Services\Callbacks\*` services
  (changelog updates and withdrawal notifiers).
- **Consumers use the abstraction.** Controllers and `OrderWithdrawalProcessor` inject
  `Sylius\Abstraction\StateMachine\StateMachineInterface` instead of the winzou
  `SM\Factory\FactoryInterface`; calls become
  `$stateMachine->can($subject, GRAPH, TRANSITION)` / `->apply($subject, GRAPH, TRANSITION)`.

## Consequences

- **Never set `orderReturnStatus` directly.** Drive every status change through a transition via
  the abstraction so the guards and `completed` listeners (audit log, withdrawal e-mails) run.
- New lifecycle behaviour is added as places/transitions in the workflow config plus a `completed`
  listener, not as conditionals in controllers or services.
- The status constants on `OrderReturn` must stay aligned with the workflow place names.
- Because the abstraction fronts the same graph, a host application that swaps the default adapter
  (or reinstalls winzou) does not require code changes in the plugin.
- The order lifecycle (`OrderTransitions::GRAPH`) now also goes through the same abstraction, so
  `OrderWithdrawalProcessor` drives the order-cancel and return-withdraw pair through one service.
