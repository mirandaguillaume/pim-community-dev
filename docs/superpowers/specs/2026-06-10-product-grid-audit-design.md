# Product Grid Audit (C1 Wave 0) — Design

Date: 2026-06-10
Branch: `audit/product-grid-wave0`
Deliverable: `docs/plans/2026-06-10-product-grid-audit.md` (report — no code changes)

---

## Context

Phase C1 (Backbone→React Product Grid migration, Strangler Fig) is too large for a single spec. The migration is decomposed into waves:

1. Toolbar (pagination, display-selector, column-selector)
2. Cells/formatters (via the existing `reactCell` bridge)
3. Saved views (view-selector + grid-views)
4. Filters (29 datafilter files)
5. Grid engine core (grid.js, body.js, row.js, header.js)
6. Teardown (remove the `datagrid-builder` Backbone mount)

**Wave 0 (this spec) is a read-only audit** that maps the dependencies before any wave starts. Output: a report that decides the wave order and the bridge strategy.

UIBundle React coverage (PR #260) is the existing safety net for the grid title components.

---

## Scope

### Analyzed code (read-only)

| Area | Path | ~Files |
|---|---|---|
| Grid engine + toolbar + views + actions | `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datagrid/**` | ~50 |
| Filters | `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/datafilter/**` | 29 |
| Mount points | `datagrid-builder.js`, `datafilter-builder.js` | 2 |
| State/data | `pageable-collection.js`, `fetcher/`, `saver/`, `remover/` | ~10 |
| Product grid glue | `src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/datagrid/**`, `product/grid/**` | ~15 |

### Out of scope

- Any code modification (audit only)
- Playwright baseline (deferred — candidate for Wave 1 spec)
- Strangler bridge spike (deferred)
- Other grids (job tracker, history) — they share the engine but are not the migration target; noted in the report only where coupling exists.

---

## Method — agent fan-out with adversarial verification

### Phase 1 — 5 parallel analysis dimensions (one read-only agent each)

| # | Dimension | Question answered | Tools |
|---|---|---|---|
| 1 | Module graph | Who imports whom — static (`madge`, imports) AND dynamic (`{{type}}-cell` conventions, `requireContext`, module names injected by backend datagrid YAML configs) | madge, grep, read |
| 2 | Mediator events | Exhaustive matrix: every `mediator.trigger/on`, `Backbone.Events`, channel names; producer(s) → consumer(s) per event | grep, read |
| 3 | State & data flow | `PageableCollection` / `state.js` / `state-listener.js`: who reads, who mutates, URL/localStorage sync | grep, read |
| 4 | DOM/jQuery coupling | Selectors shared across modules; cross-module DOM manipulation (the Strangler Fig poison) | grep, read |
| 5 | Existing React surface | How `reactCell.tsx`, `ProductGalleryRow.tsx`, quickexport mount into Backbone today; the bridge pattern to generalize | read |

### Phase 2 — adversarial verification

Every load-bearing claim (an event flow, a state mutation, a shared selector) is re-verified by an independent skeptic agent instructed to **refute** it by pointing at code. Claims that survive get a `file:line` reference in the report. Refuted claims are dropped or corrected.

### Phase 3 — synthesis

One agent (then my own review) assembles the report.

---

## Deliverable — report structure

`docs/plans/2026-06-10-product-grid-audit.md`:

1. **Zone map** — for each of the 6 zones: files, responsibilities, inbound/outbound dependencies
2. **Mediator event matrix** — event name → producers → consumers (`file:line`)
3. **Top-10 dangerous couplings** — ranked; each with: what breaks if the zone is replaced by React
4. **Recommended wave order** — justified (wave → prerequisites → risk level)
5. **Bridge strategy** — generalize `reactCell` or alternative, with the evidence from dimension 5

## Exit criterion

For each of the 6 zones the report answers, without reopening the code: **"if I replace X with React, what breaks?"**. The wave order is decided → next step is the Wave 1 spec (brainstorm with the report as input).

---

## Constraints

- Read-only: no source modification, no test execution
- No local Jest (machine constraint) — irrelevant here, nothing is run
- The report is committed via PR with auto-merge (docs-only, CI is path-filtered)
