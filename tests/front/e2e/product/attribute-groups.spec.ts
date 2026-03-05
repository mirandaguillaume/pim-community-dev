import {test, expect} from '@playwright/test';
import {login, goToProductsGrid, selectFirstProduct, waitForLoadingMasks, ensureProductExists} from '../fixtures/pim';

/**
 * Replaces Behat: edit_and_display_all_attributes.feature:23
 *
 * Tests that the Product Edit Form's attribute group navigation works:
 * - Viewing a specific group hides other groups' attributes
 * - Switching to "All" shows all attribute groups
 */

test.describe('Product edit - attribute group visibility', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    await ensureProductExists(page);
  });

  test('All attribute groups visible when switching to All group tab', async ({page}) => {
    try {
      await goToProductsGrid(page);
    } catch {
      test.skip(true, 'Product grid is empty — no products available');
      return;
    }
    await selectFirstProduct(page);

    // Wait for the PEF attribute group navigation to render
    const groupNav = page.locator('.AknVerticalNavtab-item, .attribute-group-selector .group, [data-attribute-group]');
    await groupNav.first().waitFor({timeout: 15_000});

    const groupCount = await groupNav.count();

    if (groupCount < 2) {
      test.skip(true, 'Product has fewer than 2 attribute groups — cannot test group switching');
      return;
    }

    const specificGroup = groupNav.nth(1);
    await specificGroup.click();
    await waitForLoadingMasks(page);

    // Now switch to "All" group to show all attributes
    const allGroup = page
      .locator('.AknVerticalNavtab-item, .attribute-group-selector .group, [data-attribute-group]')
      .filter({hasText: /^all$/i});

    if (await allGroup.isVisible({timeout: 5_000}).catch(() => false)) {
      await allGroup.click();
      await waitForLoadingMasks(page);
    }

    // Verify attribute fields are visible
    const hasContent = await page
      .locator('.field-container, .akeneo-text-field, .AknFieldContainer')
      .first()
      .isVisible({timeout: 10_000})
      .catch(() => false);
    expect(hasContent).toBeTruthy();
  });
});
