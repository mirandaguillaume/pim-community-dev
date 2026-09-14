import {Locator, Page, expect} from '@playwright/test';
import * as path from 'node:path';

const BEHAT_FIXTURES = path.resolve(__dirname, '../../../legacy/features/Context/fixtures');

export function fixtureFilePath(name: string): string {
  return path.join(BEHAT_FIXTURES, name);
}

// Dismiss the announcements panel overlay without triggering Backbone event handlers.
// The #overlay element (position:fixed; 100%x100%; z-index:999) blocks all clicks when
// AknOverlay--show is present. Clicking #overlay fires 'click #overlay' → onClickToCollapsePanel
// → mediator.trigger('pim-app:panel:close'), which has side effects that reset the grid's
// variant filter back to "Grouped". We remove the class directly via evaluate() to unblock
// viewport clicks without dispatching any DOM events.
async function closeAnnouncementsPanel(page: Page): Promise<void> {
  await page
    .evaluate(() => {
      const overlay = document.getElementById('overlay');
      if (overlay?.classList.contains('AknOverlay--show')) {
        overlay.classList.remove('AknOverlay--show');
      }
    })
    .catch(() => {});
}

export async function selectProductsBySku(page: Page, skus: string[]) {
  await closeAnnouncementsPanel(page);

  // Verify each target row is visible before bulk-selecting.
  for (const sku of skus) {
    await page
      .locator('tr.AknGrid-bodyRow')
      .filter({hasText: sku})
      .first()
      .waitFor({state: 'visible', timeout: 15_000});
  }

  // Select all target products in ONE atomic browser-side evaluate.
  // Splitting into per-sku awaited evaluate calls allows Backbone's backgrid:selected
  // handler to trigger row re-renders between each Playwright await: the re-rendered
  // <input> is born unchecked (race with model update), so later selections run against
  // a stale DOM. One synchronous JS call dispatches all change events before any
  // re-render can interleave — all Backbone model updates are batched together.
  //
  // Use jQuery trigger() when available: Backbone's select-row-cell.js registers its
  // 'change :checkbox' delegate via jQuery's .on(), so jQuery's event normalization
  // is the authoritative path. Native dispatchEvent(new Event) bubbles through the DOM
  // and jQuery *should* catch it via its addEventListener bridge, but jQuery trigger()
  // avoids any version-specific discrepancy in that bridging.
  await page.evaluate(targetSkus => {
    const jq = (window as any).$;
    document.querySelectorAll('tr.AknGrid-bodyRow').forEach(row => {
      const text = row.textContent ?? '';
      if (targetSkus.some(sku => text.includes(sku))) {
        const checkbox = row.querySelector('td.select-row-cell input[type="checkbox"]') as HTMLInputElement;
        if (checkbox && !checkbox.checked) {
          checkbox.checked = true;
          if (jq) {
            jq(checkbox).trigger('change');
          } else {
            checkbox.dispatchEvent(new Event('change', {bubbles: true}));
          }
        }
      }
    });
  }, skus);

  // Verify Backbone's mass-actions counter registered the selections before returning.
  // The mass-actions view el has className 'AknDefault-bottomPanel AknMassActions mass-actions'.
  // updateView() removes AknDefault-bottomPanel--hidden when count > 0. Waiting for the
  // hidden class to disappear is more reliable than checking text content because:
  //  - The .mass-actions-panel child div holds action buttons (not the counter)
  //  - The counter span (.AknMassActions-counter .count) text is i18n-translated
  await expect(
    page.locator('.AknDefault-bottomPanel.AknMassActions'),
    `Backbone mass-actions counter did not reach ${skus.length} within 15s — ` +
      'backgrid:selected event chain may be broken (check jQuery delegation on select-row-cell)'
  ).not.toHaveClass(/AknDefault-bottomPanel--hidden/, {timeout: 15_000});
}

export async function openBulkEditAttributeValues(page: Page) {
  // openMassEditOperation (declared after deleteProductViaApi) holds the wizard-opening steps.
  await openMassEditOperation(page, 'Edit attribute values');
}

export async function addAttributeToMassEdit(page: Page, attributeLabel: string) {
  // The attribute selector is a Select2 v3 widget rendered as <a class="select2-choice">
  // inside the Backbone view element (.add-attribute). Note: the 'classes' config key is
  // Select2 v4 only — Select2 v3 uses containerCssClass, so .pim-add-attributes-multiselect
  // never appears in the DOM. The clickable trigger is always .add-attribute .select2-choice.
  const selectButton = page.locator('.add-attribute .select2-choice');
  await selectButton.waitFor({state: 'visible', timeout: 15_000});
  await selectButton.click();

  // Select2 v3 opens its dropdown with id="select2-drop". Scope all subsequent interactions
  // to this unique dropdown to avoid matching other Select2 instances on the page.
  const dropdown = page.locator('#select2-drop');
  await dropdown.waitFor({state: 'visible', timeout: 10_000});

  // Select2 v3 triggers search queries on keyup, not the 'input' event.
  // fill() + dispatchEvent('keyup') ensures the query fires and results are filtered.
  const searchInput = dropdown.locator('input.select2-input');
  await searchInput.fill(attributeLabel);
  await searchInput.dispatchEvent('keyup');

  await dropdown.getByText(attributeLabel, {exact: true}).first().waitFor({timeout: 10_000});
  await dropdown.getByText(attributeLabel, {exact: true}).first().click();

  // onSelecting calls event.preventDefault() keeping the dropdown open after clicking a result.
  // Scope to #select2-drop (the currently open dropdown, unique ID in Select2 v3) to avoid
  // matching stale .ui-multiselect-footer elements left over from previous wizard interactions.
  await page.locator('#select2-drop .ui-multiselect-footer button').click();
  await waitForLoadingMasks(page);
}

export async function attachFileToMassEditAttribute(page: Page, attributeLabel: string, fileName: string) {
  // product/field/field.js gives every attribute field view the AknComparableFields class.
  const container = page
    .locator('.AknComparableFields')
    .filter({has: page.locator('.AknFieldContainer-label', {hasText: attributeLabel})})
    .first();
  await uploadFileIntoMediaField(page, container, attributeLabel, fileName, 'attachFileToMassEditAttribute');
}

export async function attachFileToProductAttribute(page: Page, attributeLabel: string, fileName: string) {
  const container = page
    .locator('.AknFieldContainer')
    .filter({has: page.locator('.AknFieldContainer-label', {hasText: attributeLabel})})
    .first();
  await uploadFileIntoMediaField(page, container, attributeLabel, fileName, 'attachFileToProductAttribute');
}

// Fetch the highest execution ID of a mass edit job (default: edit_common_attributes) via the process
// tracker (see getLatestJobExecutionId: the tracker sorts by start time, so its first row is not the newest
// execution). Keeps its historical contract: 0 when the process tracker cannot be read, so
// pollForNewMassEditJob retries through a transient error.
async function getLatestMassEditJobId(page: Page, jobCode = 'edit_common_attributes'): Promise<number> {
  return getLatestJobExecutionId(page, jobCode).catch(() => 0);
}

