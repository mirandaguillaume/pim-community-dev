import {Page, expect} from '@playwright/test';
import * as path from 'node:path';

const BEHAT_FIXTURES = path.resolve(__dirname, '../../../legacy/features/Context/fixtures');

export function fixtureFilePath(name: string): string {
  return path.join(BEHAT_FIXTURES, name);
}

export async function selectProductsBySku(page: Page, skus: string[]) {
  for (const sku of skus) {
    const row = page.locator('tr.AknGrid-bodyRow').filter({hasText: sku}).first();
    await row.waitFor({state: 'visible', timeout: 15_000});
    await row.locator('input[type="checkbox"]').check();
  }
}

export async function openBulkEditAttributeValues(page: Page) {
  // The "Bulk actions" launcher is an <a> element (tagName: 'a' in action-launcher.js),
  // not a <button> — scope to .mass-actions-panel to avoid false positives.
  await page
    .locator('.mass-actions-panel a', {hasText: /bulk actions/i})
    .first()
    .click();
  await waitForLoadingMasks(page);
  await page
    .getByText(/edit attribute values/i)
    .first()
    .click();
  // The "Next" button on the choose step is a <span class="wizard-action" data-action-target="configure">
  await page.locator('.wizard-action[data-action-target="configure"]').click();
  await waitForLoadingMasks(page);
}

export async function addAttributeToMassEdit(page: Page, attributeLabel: string) {
  // The attribute selector is a Select2 v3 multi-select. The `classes` config option puts
  // class pim-add-attributes-multiselect on the container div (not the dropdown).
  // For multi-select, the search input lives inside .select2-choices (part of the container),
  // NOT inside .select2-drop — using it directly avoids the brittle getByText(placeholder) approach.
  const container = page.locator('.pim-add-attributes-multiselect');
  await container.waitFor({state: 'visible', timeout: 15_000});

  const choicesInput = container.locator('input.select2-input');
  await choicesInput.click();
  await choicesInput.fill(attributeLabel);

  await page.locator('.select2-drop').getByText(attributeLabel, {exact: true}).first().waitFor({timeout: 10_000});
  await page.locator('.select2-drop').getByText(attributeLabel, {exact: true}).first().click();
  // The Select2 footer has a real <button> (buttonTitle: pim_common.add = "Add") to confirm.
  await page.locator('.ui-multiselect-footer button').click();
  await waitForLoadingMasks(page);
}

export async function attachFileToMassEditAttribute(page: Page, attributeLabel: string, fileName: string) {
  const container = page
    .locator('.AknComparableFields')
    .filter({has: page.locator('.AknFieldContainer-label', {hasText: attributeLabel})})
    .first();
  await container.locator('input[type="file"]').setInputFiles(fixtureFilePath(fileName));
}

export async function attachFileToProductAttribute(page: Page, attributeLabel: string, fileName: string) {
  const container = page
    .locator('.AknFieldContainer')
    .filter({has: page.locator('.AknFieldContainer-label', {hasText: attributeLabel})})
    .first();
  await container.locator('input[type="file"]').setInputFiles(fixtureFilePath(fileName));
}

export async function confirmMassEdit(page: Page): Promise<string | null> {
  // Advance from configure step to confirm step (span with data-action-target="confirm").
  // This triggers server-side validation before transitioning.
  await page.locator('.wizard-action[data-action-target="confirm"]').click();
  await waitForLoadingMasks(page);

  // Set up response capture before clicking the final validate button.
  const respPromise = page
    .waitForResponse(r => /rest\/mass.edit|mass-edit|batch-action/.test(r.url()) && r.request().method() === 'POST', {
      timeout: 15_000,
    })
    .catch(() => null);

  // Fire the job — the Confirm div on the confirm step has data-action-target="validate".
  await page.locator('.wizard-action[data-action-target="validate"]').click();
  const resp = await respPromise;
  if (!resp) return null;
  try {
    const body = await resp.json();
    const match = JSON.stringify(body).match(/show\/(\d+)/);
    return match?.[1] ?? null;
  } catch {
    return null;
  }
}

