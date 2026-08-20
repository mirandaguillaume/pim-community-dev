import {test, expect} from '../fixtures/coverage-fixture';
import {login, waitForLoadingMasks, getFirstFamilyVariantCode, createProductModelViaApi} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product-model/remove.feature:12
 *   "Successfully delete a product model from the edit form"
 *
 * The Behat scenario deletes the "amor" product model from the catalog_modeling fixture and
 * asserts its 2 variant products (SKUs 1111111111/1111111112) also disappear, plus checks
 * domain event counts. This spec creates its own disposable product model instead (via the
 * same minimal payload the "Create product model" popin itself POSTs — see
 * product-model/create-product-model.spec.ts and createProductModelViaApi in pim.ts), so it
 * doesn't depend on that fixture. It doesn't create variant products under it (that needs a
 * separate, more complex flow — see add_product_model_children.feature, a later scenario in
 * this migration); the deletion path itself doesn't care whether children exist. Event-count
 * assertions are dropped as backend-internal, not user-facing.
 *
 * Selectors traced from:
 * - "I am on the "<code>" product model page": pim_enrich_product_model_edit route,
 *   path `/enrich/product-model/{id}` (routing.yml prefix + ui/product_model.yml) — added as
 *   `'product model'` to NavigationHelper.goToEntityPage's route map.
 * - "I press the secondary action "Delete"": product model's delete action is wired through the
 *   LEGACY Backbone secondary-actions widget (form_extensions/product_model/edit.yml ->
 *   pim/product-model-edit-form/delete -> pim/form/common/delete), not the newer React
 *   SecondaryActions.tsx component used elsewhere (e.g. category edit) — confirmed by reading
 *   js/form/common/secondary-actions.js (`className: '... secondary-actions'`) and its template
 *   (templates/form/secondary-actions.html): the actual dropdown TOGGLE is the nested
 *   '.AknSecondaryActions-button.dropdown-button[data-toggle="dropdown"]', not the outer
 *   '.secondary-actions' wrapper itself (matches DropdownMenuDecorator.php::open(), which looks
 *   for '.dropdown-button' specifically). The menu renders INLINE into
 *   '[data-drop-zone="secondary-actions"]' (no portal), and the Delete item is a real
 *   `<button class="AknDropdown-menuLink delete">Delete</button>` (js/form/common/delete.js).
 *   Its confirm callback still uses Backbone.BootstrapModal (js/pim-dialog.js::confirmDelete()
 *   -> Dialog.confirm()), so the confirm-dialog step below is unaffected.
 * - "I should see the text "Confirm deletion"" / "I confirm the removal": the standard confirm
 *   dialog already used in critical/category.spec.ts (Base.php::confirmDialog():
 *   'div.modal, div[role="dialog"]' -> '.ok').
 *
 * Persistence is verified via the internal REST API (GET /enrich/product-model/rest/{id}
 * returning 404) rather than re-reading the grid/search UI — same pattern as
 * product/classify-product.spec.ts.
 */

test.describe('Remove a product model', () => {
  let nav: NavigationHelper;

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    nav = new NavigationHelper(page);
  });

  test('can delete a product model from its edit form', async ({page}) => {
    const familyVariantCode = await getFirstFamilyVariantCode(page);
    expect(familyVariantCode, 'expected at least one family variant in the catalog').toBeTruthy();

    const code = `pw_remove_pm_${Date.now()}`;
    const createResp = await createProductModelViaApi(page, code, familyVariantCode!);
    expect(createResp.ok(), `Create product model ${code} failed: ${createResp.status()}`).toBeTruthy();
    const created = await createResp.json();
    const productModelId = created.meta?.id;
    expect(productModelId, `Create response had no meta.id: ${JSON.stringify(created)}`).toBeTruthy();

    await nav.goToEntityPage('product model', productModelId);
    await expect(page.getByText(code).first()).toBeVisible({timeout: 15_000});

    // I press the secondary action "Delete"
    const secondaryActions = page.locator('.secondary-actions').first();
    await secondaryActions.locator('.dropdown-button').click();
    await secondaryActions.getByRole('button', {name: 'Delete', exact: true}).click();

    // Then I should see the text "Confirm deletion" / When I confirm the removal
    const confirmDialog = page.locator('div.modal, div[role="dialog"]');
    await expect(confirmDialog).toBeVisible({timeout: 10_000});
    await expect(confirmDialog.getByText('Confirm deletion')).toBeVisible();
    await confirmDialog.locator('.ok').click();

    // The removal redirects away from the deleted product model's edit page.
    await waitForLoadingMasks(page);
    await expect(page).not.toHaveURL(new RegExp(`#/enrich/product-model/${productModelId}$`), {timeout: 15_000});

    // Verify persistence via the API: the product model no longer exists.
    const getResp = await page.request.get(`/enrich/product-model/rest/${productModelId}`);
    expect(getResp.status()).toBe(404);
  });
});
