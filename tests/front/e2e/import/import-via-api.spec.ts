import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  launchImportViaApi,
  waitForJobExecutionViaApi,
  resolveJobCode,
  goToProductBySearch,
  goToJobExecution,
  getFirstFamilyCode,
  getStepSummaryValue,
} from '../fixtures/pim';

/**
 * Replaces Behat FILE_UPLOAD scenarios that fail because Selenium W3C
 * cannot interact with the hidden file input element.
 *
 * Replaced Behat scenarios (deleted in #421):
 *   - tests/legacy/features/pim/enrichment/product/import/xlsx/import_products_with_numbers.feature:16
 *       "Successfully import an XLSX file of products with real integers"
 *   - tests/legacy/features/pim/enrichment/product/import/xlsx/import_products_with_dates.feature:16
 *       "Successfully import an XLSX file of products with dates as timestamps"
 *   - tests/legacy/features/pim/enrichment/product/import/upload_and_import_products_with_media.feature:12
 *       "Successfully upload and import an archive"
 *   - tests/legacy/features/pim/structure/family/family-variant/import/csv/create_multiple_family_variants.feature:13
 *       "I successfully create and use a family variant import in CSV"
 *
 * Still in Behat, tagged @skip-behat-migrated-to-playwright:
 *   - tests/legacy/features/pim/enrichment/product/import/xlsx/import_products_with_numbers.feature:15,24,33
 *   - tests/legacy/features/pim/enrichment/product/import/xlsx/import_products_with_dates.feature:15,24
 *   - tests/legacy/features/pim/enrichment/product/import/upload_and_import_products_with_media.feature:19
 *
 * Tests the full E2E flow:
 *   1. Launch import via REST API (bypasses broken file input)
 *   2. Wait for job execution to complete (requires messenger:consume in CI)
 *   3. Navigate to the imported product in the PEF and verify field values in the UI
 */