// Poll until a new job execution appears with ID > prevMaxId, then return it.
async function pollForNewMassEditJob(
  page: Page,
  prevMaxId: number,
  timeout = 30_000,
  jobCode = 'edit_common_attributes'
): Promise<string | null> {
  const start = Date.now();
  while (Date.now() - start < timeout) {
    const id = await getLatestMassEditJobId(page, jobCode);
    if (id > prevMaxId) return String(id);
    await page.waitForTimeout(1_000);
  }
  return null;
}

export async function confirmMassEdit(page: Page): Promise<string | null> {
  // Advance from configure step to confirm step.
  await page.locator('.wizard-action[data-action-target="confirm"]').click();
  await waitForLoadingMasks(page);

  // Snapshot the current max job ID before firing. The mass-edit POST returns {}
  // (no job ID in the response body), so we discover the new job by polling
  // the process tracker for an ID that postdates this snapshot.
  const prevMaxId = await getLatestMassEditJobId(page);

  // Listen for the POST before clicking so we don't miss a fast response.
  const postPromise = page
    .waitForResponse(r => /rest\/mass.edit|mass-edit|batch-action/.test(r.url()) && r.request().method() === 'POST', {
      timeout: 15_000,
    })
    .catch(() => null);

  await page.locator('.wizard-action[data-action-target="validate"]').click();
  const resp = await postPromise;

  // Some endpoints (import/export launchers) do include a job ID in the response body.
  if (resp) {
    try {
      const body = await resp.json();
      const match = JSON.stringify(body).match(/show\/(\d+)/);
      if (match?.[1]) return match[1];
    } catch {
      /* ignore */
    }
  }

  // Mass-edit returns {}. Poll process-tracker until the new job execution appears.
  // 180s: under CI load the supervised Messenger consumer (3 workers) can still lag
  // behind the queue right after a worker recycle. The first mass-edit of a suite is
  // the most exposed, so we give registration a generous window before declaring the
  // consumer dead (the historical "not registered within 60s" flaky).
  return pollForNewMassEditJob(page, prevMaxId, 180_000);
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
  if (!Array.isArray(values) || values.length === 0) return false;
  const data = values[0]?.data;
  if (data == null) return false;
  // The internal API StandardToInternalApi\ValueConverter always wraps cleared file/image
  // attribute values as {filePath: null, originalFilename: null} instead of plain null.
  // Treat this object-with-null-filePath as "no value".
  if (typeof data === 'object' && !Array.isArray(data) && 'filePath' in data) {
    return (data as {filePath: string | null}).filePath != null;
  }
  return true;
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

  // Proactively dismiss the announcements overlay that appears after login.
  // #overlay.AknOverlay--show (position:fixed 100%×100% z-index:999) intercepts
  // every click on the page until dismissed — root cause of the most common
  // Playwright flaky pattern in this codebase ("locator.click timeout"). Doing
  // it once in login() covers all tests without per-spec defensive code.
  await closeAnnouncementsPanel(page);
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
  await page.locator('tr.AknGrid-bodyRow:has(td)').first().waitFor({timeout: 120_000});

  // Switch to "Product" only view if the variant selector is rendered AND not already in that view.
  // The datagrid remembers the last variant state across navigations within the same session,
  // so on subsequent calls the dropdown may already show "Ungrouped". Clicking the already-active
  // option fires no HTTP request, causing filterPromise to hang indefinitely.
  const variantDropdown = page.locator('.AknTitleContainer-variantSelector [data-toggle="dropdown"]');
  if (await variantDropdown.isVisible({timeout: 15_000}).catch(() => false)) {
    const currentLabel = (await variantDropdown.textContent().catch(() => '')) ?? '';
    if (!/ungrouped/i.test(currentLabel)) {
      await variantDropdown.click();
      const filterPromise = page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'), {
        timeout: 300_000,
      });
      await page.locator('.display-grouped-item[data-value="product"]').click();
      await filterPromise;
      await page.locator('tr.AknGrid-bodyRow:has(td)').first().waitFor({timeout: 120_000});
    }
  }

  // state-listener.js fires collection.trigger('updateState') on datagrid_filters:rendered,
  // which resets selectedModels to {}. It then shows .filter-box after 20ms. Waiting here
  // guarantees updateState has already fired before the caller selects rows — without this,
  // updateState can race with selectProductsBySku and silently clear all selections.
  await page.locator('.filter-box').waitFor({state: 'visible', timeout: 60_000});
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
  // product/form/save.js POSTs to /enrich/product/rest/{uuid} (product models:
  // /enrich/product-model/rest/{id}); the predicate accepts `rest` followed by `/`, `?` or the end.
  const isSaveRequest = (url: string, method: string) =>
    /\/enrich\/product(-model)?\/rest(\/|\?|$)/.test(url) && method === 'POST';

  // Listen before clicking so neither the request nor its response can be missed. The request
  // listener has no budget of its own; its budget starts after the click (below), so time spent
  // waiting for the button does not count. The response gets a generous bound so a request that is
  // sent but never answered fails legibly instead of consuming the whole test timeout.
  const savePromise = page.waitForResponse(resp => isSaveRequest(resp.url(), resp.request().method()), {
    timeout: 150_000,
  });
  savePromise.catch(() => {});
  const requestSent = page
    .waitForRequest(req => isSaveRequest(req.url(), req.method()), {timeout: 0})
    .then(() => 'sent' as const);
  requestSent.catch(() => {});

  await page.getByText('Save').first().click({timeout: 30_000});

  // The form can REFUSE to save without sending anything: when a field is still busy (e.g. an
  // in-flight media upload) product/form/save.js shows a "... cannot be saved right now ..." toast,
  // synchronously in the click handler (it stays about 8s), and returns. Fail fast with that reason
  // instead of waiting for a response that will never come. No automatic retry: that could hide a
  // field that never becomes ready.
  const refused = page
    .locator('#flash-messages')
    .getByText(/cannot be saved right now/i)
    .first()
    .waitFor({state: 'visible', timeout: 10_000})
    .then(() => 'refused' as const);
  refused.catch(() => {});
  let timer: ReturnType<typeof setTimeout> | undefined;
  const timedOut = new Promise<'timeout'>(resolve => {
    timer = setTimeout(() => resolve('timeout'), 30_000);
  });
  let waitError: unknown;
  let outcome: 'sent' | 'refused' | 'timeout' | 'error';
  try {
    outcome = await Promise.race([requestSent, refused.catch(() => new Promise<never>(() => {})), timedOut]);
  } catch (error) {
    waitError = error;
    outcome = 'error';
  } finally {
    clearTimeout(timer);
  }

  if (outcome !== 'sent') {
    const flash = (
      await page
        .locator('#flash-messages')
        .innerText()
        .catch(() => '')
    ).trim();
    if (outcome === 'refused') {
      throw new Error(`saveProduct: the form refused to save: "${flash}"`);
    }
    if (outcome === 'timeout') {
      throw new Error(
        `saveProduct: clicking Save sent no product save request within 30s${flash ? ` (flash: "${flash}")` : ''}`
      );
    }
    throw new Error(
      `saveProduct: waiting for the save request failed: ${waitError instanceof Error ? waitError.message : String(waitError)}`
    );
  }

  const response = await savePromise;
  // After a SUCCESSFUL save, save.js applies the server data (setData) and triggers post_fetch in a
  // jQuery .then callback, which runs asynchronously after the response, and fields re-render even
  // later. A field cleared before setData runs gets re-filled with the saved value (seen in CI on
  // "Successfully replace an image"). form/common/state.js hides "There are unsaved changes." in that
  // same post_fetch dispatch, synchronously after setData, so its disappearance proves the saved data
  // is applied and later edits will not be overwritten. Failed saves (e.g. validation errors) skip
  // post_fetch and keep the banner, so this only applies to successful ones.
  if (response.ok()) {
    await expect(
      page.getByText('There are unsaved changes.', {exact: true}),
      'saveProduct: the saved product data was never applied to the form'
    ).toBeHidden({timeout: 30_000});
  }
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

