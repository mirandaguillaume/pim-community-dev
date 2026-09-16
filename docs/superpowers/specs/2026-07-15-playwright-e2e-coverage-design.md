# Playwright E2E JS coverage (V8 → istanbul, whole front, nightly) — Design

**Goal:** Measure the JavaScript/TypeScript code coverage that the Playwright end-to-end suite exercises across the **entire** PIM front (legacy requirejs/Backbone/jQuery **and** modern React/TS), collected in the **nightly** CI run only, converted to lcov and published to Codecov under a dedicated `e2e-playwright` flag — WITHOUT instrumenting the build and WITHOUT slowing the normal per-PR pipeline.

**This is sub-project #1** of the broader "E2E coverage" effort (the original intent behind the mis-scoped, unmerged #331). **Sub-project #2 (Behat PHP coverage)** is a separate spec/plan/PR and is out of scope here.

**Non-goals:**
- No build-time instrumentation (no `babel-plugin-istanbul`, no instrumented bundle, no second `build-front`). The existing production build with `devtool: 'source-map'` is reused as-is.
- No coverage on per-PR runs (overhead + fleet cost). Coverage runs ONLY on `schedule`/`workflow_dispatch`.
- No CI gating/threshold on the E2E coverage number (tracked metric + gap visibility, not a blocking gate).
- No change to the Behat pipeline, the Jest unit pipeline, or the PHPUnit pipeline.

## Context

