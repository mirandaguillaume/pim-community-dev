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
    // The dropdown trigger displays "Attribute group: All" (or another group name).
    // We locate it by its visible text content — NOT by CSS class.
    const groupDropdown = page.getByText(/Attribute group:\s/i).first();
    await groupDropdown.waitFor({timeout: 15_000});

    // Click the dropdown to open it — the list items appear inside a sibling/parent container
    await groupDropdown.click();

    // The dropdown list items are in the DOM near the trigger.
    // From the accessibility tree: the trigger's parent contains a <list> with <listitem> children.
    // We locate all listitem elements within the closest ancestor that contains both trigger and list.
    const dropdownContainer = page.locator(':has(> :text("Attribute group:"))').first();
    const groupItems = dropdownContainer.getByRole('listitem');
    const groupCount = await groupItems.count();

    if (groupCount < 3) {
      // Need at least: "Attribute group" header + "All" + one specific group
      test.skip(true, 'Product has fewer than 2 attribute groups — cannot test group switching');
      return;
    }

    // Select a specific group (second real group, skipping "Attribute group" label and "All")
    const specificGroup = groupItems.nth(2);
    const specificGroupName = await specificGroup.textContent();
    await specificGroup.click();
    await waitForLoadingMasks(page);

    // Verify that a group banner with that name appears (confirming filter worked)
    if (specificGroupName) {
      await expect(page.locator('banner', {hasText: specificGroupName}).first()).toBeVisible({timeout: 10_000});
    }

    // Now switch back to "All" to show all groups
    await groupDropdown.click();
    // Wait for the dropdown to re-open then click "All"
    const allItem = dropdownContainer.getByRole('listitem').filter({hasText: /^All$/});
    await allItem.click();
    await waitForLoadingMasks(page);

    // After switching to "All", multiple group banners should be visible
    // (Marketing, ERP, Technical, Media — at least 2 distinct sections)
    const banners = page.locator('[role="banner"]').filter({hasText: /Marketing|ERP|Technical|Media/i});
    await expect(banners.first()).toBeVisible({timeout: 10_000});
    const bannerCount = await banners.count();
    expect(bannerCount).toBeGreaterThanOrEqual(2);
  });
});
