# Product Grid Audit (C1 Wave 0) — Execution Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking. NOTE: this is a read-only research plan — tasks dispatch analysis agents and write ONE report file; there is no TDD cycle.

**Goal:** Produce `docs/plans/2026-06-10-product-grid-audit.md` — the dependency map of the Backbone Product Grid that decides the C1 wave order and bridge strategy.

**Architecture:** Three workflow phases: (1) five parallel read-only dimension scans, (2) adversarial verification of every load-bearing claim, (3) synthesis into the report. No source file is modified; the only artifact is the report.

**Tech Stack:** Claude Workflow tool (agent fan-out), madge, grep/ripgrep, Read. No Jest, no PHP, no build.

---

## Constraints (apply to every task)

- **Read-only**: agents must NOT modify any file under `src/`. The only Write target is the report file.
- **No test execution** (no Jest — machine constraint; nothing needs running anyway).
- Every claim that enters the report needs a `file:line` reference that survived verification.
- Scope paths:
  - `src/Oro/Bundle/PimDataGridBundle/Resources/public/js/**` (datagrid/, datafilter/, fetcher/, saver/, remover/, *-builder.js, pageable-collection.js)
  - `src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/datagrid/**`
  - `src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/product/grid/**`

---

## Task 0: Branch (already done)

- [x] Branch `audit/product-grid-wave0` created from master, spec committed (`eefc7632b0`).

---

## Task 1: Dimension scans — 5 parallel agents

**Files:** none created (agents return structured JSON to the orchestrator).

Each agent receives: the scope paths above, its dimension brief below, and this output schema:

```json
{
  "findings": [
    {
      "claim": "one-sentence factual statement",
      "evidence": "file:line (exact)",
      "zone": "engine|toolbar|filters|views|actions|state|glue",
      "severity": "structural|notable|minor"
    }
  ],
  "summary": "5-10 line narrative of the dimension"
}
```

- [ ] **Step 1: Dispatch the 5 dimension agents in parallel** with these briefs:

**Agent D1 — Module graph.** Brief:
```
Map who-imports-whom inside the scope. Two layers:
(a) STATIC: run `npx madge --extensions js,ts,tsx <scope paths>` and/or grep import/require statements; summarize the clusters (engine, toolbar, filters, views, actions) and the edges BETWEEN clusters (cross-cluster imports are migration blockers).
(b) DYNAMIC: madge cannot see convention-based loading. In datagrid-builder.js, document every module-name template (e.g. 'oro/datagrid/{{type}}-cell', '{{type}}-action', '{{type}}-header-cell'), every requireContext call, and where the {{type}} values come from (backend datagrid YAML → JSON config in the DOM). List every concrete module name that can be summoned dynamically (search for files matching the templates).
Findings = cross-cluster edges + every dynamic resolution site.
```

**Agent D2 — Mediator events.** Brief:
```
Build the exhaustive pub/sub matrix for the scope. Search for: mediator.trigger, mediator.on, mediator.once, mediator.off, Backbone.Events, .trigger( on collections/models that other modules listen to, and channel-name string literals (e.g. 'datagrid:*', 'grid_load:*').
For EVERY event name: list producers (file:line of each trigger) and consumers (file:line of each listener). Events with producers outside the scope but consumers inside (or vice versa) are CRITICAL — flag them 'structural'.
Findings = one finding per event name, claim format: "event X: produced by [...], consumed by [...]".
```

**Agent D3 — State & data flow.** Brief:
```
Map the grid state lifecycle. Focus files: pageable-collection.js, datagrid/state.js (if exists), state-listener.js, fetcher/, saver/, remover/.
Answer: (a) what state does PageableCollection hold (params, filters, sort, page)? (b) who MUTATES it (file:line of every .set/.updateState/direct property write from outside the collection)? (c) who READS it? (d) how is it synced to URL / localStorage / backend (state-listener)? (e) what happens on grid_load / refresh?
Findings = every external mutation site (structural), every read dependency (notable).
```

**Agent D4 — DOM/jQuery coupling.** Brief:
```
Find cross-module DOM coupling — the Strangler Fig poison. Search the scope for: jQuery selectors that reference DOM nodes RENDERED BY ANOTHER MODULE (e.g. toolbar code selecting '.grid' nodes, filters selecting toolbar containers, anything selecting datagrid-builder's mount points like [data-type="datagrid"]), $.fn plugins applied to shared nodes, direct .html()/.append()/.remove() on containers owned by other modules, and event delegation on document or shared roots.
For each: which module owns the node, which module(s) reach into it. If a React component replaced the owner, the reacher breaks — that is the finding.
Findings = one per shared selector / cross-module DOM write.
```