- The front is served by the running PIM app: `playwright.config.ts` has **no `webServer`**; `baseURL` = `PIM_URL || http://localhost:8080`, i.e. Playwright drives the real app serving the **built** assets. Playwright launches its **own Chromium** (`~/.cache/ms-playwright`), so the Chromium-only `page.coverage` API is available (the Behat suite's Selenium `standalone-chrome` is unrelated).
- Build = **rspack** (`@rspack/core`, the `webpack` npm script). `rspack.config.js:87 devtool: 'source-map'` → full external source maps are emitted alongside the bundles, so V8 byte-range coverage can be mapped back to original sources (`src/**/*.tsx`, `public/bundles/**/*.js`).
- babel-loader processes legacy `public/bundles/**/*.js`; `.tsx` go through ts-loader. A build-instrumentation approach would therefore need to touch **two** loader chains — avoided entirely by the V8 approach.
- `test-playwright` is a **4-shard** matrix job (`PW_SHARD`), `workers: 1`, `retries: 1`, gated by `detect-changes` path filters (front changes). The nightly is the scheduled `CI` workflow (`cron`), which currently is NOT event-gated for coverage — the exact gap that made #331 pointless.
- No `v8-to-istanbul` / `istanbul-lib-*` / `nyc` currently installed → greenfield dev-deps.
- Codecov is live (`activated:true`) and already merges multiple upload sessions per commit by flag.

## Architecture & data flow

```
Nightly CI (event_name ∈ {schedule, workflow_dispatch}) → test-playwright[shard N], E2E_COVERAGE=1
  ├─ coverage fixture (test.extend), PER TEST:
  │     page.coverage.startJSCoverage({resetOnNavigation:false})   // before
  │     const entries = await page.coverage.stopJSCoverage()       // after
  │     write entries verbatim → coverage-v8/shard-N/<testId>.json  (best-effort, try/catch)
  ├─ post-tests step (per shard): node tests/front/e2e/coverage/v8-to-lcov.js
  │     for each V8 entry: v8-to-istanbul(scriptPath, 0, {source: entry.source})
  │       .loadMap() from the on-disk .map (public/**), .applyCoverage(entry.functions)
  │     filter to PIM sources (keep src/**, public/bundles/** ; drop node_modules, vendor,
  │       rspack runtime, anonymous/inline scripts) → merge (istanbul-lib-coverage)
  │     → coverage-e2e/lcov.info
  └─ Upload to Codecov: flag `e2e-playwright` (Codecov merges the 4 shards by flag)
Per-PR runs: E2E_COVERAGE unset → fixture is a strict no-op; no overhead, no upload.
```

## Components (isolated units)

### 1. `tests/front/e2e/fixtures/coverage-fixture.ts` (new)
A Playwright `test.extend` that wraps the built-in `page` fixture. Gated by `process.env.E2E_COVERAGE`:
- When **unset** → passes `page` straight through (strict no-op; zero overhead on PRs).
- When **set** → before the test body: `await page.coverage.startJSCoverage({resetOnNavigation: false})`; after: `const entries = await page.coverage.stopJSCoverage()`, then write `entries` as JSON to `coverage-v8/<PW_SHARD or 'local'>/<sanitized testInfo.testId>.json`. Wrapped in try/catch — any failure logs a warning and never fails the test.
- Interface: exports `test` (the extended test object, with the coverage as an **automatic fixture** `{auto: true}` so it runs without each spec naming it) and re-exports `expect`. Every E2E spec changes its import from `@playwright/test` to this shared base-test module — this is the standard Playwright pattern and the ONLY way to attach a per-test fixture (Playwright has no transparent/global injection of a `page`-wrapping fixture; a `globalSetup` runs once, not per-test, and a reporter has no `page`). The change is one mechanical line per spec, done in a single sweep. `resetOnNavigation:false` is required because the PIM does Backbone soft-reloads (`Routing.reloadPage()`) mid-test and we want cumulative coverage.

### 2. `tests/front/e2e/coverage/v8-to-lcov.js` (new)
A standalone Node post-processor (run once per shard after the Playwright run):
- Reads every `coverage-v8/**/**.json` dump.
- For each V8 script entry with a `url` under the PIM origin and a resolvable on-disk built file + `.map`: `const converter = v8toIstanbul(diskPath, 0, {source: entry.source}); await converter.load(); converter.applyCoverage(entry.functions); const data = converter.toIstanbul();`.
- Accumulates into an `istanbul-lib-coverage` `createCoverageMap()`, `.merge(data)` per script, deduped across tests/entries (V8 emits the same script once per test — merging unions execution counts).
- **Source filter:** keep files whose resolved original path is under `src/` or `public/bundles/`; drop `node_modules`, `vendor`, the rspack runtime chunk, and anonymous/inline scripts (no resolvable file). Path normalization strips the `webpack://`/`http://localhost:8080/` prefixes to repo-relative paths so Codecov attributes them correctly.
- Writes `coverage-e2e/lcov.info` via `istanbul-lib-report` + `istanbul-reports` (`lcovonly`). Prints a one-line summary (files, line %) to the log. Exits 0 even if zero coverage found (logs a warning) so it never fails the job.

### 3. `package.json` (modify)
Add dev-deps: `v8-to-istanbul`, `istanbul-lib-coverage`, `istanbul-lib-report`, `istanbul-reports`. (Versions pinned to current majors during implementation; all are mature, dependency-light, MIT.)

### 4. `.github/workflows/ci.yml` (modify — `test-playwright` job)
- Add `E2E_COVERAGE: ${{ (github.event_name == 'schedule' || github.event_name == 'workflow_dispatch') && '1' || '' }}` to the Playwright step env — the **event-gate #331 forgot**.
- After the Playwright step (only when `E2E_COVERAGE` is set): run `node tests/front/e2e/coverage/v8-to-lcov.js` then a `codecov/codecov-action@v4` upload with `flags: e2e-playwright`, `files: coverage-e2e/lcov.info`, `disable_search: true`. Both steps `if: env.E2E_COVERAGE == '1'` and `continue-on-error: true` (coverage never breaks the E2E gate).
- The 4 shards each upload their own lcov under the same flag; Codecov merges them.

### 5. `codecov.yml` (create or modify)
Declare the `e2e-playwright` flag with `carryforward: true` (so the last nightly's number is carried on non-nightly commits instead of dropping to 0, mirroring how the existing `frontend`/`backend` flags behave).

## Error handling & PR non-regression

- **Best-effort everywhere:** fixture collection, conversion, and upload are each `try/catch` / `continue-on-error` — a coverage failure logs and is ignored; a green E2E run stays green.
- **Strict PR no-op:** with `E2E_COVERAGE` unset, the fixture does not call `page.coverage` at all, no dumps are written, and the CI coverage + upload steps are skipped. PRs pay nothing (no V8 overhead, no extra minutes).

## Testing / validation

- No meaningful unit test (browser fixture + CI plumbing). Validation is:
  1. **Local smoke (optional, heavy):** `E2E_COVERAGE=1 PW_SHARD=1/4 npx playwright test tests/front/e2e/product/product-grid-display-selector.spec.ts` then `node tests/front/e2e/coverage/v8-to-lcov.js` → assert `coverage-e2e/lcov.info` exists and is non-empty with real `src/`/`public/bundles/` paths.
  2. **Nightly CI:** the `schedule` run produces a Codecov `e2e-playwright` flag with a non-zero % over both `src/**` and `public/bundles/**`.
- Confidence = careful review + the first nightly (the self-hosted fleet is currently unstable — expect infra flakes; the coverage steps are `continue-on-error` so they never compound a flake).

## Risks / open items (resolve during implementation)

1. **Source-map fidelity for legacy AMD.** `devtool:'source-map'` is full-fidelity, but requirejs/AMD chunk boundaries + the `imports-loader`/`imports-loader` shims (jQuery/backbone/summernote) may yield imperfect mappings → some legacy lines mis-attributed. Acceptable for a trend metric; validate on the first nightly and, if noisy, tighten the source filter.
2. **On-disk path resolution.** `v8-to-istanbul` needs the built file + `.map` on disk at merge time. Confirm the exact public asset root the app serves (`public/` vs `public/bundles/`) and that the `.map` files are present in the `build-front` output the Playwright job restores — the merge script must resolve the V8 `url` → disk path robustly (open item; nail the base path in the plan).
3. **Dump volume.** V8 entries carry full script `source` text; per-test × 4 shards can be large. Write compactly and delete `coverage-v8/` after producing `lcov.info`.
4. **`testId` filename collisions / retries.** `retries:1` re-runs a failed test; the later dump overwrites the earlier (fine — we want the passing run's coverage). Sanitize `testId` for the filename.
5. **Spec import sweep.** Every `tests/front/e2e/**/*.spec.ts` must switch its `import {test, expect} from '@playwright/test'` to the shared base-test module. This is mechanical and touches all E2E specs in one sweep; the plan lists the exact files (glob) and verifies none imports `@playwright/test` directly afterwards (a lint/grep check) so no spec silently skips coverage.
