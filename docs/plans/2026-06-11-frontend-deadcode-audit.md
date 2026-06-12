# Frontend dead-code audit — findings & why the broad purge was abandoned

After the datagrid dead-code work (#267 grid-views, #268 datagrid modules), I attempted to
extend the purge to the whole legacy frontend: a global orphan scan of every `requirejs.yml`
alias (890 across 13 bundles) + adversarial multi-agent verification of the non-dynamic
candidates. **The broad module purge was abandoned** — grep cannot reliably prove a legacy
frontend module is unreachable, because the frontend has too many runtime/dynamic load paths.

## Why grep can't decide this (the load paths that bit us)

A module's alias appearing nowhere in `*.js/*.ts/*.tsx/*.yml/*.php/*.html` does **not** mean
it's dead. It can still be loaded at runtime by:

1. **`require([...])` inside a `.twig` template** — `page_elements.html.twig` does
   `require(['pim/formupdatelistener'], function(FormUpdateListener){ new FormUpdateListener(...) })`.
   The scan didn't search `.twig`; `build-front` doesn't see a server-rendered require either.
2. **A constructed `{{type}}-X` module name** via `requireContext` — `abstract-action.js` does
   `requireContext('oro/' + action.frontend_type + '-widget')`, so `oro/export-widget` is loaded
   when a datagrid action has `frontend_type: export`. The literal alias string never appears.
   (Same shape as the `{{type}}-cell/action/filter` registry.)
3. **Config-driven `requireContext(module)`** — `form/common/grid.js`, `associations.js`,
   `item-picker.js`, `field-manager.js`, `fetcher-registry.js` resolve modules from form-extension
   / field / fetcher config values.

Both #1 and #2 produced **false-positive deletions** that passed `build-front` (static analysis)
and were only caught by **Behat** (`Uncaught TypeError: FormUpdateListener / ExportAction is not
a constructor`). Two escapes through a supposedly-exhaustive verification is enough evidence that
the method is unsound here.

## What this PR keeps (the only unambiguously-safe part)

**3 datagrid cell templates** orphaned by the C1 Wave 2 cell→React migration — their consumers
(the old Backgrid cell JS) are gone (`enabled-cell` → React #264, `product-and-product-model-image-cell`
→ React #266, `credentials-cell` cell removed #268). Templates are imported statically by the cell
JS (`import template from 'pim/template/datagrid/cell/X'`), not resolved via any `{{type}}` pattern,
and a full search (incl. `.twig`) confirms zero remaining reference. Removing these 3 `.html` files
+ their requirejs aliases is safe.

## Lesson

A reliable frontend dead-code audit on this legacy codebase needs the **webpack/RSPack build
graph** (reachable-module analysis from the real entry points: routes, form_extensions, Twig
`require([...])`, and the `{{type}}-X` `requireContext` sites) — not a grep over source. `build-front`
green is necessary but **not sufficient**; Behat is the runtime backstop. The datagrid purges
(#267/#268) were safe because they were CI-validated (Behat green) and the datagrid `{{type}}`
patterns were explicitly cross-checked; this broad form/job/widget sweep was not, so it is dropped.
