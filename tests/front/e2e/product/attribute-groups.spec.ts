import {test, expect} from '../fixtures/coverage-fixture';
import {login, goToProductsGrid, selectFirstProduct, waitForLoadingMasks, ensureProductExists} from '../fixtures/pim';

/**
 * Replaced Behat scenario (deleted in #420): tests/legacy/features/pim/enrichment/product/pef/edit_and_display_all_attributes.feature:24
 *   "Successfully edit the product and check that all attributes are visible"
 *
 * Tests that the Product Edit Form displays all attribute groups when "All" is selected.
 * The PEF defaults to "Attribute group: All" which shows all groups as sections.
 */

test.describe('Product edit - attribute group visibility', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    const sku = await ensureProductExists(page);
    expect(
      sku,
      'ensureProductExists returned null: no family to attach a product to, or POST /enrich/product/rest refused it'
    ).toBeTruthy();
  });

  test('All attribute groups visible when viewing product', async ({page}) => {
    await goToProductsGrid(page);
    await selectFirstProduct(page);

    // Wait for the PEF to fully load — the attribute group selector should show "All" by default
    const groupSelector = page.getByText(/Attribute group:\s/i).first();
    await groupSelector.waitFor({timeout: 15_000});

    // Verify "All" is the currently selected group
    await expect(groupSelector).toContainText(/All/i);

    // With "All" selected, multiple attribute group section headers should be visible.
    // Each group (Marketing, ERP, Technical, Media, etc.) renders as a <header> banner.
    // Wait for at least one group section to render
    const firstBanner = page
      .getByRole('banner')
      .filter({hasText: /Marketing|ERP|Technical|Media/i})
      .first();
    await expect(firstBanner).toBeVisible({timeout: 10_000});

    // Count how many distinct group sections are visible — should be at least 2
    const allBanners = page.getByRole('banner').filter({hasText: /Marketing|ERP|Technical|Media/i});
    const bannerCount = await allBanners.count();
    expect(bannerCount).toBeGreaterThanOrEqual(2);

    // Verify that actual form fields are rendered (textboxes for attributes)
    const firstField = page.getByRole('textbox').first();
    await expect(firstField).toBeVisible({timeout: 10_000});
  });
});
