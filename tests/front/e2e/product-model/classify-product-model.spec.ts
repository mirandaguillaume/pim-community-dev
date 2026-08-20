import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  waitForLoadingMasks,
  getFirstFamilyVariantCode,
  createProductModelViaApi,
  getFirstRootCategoryCode,
  createCategoryViaApi,
} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product-model/classify_product_model.feature:26
 *   "Count sub product model categories"
 *
 * The Behat scenario builds a 2-level product-model hierarchy from the catalog_modeling
 * fixture (root "model-nin" categorized under "tshirts", sub "model-nin-black" additionally
 * categorized under "summer"/"spring") and asserts a badge shows the MERGED total of 3
 * (1 inherited + 2 own). Replicating that needs a 2-level family variant plus fixture-specific
 * category codes.
 *
 * The category-count badge itself is fixture-agnostic: WebUser.php::iShouldSeeCategoryCount()
 * just looks for `.AknBadge:contains("N")` — a generic counter over however many category
 * checkboxes are currently ticked in the tree, with no inheritance-specific logic of its own
 * (it doesn't distinguish "inherited" from "directly assigned"). So this spec exercises the
 * same counting UI with a flat (non-hierarchical) product model directly classified against 3
 * disposable categories — same regression protection for the counting mechanism, without the
 * 2-level fixture setup. (add_product_model_children.feature, later in this migration, covers
 * the parent/child product-model hierarchy itself.)
 *
 * Selectors traced from:
 * - "I visit the "Categories" column tab": Base.php::visitColumnTab() -> '.column-navigation-link'
 *   (see product/classify-product.spec.ts).
 * - Category tree widget: same '#trees' / TreeDecorator.php contract as
 *   product/classify-product.spec.ts (li[role=treeitem], div[role=checkbox]) — the product model
 *   edit form shares the same underlying page object as the product edit form
 *   (Context/Page/Base/ProductEditForm.php), so the same DOM contract applies.
 * - "I should see N category count": WebUser.php::iShouldSeeCategoryCount() ->
 *   `.AknBadge:contains("N")`.
 *
 * Persistence (the categories array) is verified via the internal REST API, same as
 * product/classify-product.spec.ts.
 */

test.describe('Classify a product model', () => {
  let nav: NavigationHelper;

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    nav = new NavigationHelper(page);
  });

  test('shows a category count badge matching the number of assigned categories', async ({page}) => {
    const ts = Date.now();

    const familyVariantCode = await getFirstFamilyVariantCode(page);
    expect(familyVariantCode, 'expected at least one family variant in the catalog').toBeTruthy();

    const rootCode = await getFirstRootCategoryCode(page);
    expect(rootCode, 'expected at least one root category tree in the catalog').toBeTruthy();

    const categories = [
      {code: `pw_cnt_a_${ts}`, label: `PW Count A ${ts}`},
      {code: `pw_cnt_b_${ts}`, label: `PW Count B ${ts}`},
      {code: `pw_cnt_c_${ts}`, label: `PW Count C ${ts}`},
    ];
    for (const category of categories) {
      const resp = await createCategoryViaApi(page, category.code, rootCode!, category.label);
      expect(resp.ok(), `Create category ${category.code} failed: ${resp.status()}`).toBeTruthy();
    }

    const pmCode = `pw_classify_pm_${ts}`;
    const createResp = await createProductModelViaApi(page, pmCode, familyVariantCode!);
    expect(createResp.ok(), `Create product model ${pmCode} failed: ${createResp.status()}`).toBeTruthy();
    const created = await createResp.json();
    const productModelId = created.meta?.id;
    expect(productModelId, `Create response had no meta.id: ${JSON.stringify(created)}`).toBeTruthy();

    await nav.goToEntityPage('product model', productModelId);
    await expect(page.getByText(pmCode).first()).toBeVisible({timeout: 15_000});

    // I visit the "Categories" column tab
    await page.locator('.column-navigation-link').filter({hasText: 'Categories'}).click();
    await waitForLoadingMasks(page);

    const categoryTree = page.locator('#trees');
    await expect(categoryTree).toBeVisible({timeout: 15_000});

    // Tree.tsx renders 2 role=button elements per node: the expand/collapse ArrowButton
    // (first in the JSX) and the LabelWithFolder select button — .first() picks the arrow.
    const rootNode = categoryTree.getByRole('treeitem').first();
    await expect(rootNode).toBeVisible({timeout: 15_000});
    if ((await rootNode.getAttribute('aria-expanded')) === 'false') {
      await rootNode.getByRole('button').first().click();
    }

    // getByRole('treeitem', {name}) matches the ARIA accessible name (this node's own label),
    // NOT .filter({hasText}) — tree nodes nest in the DOM (a category's <li role="treeitem"> is
    // inside its parent's), so hasText subtree-searches and also matches every ancestor up to
    // the root ("Master catalog"), which strict-mode-violates (bit product/classify-product.spec.ts).
    for (const category of categories) {
      const node = categoryTree.getByRole('treeitem', {name: category.label, exact: true});
      await expect(node).toBeVisible({timeout: 15_000});
      const checkbox = node.getByRole('checkbox');
      if ((await checkbox.getAttribute('aria-checked')) === 'false') {
        await checkbox.click();
      }
    }

    await page.getByRole('button', {name: 'Save', exact: true}).click();
    await waitForLoadingMasks(page);
    await expect(page.getByText('There are unsaved changes.')).not.toBeVisible({timeout: 15_000});

    // Then I should see 3 category count
    await expect(page.locator('.AknBadge').filter({hasText: '3'})).toBeVisible({timeout: 15_000});

    // Verify persistence via the API rather than re-reading tree checkbox state.
    const getResp = await page.request.get(`/enrich/product-model/rest/${productModelId}`);
    expect(getResp.ok()).toBeTruthy();
    const productModel = await getResp.json();
    const categoryCodes: string[] = productModel.categories ?? [];
    for (const category of categories) {
      expect(categoryCodes).toContain(category.code);
    }
  });
});
