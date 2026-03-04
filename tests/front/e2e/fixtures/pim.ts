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

export async function waitForLoadingMasks(page: Page) {
  await expect(page.locator('.hash-loading-mask .loading-mask')).toBeHidden({timeout: 30_000});
  await expect(page.locator('.AknDefault-progressContainer')).toBeHidden({timeout: 30_000});
}

export async function goToFamilyPage(page: Page, familyCode?: string) {
  // Navigate through the UI like a real user: Settings → Families card → click Edit
  await page.getByRole('menuitem', {name: 'Activity'}).first().waitFor();
  await page.getByRole('menuitem', {name: 'Settings'}).click();

  // The Settings page shows a card-based menu. "Families" is a clickable card, not a menuitem.
  // The grid loads via /datagrid/family-grid, not /configuration/rest/family.
  const gridDataPromise = page.waitForResponse(
    resp => resp.url().includes('/datagrid/family-grid') && resp.status() === 200
  );
  await page.getByText('Families').first().click();
  await gridDataPromise;

  // Wait for grid rows to render — the grid uses standard table rows
  const firstRow = page
    .getByRole('row')
    .filter({has: page.getByRole('cell')})
    .first();
  await firstRow.waitFor({timeout: 30_000});

  // Click a family row to navigate to its edit page.
  // Grid rows have an "Edit" link whose href = #/configuration/family/{code}/edit.
  // Clicking the row label cell or the Edit link triggers Backbone hash navigation.
  if (familyCode) {
    await page
      .getByRole('row')
      .filter({hasText: new RegExp(familyCode, 'i')})
      .first()
      .getByRole('link', {name: 'Edit'})
      .click();
  } else {
    await firstRow.getByRole('link', {name: 'Edit'}).click();
  }

  await waitForLoadingMasks(page);

  // Wait for the family form to fully render (horizontal tabs: Properties, Attributes, etc.)
  await page.locator('.AknHorizontalNavtab-item').first().waitFor({timeout: 30_000});
}

export async function createProductViaApi(page: Page, sku: string, family: string) {
  // Use the internal REST endpoint (session-authenticated) to create a product
  const response = await page.request.post('/enrich/product/rest/', {
    data: {identifier: sku, family},
    headers: {'Content-Type': 'application/json'},
  });
  return response;
}

export async function deleteProductViaApi(page: Page, productId: string) {
  await page.request.delete(`/enrich/product/rest/${productId}`);
}

export async function goToProductBySearch(page: Page, sku: string) {
  await goToProductsGrid(page);

  // Type the SKU into the search field to filter
  const searchInput = page.locator('.search-zone input[type="search"], .AknFilterBox-search input');
  if (await searchInput.isVisible({timeout: 5_000}).catch(() => false)) {
    await searchInput.fill(sku);
    await searchInput.press('Enter');
    await page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'));
    await page.locator('tr.AknGrid-bodyRow:has(td)').first().waitFor({timeout: 30_000});
  }

  // Click on the product row
  const productPromise = page.waitForResponse(
    resp => /\/enrich\/product(-model)?\/rest\//.test(resp.url()) && resp.status() === 200
  );
  await page.locator('tr.AknGrid-bodyRow:has(td)').first().click();
  await productPromise;
}
