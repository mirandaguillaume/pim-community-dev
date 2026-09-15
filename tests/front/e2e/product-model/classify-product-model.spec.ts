import {test, expect, Page} from '../fixtures/coverage-fixture';
import {
  login,
  createProductModelViaApi,
  deleteProductModelViaApi,
  createCategoryViaApi,
  cleanUpCategoriesViaApi,
  openCategoriesTab,
  showCategoryTree,
  responseBody,
  XHR_HEADER,
} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product-model/classify_product_model.feature:26
 *   "Count sub product model categories"
 *
 * Behat steps:
 *   Given I edit the "model-nin-black" product model
 *   When I visit the "Categories" column tab
 *   And I visit the "Master" tab
 *   Then I should see 3 category count
 *   And the category of the product model "model-nin-black" should be "tshirts, summer and spring"
 * The sub product model model-nin-black is classified in summer and spring, and inherits tshirts from its root
 * model-nin. Nothing is clicked in the tree: the count of 3 comes from the server as soon as the tab loads.
 *
 * Tests:
 * 1. 'shows the own and inherited categories of a sub product model as soon as the tab loads' is the port of the
 *    scenario.
 * 2. 'ticks categories in the Master tree, saves them and counts them in the tree badge' covers the client side of
 *    the same widget on a flat model: ticking, the badge, the save and its persistence.
 *
 * Adaptations:
 * - Catalog: CI runs icecat_demo_dev, not catalog_modeling. The models use the family variant clothing_color_size
 *   (level-1 axis color, option blue), as add-product-model-children.spec.ts does. tshirts, summer and spring become
 *   disposable categories A (inherited), B and C (own) under the icecat root tree `master`. `master` is hard-coded,
 *   because GET /enrich/category/rest lists roots in no guaranteed order. The variant product nin-black-m is dropped:
 *   the badge and the model's categories only depend on the model's own and ancestors' categories
 *   (AbstractItemCategoryRepository::getItemCountByTree counts the model's rows, ProductModel::getCategories merges
 *   the ancestors' ones).
 * - "I visit the Master tab" is kept as a click on the master tree tab. Which tree renders on load is not guaranteed
 *   (see showCategoryTree), so the checkboxes are only looked for after that click.
 * - User: admin instead of Julia. The scenario asserts no permission behaviour (Julia, ROLE_CATALOG_MANAGER, would
 *   also do).
 * - Added checks: the Categories tab listing (tree `associated`, and id/code/rootId of the own and inherited
 *   categories, ProductModelCategoryController), every category ticked on load, the inherited one locked
 *   (meta.ascendant_category_ids -> readOnly -> tabindex -1), the root model's categories left untouched, and the
 *   badge scoped to the master tree instead of any `.AknBadge` containing "3".
 * Backend guards that run on backend-only PRs: ProductModelCategoryControllerTest (the listing) and ProductModelTest
 * (the merge of own and inherited categories).
 *
 * Selectors traced from:
 * - "I visit the "Categories" column tab": Base.php::visitColumnTab() -> '.column-navigation-link' (openCategoriesTab).
 * - Tree tabs and badges: catalog-switcher.html, one `#trees-list li[data-tree=<code>]` per tree with an `.AknBadge`
 *   counting that tree's selected categories (categories.js initCategoryCount / updateModel). "I should see 3 category
 *   count" was WebUser.php::iShouldSeeCategoryCount() -> `.AknBadge:contains("3")`.
 * - Tree nodes: the DSM Tree (role=treeitem, name = the node label) with a DSM Checkbox (role=checkbox, aria-checked,
 *   tabindex -1 when readOnly). getByRole('treeitem', {name}) is used rather than filter({hasText}): nodes nest, so
 *   hasText also matches every ancestor.
 */

// icecat_demo_dev root tree (categories.csv:2 `master;;Master catalog`).
const TREE_CODE = 'master';
// icecat_demo_dev family variant: level-1 axis color, level-2 axis size (family_variants.csv).
const FAMILY_VARIANT = 'clothing_color_size';

type DisposableCategory = {code: string; label: string};

async function createCategories(page: Page, categories: DisposableCategory[]): Promise<void> {
  // One at a time: entities created concurrently under the same parent can race.
  for (const {code, label} of categories) {
    const resp = await createCategoryViaApi(page, code, TREE_CODE, label);
    expect(resp.status(), `Create category ${code} failed: ${resp.status()} ${await responseBody(resp)}`).toBe(201);
  }
}

async function createModel(
  page: Page,
  code: string,
  categories: string[],
  values?: Record<string, unknown>,
  parent?: string
): Promise<number> {
  const resp = await createProductModelViaApi(page, code, FAMILY_VARIANT, values, parent, {categories});
  const text = await responseBody(resp);
  expect(resp.ok(), `Create product model ${code} failed: ${resp.status()} ${text}`).toBe(true);
  const id = JSON.parse(text)?.meta?.id;
  expect(typeof id, `Create product model ${code}: no numeric meta.id in ${text}`).toBe('number');
  return id;
}

async function getProductModelCategoryCodes(page: Page, id: number): Promise<string[]> {
  const resp = await page.request.get(`/enrich/product-model/rest/${id}`, {headers: XHR_HEADER, timeout: 30_000});
  const text = await responseBody(resp);
  expect(resp.ok(), `Get product model ${id} failed: ${resp.status()} ${text}`).toBe(true);
  const categories = JSON.parse(text)?.categories;
  expect(Array.isArray(categories), `Product model ${id} has no categories list: ${text}`).toBe(true);
  return [...categories].sort();
}

/**
 * Best-effort cleanup that never throws, so it cannot mask the test's own error. Pass children before their parent:
 * a model still referenced by another is not deleted.
 */
async function cleanUpProductModels(page: Page, ids: number[]): Promise<void> {
  for (const id of ids) {
    try {
      const resp = await deleteProductModelViaApi(page, id);
      if (!resp.ok()) {
        console.warn(`Cleanup: delete product model ${id} returned ${resp.status()} ${await responseBody(resp)}`);
      }
    } catch (e) {
      console.warn(`Cleanup: product model ${id}: ${(e as Error).message}`);
    }
  }
}

function checkboxOf(tree: ReturnType<Page['locator']>, category: DisposableCategory) {
  return tree.getByRole('treeitem', {name: category.label, exact: true}).getByRole('checkbox');
}

function byCode(a: {code: string}, b: {code: string}): number {
  return a.code < b.code ? -1 : a.code > b.code ? 1 : 0;
}

test.describe('Classify a product model', () => {
  let nav: NavigationHelper;

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    nav = new NavigationHelper(page);
  });

  test('shows the own and inherited categories of a sub product model as soon as the tab loads', async ({page}) => {
    const ts = Date.now();
    const inherited: DisposableCategory = {code: `pw_pmcat_a_${ts}`, label: `PW PM Cat A ${ts}`};
    const own: DisposableCategory[] = [
      {code: `pw_pmcat_b_${ts}`, label: `PW PM Cat B ${ts}`},
      {code: `pw_pmcat_c_${ts}`, label: `PW PM Cat C ${ts}`},
    ];
    const allCodes = [inherited, ...own].map(c => c.code).sort();
    const rootCode = `pw_pm_root_${ts}`;
    const subCode = `pw_pm_sub_${ts}`;
    // Children first, for the cleanup.
    const modelIds: number[] = [];

    try {
      await createCategories(page, [inherited, ...own]);
      const rootId = await createModel(page, rootCode, [inherited.code]);
      modelIds.unshift(rootId);
      const subId = await createModel(
        page,
        subCode,
        own.map(c => c.code),
        {color: [{locale: null, scope: null, data: 'blue'}]},
        rootCode
      );
      modelIds.unshift(subId);

      // Given I edit the "model-nin-black" product model
      await nav.goToEntityPage('product model', String(subId));
      await expect(page.getByText(subCode).first()).toBeVisible({timeout: 15_000});

      // When I visit the "Categories" column tab
      const listing = await openCategoriesTab(page, `/enrich/product-model/rest/${subId}/categories`);
      const tree = listing.trees.find(t => t.code === TREE_CODE);
      expect(tree, `no ${TREE_CODE} tree in the Categories tab listing: ${JSON.stringify(listing)}`).toBeDefined();
      expect(
        tree!.associated,
        `the sub model has categories of its own in ${TREE_CODE}: ${JSON.stringify(listing)}`
      ).toBe(true);
      expect(
        [...listing.categories].sort(byCode),
        `the listing must hold the own and the inherited categories: ${JSON.stringify(listing)}`
      ).toEqual(allCodes.map(code => ({id: expect.any(Number), code, rootId: tree!.id})));

      // Then I should see 3 category count: the badges are computed when the tab loads, before any click.
      const badge = page.locator(`#trees-list li[data-tree="${TREE_CODE}"] .AknBadge`);
      await expect(badge).toHaveText('3', {timeout: 15_000});

      // And I visit the "Master" tab
      const panel = await showCategoryTree(page, TREE_CODE, tree!.id);
      for (const category of [inherited, ...own]) {
        await expect(checkboxOf(panel, category), `${category.code} is not ticked on load`).toHaveAttribute(
          'aria-checked',
          'true',
          {timeout: 30_000}
        );
      }
      // The inherited category is locked, the model's own ones are not.
      await expect(checkboxOf(panel, inherited), `${inherited.code} is inherited: it must be locked`).toHaveAttribute(
        'tabindex',
        '-1'
      );
      for (const category of own) {
        await expect(
          checkboxOf(panel, category),
          `${category.code} is the model's own: it must be editable`
        ).toHaveAttribute('tabindex', '0');
      }
      await expect(badge).toHaveText('3', {timeout: 15_000});
      // Sanity check once the tree has rendered: loading the tab must not dirty the form.
      await expect(page.getByText('There are unsaved changes.')).toBeHidden({timeout: 5_000});

      // And the category of the product model "model-nin-black" should be "tshirts, summer and spring"
      expect(await getProductModelCategoryCodes(page, subId)).toEqual(allCodes);
      // Classifying the sub model did not leak onto its root.
      expect(await getProductModelCategoryCodes(page, rootId)).toEqual([inherited.code]);
    } finally {
      await cleanUpProductModels(page, modelIds);
      await cleanUpCategoriesViaApi(
        page,
        [...own, inherited].map(c => c.code)
      );
    }
  });

  test('ticks categories in the Master tree, saves them and counts them in the tree badge', async ({page}) => {
    const ts = Date.now();
    const categories: DisposableCategory[] = [
      {code: `pw_cnt_a_${ts}`, label: `PW Count A ${ts}`},
      {code: `pw_cnt_b_${ts}`, label: `PW Count B ${ts}`},
      {code: `pw_cnt_c_${ts}`, label: `PW Count C ${ts}`},
    ];
    const modelCode = `pw_classify_pm_${ts}`;
    const modelIds: number[] = [];

    try {
      await createCategories(page, categories);
      const modelId = await createModel(page, modelCode, []);
      modelIds.push(modelId);

      await nav.goToEntityPage('product model', String(modelId));
      await expect(page.getByText(modelCode).first()).toBeVisible({timeout: 15_000});

      // I visit the "Categories" column tab / I visit the "Master" tab
      const listing = await openCategoriesTab(page, `/enrich/product-model/rest/${modelId}/categories`);
      const tree = listing.trees.find(t => t.code === TREE_CODE);
      expect(tree, `no ${TREE_CODE} tree in the Categories tab listing: ${JSON.stringify(listing)}`).toBeDefined();
      const panel = await showCategoryTree(page, TREE_CODE, tree!.id);

      for (const category of categories) {
        const checkbox = checkboxOf(panel, category);
        await expect(checkbox, `${category.code} must start unticked`).toHaveAttribute('aria-checked', 'false', {
          timeout: 30_000,
        });
        await checkbox.click({timeout: 15_000});
        await expect(checkbox).toHaveAttribute('aria-checked', 'true', {timeout: 15_000});
      }
      const badge = page.locator(`#trees-list li[data-tree="${TREE_CODE}"] .AknBadge`);
      await expect(badge).toHaveText('3', {timeout: 15_000});

      // Save: PEF save of a product model, POST /enrich/product-model/rest/{id} (pim_enrich_product_model_rest_post).
      const [saveResp] = await Promise.all([
        page.waitForResponse(
          r => r.request().method() === 'POST' && new URL(r.url()).pathname === `/enrich/product-model/rest/${modelId}`,
          {timeout: 60_000}
        ),
        page.getByRole('button', {name: 'Save', exact: true}).click({timeout: 15_000}),
      ]);
      expect(
        saveResp.ok(),
        `Save product model ${modelCode} failed: ${saveResp.status()} ${await responseBody(saveResp)}`
      ).toBe(true);
      await expect(page.getByText('There are unsaved changes.')).toBeHidden({timeout: 15_000});

      // Then I should see 3 category count
      await expect(badge).toHaveText('3', {timeout: 15_000});

      // Persisted: exactly the ticked categories.
      expect(await getProductModelCategoryCodes(page, modelId)).toEqual(categories.map(c => c.code).sort());
    } finally {
      await cleanUpProductModels(page, modelIds);
      await cleanUpCategoriesViaApi(
        page,
        categories.map(c => c.code)
      );
    }
  });
});
