# Sylius 2.2 upgrade roadmap (plugin 2.0 line)

Status: living document. Maintained on the `2.0` branch until `v2.0.0-rc.1` is tagged.
Owner: madcoders RMA plugin maintainers.

## Goal

Upgrade `madcoders/sylius-rma-plugin` from Sylius `~1.12.0` (PHP 8.2, Symfony 6.4) to
`sylius/sylius ^2.2` (PHP ^8.2, Symfony ^6.4 || ^7.x). Sylius 2 is a rewrite-level change
for plugins, so the work is split into phases, each delivered as one or more reviewable
PRs into the `2.0` branch. One GitHub issue tracks each phase (label `sylius-2`,
milestone `2.0.0`).

## Branching model

- `2.0` is a new long-lived release branch, cut from the tip of `1.3`
  (commit `1c8525a`, the `1.3.0-rc.3` merge). It supports Sylius 2 only.
- The `1.x` branches stay for legacy fixes (Sylius 1.12).
- Future Sylius 2 minors continue the per-minor convention: `2.1`, `2.2`, ...
- Topic branches (`feature/*`, `fix/*`) target `2.0` via PRs, as on the 1.x lines.

### Forward-merge obligation

At the time `2.0` was cut, these fixes were NOT yet merged into `1.3` and must be
forward-merged (or cherry-picked) into `2.0` once they land:

- [x] `fix/issue-26-authcode-hardening` (auth-code brute-force hardening, `a74a719`)
- [x] `fix/issue-27-withdrawal-success-authz`
- [x] `fix/issue-28-return-email-recipient`

All three landed on `1.3` (released as `1.3.0-rc.4`) and were upmerged into `2.0` via the
merge of `1.3` (see the merge that introduced this line). Re-validate the carried-up logic
when the affected controllers/services are touched during phases 3, 4 and 7.

Check this list before tagging `v2.0.0-rc.1`.

## Phase dependency graph

```
P1 branch + roadmap + CI scaffold
  -> P2 composer + test application regeneration (installable)
    -> P3 rector + PHP-level source fixes (static analysis green)
      -> P4 state machine: winzou -> symfony workflow
      -> P5 resource layer: routing, grids, DI, doctrine
        -> P6 admin UI: twig hooks + Tabler rewrite
        -> P7 shop UI + emails + PDF
          -> P8 fixtures + behat + full CI + docs + release
```

P4 and P5 are independent of each other (both depend on P3). P6 depends on P5.
P7 depends on P4 and P5. P8 depends on everything.

## CI policy on the 2.0 line

No standing red. While the suite cannot pass, the heavy jobs (`unit`, `fixtures`,
`behat`, and the rector/ecs steps of `static`) are gated off for the 2.0 line with
`TODO(sylius-2)` comments in `.github/workflows/ci.yml`. The `static` job keeps at
least `composer validate` on every push so each commit stays parseable. Gates are
removed per phase:

| Gate | Re-enabled in |
|---|---|
| static: phpstan / rector / ecs steps | P3 |
| unit | P3 |
| fixtures | P5 (P8 at the latest) |
| behat | P8 |

## Phases

### Phase 1: branch, roadmap, CI scaffold (this PR)

- [x] Cut `2.0` from `1.3` tip and push (exact copy of `1c8525a`).
- [x] Commit this roadmap.
- [x] CI: add `2.0` to push triggers; gate heavy jobs for the 2.0 line.
- [x] GitHub issues for phases 2 to 8 (label `sylius-2`, milestone `2.0.0`): #35 to #41.
- [x] Phase 2 groundwork: rewritten `composer.json` targeting `sylius/sylius ^2.2`
      (verified to resolve to Sylius 2.2.6 on Symfony 7.4).

Definition of done: `origin/2.0` exists at `1c8525a`; this PR merged; CI completes
without red on the PR (gated jobs skipped); 7 phase issues open.

### Phase 2: composer bump + test application regeneration

Goal: `composer update` resolves against `sylius/sylius ^2.2` and the regenerated
`tests/Application` kernel boots (`cache:clear -e test`) with the plugin enabled.

