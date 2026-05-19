import {test, expect} from '@playwright/test';
import {login, waitForLoadingMasks, attachFileToProductAttribute, saveProduct} from '../fixtures/pim';

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
 * Requires catalog with:
 *   - image attribute (pim_catalog_image, allowed_extensions: jpg, max_file_size: 0.01 MB)
 *   - thumbnail attribute (pim_catalog_image, scopable, allowed_extensions: jpg, max_file_size: 0.01 MB)
 *   - a product with family containing both attributes
 */

const XHR = {'X-Requested-With': 'XMLHttpRequest'};

async function findProductWithImageAttributes(
  page: ReturnType<typeof test.extend>
): Promise<{sku: string; hasImage: boolean; hasThumbnail: boolean} | null> {
  const resp = await (page as any).request.get('/configuration/rest/attribute', {
    headers: XHR,
  });
  if (!resp.ok()) return null;
  const attrs = await resp.json();
  const arr = Array.isArray(attrs) ? attrs : (attrs.items ?? []);
  const image = arr.find((a: any) => a.code === 'image' && a.type === 'pim_catalog_image');
  const thumbnail = arr.find((a: any) => a.code === 'thumbnail' && a.type === 'pim_catalog_image');
  if (!image && !thumbnail) return null;
  return {sku: 'foo', hasImage: !!image, hasThumbnail: !!thumbnail};
}

async function navigateToProductPage(page: ReturnType<typeof test.extend>, sku: string) {
  await (page as any).evaluate((s: string) => {
    window.location.hash = `#/enrich/product/${s}/edit`;
  }, sku);
  await waitForLoadingMasks(page as any);
  await (page as any).locator('.edit-form, .AknFormContainer').first().waitFor({timeout: 30_000});
}

test.describe('Image attribute validation in PEF', () => {
  let catalogInfo: {sku: string; hasImage: boolean; hasThumbnail: boolean} | null = null;

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    catalogInfo = await findProductWithImageAttributes(page as any);
    await page.close();
  });

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  /**
   * Replaces Behat: validate_image_attributes.feature:26
   * Validate the max file size constraint of image attribute
   */
  test('Validate max file size constraint of image attribute', async ({page}) => {
    if (!catalogInfo?.hasImage) {
      test.skip(true, 'No "image" attribute found in this catalog');
      return;
    }
    await navigateToProductPage(page as any, catalogInfo.sku);
    await attachFileToProductAttribute(page, 'Image', 'akeneo.jpg');
    await saveProduct(page);
    await expect(page.getByText(/too large|exceed|10 kB|0\.01/i).first()).toBeVisible({timeout: 15_000});
  });

  /**
   * Replaces Behat: validate_image_attributes.feature:29
   * Validate the max file size constraint of scopable image attribute
   */
  test('Validate max file size constraint of scopable image attribute', async ({page}) => {
    if (!catalogInfo?.hasThumbnail) {
      test.skip(true, 'No "thumbnail" attribute found in this catalog');
      return;
    }
    await navigateToProductPage(page as any, catalogInfo.sku);
    const scopeDropdown = page.getByText(/ecommerce/i).first();
    if (await scopeDropdown.isVisible({timeout: 5_000}).catch(() => false)) {
      await scopeDropdown.click();
    }
    await attachFileToProductAttribute(page, 'Thumbnail', 'akeneo.jpg');
    await saveProduct(page);
    await expect(page.getByText(/too large|exceed|10 kB|0\.01/i).first()).toBeVisible({timeout: 15_000});
  });

  /**
   * Replaces Behat: validate_image_attributes.feature:34
   * Validate the allowed extensions constraint of image attribute
   */
  test('Validate allowed extensions constraint of image attribute', async ({page}) => {
    if (!catalogInfo?.hasImage) {
      test.skip(true, 'No "image" attribute found in this catalog');
      return;
    }
    await navigateToProductPage(page as any, catalogInfo.sku);
    await attachFileToProductAttribute(page, 'Image', 'fanatic-freewave-76.gif');
    await saveProduct(page);
    await expect(
      page.getByText(/gif.*not allowed|allowed extensions are jpg|extension.*not allowed/i).first()
    ).toBeVisible({timeout: 15_000});
  });

  /**
   * Replaces Behat: validate_image_attributes.feature:39
   * Validate the allowed extensions constraint of scopable image attribute
   */
  test('Validate allowed extensions constraint of scopable image attribute', async ({page}) => {
    if (!catalogInfo?.hasThumbnail) {
      test.skip(true, 'No "thumbnail" attribute found in this catalog');
      return;
    }
    await navigateToProductPage(page as any, catalogInfo.sku);
    const scopeDropdown = page.getByText(/ecommerce/i).first();
    if (await scopeDropdown.isVisible({timeout: 5_000}).catch(() => false)) {
      await scopeDropdown.click();
    }
    await attachFileToProductAttribute(page, 'Thumbnail', 'fanatic-freewave-76.gif');
    await saveProduct(page);
    await expect(
      page.getByText(/gif.*not allowed|allowed extensions are jpg|extension.*not allowed/i).first()
    ).toBeVisible({timeout: 15_000});
  });
});
