import {test, expect} from '../fixtures/coverage-fixture';
import {login, goToProductsGrid, ensureProductExists} from '../fixtures/pim';

// Guards C1 wave 1: the display selector must keep working identically
// after the Backbone → React migration (localStorage contract + reload survival).
//
// Note: product.yml displayTypes keys are 'default' (list view) and 'gallery'.
// table.js applyDisplayType treats selectedType === 'default' as the standard list
// display (no rowView override). localStorage stores the raw key ('default' or 'gallery').
test.describe('Product grid display selector', () => {
  test('switches display type and persists across reload', async ({page}) => {
    await login(page, 'admin', 'admin');
    await ensureProductExists(page);
    await goToProductsGrid(page);

    // The toggle button exists in both the legacy and the React implementation,
    // unlike the bootstrap [data-toggle] hook (legacy-only) — click it, not the host.
    const toggle = page.locator('.AknTitleContainer-displaySelector .AknActionButton');
    await expect(toggle).toBeVisible();

    // open the dropdown and pick the gallery display
    await toggle.click();
    const galleryItem = page.locator('.display-selector-item[data-type="gallery"]');
    await expect(galleryItem).toBeVisible();

    // Selecting a type triggers Routing.reloadPage(), which is a SOFT Backbone
    // navigation (hash redirect with {trigger: true}) — no browser `load` event
    // is ever fired, and the JS context survives. The localStorage write is
    // synchronous, so poll it, then wait for the rebuilt toolbar.
    await galleryItem.click();
    await expect.poll(() => page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('gallery');
    await expect(toggle).toBeVisible();

    // survives a hard reload
    await page.reload();
    await page.waitForLoadState('load');
    expect(await page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('gallery');
    await expect(toggle).toBeVisible();

    // switch back to default (list) view
    await toggle.click();
    const defaultItem = page.locator('.display-selector-item[data-type="default"]');
    await expect(defaultItem).toBeVisible();
    await defaultItem.click();
    await expect.poll(() => page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('default');
  });
});