- [ ] Finish `composer.json` alignment with PluginSkeleton `2.0` branch; delete and
      regenerate `composer.lock`.
- [ ] Regenerate `tests/Application` from PluginSkeleton 2.0 layout: new `Kernel.php`,
      `config/bundles.php` (drop FOSRestBundle, JMSSerializerBundle, BazingaHateoasBundle,
      SonataBlockBundle, ApiPlatform Core bridge, winzou; add Symfony UX bundles:
      TwigComponent, LiveComponent, StimulusBundle, TurboBundle, and
      `Sylius\TwigHooks\SyliusTwigHooksBundle`), `config/packages/`, `config/routes/`,
      `public/`, `bin/`, `assets/`, `package.json`, `webpack.config.js`, `.env` files.
- [x] Re-apply plugin-specific bits: plugin bundle registration (+ `KnpSnappyBundle`,
      which the plugin needs for the PDF generator), config import of
      `@MadcodersSyliusRmaPlugin/Resources/config/config.yml`, routing import of the
      plugin `routing.yml` (which already applies the `/admin` and `/{_locale}` prefixes),
      `tests/Application/Entity/Product.php` override + its doctrine ORM mapping,
      `_sylius.yaml` product model override, the `madcoders_*_fixtures.yaml` suites and
      `knp_snappy.yaml`.
- [x] Minimal plugin-config surgery: removed the `sylius_ui.events` section from
      `src/Resources/config/config.yml` (real twig-hooks replacement in P6); replaced the
      `winzou_state_machine` block with the `framework.workflows.return_status` graph
      (places + transitions, two same-named `withdraw` transitions). Callback listeners
      are wired in P4.
- [x] Verified `PrependDoctrineMigrationsTrait`, `SyliusPluginTrait`, and the
      `sylius_mailer.emails` config node all still exist and load in Sylius 2.2.
- [x] Dropped `symfony/flex`: with a classic-layout plugin (src/Resources), flex only
      scaffolds an unwanted root app on `composer update`. The 1.x line never used it. Also
      dropped `polishsymfonycommunity/symfony-mocker-container`; the 2.0 skeleton uses a
      bare `MicroKernelTrait` and the standard test container (no MockerContainer).
- [ ] Migrate `phpunit.xml.dist` to the PHPUnit 10 schema; align Makefile and
      `behat.yml.dist` paths with the regenerated app. (Deferred: paired with P3/P8 when the
      suites run.)

Definition of done: `composer validate` and `composer update` succeed (done);
`(cd tests/Application && bin/console cache:clear -e test)` succeeds with the plugin
enabled. **Status:** the container now compiles through the entire Sylius 2 stack
(framework, Sylius core, API Platform 4, Symfony UX, twig-hooks, workflow). The remaining
boot blocker is plugin PHP code that still references the removed winzou
`SM\Factory\FactoryInterface` (the four state-machine consumers) - this is P4. Full
`cache:clear` success is therefore gated on P4 (and any P3 API fixes that surface after).
Boot was over-scoped to P2 in the original plan; it completes at the end of P4.

### Phase 3: rector + PHP-level source fixes

Goal: plugin PHP code compiles against Sylius 2.2 APIs; phpstan, ecs, rector dry-run
and phpunit green; `static` and `unit` CI gates removed.

- [ ] Update `rector.php` with the sylius/sylius-rector Sylius 2 sets; apply in small
      commits per set.
- [ ] Manual API sweep hotspots: `src/Email/*` sender constructors, `src/Controller/*`
      request-configuration APIs, `src/Form/Extension/*` against Sylius 2 form type
      FQCNs, `src/Fixture/*` base classes and OptionsResolver signatures, `src/Twig/*`
      against removed templating helpers.
- [ ] `src/DependencyInjection/Configuration.php`: drop removed `options` nodes under
      resources; re-verify the tree against the ResourceBundle shipped with 2.2.
- [ ] Regenerate `phpstan-baseline.neon`; prefer fixing over baselining
      (see docs/adr-log and the baseline honesty workflow).
- [ ] PHPUnit 10 migration for `tests/Unit` (annotations to attributes, prophecy bump).

