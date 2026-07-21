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
 *   - tests/legacy/features/pim/enrichment/product/pef/join_an_image_to_a_product.feature:22
 *   - tests/legacy/features/pim/enrichment/product/pef/join_an_image_to_a_product.feature:27
 *   - tests/legacy/features/pim/enrichment/product/pef/join_an_image_to_a_product.feature:32
 *   - tests/legacy/features/pim/enrichment/product/pef/join_an_image_to_a_product.feature:38
 *
 * Uses Playwright setInputFiles() (via attachFileToProductAttribute) which handles hidden
 * file inputs natively — Selenium 4 W3C refuses to interact with non-visible elements.
 * The preview popin uses $.slimbox which renders #lbImage when opened.
 */

const ts = Date.now();
const ATTR_CODE = `pw_visual_${ts}`;
const ATTR_LABEL = 'Pw Visual';
const PRODUCT_SKU = `pw-img-pef-${ts}`;

let familyCode: string | null = null;
let productId: string | null = null;

test.beforeAll(async ({browser}) => {
  const page = await browser.newPage();
  await login(page, 'admin', 'admin');

  familyCode = await getFirstFamilyCode(page);
  expect(familyCode, 'No family found in catalog — a catalog must be loaded').toBeTruthy();

  const attrResp = await createAttributeViaApi(page, {
    code: ATTR_CODE,
    type: 'pim_catalog_image',
    group: 'other',
    allowed_extensions: ['jpg', 'gif'],
    scopable: false,
    localizable: false,
    labels: {en_US: ATTR_LABEL},
  });
  expect(attrResp.ok(), `Failed to create attribute ${ATTR_CODE}: ${attrResp.status()}`).toBe(true);

  await addAttributeToFamilyViaApi(page, familyCode!, ATTR_CODE);

  const productResp = await createProductViaApi(page, PRODUCT_SKU, familyCode!);
  expect(productResp.ok(), `Failed to create product ${PRODUCT_SKU}: ${productResp.status()}`).toBe(true);
  const body = await productResp.json();
  productId = body.meta?.id ?? body.id ?? null;

  await page.close();
});

test.afterAll(async ({browser}) => {
  const page = await browser.newPage();
  await login(page, 'admin', 'admin');
  if (productId) await deleteProductViaApi(page, productId);
  if (familyCode) await removeAttributeFromFamilyViaApi(page, familyCode, ATTR_CODE);
  await deleteAttributeViaApi(page, ATTR_CODE);
  await page.close();
});

test.beforeEach(async ({page}) => {
  await login(page, 'admin', 'admin');
});

async function dismissOverlay(page: Parameters<typeof login>[0]) {
  // #overlay.AknOverlay--show appears after login and blocks all clicks (position:fixed 100%).
  // Remove the class directly so clicks on form elements are not intercepted.
  await page.evaluate(() => document.getElementById('overlay')?.classList.remove('AknOverlay--show')).catch(() => {});
}

async function navigateToProduct(page: Parameters<typeof login>[0]) {
  if (!productId) throw new Error('productId not set — beforeAll must have failed');
  await page.goto(`/#/enrich/product/${productId}`);
  await waitForLoadingMasks(page);
  await page.locator('.edit-form, .AknFormContainer').first().waitFor({timeout: 30_000});
  await dismissOverlay(page);
  // group-selector.js change() handler reads event.currentTarget.dataset.element.
  // Backbone delegates events via jQuery — use jQuery's $.trigger() which correctly
  // sets currentTarget on delegated handlers, unlike native dispatchEvent which can
  // behave inconsistently across runners with animated dropdowns.
  const groupSelector = page.locator('div.group-selector');
  if (await groupSelector.isVisible({timeout: 8_000}).catch(() => false)) {
    await page.evaluate(() => {
      const li = document.querySelector('.group-selector li[data-element="other"]');
      if (li && (window as any).$) (window as any).$(li).trigger('click');
    });
    // Wait for the "Other" panel to render in the DOM before proceeding
    await page
      .locator('.group-selector .AknActionButton-highlight')
      .filter({hasText: /other/i})
      .waitFor({timeout: 10_000})
      .catch(() => {});
    await waitForLoadingMasks(page);
  }
}

async function clearImageAttribute(page: Parameters<typeof login>[0]) {
  await dismissOverlay(page);
  // The clear button is a <span class="clear-field"> with a trash icon.
  // Scoped to the attribute container to avoid false positives with other image fields.
  const container = page
    .locator('.AknFieldContainer')
    .filter({has: page.locator('.AknFieldContainer-label', {hasText: ATTR_LABEL})})
    .first();
  await container.locator('.clear-field').click({force: true});
}

test('Successfully upload an image', async ({page}) => {
  await navigateToProduct(page);
  await attachFileToProductAttribute(page, ATTR_LABEL, 'akeneo.jpg');
  await saveProduct(page);
  await expect(page.getByText('akeneo.jpg').first()).toBeVisible({timeout: 15_000});
});

test('Successfully display the image in a popin', async ({page}) => {
  // First upload and save so the file is persisted
  await navigateToProduct(page);
  await attachFileToProductAttribute(page, ATTR_LABEL, 'akeneo.jpg');
  await saveProduct(page);
  await expect(page.getByText('akeneo.jpg').first()).toBeVisible({timeout: 15_000});

  // The .open-media span triggers $.slimbox(previewUrl) on click.
  // Slimbox renders #lbImage inside #lbCenter as a visible <img>.
  const container = page
    .locator('.AknFieldContainer')
    .filter({has: page.locator('.AknFieldContainer-label', {hasText: ATTR_LABEL})})
    .first();
  await container.locator('.open-media').click();
  await expect(page.locator('#lbImage')).toBeVisible({timeout: 15_000});
});

test('Successfully remove an image', async ({page}) => {
  await navigateToProduct(page);
  await attachFileToProductAttribute(page, ATTR_LABEL, 'akeneo.jpg');
  await saveProduct(page);
  await expect(page.getByText('akeneo.jpg').first()).toBeVisible({timeout: 15_000});

  await clearImageAttribute(page);
  await saveProduct(page);
  await expect(page.getByText('akeneo.jpg')).toBeHidden({timeout: 15_000});
});

test('Successfully replace an image', async ({page}) => {
  await navigateToProduct(page);
  await attachFileToProductAttribute(page, ATTR_LABEL, 'akeneo.jpg');
  await saveProduct(page);
  await expect(page.getByText('akeneo.jpg').first()).toBeVisible({timeout: 15_000});

  await clearImageAttribute(page);
  await attachFileToProductAttribute(page, ATTR_LABEL, 'bic-core-148.gif');
  await saveProduct(page);
  await expect(page.getByText('akeneo.jpg')).toBeHidden({timeout: 15_000});
  await expect(page.getByText('bic-core-148.gif').first()).toBeVisible({timeout: 15_000});
});
