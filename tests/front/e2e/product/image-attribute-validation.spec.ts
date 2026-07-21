import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  waitForLoadingMasks,
  attachFileToProductAttribute,
  saveProduct,
  createAttributeViaApi,
  addAttributeToFamilyViaApi,
  removeAttributeFromFamilyViaApi,
  createProductViaApi,
  deleteProductViaApi,
  deleteAttributeViaApi,
  getFirstFamilyCode,
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
 * Creates image attributes then adds them to an existing catalog family.
 */

const ts = Date.now();
const IMAGE_CODE = `pw_image_${ts}`;
const THUMB_CODE = `pw_thumb_${ts}`;
const PRODUCT_SKU = `pw-img-${ts}`;
const IMAGE_LABEL = 'Pw Image';
const THUMB_LABEL = 'Pw Thumb';

let familyCode: string | null = null;
let productId: string | null = null;

test.beforeAll(async ({browser}) => {
  const page = await browser.newPage();
  await login(page, 'admin', 'admin');

  familyCode = await getFirstFamilyCode(page);
  expect(familyCode, 'No family found in catalog — icecat_demo_dev must be loaded').toBeTruthy();

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
  expect(r1.ok(), `Failed to create attribute ${IMAGE_CODE}: ${r1.status()}`).toBe(true);

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
  expect(r2.ok(), `Failed to create attribute ${THUMB_CODE}: ${r2.status()}`).toBe(true);

  await addAttributeToFamilyViaApi(page, familyCode!, IMAGE_CODE);
  await addAttributeToFamilyViaApi(page, familyCode!, THUMB_CODE);

  const r4 = await createProductViaApi(page, PRODUCT_SKU, familyCode!);
  expect(r4.ok(), `Failed to create product ${PRODUCT_SKU}: ${r4.status()}`).toBe(true);
  const body = await r4.json();
  productId = body.meta?.id ?? body.id ?? null;

  await page.close();
});

test.afterAll(async ({browser}) => {
  const page = await browser.newPage();
  await login(page, 'admin', 'admin');
  if (productId) await deleteProductViaApi(page, productId);
  if (familyCode) {
    await removeAttributeFromFamilyViaApi(page, familyCode, IMAGE_CODE);
    await removeAttributeFromFamilyViaApi(page, familyCode, THUMB_CODE);
  }
  await deleteAttributeViaApi(page, IMAGE_CODE);
  await deleteAttributeViaApi(page, THUMB_CODE);
  await page.close();
});

test.beforeEach(async ({page}) => {
  await login(page, 'admin', 'admin');
});

async function navigateToProduct(page: Parameters<typeof login>[0]) {
  if (!productId) throw new Error('productId not set — beforeAll must have failed');
  await page.goto(`/#/enrich/product/${productId}`);
  await waitForLoadingMasks(page);
  await page.locator('.edit-form, .AknFormContainer').first().waitFor({timeout: 30_000});
}

test('Validate max file size constraint of image attribute', async ({page}) => {
  await navigateToProduct(page);
  await attachFileToProductAttribute(page, IMAGE_LABEL, 'akeneo.jpg');
  await saveProduct(page);
  await expect(page.getByText(/too large|exceed|10 kB|0\.01/i).first()).toBeVisible({timeout: 15_000});
});

test('Validate max file size constraint of scopable image attribute', async ({page}) => {
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
  await navigateToProduct(page);
  await attachFileToProductAttribute(page, IMAGE_LABEL, 'fanatic-freewave-76.gif');
  await saveProduct(page);
  await expect(
    page.getByText(/gif.*not allowed|allowed extensions are jpg|extension.*not allowed/i).first()
  ).toBeVisible({timeout: 15_000});
});

test('Validate allowed extensions constraint of scopable image attribute', async ({page}) => {
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
