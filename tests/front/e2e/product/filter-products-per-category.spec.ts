import {test, expect, Page} from '../fixtures/coverage-fixture';
import {
  login,
  goToProductsGrid,
  waitForLoadingMasks,
  createProductViaApi,
  goToProductBySearch,
  createCategoryViaApi,
} from '../fixtures/pim';

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

/**
 * Returns the code and label of an existing root category tree. A brand-new root tree created
 * via createCategoryViaApi does NOT reliably show up in the product-edit "Categories" tab tree
 * widget within a normal Playwright timeout — confirmed live in CI (element(s) not found after
 * 15s, reproduced on both the initial attempt and a retry with a different disposable tree each
 * time) even though the backing query (ProductCategoryController::listAction ->
 * getItemCountByTree) is a plain synchronous SQL SELECT with no cache/ES layer, so the data
 * itself isn't stale — the most likely cause is category-tree view ACL not being granted to the
 * current user for a tree created via this raw endpoint. classify-product.spec.ts sidesteps this
 * exact problem by adding categories under an EXISTING root tree rather than creating a new one;
 * this spec follows the same proven pattern.
 */
async function getFirstRootCategory(page: Page): Promise<{code: string; label: string}> {
  const resp = await page.request.get('/enrich/category/rest', {headers: XHR_HEADER});
  expect(resp.ok(), `List root categories failed: ${resp.status()}`).toBeTruthy();
  const categories = await resp.json();
  const list = Array.isArray(categories) ? categories : Object.values(categories);
  const first = list[0] as any;
  expect(first, 'expected at least one root category tree in the catalog').toBeTruthy();
  // The internal API returns locale-keyed `labels` (plural), not a resolved `label` field —
  // e.g. {code: 'master', labels: {en_US: 'Master catalog'}}. Falling back to first.label (always
  // undefined) silently produced the category's CODE instead of its displayed label, causing a
  // deterministic mismatch against the tree widget's real (translated-label) accessible name.
  return {code: first.code, label: first.labels?.en_US ?? first.code};
}

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product/datagrid/filtering/filter_products_per_category.feature:19
 *   "Successfully filter products by category"
 *
 * The Behat scenario depends on the "apparel" catalog fixture's "2015 collection" tree, with
 * two sub-categories ("2015 women's collection" / "2015 men's collection") and specific products
 * pre-classified into them. This spec instead adds a disposable 3-level branch of categories
 * under whatever root tree already exists in the catalog (never creates a new root tree — see
 * getFirstRootCategory's comment for why), then classifies disposable products into it through
 * the real product-edit "Categories" tab UI (same pattern as classify-product.spec.ts), so it
 * works against any catalog.
 *
 * Category-tree-panel vs category-filter — resolved by tracing the real source, not assumed:
 * the product grid's category tree side panel is a SEPARATE UI element from the generic
 * filters-column/filters-selector React filter system the rest of this migration re-points
 * (text/choice/number/date/select2-* filters). It has its own legacy Backbone bridge chain:
 *   - Toggle button: src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/product/grid/
 *     category-switcher.js (`.category-switcher`, toggles `.AknDefault-thirdColumnContainer--open`)
 *   - Panel host form: .../product/grid/category-tree.js (Backbone form, `id="tree"`,
 *     `class="filter-item" data-name="category" data-type="tree"`), which builds
 *     src/Oro/Bundle/PimDataGridBundle/.../datafilter/filter/product_category-filter.js — this
 *     still integrates with the grid via the same `filters-column:init` /
 *     `filters-column:update-filter` mediator events the migrated React filters use, but the
 *     widget it mounts is:
 *   - src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/TreeView.tsx (requirejs alias
 *     `pim/tree/view`) — a thin ReactDOM.render bridge that mounts <CategoryTrees> from
 *     @akeneo-pim-community/shared (front-packages/shared/src/components/CategoryTree/
 *     CategoryTrees.tsx + CategoryTreeSwitcher.tsx) — the SAME component reused by the category
 *     management app (critical/category.spec.ts). The tree panel is already React, bridged into
 *     legacy Backbone the same way the price/metric filter option-dropdowns are — it was never
 *     migrated as part of a filters-column wave because it was never a filters-column filter.
 *
 * Selectors traced from the real DOM contract (cross-checked against the Behat page
 * objects/decorators that already encode it, and against classify-product.spec.ts which already
 * exercises the same underlying Tree component elsewhere):
 *   - Toggle: WebUser.php::iToggleTheCategoryTree() -> `.category-switcher`,
 *     `.AknDefault-thirdColumnContainer--open`.
 *   - Tree select dropdown: Product/Index.php `'Tree select' => '#tree [aria-haspopup="listbox"]
 *     button'`; options render in a portal Overlay.tsx creates at runtime (`#dropdown-root`),
 *     `Dropdown.ItemCollection role="listbox"` / `Dropdown.Item role="option"`
 *     (CategoryTreeSwitcher.tsx).
 *   - Tree nodes: Tree.tsx renders `li[role="treeitem"]` with 2 buttons — an unlabelled arrow
 *     toggle (expand/collapse, toggles aria-expanded) and a title+text-labelled button (select)
 *     — matches CategoryDecorator.php's `button:nth-child(2)`. getByRole('treeitem',
 *     {name, exact:true}) correctly scopes to a single node's own label even when nested — same
 *     fix already proven in classify-product.spec.ts.
 *   - "Unclassified products": a synthetic leaf node TreeView.tsx injects into every tree's
 *     children (jstree.unclassified translation, "Unclassified products" in en_US) — matches
 *     CategoryDecorator.php's `button[title="Unclassified products"]`.
 *   - "Include sub-categories" switch: BooleanInput role="switch" inside #tree, with dedicated
 *     Yes/No buttons (translated "Yes"/"No") — clicking those directly is more reliable than
 *     reading aria-checked and toggling.
 *
 * The grid's free-text search is combined with the category tree filter throughout (they AND
 * together, standard datagrid behavior) to scope every assertion to only this test's disposable
 * products — the shared/seeded catalog has many pre-existing products that would also read as
 * "unclassified" with respect to a brand-new disposable tree, so an unscoped grid-count assertion
 * (as the original Behat scenario does, safe only because Behat runs against an isolated
 * per-scenario "apparel" fixture DB) would be unreliable here.
 *
 * The original scenario's "2015 men's collection" node has no directly-classified product (only
 * "blue-jeans", classified into a nested "men_2015_summer" sub-category, appears once "Include
 * sub-categories" is re-enabled) — that recursive-matching behaviour is the actual point of the
 * scenario, so this spec reproduces the same 3-level shape (tree -> men -> men-summer) rather
 * than flattening it away.
 */

