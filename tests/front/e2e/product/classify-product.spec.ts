import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  goToProductBySearch,
  waitForLoadingMasks,
  createProductViaApi,
  getProductViaApi,
  getFirstRootCategoryCode,
  createCategoryViaApi,
} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product/pef/classify/classify_product.feature:16
 *   "Associate a product to categories"
 *
 * The Behat scenario relies on the footwear catalog's fixture tree ("2014 collection" >
 * "Summer collection"/"Winter collection"). This spec creates its own disposable root-level
 * categories instead, via the same REST endpoint the category management React app uses
 * (createCategory.ts -> POST /enrich/product-category-tree/create), so it works against any
 * catalog with at least one root tree (every install ships with one).
 *
 * Selectors traced from:
 * - "I visit the "Categories" column tab": WebUser.php::iVisitTheColumnTab() ->
 *   Base.php::visitColumnTab() -> clicks the `.column-navigation-link` whose text matches.
 * - Category pane container: Product/Edit.php `'Category pane' => '#product-categories'`.
 * - Category tree widget: Product/Edit.php `'Category tree' => ['css' => '#trees', 'decorators'
 *   => [TreeDecorator::class]]`. TreeDecorator.php confirms the real DOM contract: tree nodes
 *   are `li[role=treeitem]` (expand via a `button` inside, toggling `aria-expanded`), and each
 *   node has a `div[role=checkbox]` toggled via `aria-checked` (TreeDecorator::select()). This
 *   is the DSM/Category-front `Tree` component (role=treeitem/role=group in
 *   front-packages/akeneo-design-system/src/components/Tree/Tree.tsx and
 *   src/Akeneo/Category/front/src/feature/components/tree/base/Tree.tsx) — a React tree, but one
 *   already exercised live in critical/category.spec.ts, not a stale-route risk here since no
 *   navigation is involved, only in-page tree interaction.
 * - "I press the "Save" button" / "I should not see the text "There are unsaved changes."":
 *   the standard PEF save button + unsaved-changes banner, already used elsewhere in this
 *   suite (e.g. export/edit-export.spec.ts).
 *
 * Persistence is verified via the internal REST API (GET /enrich/product/rest/{id}) rather
 * than by re-reading the tree UI, mirroring the API-verification pattern used in
 * import/import-via-api.spec.ts — more robust than re-parsing tree checkbox state.
 */

test.describe('Classify a product', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('can associate a product to categories via the PEF', async ({page}) => {
    const ts = Date.now();
    const sku = `pw-classify-${ts}`;
    const categoryA = `pw_cat_a_${ts}`;
    const categoryB = `pw_cat_b_${ts}`;
    const labelA = `PW Category A ${ts}`;
    const labelB = `PW Category B ${ts}`;

    const rootCode = await getFirstRootCategoryCode(page);
    expect(rootCode, 'expected at least one root category tree in the catalog').toBeTruthy();

    const [catAResp, catBResp] = await Promise.all([
      createCategoryViaApi(page, categoryA, rootCode!, labelA),
      createCategoryViaApi(page, categoryB, rootCode!, labelB),
    ]);
    expect(catAResp.ok(), `Create category ${categoryA} failed: ${catAResp.status()}`).toBeTruthy();
    expect(catBResp.ok(), `Create category ${categoryB} failed: ${catBResp.status()}`).toBeTruthy();

    const productResp = await createProductViaApi(page, sku);
    expect(productResp.ok(), `Create product ${sku} failed: ${productResp.status()}`).toBeTruthy();
    // The internal_api normalizer returns the product's UUID under meta.id — the GET-by-id
    // route (pim_enrich_product_rest_get) requires this UUID, not the SKU (see product.yml).
    const createdProduct = await productResp.json();
    const productUuid = createdProduct.meta?.id;
    expect(productUuid, `Create product response had no meta.id: ${JSON.stringify(createdProduct)}`).toBeTruthy();

    await goToProductBySearch(page, sku);

    // Visit the "Categories" column tab (Base.php::visitColumnTab())
    await page.locator('.column-navigation-link').filter({hasText: 'Categories'}).click();
    await waitForLoadingMasks(page);

    const categoryTree = page.locator('#trees');
    await expect(categoryTree).toBeVisible({timeout: 15_000});

    // The 2 new categories are direct children of the root tree — expand it if collapsed
    // (TreeDecorator.php::expandNode(): click the node's inner `button` while aria-expanded=false).
    // Tree.tsx renders 2 role=button elements per node: the expand/collapse ArrowButton
    // (first in the JSX) and the LabelWithFolder select button — .first() picks the arrow.
    const rootNode = categoryTree.getByRole('treeitem').first();
    await expect(rootNode).toBeVisible({timeout: 15_000});
    if ((await rootNode.getAttribute('aria-expanded')) === 'false') {
      await rootNode.getByRole('button').first().click();
    }

    // Select both disposable categories (TreeDecorator.php::select(): click the node's
    // div[role=checkbox] while aria-checked=false).
    // getByRole('treeitem', {name}) matches the ARIA accessible name (this node's own label),
    // NOT .filter({hasText}) — tree nodes nest in the DOM (a category's <li role="treeitem"> is
    // inside its parent's), so hasText subtree-searches and also matches every ancestor up to
    // the root ("Master catalog"), which strict-mode-violates.
    for (const label of [labelA, labelB]) {
      const node = categoryTree.getByRole('treeitem', {name: label, exact: true});
      await expect(node).toBeVisible({timeout: 15_000});
      const checkbox = node.getByRole('checkbox');
      if ((await checkbox.getAttribute('aria-checked')) === 'false') {
        await checkbox.click();
      }
    }

    await page.getByRole('button', {name: 'Save', exact: true}).click();
    await waitForLoadingMasks(page);

    await expect(page.getByText('There are unsaved changes.')).not.toBeVisible({timeout: 15_000});

    // Verify persistence via the API rather than re-reading tree checkbox state.
    const product = await getProductViaApi(page, productUuid);
    const categoryCodes: string[] = product.categories ?? [];
    expect(categoryCodes).toContain(categoryA);
    expect(categoryCodes).toContain(categoryB);
  });
});
