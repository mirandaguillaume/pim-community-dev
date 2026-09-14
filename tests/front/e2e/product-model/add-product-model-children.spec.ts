import type {APIResponse, Page} from '@playwright/test';
import {test, expect} from '../fixtures/coverage-fixture';
import {login, createProductModelViaApi} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product-model/add_product_model_children.feature:93
 *   "Successfully add a variant product to a sub product model"
 *
 * Adaptations:
 * - Catalog: the Behat scenario uses the catalog_modeling "apollon" > "apollon_blue" hierarchy. CI
 *   runs icecat_demo_dev, so the test builds an equivalent disposable 3-tier tree instead:
 *   a root product model and a level-1 sub product model with color=blue, both on the
 *   icecat_demo_dev family variant "clothing_color_size". That variant has the same 2-level
 *   layout (level 1 axis color, level 2 axis size, sku among the level-2 attributes). Sources:
 *   src/Akeneo/Platform/Installer/back/src/Infrastructure/Symfony/Resources/fixtures/icecat_demo_dev/
 *   family_variants.csv:2, attribute_options.csv:87 (color "blue") and :101 (size "xl", label XL),
 *   attributes.csv (size label "Size", sku label "SKU"). Codes carry a Date.now() suffix, so a
 *   retry never collides with the axis-uniqueness rule. If the family variant or the parent is
 *   missing, the internal create endpoint throws an uncaught InvalidPropertyException (500, not a
 *   JSON 400; ProductModelUpdater::updateParent / updateFamilyVariant). The setup assertions
 *   therefore print the raw text body.
 * - Background "reference_color" attribute: dropped. Only the many-axes scenarios use it, and it
 *   needs a "color" reference-data configuration that icecat_demo_dev does not ship.
 * - "I am logged in as Julia": admin/admin, like every other spec (ROLE_ADMINISTRATOR holds
 *   pim_enrich_product_create, which the "Add new" footer checks in
 *   variant-navigation.js::isCreationGranted and ProductController::createAction requires).
 * - The level-2 dropdown opens empty ("No matches found") because the disposable sub product model
 *   has no variant children yet, unlike apollon_blue. The "Add new" footer does not depend on the
 *   results. variant-navigation.js::addSelect2Footer appends it to #select2-drop once the
 *   product-model-by-code / family-variant fetches and the ACL check resolve.
 * - Field order: Size is picked BEFORE the SKU is typed, the reverse of the Behat table.
 *   simple-select-async.js:69-73 onChange calls getRoot().render(), which rebuilds every field of
 *   the modal asynchronously (fields-container.js:44-113). Each field is also rendered twice per
 *   build: renderExtensions() and then showErrors(), whose bad_request event makes every field
 *   render again (fields-container.js:107-108, field.js:51,60). A SKU typed first could therefore
 *   land in a detached input. The sync point is the REBUILT Size field repainting "XL" in its
 *   .select2-chosen. That only happens once its initSelection ajax returns
 *   (simple-select-async.js:167-185), after both renders have run. The order is irrelevant to the
 *   payload the backend receives.
 * - Persistence check (not in Behat): the created product is re-read through
 *   GET /enrich/product/rest/{uuid} and must have the expected identifier, family, parent and size.
 * - No domain event assertion is dropped: unlike feature:79, this scenario has no
 *   "@purge-messenger" tag and no "event ... should have been raised" step.
 *
 * Selectors traced from:
 * - "I am on the "apollon_blue" product model page": NavigationHelper.goToEntityPage('product model',
 *   id) -> #/enrich/product-model/{id}. Readiness: navigation.html:16-18 renders one hidden
 *   `input.variant-navigation.select-field` per level whose parent level has a selected entity.
 *   For a level-1 sub product model that is levels 1 and 2 (VariantNavigationNormalizer), so exactly
 *   2 Select2 containers. Select2 3.4 copies the element's non-select2 classes onto its container
 *   (select2.js:709, identity adaptContainerCssClass), giving `.variant-navigation.select2-container`.
 *   That is the selector VariantNavigationDecorator::getChildrenSelectorForLevel uses.
 * - "I open the variant navigation children selector for level 2": the decorator takes container
 *   index level - 1 = 1, then Select2Decorator::open() clicks `.select2-arrow` unless the container
 *   already has `select2-dropdown-open`. variant-navigation.js:36-37 re-renders the bar on every
 *   post_fetch, which can swallow an early opening. So the open-and-wait is wrapped in toPass, and
 *   the arrow is clicked only when the container is not already open.
 * - "I press the "Add new" button and wait for modal": add-child-button.html renders
 *   `<button class="AknButton AknButton--apply add-child">`, labelled
 *   pim_enrich.entity.product_model.module.variant_axis.create = "Add new" (Enrichment
 *   jsmessages.en_US.yml:182). The button is scoped to #select2-drop, the id Select2 gives only to
 *   the active dropdown (select2.js:1364-1365,1419). The click opens a modal with className
 *   'modal modal--fullPage add-product-model-child' (variant-navigation.js:215). That class also
 *   keeps the hidden Summernote `.note-*-dialog.modal` elements out of the match (see
 *   remove-product-model.spec.ts).
 * - "I should see the text "Add a new Size"": header.html `.AknFullPage-title` with
 *   title_create_label "Add a new {{ axes }}" (jsmessages.en_US.yml:183). The axes are those of
 *   level parent.meta.level + 1 (header.js:16-41).
 * - "Size (variant axis)" field: field.html renders `<label class="AknFieldContainer-label">Size
 *   <em>(variant axis)</em></label>` (requiredLabel from jsmessages.en_US.yml:184) inside
 *   field.js's `.AknFieldContainer`. The control is a hidden `<input class="select2 select-field">`
 *   (simple-select-async.html), and Select2 rewrites the label's for= to its offscreen focusser
 *   (select2.js:2004). getByLabel() would therefore resolve to that focusser, so the field is
 *   located through its label text and driven through `.select2-choice`. Results render in
 *   #select2-drop as `.select2-result-selectable` and are selected on mouseup (select2.js:776-784).
 *   The drop (z-index 9999, select2.css) sits above the full-page modal (z-index 1040,
 *   FullPage.less), so a real click reaches it, the same way pim.ts's addAttributeToMassEdit does.
 *   Choices come from pim_enrich_attributeoption_get (9 size options, under the 20 per page), so no
 *   search term is typed.
 * - "SKU" field: the identifier attribute is mapped to akeneo-text-field (BaseFieldProvider) and
 *   rendered by values/text with no required marker. field.js:145-167 computes one fieldId per
 *   render for both `<label for>` and the text.html `<input id>`, so getByLabel('SKU') resolves the
 *   input. text.js updates the model on the `input` event, which fill() dispatches.
 * - "I confirm the child creation": Base.php::confirmDialog clicks the first `.ok` of the modal,
 *   which is the inner "Save" button (add-child-form.html:9, rendered inside `.modal-body`). The
 *   modal's outer "Confirm" button (bootstrap-modal.js template, after `.modal-body`) sits under
 *   the nested absolute `.AknFullPage` (FullPage.less:3-16), so it cannot receive the click.
 *   `.modal-body .ok` is therefore the only `.ok` a user can click. Both reach the same handler:
 *   form-modal.js:90-105 -> variant-navigation.js::submitForm -> add-child.js:44-50 POST
 *   pim_enrich_product_rest_create = /enrich/product/rest (compiled route dump, no trailing slash).
 * - "I should be on the product "apollon_blue_xl" edit page": variant-navigation.js:426-439
 *   redirects to pim_enrich_product_edit {uuid}. The uuid is read from the POST response meta.uuid
 *   (InternalApi ProductNormalizer). router.redirectToRoute changes the hash without {trigger: true},
 *   so the URL assertion passes before the product page exists. The real sync is the title drop
 *   zone (default-template.html:22) showing the new SKU. The product label falls back to the
 *   identifier because family clothing's attribute_as_label (variation_name) is left empty on
 *   purpose (product-label.js:29, AbstractProduct::getLabel).
 */

