import type {APIResponse, Page} from '@playwright/test';
import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  goToProductsGrid,
  searchProductGrid,
  selectProductsBySku,
  openBulkEditAttributeValues,
  addAttributeToMassEdit,
  confirmMassEdit,
  waitForJobExecutionViaApi,
  waitForLoadingMasks,
  createAttributeViaApi,
  createAttributeOptionViaApi,
  createFamilyViaApi,
  createProductViaApi,
  getProductViaApi,
  getProductHistoryViaApi,
  getLatestJobExecutionId,
  waitForNewJobExecutionsToFinish,
  deleteProductViaApi,
  deleteFamilyViaApi,
  deleteAttributeViaApi,
  type ProductVersion,
  XHR_HEADER,
} from '../fixtures/pim';

/**
 * Replaces Behat:
 *   - tests/legacy/features/pim/enrichment/product/versioning/display_removed_value_history.feature:8
 *     "Update product history when multiple linked attributes are removed" (PIM-3420)
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/edit-common-attribute/mass_edit_and_update_attribute_history.feature:40
 *     "Display history when editing product attributes" (PIM-1920)
 *
 * The previous version of this spec cited both scenarios but covered neither. It edited whichever product came
 * first in the grid and skipped itself when no editable field or History tab was found, which in CI was every
 * run. Its rationale ("Name  en", the W3C getText double space) is also obsolete:
 * AssertionContext::iShouldSeeHistory now normalises whitespace.
 *
 * Adaptations:
 * - admin instead of Julia. Disposable data is created through the internal API, one call at a time
 *   (concurrent attribute creates in one group race), instead of the footwear catalog:
 *   - a localizable text attribute plays "Name";
 *   - a multiselect with the options cold/snowy plays "Weather conditions";
 *   - disposable families play Boots/Sandals/Sneakers.
 *   Everything is deleted in `finally`.
 * - Products are created with POST /enrich/product/rest (the endpoint behind the create popin) instead of the popin.
 * - PIM-3420: version 2 is ONE POST /enrich/product/rest/{uuid} (UpdateProductController, what the PEF Save sends)
 *   carrying both values, instead of typing in the PEF.
 *   - The body is the product GET response without `meta`, exactly as product/form/save.js deletes it before saving.
 *   - Kept, `meta` has no user-intent factory and is not ignored (Product factories.yml), so
 *     UserIntentFactoryRegistry throws "Cannot create userIntent from meta fieldName" and the save fails.
 * - PIM-3420: the attribute is deleted with DELETE /rest/attribute/{code} (AttributeController::removeAction, the
 *   action behind the attributes grid's delete). The Behat flow used the grid instead: search, row delete action,
 *   then typing the code in the confirmation modal. That UI flow is NOT covered here, so removing the Behat scenario
 *   also removes its only end-to-end run.
 * - PIM-3420: Behat's "the history of the product has been built" (buildPendingVersions) is replaced by waiting for
 *   the clean_removed_attribute_job that the delete launches on kernel.terminate (AttributeRemovalSubscriber).
 *   - First, snapshot the highest execution id before the delete.
 *   - Then require that every newer execution has finished.
 *   - One of them must be COMPLETED with a clean_attribute.clean_product step that read at least one product. Its
 *     count comes from Elasticsearch `attributes_for_this_level`, which holds the raw_values keys until the job
 *     strips them, and only this test's product holds this attribute.
 *   - Finally, the attribute must answer 404.
 * - The "date: now" column check is dropped: logged_at is a string formatted per locale and timezone. Each check
 *   instead asserts the exact version numbers [2, 1] and that version 1 does not already carry the version 2 change.
 * - Every history check is made on the history REST endpoint the tab reads, then in the tab itself.
 * - The tab is always opened after a full page reload, for two reasons:
 *   - the attribute fetcher caches by code for the whole document (base-fetcher.js), so without a reload the deleted
 *     attribute would still be found and labelled (Behat refreshes for the same reason);
 *   - history.js keeps the expanded versions on its prototype.
 * - Weather values are compared as a set of tokens: Behat saw "snowy,cold" for the input "Cold, Snowy".
 *
 * Selectors, traced from source (the history markup is Underscore templates with no ARIA roles, so no getByRole):
 * - History tab: templates/form/column-tabs-navigation.html renders each column tab as a `.column-navigation-link`
 *   div holding its label, "History" (history.js registers pim_common.history). Same pattern as comments.spec.ts.
 * - Versions (templates/product/history.html):
 *   - `.history-block` holds one `tr.entity-version[data-version]` per version, which is what Behat's
 *     getHistoryRows counts (Base/Form.php);
 *   - each version row has a `.version-expander` (history.js toggleVersion);
 *   - each version has a `tr.changeset[data-version]` that carries `hide` until expanded;
 *   - inside it, each change is a row with `td.property`, followed by a row with `.old-values` / `.new-values`.
 * - Property labels (history.js addAttributesLabelToVersions / getAttributeLabel):
 *   - a localizable attribute renders its label plus a flag span whose text is the language (i18n.ts getFlag), so
 *     the whitespace-normalised text is "<label> en";
 *   - a non-localizable one (the multiselect) renders the bare label;
 *   - a changeset key whose attribute no longer exists renders the raw key.
 *   The flag markup has newlines, so rows are found with a string hasText (whitespace-normalised) and checked with
 *   toHaveText, never with an anchored regex (a regex is matched against the raw text).
 * - Mass edit: the pim.ts wizard helpers, as in mass-edit-image.spec.ts.
 *   - The field root carries `data-attribute="<code>"` (field.js) and its input is `.field-input input[type="text"]`
 *     (field.html, text.html).
 *   - text-field.js only updates the model on `change`, hence the Tab after fill.
 */