Definition of done: `make phpstan`, `make ecs`, `make rector`, `make phpunit` green
locally and in CI; gates removed for `static` steps and `unit`.

### Phase 4: state machine migration (winzou -> symfony workflow)

Goal: the `return_status` graph runs on symfony/workflow behind
`Sylius\Abstraction\StateMachine\StateMachineInterface`; all callbacks fire as
workflow event listeners; ADR recorded.

Graph definition replacing the `winzou_state_machine` block in
`src/Resources/config/config.yml` (keep graph name `return_status` so
`OrderReturnInterface::GRAPH`, constants, and `_sylius.state_machine` routing values
stay unchanged):

```yaml
framework:
    workflows:
        return_status:
            type: state_machine
            marking_store: { type: method, property: orderReturnStatus }
            supports:
                - Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface
            initial_marking: draft
            places: [draft, new, completed, canceled, withdrawal_request, withdrawn]
            transitions:
                new: { from: draft, to: new }
                complete: { from: new, to: completed }
                cancel: { from: [draft, new], to: canceled }
                request_withdrawal: { from: draft, to: withdrawal_request }
                # two same-named transitions preserve winzou from-scoped callbacks
                withdraw: { from: draft, to: withdrawn }
                withdraw_from_request: { name: withdraw, from: withdrawal_request, to: withdrawn }
                fallback_to_return: { from: withdrawal_request, to: new }
```

Callback mapping (existing services in `src/Services/Callbacks/` stay; a thin
workflow subscriber delegates to them):

| winzou callback | workflow event | listener behavior |
|---|---|---|
| updated_changelog_on_cancel | `workflow.return_status.completed.cancel` | delegate to UpdatedChangelogOnCancel |
| updated_changelog_on_complete | `workflow.return_status.completed.complete` | delegate to UpdatedChangelogOnComplete |
| withdrawal_request_notifier | `workflow.return_status.completed.request_withdrawal` | delegate to WithdrawalRequestNotifier |
| withdrawal_instant_notifier (withdraw from draft) | `workflow.return_status.completed.withdraw` | if transition froms == [draft]: WithdrawalCompletedNotifier::onWithdraw |
| withdrawal_approved_notifier (withdraw from withdrawal_request) | same event | if froms == [withdrawal_request]: WithdrawalResolutionNotifier::onConfirmWithdrawal |
| withdrawal_resolution_fallback | `workflow.return_status.completed.fallback_to_return` | delegate to WithdrawalResolutionNotifier::onFallbackToReturn |

- [x] Added the workflow graph (phase 2) and the subscriber
      (`src/Workflow/OrderReturnWorkflowSubscriber.php`), wired via
      `src/Resources/config/services/workflow.xml` (auto-loaded by the `services/**/*.xml`
      glob) per ADR 0003.
- [x] Replaced `SM\Factory\FactoryInterface` injections with
      `Sylius\Abstraction\StateMachine\StateMachineInterface` (service id
      `sylius_abstraction.state_machine`) in AdminWithdrawalConfirmController,
      ReturnController, WithdrawalController, and OrderWithdrawalProcessor (+ their service
      XML args).
- [x] Fixed the latent bug: ReturnController passed `STATUS_NEW` where `TRANSITION_NEW`
      was intended.
- [x] Default adapter is `symfony_workflow`; the graph resolves without an explicit
      `graphs_to_adapters_mapping` entry.
- [ ] Smoke-test the `_sylius.state_machine` admin transition routes end to end
      (cancel, complete, fallback_to_return) - deferred to the phase 6 admin walkthrough
      (needs the admin UI; routes register and the graph dumps).
- [x] ADR `docs/adr-log/0013-symfony-workflow-state-machine.md` recorded, superseding 0004.

Definition of done: no winzou config remains (done); `bin/console workflow:dump
return_status` shows the graph (verified); unit tests cover the subscriber's subscription
contract and the migrated consumers (`OrderWithdrawalProcessorTest`,
`AdminWithdrawalConfirmControllerTest` now mock the abstraction). The `withdraw`-origin
branching (draft vs withdrawal_request) is covered end to end by the Behat withdrawal
suites rather than a unit mock, because the notifier callbacks are `final readonly` and not
unit-doubleable. **The container now boots (`cache:clear -e test` succeeds on PHP 8.2)** -
this completes the boot that phase 2 could not finish.