// icecat_demo_dev family_variants.csv:2 (clothing: level 1 axis color, level 2 axis size + sku).
const FAMILY_VARIANT = 'clothing_color_size';

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};
const XHR_JSON_HEADERS = {'Content-Type': 'application/json', ...XHR_HEADER};

async function describeResponse(response: APIResponse): Promise<string> {
  return `${response.status()} ${await response.text().catch(() => '')}`;
}

/**
 * Create a level-1 sub product model under an existing root product model, via the same internal
 * endpoint and payload shape the add-child modal itself POSTs for a sub product model
 * (variant-navigation.js:223-231 initial state {parent, values, family_variant} + values-behavior.js
 * [{scope: null, locale: null, data}] + the "code" field). ProductModelUpdater::update
 * (Component/Product/Updater/ProductModelUpdater.php:35-55) sets the parent first
 * (updateParentAndFamily / updateParent:183-230, which requires a root parent of the same family
 * variant), then code, family_variant and values.
 */
async function createSubProductModelViaApi(
  page: Page,
  code: string,
  parentCode: string,
  familyVariantCode: string,
  axisValues: Record<string, string>
): Promise<APIResponse> {
  const values: Record<string, Array<{locale: null; scope: null; data: string}>> = {};
  for (const [attributeCode, optionCode] of Object.entries(axisValues)) {
    values[attributeCode] = [{locale: null, scope: null, data: optionCode}];
  }

  return page.request.post('/enrich/product-model/rest/create', {
    data: {code, parent: parentCode, family_variant: familyVariantCode, values},
    headers: XHR_JSON_HEADERS,
  });
}