const JSON_XHR_HEADERS = {'Content-Type': 'application/json', ...XHR_HEADER};
const CLEAN_JOB = 'clean_removed_attribute_job';

type Disposables = {productUuids: string[]; familyCodes: string[]; attributeCodes: string[]};

async function describeResponse(resp: APIResponse): Promise<string> {
  return `${resp.status()} ${await resp.text().catch(() => '<no body>')}`;
}

async function expectOk(resp: APIResponse, action: string): Promise<void> {
  expect(resp.ok(), `${action} failed: ${await describeResponse(resp)}`).toBeTruthy();
}

function tokens(value: unknown): string[] {
  return String(value ?? '')
    .split(',')
    .map(token => token.trim())
    .filter(token => token !== '')
    .sort();
}

function expectVersionsTwoAndOne(history: ProductVersion[], sku: string): void {
  expect(
    history.map(version => version.version),
    `${sku} should have exactly versions [2, 1]: ${JSON.stringify(history)}`
  ).toEqual([2, 1]);
}

/**
 * Best-effort cleanup in dependency order: products, then families (refused while a product uses them), then
 * attributes. A 404 is expected for anything the test already deleted; other refusals are logged.
 */
async function cleanUp(page: Page, disposables: Disposables): Promise<void> {
  const attempt = async (label: string, remove: () => Promise<APIResponse>) => {
    try {
      const resp = await remove();
      if (!resp.ok() && resp.status() !== 404) {
        console.warn(`[cleanup] ${label} refused: ${await describeResponse(resp)}`);
      }
    } catch (e) {
      console.warn(`[cleanup] ${label}: ${(e as Error).message}`);
    }
  };
  for (const uuid of disposables.productUuids) {
    await attempt(`product ${uuid}`, () => deleteProductViaApi(page, uuid));
  }
  for (const code of disposables.familyCodes) {
    await attempt(`family ${code}`, () => deleteFamilyViaApi(page, code));
  }
  for (const code of disposables.attributeCodes) {
    await attempt(`attribute ${code}`, () => deleteAttributeViaApi(page, code));
  }
}

async function createProductOrFail(page: Page, sku: string, familyCode: string): Promise<string> {
  const resp = await createProductViaApi(page, sku, familyCode);
  await expectOk(resp, `Create product ${sku}`);
  const text = await resp.text();
  const uuid = JSON.parse(text)?.meta?.id;
  expect(uuid, `Create product ${sku} returned no meta.id: ${text}`).toBeTruthy();
  return String(uuid);
}

/**
 * Open the product edit form on a freshly loaded document, then its History tab, and wait for the version rows.
 */
async function openHistoryTab(page: Page, productUuid: string, expectedVersionCount: number): Promise<void> {
  const productHash = `#/enrich/product/${productUuid}`;
  if (!page.url().endsWith(productHash)) {
    // Hash-only navigation: same document, so it does not reset the fetcher caches by itself.
    await page.goto(`/${productHash}`);
  }
  const productLoaded = page.waitForResponse(
    resp => resp.request().method() === 'GET' && new URL(resp.url()).pathname === `/enrich/product/rest/${productUuid}`,
    {timeout: 120_000}
  );
  await page.reload();
  const productResp = await productLoaded;
  expect(productResp.ok(), `Load product ${productUuid} failed: ${productResp.status()}`).toBeTruthy();
  await waitForLoadingMasks(page);

  await page.locator('.column-navigation-link').filter({hasText: 'History'}).click({timeout: 60_000});
  const historyBlock = page.locator('.history-block');
  await expect(historyBlock, `History tab of product ${productUuid} never rendered`).toBeVisible({timeout: 30_000});
  await expect(
    historyBlock.locator('tr.entity-version'),
    `History tab of product ${productUuid} should list ${expectedVersionCount} versions`
  ).toHaveCount(expectedVersionCount, {timeout: 30_000});
}

