import type {Request} from '@playwright/test';
import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  goToProductBySearch,
  createProductViaApi,
  deleteProductViaApi,
  getProductViaApi,
  createCategoryViaApi,
  cleanUpCategoriesViaApi,
  openCategoriesTab,
  showCategoryTree,
  responseBody,
} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product/pef/classify/classify_product.feature:16
 *   "Associate a product to categories"
 *
 * Behat steps:
 *   Given I edit the "tea" product
 *   When I visit the "Categories" column tab
 *   And I visit the "2014 collection" tab
 *   And I expand the "2014 collection" category
 *   And I click on the "Summer collection" category
 *   And I click on the "Winter collection" category
 *   And I press the "Save" button
 *   Then I should not see the text "There are unsaved changes."
 *   And the categories of the product "tea" should be "summer_collection and winter_collection"
 *   And 1 event of type "product.updated" should have been raised
 *
 * Adaptations:
 * - Catalog: CI runs icecat_demo_dev, not footwear. Summer and Winter collection become the disposable categories
 *   `pw_cls_a_<ts>` under the root tree `master` and `pw_cls_b_<ts>` under the root tree `sales`
 *   (icecat_demo_dev/categories.csv). Both roots are hard-coded: GET /enrich/category/rest lists roots in no
 *   guaranteed order, and root trees created through the API are not reliably shown in the edit form.
 * - Trees: footwear has a single root, so Behat's "2014 collection" tab click never switched trees. Here the second
 *   category sits in another tree, so the spec switches between two trees for real. It checks that each tree's
 *   badge counts its own selection, that the first tree keeps its ticks after switching back, and that the save
 *   sends the categories of both trees (categories.js updateModel).
 * - The product.updated event count cannot be observed from the browser. It is split in two: the spec checks that
 *   one Save click sends exactly one product update request, and UpdateProductCategoriesControllerIntegration
 *   (PHPUnit, run on backend-only PRs too) checks that one such request raises exactly one ProductUpdated event and
 *   stores the exact categories.
 * - User: admin instead of Julia. The scenario asserts no permission behaviour.
 * - Kept from Behat: the exact category set (FixturesContext.php compares the sorted lists), checked in the save
 *   response and in an independent API re-read. The product is created with no categories.
 *
 * Selectors and flow traced from:
 * - "I visit the "Categories" column tab": Base.php::visitColumnTab() -> '.column-navigation-link'. The tab loads
 *   GET /enrich/product/rest/{uuid}/categories (openCategoriesTab), which lists the trees with their ids.
 * - "I visit the "2014 collection" tab" / "I expand ... category": catalog-switcher.html
 *   `#trees-list li[data-tree=<code>]`, categories.js changeTree -> TreeAssociate.switchTree, root expanded with its
 *   arrow (showCategoryTree). Each tree renders into `#tree-<id>` (categories.html).
 * - "I click on the ... category": the DSM Tree node (role=treeitem, name = the label) and its DSM Checkbox
 *   (role=checkbox, aria-checked). getByRole('treeitem', {name}) is used rather than filter({hasText}): nodes nest,
 *   so hasText also matches every ancestor.
 * - Badges: the `.AknBadge` of each tree tab, the number of selected categories in that tree.
 * - "I press the "Save" button": the PEF Save button, which POSTs /enrich/product/rest/{uuid}
 *   (pim_enrich_product_rest_post, UpdateProductController, answering the normalized product).
 */

// icecat_demo_dev root trees (categories.csv: `master;;Master catalog` and `sales;;Sales catalog`).
const FIRST_TREE = 'master';
const SECOND_TREE = 'sales';

