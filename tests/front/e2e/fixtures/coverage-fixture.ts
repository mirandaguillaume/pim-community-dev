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