export async function createProductViaApi(
  page: Page,
  sku: string,
  family?: string,
  extra?: {parent?: string; values?: Record<string, unknown>}
) {
  // Use the internal REST endpoint (session-authenticated) to create a product
  const data: Record<string, unknown> = {identifier: sku, ...extra};
  if (family) data.family = family;
  const response = await page.request.post('/enrich/product/rest', {
    data,
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
  return response;
}

export async function deleteProductViaApi(page: Page, productId: string) {
  // ProductController::removeAction only deletes for an XHR request: anything else gets a redirect to '/',
  // which APIRequestContext follows, so without the header the call "succeeded" and deleted nothing.
  return page.request.delete(`/enrich/product/rest/${productId}`, {headers: XHR_HEADER});
}

/**
 * Delete a family via the internal REST API (DELETE /configuration/rest/family/{code}, no trailing slash,
 * FamilyController::removeAction). XHR only. Returns the response: 204 on success, 422 while a product
 * (FamilyRemover counts them in SQL) or a family variant still uses the family.
 */
export async function deleteFamilyViaApi(page: Page, code: string) {
  return page.request.delete(`/configuration/rest/family/${code}`, {headers: XHR_HEADER});
}

export type ProductVersion = {
  id: number;
  version: number;
  author: string;
  logged_at: string;
  pending: boolean;
  changeset: Record<string, {old?: unknown; new?: unknown}>;
};

/**
 * Read a product's history as the PEF History tab does (fetcher product-history, route
 * pim_enrich_product_history_rest_get: GET /enrich/product/rest/product/{uuid}/history, VersioningController).
 * Newest version first, pending versions excluded (VersionRepository::getLogEntries). Changeset keys are the
 * flat versioning headers (`<attribute>`, `<attribute>-<locale>`, ...) and values are presented strings.
 */
export async function getProductHistoryViaApi(page: Page, productUuid: string): Promise<ProductVersion[]> {
  const resp = await page.request.get(`/enrich/product/rest/product/${productUuid}/history`, {headers: XHR_HEADER});
  const text = await resp.text().catch(() => '');
  if (!resp.ok()) {
    throw new Error(`Get history of product ${productUuid} failed: ${resp.status()} ${text}`);
  }
  const history = JSON.parse(text);
  if (!Array.isArray(history)) {
    throw new Error(`History of product ${productUuid} is not a list: ${text}`);
  }
  return history;
}

/**
 * Create an option of a select attribute via the internal REST API (POST /configuration/attribute-option/{id},
 * no trailing slash, AttributeOptionController::createAction). `attributeId` is the numeric id returned as
 * `meta.id` by createAttributeViaApi. The form is submitted without clearing missing fields, so `{code}` is
 * enough. Returns the raw response.
 */
export async function createAttributeOptionViaApi(page: Page, attributeId: number, code: string) {
  return page.request.post(`/configuration/attribute-option/${attributeId}`, {
    data: {code},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

export type JobExecutionRow = {
  job_execution_id: number;
  job_name: string;
  status: string;
  started_at: string | null;
  [key: string]: unknown;
};

// Upper bound of one process tracker read, see getJobExecutionRowsViaApi.
const PROCESS_TRACKER_MAX_ROWS = 5_000;

/**
 * List every visible execution of a job instance from the process tracker (POST /rest/process-tracker,
 * GetJobExecutionAction, which reads its filters from the query string and redirects requests without the XHR
 * header). Queued executions are listed too: the WHERE clause only filters is_visible and the requested filters
 * (SearchJobExecution::buildSqlWherePart). Every process tracker helper below reads through this function.
 * Throws with the status and body when the search fails or does not answer JSON.
 *
 * Rows are sorted by start_time DESC, then id ASC (SearchJobExecution::buildSqlOrderByPart). A queued execution
 * (NULL start_time) therefore comes LAST, so the first row is not the newest execution: compare ids. It also
 * jumps to the top once a worker starts it, so reading page after page races: when it starts between two page
 * requests, every row before it shifts one place down, and it is missed (it was not on page 1 yet and is no longer
 * on page 2). Hence ONE request, a single SELECT that sees one consistent snapshot, rather than re-reading pages
 * until two passes agree. `size` has no server-side cap (GetJobExecutionAction casts it as is,
 * SearchJobExecutionHandler only caps `page` at 50). 5000 rows is the reach of the former 50 x 100 pager and far
 * above what these job codes accumulate (tens of executions per CI run). A full page throws instead of silently
 * dropping rows.
 */
export async function getJobExecutionRowsViaApi(page: Page, jobCode: string): Promise<JobExecutionRow[]> {
  const resp = await page.request.post('/rest/process-tracker', {
    params: {'code[]': jobCode, page: '1', size: String(PROCESS_TRACKER_MAX_ROWS)},
    headers: XHR_HEADER,
  });
  const text = await resp.text().catch(() => '');
  if (!resp.ok()) {
    throw new Error(`Process tracker search for ${jobCode} failed: ${resp.status()} ${text}`);
  }
  let body: {rows?: unknown} | null;
  try {
    body = JSON.parse(text);
  } catch {
    throw new Error(`Process tracker search for ${jobCode} did not return JSON: ${resp.status()} ${text}`);
  }
  const rows = (Array.isArray(body?.rows) ? body.rows : []) as JobExecutionRow[];
  if (rows.length >= PROCESS_TRACKER_MAX_ROWS) {
    throw new Error(
      `Process tracker lists ${PROCESS_TRACKER_MAX_ROWS} or more executions of ${jobCode}: newer ones may be missing`
    );
  }
  return rows;
}

/** Highest execution id of a job instance in the process tracker, 0 when it never ran. Throws when it cannot be read. */
export async function getLatestJobExecutionId(page: Page, jobCode: string): Promise<number> {
  const rows = await getJobExecutionRowsViaApi(page, jobCode);
  return rows.reduce((max, row) => Math.max(max, Number(row.job_execution_id) || 0), 0);
}

/**
 * Ids of the visible executions of a job instance, in the process tracker order (see getJobExecutionRowsViaApi:
 * compare ids, never take the first one). Unlike getLatestMassEditJobId, a failed search throws with status and body.
 */
export async function getJobExecutionIdsViaApi(page: Page, jobCode: string): Promise<number[]> {
  return (await getJobExecutionRowsViaApi(page, jobCode)).map(row => Number(row.job_execution_id));
}

/**
 * Poll the process tracker until executions of `jobCode` newer than `prevMaxId` exist, and return their
 * ids in ascending order. QueueJobLauncher::launch inserts the execution row before the launching request
 * answers, so the id shows up quickly even when the job consumer is still busy with other messages.
 */
export async function waitForNewJobExecutionIds(
  page: Page,
  jobCode: string,
  prevMaxId: number,
  timeout = 30_000
): Promise<number[]> {
  const start = Date.now();
  let lastIds: number[] = [];
  while (Date.now() - start < timeout) {
    lastIds = await getJobExecutionIdsViaApi(page, jobCode);
    const newIds = lastIds.filter(id => id > prevMaxId).sort((a, b) => a - b);
    if (newIds.length > 0) return newIds;
    await page.waitForTimeout(1_000);
  }
  throw new Error(
    `No ${jobCode} execution newer than #${prevMaxId} appeared within ${timeout}ms (last ids: ${JSON.stringify(lastIds)})`
  );
}

/**
 * Wait until at least one execution of `jobCode` newer than `prevMaxId` exists and every such execution is
 * finished, then return them. Statuses are the process tracker labels (Akeneo\Platform\Job\Domain\Model\Status);
 * a stale STARTING/IN_PROGRESS execution is already reported as FAILED by SearchJobExecution. Throws with the
 * candidate rows on timeout. Taking `prevMaxId` from getLatestJobExecutionId BEFORE the action that launches the
 * job is what ties the result to that action.
 */
export async function waitForNewJobExecutionsToFinish(
  page: Page,
  jobCode: string,
  prevMaxId: number,
  timeout = 300_000
): Promise<JobExecutionRow[]> {
  const unfinished = ['STARTING', 'IN_PROGRESS', 'STOPPING', 'PAUSING', 'PAUSED'];
  const deadline = Date.now() + timeout;
  let newRows: JobExecutionRow[] = [];
  while (Date.now() < deadline) {
    newRows = (await getJobExecutionRowsViaApi(page, jobCode)).filter(row => Number(row.job_execution_id) > prevMaxId);
    if (newRows.length > 0 && newRows.every(row => !unfinished.includes(row.status))) {
      return newRows;
    }
    await page.waitForTimeout(2_000);
  }
  throw new Error(
    `No finished ${jobCode} execution newer than #${prevMaxId} within ${timeout}ms, candidates: ${JSON.stringify(newRows)}`
  );
}

/**
 * Type a term into the product grid search box and wait for the grid to refresh.
 * The box is the label_or_identifier filter (SearchFilterInput.tsx: `.search-filter input[name="value"]`,
 * type="text", the Behat SearchDecorator contract), which matches `*term*` on identifiers and labels
 * (LabelOrIdentifierFilter). The datagrid restores the last term from its saved state, and re-submitting an
 * unchanged value fires no request, so nothing is sent (or awaited) when the box already holds `term`: to force
 * a new request, search a different term. A caller that retries for Elasticsearch lag must alternate terms
 * between attempts, since the box keeps the term of the previous attempt.
 */
export async function searchProductGrid(page: Page, term: string) {
  const searchInput = page.locator('.search-filter input[name="value"]');
  await expect(searchInput, 'product grid search input not found').toBeVisible({timeout: 15_000});
  if ((await searchInput.inputValue()) !== term) {
    // Listen BEFORE pressing Enter: the Enter keydown submits synchronously (runTimeout -> doSearch).
    const gridRefresh = page.waitForResponse(
      resp => resp.url().includes('/datagrid/product-grid') && !resp.url().includes('/datagrid_view/'),
      {timeout: 30_000}
    );
    // A fill or press failure below would otherwise leave this promise to reject unhandled.
    gridRefresh.catch(() => {});
    await searchInput.fill(term, {timeout: 15_000});
    await searchInput.press('Enter', {timeout: 15_000});
    await gridRefresh;
  }
  await waitForLoadingMasks(page);
}

/**
 * Delete a product model through the internal API (DELETE /enrich/product-model/rest/{id}, numeric id
 * from the create response's meta.id). ProductModelController::removeAction has the same XHR guard as
 * products, and answers 422 when RemoveProductModelCommand validation refuses the delete, so delete the
 * products that reference a model first. Returns the response so callers can report a failed delete.
 */
export async function deleteProductModelViaApi(page: Page, productModelId: number | string) {
  return page.request.delete(`/enrich/product-model/rest/${productModelId}`, {headers: XHR_HEADER});
}

/**
 * Open the "Bulk actions" wizard for the rows already selected in the product grid, pick the operation
 * tile whose text contains `operationLabel` (e.g. 'Edit attribute values', 'Associate products') and
 * move on to its configure step.
 */
export async function openMassEditOperation(page: Page, operationLabel: string) {
  // Remove the overlay backdrop before interacting with the wizard.
  // closeAnnouncementsPanel removes AknOverlay--show via evaluate() without triggering
  // Backbone event handlers — safe to call multiple times.
  await closeAnnouncementsPanel(page);

  // The "Bulk actions" launcher is an <a> element (tagName: 'a' in action-launcher.js),
  // not a <button> — scope to .mass-actions-panel to avoid false positives.
  const bulkLink = page.locator('.mass-actions-panel a', {hasText: /bulk actions/i}).first();
  await bulkLink.waitFor({state: 'visible', timeout: 15_000});
  // Use a JS programmatic click instead of Playwright's locator.click(). The #overlay element
  // (position:fixed; 100%×100%; z-index:999) is always present in the DOM and captures pointer
  // events at those screen coordinates even after AknOverlay--show is removed — Playwright's
  // force:true bypasses actionability checks but still sends a coordinate-based CDP mouse event
  // that the overlay intercepts. element.click() dispatches the event directly to the DOM node,
  // bypassing z-index hit-testing entirely.
  await page.evaluate(() => {
    const panel = document.querySelector('.mass-actions-panel');
    const link = panel && Array.from(panel.querySelectorAll('a')).find(a => /bulk\s*action/i.test(a.textContent || ''));
    if (link) (link as HTMLElement).click();
  });
  await waitForLoadingMasks(page);

  // The choose step renders via ChooseApp.tsx (React + akeneo-design-system <Tile>) — tiles do NOT
  // carry data-code attributes; the legacy choose.html Underscore template is dead code. Scope to
  // .operation (class injected by ChooseApp) to safely exclude toast notifications (which lack it).
  const tile = page.locator('.operation').filter({hasText: operationLabel}).first();
  await tile.waitFor({state: 'visible', timeout: 120_000});
  await tile.click({timeout: 15_000});

  // The "Next" button on the choose step is a <span class="wizard-action" data-action-target="configure">
  const configureBtn = page.locator('.wizard-action[data-action-target="configure"]');
  await configureBtn.waitFor({state: 'visible', timeout: 15_000});
  await configureBtn.click({timeout: 15_000});
  await waitForLoadingMasks(page);
}

/**
 * From the mass edit wizard's confirm step, click the real Confirm button
 * (`.wizard-action[data-action-target="validate"]`, mass-edit/form.html) and return the launched job
 * execution id together with the JSON payload the wizard POSTed.
 *
 * form.js posts getFormData() ({filters, jobInstanceCode, actions, itemsCount}) to
 * pim_enrich_mass_edit_rest_launch = POST /rest/mass_edit/ (compiled route dump). MassEditController
 * answers an empty JSON body, so the id is discovered by polling the process tracker for a `jobCode`
 * execution newer than a snapshot taken before the click (the job must be registered as visible).
 *
 * Unlike confirmMassEdit, this throws with the status, body or payload when the launch request is not
 * sent, fails, or no job execution appears.
 */
export async function launchMassEditJob(page: Page, jobCode: string): Promise<{jobId: string; payload: any}> {
  const validateButton = page.locator('.wizard-action[data-action-target="validate"]');
  await expect(validateButton, 'mass edit: the confirm step Confirm button is not displayed').toBeVisible({
    timeout: 30_000,
  });

  const prevMaxId = await getLatestMassEditJobId(page, jobCode);

  const isLaunch = (method: string, url: string) => method === 'POST' && new URL(url).pathname === '/rest/mass_edit/';
  const requestSent = page.waitForRequest(req => isLaunch(req.method(), req.url()), {timeout: 30_000});
  requestSent.catch(() => {});
  const launched = page.waitForResponse(resp => isLaunch(resp.request().method(), resp.url()), {timeout: 60_000});
  launched.catch(() => {});

  await validateButton.click({timeout: 15_000});

  const request = await requestSent;
  const response = await launched;
  expect(response.ok(), `mass edit launch failed: ${response.status()} ${await response.text().catch(() => '')}`).toBe(
    true
  );
  const payload = request.postDataJSON();

  // 180s: same registration window as confirmMassEdit (Messenger consumer lag under CI load).
  const jobId = await pollForNewMassEditJob(page, prevMaxId, 180_000, jobCode);
  expect(
    jobId,
    `no new ${jobCode} job execution (id > ${prevMaxId}) appeared in the process tracker within 180s; ` +
      `launch payload: ${JSON.stringify(payload)}`
  ).toBeTruthy();

  return {jobId: jobId!, payload};
}

// Export job and exported-file helpers (hoisted from export-products-and-download.spec.ts, shared with
// export-launch.spec.ts). The mutating internal controllers used here (UpdateProductController,
// JobInstanceController create/put/delete/launch) return `new RedirectResponse('/')` when the request is not
// an XHR, and APIRequestContext follows that redirect to a 200, so they send X-Requested-With and assert the
// response BODY shape. Headers are built inside each function: XHR_HEADER is a top-level const declared
// further down this file, so a top-level constant spreading it here would throw at module load (TDZ).

/**
 * A response body as text, for failure messages. Accepts an APIResponse or a page Response.
 */
export async function responseBody(resp: {text(): Promise<string>}): Promise<string> {
  return resp.text().catch(() => '<no body>');
}

/**
 * POST /enrich/product/rest/{uuid} (pim_enrich_product_rest_post, UpdateProductController). The payload is
 * the internal format: `values` is mandatory, media values are {filePath, originalFilename}
 * (InternalApiToStandard\ValueConverter).
 */
export async function updateProductViaApi(page: Page, uuid: string, payload: Record<string, unknown>): Promise<void> {
  const resp = await page.request.post(`/enrich/product/rest/${uuid}`, {
    data: payload,
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
  const body = await resp.json().catch(() => null);
  expect(resp.ok(), `Update product ${uuid} failed: ${resp.status()} ${JSON.stringify(body)}`).toBeTruthy();
  expect(body?.meta?.id, `Update product ${uuid} returned an unexpected body: ${JSON.stringify(body)}`).toBe(uuid);
}

/**
 * POST /job-instance/rest/export (pim_enrich_job_instance_rest_export_create, no trailing slash) creating a
 * job instance of job name csv_product_export (connector "Akeneo CSV Connector", icecat jobs.yml).
 * JobInstanceUpdater maps alias -> job name; the UI creation modal sends the same keys.
 * JobInstanceController::createAction resets the raw parameters to the job defaults, so configure it with
 * configureProductExportJobViaApi afterwards.
 */
export async function createProductExportJobViaApi(page: Page, code: string, label: string): Promise<void> {
  const resp = await page.request.post('/job-instance/rest/export', {
    data: {code, label, alias: 'csv_product_export', connector: 'Akeneo CSV Connector'},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
  const body = await resp.json().catch(() => null);
  expect(resp.ok(), `Create export job ${code} failed: ${resp.status()} ${JSON.stringify(body)}`).toBeTruthy();
  expect(body?.code, `Create export job ${code} returned an unexpected body: ${JSON.stringify(body)}`).toBe(code);
}

/**
 * PUT /job-instance/rest/export/{code} (pim_enrich_job_instance_rest_export_put), then read it back with
 * GET /job-instance/rest/export/{code} and require every sent top-level key to be stored exactly as sent.
 *
 * JobInstanceUpdater "configuration" -> JobParametersFactory::create merges the job defaults with what is sent
 * at the TOP level only (array_merge), and the GET returns the raw parameters unchanged
 * (Standard\JobInstanceNormalizer::normalizeConfiguration). So a sent `filters` replaces the default filters
 * (enabled, completeness >= 100, categories) wholesale, and `toEqual` per sent key is exact, including the
 * order of `filters.data`. Only `filters.structure` is validated; `filters.data` allows extra fields
 * (ConstraintCollectionProvider\ProductCsvExport), so an `updated` / "SINCE LAST JOB" entry is accepted.
 */
export async function configureProductExportJobViaApi(
  page: Page,
  code: string,
  configuration: Record<string, unknown>
): Promise<void> {
  const putResp = await page.request.put(`/job-instance/rest/export/${code}`, {
    data: {configuration},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
  const putBody = await putResp.json().catch(() => null);
  expect(
    putResp.ok(),
    `Configure export job ${code} failed: ${putResp.status()} ${JSON.stringify(putBody)}`
  ).toBeTruthy();
  expect(putBody?.code, `Configure export job ${code} returned an unexpected body: ${JSON.stringify(putBody)}`).toBe(
    code
  );

  const getResp = await page.request.get(`/job-instance/rest/export/${code}`, {headers: XHR_HEADER});
  const job = await getResp.json().catch(() => null);
  expect(getResp.ok(), `Get export job ${code} failed: ${getResp.status()} ${JSON.stringify(job)}`).toBeTruthy();
  const stored: Record<string, unknown> = job?.configuration ?? {};
  for (const [key, value] of Object.entries(configuration)) {
    expect(
      stored[key],
      `Export job ${code}: stored "${key}" differs from what was sent. Stored configuration: ${JSON.stringify(stored)}`
    ).toEqual(value);
  }
}

/**
 * GET /job-execution/rest/{id} (pim_enrich_job_execution_rest_get). The InternalApi JobExecutionController
 * adds meta.archives ({archiver: {label, files}}) and meta.generateZipArchive.
 */
export async function getJobExecutionViaApi(page: Page, jobId: string): Promise<any> {
  const resp = await page.request.get(`/job-execution/rest/${jobId}`, {headers: XHR_HEADER});
  expect(resp.ok(), `Get job execution ${jobId} failed: ${resp.status()} ${await responseBody(resp)}`).toBeTruthy();

  return resp.json();
}

/**
 * GET /job/{id}/download/{archiver}/{key} (pim_enrich_job_tracker_download_file), the URL the
 * "Download generated file" link points to.
 */
export async function downloadArchivedFile(page: Page, jobId: string, archiver: string, key: string): Promise<string> {
  const resp = await page.request.get(`/job/${jobId}/download/${archiver}/${encodeURIComponent(key)}`);
  expect(resp.ok(), `Download ${archiver}/${key} failed: ${resp.status()} ${await responseBody(resp)}`).toBeTruthy();

  return resp.text();
}

/**
 * Quote-aware CSV parser: enclosed fields, doubled enclosures, CRLF or LF line endings. Blank lines dropped.
 */
export function parseCsv(text: string, delimiter = ';', enclosure = '"'): string[][] {
  const rows: string[][] = [];
  let row: string[] = [];
  let field = '';
  let inEnclosure = false;
  const input = text.replace(/^﻿/, '');

  for (let i = 0; i < input.length; i++) {
    const char = input[i];
    if (inEnclosure) {
      if (char === enclosure && input[i + 1] === enclosure) {
        field += enclosure;
        i++;
      } else if (char === enclosure) {
        inEnclosure = false;
      } else {
        field += char;
      }
    } else if (char === enclosure) {
      inEnclosure = true;
    } else if (char === delimiter) {
      row.push(field);
      field = '';
    } else if (char === '\n' || char === '\r') {
      if (char === '\r' && input[i + 1] === '\n') {
        i++;
      }
      row.push(field);
      rows.push(row);
      row = [];
      field = '';
    } else {
      field += char;
    }
  }
  if (field !== '' || row.length > 0) {
    row.push(field);
    rows.push(row);
  }

  return rows.filter(r => !(r.length === 1 && r[0] === ''));
}

/**
 * Open an export job page and click "Export now", like Behat's "I am on the ... export job page" + "I launch
 * the export job". Returns the job execution id.
 * - Page: '#/spread/export/{code}' (Behat Page/Export/Show.php), navigated by hash assignment.
 * - Button: job_instance/csv_product_export_show.yml (pim/job/common/edit/launch, label
 *   pim_import_export.form.job_instance.button.export.title = "Export now") renders
 *   templates/export/common/edit/launch.html `<button class="AknButton AknButton--apply ...">`.
 * - launch.js POSTs /job-instance/rest/export/{code}/launch (pim_enrich_job_instance_rest_export_launch) and
 *   redirects to response.redirectUrl (#/job/show/{id}). The response listener is registered before the click.
 */
export async function launchExportFromJobPage(page: Page, jobCode: string): Promise<string> {
  await page.evaluate(code => {
    window.location.hash = `#/spread/export/${code}`;
  }, jobCode);
  const exportNow = page.getByRole('button', {name: 'Export now', exact: true});
  await expect(exportNow, `"Export now" never rendered for ${jobCode}`).toBeVisible({timeout: 30_000});

  const launchResponsePromise = page.waitForResponse(
    r => r.url().endsWith(`/job-instance/rest/export/${jobCode}/launch`) && r.request().method() === 'POST',
    {timeout: 60_000}
  );
  launchResponsePromise.catch(() => {});
  await exportNow.click({timeout: 30_000});
  const launchResponse = await launchResponsePromise;
  expect(
    launchResponse.ok(),
    `Launch ${jobCode} failed: ${launchResponse.status()} ${await responseBody(launchResponse)}`
  ).toBeTruthy();
  const launchBody = await launchResponse.json().catch(() => null);
  const jobId: string | undefined = launchBody?.redirectUrl?.match(/\/job\/show\/(\d+)/)?.[1];
  expect(jobId, `No job execution id in launch response: ${JSON.stringify(launchBody)}`).toBeTruthy();
  await expect(page).toHaveURL(new RegExp(`#/job/show/${jobId}$`), {timeout: 30_000});

  return jobId!;
}

/**
 * Read the CSV an export job execution wrote, from its archive (the job must have finished). Requires exactly
 * one `.csv` key in meta.archives.output.files (FileWriterArchiver, archiver "output"); media files, when
 * exported, live under files/ and are not listed at the top level. Does not look at meta.generateZipArchive,
 * which is false for a single CSV without media.
 */
export async function readExportedCsv(
  page: Page,
  jobId: string
): Promise<{key: string; text: string; header: string[]; rows: string[][]}> {
  const detail = await getJobExecutionViaApi(page, jobId);
  const outputFiles = detail.meta?.archives?.output?.files ?? {};
  const csvKeys = Object.keys(outputFiles).filter(key => key.endsWith('.csv'));
  expect(csvKeys, `Job ${jobId}: unexpected output archives: ${JSON.stringify(detail.meta)}`).toHaveLength(1);

  const text = await downloadArchivedFile(page, jobId, 'output', csvKeys[0]);
  const [header, ...rows] = parseCsv(text);
  expect(header, `Job ${jobId}: CSV has no header. CSV body:\n${text}`).toBeTruthy();

  return {key: csvKeys[0], text, header, rows};
}

/**
 * DELETE /job-instance/rest/export/{code} (pim_enrich_job_instance_rest_export_delete), best-effort cleanup:
 * warns instead of failing. edit-export.spec.ts picks the first export grid row matching /csv.*product/i, so a
 * leaked disposable csv_product_export job can change what it edits.
 */
export async function deleteExportJobViaApi(page: Page, code: string): Promise<void> {
  const resp = await page.request.delete(`/job-instance/rest/export/${code}`, {headers: XHR_HEADER}).catch(() => null);
  if (resp && !resp.ok()) {
    console.warn(`Cleanup: delete export job ${code} returned ${resp.status()} ${await responseBody(resp)}`);
  }
}

/**
 * Pick `fileName` in the media field rendered inside `container` (product/field/media.html) and return
 * only once its upload has been answered OK and the uploaded file is rendered. Shared by
 * attachFileToProductAttribute (product edit form) and attachFileToMassEditAttribute (mass-edit
 * wizard); `caller` names the public helper in error messages.
 */
async function uploadFileIntoMediaField(
  page: Page,
  container: Locator,
  attributeLabel: string,
  fileName: string,
  caller: string
): Promise<void> {
  // media.html renders two exclusive states: EMPTY (.AknMediaField without .has-file, holding
  // input[type=file]) and FILLED (.AknMediaField.has-file, holding the preview, .filename and
  // .clear-field). Field.render() is asynchronous (jQuery 3 runs its .then chain through
  // setTimeout), so right after a clear the DOM still shows the previous state for a few ms, and a
  // save's re-render can re-fill a field that was just cleared. Every step below is bounded instead of
  // acting once on a possibly stale node: a force click on a .clear-field that was being replaced used
  // to retry for the full 600s test timeout ("Successfully replace an image", seen repeatedly in CI).
  const emptyInput = container.locator('.AknMediaField:not(.has-file) input[type="file"]');
  const filled = container.locator('.AknMediaField.has-file');
  await expect(emptyInput.or(filled).first(), `"${attributeLabel}" media field never rendered`).toBeAttached({
    timeout: 30_000,
  });

  // Selecting a file only STARTS an async upload. media-field.js updateModel() calls setReady(false),
  // POSTs the file (image fields: /image-media, route akeneo_file_storage_upload_image; other file
  // fields: /media/, route pim_enrich_media_rest_post), writes the uploaded file into the value in the
  // ajax .done() and calls setReady(true) only in the ajax .always(). Acting before that loses the file:
  // - Product edit form: while a field is not ready, product/form/save.js refuses to save ("The product
  //   cannot be saved right now. The following fields are not ready: ...") and sends no request, so a
  //   Save clicked right after setInputFiles was silently dropped (proven in CI traces on PR #415; it
  //   had been mistaken for runner load for weeks).
  // - Mass-edit wizard: it has no such guard. Its Next action validates the value as it was before the
  //   upload (still empty, which is valid) and moves on to the Confirm step, so an extension error
  //   never shows (CI run 34846941289), and a job can be launched without the file (CI run 34831928439:
  //   job completed, image value missing). Both flaked, passing on retry.
  const isUpload = (method: string, url: string) => {
    const {pathname} = new URL(url);
    return method === 'POST' && (pathname === '/image-media' || pathname === '/media/');
  };

  // Only the steps that are safe to repeat are retried: clearing the field and picking the file until
  // an upload request actually leaves the page. A file picked on an input that a pending render was
  // replacing reaches no change handler and sends nothing, so that attempt fails fast and is retried.
  // Once a request is sent the file is never picked again: during an upload the field still shows its
  // empty state, and a second pick would open media-field.js's blocking "already in upload" dialog and
  // start a second upload.
  let upload: ReturnType<Page['waitForResponse']> | undefined;
  await expect(async () => {
    if ((await filled.count()) > 0) {
      await filled.locator('.clear-field').first().click({force: true, timeout: 5_000});
    }
    await expect(emptyInput).toBeAttached({timeout: 5_000});

    const requestSent = page.waitForRequest(req => isUpload(req.method(), req.url()), {timeout: 10_000});
    requestSent.catch(() => {});
    const response = page.waitForResponse(resp => isUpload(resp.request().method(), resp.url()), {
      timeout: 90_000,
    });
    response.catch(() => {});
    await emptyInput.setInputFiles(fixtureFilePath(fileName), {timeout: 5_000});
    await requestSent;
    upload = response;
  }).toPass({timeout: 60_000});
  if (!upload) {
    throw new Error(`${caller}: no upload of ${fileName} to "${attributeLabel}" was started`);
  }

  // Not retried: a failed upload fails the helper with the server's answer.
  const uploaded = await upload;
  expect(
    uploaded.ok(),
    `Upload of ${fileName} to "${attributeLabel}" failed: ${uploaded.status()} ${await uploaded.text().catch(() => '')}`
  ).toBeTruthy();
  // The upload's done() callback calls render() and its always() callback then calls setReady(true)
  // in the same task, while render() writes the DOM several macrotasks later: once this file's filled
  // state is in the DOM, the field is ready. The field was empty when the file was picked, so this
  // cannot match a previous value with the same file name.
  await expect(
    container.locator('.filename', {hasText: fileName}),
    `"${attributeLabel}" did not render ${fileName} after its upload`
  ).toBeAttached({timeout: 30_000});
}

/**
 * Read one counter of a normalized step execution summary. StepExecutionNormalizer::normalizeSummary
 * translates every key through `job_execution.summary.<key>` (e.g. 'deleted_attribute_groups' comes back
 * as 'Deleted attribute groups' for an en_US user), so the translated label is tried first, then the raw
 * translation key, then the bare key. Callers should dump the step in their failure message.
 */
export function getStepSummaryValue(step: {summary?: Record<string, unknown>}, key: string, label: string): unknown {
  const summary = step.summary ?? {};
  return summary[label] ?? summary[`job_execution.summary.${key}`] ?? summary[key];
}

export type JobNotification = {
  id: number;
  type: string;
  message: string;
  url: string | null;
  actionType: string | null;
  viewed: boolean;
};

/**
 * Return the current user's notification that links to a job execution, or undefined. GET /notification/list
 * (NotificationController::listAction) returns only the 10 most recent notifications, rendered by
 * list.json.twig with `url` = path(route, routeParams); job notifications use route
 * akeneo_job_process_tracker_details, so their url is `/job/show/<id>` (NotificationFactory::create).
 */
export async function getJobNotificationViaApi(
  page: Page,
  jobExecutionId: number | string
): Promise<JobNotification | undefined> {
  const resp = await page.request.get('/notification/list', {headers: XHR_HEADER});
  if (!resp.ok()) {
    throw new Error(`GET /notification/list failed: ${resp.status()} ${await resp.text().catch(() => '')}`);
  }
  const body = await resp.json();
  const notifications: JobNotification[] = Array.isArray(body?.notifications) ? body.notifications : [];
  return notifications.find(notification => notification.url === `/job/show/${jobExecutionId}`);
}

/**
 * Create a family via the internal REST API (POST /configuration/rest/family). The identifier
 * attribute (sku) is added automatically by the backend updater on creation — no need to include
 * it in `attributes`.
 */
export async function createFamilyViaApi(page: Page, code: string, attributes: string[] = []) {
  return page.request.post('/configuration/rest/family/', {
    data: {code, attributes},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

/**
 * Create an association type via the internal REST API (POST /configuration/rest/association-type,
 * AssociationTypeController::createAction() -> AssociationTypeUpdater, which only recognizes
 * code/labels/is_two_way/is_quantified).
 */
export async function createAssociationTypeViaApi(page: Page, code: string) {
  return page.request.post('/configuration/rest/association-type/', {
    data: {code},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

/**
 * Create a family variant via the internal REST API (POST /configuration/rest/family-variant,
 * FamilyVariantController::createAction() -> FamilyVariantUpdater). `variant_attribute_sets` is
 * an array of `{level, axes, attributes}` — axes must be attributes of one of
 * FamilyVariant::getAvailableAxesAttributeTypes() (metric, simpleselect, boolean, reference data/
 * entity simpleselect); the number of levels is immutable once created.
 */
export async function createFamilyVariantViaApi(
  page: Page,
  code: string,
  familyCode: string,
  variantAttributeSets: Array<{level: number; axes: string[]; attributes: string[]}>
) {
  return page.request.post('/configuration/rest/family-variant/', {
    data: {code, family: familyCode, variant_attribute_sets: variantAttributeSets},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

/**
 * Fetch a product's data via the internal REST API (categories, values, etc.).
 */
export async function getProductViaApi(page: Page, identifier: string): Promise<any> {
  const response = await page.request.get(`/enrich/product/rest/${identifier}`, {
    headers: XHR_HEADER,
  });
  expect(response.ok(), `Get product ${identifier} failed: ${response.status()}`).toBeTruthy();
  return response.json();
}

/**
 * Return the code of the first root category tree in the catalog (e.g. "master"/"default"
 * for "Master catalog"). Every catalog install ships with at least one.
 */
export async function getFirstRootCategoryCode(page: Page): Promise<string | null> {
  const resp = await page.request.get('/enrich/category/rest', {headers: XHR_HEADER});
  if (!resp.ok()) return null;
  const categories = await resp.json();
  const list = Array.isArray(categories) ? categories : Object.values(categories);
  return (list[0] as any)?.code ?? null;
}

/**
 * Create a category via the internal REST API used by the category management React app
 * (src/Akeneo/Category/front/src/feature/infrastructure/savers/createCategory.ts). Passing
 * a `parent` code creates a sub-category under it; omitting it creates a new root tree.
 */
export async function createCategoryViaApi(page: Page, code: string, parent?: string, labelEnUS?: string) {
  const data: Record<string, unknown> = {code};
  if (parent) data.parent = parent;
  if (labelEnUS) data.labels = {en_US: labelEnUS};

  return page.request.post('/enrich/product-category-tree/create', {
    data,
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

/**
 * Return the code of any existing family variant in the catalog (every catalog install ships
 * with at least one, e.g. via the family-variant fixture that pairs with the default family).
 */
export async function getFirstFamilyVariantCode(page: Page): Promise<string | null> {
  const resp = await page.request.get('/configuration/rest/family-variant', {headers: XHR_HEADER});
  if (!resp.ok()) return null;
  const familyVariants = await resp.json();
  const codes = Object.keys(familyVariants);
  return codes[0] ?? null;
}

/**
 * Create a product model via the internal REST API — same endpoint and minimal payload
 * (code + family_variant) the "Create product model" popin itself POSTs
 * (create.yml excludedProperties: [family] — family is inferred server-side from the
 * family variant). Returns the raw response; the created product model's numeric id is at
 * `(await response.json()).meta.id`.
 *
 * Pass `parent` (a ROOT product model code of the same family variant) to create a sub product
 * model — ProductModelUpdater::updateParent() rejects any non-root parent. Values are validated
 * per variation level (OnlyExpectedAttributesValidator): a root model may only hold the family
 * variant's common attributes, a level-1 sub model only its level-1 attribute set (and it must
 * carry that level's axis values, NotEmptyVariantAxes).
 */
export async function createProductModelViaApi(
  page: Page,
  code: string,
  familyVariantCode: string,
  values?: Record<string, unknown>,
  parent?: string
) {
  return page.request.post('/enrich/product-model/rest/create', {
    data: {code, family_variant: familyVariantCode, ...(values ? {values} : {}), ...(parent ? {parent} : {})},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

/**
 * Launch the "delete_attributes" bulk job via the internal REST API
 * (MassDeleteAttributeController::launchAction(), POST /rest/attribute/mass-delete). This endpoint
 * expects a JSON body already shaped as a job "filters" configuration
 * (DeleteAttributesTasklet reads `filters.search` / `filters.options` via the shared
 * SearchableRepositoryInterface::findBySearch() contract, same `options.identifiers` shape
 * used by getFirstFamilyVariantCode's list endpoint).
 */
export async function launchMassDeleteAttributesViaApi(page: Page, codes: string[]) {
  return page.request.post('/rest/attribute/mass-delete', {
    data: {
      filters: {
        search: null,
        options: {identifiers: codes},
      },
    },
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

/**
 * Create an attribute group via the internal REST API (PUT /rest/attribute-group/,
 * AttributeGroupController::createAction() -> AttributeGroupUpdater, which accepts `labels`).
 * Without labels, the React attribute-groups grid shows the group as `[code]` (getLabel fallback).
 */
export async function createAttributeGroupViaApi(page: Page, code: string, labels?: Record<string, string>) {
  return page.request.put('/rest/attribute-group/', {
    data: labels ? {code, labels} : {code},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
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
    metric_family?: string;
    default_metric_unit?: string;
    decimals_allowed?: boolean;
    negative_allowed?: boolean;
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

/**
 * Delete an attribute (DELETE /rest/attribute/{code}, AttributeController::removeAction). Returns the response:
 * 204 on success, 400 when a deletion guard refuses, 404 when it does not exist. A successful delete blacklists
 * the code and launches clean_removed_attribute_job on kernel.terminate (AttributeRemovalSubscriber).
 */
export async function deleteAttributeViaApi(page: Page, code: string) {
  return page.request.delete(`/rest/attribute/${code}`, {headers: XHR_HEADER});
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
  return (
    rows
      // NB: `family` here is the grid's rendered family LABEL (localized), NOT the code.
      // For any code-keyed API (e.g. /configuration/rest/family/{code}) resolve the real
      // code with getProductFamilyCode(uuid) — the label 404s and is ES-order-dependent.
      .filter(r => r.document_type === 'product' && r.identifier && r.technical_id)
      .slice(0, limit)
      .map(r => ({sku: String(r.identifier), uuid: String(r.technical_id), family: String(r.family ?? '')}))
  );
}

/**
 * Resolve a product's family CODE via the enrich product API (internal format, which
 * keys the family by code). Use this instead of `getFirstProductsFromGrid().family`,
 * which returns the localized LABEL — that 404s against the code-keyed family endpoints,
 * and which family lands "first" in the grid is ES-ordering-dependent (non-deterministic
 * across Playwright shards). Returns null when the product has no family or the fetch fails.
 */
export async function getProductFamilyCode(page: Page, productUuid: string): Promise<string | null> {
  const resp = await page.request.get(`/enrich/product/rest/${productUuid}`, {headers: XHR_HEADER});
  if (!resp.ok()) return null;
  const product = await resp.json();
  return product?.family ? String(product.family) : null;
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

export const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

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
 * Launch an export job via the internal REST API. Same endpoint family and
 * response shape as launchImportViaApi (both actions share
 * JobInstanceController::launchAction()), but exports take no uploaded file,
 * so no multipart body is sent. Returns the job execution ID.
 */
export async function launchExportViaApi(page: Page, jobCode: string): Promise<string> {
  const response = await page.request.post(`/job-instance/rest/export/${jobCode}/launch`, {
    headers: XHR_HEADER,
  });

  expect(response.ok(), `Launch export ${jobCode} failed: ${response.status()}`).toBeTruthy();
  const body = await response.json();
  const match = body.redirectUrl?.match(/\/job\/show\/(\d+)/);
  expect(match, `No job execution ID in response: ${JSON.stringify(body)}`).toBeTruthy();
  return match![1];
}

/**
 * Poll a job execution via the internal REST API until it finishes.
 * Returns the full job execution data including step summaries.
 */
export async function waitForJobExecutionViaApi(page: Page, jobExecutionId: string, timeout = 180_000): Promise<any> {
  let data: any;
  const start = Date.now();
  while (Date.now() - start < timeout) {
    const resp = await page.request.get(`/job-execution/rest/${jobExecutionId}`, {
      headers: XHR_HEADER,
    });
    // JobExecutionController::getAction answers 404/403 for a missing or ungranted execution: fail with the
    // server's answer instead of a JSON parse error or a body without isRunning.
    if (!resp.ok()) {
      throw new Error(
        `GET /job-execution/rest/${jobExecutionId} failed: ${resp.status()} ${await resp.text().catch(() => '')}`
      );
    }
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

  // Type the SKU into the grid search box (see searchProductGrid). The previous selector
  // ('.search-zone input[type="search"], .AknFilterBox-search input') matched nothing, and isVisible()
  // ignores its timeout and never waits, so the search was always silently skipped and the row lookup
  // below only worked when the SKU happened to be on the first grid page.
  await searchProductGrid(page, sku);

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
