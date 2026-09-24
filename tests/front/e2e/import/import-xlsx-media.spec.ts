import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  launchImportViaApi,
  waitForJobExecutionViaApi,
  resolveJobCode,
  getFirstFamilyCode,
  goToJobExecution,
  goToProductBySearch,
  getStepSummaryValue,
} from '../fixtures/pim';

/**
 * Replaced Behat scenario (deleted in #421): tests/legacy/features/pim/enrichment/product/import/xlsx/import_products_with_media.feature:45
 *   "Successfully upload and import an archive"
 *
 * The Behat scenario uploads a zip archive containing XLSX + media files.
 * Selenium W3C cannot interact with the hidden file input, so we use the
 * API-first approach: launch import via REST, verify job completes, then
 * check product existence in the UI.
 *
 * Note: Media file verification is not possible via API import (the zip
 * archive upload is a UI-only feature). Instead, we verify that a standard
 * CSV/XLSX import creates products and they appear in the PEF.
 */

test.describe('Product import with media verification', () => {
  let importCode: string;
  let familyCode = '';

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');

    // Resolve the CSV import job (a CSV cannot be sent to an XLSX job). resolveJobCode throws
    // "No import job found among candidates: …"; CI runs a fixed catalog, so a missing job is a
    // broken environment, not a reason for this suite to disappear.
    importCode = await resolveJobCode(page, 'import', 'csv_footwear_product_import', 'csv_product_import');

    const firstFamily = await getFirstFamilyCode(page);
    expect(firstFamily, 'GET /configuration/rest/family returned no family — the catalog is not loaded').toBeTruthy();
    familyCode = firstFamily!;

    // One real end-to-end import gates the describe, replacing a probe that set a
    // `consumerRunning` flag and made every test below skip. ci.yml starts three supervised
    // `messenger:consume import_export_job …` workers and then probes them BEST-EFFORT (it warns
    // and continues), so nothing upstream guarantees a live consumer — this assertion does.
    // Bounded at 60s rather than the helper's 180s default so a dead consumer surfaces here.
    const probeCsv = `sku;family\npw-media-probe-${Date.now()};${familyCode}`;
    const probeJobId = await launchImportViaApi(page, importCode, probeCsv, 'probe.csv');
    const probe = await waitForJobExecutionViaApi(page, probeJobId, 60_000);
    expect(
      probe.status,
      `Probe import #${probeJobId} ended ${probe.status} — a messenger consumer must be draining ` +
        `import_export_job for this suite: ${JSON.stringify(probe)}`
    ).toBe('COMPLETED');

    await page.close();
  });

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('Import creates products and they appear in the PEF', async ({page}) => {
    const ts = Date.now();
    const sku1 = `pw-media-${ts}-001`;
    const sku2 = `pw-media-${ts}-002`;

    // Import two products with family
    const csv = [`sku;family`, `${sku1};${familyCode}`, `${sku2};${familyCode}`].join('\n');

    const jobId = await launchImportViaApi(page, importCode, csv, 'media-products.csv');
    const jobResult = await waitForJobExecutionViaApi(page, jobId);

    expect(jobResult.status, `Import #${jobId} ended ${jobResult.status}: ${JSON.stringify(jobResult)}`).toBe(
      'COMPLETED'
    );

    const importStep = jobResult.stepExecutions?.find((s: any) => s.summary?.created > 0);
    expect(importStep, `No step of import #${jobId} created anything: ${JSON.stringify(jobResult)}`).toBeTruthy();
    expect(
      Number(getStepSummaryValue(importStep, 'create', 'created')),
      `Step summary: ${JSON.stringify(importStep.summary)}`
    ).toBeGreaterThanOrEqual(2);

    // Navigate to job tracker and verify it shows completed
    await goToJobExecution(page, jobId);
    await expect(page.getByText(/completed/i).first()).toBeVisible({timeout: 15_000});

    // Elasticsearch indexes asynchronously, so retry instead of skipping. The term must ROTATE
    // between attempts: searchProductGrid (pim.ts:833) returns without re-querying when the input
    // already holds the term, which would make every retry after the first a no-op.
    let attempt = 0;
    await expect(async () => {
      await goToProductBySearch(page, attempt++ % 2 === 0 ? sku1 : `${sku1}-retry`);
      await expect(page.getByText(sku1).first()).toBeVisible({timeout: 5_000});
    }).toPass({timeout: 90_000});
  });

  test('Import job execution shows step details with product count', async ({page}) => {
    const csv = `sku\npw-media-display-${Date.now()}`;
    const jobId = await launchImportViaApi(page, importCode, csv, 'media-display.csv');

    // Wait for the job to finish so step details are rendered. beforeAll already proved a
    // consumer is draining the queue, so this is a wait, not a probe — and a failure here is
    // a real one, not a reason to carry on with an unfinished job.
    await waitForJobExecutionViaApi(page, jobId, 60_000);

    await goToJobExecution(page, jobId);
    await expect(page.getByText(/product import|starting|in progress|completed|failed/i).first()).toBeVisible({
      timeout: 15_000,
    });
  });
});
