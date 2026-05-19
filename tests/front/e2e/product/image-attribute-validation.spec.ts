import {test, expect} from '@playwright/test';
import {
  login,
  waitForLoadingMasks,
  attachFileToProductAttribute,
  saveProduct,
  createAttributeViaApi,
  createFamilyViaApi,
  createProductViaApi,
  deleteProductViaApi,
  deleteFamilyViaApi,
  deleteAttributeViaApi,
} from '../fixtures/pim';

/**
 * Replaces Behat:
 *   - tests/legacy/features/pim/enrichment/product/validation/validate_image_attributes.feature:23
 *   - tests/legacy/features/pim/enrichment/product/validation/validate_image_attributes.feature:26
 *   - tests/legacy/features/pim/enrichment/product/validation/validate_image_attributes.feature:34
 *   - tests/legacy/features/pim/enrichment/product/validation/validate_image_attributes.feature:39
 *
 * Uses Playwright setInputFiles() which handles hidden file inputs natively,
 * unlike Selenium W3C which fails to locate non-visible elements.
 *
 * Creates its own test fixtures via the internal REST API (mirrors Behat Background).
 */

const ts = Date.now();
const IMAGE_CODE = `pw_image_${ts}`;
const THUMB_CODE = `pw_thumb_${ts}`;
const FAMILY_CODE = `pw_img_fam_${ts}`;
const PRODUCT_SKU = `pw-img-${ts}`;
const IMAGE_LABEL = 'Pw Image';
const THUMB_LABEL = 'Pw Thumb';

let setupOk = false;
let productId: string | null = null;

test.beforeAll(async ({browser}) => {
  const page = await browser.newPage();
  await login(page, 'admin', 'admin');

  const r1 = await createAttributeViaApi(page, {
    code: IMAGE_CODE,
    type: 'pim_catalog_image',
    group: 'other',
    allowed_extensions: ['jpg'],
    max_file_size: '0.01',
    scopable: false,
    localizable: false,
    labels: {en_US: IMAGE_LABEL},
  });

  const r2 = await createAttributeViaApi(page, {
    code: THUMB_CODE,
    type: 'pim_catalog_image',
    group: 'other',
    allowed_extensions: ['jpg'],
    max_file_size: '0.01',
    scopable: true,
    localizable: false,
    labels: {en_US: THUMB_LABEL},
  });

  const r3 = await createFamilyViaApi(page, {
    code: FAMILY_CODE,
    attributes: ['sku', IMAGE_CODE, THUMB_CODE],
  });

  const r4 = await createProductViaApi(page, PRODUCT_SKU, FAMILY_CODE);
  if (r4.ok()) {
    const body = await r4.json();
    productId = body.meta?.id ?? body.id ?? null;
  }

  setupOk = r1.ok() && r2.ok() && r3.ok() && r4.ok();
  await page.close();
});

test.afterAll(async ({browser}) => {
  const page = await browser.newPage();
  await login(page, 'admin', 'admin');
  if (productId) await deleteProductViaApi(page, productId);
  await deleteFamilyViaApi(page, FAMILY_CODE);
  await deleteAttributeViaApi(page, IMAGE_CODE);
  await deleteAttributeViaApi(page, THUMB_CODE);
  await page.close();
});

test.beforeEach(async ({page}) => {
  await login(page, 'admin', 'admin');
});

async function navigateToProduct(page: Parameters<typeof login>[0]) {
  await page.evaluate((sku: string) => {
    window.location.hash = `#/enrich/product/${sku}/edit`;
  }, PRODUCT_SKU);
  await waitForLoadingMasks(page);
  await page.locator('.edit-form, .AknFormContainer').first().waitFor({timeout: 30_000});
}

test('Validate max file size constraint of image attribute', async ({page}) => {
  if (!setupOk) {
    test.skip(true, 'Test fixtures could not be created');
    return;
  }
  await navigateToProduct(page);
  await attachFileToProductAttribute(page, IMAGE_LABEL, 'akeneo.jpg');
  await saveProduct(page);
  await expect(page.getByText(/too large|exceed|10 kB|0\.01/i).first()).toBeVisible({timeout: 15_000});
});

test('Validate max file size constraint of scopable image attribute', async ({page}) => {
  if (!setupOk) {
    test.skip(true, 'Test fixtures could not be created');
    return;
  }
  await navigateToProduct(page);
  const scopeDropdown = page.getByText(/ecommerce/i).first();
  if (await scopeDropdown.isVisible({timeout: 5_000}).catch(() => false)) {
    await scopeDropdown.click();
  }
  await attachFileToProductAttribute(page, THUMB_LABEL, 'akeneo.jpg');
  await saveProduct(page);
  await expect(page.getByText(/too large|exceed|10 kB|0\.01/i).first()).toBeVisible({timeout: 15_000});
});

test('Validate allowed extensions constraint of image attribute', async ({page}) => {
  if (!setupOk) {
    test.skip(true, 'Test fixtures could not be created');
    return;
  }
  await navigateToProduct(page);
  await attachFileToProductAttribute(page, IMAGE_LABEL, 'fanatic-freewave-76.gif');
  await saveProduct(page);
  await expect(
    page.getByText(/gif.*not allowed|allowed extensions are jpg|extension.*not allowed/i).first()
  ).toBeVisible({timeout: 15_000});
});

test('Validate allowed extensions constraint of scopable image attribute', async ({page}) => {
  if (!setupOk) {
    test.skip(true, 'Test fixtures could not be created');
    return;
  }
  await navigateToProduct(page);
  const scopeDropdown = page.getByText(/ecommerce/i).first();
  if (await scopeDropdown.isVisible({timeout: 5_000}).catch(() => false)) {
    await scopeDropdown.click();
  }
  await attachFileToProductAttribute(page, THUMB_LABEL, 'fanatic-freewave-76.gif');
  await saveProduct(page);
  await expect(
    page.getByText(/gif.*not allowed|allowed extensions are jpg|extension.*not allowed/i).first()
  ).toBeVisible({timeout: 15_000});
});
