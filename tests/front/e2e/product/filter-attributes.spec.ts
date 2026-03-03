import {test, expect, Page} from '@playwright/test';
import {login, goToProductsGrid, waitForLoadingMasks} from '../fixtures/pim';

/**
 * This test replaces the Behat scenario from:
 *   tests/legacy/features/pim/enrichment/product/pef/edit_product_and_filter_attributes.feature:41
 *
 * Prerequisite: The PIM must be loaded with the "footwear" catalog
 * (or any catalog that has the "boots" family with required attributes in multiple groups).
 *
 * The Behat test checked entire page text which caused false positives with W3C WebDriver
 * (opacity:0 elements included in getText()). This Playwright version uses scoped selectors
 * to only check the attribute edit area.
 */

const FAMILY = 'boots';
const SKU = `pw_boots_${Date.now()}`;

async function createProduct(page: Page, sku: string, family: string) {
  // Click the Create button on the products grid
  await page
    .locator('.AknButton[data-toggle="modal"], .AknButton--action')
    .filter({hasText: /create/i})
    .click();

  // Wait for the creation modal
  await page.locator('.modal').waitFor({timeout: 10_000});

  // Fill in SKU
  const skuInput = page.locator('.modal input[name="identifier"], .modal input').first();
  await skuInput.fill(sku);

  // Select family via Select2
  const familyField = page.locator('.modal .select2-container').first();
  await familyField.click();
  const searchInput = page.locator('.select2-drop-active .select2-input');
  await searchInput.fill(family);
  await page
    .locator('.select2-results .select2-result-label')
    .filter({hasText: new RegExp(family, 'i')})
    .first()
    .click();

  // Click Save in the modal
  const savePromise = page.waitForResponse(
    resp => /\/enrich\/product(-model)?\/rest\//.test(resp.url()) && resp.request().method() === 'POST'
  );
  await page.locator('.modal .AknButton--apply, .modal .ok').click();
  await savePromise;

  // Wait to be on the product edit page
  await waitForLoadingMasks(page);
  await page.locator('.edit-form').waitFor({timeout: 30_000});
}

async function visitAttributeGroup(page: Page, groupName: string) {
  const tab = page.locator('.AknVerticalNavtab .tab').filter({hasText: groupName});
  await tab.click();
  await waitForLoadingMasks(page);
}

async function clickRequiredAttributeIndicator(page: Page, groupCode: string) {
  const indicator = page.locator(`.required-attribute-indicator[data-group="${groupCode}"]`);
  await indicator.click();
  // Wait for the attribute filter to apply
  await waitForLoadingMasks(page);
  // Give the filter a moment to re-render attributes
  await page.waitForTimeout(500);
}

async function selectAttributeFilter(page: Page, filterLabel: string) {
  // Open the attribute filter dropdown
  const filterDropdown = page.locator('.attribute-filter [data-toggle="dropdown"]');
  await filterDropdown.click();

  // Click the desired filter option
  await page.locator('.AknDropdown-menuLink').filter({hasText: filterLabel}).click();

  // Wait for re-render
  await waitForLoadingMasks(page);
  await page.waitForTimeout(500);
}

/** Get all visible attribute labels in the edit form area */
function attributeLabelsLocator(page: Page) {
  return page.locator('.edit-form .field-container .AknFieldContainer-label, .edit-form .field-container label');
}

test.describe('Product edit - filter attributes', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    await goToProductsGrid(page);
  });

  test('Filter to show only missing required attributes by group indicator click', async ({page}) => {
    await createProduct(page, SKU, FAMILY);

    // Visit the "Product information" group
    await visitAttributeGroup(page, 'Product information');

    // Click on the required attribute indicator for the "info" group
    // This should filter to show only missing required attributes from this group
    await clickRequiredAttributeIndicator(page, 'info');

    // In the attribute editing area, check which attributes are visible
    const editForm = page.locator('.edit-form');

    // Should see these missing required attributes from the "info" group
    await expect(editForm.getByText('Name', {exact: false})).toBeVisible({timeout: 10_000});
    await expect(editForm.getByText('Weather conditions', {exact: false})).toBeVisible({timeout: 10_000});
    await expect(editForm.getByText('Description', {exact: false})).toBeVisible({timeout: 10_000});

    // Should NOT see these (non-required in info group, or in other groups)
    // Use scoped check within the attribute fields area, not whole page
    const attributeFields = page.locator('.edit-form .field-container');

    // "Manufacturer" is in info group but not required
    await expect(attributeFields.filter({hasText: 'Manufacturer'})).toHaveCount(0, {timeout: 5_000});

    // These belong to other groups (marketing, media, sizes_and_colors)
    await expect(attributeFields.filter({hasText: /^Price$/})).toHaveCount(0, {timeout: 5_000});
    await expect(attributeFields.filter({hasText: /^Rating$/})).toHaveCount(0, {timeout: 5_000});
    await expect(attributeFields.filter({hasText: /^Side view$/})).toHaveCount(0, {timeout: 5_000});

    // "Size" and "Color" are in sizes_and_colors group, should NOT be shown
    // This is the assertion that failed in Behat because "Size" appeared in opacity:0 elements
    await expect(attributeFields.filter({hasText: /^Size$/})).toHaveCount(0, {timeout: 5_000});
    await expect(attributeFields.filter({hasText: /^Color$/})).toHaveCount(0, {timeout: 5_000});

    // Now switch to "All attributes" filter
    await selectAttributeFilter(page, 'All attributes');

    // Should see Manufacturer and SKU (all info group attributes)
    await expect(editForm.getByText('Manufacturer', {exact: false})).toBeVisible({timeout: 10_000});

    // Size and Color still should NOT be visible (they're in a different group)
    await expect(attributeFields.filter({hasText: /^Side view$/})).toHaveCount(0, {timeout: 5_000});
    await expect(attributeFields.filter({hasText: /^Size$/})).toHaveCount(0, {timeout: 5_000});
    await expect(attributeFields.filter({hasText: /^Color$/})).toHaveCount(0, {timeout: 5_000});
  });
});
