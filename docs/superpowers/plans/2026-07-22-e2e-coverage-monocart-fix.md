# E2E coverage — monocart fix (kill the degenerate 100%) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the Playwright E2E JS coverage (PR #339) report a real per-line % instead of `100%`, by collecting raw V8 coverage in the fixture and converting with `monocart-coverage-reports` (reconstructs the full line denominator). No build instrumentation; the nightly-only / no-PR-cost / best-effort / Codecov-`e2e-playwright`-flag architecture is unchanged.

**Architecture:** Fixture adds `includeRawScriptCoverage: true` and dumps raw V8 entries; a monocart-based converter replaces the hand-rolled `v8-to-lcov.js`; deps swap `v8-to-istanbul`+`istanbul-lib-*` for `monocart-coverage-reports`; the CI convert step points at the new script. Per-shard lcov → Codecov merges by flag (unchanged).

**Tech Stack:** Playwright (`@playwright/test` ^1.50), Node, `monocart-coverage-reports`, GitHub Actions, Codecov.

## Global Constraints

- **The `monocart-coverage-reports` dep + `yarn.lock` update is done by the controller out-of-band** (yarn v1 `--frozen-lockfile` needs a real `yarn add`, run in the docker `node` service). Task 1 assumes `package.json` already lists `monocart-coverage-reports` and no longer lists `v8-to-istanbul`/`istanbul-lib-*`, and that `yarn.lock` is consistent. Implementers do NOT run `yarn add`.
- **Nightly-only + strict PR no-op + best-effort** — unchanged from #339. Coverage steps stay `if: schedule||workflow_dispatch` + `continue-on-error`; the fixture is a no-op when `E2E_COVERAGE` is unset; every coverage path is `try/catch` and never fails a green E2E run.
- **Whole front** — the monocart `sourceFilter` keeps `**/src/**` + `**/public/bundles/**`, drops `**/node_modules/**`.
- **Cannot run Playwright/Jest locally** (fleet-heavy; Jest OOMs). Local checks: `node tests/front/e2e/coverage/e2e-coverage-report.check.js` (config guard, needs no monocart), `node_modules/.bin/prettier --check`, `python3` YAML validation. Real proof = the next nightly.
- Codecov flag is exactly `e2e-playwright`. lcov output path is exactly `coverage-e2e/lcov.info`. Commit messages: no `Co-Authored-By`, no AI/Claude mention.
- Worktree: `/home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart` on branch `ci/e2e-coverage-monocart`.

---

### Task 1: Replace the converter with monocart + config check

**Files:**
- Create: `tests/front/e2e/coverage/e2e-coverage-report.js`
- Create (test): `tests/front/e2e/coverage/e2e-coverage-report.check.js`
- Delete: `tests/front/e2e/coverage/v8-to-lcov.js`, `tests/front/e2e/coverage/v8-to-lcov.check.js`

**Interfaces:**
- Consumes: per-test raw-V8 dumps written by the fixture (Task 2) — `coverage-v8/<shard>/<testId>.json`, each an array of `{source, ...rawScriptCoverage}`.
- Produces (consumed by Task 3 CI): a CLI `node tests/front/e2e/coverage/e2e-coverage-report.js` that reads `coverage-v8/**/*.json`, writes `coverage-e2e/lcov.info` via monocart, exits 0 always. Exports `{buildOptions, listDumps, OUT_DIR, V8_DIR}`.

- [ ] **Step 1: Write the failing config check**

Create `tests/front/e2e/coverage/e2e-coverage-report.check.js`:

```js
// Config guard — runs via `node` WITHOUT monocart installed (the converter
// lazy-requires monocart inside main(), so requiring the module here is safe).
const assert = require('assert');
const {buildOptions, OUT_DIR} = require('./e2e-coverage-report');

const o = buildOptions();
assert.deepStrictEqual(o.reports, ['lcovonly'], 'must emit lcovonly');
assert.ok(String(OUT_DIR).endsWith('coverage-e2e'), 'outputs to coverage-e2e');
assert.strictEqual(o.outputDir, OUT_DIR, 'outputDir is the coverage-e2e dir');
assert.strictEqual(o.sourceFilter['**/src/**'], true, 'keeps src sources');
assert.strictEqual(o.sourceFilter['**/public/bundles/**'], true, 'keeps legacy bundle sources');
assert.strictEqual(o.sourceFilter['**/node_modules/**'], false, 'drops node_modules sources');
assert.strictEqual(o.entryFilter['**/node_modules/**'], false, 'drops node_modules entries');
console.log('e2e-coverage-report config check passed');
```