test.describe('Add children to a product model', () => {
  let nav: NavigationHelper;

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    nav = new NavigationHelper(page);
  });

  test('adds a variant product to a sub product model from the variant navigation', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_root_${ts}`;
    const subProductModelCode = `pw_addchild_blue_${ts}`;
    const sku = `pw_addchild_blue_xl_${ts}`;

    // Setup: root product model -> sub product model (color=blue), created sequentially because the
    // sub product model needs its parent to exist.
    const rootResp = await createProductModelViaApi(page, rootCode, FAMILY_VARIANT);
    expect(
      rootResp.ok(),
      `Create root product model ${rootCode} failed: ${await describeResponse(rootResp)}`
    ).toBeTruthy();

    const subResp = await createSubProductModelViaApi(page, subProductModelCode, rootCode, FAMILY_VARIANT, {
      color: 'blue',
    });
    expect(
      subResp.ok(),
      `Create sub product model ${subProductModelCode} failed: ${await describeResponse(subResp)}`
    ).toBeTruthy();
    const subBody = await subResp.json();
    expect(subBody?.meta?.level, `Sub product model is not at variation level 1: ${JSON.stringify(subBody)}`).toBe(1);
    const subProductModelId = subBody?.meta?.id;
    expect(subProductModelId, `Create response had no meta.id: ${JSON.stringify(subBody)}`).toBeTruthy();

    // Given I am on the "apollon_blue" product model page
    await nav.goToEntityPage('product model', String(subProductModelId));
    const childSelectors = page.locator('.variant-navigation.select2-container');
    await expect(childSelectors).toHaveCount(2, {timeout: 30_000});

    // When I open the variant navigation children selector for level 2
    // And I press the "Add new" button and wait for modal
    const levelTwoSelector = childSelectors.nth(1);
    const dropdown = page.locator('#select2-drop');
    const addNewButton = dropdown.getByRole('button', {name: 'Add new', exact: true});
    await expect(async () => {
      const isOpen = await levelTwoSelector.evaluate(element => element.classList.contains('select2-dropdown-open'));
      if (!isOpen) {
        await levelTwoSelector.locator('.select2-arrow').click({timeout: 5_000});
      }
      await expect(dropdown).toBeVisible({timeout: 5_000});
      await expect(addNewButton).toBeVisible({timeout: 10_000});
    }).toPass({timeout: 60_000});
    await addNewButton.click();

    const modal = page.locator('.modal.add-product-model-child');
    await expect(modal).toBeVisible({timeout: 15_000});

    // Then I should see the text "Add a new Size"
    await expect(modal.getByText('Add a new Size', {exact: true})).toBeVisible({timeout: 15_000});
    // The modal offers the Size axis field and the SKU field (Behat's fillField spin enforced this).
    const sizeLabel = modal.locator('.AknFieldContainer-label', {hasText: 'Size (variant axis)'});
    await expect(sizeLabel).toBeVisible({timeout: 15_000});
    const skuInput = modal.getByLabel('SKU', {exact: true});
    await expect(skuInput).toBeVisible({timeout: 15_000});

    // When I fill in the following child information: | Size (variant axis) | XL |
    const sizeField = modal
      .locator('.AknFieldContainer')
      .filter({has: page.locator('.AknFieldContainer-label', {hasText: 'Size (variant axis)'})});
    await sizeField.locator('.select2-choice').click();
    const xlOption = dropdown.locator('.select2-result-selectable').filter({hasText: /^\s*XL\s*$/});
    await expect(xlOption).toBeVisible({timeout: 15_000});
    await xlOption.click();
    // The change re-renders the whole modal form: wait for the REBUILT field to show the value.
    await expect(sizeField.locator('.select2-chosen')).toHaveText('XL', {timeout: 15_000});

    // When I fill in the following child information: | SKU | apollon_blue_xl |
    await skuInput.fill(sku);
    await expect(skuInput).toHaveValue(sku);

    // And I confirm the child creation
    const createRespPromise = page.waitForResponse(
      response => /\/enrich\/product\/rest(\?|$)/.test(response.url()) && response.request().method() === 'POST',
      {timeout: 30_000}
    );
    await modal.locator('.modal-body .ok').click();
    const createResp = await createRespPromise;
    const createText = await createResp.text().catch(() => '');
    expect(createResp.ok(), `Create variant product failed: ${createResp.status()} ${createText}`).toBeTruthy();
    let createBody: any = null;
    try {
      createBody = JSON.parse(createText);
    } catch (error) {
      createBody = null;
    }
    const uuid: string | undefined = createBody?.meta?.uuid;
    expect(uuid, `Create response had no meta.uuid: ${createText}`).toBeTruthy();
    await expect(modal).toBeHidden({timeout: 15_000});

    // Then I should be on the product "apollon_blue_xl" edit page
    await expect(page).toHaveURL(new RegExp(`#/enrich/product/${uuid}$`), {timeout: 30_000});
    await expect(page.locator('.AknTitleContainer-title').filter({hasText: sku})).toBeVisible({timeout: 30_000});

    // Persistence: the variant product exists under the sub product model with the chosen axis value.
    const productResp = await page.request.get(`/enrich/product/rest/${uuid}`, {headers: XHR_HEADER});
    const productText = await productResp.text().catch(() => '');
    expect(productResp.ok(), `Get product ${uuid} failed: ${productResp.status()} ${productText}`).toBeTruthy();
    const product = JSON.parse(productText);
    expect(product.identifier, productText).toBe(sku);
    expect(product.family, productText).toBe('clothing');
    expect(product.parent, productText).toBe(subProductModelCode);
    expect(String(product.values?.size?.[0]?.data ?? '').toLowerCase(), JSON.stringify(product.values?.size)).toBe(
      'xl'
    );
  });
});
