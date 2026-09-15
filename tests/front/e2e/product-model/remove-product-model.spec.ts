import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  waitForLoadingMasks,
  createProductModelViaApi,
  createProductViaApi,
  deleteProductModelViaApi,
  responseBody,
  searchProductGrid,
  XHR_HEADER,
} from '../fixtures/pim';
import {DataGridPage} from '../pages/DataGridPage';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product-model/remove.feature:12
 *   "Successfully delete a product model from the edit form"
 *
 * Adaptations:
 * - Catalog: Behat deletes "amor" from catalog_modeling, a root product model whose variant products
 *   are 1111111111 and 1111111112. CI runs icecat_demo_dev, so the test builds its own disposable 3-tier
 *   tree on the family variant "clothing_color_size" (level 1 axis color, level 2 axis size, sku at
 *   level 2; icecat_demo_dev family_variants.csv:2): a root product model, a sub product model with
 *   color=blue (attribute_options.csv:87) and a variant product with size=xl (attribute_options.csv:101).
 *   The tree is created sequentially, because each child needs its parent. The codes share a
 *   Date.now() prefix, so retries cannot collide. The root is deleted, as "amor" is.
 * - Persona: Julia is kept (icecat_demo_dev users.csv:3, ROLE_CATALOG_MANAGER). The Delete action is
 *   ACL-gated in the UI (form_extensions/product_model/edit.yml: aclResourceId
 *   pim_enrich_product_model_remove) and in the backend (@AclAncestor on
 *   ProductModelController::removeAction). Fixture roles get every privilege by default
 *   (AddDefaultPrivilegesSubscriber), and acl.yml does not opt this one out.
 * - Where the delete lands: form_extensions/product_model/edit.yml sets
 *   `config.redirect: pim_enrich_product_index` on pim-product-model-edit-form-delete. It overrides the
 *   `redirect: 'oro_default'` default of js/form/common/delete.js (initialize() merges meta.config over
 *   the defaults with _.extend), and doDelete() calls router.redirectToRoute(this.config.redirect). The
 *   route is `/enrich/product/` in fos_js_routes.json, the product grid. router.redirect() navigates
 *   without {trigger: true}, but Backbone 0.9.10 still routes it: navigate() stores the fragment with
 *   only the '#' stripped ('/enrich/product/'), checkUrl() on hashchange computes 'enrich/product/', and
 *   the mismatch makes it call loadUrl(). The grid therefore renders after the URL has changed; the test
 *   syncs on the grid data response, not on the URL.
 * - "I should not see product 1111111111 / 1111111112" ran on that grid, in its default unfiltered view.
 *   With no filter the grid only lists documents without a parent
 *   (ProductAndProductModelQueryBuilder::shouldSearchDocumentsWithoutParent), so a variant product never
 *   shows there, deleted or not. The test makes the check meaningful. The grid search box is the
 *   label_or_identifier filter, and with it the grid lists every matching document, root, sub product
 *   model and variant product alike (no parent restriction, no aggregation:
 *   shouldAggregateResults). Searching the shared code prefix must list all 3 rows before the delete,
 *   and none on the grid the delete lands on. removeAction refreshes the index before answering, so no
 *   retry is needed after the delete.
 * - The database side is checked through the internal API: after the delete, GET on the root and the sub
 *   product model (/enrich/product-model/rest/{id}) and on the variant product
 *   (/enrich/product/rest/{uuid}) answer 404. Before it, the sub product model and the variant (with its
 *   parent) answer 200, so the 404s cannot pass on a broken setup. The children go through the database
 *   cascade (ProductModel::$parent and AbstractProduct::$parent are ON DELETE CASCADE).
 * - "1 event of type product_model.removed" / "0 event of type product.removed" are backend-internal. They
 *   are guarded in PHPUnit on the same remover and on the same endpoint:
 *   tests/back/Pim/Enrichment/Integration/Product/RemoveProductModelIntegration.php and
 *   tests/back/Pim/Enrichment/Integration/Controller/RemoveProductModelControllerIntegration.php. The
 *   latter also covers removeAction's database, index and non-XHR behaviour on backend-only changes, which
 *   this spec does not run on.
 *
 * Selectors traced from:
 * - "I am on the "amor" product model page": pim_enrich_product_model_edit, `#/enrich/product-model/{id}`
 *   (NavigationHelper.goToEntityPage('product model', id)).
 * - "I press the secondary action "Delete"": the legacy Backbone secondary-actions widget
 *   (js/form/common/secondary-actions.js, templates/form/secondary-actions.html). The toggle is the nested
 *   '.dropdown-button' (DropdownMenuDecorator.php::open()), the menu renders inline, and the Delete item
 *   is a real `<button class="AknDropdown-menuLink delete">` (js/form/common/delete.js).
 * - "I should see the text "Confirm deletion"" / "I confirm the removal": Dialog.confirmDelete()
 *   (js/pim-dialog.js) builds a Backbone.BootstrapModal with the 'modal--fullPage' class, and its OK
 *   control is a `<div class="... ok">` with no button role. The '.modal--fullPage' scoping matters: the
 *   clothing edit form renders Summernote dialogs that also carry the plain 'modal' class.
 * - Product grid: the "Products" menu item (pim.ts goToProductsGrid), the search box
 *   '.search-filter input[name="value"]' (searchProductGrid), rows 'tr.AknGrid-bodyRow'
 *   (datagrid/grid.js rowClassName) and the empty state '.no-data' (datagrid/grid.js noDataBlock,
 *   DataGridPage.expectRowCount).
 */