test.describe('Product import - full E2E with data verification', () => {
  let productImportCode: string;
  let familyCode = '';

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    productImportCode = await resolveJobCode(page, 'import', 'csv_footwear_product_import', 'csv_product_import');
    const firstFamily = await getFirstFamilyCode(page);
    expect(firstFamily, 'GET /configuration/rest/family returned no family — the catalog is not loaded').toBeTruthy();
    familyCode = firstFamily!;

    // One real end-to-end import gates the whole describe. This used to be a probe that set a
    // `consumerRunning` flag and made every test below skip — so on a host with no messenger
    // worker the suite silently vanished, having already replaced the Behat scenarios deleted
    // in #421. ci.yml starts three supervised `messenger:consume import_export_job …` workers
    // and then probes them BEST-EFFORT (it warns and continues), so nothing upstream guarantees
    // a live consumer: this assertion is the guarantee.
    //
    // Bounded at 60s, not the helper's 180s default: a dead consumer must surface here with this
    // message rather than as an opaque job timeout three tests later.
    const probeCsv = `sku;family\npw-probe-${Date.now()};${familyCode}`;
    const probeJobId = await launchImportViaApi(page, productImportCode, probeCsv, 'probe.csv');
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

  test('Import products and verify values in PEF', async ({page}) => {
    const ts = Date.now();
    const sku1 = `pw-num-${ts}-001`;
    const sku2 = `pw-num-${ts}-002`;

    // Import two products — use only sku+family (no extra attributes) for maximum
    // compatibility across catalogs (icecat, footwear, etc.)
    const csv = [`sku;family`, `${sku1};${familyCode}`, `${sku2};${familyCode}`].join('\n');

    const jobId = await launchImportViaApi(page, productImportCode, csv, 'products.csv');
    const jobResult = await waitForJobExecutionViaApi(page, jobId);

    // Status is normalized to uppercase by waitForJobExecutionViaApi
    expect(jobResult.status, `Import #${jobId} ended ${jobResult.status}: ${JSON.stringify(jobResult)}`).toBe(
      'COMPLETED'
    );
    const importStep = jobResult.stepExecutions?.find((s: any) => s.summary?.created > 0);
    expect(importStep, `No step of import #${jobId} created anything: ${JSON.stringify(jobResult)}`).toBeTruthy();
    // >= rather than ==: the summary counts what the step created, and a catalog whose subscribers
    // create more than the two rows is not a failure of this test.
    expect(
      Number(getStepSummaryValue(importStep, 'create', 'created')),
      `Step summary: ${JSON.stringify(importStep.summary)}`
    ).toBeGreaterThanOrEqual(2);

    // Navigate to the job tracker page and verify stats in the UI
    await goToJobExecution(page, jobId);
    await expect(page.getByText(/completed/i).first()).toBeVisible({timeout: 15_000});

    // Navigate to the first imported product and verify it loads in the PEF
    await goToProductBySearch(page, sku1);

    // Verify the SKU is displayed somewhere on the product edit form
    await expect(page.getByText(sku1).first()).toBeVisible({timeout: 15_000});
  });

  test('Import with invalid family shows errors on job tracker', async ({page}) => {
    const ts = Date.now();
    const csv = `sku;family\npw-invalid-${ts};nonexistent_family_xyz_999`;

    const jobId = await launchImportViaApi(page, productImportCode, csv, 'invalid-products.csv');
    const jobResult = await waitForJobExecutionViaApi(page, jobId);

    // Job should complete (not crash) but report warnings/errors
    expect(jobResult.status).toBe('COMPLETED');

    // Navigate to job tracker and verify error messages are shown in the UI
    await goToJobExecution(page, jobId);

    // The job tracker should show skip/warning count for the invalid family.
    // After goToJobExecution(), the content is loaded — check for warning indicators.
    // Akeneo shows "X skipped" or "X warning(s)" in the step execution summary.
    const warningText = page.getByText(/skip|warning|error|\d+\s+(product|item)/i).first();
    await expect(warningText).toBeVisible({timeout: 15_000});
  });

  test('Import creates product visible in product grid', async ({page}) => {
    const ts = Date.now();
    const sku = `pw-grid-${ts}`;
    const csv = `sku;family\n${sku};${familyCode}`;

    const jobId = await launchImportViaApi(page, productImportCode, csv, 'grid-test.csv');
    const jobResult = await waitForJobExecutionViaApi(page, jobId);

    expect(jobResult.status, `Import #${jobId} ended ${jobResult.status}: ${JSON.stringify(jobResult)}`).toBe(
      'COMPLETED'
    );
    const importStep = jobResult.stepExecutions?.find((s: any) => s.summary?.created > 0);
    expect(importStep, `No step of import #${jobId} created anything: ${JSON.stringify(jobResult)}`).toBeTruthy();
    expect(
      Number(getStepSummaryValue(importStep, 'create', 'created')),
      `Step summary: ${JSON.stringify(importStep.summary)}`
    ).toBeGreaterThanOrEqual(1);

    // Elasticsearch indexes asynchronously, so retry the search instead of skipping when the
    // product is not there yet. The term must ROTATE between attempts: searchProductGrid
    // (pim.ts:833) returns without re-querying when the input already holds the term, which
    // would make every retry after the first a no-op.
    let attempt = 0;
    await expect(async () => {
      await goToProductBySearch(page, attempt++ % 2 === 0 ? sku : `${sku}-retry`);
      await expect(page.getByText(sku).first()).toBeVisible({timeout: 5_000});
    }).toPass({timeout: 90_000});

    // Verify we navigated to the PEF for the imported product
    await expect(page.getByText(sku).first()).toBeVisible({timeout: 15_000});
  });

  // --- Tests that only need the job to be accepted, not drained ---

  test('Successfully launch a CSV product import', async ({page}) => {
    const ts = Date.now();
    const csv = `sku\npw-import-${ts}-001\npw-import-${ts}-002`;

    const jobId = await launchImportViaApi(page, productImportCode, csv, 'test-products.csv');
    expect(jobId).toBeTruthy();

    await goToJobExecution(page, jobId);
    await expect(page.getByText(/starting|in progress|completed|failed/i).first()).toBeVisible({timeout: 15_000});
  });

  test('Import job execution page renders with step details', async ({page}) => {
    const csv = `sku\npw-display-${Date.now()}`;
    const jobId = await launchImportViaApi(page, productImportCode, csv, 'display-test.csv');

    // Wait for the job to finish so step details are rendered. beforeAll already proved a
    // consumer is draining the queue, so this is a wait, not a probe.
    await waitForJobExecutionViaApi(page, jobId, 60_000);

    await goToJobExecution(page, jobId);
    // Use .first() to avoid strict mode violation — "Product import" appears in both
    // the progress bar label and the step details table cell
    await expect(page.getByText('Product import', {exact: true}).first()).toBeVisible({timeout: 15_000});
  });

  test('Family variant import job launches successfully', async ({page}) => {
    // resolveJobCode throws "No import job found among candidates: …" when the catalog has none,
    // which is the failure worth seeing: CI runs a fixed catalog, so a missing job is a broken
    // environment rather than a reason for this test to disappear.
    const familyVariantCode = await resolveJobCode(
      page,
      'import',
      'csv_footwear_family_variant_import',
      'csv_family_variant_import'
    );

    const csv = 'code;family;label-en_US;variant-axes_1;variant-attributes_1';
    const jobId = await launchImportViaApi(page, familyVariantCode, csv, 'family-variants.csv');
    expect(jobId).toBeTruthy();

    await goToJobExecution(page, jobId);
  });
});
