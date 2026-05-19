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
  createProductViaApi,
  createAttributeViaApi,
  addAttributeToFamilyViaApi,
  removeAttributeFromFamilyViaApi,
  deleteProductViaApi,
  deleteAttributeViaApi,
  getFirstFamilyCode,
} from '../fixtures/pim';

/**
 * Replaces Behat:
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/edit-common-attribute/edit_common_attributes_images.feature:32
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/validate/validate_editing_common_image_attributes.feature:43
 *
 * Uses Playwright setInputFiles() which handles hidden file inputs natively,
 * unlike Selenium W3C which fails to locate non-visible elements.
 *
 * Creates an image attribute and adds it to an existing catalog family.
 */

test.describe('Mass edit image attributes', () => {
  const ts = Date.now();
  const ATTR_CODE = `pw_side_view_${ts}`;
  const ATTR_LABEL = 'Pw Side View';
  const sku1 = `pw-me-img-1-${ts}`;
  const sku2 = `pw-me-img-2-${ts}`;
  let familyCode: string | null = null;
  let productId1: string | null = null;
  let productId2: string | null = null;

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');

    familyCode = await getFirstFamilyCode(page);
    expect(familyCode, 'No family found in catalog — icecat_demo_dev must be loaded').toBeTruthy();

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

    await addAttributeToFamilyViaApi(page, familyCode!, ATTR_CODE);

    const r3 = await createProductViaApi(page, sku1, familyCode!);
    expect(r3.ok(), `Failed to create product ${sku1}: ${r3.status()}`).toBe(true);
    const body3 = await r3.json();
    productId1 = body3.meta?.id ?? body3.id ?? null;

    const r4 = await createProductViaApi(page, sku2, familyCode!);
    expect(r4.ok(), `Failed to create product ${sku2}: ${r4.status()}`).toBe(true);
    const body4 = await r4.json();
    productId2 = body4.meta?.id ?? body4.id ?? null;

    await page.close();
  });

  test.afterAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    if (productId1) await deleteProductViaApi(page, productId1);
    if (productId2) await deleteProductViaApi(page, productId2);
    if (familyCode) await removeAttributeFromFamilyViaApi(page, familyCode, ATTR_CODE);
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
  test('Successfully update many images values at once', async ({page}) => {
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1, sku2]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'SNKRS-1R.png');

    const jobId = await confirmMassEdit(page);
    if (jobId) {
      const result = await waitForJobExecutionViaApi(page, jobId);
      expect(['COMPLETED', 'completed']).toContain(result.status?.toUpperCase?.() ?? result.status);
    } else {
      await expect(page.getByText(/mass edit|action will be processed/i).first()).toBeVisible({
        timeout: 30_000,
      });
    }

    expect(await productHasAttributeValue(page, sku1, ATTR_CODE)).toBe(true);
    expect(await productHasAttributeValue(page, sku2, ATTR_CODE)).toBe(true);
  });

  /**
   * Replaces Behat: validate_editing_common_image_attributes.feature:43
   * Mass edit image attribute — set, clear, and validate extension
   */
  test('Mass edit image attribute — set, clear, and validate extension', async ({page}) => {
    await goToProductsGrid(page);

    // Step 1: set image on sku1 + sku2
    await selectProductsBySku(page, [sku1, sku2]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'SNKRS-1R.png');
    const jobId1 = await confirmMassEdit(page);
    if (jobId1) {
      await waitForJobExecutionViaApi(page, jobId1);
    }
    expect(await productHasAttributeValue(page, sku1, ATTR_CODE)).toBe(true);
    expect(await productHasAttributeValue(page, sku2, ATTR_CODE)).toBe(true);

    // Step 2: clear image by confirming without attaching a file
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1, sku2]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    const jobId2 = await confirmMassEdit(page);
    if (jobId2) {
      await waitForJobExecutionViaApi(page, jobId2);
    }
    expect(await productHasAttributeValue(page, sku1, ATTR_CODE)).toBe(false);
    expect(await productHasAttributeValue(page, sku2, ATTR_CODE)).toBe(false);

    // Step 3: attach invalid extension (.gif) and verify validation error
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1, sku2]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'bic-core-148.gif');
    await page
      .getByRole('button', {name: /next|confirm/i})
      .first()
      .click();
    await waitForLoadingMasks(page);
    await expect(
      page.getByText(/gif.*not allowed|allowed extensions are png|extension.*not allowed/i).first()
    ).toBeVisible({timeout: 15_000});

    expect(await productHasAttributeValue(page, sku1, ATTR_CODE)).toBe(false);
    expect(await productHasAttributeValue(page, sku2, ATTR_CODE)).toBe(false);
  });
});