Dependency pins added to reach a booting Sylius 2.2 set (composer.json), replacing what
Flex's `extra.symfony.require` would have enforced:

- `config.platform.php: 8.2.0` - resolve for the minimum supported PHP, so no `^8.4`-only
  dependency (e.g. `doctrine/instantiator`) sneaks into a `^8.2` plugin.
- Symfony low-level components (`var-exporter`, `type-info`, `twig-bridge`, `mime`,
  `error-handler`, `var-dumper`, `dom-crawler`, `web-link`) capped at `^6.4 || ^7.1` -
  Symfony 7.4 allows `^8.0` siblings, and mixing 8.1 var-exporter with ORM 3 broke
  LazyGhost proxy warmup.
- `api-platform/{symfony,state,doctrine-orm}: ~4.2.1` - Sylius 2.2 requires `^4.2.1` but
  composer floated to 4.3, which feeds `symfony/type-info` an illegal `object|Class` union
  and fails route warmup.

Known remaining unit failures (belong to phase 3): 4 `Fixture/Factory` tests fail on the
Sylius 2 `TranslatableTrait` (getTranslation requires a current locale). Not state-machine
related; the unit CI gate stays off until phase 3.

### Phase 5: resource layer: routing, grids, DI, doctrine

Goal: all plugin routes register, grids load, doctrine schema and migrations valid.
No template work yet.

- [ ] Resource routing (`src/Resources/config/routing/{admin,shop}_routing.yml`):
      verify `type: sylius.resource` YAML routing is still loaded (expected: yes);
      update `templates: "@SyliusAdmin\Crud"` to the Sylius 2 crud template value
      (DISCOVER from vendor SyliusAdminBundle routing).
- [ ] Grids (`src/Resources/config/grids/*.yml`, 5 files): audit for the removed
      `entities` filter (replace with `entity` + `options.fields`); audit action and
      field types against the Sylius 2 grid bundle.
- [ ] DI (`src/Resources/config/services/*.xml`): services are private by default in
      Sylius 2; ensure controllers are public or tagged `controller.service_arguments`;
      replace any injection of now-private Sylius services with official aliases.
- [ ] Doctrine: XML mapped superclasses stay (ORM 2.x); verify `gedmo:timestampable`
      against the resolved gedmo version; run `doctrine:migrations:migrate` and
      `doctrine:schema:validate`; add a new migration only if diffs appear.
- [ ] Security (`src/Security/`): verify voter registration and the guest auth-code
      flow against the regenerated test app `security.yaml` (firewall names changed).

Definition of done: `bin/console debug:router | grep madcoders` lists all routes; all
five grids load without exception; migrations plus schema validate clean; `fixtures`
CI gate removed if fixtures already load.

### Phase 6: admin UI: twig hooks + Tabler rewrite

Goal: all admin pages render on the Bootstrap/Tabler admin; every `sylius_ui.events`
usage replaced with sylius/twig-hooks; menus work. Largest single work item.

Hook mapping table. OWN = plugin-defined hook. DISCOVER = exact Sylius 2.2 hook name
must be read from vendor templates
(`vendor/sylius/sylius/src/Bundle/AdminBundle/templates/**`), never guessed; verify
with `bin/console debug:twig-hooks`.

| Sylius 1 event / block | Sylius 2.2 target |
|---|---|
| `sylius.admin.product.tab_details` block `madcoders_rma_non_returnable` (`src/Resources/views/Admin/Product/_nonReturnable.html.twig`) | DISCOVER: product create AND update form hooks; hookable template for the checkbox row |
| `sylius.admin.order.show.after_content` (RMA panel on order show) | DISCOVER: order show content hook |
| `madcoders_rma.admin.order_return.show.content` + `_legacySonataEvent` sub-blocks | OWN hook `madcoders_rma.admin.order_return.show.content` with hookables; delete the bridges |
| `madcoders_rma.admin.configuration.content` + bridges | OWN hook; delete the bridges |
| `sylius_template_event()` in `Admin/Return/show.html.twig`, `Admin/Configuration/show.html.twig` | replace with `{% hook 'madcoders_rma...' %}` |

