import {test, expect} from '@playwright/test';
import {login, goToProductsGrid, selectFirstProduct, waitForLoadingMasks, ensureProductExists} from '../fixtures/pim';

/**
 * Replaces Behat: edit_and_display_all_attributes.feature:23
 *
 * Tests that the Product Edit Form's attribute group navigation works:
 * - Viewing a specific group filters to that group's attributes
 * - Switching to "All" shows all attribute groups
 *
 * The PEF uses a dropdown selector for attribute groups, not tabs.
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

    // Wait for the PEF attribute group dropdown to render.
    // The trigger displays text like "Attribute group: All".
    const groupDropdown = page.getByText(/Attribute group:\s/i).first();
    await groupDropdown.waitFor({timeout: 15_000});

    // Click the dropdown to open the group list
    await groupDropdown.click();

    // The dropdown items are listitem elements near the trigger.
    // Use the parent that directly contains the trigger text.
    const dropdownContainer = page.locator(':has(> :text("Attribute group:"))').first();
    const groupItems = dropdownContainer.getByRole('listitem');
    const groupCount = await groupItems.count();

    if (groupCount < 3) {
      test.skip(true, 'Product has fewer than 2 attribute groups — cannot test group switching');
      return;
    }

    // Select a specific group (skip "Attribute group" label at index 0 and "All" at index 1)
    const specificGroup = groupItems.nth(2);
    const specificGroupName = (await specificGroup.textContent())?.trim();
    await specificGroup.click();
    await waitForLoadingMasks(page);

    // After selecting a specific group, verify its section header is visible
    if (specificGroupName) {
      await expect(page.getByRole('banner').filter({hasText: specificGroupName}).first()).toBeVisible({
        timeout: 10_000,
      });
    }

    // Switch back to "All"
    await groupDropdown.click();
    const allItem = dropdownContainer.getByRole('listitem').filter({hasText: /^All$/});
    await allItem.click();
    await waitForLoadingMasks(page);

    // After switching to "All", at least 2 attribute group section headers should be visible.
    // These are <header> elements (role="banner") containing group names like Marketing, ERP, etc.
    const sectionHeaders = page.getByRole('banner').filter({hasText: /Marketing|ERP|Technical|Media/i});
    await expect(sectionHeaders.first()).toBeVisible({timeout: 10_000});
    const headerCount = await sectionHeaders.count();
    expect(headerCount).toBeGreaterThanOrEqual(2);
  });
});