**Agent D5 — Existing React surface.** Brief:
```
Document how React already lives inside the Backbone grid: 
(a) reactCell.tsx (UIBundle datagrid/) — how it extends StringCell, where ReactDOM render/unmount happens, how props flow from the Backbone model;
(b) ProductGalleryRow.tsx (PimDataGridBundle datagrid/) — how a full row is React-rendered;
(c) quickexport/ components — how they mount;
(d) ProductGridViewTitle/locale-switcher bridges (UIBundle grid/, product/grid/).
For each: mount mechanism, unmount/cleanup (React 18 createRoot vs legacy render — check which API), data flow in (props/model), data flow out (events/callbacks), and what it would take to generalize this into THE bridge pattern for waves 1-5.
Findings = one per bridge mechanism + gaps (e.g. no unmount → memory leak risk).
```

- [ ] **Step 2: Collect the 5 result sets.** Filter `severity in (structural, notable)` for verification; `minor` goes straight to the report appendix.

---

## Task 2: Adversarial verification

**Files:** none created.

- [ ] **Step 1: For every structural/notable finding, dispatch a skeptic agent** with this prompt template:

```
You are a skeptical code reviewer. CLAIM under test: "<claim>" with evidence "<file:line>".
Your job is to REFUTE it: read the cited file (and neighbors if needed) and check
(a) the cited line actually contains what the claim says,
(b) the claim's direction is right (producer vs consumer, owner vs reacher),
(c) the claim is not dead code (module actually reachable — check it is imported or dynamically resolvable per the {{type}} templates in datagrid-builder.js).
Return JSON: {"verdict": "confirmed|corrected|refuted", "correctedClaim": "...or null", "correctedEvidence": "...or null", "note": "1 line"}.
Default to "refuted" if the evidence does not check out.
```

- [ ] **Step 2: Apply verdicts.** `confirmed` → report as-is. `corrected` → report the corrected claim. `refuted` → drop (log count of drops in the report's method note).

---

## Task 3: Synthesis — write the report

**Files:**
- Create: `docs/plans/2026-06-10-product-grid-audit.md`

- [ ] **Step 1: Dispatch one synthesis agent** with all surviving findings and this exact report skeleton:

```markdown
# Product Grid Audit — C1 Wave 0
Date: 2026-06-10 · Method: 5-dimension agent fan-out + adversarial verification (N claims checked, M dropped)

## 1. Zone map
### 1.x <zone> (engine|toolbar|filters|views|actions|state|glue)
- Files: ...
- Responsibilities: ...
- Inbound dependencies (who needs this zone): ...
- Outbound dependencies (what this zone needs): ...

## 2. Mediator event matrix
| Event | Producers (file:line) | Consumers (file:line) | Crosses zones? |

## 3. Top-10 dangerous couplings (ranked)
| # | Coupling | Evidence | What breaks if the owner zone goes React |

## 4. Recommended wave order
| Wave | Zone | Prerequisites | Risk | Rationale |
(also: explicit revision of the provisional order toolbar→cells→views→filters→core→teardown if findings contradict it)

## 5. Bridge strategy
- Current mechanisms (from D5), verdict on generalizing reactCell, recommended pattern for waves 1-5, gaps to fix first (e.g. unmount lifecycle).

## Appendix — minor findings
```

- [ ] **Step 2: Personally review the synthesized report** (orchestrator, not an agent): check every section is filled, wave order is actually justified by cited findings (not vibes), and the exit criterion holds — "for each zone: if I replace X with React, what breaks?" is answerable.

- [ ] **Step 3: Commit**

```bash
git add docs/plans/2026-06-10-product-grid-audit.md
git commit -m "docs: product grid dependency audit (C1 wave 0)"
```

---

## Task 4: PR

- [ ] **Step 1: Push and open PR**

```bash
git push -u origin audit/product-grid-wave0
gh pr create --title "docs: product grid dependency audit (C1 wave 0)" --body "Read-only audit of the Backbone product grid: module graph, mediator event matrix, state flows, DOM coupling, existing React bridges. Decides the C1 wave order and bridge strategy. No code changes."
```

- [ ] **Step 2: Enable auto-merge immediately**

```bash
gh pr merge <N> --auto --squash
```

Docs-only change → CI is path-filtered, only light jobs run.

---

## Self-Review Notes

- Spec coverage: scope ✓ (Task 1 paths = spec table), 5 dimensions ✓ (D1-D5 = spec dimensions 1-5), adversarial verification ✓ (Task 2), report structure ✓ (Task 3 skeleton = spec deliverable sections 1-5), exit criterion ✓ (Task 3 Step 2).
- No placeholders: every agent brief and the report skeleton are complete.
- Read-only constraint repeated in every agent-facing brief via the Constraints section.
