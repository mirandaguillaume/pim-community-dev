import {test, expect} from '@playwright/test';
import {
  login,
  goToProductsGrid,
  waitForLoadingMasks,
  waitForJobExecutionViaApi,
  ensureProductExists,
  selectProductsBySku,
  openBulkEditAttributeValues,
  addAttributeToMassEdit,
  attachFileToMassEditAttribute,
  confirmMassEdit,
  productHasAttributeValue,
  createAttributeViaApi,
  addAttributeToFamilyViaApi,
  removeAttributeFromFamilyViaApi,
  deleteAttributeViaApi,
  getFirstProductsFromGrid,
  getProductFamilyCode,
} from '../fixtures/pim';

/**
 * Replaces Behat:
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/edit-common-attribute/edit_common_attributes_images.feature:32
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/validate/validate_editing_common_image_attributes.feature:43
 *
 * Uses Playwright setInputFiles() which handles hidden file inputs natively,
 * unlike Selenium W3C which fails to locate non-visible elements.
 *
 * Uses existing indexed catalog products to avoid Elasticsearch indexing lag in CI.
 */

test.describe('Mass edit image attributes', () => {
  const ts = Date.now();
  const ATTR_CODE = `pw_side_view_${ts}`;
  const ATTR_LABEL = 'Pw Side View';

  let sku1: string | null = null;
  let sku2: string | null = null;
  let uuid1: string | null = null;
  let uuid2: string | null = null;
  let families: string[] = [];

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');

    const products = await getFirstProductsFromGrid(page, 2);
    expect(
      products.length,
      'Need at least 2 indexed products in the catalog — icecat_demo_dev must be loaded'
    ).toBeGreaterThanOrEqual(2);

    sku1 = products[0].sku;
    sku2 = products[1].sku;
    uuid1 = products[0].uuid;
    uuid2 = products[1].uuid;

    // Resolve the family CODE from the product API (the grid's `.family` is the localized
    // LABEL, which 404s against the code-keyed family endpoint — and which family lands
    // "first" in the ES-ordered grid varies across shards). Deduplicate so we PUT each once.
    const familyCodes = await Promise.all([getProductFamilyCode(page, uuid1!), getProductFamilyCode(page, uuid2!)]);
    families = [...new Set(familyCodes.filter((code): code is string => Boolean(code)))];
    expect(families.length, 'Products must belong to at least one family').toBeGreaterThan(0);

    const r1 = await createAttributeViaApi(page, {
      code: ATTR_CODE,
      type: 'pim_catalog_image',
      group: 'other',
      allowed_extensions: ['png', 'jpeg', 'jpg'],
      scopable: false,
      localizable: false,
      labels: {en_US: ATTR_LABEL},
    });
    expect(r1.ok(), `Failed to create attribute ${ATTR_CODE}: ${r1.status()}`).toBe(true);

    for (const familyCode of families) {
      await addAttributeToFamilyViaApi(page, familyCode, ATTR_CODE);
    }

    await page.close();
  });

  test.afterAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    for (const familyCode of families) {
      await removeAttributeFromFamilyViaApi(page, familyCode, ATTR_CODE);
    }
    await deleteAttributeViaApi(page, ATTR_CODE);
    await page.close();
  });

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    await ensureProductExists(page);
  });

  /**
   * Replaces Behat: edit_common_attributes_images.feature:32
   * Successfully update many images values at once
   */
  test('Successfully update many images values at once', {timeout: 720_000}, async ({page}) => {
    // timeout: 720_000 covers beforeEach + UI flow + pollForNewMassEditJob (≤180s) + waitForJobExecutionViaApi (≤420s)
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1!, sku2!]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'SNKRS-1R.png');

    const jobId = await confirmMassEdit(page);
    expect(
      jobId,
      'Mass-edit job not registered in process-tracker within 60s — consumer may be dead or queue backlog too large'
    ).toBeTruthy();
    const result = await waitForJobExecutionViaApi(page, jobId!, 420_000);
    expect(['COMPLETED', 'completed']).toContain(result.status?.toUpperCase?.() ?? result.status);

    expect(await productHasAttributeValue(page, uuid1!, ATTR_CODE)).toBe(true);
    expect(await productHasAttributeValue(page, uuid2!, ATTR_CODE)).toBe(true);
  });

  /**
   * Replaces Behat: validate_editing_common_image_attributes.feature:43
   * Mass edit image attribute — set, clear, and validate extension
   */
  test('Mass edit image attribute — set, clear, and validate extension', {timeout: 1_380_000}, async ({page}) => {
    // timeout: 1_380_000 covers 3 wizard flows × (≤180s poll + ≤360s job) on slow CI runners
    await goToProductsGrid(page);

    // Step 1: set image on sku1 + sku2
    await selectProductsBySku(page, [sku1!, sku2!]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'SNKRS-1R.png');
    const jobId1 = await confirmMassEdit(page);
    expect(jobId1, 'Step 1: mass-edit job not registered in process-tracker within 60s').toBeTruthy();
    await waitForJobExecutionViaApi(page, jobId1!, 360_000);
    expect(await productHasAttributeValue(page, uuid1!, ATTR_CODE)).toBe(true);
    expect(await productHasAttributeValue(page, uuid2!, ATTR_CODE)).toBe(true);

    // Step 2: clear image by confirming without attaching a file
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1!, sku2!]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    const jobId2 = await confirmMassEdit(page);
    expect(jobId2, 'Step 2: mass-edit job not registered in process-tracker within 120s').toBeTruthy();
    await waitForJobExecutionViaApi(page, jobId2!, 360_000);
    expect(await productHasAttributeValue(page, uuid1!, ATTR_CODE)).toBe(false);
    expect(await productHasAttributeValue(page, uuid2!, ATTR_CODE)).toBe(false);

    // Step 3: attach invalid extension (.gif) and verify validation error
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1!, sku2!]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'bic-core-148.gif');
    await page.locator('.wizard-action[data-action-target="confirm"]').click();
    await waitForLoadingMasks(page);
    await expect(
      page.getByText(/gif.*not allowed|allowed extensions are png|extension.*not allowed/i).first()
    ).toBeVisible({timeout: 15_000});

    expect(await productHasAttributeValue(page, uuid1!, ATTR_CODE)).toBe(false);
    expect(await productHasAttributeValue(page, uuid2!, ATTR_CODE)).toBe(false);
  });
});
