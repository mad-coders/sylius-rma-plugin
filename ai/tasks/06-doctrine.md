# Task 06 - Validate Doctrine + state machine

**Goal:** Confirm the data layer and return state machine work on Sylius 1.12 (doctrine/orm 2.13).

## Steps
- Run schema validation against the rebuilt test app.
- Confirm the `winzou_state_machine.order_return` graph loads and the `new` / `complete` /
  `cancel` transitions plus the two after-callbacks resolve their services.
- Confirm the admin `applyStateMachineTransitionAction` routes load.

## Verify
- `(cd tests/Application && APP_ENV=test bin/console doctrine:schema:validate)` - mapping valid.
- `(cd tests/Application && APP_ENV=test bin/console debug:router | grep order_return)` lists
  the cancel/complete routes.
- A return can transition draft -> new -> completed/canceled (covered by Behat in task 07).