export async function productHasAttributeValue(
  page: Page,
  productUuid: string,
  attributeCode: string
): Promise<boolean> {
  const resp = await page.request.get(`/enrich/product/rest/${productUuid}`, {
    headers: {'X-Requested-With': 'XMLHttpRequest'},
  });
  if (!resp.ok()) return false;
  const product = await resp.json();
  const values = product.values?.[attributeCode];
  return Array.isArray(values) && values.length > 0 && values[0]?.data != null;
}

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

  // Start listening BEFORE clicking to avoid race conditions.
  // Use a short timeout + catch: if already on the product grid (e.g. after a mass edit redirect),
  // clicking "Products" may not trigger a new datagrid request. The grid rows waitFor below is
  // the authoritative signal that the grid is ready.
  const gridDataPromise = page
    .waitForResponse(resp => resp.url().includes('/datagrid/product-grid') && !resp.url().includes('/datagrid_view/'), {
      timeout: 10_000,
    })
    .catch(() => null);
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

export async function createProductViaApi(page: Page, sku: string, family?: string) {
  // Use the internal REST endpoint (session-authenticated) to create a product
  const data: Record<string, string> = {identifier: sku};
  if (family) data.family = family;
  const response = await page.request.post('/enrich/product/rest', {
    data,
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
  return response;
}

export async function deleteProductViaApi(page: Page, productId: string) {
  await page.request.delete(`/enrich/product/rest/${productId}`);
}

export async function createAttributeViaApi(
  page: Page,
  data: {
    code: string;
    type: string;
    group: string;
    scopable?: boolean;
    localizable?: boolean;
    allowed_extensions?: string[];
    max_file_size?: string;
    labels?: Record<string, string>;
  }
) {
  return page.request.put('/rest/attribute/', {
    data: {scopable: false, localizable: false, labels: {}, ...data},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

function prepareFamilyForPut(family: Record<string, unknown>): Record<string, unknown> {
  // Mirror what the Akeneo family form does before PUT:
  // 1. attributes is an array of objects {code, ...} — send only codes
  // 2. delete the meta field (read-only server data)
  const rawAttrs = (family.attributes ?? []) as Array<string | {code: string}>;
  const attrCodes = rawAttrs.map(a => (typeof a === 'string' ? a : a.code));
  const {meta: _meta, ...rest} = family;
  return {...rest, attributes: attrCodes};
}

export async function addAttributeToFamilyViaApi(page: Page, familyCode: string, attributeCode: string): Promise<void> {
  const getResp = await page.request.get(`/configuration/rest/family/${familyCode}`, {
    headers: XHR_HEADER,
  });
  if (!getResp.ok()) throw new Error(`Could not fetch family ${familyCode}: ${getResp.status()}`);
  const family = prepareFamilyForPut(await getResp.json());
  const attrs = family.attributes as string[];
  if (attrs.includes(attributeCode)) return;
  const putResp = await page.request.put(`/configuration/rest/family/${familyCode}`, {
    data: {...family, attributes: [...attrs, attributeCode]},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
  if (!putResp.ok()) throw new Error(`Could not add attribute to family ${familyCode}: ${putResp.status()}`);
}

export async function removeAttributeFromFamilyViaApi(
  page: Page,
  familyCode: string,
  attributeCode: string
): Promise<void> {
  const getResp = await page.request.get(`/configuration/rest/family/${familyCode}`, {
    headers: XHR_HEADER,
  });
  if (!getResp.ok()) return;
  const family = prepareFamilyForPut(await getResp.json());
  const attrs = family.attributes as string[];
  if (!attrs.includes(attributeCode)) return;
  await page.request.put(`/configuration/rest/family/${familyCode}`, {
    data: {...family, attributes: attrs.filter(a => a !== attributeCode)},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

export async function deleteAttributeViaApi(page: Page, code: string) {
  await page.request.delete(`/rest/attribute/${code}`, {headers: XHR_HEADER});
}

/**
 * Fetch the first N simple products from the product grid (Elasticsearch-backed).
 * Returns rows already indexed — safe to use for grid-based test interactions.
 */
export async function getFirstProductsFromGrid(
  page: Page,
  limit = 2
): Promise<Array<{sku: string; uuid: string; family: string}>> {
  const perPage = Math.max(limit * 5, 20);
  const params = new URLSearchParams([
    ['product-grid[_pager][_page]', '1'],
    ['product-grid[_pager][_per_page]', String(perPage)],
  ]);
  const resp = await page.request.get(`/datagrid/product-grid?${params}`, {headers: XHR_HEADER});
  if (!resp.ok()) return [];
  const body = await resp.json();
  const rows: any[] = Array.isArray(body?.data) ? body.data : [];
  return rows
    .filter(r => r.document_type === 'product' && r.identifier && r.technical_id)
    .slice(0, limit)
    .map(r => ({sku: String(r.identifier), uuid: String(r.technical_id), family: String(r.family ?? '')}));
}

/**
 * Navigate to the export job grid, then open a specific export job's edit page.
 * If no jobCode is provided, opens the first available export job.
 */
export async function goToExportJobEdit(page: Page, jobCode?: string) {
  await page.getByRole('menuitem', {name: 'Activity'}).first().waitFor();

  // Navigate: Imports/Exports → Export profiles
  await page.getByRole('menuitem', {name: /export/i}).click();
  await waitForLoadingMasks(page);

  // Wait for the grid to load (admin grids use standard <table> rows)
  const exportRows = page.getByRole('row').filter({has: page.getByRole('cell')});
  await exportRows.first().waitFor({timeout: 30_000});

  // Click Edit on the target row
  if (jobCode) {
    await exportRows.filter({hasText: jobCode}).first().getByRole('link', {name: /edit/i}).click();
  } else {
    await exportRows.first().getByRole('link', {name: /edit/i}).click();
  }

  await waitForLoadingMasks(page);
  // Wait for the form tabs to render
  await page.locator('.AknHorizontalNavtab-item, .tab-pane, [data-toggle="tab"]').first().waitFor({timeout: 30_000});
}

/**
 * Navigate to the import job show page.
 */
export async function goToImportJobPage(page: Page, jobCode: string) {
  await page.getByRole('menuitem', {name: 'Activity'}).first().waitFor();

  // Navigate: Imports/Exports → Import profiles
  await page.getByRole('menuitem', {name: /import/i}).click();
  await waitForLoadingMasks(page);

  // Wait for the grid to load (admin grids use standard <table> rows)
  const importRows = page.getByRole('row').filter({has: page.getByRole('cell')});
  await importRows.first().waitFor({timeout: 30_000});

  // Click on the target import job row
  await importRows.filter({hasText: jobCode}).first().getByRole('cell').first().click();

  await waitForLoadingMasks(page);
}

/**
 * Wait for a job execution to complete by polling the job status indicator.
 */
export async function waitForJobCompletion(page: Page, timeout = 120_000) {
  await expect(page.locator('[data-testid="job-status"]')).toContainText(/completed|failed/i, {timeout});
}

/**
 * Navigate to the user group grid, then open a group for editing.
 */
export async function goToUserGroupEdit(page: Page, groupName?: string) {
  await page.getByRole('menuitem', {name: 'Activity'}).first().waitFor();
  await page.getByRole('menuitem', {name: /system/i}).click();

  // Click "User Groups" in the system menu
  const gridPromise = page.waitForResponse(
    resp => resp.url().includes('/datagrid/pim-user-group-grid') && resp.status() === 200
  );
  await page.getByText('User Groups').first().click();
  await gridPromise;

  // Wait for grid rows (admin grids use standard <table> rows)
  const groupRows = page.getByRole('row').filter({has: page.getByRole('cell')});
  await groupRows.first().waitFor({timeout: 30_000});

  // Click Update on the target row (user group grid uses "Update" links, not "Edit")
  if (groupName) {
    await groupRows.filter({hasText: groupName}).first().getByRole('link', {name: 'Update'}).click();
  } else {
    await groupRows.first().getByRole('link', {name: 'Update'}).click();
  }

  await waitForLoadingMasks(page);
}

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

/**
 * Launch an import job by uploading a file via the internal REST API.
 * Bypasses the UI upload widget (which is broken in Selenium W3C).
 * Returns the job execution ID.
 */
export async function launchImportViaApi(
  page: Page,
  jobCode: string,
  fileContent: string | Buffer,
  fileName: string
): Promise<string> {
  const buffer = typeof fileContent === 'string' ? Buffer.from(fileContent) : fileContent;
  const mimeType = fileName.endsWith('.csv')
    ? 'text/csv'
    : fileName.endsWith('.xlsx')
      ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      : 'application/octet-stream';

  const response = await page.request.post(`/job-instance/rest/import/${jobCode}/launch`, {
    headers: XHR_HEADER,
    multipart: {
      file: {name: fileName, mimeType, buffer},
    },
  });

  expect(response.ok(), `Launch import ${jobCode} failed: ${response.status()}`).toBeTruthy();
  const body = await response.json();
  const match = body.redirectUrl?.match(/\/job\/show\/(\d+)/);
  expect(match, `No job execution ID in response: ${JSON.stringify(body)}`).toBeTruthy();
  return match![1];
}

/**
 * Poll a job execution via the internal REST API until it finishes.
 * Returns the full job execution data including step summaries.
 */
export async function waitForJobExecutionViaApi(page: Page, jobExecutionId: string, timeout = 120_000): Promise<any> {
  let data: any;
  const start = Date.now();
  while (Date.now() - start < timeout) {
    const resp = await page.request.get(`/job-execution/rest/${jobExecutionId}`, {
      headers: XHR_HEADER,
    });
    data = await resp.json();
    if (!data.isRunning) {
      // Normalize status to uppercase for consistent comparison across Akeneo versions.
      // The REST API returns title-case labels ("Completed", "Failed") but tests
      // use uppercase constants ("COMPLETED", "FAILED").
      if (data.status) data.status = data.status.toUpperCase();
      return data;
    }
    await page.waitForTimeout(2_000);
  }
  throw new Error(`Job execution ${jobExecutionId} still running after ${timeout}ms`);
}

/**
 * Try multiple candidate job codes and return the first one that exists in the catalog.
 * Handles catalog-dependent naming (e.g., footwear catalog prefixes codes with "footwear_").
 */
export async function resolveJobCode(page: Page, type: 'import' | 'export', ...candidates: string[]): Promise<string> {
  for (const code of candidates) {
    const resp = await page.request.get(`/job-instance/rest/${type}/${code}`, {
      headers: XHR_HEADER,
    });
    if (resp.ok()) return code;
  }
  throw new Error(`No ${type} job found among candidates: ${candidates.join(', ')}`);
}

/**
 * Discover the first available family code from the catalog.
 */
export async function getFirstFamilyCode(page: Page): Promise<string | null> {
  const resp = await page.request.get('/configuration/rest/family', {
    headers: XHR_HEADER,
  });
  if (!resp.ok()) return null;
  const families = await resp.json();
  // The endpoint returns an object keyed by family code, not an array
  if (Array.isArray(families) && families.length > 0) return families[0].code;
  if (families && typeof families === 'object') {
    const keys = Object.keys(families);
    if (keys.length > 0) return keys[0];
  }
  return null;
}

/**
 * Ensure at least one product exists in the catalog by creating one via the internal REST API.
 * Returns the SKU of the created product, or null if creation was skipped (product already exists).
 */
export async function ensureProductExists(page: Page): Promise<string | null> {
  const family = await getFirstFamilyCode(page);
  if (!family) return null;

  const sku = `pw-test-${Date.now()}`;
  const resp = await createProductViaApi(page, sku, family);
  if (resp.ok()) return sku;
  return null;
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

  // Click on the row that contains our SKU — no fallback to avoid clicking the wrong product
  const targetRow = page.locator('tr.AknGrid-bodyRow').filter({hasText: sku});
  await targetRow.first().waitFor({state: 'visible', timeout: 15_000});
  const productPromise = page.waitForResponse(
    resp => /\/enrich\/product(-model)?\/rest\//.test(resp.url()) && resp.status() === 200
  );
  await targetRow.first().click();
  await productPromise;
}

/**
 * Navigate to a job execution page using hash routing.
 * Uses JavaScript hash manipulation instead of page.goto() to avoid
 * a full SPA re-bootstrap that is unreliable in CI.
 */
export async function goToJobExecution(page: Page, jobId: string) {
  await page.evaluate(id => {
    window.location.hash = `#/job/show/${id}`;
  }, jobId);

  // Wait for the SPA hash router to process the route change and render content.
  // 30s timeout accommodates slow CI machines.
  await expect(page.getByText(/execution details/i)).toBeVisible({timeout: 30_000});
  await waitForLoadingMasks(page);

  // Wait for the actual job execution content to load (step details are fetched asynchronously).
  // The page shows step names like "Product import" or status labels once loaded.
  await page
    .locator('.AknJobExecution, [data-tab], .job-execution-summary, .AknTitleContainer')
    .first()
    .waitFor({timeout: 30_000})
    .catch(() => {});
  // Also wait for any status text (completed, failed, starting, etc.) in the content area
  await expect(page.getByText(/completed|failed|starting|in progress|product import/i).first()).toBeVisible({
    timeout: 30_000,
  });
}
