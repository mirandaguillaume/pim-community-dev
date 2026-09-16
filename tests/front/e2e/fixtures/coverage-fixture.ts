import {test as base, expect} from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
// Same transform behat-cdp-coverage.js (the Behat side's CDP dump writer) uses to name its dumps --
// imported, not re-implemented, so the two can never drift apart. See its filename below: naming the
// Playwright dump from this same function, applied to the same string the marker writes, is what
// gives PHP and JS the one shared identity the join in build-inventory.js needs.
import {sanitise} from '../coverage/behat-cdp-coverage';

export {expect};
export type {Page, Locator, APIRequestContext} from '@playwright/test';

const COVERAGE = !!process.env.E2E_COVERAGE;
const SHARD = (process.env.PW_SHARD || 'local').replace(/[^0-9a-z]/gi, '-');
const OUT = path.resolve(__dirname, '../../../..', 'coverage-v8', SHARD);

/**
 * Where the PHP collector looks for the current-test id. Writing it here is what gives the
 * Playwright suite PHP coverage: the auto_prepend shim runs for every APP_ENV, so the only missing
 * piece was telling it which test caused a request.
 */
const MARKER_DIR = path.resolve(__dirname, '../../../..', 'var/tests/behat-coverage');

/**
 * Overrides the built-in `page` fixture. When E2E_COVERAGE is set (nightly only)
 * it wraps the test with Chromium V8 JS coverage and dumps the raw entries per
 * test for the monocart e2e-coverage-report post-processor. Strict no-op
 * otherwise (zero PR cost).
 * Every coverage call is best-effort: a failure is logged and never fails the test.
 */
export const test = base.extend({
  page: async ({page}, use, testInfo) => {
    // titlePath() is stable and human-readable, and matches how Playwright reports a test -- so the
    // inventory keys line up with what `npx playwright test` prints. Computed ONCE and used for both
    // the marker (raw) and the JS dump filename (sanitised): those used to be two unrelated strings
    // (the marker wrote titlePath, the dump was named from testInfo.testId, an opaque Playwright
    // hash), so build-inventory.js's join could never reunite a Playwright test's PHP and JS halves
    // -- it silently produced two entries instead of one. Same identity, both sides, fixes that.
    const testId = testInfo.titlePath.join(' > ');

    if (COVERAGE) {
      try {
        await page.coverage.startJSCoverage({resetOnNavigation: false});
      } catch (e) {
        console.warn(`[coverage] startJSCoverage failed: ${(e as Error).message}`);
      }

      try {
        fs.mkdirSync(MARKER_DIR, {recursive: true});
        fs.writeFileSync(path.join(MARKER_DIR, '.current-test'), testId);
      } catch (e) {
        console.warn(`[coverage] marker write failed: ${(e as Error).message}`);
      }
    }

    await use(page);

    if (COVERAGE) {
      try {
        // stopJSCoverage() entries are already the raw V8 shape monocart expects
        // ({url, scriptId, source, functions}) — no reshaping needed.
        const entries = await page.coverage.stopJSCoverage();
        fs.mkdirSync(OUT, {recursive: true});
        fs.writeFileSync(path.join(OUT, `${sanitise(testId)}.json`), JSON.stringify(entries));
      } catch (e) {
        console.warn(`[coverage] stopJSCoverage failed: ${(e as Error).message}`);
      }
    }
  },
});