function categoryPanel(page: Page) {
  return page.locator('#tree');
}

function gridRow(page: Page, sku: string) {
  return page.locator('tr.AknGrid-bodyRow').filter({hasText: sku});
}

async function openCategoryTreePanel(page: Page) {
  const thirdColumn = page.locator('.AknDefault-thirdColumnContainer');
  const isOpen = await thirdColumn
    .evaluate(el => el.classList.contains('AknDefault-thirdColumnContainer--open'))
    .catch(() => false);
  if (!isOpen) {
    await page.locator('.category-switcher').click();
  }
  await expect(categoryPanel(page)).toBeVisible({timeout: 15_000});
}

async function selectCategoryTree(page: Page, treeLabel: string) {
  await page.locator('#tree [aria-haspopup="listbox"] button').click();
  const dropdown = page.locator('#dropdown-root [role="listbox"]');
  await expect(dropdown).toBeVisible({timeout: 10_000});
  await dropdown.getByRole('option', {name: treeLabel, exact: true}).click();
  await waitForLoadingMasks(page);
}

async function clickCategoryNode(page: Page, label: string) {
  const node = categoryPanel(page).getByRole('treeitem', {name: label, exact: true});
  await expect(node).toBeVisible({timeout: 15_000});
  await node.getByRole('button', {name: label, exact: true}).click();
  await waitForLoadingMasks(page);
}

async function clickUnclassified(page: Page) {
  await categoryPanel(page).getByRole('button', {name: 'Unclassified products'}).click();
  await waitForLoadingMasks(page);
}

async function setIncludeSubCategories(page: Page, include: boolean) {
  const switchLocator = categoryPanel(page).getByRole('switch');
  await expect(switchLocator).toBeVisible({timeout: 10_000});
  await switchLocator.getByRole('button', {name: include ? 'Yes' : 'No', exact: true}).click();
}

async function searchGrid(page: Page, term: string) {
  const searchInput = page.locator('.search-zone input[type="search"], .AknFilterBox-search input');
  await searchInput.fill(term);
  await searchInput.press('Enter');
  await page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'));
  await waitForLoadingMasks(page);
}

/**
 * Classifies a disposable product into a category via the real product-edit "Categories" tab —
 * same DOM contract as classify-product.spec.ts (#trees, li[role=treeitem], div[role=checkbox]),
 * walking a path of labels from the tree root down to the target node, expanding intermediate
 * nodes on the way (TreeDecorator.php::expandNode()).
 */
async function classifyProductIntoCategory(page: Page, sku: string, treeLabel: string, path: string[]) {
  await goToProductBySearch(page, sku);
  await page.locator('.column-navigation-link').filter({hasText: 'Categories'}).click();
  await waitForLoadingMasks(page);

  const categoryTree = page.locator('#trees');
  await expect(categoryTree).toBeVisible({timeout: 15_000});

  const root = categoryTree.getByRole('treeitem', {name: treeLabel, exact: true});
  await expect(root).toBeVisible({timeout: 15_000});
  if ((await root.getAttribute('aria-expanded')) === 'false') {
    await root.getByRole('button').first().click();
  }

  let current = root;
  for (const label of path) {
    const child = current.getByRole('treeitem', {name: label, exact: true});
    await expect(child).toBeVisible({timeout: 15_000});
    if (label !== path[path.length - 1] && (await child.getAttribute('aria-expanded')) === 'false') {
      await child.getByRole('button').first().click();
    }
    current = child;
  }

  const checkbox = current.getByRole('checkbox');
  if ((await checkbox.getAttribute('aria-checked')) === 'false') {
    await checkbox.click();
  }

  await page.getByText('Save').first().click();
  await waitForLoadingMasks(page);
  await expect(page.getByText('There are unsaved changes.')).not.toBeVisible({timeout: 15_000});
}

