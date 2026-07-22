# Playwright E2E coverage — fix the degenerate 100% (switch to monocart-coverage-reports) — Design

**Goal:** Fix the Playwright E2E JS coverage (shipped in PR #339) so it reports a **real per-line coverage number** instead of the degenerate `100%`, WITHOUT introducing build instrumentation. Keep the V8-collection / nightly-only / Codecov-`e2e-playwright`-flag architecture; only the collection detail and the V8→lcov conversion change.

**Context — the bug (root cause, diagnosed 2026-07-22 from the first nightly `29894462226`):**
The pipeline runs end-to-end (all 4 shards: Setup ✓ / Convert ✓ / Upload ✓) and the Codecov `e2e-playwright` flag appeared — but at **100.0% over 739 files**, which the converter logs confirm (`[v8-to-lcov] 739 files, lines 100%`). Two cumulative causes:
1. **The fixture never collects raw V8 coverage.** `page.coverage.stopJSCoverage()` without `includeRawScriptCoverage: true` returns Playwright's *processed* `functions`, not the raw V8 `rawScriptCoverage` (block-level ranges + counts) needed to reconstruct a real denominator.
2. **Hand-rolled `v8-to-istanbul` + source maps does not reconstruct the full original-file line count.** It emits only the covered lines per mapped original file → covered == total → 100%. This is a known limitation of raw `v8-to-istanbul` with source-mapped, bundled code.

**The fix:** collect raw V8 coverage in the fixture, and convert with **`monocart-coverage-reports`** — the purpose-built tool that unpacks original sources from the source maps and reconstructs the full per-file line denominator (accurate istanbul/lcov from Playwright/Puppeteer V8 data). `devtool: 'source-map'` already emits `sourcesContent`, which monocart uses.

**Non-goals:** No build instrumentation (still no babel-plugin-istanbul / second build). No change to the nightly-only gating, the strict-PR-no-op guarantee, the best-effort behaviour, the 4-shard structure, or the Codecov `e2e-playwright` flag. No change to Behat/Jest/PHPUnit pipelines.

## Architecture (unchanged except collection detail + converter)

```
Nightly test-playwright[shard N], E2E_COVERAGE=1
  ├─ coverage fixture (page override), PER TEST:
  │     page.coverage.startJSCoverage({resetOnNavigation:false, includeRawScriptCoverage:true})
  │     entries = await page.coverage.stopJSCoverage()
  │     dump entries.map(it => ({source: it.text, ...it.rawScriptCoverage})) → coverage-v8/<shard>/<testId>.json
  ├─ post-tests step (per shard): node tests/front/e2e/coverage/e2e-coverage-report.js
  │     MCR({outputDir:'coverage-e2e', reports:['lcovonly'], baseDir:cwd,
  │          entryFilter:{'**/node_modules/**':false,'**/*':true},
  │          sourceFilter:{'**/node_modules/**':false,'**/src/**':true,'**/public/bundles/**':true}})
  │     for each dump file: await mcr.add(entries);  then await mcr.generate()  → coverage-e2e/lcov.info
  └─ Upload to Codecov flag e2e-playwright (Codecov merges the 4 shards by flag)
Per-PR runs: E2E_COVERAGE unset → fixture no-op; CI coverage steps if:-skipped. (unchanged)
```

## Components (the delta from #339)

### 1. `tests/front/e2e/fixtures/coverage-fixture.ts` (modify)
Two changes inside the `E2E_COVERAGE`-gated branch:
- `startJSCoverage({resetOnNavigation: false, includeRawScriptCoverage: true})` (add the raw flag).
- Dump `entries.map(it => ({source: it.text, ...it.rawScriptCoverage}))` instead of the raw `entries` — the shape monocart's `add()` consumes for raw V8 coverage. Everything else (gate, `try/catch` best-effort, `coverage-v8/<shard>/<testId>.json` path, re-exports) is unchanged.

### 2. `tests/front/e2e/coverage/e2e-coverage-report.js` (replace `v8-to-lcov.js`)
Rename `v8-to-lcov.js` → `e2e-coverage-report.js` (its job is no longer a hand v8→istanbul map). It:
- Reads every `coverage-v8/**/*.json` dump (arrays of `{source, ...rawScriptCoverage}`).
- Builds `const mcr = MCR({name, outputDir: 'coverage-e2e', reports: ['lcovonly'], baseDir: process.cwd(), entryFilter, sourceFilter})`.
- `await mcr.add(entries)` per dump file (accumulates), then `await mcr.generate()` → `coverage-e2e/lcov.info` + prints monocart's summary.
- Best-effort: wrapped so any failure logs and the process still exits 0 (never fails the E2E job). If `coverage-v8` is empty/missing, logs and exits 0.
- `entryFilter` keeps all non-`node_modules` V8 entries; `sourceFilter` keeps only `**/src/**` and `**/public/bundles/**` unpacked sources — dropping `node_modules`/vendor/runtime.

### 3. `tests/front/e2e/coverage/e2e-coverage-report.check.js` (replace `v8-to-lcov.check.js`)
A `node`-runnable regression test that is the guard against the exact 100% bug: feed a tiny **synthetic raw V8 dump** (one script `source` with a covered branch and an un-covered branch + a minimal `functions`/ranges `result`) through the converter's core and assert the produced lcov reports **covered < total** (i.e. NOT 100%) and includes the source. Runs via `node` only (no Jest/Playwright), and must load even if monocart is not installed (guard the require; if monocart is absent, print a SKIP and exit 0 so the offline sandbox still passes — CI has it installed).

### 4. `package.json` (modify)
- Add dev-dep `monocart-coverage-reports` (current major).
- Remove `v8-to-istanbul`, `istanbul-lib-coverage`, `istanbul-lib-report`, `istanbul-reports` (superseded by monocart). Reconcile `yarn.lock` (CI `--frozen-lockfile` — pin to versions resolvable in the lockfile, per the #339 dep lesson).

### 5. `.github/workflows/ci.yml` (modify)
Only the convert step's script path: `node tests/front/e2e/coverage/e2e-coverage-report.js` (was `v8-to-lcov.js`). The `E2E_COVERAGE` env gate, the `if: schedule||workflow_dispatch`, `continue-on-error`, and the Codecov upload (`files: coverage-e2e/lcov.info`, `flags: e2e-playwright`) are unchanged.

## Testing / validation
- Local: the `e2e-coverage-report.check.js` node regression test (asserts real <100% coverage on a synthetic dump) + prettier + `python3` YAML validation. No Jest/Playwright locally.
- Real proof = the **next nightly**: the `e2e-playwright` flag should read a **realistic sub-100% line %** over `src/**` + `public/bundles/**` (not 100/739), and the converter log should print monocart's summary with covered < total.

## Risks / open items (resolve during implementation)
1. **monocart `add()` input shape.** The README shows the raw-V8 form `{source: it.text, ...it.rawScriptCoverage}`. Confirm the exact property monocart expects (it accepts Playwright's processed entries too, but raw is the accurate path); the implementer verifies against monocart's types/README when coding.
2. **lcov output filename.** `reports:['lcovonly']` writes `lcov.info` under `outputDir`; confirm it lands at `coverage-e2e/lcov.info` (the CI upload path) — set `outputDir: 'coverage-e2e'` (repo-root-relative) accordingly.
3. **Path normalization for Codecov.** monocart unpacks sourcemap sources to paths like `webpack://…/src/…` or repo-relative; use `baseDir`/`sourcePath` so Codecov attributes them to `src/**` / `public/bundles/**`. Validate on the first nightly; if paths are off, add a `sourcePath` normalizer.
4. **Still-degenerate risk.** If monocart *also* returns ~100% (e.g. Playwright's raw coverage is only function-level for this bundle), that confirms V8 cannot yield line-accurate coverage here and the fallback is the instrumented-build (babel-plugin-istanbul) approach — a separate decision, out of scope for this fix.