/**
 * In the open History tab, expand `version` (as AssertionContext::iShouldSeeHistory does) and check that it has
 * exactly one change whose property reads `property`, with new values made of `expectedTokens`.
 */
async function expectHistoryChange(
  page: Page,
  version: number,
  property: string,
  expectedTokens: string[]
): Promise<void> {
  const historyBlock = page.locator('.history-block');
  const changeset = historyBlock.locator(`tr.changeset[data-version="${version}"]:not(.hide)`);
  // toggleVersion re-renders asynchronously; only click when the changeset is not shown yet, and give the render
  // enough time that a retry does not collapse a version that is about to appear.
  await expect(async () => {
    if ((await changeset.count()) === 0) {
      await historyBlock
        .locator(`tr.entity-version[data-version="${version}"] .version-expander`)
        .click({timeout: 10_000});
    }
    await expect(changeset).toBeVisible({timeout: 15_000});
  }, `Version ${version} could not be expanded`).toPass({timeout: 60_000});

  const propertyCell = changeset.locator('td.property', {hasText: property});
  try {
    await expect(propertyCell).toHaveCount(1, {timeout: 15_000});
    await expect(propertyCell).toHaveText(property, {timeout: 5_000});
  } catch (error) {
    const found = await changeset
      .locator('td.property')
      .allInnerTexts()
      .catch(() => [] as string[]);
    throw new Error(
      `Version ${version}: expected one change labelled "${property}", found ${JSON.stringify(found)}. ${(error as Error).message}`
    );
  }

  const newValues = propertyCell.locator('xpath=ancestor::tr[1]/following-sibling::tr[1]').locator('.new-values');
  await expect
    .poll(async () => tokens(await newValues.textContent({timeout: 5_000}).catch(() => null)), {
      message: `Version ${version}: new values of "${property}"`,
      timeout: 15_000,
    })
    .toEqual([...expectedTokens].sort());
}

