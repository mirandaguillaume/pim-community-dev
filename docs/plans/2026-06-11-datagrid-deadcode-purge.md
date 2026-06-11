# Datagrid dead-code purge (2026-06-11)

Follow-up to the C1 Wave 3 investigation, which removed the dead `grid-views/`
Backbone view (#267). A systematic scan of the `oro/datagrid/*` module palette plus
adversarial verification found a further set of unreachable modules — removed here.

## Why these "extension points" are safe to remove

The `oro/datagrid/{{type}}-cell` / `{{type}}-action` / `requireJSModules` mechanism is
the legacy way EE and plugins extended grids, so "unused in CE" is normally not enough
to delete. Three facts collapse that caution for this fork:

1. **EE is SaaS.** Akeneo Enterprise is now a managed cloud product; no on-premise EE
   layers bundles on a CE install via these extension points anymore.
2. **The drift breaks existing plugins anyway.** This fork has diverged massively from
   upstream CE (AMD→ESM + RSPack frontend, PHP 8.4 / Symfony 6.4 / Doctrine 3 backend,
   PHPSpec removed). Any marketplace plugin needs heavy porting to run here at all —
   so a missing `number-cell` is not what breaks it.
3. **The target plugin architecture is Module Federation, not this registry.** Future
   extensibility federates exposed **React** components (the `ReactCellBase` palette),
   not legacy Backbone cells resolved through the requirejs registry. These modules are
   the world MF replaces.

With no current, EE, or future consumer, an unused legacy extension point is dead code.

## Removed (verified unreachable)

**Frontend (10 modules + 10 requirejs aliases):**
- cells: `number-cell`, `integer-cell`, `date-cell` (no grid sets these `frontend_type`s;
  numeric/date attribute columns format server-side and render as `string`),
  `credentials-cell` (its only consumer, the `api_connection` datagrid, was removed in
  2019 — commit `1a8269fcdd`).
- header cell: `attribute-header-cell` (loaded only when `column.headerCell === 'attribute'`,
  which no CE config sets; EE-shaped, and EE is SaaS).
- actions: `refresh-collection-action`, `reset-collection-action`, `tab-redirect-action`,
  `revoke-action` (no grid emits these action `type`s).
- listener: `callback-listener` (never referenced in any config).

**Backend (PIM-specific orphans):**
- `Extension/Action/Actions/TabRedirectAction.php` + its `actions.yml` service (the file
  held only this dead service, so the file and its `$loader->load('actions.yml')` line
  are removed too).
- `Repository/ClientRepository.php` + its `pim_enrich.repository.client` service. **Note:**
  this is the *datagrid* `ClientRepository` (selected the `api_connection` credentials
  column). It is distinct from the live OAuth `Akeneo\Tool\Bundle\ApiBundle\…\ClientRepository`
  used by `ClientManager` — a confusion a text grep nearly caused, disambiguated via
  Serena (`find_referencing_symbols` → `{}`) plus the `Client` entity mapping (`#[ORM\Entity]`,
  no `repositoryClass`).

## Kept (deliberately)

- `attribute-type-cell` — **live** (the attribute grid, `attribute.yml`,
  `frontend-type: attribute-type`).
- `oro_datagrid.extension.action.type.revoke` PHP service — lives in the **Oro framework**
  bundle (`DataGridBundle`); pruning framework primitives is out of scope. Only the PIM
  `revoke-action.js` is removed.

## Verification method

Mechanical alias-reference scan → type-usage cross-check against all datagrid configs →
adversarial multi-agent verification (dynamic loading, PHP attribute-type→frontend_type
mapping, PHP action metadata, git history, tests) → Serena LSP reference checks for the
PHP symbols. `build-front` (module resolution), `lint-back`/`coupling-back` (PHP), and the
grid Behat suites are the CI backstop.

## Known follow-up (not done here)

`datagrid-builder.js` still maps `cellTypes = {integer, decimal→number, percent→number}`;
those entries now point to removed cells but are inert (no column triggers them). Left
untouched to avoid editing the core builder in a deletion PR.
