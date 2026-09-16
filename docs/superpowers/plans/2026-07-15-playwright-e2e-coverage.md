# Playwright E2E JS Coverage (V8 → istanbul) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Collect the JS/TS coverage the Playwright E2E suite exercises across the whole front (legacy AMD + React), nightly-only, via Chromium V8 coverage → istanbul → lcov → Codecov flag `e2e-playwright`, with zero build instrumentation and zero per-PR overhead.

**Architecture:** A Playwright base-test module overrides the `page` fixture to start/stop `page.coverage` (gated by `E2E_COVERAGE`), dumping raw V8 JSON per test. A standalone Node post-processor converts those dumps with `v8-to-istanbul` (using the on-disk `public/dist/*.map` source maps), filters to PIM sources, merges into one lcov, which CI uploads to Codecov only on `schedule`/`workflow_dispatch`. All coverage code paths are best-effort (never break a green E2E run).

**Tech Stack:** Playwright (`@playwright/test`), Node, `v8-to-istanbul`, `istanbul-lib-coverage`, `istanbul-lib-report`, `istanbul-reports`, GitHub Actions, Codecov.

## Global Constraints

- **Nightly-only:** coverage collection + upload run ONLY when `github.event_name` is `schedule` or `workflow_dispatch`. Per-PR runs must be a strict no-op (no V8 overhead, no upload).
- **Best-effort:** every coverage code path (fixture start/stop, conversion, upload) is `try/catch` / `continue-on-error`. A coverage failure logs and is ignored; it NEVER fails the E2E job.
- **No build changes:** reuse the existing rspack build (`devtool:'source-map'`, assets restored to `public/dist/`). Do NOT add babel-plugin-istanbul or a second build.
- **Whole front:** the source filter keeps `src/**` AND `public/bundles/**` (legacy AMD) — dropping `node_modules`, `vendor`, the rspack runtime, and anonymous/inline scripts.
- **Cannot run Playwright/Jest locally** (fleet-heavy; Jest OOMs the machine). Local verification is limited to `node` (the converter has a node-runnable test) + `yarn lint`/prettier. The full pipeline is validated by the first nightly.
- Codecov flag name is exactly `e2e-playwright`. Commit messages: no `Co-Authored-By`, no AI/Claude mention.
- Worktree: all work in `/home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage` on branch `ci/playwright-e2e-coverage`.

---

### Task 1: `v8-to-lcov.js` converter + node-runnable test + deps

**Files:**
- Create: `tests/front/e2e/coverage/v8-to-lcov.js`
- Create (test): `tests/front/e2e/coverage/v8-to-lcov.check.js` (named `.check.js`, NOT `.test.js`/`.spec.ts`, so neither Jest nor Playwright auto-collects it — it runs only via `node`)
- Modify: `package.json` (devDependencies)

**Interfaces:**
- Produces (consumed by Task 4 CI): a CLI `node tests/front/e2e/coverage/v8-to-lcov.js` that reads `coverage-v8/**/*.json`, writes `coverage-e2e/lcov.info`, exits 0 always. Also exports `{urlToDiskPath, keepSource, convertDump}` for the test.
- Consumes: raw Playwright `stopJSCoverage()` entries written by Task 2 (`{url, source, functions}[]` per file).

- [ ] **Step 1: Add the dev-dependencies**

In `package.json`, add to `devDependencies` (keep alphabetical order with neighbors):

```json
"istanbul-lib-coverage": "^3.2.2",
"istanbul-lib-report": "^3.0.1",
"istanbul-reports": "^3.1.7",
"v8-to-istanbul": "^9.3.0",
```

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage && yarn install --mode=skip-build 2>&1 | tail -5` (or `yarn install`) — Expected: the 4 packages resolve and appear in `yarn.lock`. (If the sandbox blocks network, note it and rely on CI's `yarn install`.)

- [ ] **Step 2: Write the failing node test**

Create `tests/front/e2e/coverage/v8-to-lcov.check.js` (plain Node assertions — runs via `node`, NOT Jest, so it does not OOM):

```js
const assert = require('assert');
const {urlToDiskPath, keepSource} = require('./v8-to-lcov');

// urlToDiskPath: strip the served origin, resolve under public/
assert.strictEqual(
  urlToDiskPath('http://localhost:8080/dist/pim.js', '/repo'),
  '/repo/public/dist/pim.js',
  'served /dist/* maps to public/dist/*'
);
assert.strictEqual(
  urlToDiskPath('http://localhost:8080/js/require-paths.js', '/repo'),
  '/repo/public/js/require-paths.js',
  'served /js/* maps to public/js/*'
);
assert.strictEqual(
  urlToDiskPath('http://localhost:8080/', '/repo'),
  null,
  'the bare document URL has no disk asset'
);
assert.strictEqual(
  urlToDiskPath('inline-script-1', '/repo'),
  null,
  'anonymous/inline scripts have no disk path'
);

