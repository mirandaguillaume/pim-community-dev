import {Page, expect} from '@playwright/test';

export async function login(page: Page, username: string, password: string) {
  await page.goto('/user/login');
  await page.locator('input[name="_username"]').fill(username);
  await page.locator('input[name="_password"]').fill(password);
  await page.getByRole('button', {name: 'Login'}).click();

  await expect(page).not.toHaveURL(/\/user\/login/, {timeout: 120_000});
  await expect(page.locator('.AknDefault-progressContainer')).toBeHidden({timeout: 120_000});

  // Wait for the route loading mask overlay to be hidden
  await expect(page.locator('.hash-loading-mask .loading-mask')).toBeHidden({timeout: 120_000});
}

export async function goToProductsGrid(page: Page) {
  await page.getByRole('menuitem', {name: 'Activity'}).first().waitFor();

  // Start listening BEFORE clicking to avoid race conditions
  const gridViewPromise = page.waitForResponse(resp => resp.url().includes('/datagrid_view/rest/product-grid/default'));
  const gridDataPromise = page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'));
  await page.getByText('Products').first().click();
  await Promise.all([gridViewPromise, gridDataPromise]);

  // Switch to ungrouped (products only) view if the selector is rendered
  const groupedVariant = page.locator('.search-zone [data-type="grouped-variant"]');
  if (await groupedVariant.isVisible({timeout: 15_000}).catch(() => false)) {
    const filterPromise = page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'));
    await groupedVariant.click();
    await page.locator('.search-zone [data-value="product"]').click();
    await filterPromise;
    await expect(page.locator('.AknLoadingMask')).toBeHidden({timeout: 30_000});
    await expect(page.locator('.AknTitleContainer-title div')).not.toContainText('product models');
  }
}

export async function selectFirstProduct(page: Page) {
  // Listen for both responses before clicking to avoid race conditions
  const productPromise = page.waitForResponse(resp => /\/enrich\/product\/rest\//.test(resp.url()));
  const configPromise = page.waitForResponse(resp => /\/configuration\/rest\//.test(resp.url()));
  await page.locator('tr').nth(1).click();
  await Promise.all([productPromise, configPromise]);
}

export async function saveProduct(page: Page) {
  const savePromise = page.waitForResponse(resp =>
    /\/enrich\/product\/rest\//.test(resp.url()) && resp.request().method() === 'POST'
  );
  await page.getByText('Save').first().click();
  await savePromise;
}

export async function reloadProduct(page: Page) {
  const productPromise = page.waitForResponse(resp => /\/enrich\/product\/rest\//.test(resp.url()));
  const configPromise = page.waitForResponse(resp => /\/configuration\/rest\//.test(resp.url()));
  await page.reload();
  await Promise.all([productPromise, configPromise]);
}

export function firstTextField(page: Page) {
  return page.locator('.edit-form .akeneo-text-field input:not([disabled]).AknTextField').first();
}