test.describe('Product version history', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  /**
   * display_removed_value_history.feature:8 (PIM-3420)
   */
  test('keeps the history of a product after an attribute of its changeset is deleted', async ({page}) => {
    // Set in the body: TestDetails has no `timeout` key in this Playwright version.
    // Covers setup and two reloaded History tabs, plus up to 300s for clean_removed_attribute_job.
    test.setTimeout(900_000);
    // Codes use underscores only: history.js splits changeset keys on "-" to find the attribute code.
    const ts = Date.now();
    const nameCode = `pw_hist_name_${ts}`;
    const nameLabel = `PW Hist Name ${ts}`;
    const weatherCode = `pw_hist_weather_${ts}`;
    const weatherLabel = `PW Hist Weather ${ts}`;
    const familyCode = `pw_hist_family_${ts}`;
    const sku = `pw_hist_${ts}`;
    const disposables: Disposables = {productUuids: [], familyCodes: [], attributeCodes: []};

    try {
      // Given a family with a localizable text attribute and a multiselect with the options cold and snowy
      disposables.attributeCodes.push(nameCode);
      await expectOk(
        await createAttributeViaApi(page, {
          code: nameCode,
          type: 'pim_catalog_text',
          group: 'other',
          localizable: true,
          labels: {en_US: nameLabel},
        }),
        `Create attribute ${nameCode}`
      );

      disposables.attributeCodes.push(weatherCode);
      const weatherResp = await createAttributeViaApi(page, {
        code: weatherCode,
        type: 'pim_catalog_multiselect',
        group: 'other',
        labels: {en_US: weatherLabel},
      });
      await expectOk(weatherResp, `Create attribute ${weatherCode}`);
      const weatherText = await weatherResp.text();
      // AttributeNormalizer (internal_api) adds meta.id, the id the option route expects.
      const weatherId = JSON.parse(weatherText)?.meta?.id;
      expect(typeof weatherId, `Create attribute ${weatherCode} returned no numeric meta.id: ${weatherText}`).toBe(
        'number'
      );
      for (const option of ['cold', 'snowy']) {
        await expectOk(
          await createAttributeOptionViaApi(page, weatherId, option),
          `Create option ${option} of ${weatherCode}`
        );
      }

      disposables.familyCodes.push(familyCode);
      await expectOk(
        await createFamilyViaApi(page, familyCode, [nameCode, weatherCode]),
        `Create family ${familyCode}`
      );

      // And a product of that family (version 1)
      const productUuid = await createProductOrFail(page, sku, familyCode);
      disposables.productUuids.push(productUuid);

      // When I change the name to "Nice boots" and the weather conditions to cold and snowy, in one save (version 2)
      const {meta: _meta, ...product} = await getProductViaApi(page, productUuid);
      product.values = {
        ...product.values,
        [nameCode]: [{locale: 'en_US', scope: null, data: 'Nice boots'}],
        [weatherCode]: [{locale: null, scope: null, data: ['cold', 'snowy']}],
      };
      const saveResp = await page.request.post(`/enrich/product/rest/${productUuid}`, {
        data: product,
        headers: JSON_XHR_HEADERS,
      });
      await expectOk(saveResp, `Save product ${sku}`);

      // Then there should be 2 versions, version 2 changing "Name en" and "Weather conditions"
      const history = await getProductHistoryViaApi(page, productUuid);
      const historyDump = JSON.stringify(history);
      expectVersionsTwoAndOne(history, sku);
      expect(history[0].changeset[`${nameCode}-en_US`]?.new, historyDump).toBe('Nice boots');
      expect(tokens(history[0].changeset[weatherCode]?.new), historyDump).toEqual(['cold', 'snowy']);
      expect(Object.keys(history[1].changeset), historyDump).not.toContain(`${nameCode}-en_US`);
      expect(Object.keys(history[1].changeset), historyDump).not.toContain(weatherCode);

      await openHistoryTab(page, productUuid, 2);
      await expectHistoryChange(page, 2, weatherLabel, ['cold', 'snowy']);
      await expectHistoryChange(page, 2, `${nameLabel} en`, ['Nice boots']);

      // When I delete the multiselect attribute
      const previousCleanJobId = await getLatestJobExecutionId(page, CLEAN_JOB);
      const deleteResp = await deleteAttributeViaApi(page, weatherCode);
      expect(deleteResp.status(), `Delete attribute ${weatherCode}: ${await describeResponse(deleteResp)}`).toBe(204);

      // And the removed attribute values have been cleaned
      const cleanRows = await waitForNewJobExecutionsToFinish(page, CLEAN_JOB, previousCleanJobId, 300_000);
      const cleanExecutions = [];
      for (const row of cleanRows) {
        const execution = await waitForJobExecutionViaApi(page, String(row.job_execution_id), 60_000);
        cleanExecutions.push({
          id: row.job_execution_id,
          status: execution.status,
          steps: ((execution.stepExecutions ?? []) as Array<{label: string; summary: unknown}>).map(step => ({
            label: step.label,
            summary: step.summary,
          })),
        });
      }
      // StepExecutionNormalizer keys the summary by the translated "job_execution.summary.read" ("read" in en_US).
      const readCount = (summary: unknown) =>
        Number(
          Object.entries((summary ?? {}) as Record<string, unknown>).find(([key]) => /^read$/i.test(key))?.[1] ?? 0
        );
      expect(
        cleanExecutions.some(
          execution =>
            execution.status === 'COMPLETED' &&
            execution.steps.some(step => /clean_product$/.test(step.label) && readCount(step.summary) >= 1)
        ),
        `No ${CLEAN_JOB} execution newer than #${previousCleanJobId} completed a clean_product step that read a ` +
          `product: ${JSON.stringify(cleanExecutions)}`
      ).toBe(true);
      const attributeResp = await page.request.get(`/rest/attribute/${weatherCode}`, {
        params: {apply_filters: 'false'},
        headers: XHR_HEADER,
      });
      expect(
        attributeResp.status(),
        `Attribute ${weatherCode} still readable after delete: ${await describeResponse(attributeResp)}`
      ).toBe(404);

      // Then there should still be 2 versions, and version 2 shows the raw code of the deleted attribute
      const historyAfterDelete = await getProductHistoryViaApi(page, productUuid);
      const historyAfterDeleteDump = JSON.stringify(historyAfterDelete);
      expectVersionsTwoAndOne(historyAfterDelete, sku);
      expect(tokens(historyAfterDelete[0].changeset[weatherCode]?.new), historyAfterDeleteDump).toEqual([
        'cold',
        'snowy',
      ]);
      expect(historyAfterDelete[0].changeset[`${nameCode}-en_US`]?.new, historyAfterDeleteDump).toBe('Nice boots');

      await openHistoryTab(page, productUuid, 2);
      await expectHistoryChange(page, 2, weatherCode, ['cold', 'snowy']);
      await expectHistoryChange(page, 2, `${nameLabel} en`, ['Nice boots']);
    } finally {
      await cleanUp(page, disposables);
    }
  });

  /**
   * mass_edit_and_update_attribute_history.feature:40 (PIM-1920)
   */
  test('adds one version to each mass edited product', async ({page}) => {
    // confirmMassEdit polls up to 180s and the job may take up to 420s, plus setup and three reloaded History tabs.
    test.setTimeout(900_000);
    const ts = Date.now();
    const nameCode = `pw_mhist_name_${ts}`;
    const nameLabel = `PW MHist Name ${ts}`;
    const skuPrefix = `pw_mhist_${ts}`;
    const products = ['boots', 'sandals', 'sneakers'].map(kind => ({
      sku: `${skuPrefix}_${kind}`,
      familyCode: `pw_mhist_${kind}_${ts}`,
      uuid: '',
    }));
    const disposables: Disposables = {productUuids: [], familyCodes: [], attributeCodes: []};

    try {
      // Given a localizable text attribute in three families, and one product in each family.
      // The mass edit only applies an attribute that belongs to the product's family (CheckAttributeEditable).
      disposables.attributeCodes.push(nameCode);
      await expectOk(
        await createAttributeViaApi(page, {
          code: nameCode,
          type: 'pim_catalog_text',
          group: 'other',
          localizable: true,
          labels: {en_US: nameLabel},
        }),
        `Create attribute ${nameCode}`
      );
      for (const product of products) {
        disposables.familyCodes.push(product.familyCode);
        await expectOk(
          await createFamilyViaApi(page, product.familyCode, [nameCode]),
          `Create family ${product.familyCode}`
        );
      }
      for (const product of products) {
        product.uuid = await createProductOrFail(page, product.sku, product.familyCode);
        disposables.productUuids.push(product.uuid);
        const history = await getProductHistoryViaApi(page, product.uuid);
        expect(
          history.map(version => version.version),
          `${product.sku} should start with version 1 only: ${JSON.stringify(history)}`
        ).toEqual([1]);
      }

      // And I select the three products in the grid
      await goToProductsGrid(page);
      const rows = page.locator('tr.AknGrid-bodyRow').filter({hasText: skuPrefix});
      // A product created a moment ago may not be searchable yet. Each retry searches a different term matching all
      // three SKUs, because re-submitting the current term sends no request.
      const searchTerms = [skuPrefix, `mhist_${ts}`];
      let searchCount = 0;
      await expect(async () => {
        await searchProductGrid(page, searchTerms[searchCount++ % searchTerms.length]);
        await expect(rows).toHaveCount(products.length, {timeout: 10_000});
      }, `The grid never listed the ${products.length} products matching ${skuPrefix}`).toPass({timeout: 90_000});
      await selectProductsBySku(
        page,
        products.map(product => product.sku)
      );

      // When I change the Name to "cool boots" with "Edit attribute values"
      await openBulkEditAttributeValues(page);
      await addAttributeToMassEdit(page, nameLabel);
      const nameInput = page
        .locator(`[data-attribute="${nameCode}"] .field-input`)
        .first()
        .locator('input[type="text"]');
      await expect(nameInput, `Mass edit field for ${nameLabel} not found`).toBeVisible({timeout: 30_000});
      await nameInput.fill('cool boots', {timeout: 15_000});
      await nameInput.press('Tab', {timeout: 15_000});

      // And I confirm mass edit, and wait for the edit_common_attributes job to finish
      const jobId = await confirmMassEdit(page);
      expect(jobId, 'The edit_common_attributes execution never appeared in the process tracker').toBeTruthy();
      const execution = await waitForJobExecutionViaApi(page, jobId!, 420_000);
      expect(execution.status, `Mass edit job ${jobId}: ${JSON.stringify(execution)}`).toBe('COMPLETED');

      // Then each product should have exactly 2 versions, version 2 changing "Name en" to "cool boots"
      for (const product of products) {
        const history = await getProductHistoryViaApi(page, product.uuid);
        const historyDump = JSON.stringify(history);
        expectVersionsTwoAndOne(history, product.sku);
        expect(history[0].changeset[`${nameCode}-en_US`]?.new, historyDump).toBe('cool boots');
        expect(Object.keys(history[1].changeset), historyDump).not.toContain(`${nameCode}-en_US`);

        await openHistoryTab(page, product.uuid, 2);
        await expectHistoryChange(page, 2, `${nameLabel} en`, ['cool boots']);
      }
    } finally {
      await cleanUp(page, disposables);
    }
  });
});