// keepSource: keep src/** and public/bundles/**, drop everything else
assert.strictEqual(keepSource('webpack://pim/./src/Foo/Bar.tsx'), true, 'keep src tsx');
assert.strictEqual(keepSource('webpack://pim/./public/bundles/pimui/js/x.js'), true, 'keep legacy bundles');
assert.strictEqual(keepSource('webpack://pim/./node_modules/react/index.js'), false, 'drop node_modules');
assert.strictEqual(keepSource('webpack://pim/webpack/bootstrap'), false, 'drop rspack runtime');
assert.strictEqual(keepSource('/vendor/symfony/foo.js'), false, 'drop vendor');

console.log('v8-to-lcov unit checks passed');
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage && node tests/front/e2e/coverage/v8-to-lcov.check.js`
Expected: FAIL — `Cannot find module './v8-to-lcov'` (converter not written yet).

- [ ] **Step 4: Write the converter**

Create `tests/front/e2e/coverage/v8-to-lcov.js`:

```js
/**
 * Convert raw Playwright V8 JS-coverage dumps (written per-test by the coverage
 * fixture) into a single merged lcov report, mapping bundled code back to the
 * original PIM sources (src/** and public/bundles/**) via the on-disk rspack
 * source maps (devtool:'source-map', assets under public/dist).
 *
 * Best-effort: any per-entry failure is logged and skipped; the process always
 * exits 0 so it can never fail the E2E job.
 *
 * Usage: node tests/front/e2e/coverage/v8-to-lcov.js
 *   IN : coverage-v8/**/*.json   (arrays of {url, source, functions})
 *   OUT: coverage-e2e/lcov.info
 */
const fs = require('fs');
const path = require('path');
// The istanbul deps are lazy-required INSIDE the functions that use them, so the
// pure helpers (urlToDiskPath/keepSource/normalizeSource) — and the node check that
// imports them — load even when v8-to-istanbul et al. are not installed.

const REPO_ROOT = path.resolve(__dirname, '../../../..');
const V8_DIR = path.join(REPO_ROOT, 'coverage-v8');
const OUT_DIR = path.join(REPO_ROOT, 'coverage-e2e');

/**
 * Map a V8 script `url` (served by the PIM app) to its built file on disk.
 * Only same-origin http(s) asset URLs resolve; anonymous/inline scripts return null.
 */
