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
  const gridDataPromise = page.waitForResponse(
    resp => resp.url().includes('/datagrid/product-grid') && !resp.url().includes('/datagrid_view/')
  );
  await page.getByRole('menuitem', {name: 'Products'}).click();
  await gridDataPromise;

  // Wait for the grid rows to actually render
  await page.locator('tr.AknGrid-bodyRow:has(td)').first().waitFor({timeout: 30_000});

  // Switch to "Product" only view if the variant selector is rendered
  const variantDropdown = page.locator('.AknTitleContainer-variantSelector [data-toggle="dropdown"]');
  if (await variantDropdown.isVisible({timeout: 5_000}).catch(() => false)) {
    await variantDropdown.click();
    const filterPromise = page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'));
    await page.locator('.display-grouped-item[data-value="product"]').click();
    await filterPromise;
    await page.locator('tr.AknGrid-bodyRow:has(td)').first().waitFor({timeout: 30_000});
  }
}

export async function selectFirstProduct(page: Page) {
  // Listen for the product or product-model REST call before clicking to avoid race conditions
  const productPromise = page.waitForResponse(
    resp => /\/enrich\/product(-model)?\/rest\//.test(resp.url()) && resp.status() === 200
  );
  await page.locator('tr.AknGrid-bodyRow:has(td)').first().click();
  await productPromise;
}

export async function saveProduct(page: Page) {
  const savePromise = page.waitForResponse(
    resp => /\/enrich\/product(-model)?\/rest\//.test(resp.url()) && resp.request().method() === 'POST'
  );
  await page.getByText('Save').first().click();
  await savePromise;
}

export async function reloadProduct(page: Page) {
  const productPromise = page.waitForResponse(
    resp => /\/enrich\/product(-model)?\/rest\//.test(resp.url()) && resp.status() === 200
  );
  await page.reload();
  await productPromise;
}

export function firstTextField(page: Page) {
  return page.locator('.edit-form .akeneo-text-field input:not([disabled]).AknTextField').first();
}
