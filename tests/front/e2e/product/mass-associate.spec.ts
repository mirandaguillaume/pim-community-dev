import {test, expect} from '../fixtures/coverage-fixture';
import type {Locator, Page} from '../fixtures/coverage-fixture';
import {
  login,
  goToProductsGrid,
  selectProductsBySku,
  createProductViaApi,
  createProductModelViaApi,
  deleteProductViaApi,
  deleteProductModelViaApi,
  searchProductGrid,
  openMassEditOperation,
  launchMassEditJob,
  waitForJobExecutionViaApi,
} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product/mass-edit/mass_associate.feature:47
 *   "Mass associate products to product models" (lines 47-75 at b43e9472c0^). The scenario was
 *   deleted from master by 07acc2fa64 (#421); read it with
 *   `git log --all -p -- tests/legacy/features/pim/enrichment/product/mass-edit/mass_associate.feature`.
 *   It selected Bag, Belt and Hat, chose "Associate products", added the product models juno and amor
 *   through the item picker, validated the mass edit, waited for add_association and checked that each
 *   product's X_SELL association lists product_models amor,juno. Nothing else runs
 *   AssociationFieldAdder::addAssociatedProductModels end to end.
 *
 * Adaptations:
 * - Catalog icecat_demo_dev (the CI seed) instead of catalog_modeling, logged in as admin instead of
 *   Julia.
 * - Bag, Belt and Hat become 3 disposable family-less products `pw-massassoc-<ts>-1..3`. juno and amor
 *   become 2 disposable ROOT product models `pw_massassoc_<ts>_pm_a|b` on clothing_color_size, with no
 *   values: a root model may only hold common attributes. Everything is created one entity at a time
 *   and deleted in afterAll (products first, since they reference the models).
 * - "I sort by ID" is dropped: the 3 rows are found with a grid search, retried because the internal
 *   create does not refresh the Elasticsearch index.
 * - Single choose -> configure entry. The Behat choose -> previous -> choose re-entry is dropped: when
 *   the wizard goes back to the choose step, form.js render() replaces its DOM with `$el.html()`, which
 *   strips the jQuery handlers delegated on the detached associate view. On re-entry the view shows its
 *   stale "Add associations" button until associate.js's async association-type fetch re-renders it and
 *   calls delegateEvents(). The single entry never detaches the view, so there is no dead window.
 * - X_SELL ("Cross sell", icecat association_types.csv:2) is picked explicitly in the type dropdown
 *   instead of relying on the default (sessionStorage, else the first fetched type). It is not two-way,
 *   so the two-way branch of AssociationFieldAdder stays untested, as it was in Behat.
 * - "I search juno / check juno / search amor / check amor" becomes one picker search for the fragment
 *   both codes share, then one check per row, each confirmed by its basket entry.
 * - Persistence is read back through the internal product API (GET /enrich/product/rest/{uuid}).
 * - Checks Behat did not have: X_SELL exists and is not quantified, the products start with no
 *   product-model association, the confirm step lists both models, the POST /rest/mass_edit/ payload,
 *   and the job finishes without warnings or skipped items.
 *
 * Overlapping Behat coverage that is still active, which narrows down a red run of this spec to the
 * product_models mass-add path (or this spec): update_associations.feature:30 (PR suite) searches,
 * checks and confirms a row in the same picker modal; update_associations.feature:46 (@critical, nightly
 * suite only) does it for a product-model row; mass_associate.feature:13 and :48 run this wizard and the
 * add_association job with product rows.
 *
 * DOM and API contracts:
 * - Tile: ChooseApp.tsx renders `<Tile className="operation">` with the label
 *   associate_to_product_and_product_model.label = "Associate products" (Enrichment
 *   jsmessages.en_US.yml:426); openMassEditOperation clicks it and Next.
 * - Configure step: mass-edit/product/associate/pick.html. `.association-type-selector` holds a
 *   bootstrap `[data-toggle="dropdown"]` (it adds `open` to the `.AknDropdown`, which
 *   bootstrap.dropdown.less shows) and `li[data-association-type]` entries handled by
 *   associate.js::changeAssociationType. The highlight re-renders with the type label.
 *   `div.add-associations` has no button role.
 * - Picker: associate.js::manageProducts opens a Backbone.BootstrapModal (`div.modal`) titled
 *   "Add {{ associationType }} associations" (jsmessages.en_US.yml:111) and renders the
 *   pim-associations-product-and-product-model-picker-modal form (form_extensions/associations/
 *   product.yml) into `.modal-body`. Every click on "Add associations" builds a new modal, so it is
 *   clicked once. The grid is association-product-picker-grid (search refreshes
 *   /datagrid/association-product-picker-grid). Its search box is the same `.search-filter
 *   input[name="value"]`, and rows are checked through `td.boolean-cell input[type="checkbox"]` (Behat
 *   DataGridContext::iCheckTheRows).
 * - Checking: product-model rows arrive with is_checked = true (Row::fromProductModel) and are reset
 *   to null by the picker's updateChecked only when the grid collection is set. A click toggles the
 *   value once (boolean-cell.js enterEditMode), so a click on a row that is still `true` unselects it.
 *   The click is therefore repeated only while the basket has no entry for that row. The basket
 *   (item-picker-basket.html) renders `data-itemCode="product_model;<technical id>"` on the row div AND
 *   on its remove button, so the selector is scoped to `.AknGrid-bodyRow`. HTML lowercases the
 *   attribute name.
 * - Picker Confirm: modal-centered.html renders `div.AknButton.AknFullPage-ok.ok` (no button role).
 *   associate.js then stores `{X_SELL: {product_uuids: [], product_models: [codes], groups: []}}` and
 *   navigates the wizard to its confirm step, which lists the items in `.step .basket-inner`
 *   (confirm.html). The label falls back to the model code (ProductModel::getLabel).
 */

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

// icecat_demo_dev family_variants.csv:2 (clothing: level-1 axis color, level-2 axes size + sku).
const FAMILY_VARIANT = 'clothing_color_size';
// icecat_demo_dev association_types.csv:2.
const ASSOCIATION_TYPE = 'X_SELL';
const ASSOCIATION_TYPE_LABEL = 'Cross sell';
const PICKER_TITLE = `Add ${ASSOCIATION_TYPE_LABEL} associations`;
const PICKER_GRID_URL = '/datagrid/association-product-picker-grid';

type CreatedModel = {code: string; id: number};

const ts = Date.now();
const SKUS = [1, 2, 3].map(n => `pw-massassoc-${ts}-${n}`);
// Underscores keep the SKU searches (hyphens) and the model search from matching each other's rows.
// The a/b discriminator comes last, so `massassoc_<ts>_pm` is contained in both codes (the
// label_or_identifier filter is a wildcard "contains" match).
const MODEL_CODES = [`pw_massassoc_${ts}_pm_a`, `pw_massassoc_${ts}_pm_b`];

const createdProductUuids: string[] = [];
const createdModels: CreatedModel[] = [];

async function getProductJson(page: Page, uuid: string): Promise<any> {
  const resp = await page.request.get(`/enrich/product/rest/${uuid}`, {headers: XHR_HEADER});
  const text = await resp.text();
  expect(resp.ok(), `GET product ${uuid} failed: ${resp.status()} ${text}`).toBe(true);
  return JSON.parse(text);
}

async function expectAssociationTypeUsable(page: Page) {
  // pim_enrich_associationtype_rest_index (compiled route dump, trailing slash).
  const resp = await page.request.get('/configuration/rest/association-type/', {headers: XHR_HEADER});
  const text = await resp.text();
  expect(resp.ok(), `List association types failed: ${resp.status()} ${text}`).toBe(true);
  const body = JSON.parse(text);
  const types: any[] = Array.isArray(body) ? body : Object.values(body);
  expect(
    types.map(type => type?.code),
    `association type ${ASSOCIATION_TYPE} is missing from the catalog: ${text}`
  ).toContain(ASSOCIATION_TYPE);
  const type = types.find(candidate => candidate?.code === ASSOCIATION_TYPE);
  // A quantified type renders the React QuantifiedAssociations confirm step (associate.js:80-87) and
  // is rejected by AssociationFieldAdder, so this flow needs a plain type.
  expect(type.is_quantified, `${ASSOCIATION_TYPE} must not be quantified: ${JSON.stringify(type)}`).toBe(false);
  expect(type.labels?.en_US, `${ASSOCIATION_TYPE} en_US label: ${JSON.stringify(type)}`).toBe(ASSOCIATION_TYPE_LABEL);
}

async function pickAssociationType(page: Page, code: string, label: string) {
  const selector = page.locator('.association-type-selector');
  await expect(selector, 'the Associate configure step never rendered its association type selector').toBeVisible({
    timeout: 60_000,
  });
  await selector.locator('[data-toggle="dropdown"]').click({timeout: 15_000});
  const option = selector.locator(`.associations-list li[data-association-type="${code}"]`);
  await expect(option, `association type "${code}" is not listed in the open type dropdown`).toBeVisible({
    timeout: 15_000,
  });
  await option.click({timeout: 15_000});
  await expect(
    selector.locator('.AknActionButton-highlight'),
    `the association type selector does not show "${label}" after picking ${code}`
  ).toHaveText(label, {timeout: 30_000});
}

async function pickProductModelsInPicker(page: Page, picker: Locator, searchFragment: string, models: CreatedModel[]) {
  const search = picker.locator('.search-filter input[name="value"]');
  await expect(search, 'the item picker search input never rendered').toBeVisible({timeout: 60_000});

  const rows = picker.locator('tr.AknGrid-bodyRow');
  // The grid sends no request for an unchanged term, so each retry alternates between two terms that
  // both match the two models (and nothing else).
  const terms = [searchFragment, `${searchFragment}_`];
  let attempt = 0;
  await expect(async () => {
    const term = terms[attempt++ % terms.length];
    const refresh = page.waitForResponse(resp => resp.url().includes(PICKER_GRID_URL), {timeout: 30_000});
    refresh.catch(() => {});
    await search.fill(term, {timeout: 10_000});
    await search.press('Enter', {timeout: 10_000});
    await refresh;
    for (const {code} of models) {
      await expect(rows.filter({hasText: code}), `picker row ${code} not listed for "${term}"`).toHaveCount(1, {
        timeout: 5_000,
      });
    }
  }).toPass({timeout: 120_000});

  for (const {code, id} of models) {
    const basketRow = picker.locator(`.item-picker-basket .AknGrid-bodyRow[data-itemcode="product_model;${id}"]`);
    const checkbox = rows.filter({hasText: code}).locator('td.boolean-cell input[type="checkbox"]:not(:disabled)');
    await expect(async () => {
      // Click only while the model is not in the basket: a click toggles, so repeating it after a
      // successful selection would remove the model again.
      if ((await basketRow.count()) === 0) {
        await checkbox.click({timeout: 5_000});
      }
      await expect(basketRow, `product model ${code} (id ${id}) did not reach the picker basket`).toHaveCount(1, {
        timeout: 5_000,
      });
    }).toPass({timeout: 30_000});
  }

  await expect(
    picker.locator('.item-picker-basket .AknGrid-bodyRow'),
    'the picker basket should hold exactly the 2 product models'
  ).toHaveCount(models.length, {timeout: 10_000});
}

test.describe('Mass product association', () => {
  test.afterAll(async ({browser}) => {
    if (createdProductUuids.length === 0 && createdModels.length === 0) {
      return;
    }
    const page = await browser.newPage();
    try {
      await login(page, 'admin', 'admin');
      // Products first: they reference the models through their associations.
      for (const uuid of createdProductUuids) {
        const resp = await deleteProductViaApi(page, uuid).catch(() => null);
        if (!resp?.ok()) {
          console.warn(
            `cleanup: DELETE product ${uuid} -> ${resp?.status() ?? 'no response'} ${await resp?.text().catch(() => '')}`
          );
        }
      }
      for (const {code, id} of createdModels) {
        const resp = await deleteProductModelViaApi(page, id).catch(() => null);
        if (!resp?.ok()) {
          console.warn(
            `cleanup: DELETE product model ${code} (${id}) -> ${resp?.status() ?? 'no response'} ` +
              `${await resp?.text().catch(() => '')}`
          );
        }
      }
    } catch (error) {
      console.warn(`cleanup failed: ${error instanceof Error ? error.message : String(error)}`);
    } finally {
      await page.close().catch(() => {});
    }
  });

  test('Mass associate products to product models', async ({page}) => {
    // Grid and picker retries (<= 2 min each), the process tracker poll (<= 180s) and the job wait
    // (<= 420s), like mass-edit-image.spec.ts.
    test.setTimeout(900_000);

    await login(page, 'admin', 'admin');

    // --- Disposable data, one entity at a time ---
    await expectAssociationTypeUsable(page);

    for (const code of MODEL_CODES) {
      const resp = await createProductModelViaApi(page, code, FAMILY_VARIANT);
      const text = await resp.text();
      expect(resp.ok(), `Create product model ${code} failed: ${resp.status()} ${text}`).toBe(true);
      const id = JSON.parse(text)?.meta?.id;
      expect(typeof id, `Create product model ${code} returned no numeric meta.id: ${text}`).toBe('number');
      createdModels.push({code, id});
    }

    for (const sku of SKUS) {
      const resp = await createProductViaApi(page, sku);
      const text = await resp.text();
      expect(resp.ok(), `Create product ${sku} failed: ${resp.status()} ${text}`).toBe(true);
      const uuid = JSON.parse(text)?.meta?.uuid;
      expect(uuid, `Create product ${sku} returned no meta.uuid: ${text}`).toBeTruthy();
      createdProductUuids.push(uuid);
    }

    for (const uuid of createdProductUuids) {
      const product = await getProductJson(page, uuid);
      expect(
        product.associations?.[ASSOCIATION_TYPE]?.product_models ?? [],
        `product ${uuid} already has ${ASSOCIATION_TYPE} product models before the mass edit: ` +
          JSON.stringify(product.associations)
      ).toEqual([]);
    }

    // --- Select the 3 products in the grid ---
    await goToProductsGrid(page);
    const skuTerms = [`pw-massassoc-${ts}`, `pw-massassoc-${ts}-`];
    let gridAttempt = 0;
    await expect(async () => {
      // An unchanged term sends no request, so retries alternate between two terms matching all 3 SKUs.
      await searchProductGrid(page, skuTerms[gridAttempt++ % skuTerms.length]);
      await expect(
        page.locator('tr.AknGrid-bodyRow').filter({hasText: `pw-massassoc-${ts}-`}),
        'the product grid search does not list the 3 disposable products'
      ).toHaveCount(SKUS.length, {timeout: 5_000});
    }).toPass({timeout: 120_000});
    await selectProductsBySku(page, SKUS);

    // --- Bulk actions > Associate products > configure ---
    await openMassEditOperation(page, 'Associate products');
    await pickAssociationType(page, ASSOCIATION_TYPE, ASSOCIATION_TYPE_LABEL);

    const pickerModal = page.locator('div.modal').filter({hasText: PICKER_TITLE});
    await page.locator('.add-associations', {hasText: 'Add associations'}).click({timeout: 30_000});
    await expect(
      pickerModal.locator('.AknFullPage-title', {hasText: PICKER_TITLE}),
      `the "${PICKER_TITLE}" picker did not open`
    ).toBeVisible({timeout: 30_000});

    await pickProductModelsInPicker(page, pickerModal, `massassoc_${ts}_pm`, createdModels);

    await pickerModal.locator('.AknFullPage-ok.ok').click({timeout: 15_000});
    await expect(pickerModal, 'the picker modal did not close after Confirm').toHaveCount(0, {timeout: 30_000});

    // --- Confirm step lists both models ---
    await expect(
      page.locator('.wizard-action[data-action-target="validate"]'),
      'the wizard did not reach its confirm step after the picker Confirm'
    ).toBeVisible({timeout: 30_000});
    const confirmList = page.locator('.step .basket-inner');
    await expect(
      confirmList.locator('.AknGrid-bodyRow'),
      'the confirm step should list the 2 picked product models'
    ).toHaveCount(createdModels.length, {timeout: 30_000});
    for (const code of MODEL_CODES) {
      await expect(confirmList, `the confirm step does not list ${code}`).toContainText(code, {timeout: 10_000});
    }

    // --- Launch through the real Confirm button ---
    const {jobId, payload} = await launchMassEditJob(page, 'add_association');
    const payloadDump = `launch payload: ${JSON.stringify(payload)}`;
    expect(payload?.jobInstanceCode, payloadDump).toBe('add_association');
    expect(payload?.itemsCount, payloadDump).toBe(SKUS.length);
    expect(payload?.actions?.[0]?.field, payloadDump).toBe('associations');
    const launchedAssociation = payload?.actions?.[0]?.value?.[ASSOCIATION_TYPE];
    expect([...(launchedAssociation?.product_models ?? [])].sort(), payloadDump).toEqual([...MODEL_CODES].sort());
    expect(launchedAssociation?.product_uuids, payloadDump).toEqual([]);
    expect(payload?.filters?.[0], payloadDump).toMatchObject({field: 'id', operator: 'IN'});
    expect(payload?.filters?.[0]?.value, payloadDump).toHaveLength(SKUS.length);

    // --- The job completes without warnings or skipped items ---
    const execution = await waitForJobExecutionViaApi(page, jobId, 420_000);
    const executionDump = JSON.stringify(execution);
    expect(execution.status, `add_association job ${jobId} did not complete: ${executionDump}`).toBe('COMPLETED');
    const steps: any[] = execution.stepExecutions ?? [];
    expect(steps.length, `add_association job ${jobId} has no step executions: ${executionDump}`).toBeGreaterThan(0);
    for (const step of steps) {
      expect(step.warnings ?? [], `step "${step.label}" of job ${jobId} has warnings: ${JSON.stringify(step)}`).toEqual(
        []
      );
      // Summary keys come back translated (StepExecutionNormalizer::normalizeSummary), e.g. "Skipped products".
      expect(
        Object.keys(step.summary ?? {}).filter(key => /skipped/i.test(key)),
        `step "${step.label}" of job ${jobId} skipped items: ${JSON.stringify(step.summary)}`
      ).toEqual([]);
    }

    // --- Every product now has both product models, and nothing else, in X_SELL ---
    for (const uuid of createdProductUuids) {
      const product = await getProductJson(page, uuid);
      const associationsDump = `product ${uuid} associations: ${JSON.stringify(product.associations)}`;
      const association = product.associations?.[ASSOCIATION_TYPE];
      expect([...(association?.product_models ?? [])].sort(), associationsDump).toEqual([...MODEL_CODES].sort());
      expect(association?.product_uuids, associationsDump).toEqual([]);
      expect(association?.groups, associationsDump).toEqual([]);
    }
  });
});