function urlToDiskPath(url, repoRoot) {
  if (typeof url !== 'string' || !/^https?:\/\//.test(url)) return null;
  let pathname;
  try {
    pathname = new URL(url).pathname;
  } catch (e) {
    return null;
  }
  if (!pathname || pathname === '/' || !/\.[cm]?js$/.test(pathname)) return null;
  return path.join(repoRoot, 'public', pathname.replace(/^\/+/, ''));
}

/**
 * Keep only original PIM sources: src/** and public/bundles/** (legacy AMD).
 * Drop node_modules, vendor, the rspack runtime, and anything else.
 */
function keepSource(sourcePath) {
  const p = sourcePath.replace(/\\/g, '/');
  if (/\/node_modules\//.test(p) || /(^|\/)vendor\//.test(p)) return false;
  if (/\/webpack\//.test(p) || /webpack\/(bootstrap|runtime)/.test(p)) return false;
  return /\/src\//.test(p) || /\/public\/bundles\//.test(p);
}

/** Normalize a source-map source path to a repo-relative path Codecov understands. */
function normalizeSource(sourcePath) {
  let p = sourcePath.replace(/\\/g, '/').replace(/^webpack:\/\/[^/]*\//, '').replace(/^\.\//, '');
  const idx = p.search(/(^|\/)(src|public)\//);
  if (idx >= 0) p = p.slice(p.indexOf(p.match(/(src|public)\//)[0]));
  return p;
}

/** Convert one V8 dump array into istanbul file-coverage objects, filtered + normalized. */
async function convertDump(entries, repoRoot) {
  const v8toIstanbul = require('v8-to-istanbul');
  const out = [];
  for (const entry of entries || []) {
    const diskPath = urlToDiskPath(entry.url, repoRoot);
    if (!diskPath || !fs.existsSync(diskPath)) continue;
    try {
      const converter = v8toIstanbul(diskPath, 0, {source: entry.source});
      await converter.load();
      converter.applyCoverage(entry.functions || []);
      const istanbul = converter.toIstanbul();
      for (const [file, fileCov] of Object.entries(istanbul)) {
        if (!keepSource(file)) continue;
        fileCov.path = normalizeSource(file);
        out.push(fileCov);
      }
    } catch (e) {
      console.warn(`[v8-to-lcov] skip ${entry.url}: ${e.message}`);
    }
  }
  return out;
}

async function main() {
  const libCoverage = require('istanbul-lib-coverage');
  const libReport = require('istanbul-lib-report');
  const reports = require('istanbul-reports');
  const map = libCoverage.createCoverageMap({});
  if (!fs.existsSync(V8_DIR)) {
    console.warn(`[v8-to-lcov] no ${V8_DIR}; nothing to convert`);
    return;
  }
  const files = [];
  (function walk(dir) {
    for (const name of fs.readdirSync(dir)) {
      const full = path.join(dir, name);
      if (fs.statSync(full).isDirectory()) walk(full);
      else if (name.endsWith('.json')) files.push(full);
    }
  })(V8_DIR);

  for (const f of files) {
    let entries;
    try {
      entries = JSON.parse(fs.readFileSync(f, 'utf8'));
    } catch (e) {
      console.warn(`[v8-to-lcov] bad json ${f}: ${e.message}`);
      continue;
    }
    for (const fileCov of await convertDump(entries, REPO_ROOT)) {
      // Merge unions execution counts for the same file across tests/scripts.
      map.merge({[fileCov.path]: fileCov});
    }
  }

  fs.mkdirSync(OUT_DIR, {recursive: true});
  const context = libReport.createContext({dir: OUT_DIR, coverageMap: map});
  reports.create('lcovonly', {file: 'lcov.info'}).execute(context);
  const summary = map.getCoverageSummary();
  console.log(
    `[v8-to-lcov] ${map.files().length} files, lines ${summary.lines.pct}% → ${path.join(OUT_DIR, 'lcov.info')}`
  );
}

if (require.main === module) {
  main().catch(e => console.warn(`[v8-to-lcov] fatal (ignored): ${e.message}`));
}

module.exports = {urlToDiskPath, keepSource, normalizeSource, convertDump};
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage && node tests/front/e2e/coverage/v8-to-lcov.check.js`
Expected: PASS — prints `v8-to-lcov unit checks passed`.

- [ ] **Step 6: Prettier + commit**

Run: `node_modules/.bin/prettier --check tests/front/e2e/coverage/v8-to-lcov.js tests/front/e2e/coverage/v8-to-lcov.check.js 2>&1 | tail -3` (fix if needed).

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage
git add tests/front/e2e/coverage/v8-to-lcov.js tests/front/e2e/coverage/v8-to-lcov.check.js package.json yarn.lock
git commit -m "feat(ci): v8-to-lcov converter — Playwright V8 coverage → istanbul lcov (src + legacy bundles)"
```

---

### Task 2: Playwright coverage base-test module (page override)

**Files:**
- Create: `tests/front/e2e/fixtures/coverage-fixture.ts`

**Interfaces:**
- Produces (consumed by Task 3 sweep): a module exporting `test` (a `@playwright/test` `base.extend` that overrides `page`) and re-exporting `expect`, `Page`, `Locator`, `APIRequestContext` so a spec can swap ONLY its import path with no other change.
- Produces (consumed by Task 1 at runtime): per-test files `coverage-v8/<shard>/<testId>.json` = the raw `stopJSCoverage()` array.

- [ ] **Step 1: Implement the fixture**

Create `tests/front/e2e/fixtures/coverage-fixture.ts`:

```ts
import {test as base, expect} from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

export {expect};
export type {Page, Locator, APIRequestContext} from '@playwright/test';

const COVERAGE = !!process.env.E2E_COVERAGE;
const SHARD = (process.env.PW_SHARD || 'local').replace(/[^0-9a-z]/gi, '-');
const OUT = path.resolve(__dirname, '../../../..', 'coverage-v8', SHARD);

/**
 * Overrides the built-in `page` fixture. When E2E_COVERAGE is set (nightly only)
 * it wraps the test with Chromium V8 JS coverage and dumps the raw entries per
 * test for the v8-to-lcov post-processor. Strict no-op otherwise (zero PR cost).
 * Every coverage call is best-effort: a failure is logged and never fails the test.
 */
export const test = base.extend({
  page: async ({page}, use, testInfo) => {
    if (COVERAGE) {
      try {
        await page.coverage.startJSCoverage({resetOnNavigation: false});
      } catch (e) {
        console.warn(`[coverage] startJSCoverage failed: ${(e as Error).message}`);
      }
    }

    await use(page);

    if (COVERAGE) {
      try {
        const entries = await page.coverage.stopJSCoverage();
        fs.mkdirSync(OUT, {recursive: true});
        const name = testInfo.testId.replace(/[^0-9a-z]/gi, '-');
        fs.writeFileSync(path.join(OUT, `${name}.json`), JSON.stringify(entries));
      } catch (e) {
        console.warn(`[coverage] stopJSCoverage failed: ${(e as Error).message}`);
      }
    }
  },
});
```

- [ ] **Step 2: Verify it type-checks / lints**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage && node_modules/.bin/prettier --check tests/front/e2e/fixtures/coverage-fixture.ts 2>&1 | tail -3`
Expected: `All matched files use Prettier code style!`. (Do NOT run Playwright — heavy. Type-correctness is confirmed by review; `page.coverage` is Chromium-only and the E2E suite runs Chromium.)

- [ ] **Step 3: Commit**

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage
git add tests/front/e2e/fixtures/coverage-fixture.ts
git commit -m "feat(ci): Playwright base-test fixture — E2E_COVERAGE-gated V8 coverage per test (no-op on PRs)"
```

---

### Task 3: E2E spec import sweep

**Files:**
- Modify: every `tests/front/e2e/**/*.spec.ts` that imports `test` from `@playwright/test` (19 spec files).

**Interfaces:**
- Consumes: the base-test module from Task 2 (`tests/front/e2e/fixtures/coverage-fixture.ts`), which re-exports `test`, `expect`, `Page`, `Locator`, `APIRequestContext`.

- [ ] **Step 1: Sweep the import path in specs that import `test`**

For each `*.spec.ts` under `tests/front/e2e/` whose import line references `test` from `@playwright/test`, replace `'@playwright/test'` with a correct relative path to `tests/front/e2e/fixtures/coverage-fixture`. Compute the relative prefix from the file's depth (files under `tests/front/e2e/<dir>/x.spec.ts` → `../fixtures/coverage-fixture`; deeper dirs add `../`).

Run this sweep:

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage
for f in $(grep -rl "from '@playwright/test'" tests/front/e2e --include='*.spec.ts'); do
  # only rewrite specs that import `test` (the fixture provides test/expect/types)
  if grep -qE "import \{[^}]*\btest\b[^}]*\} from '@playwright/test'" "$f"; then
    depth=$(echo "${f#tests/front/e2e/}" | tr -cd '/' | wc -c)   # subdirs below e2e
    prefix=$(printf '../%.0s' $(seq 1 "$depth"))
    sed -i "s#from '@playwright/test'#from '${prefix}fixtures/coverage-fixture'#" "$f"
  fi
done
```

- [ ] **Step 2: Verify no `test`-importing spec still points at `@playwright/test`, and the relative paths resolve**

Run:
```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage
echo "specs still importing test from @playwright/test (must be 0):"
grep -rlE "import \{[^}]*\btest\b[^}]*\} from '@playwright/test'" tests/front/e2e --include='*.spec.ts' | wc -l
echo "resolve check (each path must exist):"
for f in $(grep -rloE "from '(\.\./)+fixtures/coverage-fixture'" tests/front/e2e --include='*.spec.ts'); do
  rel=$(grep -oE "(\.\./)+fixtures/coverage-fixture" "$f" | head -1)
  [ -f "$(dirname "$f")/$rel.ts" ] || echo "BROKEN in $f -> $rel"
done
```
Expected: `0` specs still importing test from `@playwright/test`; no `BROKEN` lines.

- [ ] **Step 3: Prettier + commit**

Run: `node_modules/.bin/prettier --check 'tests/front/e2e/**/*.spec.ts' 2>&1 | tail -3` (fix if needed).

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage
git add tests/front/e2e
git commit -m "test(e2e): route specs through the coverage base-test module (import sweep)"
```

---

### Task 4: CI wiring + codecov flag

**Files:**
- Modify: `.github/workflows/ci.yml` (`test-playwright` job — env + 2 new steps)
- Create: `codecov.yml`

**Interfaces:**
- Consumes: `coverage-e2e/lcov.info` produced by Task 1's converter from Task 2's dumps.

- [ ] **Step 1: Add the `E2E_COVERAGE` env gate to the Playwright run step**

In `.github/workflows/ci.yml`, the `test-playwright` job's `Setup and run Playwright` step (around line 1570). Add an `env:` block to that step (or set it inline before the `npx playwright` invocation) so coverage is collected only on the nightly/dispatch. Add, at the step level:

```yaml
      - name: Setup and run Playwright
        env:
          E2E_COVERAGE: ${{ (github.event_name == 'schedule' || github.event_name == 'workflow_dispatch') && '1' || '' }}
          PW_SHARD: ${{ matrix.shard }}/4
        run: |
          # ... existing script unchanged ...
```

(Keep the existing `run:` body verbatim; only prepend the `env:` block. If `PW_SHARD` is already exported inside the script, leave that too — the env block is harmless/idempotent.)

- [ ] **Step 2: Add the convert + upload steps after the Playwright step**

Immediately AFTER the `Setup and run Playwright` step, add:

```yaml
      - name: Convert E2E V8 coverage to lcov
        if: ${{ github.event_name == 'schedule' || github.event_name == 'workflow_dispatch' }}
        continue-on-error: true
        run: node tests/front/e2e/coverage/v8-to-lcov.js

      - name: Upload E2E coverage to Codecov
        if: ${{ github.event_name == 'schedule' || github.event_name == 'workflow_dispatch' }}
        continue-on-error: true
        uses: codecov/codecov-action@v4
        with:
          files: coverage-e2e/lcov.info
          flags: e2e-playwright
          disable_search: true
          fail_ci_if_error: false
        env:
          CODECOV_TOKEN: ${{ secrets.CODECOV_TOKEN }}
```

- [ ] **Step 3: Create `codecov.yml` declaring the flag with carryforward**

Create `codecov.yml` at the repo root:

```yaml
# Declares the nightly E2E flag so its last value carries forward onto commits
# that do not run the nightly (otherwise it would read as missing on every PR).
# Other flags (frontend, backend, test-phpunit-integration) keep Codecov defaults.
flags:
  e2e-playwright:
    carryforward: true
    paths:
      - src/
      - public/bundles/

coverage:
  status:
    project: false
    patch: false
```

(`coverage.status.project/patch: false` keeps Codecov from posting a blocking commit status — this is a tracked metric, not a gate, per the spec's non-goals.)

- [ ] **Step 4: Lint the YAML + commit**

Run: `cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage && python3 -c "import yaml,sys; yaml.safe_load(open('.github/workflows/ci.yml')); yaml.safe_load(open('codecov.yml')); print('yaml ok')"`
Expected: `yaml ok`.

```bash
cd /home/gumiranda/claude-worktrees/pim-community-dev/playwright-e2e-coverage
git add .github/workflows/ci.yml codecov.yml
git commit -m "ci: collect Playwright E2E JS coverage nightly (V8→lcov, Codecov flag e2e-playwright)"
```

---

## Post-implementation (controller)

Push the branch, open the PR, enable auto-merge (`gh pr merge --auto --squash`). The per-PR CI proves the **no-op** path (E2E_COVERAGE unset → Playwright unchanged, no new steps run — the `if:` gates skip them). The **coverage path itself is only exercised by the nightly `schedule` run** (or a manual `workflow_dispatch`): after merge, trigger `workflow_dispatch` (or wait for the nightly) and verify Codecov shows a non-zero `e2e-playwright` flag spanning both `src/**` and `public/bundles/**`. If the first nightly shows 0% or missing files, the likely cause is the `urlToDiskPath` base or the source-map `sources` prefix (spec Risk #1/#2) — fix in `v8-to-lcov.js`, not the fixture.

## Self-Review notes (author)

- **Spec coverage:** fixture (§Component 1) → Task 2; converter (§Component 2) → Task 1; dev-deps (§Component 3) → Task 1 Step 1; ci.yml gate+merge+upload (§Component 4) → Task 4 Steps 1-2; codecov.yml flag (§Component 5) → Task 4 Step 3; import sweep (§Risk 5) → Task 3. Best-effort + PR no-op (§Error handling) → fixture `COVERAGE` gate + all `if:`/`continue-on-error`. Whole-front filter (§Non-goals/Context) → `keepSource` keeps `src/` + `public/bundles/`.
- **Placeholder scan:** none — full code in every step.
- **Type/name consistency:** `urlToDiskPath`, `keepSource`, `normalizeSource`, `convertDump` are named identically in the converter, its test, and the interfaces block; the fixture writes `coverage-v8/<shard>/<testId>.json` and the converter reads `coverage-v8/**/*.json` → `coverage-e2e/lcov.info`; the Codecov step uploads exactly that path with flag `e2e-playwright` matching codecov.yml.
- **Open items carried from spec:** `urlToDiskPath` base (`public/` + pathname) is the concrete resolution of Risk #2; source-map `sources` normalization (`normalizeSource`) is the concrete resolution of Risk #1 — both validated by the first nightly.