// icecat_demo_dev family_variants.csv:2 (family clothing: level 1 axis color, level 2 axis size + sku).
const FAMILY_VARIANT = 'clothing_color_size';
const FAMILY = 'clothing';

const isProductGridData = (url: string) => url.includes('/datagrid/product-grid') && !url.includes('/datagrid_view/');

test.describe('Remove a product model', () => {
  let nav: NavigationHelper;
  // Set while the disposable tree exists, so afterEach can delete it when the test fails before its own delete.
  let rootIdToCleanUp: number | undefined;

  test.beforeEach(async ({page}) => {
    rootIdToCleanUp = undefined;
    await login(page, 'julia', 'julia');
    nav = new NavigationHelper(page);
  });

  test.afterEach(async ({page}) => {
    if (rootIdToCleanUp === undefined) return;
    const rootId = rootIdToCleanUp;
    rootIdToCleanUp = undefined;
    // Soft and non-throwing: a failed cleanup is reported without hiding the test's own error. The database
    // cascade removes the sub product model and the variant product with the root.
    try {
      const resp = await deleteProductModelViaApi(page, rootId);
      expect
        .soft(resp.ok(), `cleanup: delete product model ${rootId}: ${resp.status()} ${await responseBody(resp)}`)
        .toBeTruthy();
    } catch (error) {
      expect.soft(false, `cleanup: delete product model ${rootId} threw: ${error}`).toBeTruthy();
    }
  });

  test('deletes a root product model and its descendants from its edit form, then lands on the product grid', async ({
    page,
  }) => {
    const prefix = `pw_rmpm_${Date.now()}_`;
    const rootCode = `${prefix}root`;
    const subCode = `${prefix}blue`;
    const sku = `${prefix}blue_xl`;

    // --- Setup: root -> sub product model (color=blue) -> variant product (size=xl), strictly sequential ---
    // A bad parent or family variant is an uncaught 500 with a non-JSON body: print the text body.
    const rootResp = await createProductModelViaApi(page, rootCode, FAMILY_VARIANT);
    expect(
      rootResp.ok(),
      `Create root product model ${rootCode} failed: ${rootResp.status()} ${await responseBody(rootResp)}`
    ).toBeTruthy();
    const rootBody = await rootResp.json();
    const rootId: number = rootBody?.meta?.id;
    expect(rootId, `Create root product model response had no meta.id: ${JSON.stringify(rootBody)}`).toBeTruthy();
    rootIdToCleanUp = rootId;

    const subResp = await createProductModelViaApi(
      page,
      subCode,
      FAMILY_VARIANT,
      {color: [{locale: null, scope: null, data: 'blue'}]},
      rootCode
    );
    expect(
      subResp.ok(),
      `Create sub product model ${subCode} failed: ${subResp.status()} ${await responseBody(subResp)}`
    ).toBeTruthy();
    const subBody = await subResp.json();
    expect(subBody?.meta?.level, `Sub product model is not at variation level 1: ${JSON.stringify(subBody)}`).toBe(1);
    const subId: number = subBody?.meta?.id;
    expect(subId, `Create sub product model response had no meta.id: ${JSON.stringify(subBody)}`).toBeTruthy();

    const variantResp = await createProductViaApi(page, sku, FAMILY, {
      parent: subCode,
      values: {size: [{locale: null, scope: null, data: 'xl'}]},
    });
    expect(
      variantResp.ok(),
      `Create variant product ${sku} failed: ${variantResp.status()} ${await responseBody(variantResp)}`
    ).toBeTruthy();
    const variantBody = await variantResp.json();
    const variantUuid: string = variantBody?.meta?.uuid;
    expect(
      variantUuid,
      `Create variant product response had no meta.uuid: ${JSON.stringify(variantBody)}`
    ).toBeTruthy();

    // --- Positive controls: the children exist and are wired, so the 404s at the end prove the delete ---
    const subBefore = await page.request.get(`/enrich/product-model/rest/${subId}`, {
      headers: XHR_HEADER,
      timeout: 15_000,
    });
    expect(
      subBefore.status(),
      `GET sub product model ${subId} before the delete: ${await responseBody(subBefore)}`
    ).toBe(200);
    const variantBefore = await page.request.get(`/enrich/product/rest/${variantUuid}`, {
      headers: XHR_HEADER,
      timeout: 15_000,
    });
    expect(
      variantBefore.status(),
      `GET variant product ${variantUuid} before the delete: ${await responseBody(variantBefore)}`
    ).toBe(200);
    const variantBeforeBody = await variantBefore.json();
    expect(variantBeforeBody?.parent, `Variant product parent: ${JSON.stringify(variantBeforeBody?.parent)}`).toBe(
      subCode
    );

    // The product grid search lists the 3 documents of the tree. Both terms match all 3 codes; alternating
    // them forces a new grid request on each attempt while Elasticsearch catches up (see searchProductGrid).
    const searchTerms = [prefix, prefix.slice(0, -1)];
    const treeRows = page.locator('tr.AknGrid-bodyRow').filter({hasText: prefix});
    await page.getByRole('menuitem', {name: 'Activity'}).first().waitFor({timeout: 30_000});
    const gridOpened = page.waitForResponse(resp => isProductGridData(resp.url()), {timeout: 60_000});
    gridOpened.catch(() => {});
    await page.getByRole('menuitem', {name: 'Products'}).click({timeout: 15_000});
    await gridOpened;
    let searchAttempt = 0;
    await expect(async () => {
      await searchProductGrid(page, searchTerms[searchAttempt++ % searchTerms.length]);
      await expect(
        treeRows,
        'the product grid search does not list the root, the sub product model and the variant'
      ).toHaveCount(3, {
        timeout: 5_000,
      });
    }).toPass({timeout: 120_000});

    // --- Given I am on the root product model page ---
    await nav.goToEntityPage('product model', String(rootId));
    await expect(page.getByText(rootCode).first()).toBeVisible({timeout: 15_000});

    // --- And I press the secondary action "Delete" ---
    const secondaryActions = page.locator('.secondary-actions').first();
    await secondaryActions.locator('.dropdown-button').click({timeout: 15_000});
    await secondaryActions.getByRole('button', {name: 'Delete', exact: true}).click({timeout: 10_000});

    // --- Then I should see the text "Confirm deletion" / When I confirm the removal ---
    const confirmDialog = page.locator('div.modal--fullPage').filter({hasText: 'Confirm deletion'});
    await expect(confirmDialog).toBeVisible({timeout: 10_000});

    // Listen before clicking OK: the DELETE answer and the grid the redirect renders can both be fast.
    const deleteResponse = page.waitForResponse(
      resp => resp.request().method() === 'DELETE' && resp.url().endsWith(`/enrich/product-model/rest/${rootId}`),
      {timeout: 30_000}
    );
    deleteResponse.catch(() => {});
    const landingGridData = page.waitForResponse(resp => isProductGridData(resp.url()), {timeout: 60_000});
    landingGridData.catch(() => {});

    await confirmDialog.locator('.ok').click({timeout: 10_000});
    const deleted = await deleteResponse;
    expect(
      deleted.ok(),
      `DELETE product model ${rootId} failed: ${deleted.status()} ${await responseBody(deleted)}`
    ).toBeTruthy();
    rootIdToCleanUp = undefined;
    await expect(confirmDialog).toHaveCount(0, {timeout: 10_000});

    // --- The delete lands on the product grid (pim_enrich_product_index, `/enrich/product/`) ---
    await expect(page).toHaveURL(/#\/enrich\/product\/$/, {timeout: 15_000});
    await landingGridData;
    await waitForLoadingMasks(page);
    await new DataGridPage(page).waitForGridLoaded();

    // --- Then I should not see product <variant> (nor the root or the sub product model) ---
    // The grid restores its last search from sessionStorage, and re-submitting an unchanged term sends nothing.
    // Searching both terms in turn guarantees that the last one sends a request, answered after the delete.
    await searchProductGrid(page, searchTerms[0]);
    await searchProductGrid(page, searchTerms[1]);
    await expect(page.locator('.search-filter input[name="value"]')).toHaveValue(searchTerms[1], {timeout: 5_000});
    await expect(treeRows, 'the product grid still lists a document of the deleted tree').toHaveCount(0, {
      timeout: 10_000,
    });
    await expect(
      page.locator('.grid-container .no-data'),
      'the product grid search should come back empty'
    ).toBeVisible({
      timeout: 15_000,
    });

    // --- Database: the root, the sub product model and the variant product are gone ---
    for (const [label, url] of [
      [`root product model ${rootId}`, `/enrich/product-model/rest/${rootId}`],
      [`sub product model ${subId}`, `/enrich/product-model/rest/${subId}`],
      [`variant product ${variantUuid}`, `/enrich/product/rest/${variantUuid}`],
    ]) {
      const resp = await page.request.get(url, {headers: XHR_HEADER, timeout: 15_000});
      expect(
        resp.status(),
        `GET ${label} after the delete should be 404: ${resp.status()} ${await responseBody(resp)}`
      ).toBe(404);
    }
  });
});
