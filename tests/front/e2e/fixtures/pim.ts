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
  await page.getByText('Products').click();

  await page.waitForResponse(resp => resp.url().includes('/datagrid_view/rest/product-grid/default'));
  await page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'));

  // Switch to ungrouped (products only) view
  await page.locator('.search-zone [data-type="grouped-variant"]').click();
  await page.locator('.search-zone [data-value="product"]').click();

  await expect(page.locator('.AknLoadingMask')).toBeVisible();
  await page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'));
  await expect(page.locator('.AknLoadingMask')).toBeHidden();
  await expect(page.locator('.AknTitleContainer-title div')).not.toContainText('product models');
}

export async function selectFirstProduct(page: Page) {
  await page.locator('tr').nth(1).click();
  await page.waitForResponse(resp => /\/enrich\/product\/rest\//.test(resp.url()));
  await page.waitForResponse(resp => /\/configuration\/rest\//.test(resp.url()));
}

export async function saveProduct(page: Page) {
  const savePromise = page.waitForResponse(resp =>
    /\/enrich\/product\/rest\//.test(resp.url()) && resp.request().method() === 'POST'
  );
  await page.getByText('Save').click();
  await savePromise;
}

export async function reloadProduct(page: Page) {
  await page.reload();
  await page.waitForResponse(resp => /\/enrich\/product\/rest\//.test(resp.url()));
  await page.waitForResponse(resp => /\/configuration\/rest\//.test(resp.url()));
}

export function firstTextField(page: Page) {
  return page.locator('.edit-form .akeneo-text-field input:not([disabled]).AknTextField').first();
}
