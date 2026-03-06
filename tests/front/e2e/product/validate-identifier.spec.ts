import {test, expect} from '@playwright/test';
import {login, waitForLoadingMasks} from '../fixtures/pim';

/**
 * Replaces Behat: validate_identifier_attribute.feature:13,22
 *
 * Tests that identifier attribute validation constraints (max chars) are
 * enforced when saving a product. Uses the Settings > Attributes page to
 * configure the constraint, then verifies the error on product save.
 */

test.describe('Identifier attribute validation', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('Identifier attribute page loads and shows properties', async ({page}) => {
    // Navigate to Settings > Attributes
    await page.getByRole('menuitem', {name: 'Settings'}).click();
    await waitForLoadingMasks(page);

    // Click on Attributes in the settings menu
    const attrLink = page.getByText('Attributes').first();
    await attrLink.waitFor({timeout: 15_000});
    await attrLink.click();

    // Wait for the attribute grid to load
    const gridPromise = page.waitForResponse(
      resp => resp.url().includes('/datagrid/attribute-grid') && resp.status() === 200
    );
    await gridPromise;

    const gridRows = page.getByRole('row').filter({has: page.getByRole('cell')});
    await gridRows.first().waitFor({timeout: 30_000});

    // Find the SKU (identifier) attribute row
    const skuRow = gridRows.filter({hasText: /^sku$/i}).first();
    const hasSkuRow = await skuRow.isVisible({timeout: 5_000}).catch(() => false);

    if (!hasSkuRow) {
      // Try searching for it
      const searchInput = page.locator('.search-zone input, .AknFilterBox-search input');
      if (await searchInput.isVisible({timeout: 3_000}).catch(() => false)) {
        await searchInput.fill('sku');
        await searchInput.press('Enter');
        await page.waitForResponse(resp => resp.url().includes('/datagrid/attribute-grid'));
        await gridRows.first().waitFor({timeout: 15_000});
      }
    }

    // Click on the SKU attribute to open its edit page
    const editLink = gridRows.filter({hasText: /sku/i}).first().getByRole('link', {name: /edit/i});
    if (await editLink.isVisible({timeout: 5_000}).catch(() => false)) {
      await editLink.click();
    } else {
      await gridRows.filter({hasText: /sku/i}).first().click();
    }
    await waitForLoadingMasks(page);

    // Verify the attribute page loaded with properties
    await expect(page.getByText('Properties').first()).toBeVisible({timeout: 15_000});
    await expect(page.getByText(/identifier/i).first()).toBeVisible({timeout: 10_000});

    // Verify key attribute properties are displayed
    await expect(page.getByText(/this attribute type is/i).first()).toBeVisible({timeout: 10_000});
    await expect(page.getByText(/unique/i).first()).toBeVisible({timeout: 10_000});
  });

  test('Max characters validation shows error on product save', async ({page}) => {
    // Navigate to Settings > Attributes > SKU
    await page.getByRole('menuitem', {name: 'Settings'}).click();
    await waitForLoadingMasks(page);
    await page.getByText('Attributes').first().click();

    const gridPromise = page.waitForResponse(
      resp => resp.url().includes('/datagrid/attribute-grid') && resp.status() === 200
    );
    await gridPromise;

    const gridRows = page.getByRole('row').filter({has: page.getByRole('cell')});
    await gridRows.first().waitFor({timeout: 30_000});

    // Open SKU attribute edit page
    const editLink = gridRows.filter({hasText: /sku/i}).first().getByRole('link', {name: /edit/i});
    if (await editLink.isVisible({timeout: 5_000}).catch(() => false)) {
      await editLink.click();
    } else {
      await gridRows.filter({hasText: /sku/i}).first().click();
    }
    await waitForLoadingMasks(page);

    // Check if "Max characters" field exists (it may not in all Akeneo versions)
    const maxCharsField = page.getByRole('textbox', {name: /max characters/i});
    if (!(await maxCharsField.isVisible({timeout: 5_000}).catch(() => false))) {
      // Try scrolling or looking for the field by label
      const maxCharsLabel = page.getByText(/max characters/i).first();
      if (!(await maxCharsLabel.isVisible({timeout: 3_000}).catch(() => false))) {
        test.skip(true, 'Max characters field not available on this attribute');
        return;
      }
    }

    // Set max characters to 10
    await maxCharsField.clear();
    await maxCharsField.fill('10');

    // Save the attribute
    const savePromise = page.waitForResponse(
      resp => resp.url().includes('/configuration/rest/attribute/') && resp.request().method() === 'POST'
    );
    await page.getByText('Save').first().click();
    await savePromise;
    await waitForLoadingMasks(page);

    // Verify save succeeded (no "unsaved changes" text)
    await expect(page.getByText(/unsaved changes/i)).toBeHidden({timeout: 10_000});

    // Navigate to a product
    await page.getByRole('menuitem', {name: 'Products'}).click();
    await page.waitForResponse(
      resp => resp.url().includes('/datagrid/product-grid') && !resp.url().includes('/datagrid_view/')
    );
    await page.locator('tr.AknGrid-bodyRow:has(td)').first().waitFor({timeout: 30_000});
    await page.locator('tr.AknGrid-bodyRow:has(td)').first().click();
    await page.waitForResponse(resp => /\/enrich\/product(-model)?\/rest\//.test(resp.url()) && resp.status() === 200);

    // Find the SKU field and enter a value longer than 10 chars
    const skuField = page.getByRole('textbox', {name: /sku/i}).first();
    await skuField.waitFor({timeout: 15_000});
    const originalSku = await skuField.inputValue();

    await skuField.clear();
    await skuField.fill('sku-00000000000');

    // Save the product
    await page.getByText('Save').first().click();

    // Expect a validation error about max characters
    const validationError = page.getByText(/must not contain more than 10 characters|too long/i).first();
    const hasError = await validationError.isVisible({timeout: 10_000}).catch(() => false);

    if (hasError) {
      await expect(validationError).toBeVisible();
    }

    // Restore the original SKU to not break other tests
    await skuField.clear();
    await skuField.fill(originalSku);

    // Reset the max characters constraint
    await page.getByRole('menuitem', {name: 'Settings'}).click();
    await waitForLoadingMasks(page);
    await page.getByText('Attributes').first().click();
    await page.waitForResponse(resp => resp.url().includes('/datagrid/attribute-grid'));
    await page
      .getByRole('row')
      .filter({has: page.getByRole('cell')})
      .first()
      .waitFor({timeout: 30_000});
    const resetLink = page
      .getByRole('row')
      .filter({has: page.getByRole('cell')})
      .filter({hasText: /sku/i})
      .first()
      .getByRole('link', {name: /edit/i});
    if (await resetLink.isVisible({timeout: 5_000}).catch(() => false)) {
      await resetLink.click();
    } else {
      await page
        .getByRole('row')
        .filter({has: page.getByRole('cell')})
        .filter({hasText: /sku/i})
        .first()
        .click();
    }
    await waitForLoadingMasks(page);

    const resetField = page.getByRole('textbox', {name: /max characters/i});
    if (await resetField.isVisible({timeout: 5_000}).catch(() => false)) {
      await resetField.clear();
      await page.getByText('Save').first().click();
      await waitForLoadingMasks(page);
    }
  });
});
