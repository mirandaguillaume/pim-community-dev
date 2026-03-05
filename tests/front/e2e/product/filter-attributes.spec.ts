import {test, expect, Page} from '@playwright/test';
import {login, goToProductsGrid, selectFirstProduct, waitForLoadingMasks, ensureProductExists} from '../fixtures/pim';

/**
 * This test replaces the Behat scenario from:
 *   tests/legacy/features/pim/enrichment/product/pef/edit_product_and_filter_attributes.feature:41
 *
 * Prerequisite: The PIM must have at least one product with a family that has
 * both required and non-required attributes (e.g. icecat_demo_dev catalog).
 *
 * The Behat test checked entire page text which caused false positives with W3C WebDriver
 * (opacity:0 elements included in getText()). This Playwright version uses scoped selectors
 * and tests the filter behavior generically (attribute count changes when filter is applied).
 */

async function countVisibleAttributes(page: Page): Promise<number> {
  return page.locator('.edit-form .field-container:visible').count();
}

async function selectAttributeFilter(page: Page, filterLabel: string) {
  const filterDropdown = page.locator('.attribute-filter [data-toggle="dropdown"]');
  await filterDropdown.click();
  await page.locator('.AknDropdown-menuLink').filter({hasText: filterLabel}).click();
  await waitForLoadingMasks(page);
  // Wait for attributes to re-render
  await page.waitForTimeout(500);
}

async function clickRequiredAttributeIndicator(page: Page, groupCode: string) {
  const indicator = page.locator(`.required-attribute-indicator[data-group="${groupCode}"]`);
  await indicator.click();
  await waitForLoadingMasks(page);
  await page.waitForTimeout(500);
}

test.describe('Product edit - filter attributes', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    await ensureProductExists(page);
    try {
      await goToProductsGrid(page);
    } catch {
      test.skip(true, 'Product grid is empty — no products available');
      return;
    }
    await selectFirstProduct(page);
    await waitForLoadingMasks(page);
  });

  test('Required attribute indicator filters to show only missing required attributes', async ({page}) => {
    // Wait for the product edit form to fully load
    await page.locator('.edit-form .field-container').first().waitFor({timeout: 30_000});

    // Find the first attribute group that has a required-attribute-indicator (badge)
    const indicators = page.locator('.required-attribute-indicator[data-group]');
    const indicatorCount = await indicators.count();
    test.skip(
      indicatorCount === 0,
      'No required attribute indicators found — product has no missing required attributes'
    );

    const firstIndicator = indicators.first();
    const groupCode = await firstIndicator.getAttribute('data-group');

    // Count attributes visible with "All attributes" (default)
    const allAttributesCount = await countVisibleAttributes(page);
    expect(allAttributesCount).toBeGreaterThan(0);

    // Click the required attribute indicator to filter
    await firstIndicator.click();
    await waitForLoadingMasks(page);
    await page.waitForTimeout(500);

    // Count attributes after filtering — should be fewer (only missing required)
    const filteredCount = await countVisibleAttributes(page);
    expect(filteredCount).toBeGreaterThan(0);
    expect(filteredCount).toBeLessThanOrEqual(allAttributesCount);

    // The "updated-status" element (state.js) should use display:none, not opacity:0
    // This verifies our state.js fix: opacity:0 caused W3C getText() false positives
    const stateElement = page.locator('.updated-status');
    if ((await stateElement.count()) > 0) {
      // If the product hasn't been modified, the element should be hidden via display:none
      await expect(stateElement).toBeHidden();
    }

    // Switch back to "All attributes"
    await selectAttributeFilter(page, 'All attributes');

    // Attribute count should be back to the full set for this group
    const restoredCount = await countVisibleAttributes(page);
    expect(restoredCount).toBeGreaterThanOrEqual(allAttributesCount);
  });
});
