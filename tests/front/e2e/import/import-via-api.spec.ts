import {test, expect} from '@playwright/test';
import {login, launchImportViaApi, waitForLoadingMasks, resolveJobCode} from '../fixtures/pim';

/**
 * Replaces Behat FILE_UPLOAD scenarios that fail because Selenium W3C
 * cannot interact with the hidden file input element:
 *
 *   - import_products_with_dates.feature:15,23,31
 *   - import_products_with_numbers.feature:23,31,39
 *   - import_products_with_media.feature:44
 *   - upload_and_import_products_with_media.feature:11,45
 *   - create_multiple_family_variants.feature:12
 *
 * These tests verify the file upload + job launch pipeline via the internal REST API,
 * then check that the job execution tracker page renders correctly.
 * Note: The actual import batch processing requires a message consumer daemon,
 * so we only test the launch flow and job tracker rendering, not execution results.
 */

test.describe('Product import via file upload API', () => {
  let productImportCode: string;

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    productImportCode = await resolveJobCode(page, 'import', 'csv_footwear_product_import', 'csv_product_import');
    await page.close();
  });

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('Successfully launch a CSV product import', async ({page}) => {
    const ts = Date.now();
    const csv = `sku\npw-import-${ts}-001\npw-import-${ts}-002`;

    // Launch the import — this validates file upload + job creation
    const jobId = await launchImportViaApi(page, productImportCode, csv, 'test-products.csv');
    expect(jobId).toBeTruthy();

    // Navigate to the job tracker page and verify it renders
    await page.goto(`/#/job/show/${jobId}`);
    await waitForLoadingMasks(page);

    // The job tracker page shows breadcrumbs including "EXECUTION DETAILS"
    // and a status badge (STARTING, IN PROGRESS, COMPLETED, etc.)
    await expect(page.getByText(/execution details/i)).toBeVisible({timeout: 15_000});
    await expect(page.getByText(/starting|in progress|completed|failed/i).first()).toBeVisible({timeout: 15_000});
  });

  test('Import with invalid data launches without error', async ({page}) => {
    const ts = Date.now();
    const csv = `sku;family\npw-invalid-${ts};nonexistent_family_xyz_999`;

    // The launch should still succeed (validation happens during execution, not launch)
    const jobId = await launchImportViaApi(page, productImportCode, csv, 'invalid-products.csv');
    expect(jobId).toBeTruthy();

    // Job tracker page should render even with invalid input
    await page.goto(`/#/job/show/${jobId}`);
    await waitForLoadingMasks(page);
    await expect(page.getByText(/execution details/i)).toBeVisible({timeout: 15_000});
  });

  test('Import job execution page renders with step details', async ({page}) => {
    const csv = `sku\npw-display-${Date.now()}`;
    const jobId = await launchImportViaApi(page, productImportCode, csv, 'display-test.csv');

    await page.goto(`/#/job/show/${jobId}`);
    await waitForLoadingMasks(page);

    // Verify the page shows import step names (always present on import job execution pages)
    await expect(page.getByText(/execution details/i)).toBeVisible({timeout: 15_000});
    await expect(page.getByText('Product import', {exact: true})).toBeVisible({timeout: 15_000});
  });

  test('Family variant import job launches successfully', async ({page}) => {
    let familyVariantCode: string;
    try {
      familyVariantCode = await resolveJobCode(
        page,
        'import',
        'csv_footwear_family_variant_import',
        'csv_family_variant_import'
      );
    } catch {
      test.skip(true, 'No family variant import job available in this catalog');
      return;
    }

    const csv = 'code;family;label-en_US;variant-axes_1;variant-attributes_1';
    const jobId = await launchImportViaApi(page, familyVariantCode, csv, 'family-variants.csv');
    expect(jobId).toBeTruthy();

    // Verify the job tracker page renders
    await page.goto(`/#/job/show/${jobId}`);
    await waitForLoadingMasks(page);
    await expect(page.getByText(/execution details/i)).toBeVisible({timeout: 15_000});
  });
});
