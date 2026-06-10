import {test, expect} from '@playwright/test';
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

    // selection triggers a full page reload — arm the load listener BEFORE clicking
    const galleryReload = page.waitForEvent('load');
    await galleryItem.click();
    await galleryReload;
    expect(await page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('gallery');

    // survives an explicit reload
    await page.reload();
    await page.waitForLoadState('load');
    expect(await page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('gallery');
    await expect(toggle).toBeVisible();

    // switch back to default (list) view
    await toggle.click();
    const defaultItem = page.locator('.display-selector-item[data-type="default"]');
    await expect(defaultItem).toBeVisible();
    const defaultReload = page.waitForEvent('load');
    await defaultItem.click();
    await defaultReload;
    expect(await page.evaluate(() => localStorage.getItem('display-selector:product-grid'))).toBe('default');
  });
});
