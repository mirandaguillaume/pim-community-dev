import {test, expect} from '@playwright/test';
import {login, waitForLoadingMasks, createProductViaApi, getFirstFamilyCode} from '../fixtures/pim';

/**
 * Replaces Behat: validate_identifier_attribute.feature:13,22
 *
 * Tests that identifier attribute validation constraints (max chars) are
 * enforced when saving a product. Uses the Settings > Attributes page to
 * configure the constraint, then verifies the error on product save.
 */

async function navigateToSkuAttribute(page: import('@playwright/test').Page) {
  // Navigate directly to the SKU attribute edit page via hash routing.
  // This avoids all grid navigation race conditions (menu clicks, search filters,
  // cached grid state) by going straight to the attribute edit form.
  await page.evaluate(() => {
    window.location.hash = '#/configuration/attribute/sku/edit';
  });

  // Wait for the attribute form to load — the Properties tab must be visible
  await expect(page.getByText('Properties').first()).toBeVisible({timeout: 30_000});
  await waitForLoadingMasks(page);
}

/** Create a product via the internal API and return its UUID */
async function createProduct(page: import('@playwright/test').Page): Promise<string> {
  const family = await getFirstFamilyCode(page);
  // Keep SKU short (≤10 chars) to avoid conflicts with max_characters constraints on the sku attribute
  const sku = `pw${Date.now().toString(36).slice(-6)}`;
  const resp = await createProductViaApi(page, sku, family ?? undefined);

  if (!resp.ok()) {
    throw new Error(`Failed to create product via API: ${resp.status()} ${await resp.text()}`);
  }

  const body = await resp.json();
  const uuid = body?.meta?.id ?? body?.uuid ?? body?.identifier;

  if (!uuid) {
    throw new Error(`Product created but no UUID returned: ${JSON.stringify(body).slice(0, 500)}`);
  }

  return uuid;
}

/** Navigate to a product's PEF by UUID using full page navigation */
async function navigateToProduct(page: import('@playwright/test').Page, uuid: string) {
  // The Akeneo PEF route is /enrich/product/{uuid} (not /enrich/product/uuid/{uuid})
  const productResponsePromise = page.waitForResponse(
    r => /\/enrich\/product(-model)?\/rest\//.test(r.url()) && r.status() === 200
  );
  await page.goto(`/#/enrich/product/${uuid}`);
  await productResponsePromise;
  await waitForLoadingMasks(page);
}

test.describe('Identifier attribute validation', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('Identifier attribute page loads and shows properties', async ({page}) => {
    await navigateToSkuAttribute(page);

    await expect(page.getByText('Properties').first()).toBeVisible({timeout: 15_000});
    await expect(page.getByText(/identifier/i).first()).toBeVisible({timeout: 10_000});
    await expect(page.getByText(/this attribute type is/i).first()).toBeVisible({timeout: 10_000});
    await expect(page.getByText(/unique/i).first()).toBeVisible({timeout: 10_000});
  });

  test('Max characters validation shows error on product save', async ({page}) => {
    // First, clear any leftover max_characters constraint from previous failed runs
    await navigateToSkuAttribute(page);
    const preCleanField = page.getByRole('textbox', {name: /max characters/i});
    await expect(preCleanField).toBeVisible({timeout: 10_000});
    const currentVal = await preCleanField.inputValue();
    if (currentVal !== '') {
      await preCleanField.clear();
      await page.getByText('Save').first().click();
      await waitForLoadingMasks(page);
      await expect(page.getByText(/unsaved changes/i)).toBeHidden({timeout: 15_000});
    }

    // Create a product via API (before setting constraint, to avoid SKU length rejection)
    const productUuid = await createProduct(page);

    // Set max characters to 10 on the SKU attribute
    await navigateToSkuAttribute(page);
    const maxCharsField = page.getByRole('textbox', {name: /max characters/i});
    await expect(maxCharsField).toBeVisible({timeout: 10_000});
    await maxCharsField.clear();
    await maxCharsField.fill('10');
    await page.getByText('Save').first().click();
    await waitForLoadingMasks(page);
    await expect(page.getByText(/unsaved changes/i)).toBeHidden({timeout: 15_000});

    // Navigate to the product and enter a SKU longer than 10 characters
    await navigateToProduct(page, productUuid);

    const skuField = page.getByRole('textbox', {name: /sku/i}).first();
    await expect(skuField).toBeVisible({timeout: 15_000});
    await skuField.clear();
    await skuField.fill('sku-00000000000');

    // Save and assert validation error
    await page.getByText('Save').first().click();
    await expect(page.getByText(/must not contain more than 10 characters|too long/i).first()).toBeVisible({
      timeout: 15_000,
    });

    // Reset the max characters constraint to not break other tests
    await navigateToSkuAttribute(page);
    const resetField = page.getByRole('textbox', {name: /max characters/i});
    await expect(resetField).toBeVisible({timeout: 10_000});
    await resetField.clear();
    await page.getByText('Save').first().click();
    await waitForLoadingMasks(page);
  });
});