test.describe('Classify a product', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('can associate a product to categories of two trees via the PEF', async ({page}) => {
    const ts = Date.now();
    const sku = `pw-classify-${ts}`;
    const categoryA = {code: `pw_cls_a_${ts}`, label: `PW Classify A ${ts}`, tree: FIRST_TREE};
    const categoryB = {code: `pw_cls_b_${ts}`, label: `PW Classify B ${ts}`, tree: SECOND_TREE};
    const expectedCodes = [categoryA.code, categoryB.code].sort();
    const createdCategoryCodes: string[] = [];
    let productUuid: string | undefined;

    try {
      // One at a time: entities created concurrently can race on a shared parent.
      for (const category of [categoryA, categoryB]) {
        createdCategoryCodes.push(category.code);
        const resp = await createCategoryViaApi(page, category.code, category.tree, category.label);
        expect(
          resp.status(),
          `Create category ${category.code} failed: ${resp.status()} ${await responseBody(resp)}`
        ).toBe(201);
      }

      const productResp = await createProductViaApi(page, sku);
      const productText = await responseBody(productResp);
      expect(productResp.ok(), `Create product ${sku} failed: ${productResp.status()} ${productText}`).toBe(true);
      // The internal_api normalizer returns the product UUID under meta.id: the product routes need it, not the SKU.
      productUuid = JSON.parse(productText)?.meta?.id;
      expect(productUuid, `Create product ${sku}: no meta.id in ${productText}`).toBeTruthy();
      const uuid = productUuid!;

      // Given I edit the "tea" product
      await goToProductBySearch(page, sku);

      // When I visit the "Categories" column tab
      const listing = await openCategoriesTab(page, `/enrich/product/rest/${uuid}/categories`);
      const treeIdOf = (code: string): number => {
        const tree = listing.trees.find(t => t.code === code);
        expect(tree, `no ${code} tree in the Categories tab listing: ${JSON.stringify(listing)}`).toBeDefined();
        return tree!.id;
      };
      const firstTreeId = treeIdOf(FIRST_TREE);
      const secondTreeId = treeIdOf(SECOND_TREE);
      const badgeOf = (treeCode: string) => page.locator(`#trees-list li[data-tree="${treeCode}"] .AknBadge`);

      // And I visit the "2014 collection" tab / And I expand the "2014 collection" category
      const firstTree = await showCategoryTree(page, FIRST_TREE, firstTreeId);
      // And I click on the "Summer collection" category
      const checkboxA = firstTree.getByRole('treeitem', {name: categoryA.label, exact: true}).getByRole('checkbox');
      await expect(checkboxA, `${categoryA.code} must start unticked`).toHaveAttribute('aria-checked', 'false', {
        timeout: 30_000,
      });
      await checkboxA.click({timeout: 15_000});
      await expect(checkboxA).toHaveAttribute('aria-checked', 'true', {timeout: 15_000});
      await expect(badgeOf(FIRST_TREE)).toHaveText('1', {timeout: 15_000});

      // And I click on the "Winter collection" category: in the other tree here, so switch trees first.
      const secondTree = await showCategoryTree(page, SECOND_TREE, secondTreeId);
      const checkboxB = secondTree.getByRole('treeitem', {name: categoryB.label, exact: true}).getByRole('checkbox');
      await expect(checkboxB, `${categoryB.code} must start unticked`).toHaveAttribute('aria-checked', 'false', {
        timeout: 30_000,
      });
      await checkboxB.click({timeout: 15_000});
      await expect(checkboxB).toHaveAttribute('aria-checked', 'true', {timeout: 15_000});
      await expect(badgeOf(SECOND_TREE)).toHaveText('1', {timeout: 15_000});
      await expect(badgeOf(FIRST_TREE), 'switching trees must keep the first tree count').toHaveText('1', {
        timeout: 15_000,
      });

      // Back to the first tree: its tick is still there (switchTree does not re-render a rendered tree).
      const firstTreeAgain = await showCategoryTree(page, FIRST_TREE, firstTreeId);
      await expect(
        firstTreeAgain.getByRole('treeitem', {name: categoryA.label, exact: true}).getByRole('checkbox'),
        `${categoryA.code} lost its tick after switching trees`
      ).toHaveAttribute('aria-checked', 'true', {timeout: 15_000});

      // And I press the "Save" button
      const productPath = `/enrich/product/rest/${uuid}`;
      const isSaveRequest = (r: Request) => r.method() === 'POST' && new URL(r.url()).pathname === productPath;
      let saveRequests = 0;
      const countSaveRequest = (r: Request) => {
        if (isSaveRequest(r)) saveRequests++;
      };
      page.on('request', countSaveRequest);
      const [saveResp] = await Promise.all([
        page.waitForResponse(r => isSaveRequest(r.request()), {timeout: 60_000}),
        page.getByRole('button', {name: 'Save', exact: true}).click({timeout: 15_000}),
      ]);
      const saveText = await responseBody(saveResp);
      expect(saveResp.status(), `Save product ${sku} failed: ${saveResp.status()} ${saveText}`).toBe(200);
      const saved = JSON.parse(saveText);
      expect([...(saved?.categories ?? [])].sort(), `Save response categories: ${saveText}`).toEqual(expectedCodes);

      // Then I should not see the text "There are unsaved changes."
      await expect(page.getByText('There are unsaved changes.')).toBeHidden({timeout: 15_000});

      // And 1 event of type "product.updated" should have been raised: the browser half, one update request per
      // Save click. UpdateProductCategoriesControllerIntegration checks one event per request.
      page.off('request', countSaveRequest);
      expect(saveRequests, 'one Save click must send exactly one product update request').toBe(1);

      // And the categories of the product "tea" should be "summer_collection and winter_collection"
      const product = await getProductViaApi(page, uuid);
      expect([...(product.categories ?? [])].sort(), `Persisted categories: ${JSON.stringify(product)}`).toEqual(
        expectedCodes
      );
    } finally {
      // Best-effort cleanup that never throws, so it cannot mask the test's own error. The product goes first, as
      // it is classified in the categories.
      if (productUuid) {
        try {
          const resp = await deleteProductViaApi(page, productUuid);
          if (!resp.ok()) {
            console.warn(`Cleanup: delete product ${sku} returned ${resp.status()} ${await responseBody(resp)}`);
          }
        } catch (e) {
          console.warn(`Cleanup: product ${sku}: ${(e as Error).message}`);
        }
      }
      await cleanUpCategoriesViaApi(page, createdCategoryCodes);
    }
  });
});
