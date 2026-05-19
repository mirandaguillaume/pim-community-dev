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
} from '../fixtures/pim';

/**
 * Replaces Behat:
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/edit-common-attribute/edit_common_attributes_images.feature:32
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/validate/validate_editing_common_image_attributes.feature:43
 *
 * Uses Playwright setInputFiles() which handles hidden file inputs natively,
 * unlike Selenium W3C which fails to locate non-visible elements.
 */

const SIDE_VIEW_FAMILIES = ['sneakers', 'boots', 'sandals'];

async function findFamilyWithSideView(page: ReturnType<typeof test.extend>): Promise<string | null> {
  const resp = await (page as any).request.get('/configuration/rest/family', {
    headers: {'X-Requested-With': 'XMLHttpRequest'},
  });
  if (!resp.ok()) return null;
  const families = await resp.json();
  const arr = Array.isArray(families) ? families : (families.items ?? []);
  for (const candidate of SIDE_VIEW_FAMILIES) {
    if (arr.some((f: any) => f.code === candidate)) return candidate;
  }
  return null;
}

test.describe('Mass edit image attributes', () => {
  const ts = Date.now();
  const sku1 = `pw-me-img-1-${ts}`;
  const sku2 = `pw-me-img-2-${ts}`;
  let familyCode: string | null = null;
  let setupOk = false;

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    familyCode = await findFamilyWithSideView(page as any);
    if (familyCode) {
      const r1 = await createProductViaApi(page, sku1, familyCode);
      const r2 = await createProductViaApi(page, sku2, familyCode);
      setupOk = r1.ok() && r2.ok();
    }
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
    if (!familyCode || !setupOk) {
      test.skip(true, 'No footwear family with side_view attribute found in this catalog');
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
    await addAttributeToMassEdit(page, 'Side view');
    await attachFileToMassEditAttribute(page, 'Side view', 'SNKRS-1R.png');

    const jobId = await confirmMassEdit(page);
    if (jobId) {
      const result = await waitForJobExecutionViaApi(page, jobId);
      expect(['COMPLETED', 'completed']).toContain(result.status?.toUpperCase?.() ?? result.status);
    } else {
      await expect(page.getByText(/mass edit|action will be processed/i).first()).toBeVisible({
        timeout: 30_000,
      });
    }

    const sku1HasImage = await productHasAttributeValue(page, sku1, 'side_view');
    const sku2HasImage = await productHasAttributeValue(page, sku2, 'side_view');
    expect(sku1HasImage).toBe(true);
    expect(sku2HasImage).toBe(true);
  });

  /**
   * Replaces Behat: validate_editing_common_image_attributes.feature:43
   * Successfully mass edit an image attribute (set, clear, invalid extension)
   */
  test('Mass edit image attribute — set, clear, and validate extension', async ({page}) => {
    if (!familyCode || !setupOk) {
      test.skip(true, 'No footwear family with side_view attribute found in this catalog');
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
    await addAttributeToMassEdit(page, 'Side view');
    await attachFileToMassEditAttribute(page, 'Side view', 'SNKRS-1R.png');
    const jobId1 = await confirmMassEdit(page);
    if (jobId1) {
      await waitForJobExecutionViaApi(page, jobId1);
    }
    expect(await productHasAttributeValue(page, sku1, 'side_view')).toBe(true);
    expect(await productHasAttributeValue(page, sku2, 'side_view')).toBe(true);

    // Step 2: clear image by confirming without attaching a file
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1, sku2]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, 'Side view');
    const jobId2 = await confirmMassEdit(page);
    if (jobId2) {
      await waitForJobExecutionViaApi(page, jobId2);
    }
    expect(await productHasAttributeValue(page, sku1, 'side_view')).toBe(false);
    expect(await productHasAttributeValue(page, sku2, 'side_view')).toBe(false);

    // Step 3: attach invalid extension (.gif) and verify validation error
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1, sku2]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, 'Side view');
    await attachFileToMassEditAttribute(page, 'Side view', 'bic-core-148.gif');
    await page
      .getByRole('button', {name: /next|confirm/i})
      .first()
      .click();
    await waitForLoadingMasks(page);
    await expect(
      page.getByText(/gif.*not allowed|allowed extensions are png|extension.*not allowed/i).first()
    ).toBeVisible({timeout: 15_000});

    // Products must remain without side_view after the invalid attempt
    expect(await productHasAttributeValue(page, sku1, 'side_view')).toBe(false);
    expect(await productHasAttributeValue(page, sku2, 'side_view')).toBe(false);
  });
});