test.describe('Filter products by category', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('can filter the product grid by category via the tree panel', async ({page}) => {
    const ts = Date.now();
    const prefix = `pw-cat-${ts}`;
    const skuWomenA = `${prefix}-women-a`;
    const skuWomenB = `${prefix}-women-b`;
    const skuMen = `${prefix}-men`;
    const skuUnclassified = `${prefix}-unclassified`;

    const womenCode = `pw_women_${ts}`;
    const womenLabel = `PW Women ${ts}`;
    const menCode = `pw_men_${ts}`;
    const menLabel = `PW Men ${ts}`;
    const menSummerCode = `pw_men_summer_${ts}`;
    const menSummerLabel = `PW Men Summer ${ts}`;

    // Add a disposable 3-level branch under the catalog's existing root tree: root -> {women,
    // men -> men-summer}. See getFirstRootCategory's comment for why we don't create a new tree.
    const {code: treeCode, label: treeLabel} = await getFirstRootCategory(page);
    const [womenResp, menResp] = await Promise.all([
      createCategoryViaApi(page, womenCode, treeCode, womenLabel),
      createCategoryViaApi(page, menCode, treeCode, menLabel),
    ]);
    expect(womenResp.ok(), `Create category ${womenCode} failed: ${womenResp.status()}`).toBeTruthy();
    expect(menResp.ok(), `Create category ${menCode} failed: ${menResp.status()}`).toBeTruthy();
    const menSummerResp = await createCategoryViaApi(page, menSummerCode, menCode, menSummerLabel);
    expect(menSummerResp.ok(), `Create category ${menSummerCode} failed: ${menSummerResp.status()}`).toBeTruthy();

    // Create the 4 disposable products (one left unclassified).
    for (const sku of [skuWomenA, skuWomenB, skuMen, skuUnclassified]) {
      const resp = await createProductViaApi(page, sku);
      expect(resp.ok(), `Create product ${sku} failed: ${resp.status()}`).toBeTruthy();
    }

    // Classify via the real product-edit "Categories" tab UI.
    await classifyProductIntoCategory(page, skuWomenA, treeLabel, [womenLabel]);
    await classifyProductIntoCategory(page, skuWomenB, treeLabel, [womenLabel]);
    await classifyProductIntoCategory(page, skuMen, treeLabel, [menLabel, menSummerLabel]);

    // --- The scenario under test ---
    await goToProductsGrid(page);
    await searchGrid(page, prefix);

    await openCategoryTreePanel(page);
    await selectCategoryTree(page, treeLabel);

    // Selecting the tree (no specific node) shows every classified product under it.
    await expect(gridRow(page, skuWomenA)).toBeVisible({timeout: 15_000});
    await expect(gridRow(page, skuWomenB)).toBeVisible({timeout: 15_000});
    await expect(gridRow(page, skuMen)).toBeVisible({timeout: 15_000});
    await expect(gridRow(page, skuUnclassified)).not.toBeVisible();

    await setIncludeSubCategories(page, false);

    // Direct children only: "women" has 2 direct products.
    await clickCategoryNode(page, womenLabel);
    await expect(gridRow(page, skuWomenA)).toBeVisible({timeout: 15_000});
    await expect(gridRow(page, skuWomenB)).toBeVisible({timeout: 15_000});
    await expect(gridRow(page, skuMen)).not.toBeVisible();

    // "men" has no directly-classified product (skuMen is one level deeper, in men-summer) — with
    // sub-categories off, filtering by "men" must NOT surface it.
    await clickCategoryNode(page, menLabel);
    await expect(gridRow(page, skuMen)).not.toBeVisible();
    await expect(gridRow(page, skuWomenA)).not.toBeVisible();

    // "unclassified" (scoped to this tree) surfaces the product with no categories at all.
    await clickUnclassified(page);
    await expect(gridRow(page, skuUnclassified)).toBeVisible({timeout: 15_000});
    await expect(gridRow(page, skuWomenA)).not.toBeVisible();
    await expect(gridRow(page, skuMen)).not.toBeVisible();

    // Re-enable "Include sub-categories" and re-select the tree, mirroring the original
    // scenario's exact step sequence (CategoryTree's fetch only re-runs when the tree selection
    // itself changes, not merely when the switch value changes).
    await setIncludeSubCategories(page, true);
    await selectCategoryTree(page, treeLabel);

    // Now "men" recursively includes its "men-summer" child, surfacing skuMen.
    await clickCategoryNode(page, menLabel);
    await expect(gridRow(page, skuMen)).toBeVisible({timeout: 15_000});
  });
});