- [ ] **Step 2: Run it to verify it fails**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart && node tests/front/e2e/coverage/e2e-coverage-report.check.js`
Expected: FAIL — `Cannot find module './e2e-coverage-report'` (converter not written yet).

- [ ] **Step 3: Write the monocart converter**

Create `tests/front/e2e/coverage/e2e-coverage-report.js`:

```js
/**
 * Convert raw Playwright V8 JS-coverage dumps (written per-test by the coverage
 * fixture) into an lcov report via monocart-coverage-reports, which unpacks the
 * original sources from the rspack source maps (devtool:'source-map') and
 * reconstructs the FULL per-file line denominator — unlike the previous raw
 * v8-to-istanbul, which reported a degenerate 100%.
 *
 * Best-effort: any failure logs and the process still exits 0 (never fails the
 * E2E job). Reads coverage-v8 (shard)/*.json → writes coverage-e2e/lcov.info.
 */
const fs = require('fs');
const path = require('path');

const REPO_ROOT = path.resolve(__dirname, '../../../..');
const V8_DIR = path.join(REPO_ROOT, 'coverage-v8');
const OUT_DIR = path.join(REPO_ROOT, 'coverage-e2e');

/** monocart options: lcov output; keep only src/** + public/bundles/** sources. */
function buildOptions() {
  return {
    name: 'E2E Playwright Coverage',
    outputDir: OUT_DIR,
    baseDir: REPO_ROOT,
    logging: 'error',
    reports: ['lcovonly'],
    entryFilter: {'**/node_modules/**': false, '**/*': true},
    sourceFilter: {'**/node_modules/**': false, '**/src/**': true, '**/public/bundles/**': true},
  };
}

function listDumps(dir) {
  const out = [];
  if (!fs.existsSync(dir)) return out;
  for (const name of fs.readdirSync(dir)) {
    const full = path.join(dir, name);
    if (fs.statSync(full).isDirectory()) out.push(...listDumps(full));
    else if (name.endsWith('.json')) out.push(full);
  }
  return out;
}

async function main() {
  const dumps = listDumps(V8_DIR);
  if (!dumps.length) {
    console.warn(`[e2e-coverage] no dumps under ${V8_DIR}; nothing to convert`);
    return;
  }
  const MCR = require('monocart-coverage-reports');
  const mcr = MCR(buildOptions());
  let added = 0;
  for (const f of dumps) {
    try {
      const entries = JSON.parse(fs.readFileSync(f, 'utf8'));
      if (Array.isArray(entries) && entries.length) {
        await mcr.add(entries);
        added++;
      }
    } catch (e) {
      console.warn(`[e2e-coverage] skip ${f}: ${e.message}`);
    }
  }
  if (!added) {
    console.warn('[e2e-coverage] no valid dumps added');
    return;
  }
  await mcr.generate();
}

if (require.main === module) {
  main().catch(e => console.warn(`[e2e-coverage] fatal (ignored): ${e.message}`));
}

module.exports = {buildOptions, listDumps, OUT_DIR, V8_DIR};
```

- [ ] **Step 4: Run the check to verify it passes**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart && node tests/front/e2e/coverage/e2e-coverage-report.check.js`
Expected: PASS — prints `e2e-coverage-report config check passed`. (This loads the converter without monocart — the config is what it guards.)

- [ ] **Step 5: Delete the old hand-rolled converter + its check**

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart
git rm tests/front/e2e/coverage/v8-to-lcov.js tests/front/e2e/coverage/v8-to-lcov.check.js
```

- [ ] **Step 6: Prettier + commit**

Run: `node_modules/.bin/prettier --check tests/front/e2e/coverage/e2e-coverage-report.js tests/front/e2e/coverage/e2e-coverage-report.check.js 2>&1 | tail -3` (fix if needed).

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart
git add tests/front/e2e/coverage/e2e-coverage-report.js tests/front/e2e/coverage/e2e-coverage-report.check.js
git commit -m "fix(ci): convert E2E V8 coverage with monocart (real line denominator, kills the 100%)"
```

---

### Task 2: Collect raw V8 coverage in the fixture

**Files:**
- Modify: `tests/front/e2e/fixtures/coverage-fixture.ts`

