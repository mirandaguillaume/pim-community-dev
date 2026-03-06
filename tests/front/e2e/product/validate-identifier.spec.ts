import {test, expect} from '@playwright/test';
import {login, waitForLoadingMasks} from '../fixtures/pim';

/**
 * Replaces Behat: validate_identifier_attribute.feature:13,22
 *
 * Tests that identifier attribute validation constraints (max chars) are
 * enforced when saving a product. Uses the Settings > Attributes page to
 * configure the constraint, then verifies the error on product save.
 *
 * Requires: "default" catalog with SKU attribute and at least one product.
 */

async function navigateToSkuAttribute(page: import('@playwright/test').Page) {
  await page.getByRole('menuitem', {name: 'Settings'}).click();
  await waitForLoadingMasks(page);

  await page.getByRole('menuitem', {name: 'Attributes'}).click();

  await page.waitForResponse(resp => resp.url().includes('/datagrid/attribute-grid') && resp.status() === 200);

  const gridRows = page.getByRole('row').filter({has: page.getByRole('cell')});
  await gridRows.first().waitFor({timeout: 30_000});

  // Search for SKU — the grid may span multiple pages
  const searchInput = page.getByRole('textbox', {name: /search by code or label/i});
  await searchInput.fill('sku');
  await searchInput.press('Enter');
  await page.waitForResponse(resp => resp.url().includes('/datagrid/attribute-grid') && resp.status() === 200);
  await gridRows.first().waitFor({timeout: 15_000});

  // Click the Edit link on the SKU row
  const skuRow = gridRows.filter({hasText: /sku/i}).first();
  await skuRow.waitFor({state: 'visible', timeout: 10_000});

  const editLink = skuRow.getByRole('link', {name: /edit/i});
  if (await editLink.isVisible({timeout: 3_000}).catch(() => false)) {
    await editLink.click();
  } else {
    await skuRow.click();
  }
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
    await navigateToSkuAttribute(page);

    // Set max characters to 10
    const maxCharsField = page.getByRole('textbox', {name: /max characters/i});
    await expect(maxCharsField).toBeVisible({timeout: 10_000});
    await maxCharsField.clear();
    await maxCharsField.fill('10');

    // Save the attribute
    const savePromise = page.waitForResponse(
      resp => resp.url().includes('/configuration/rest/attribute/') && resp.request().method() === 'POST'
    );
    await page.getByText('Save').first().click();
    await savePromise;
    await waitForLoadingMasks(page);

    await expect(page.getByText(/unsaved changes/i)).toBeHidden({timeout: 10_000});

    // Navigate to a product and open the first one
    await page.getByRole('menuitem', {name: 'Products'}).click();
    await page.waitForResponse(
      resp => resp.url().includes('/datagrid/product-grid') && !resp.url().includes('/datagrid_view/')
    );
    await page.locator('tr.AknGrid-bodyRow:has(td)').first().waitFor({timeout: 30_000});
    await page.locator('tr.AknGrid-bodyRow:has(td)').first().click();
    await page.waitForResponse(resp => /\/enrich\/product(-model)?\/rest\//.test(resp.url()) && resp.status() === 200);

    // Enter a SKU longer than 10 characters
    const skuField = page.getByRole('textbox', {name: /sku/i}).first();
    await expect(skuField).toBeVisible({timeout: 15_000});
    const originalSku = await skuField.inputValue();

    await skuField.clear();
    await skuField.fill('sku-00000000000');

    // Save and assert validation error
    await page.getByText('Save').first().click();
    await expect(page.getByText(/must not contain more than 10 characters|too long/i).first()).toBeVisible({
      timeout: 15_000,
    });

    // Restore original SKU
    await skuField.clear();
    await skuField.fill(originalSku);

    // Reset the max characters constraint to not break other tests
    await navigateToSkuAttribute(page);
    const resetField = page.getByRole('textbox', {name: /max characters/i});
    await expect(resetField).toBeVisible({timeout: 10_000});
    await resetField.clear();
    await page.getByText('Save').first().click();
    await waitForLoadingMasks(page);
  });
});