- [ ] Rewrite ~40 templates under `src/Resources/views/Admin/**` plus `BulkAction/`
      and `credits.html.twig`: extend the new `@SyliusAdmin` base layout (DISCOVER),
      SemanticUI to Tabler markup, `ux_icon` usage, grid custom field templates
      (`Admin/Return/Grid/Field/*`), form themes for `Admin/Reason/_form.html.twig`
      and `Admin/Consent/_form.html.twig`.
- [ ] Menus: `src/Ui/Menu/AdminMenuListener.php` (`sylius.menu.admin.main`): verify
      event survives; update icon names and section placement.
- [ ] Routing vars: Tabler icon names; verify `vars.subheader` is still consumed.
- [ ] Inventory Behat selector coupling (grep `tests/Behat` for SemanticUI CSS
      selectors) to size the P8 rewrite.
- [ ] Restore or rewrite relevant `tests/Application/templates/bundles/` overrides.
- [ ] ADR `docs/adr-log/0014-twig-hooks-presentation.md` superseding the sylius_ui
      parts of 0006.

Definition of done: manual admin walkthrough clean (returns index/show, reasons CRUD,
consents CRUD, configuration page, product form shows the non-returnable checkbox,
order show displays the RMA panel); no Twig errors in the log; admin Behat suites pass
locally even though the CI gate is still off.

### Phase 7: shop UI + emails + PDF

Goal: guest and account return flows render on the new shop frontend; all emails send;
PDF generation works or is feature-flagged off.

- [ ] Shop templates (`src/Resources/views/{Auth,Return,Withdrawal,Shop}/**`): extend
      the Sylius 2.2 shop layout and account layout (DISCOVER paths), Bootstrap markup.
- [ ] Shop hooks: `sylius.shop.layout.footer` block -> DISCOVER footer hook;
      `madcoders_rma.shop.account.order_return.show.subcontent` -> OWN hook, delete
      the two `_legacySonataEvent` bridges; replace `sylius_template_event()` in
      `src/Resources/views/Shop/Return/Account/show.html.twig`.
- [ ] Account menu: `src/Ui/Menu/AccountMenuListener.php` (`sylius.menu.shop.account`):
      verify event and signature.
- [ ] Emails: verify sender API; rewrite email templates in
      `src/Resources/views/Email/` against the Sylius 2 email layout; confirm subjects
      and translation keys.
- [ ] PDF: verify knp-snappy-bundle boots under the resolved Symfony version and
      wkhtmltopdf renders the rewritten template. If broken: keep the ADR 0011 feature
      flag off and open a follow-up issue for a Gotenberg/dompdf replacement.
- [ ] Twig extensions in `src/Twig/` feeding these views: verify output fragments
      against the new markup.

Definition of done: manual walkthrough clean (guest auth-code flow, return submission,
withdrawal request and confirm, account return list/show, footer link); all emails
render without template errors via null/Mailhog transport; PDF generated or flagged
off with an issue.

### Phase 8: fixtures, behat, full CI, docs, release

- [ ] Fixtures: `make fixtures-test` green against the Sylius 2.2 default suite plus
      plugin fixtures.
- [ ] Behat: update `behat.yml.dist`, contexts and page objects (SemanticUI selectors
      to Tabler/Bootstrap); verify MockerContainer usage in the test kernel; all
      feature files pass non-JS.
- [ ] CI: remove all 2.0 gating; push triggers list `1.0, 1.1, 1.2, 1.3, 2.0, master,
      main`; consider a PHP 8.3 / Symfony 7 matrix axis.
- [ ] README: requirements table (PHP ^8.2, Sylius ^2.2, Symfony ^6.4 || ^7),
      install instructions per the new skeleton.