**Interfaces:**
- Produces (consumed by Task 1): per-test files `coverage-v8/<shard>/<testId>.json` = an array of `{source, ...rawScriptCoverage}` (the raw-V8 shape monocart's `add()` consumes).

- [ ] **Step 1: Add `includeRawScriptCoverage` + dump the raw-V8 shape**

In `tests/front/e2e/fixtures/coverage-fixture.ts`, inside the `COVERAGE` branch of the `page` override, make exactly two changes:

1. The start call — add the raw flag:

```ts
        await page.coverage.startJSCoverage({resetOnNavigation: false, includeRawScriptCoverage: true});
```

2. The dump — write the raw-V8 shape monocart consumes (each entry: the script source text + its raw V8 `rawScriptCoverage`). Replace the `writeFileSync(..., JSON.stringify(entries))` line with:

```ts
        const raw = entries.map((it: any) => ({source: it.source, ...it.rawScriptCoverage}));
        fs.writeFileSync(path.join(OUT, `${name}.json`), JSON.stringify(raw));
```

Everything else (the `E2E_COVERAGE` gate, the `try/catch`, the `coverage-v8/<shard>/<testId>.json` path, the re-exports) is unchanged.

**Note (resolve while editing):** the Playwright `JSCoverageEntry` field carrying the script text is `source` on `@playwright/test` ^1.50 (the monocart README's `it.text` is an older field name). Confirm against the installed Playwright types (`node_modules/@playwright/test`/`playwright-core` `JSCoverageEntry`) — if the text is exposed as `.text`, use that instead; the `rawScriptCoverage` spread is unconditional.

- [ ] **Step 2: Prettier**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart && node_modules/.bin/prettier --check tests/front/e2e/fixtures/coverage-fixture.ts 2>&1 | tail -3`
Expected: clean. (Do NOT run Playwright.)

- [ ] **Step 3: Commit**

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart
git add tests/front/e2e/fixtures/coverage-fixture.ts
git commit -m "fix(ci): collect raw V8 coverage (includeRawScriptCoverage) for monocart"
```

---

### Task 3: Point the CI convert step at the new script

**Files:**
- Modify: `.github/workflows/ci.yml` (the "Convert E2E V8 coverage to lcov" step)

**Interfaces:**
- Consumes: `tests/front/e2e/coverage/e2e-coverage-report.js` (Task 1). Produces the same `coverage-e2e/lcov.info` the (unchanged) Codecov upload step consumes.

- [ ] **Step 1: Update the script path**

In `.github/workflows/ci.yml`, the `Convert E2E V8 coverage to lcov` step — change its `run:` from the old converter to the new one:

```yaml
      - name: Convert E2E V8 coverage to lcov
        if: ${{ github.event_name == 'schedule' || github.event_name == 'workflow_dispatch' }}
        continue-on-error: true
        run: node tests/front/e2e/coverage/e2e-coverage-report.js
```

(Only the `run:` script path changes from `v8-to-lcov.js` → `e2e-coverage-report.js`. The `if:`, `continue-on-error`, the `E2E_COVERAGE` env on the Playwright step, and the Codecov upload step — `files: coverage-e2e/lcov.info`, `flags: e2e-playwright` — are unchanged.)

- [ ] **Step 2: Validate YAML + commit**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart && python3 -c "import yaml; yaml.safe_load(open('.github/workflows/ci.yml')); print('yaml ok')"`
Expected: `yaml ok`.

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/e2e-cov-monocart
git add .github/workflows/ci.yml
git commit -m "ci: run the monocart E2E coverage converter in the nightly Playwright job"
```

---

## Post-implementation (controller)

Push, open the PR, enable auto-merge (`gh pr merge --auto --squash`). Per-PR CI proves the no-op path (coverage steps `if:`-skipped; the new `tests/front/e2e/**` files are outside Jest/Stryker/ESLint scope). The **real proof is the next nightly** (or a `workflow_dispatch`): the Codecov `e2e-playwright` flag should read a **realistic sub-100% line %** over `src/**` + `public/bundles/**` (not 100/739), and the converter log should print monocart's summary with covered < total. If it is STILL ~100%, that confirms V8 cannot give line-accurate coverage for this bundle (spec Risk #4) → escalate to the instrumented-build approach.

## Self-Review notes (author)

- **Spec coverage:** fixture raw-V8 collection (§Component 1) → Task 2; monocart converter (§Component 2) → Task 1; config check (§Component 3) → Task 1 Steps 1-4; deps (§Component 4) → controller out-of-band (Global Constraints); CI script path (§Component 5) → Task 3. No-op/best-effort preserved (fixture gate + `if:`/`continue-on-error` untouched).
- **Placeholder scan:** none — full code in every step. The two flagged open items (`it.source` vs `it.text`; the eventual still-100% escalation) are explicit resolution notes, not placeholders.
- **Type/name consistency:** `buildOptions`/`listDumps`/`OUT_DIR`/`V8_DIR` named identically in the converter, its check, and the interfaces block; the fixture writes `coverage-v8/<shard>/<testId>.json` (array of `{source,...rawScriptCoverage}`) and the converter reads exactly that → `coverage-e2e/lcov.info`; the CI upload path + flag match the spec.
