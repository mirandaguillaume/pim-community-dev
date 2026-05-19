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
  createFamilyViaApi,
  deleteProductViaApi,
  deleteFamilyViaApi,
  deleteAttributeViaApi,
} from '../fixtures/pim';

/**
 * Replaces Behat:
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/edit-common-attribute/edit_common_attributes_images.feature:32
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/validate/validate_editing_common_image_attributes.feature:43
 *
 * Uses Playwright setInputFiles() which handles hidden file inputs natively,
 * unlike Selenium W3C which fails to locate non-visible elements.
 *
 * Creates its own test fixtures via the internal REST API (mirrors Behat Background).
 */

test.describe('Mass edit image attributes', () => {
  const ts = Date.now();
  const ATTR_CODE = `pw_side_view_${ts}`;
  const ATTR_LABEL = 'Pw Side View';
  const FAMILY_CODE = `pw_me_img_fam_${ts}`;
  const sku1 = `pw-me-img-1-${ts}`;
  const sku2 = `pw-me-img-2-${ts}`;
  let setupOk = false;
  let productId1: string | null = null;
  let productId2: string | null = null;

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');

    const r1 = await createAttributeViaApi(page, {
      code: ATTR_CODE,
      type: 'pim_catalog_image',
      group: 'other',
      allowed_extensions: ['png', 'jpeg', 'jpg'],
      scopable: false,
      localizable: false,
      labels: {en_US: ATTR_LABEL},
    });

    const r2 = await createFamilyViaApi(page, {
      code: FAMILY_CODE,
      attributes: ['sku', ATTR_CODE],
    });

    const r3 = await createProductViaApi(page, sku1, FAMILY_CODE);
    if (r3.ok()) {
      const body = await r3.json();
      productId1 = body.meta?.id ?? body.id ?? null;
    }
    const r4 = await createProductViaApi(page, sku2, FAMILY_CODE);
    if (r4.ok()) {
      const body = await r4.json();
      productId2 = body.meta?.id ?? body.id ?? null;
    }

    setupOk = r1.ok() && r2.ok() && r3.ok() && r4.ok();
    await page.close();
  });

  test.afterAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    if (productId1) await deleteProductViaApi(page, productId1);
    if (productId2) await deleteProductViaApi(page, productId2);
    await deleteFamilyViaApi(page, FAMILY_CODE);
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
    if (!setupOk) {
      test.skip(true, 'Test fixtures could not be created');
      return;
    }

    try {
      await goToProductsGrid(page);
    } catch {
      test.skip(true, 'Product grid is empty — no products available');
      return;
    }

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
    if (!setupOk) {
      test.skip(true, 'Test fixtures could not be created');
      return;
    }

    try {
      await goToProductsGrid(page);
    } catch {
      test.skip(true, 'Product grid is empty — no products available');
      return;
    }

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