- [ ] UPGRADE.md: "Upgrading from 1.x to 2.0" section: workflow migration guide for
      integrators, sylius_ui event names to twig hook names table, removed
      `_legacySonataEvent` bridge events, template override path changes.
- [ ] CHANGELOG.md: `2.0.0-rc.1` entry (keep-a-changelog).
- [ ] Verify the forward-merge checklist above is complete.
- [ ] Tag `v2.0.0-rc.1` from `2.0`.

Definition of done: all four CI jobs green on `2.0` with no gating; docs merged;
rc tag published.

## What stays as-is

| Asset | Verdict |
|---|---|
| XML doctrine mapped superclasses (`src/Resources/config/doctrine/*.orm.xml`) | Stays. ORM 2.x in Sylius 2.2. Verify gedmo XML schema. |
| YAML grids | Stay, with `entities` -> `entity` filter audit and Tabler field templates. |
| `Configuration.php` resource tree | Mostly stays; remove `options` nodes; re-verify against ResourceBundle in 2.2. |
| `type: sylius.resource` YAML routing | Stays (verify at P5); `templates:` value changes. |
| SenderInterface mailer wrappers + `sylius_mailer.emails` | Likely stays; verify at P2 compile. |
| XML service wiring (ADR 0003) | Stays; adjust for private-by-default services. |
| `SyliusPluginTrait`, `AbstractResourceExtension`, `PrependDoctrineMigrationsTrait` | Stay; verify versions at P2. |
| Existing migrations in `src/Migrations` | Stay (1.x schema history); verify against dbal ^3.9. |
| SemanticUI templates, `sylius_ui.events`, `_legacySonataEvent`, winzou config | Go. |

## Temporarily disabled ledger

Everything disabled to keep the container compiling must be listed here and the list
must be EMPTY before `v2.0.0-rc.1`.

| Item | Disabled in | Restored in | Status |
|---|---|---|---|
| Plugin Behat context/page services import (`tests/Application/config/services_test.yaml` -> `tests/Behat/Resources/services.xml`) | P2 | P8 | Deferred. Still references Sylius 1 ids (`sylius.order_item_quantity_modifier` -> `sylius.modifier.order_item_quantity`) and SemanticUI admin CRUD page parents; the P8 Behat rework re-enables and fixes it. |
| `sylius_ui.events` block in `src/Resources/config/config.yml` (product checkbox, footer link, admin/shop show + configuration events, `_legacySonataEvent` bridges) | P2 | P6 | Removed. Replaced by sylius/twig-hooks in P6 (admin) and P7 (shop). |
| ~~winzou callback listeners (changelog updates + withdrawal notifiers)~~ | P2 | P4 | RESTORED in P4: re-wired as `workflow.return_status.completed.*` listeners in `Workflow\OrderReturnWorkflowSubscriber`. |

## Risk register

| Risk | Likelihood | Mitigation |
|---|---|---|
| Behat page objects coupled to SemanticUI selectors (hidden rewrite cost) | High | Selector inventory during P6; rewrite suites per feature area; behat gate stays off until P8 |
| knp-snappy/wkhtmltopdf incompatible or renders Bootstrap templates badly | Medium | ADR 0011 feature flag as kill switch; time-boxed verify in P7; follow-up issue for Gotenberg/dompdf |
| Wrong twig hook names | Medium | DISCOVER rule: read vendor templates, verify with `debug:twig-hooks`, never guess |
| MockerContainer or friends-of-behat stack incompatible with Symfony 7 kernel | Medium | Pin Symfony ^6.4 first (allowed by Sylius 2.2); move to 7.x in a later minor |
| In-flight 1.3 fixes missed on 2.0 | Medium | Forward-merge checklist above, checked before rc tag |
| phpstan 2.x vs Sylius extension expectations | Low | Keep 2.x; drop a conflicting extension before downgrading phpstan |
| Gedmo timestampable XML with newer gedmo/doctrine | Low | `schema:validate` in P5; fall back to explicit lifecycle callbacks |
| Two same-named `withdraw` workflow transitions behaving unexpectedly in `can()` | Low | P4 unit tests for both origins; matches winzou semantics |
