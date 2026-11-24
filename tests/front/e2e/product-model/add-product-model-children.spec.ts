import type {APIResponse, Locator, Page, Response as PageResponse} from '@playwright/test';
import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  createAttributeViaApi,
  createFamilyViaApi,
  createFamilyVariantViaApi,
  createProductModelViaApi,
  createProductViaApi,
  deleteAttributeViaApi,
  deleteFamilyViaApi,
  deleteFamilyVariantViaApi,
  deleteProductViaApi,
  deleteProductModelViaApi,
  getProductModelChildrenViaApi,
  XHR_HEADER,
} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * This spec replaces the whole of tests/legacy/features/pim/enrichment/product-model/add_product_model_children.feature,
 * now deleted. One test per scenario; each test's doc block gives its adaptations. Line numbers are those of the
 * feature file at the time each scenario was removed:
 *
 * - feature:93  "Successfully add a variant product to a sub product model" (#420)
 *     -> 'adds a variant product to a sub product model from the variant navigation'
 * - feature:174 "I cannot add a variant product with an already existing axis value combination" (#539)
 *     -> 'refuses a variant product whose axis value combination already exists'
 * - feature:56  "Successfully add a new sub product model when I already am on a sub product product model" (#546)
 *     -> 'adds a sub product model from a sub product model page'
 * The remaining ten, removed together with the file (line numbers of its last version):
 * - feature:15  @critical "Successfully add a sub product model with one axis to a root product model"
 *     -> 'adds a sub product model with one axis to a root product model'
 * - feature:26  "Successfully add a sub product model with many axes to a root product model"
 *     -> 'adds a sub product model with many axes to a root product model'
 * - feature:56  "Successfully add a sub product model when I am on a variant product"
 *     -> 'adds a sub product model from a variant product page'
 * - feature:68  @critical @purge-messenger "Successfully add a variant product to a root product model"
 *     -> 'adds a variant product to a root product model'
 * - feature:81  "Successfully add a new variant product when I already am on a variant product"
 *     -> 'adds a variant product from a variant product page'
 * - feature:92  "Successfully add a variant product with many axes to a root product model"
 *     -> 'adds a variant product with many axes to a root product model'
 * - feature:122 "I cannot add a sub product model without code"
 *     -> 'refuses a sub product model without code'
 * - feature:132 "I cannot add a sub product model without axis value"
 *     -> 'refuses a sub product model without axis value'
 * - feature:142 "I cannot add a variant product without axis value"
 *     -> 'refuses a variant product without axis value'
 * - feature:152 "I cannot add a sub product model with an already existing axis value combination"
 *     -> 'refuses a sub product model whose axis value combination already exists'
 *
 * Adaptations shared by the ten: the catalog_modeling products (apollon, amor, 1111111121...) become disposable
 * trees built through the internal API on icecat_demo_dev's clothing family variants, every code carries a
 * Date.now() suffix, and each tree is deleted in `finally` from the children endpoint (children first), which also
 * catches a child the UI created when the test failed before reading its id. "I am logged in as Julia" is admin, and
 * the Background "reference_color" attribute is dropped (see the next block). The "I cannot ..." tests also check
 * the 400 body and that the parent still has exactly the children it had before.
 */

/**
 * Replaced Behat scenario (deleted in #420): tests/legacy/features/pim/enrichment/product-model/add_product_model_children.feature:93
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
 *   The drop (z-index 9999, select2.css) sits above the modal (`.modal` z-index 1050, modal.less;
 *   nested `.AknFullPage` z-index 1040, FullPage.less), so a real click reaches it, the same way
 *   pim.ts's addAttributeToMassEdit does.
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
 *   so the URL assertion passes before the product page exists. The real sync is the product label
 *   heading showing the new SKU: pim/product-edit-form/product-label (product/edit.yml:79-82) extends
 *   form/common/label.js, whose element is `h1.AknTitleContainer-title` (label.js:7-8). It is
 *   appended (view/base.ts:246) inside the default-template.html:22 `title` drop zone, a div with the
 *   SAME class, so a bare `.AknTitleContainer-title` filtered on the SKU would match both nested
 *   elements and fail strict mode. The heading role matches only the h1. It stays empty until the
 *   family fetch resolves (product-label.js:25-27), then falls back to the identifier because
 *   family clothing's attribute_as_label (variation_name, icecat_demo_dev families.csv:17) is left
 *   empty on purpose (product-label.js:29). The previous product model page's h1 shows the
 *   sub-model code, which never equals the SKU.
 */

// icecat_demo_dev family_variants.csv:2 (clothing: level 1 axis color, level 2 axis size + sku).
const FAMILY_VARIANT = 'clothing_color_size';
// icecat_demo_dev family_variants.csv:4 (clothing: ONE level, axes color and size, sku among its attributes).
const ONE_LEVEL_FAMILY_VARIANT = 'clothing_colorsize';

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

/**
 * "I open the variant navigation children selector for level N" + "I press the "Add new" button and wait for
 * modal", exactly as the first tests of this file do it inline (selectors traced in the header block above):
 * navigation.html:16-18 renders one `.variant-navigation` Select2 per level whose parent level has a selected
 * entity, so `expectedSelectorCount` is 1 on a root product model page and 2 on a sub product model or a level-2
 * variant product page, and level N is container N - 1 (VariantNavigationDecorator::getChildrenSelectorForLevel).
 * The opening is retried because variant-navigation.js:36-37 re-renders the bar on every post_fetch.
 */
async function openAddChildModal(page: Page, level: number, expectedSelectorCount: number): Promise<Locator> {
  const childSelectors = page.locator('.variant-navigation.select2-container');
  await expect(childSelectors).toHaveCount(expectedSelectorCount, {timeout: 30_000});
  const selector = childSelectors.nth(level - 1);
  const dropdown = page.locator('#select2-drop');
  const addNewButton = dropdown.getByRole('button', {name: 'Add new', exact: true});
  await expect(async () => {
    const isOpen = await selector.evaluate(element => element.classList.contains('select2-dropdown-open'));
    if (!isOpen) {
      await selector.locator('.select2-arrow').click({timeout: 5_000});
    }
    await expect(dropdown).toBeVisible({timeout: 5_000});
    await expect(addNewButton).toBeVisible({timeout: 10_000});
  }).toPass({timeout: 60_000});
  await addNewButton.click({timeout: 10_000});

  const modal = page.locator('.modal.add-product-model-child');
  await expect(modal).toBeVisible({timeout: 15_000});
  return modal;
}

/** The `.AknFieldContainer` of an axis field, located by its "<label> (variant axis)" label (field.html:2-3). */
function axisField(modal: Locator, label: string): Locator {
  return modal
    .locator('.AknFieldContainer')
    .filter({has: modal.page().locator('.AknFieldContainer-label', {hasText: `${label} (variant axis)`})});
}

/**
 * Pick an option of a simple select axis (simple-select-async.js, Select2 3.4), as the tests above do: click the
 * `.select2-choice`, click the exact result in #select2-drop, then wait for the REBUILT field to repaint the label,
 * because the change re-renders the whole modal form (simple-select-async.js:69-73).
 */
async function pickAxisOption(field: Locator, optionLabel: string): Promise<void> {
  await field.locator('.select2-choice').click({timeout: 15_000});
  const option = field
    .page()
    .locator('#select2-drop .select2-result-selectable')
    .filter({hasText: new RegExp(`^\\s*${optionLabel}\\s*$`)});
  await expect(option).toBeVisible({timeout: 15_000});
  await option.click({timeout: 10_000});
  await expect(field.locator('.select2-chosen')).toHaveText(optionLabel, {timeout: 15_000});
}

/**
 * Turn a boolean axis on ("| Handmade (variant axis) | Yes |"). add_child.yml:34-38 maps akeneo-switch-field to
 * pim/form/common/fields/values/boolean with defaultValue false, so the field starts off. boolean.html renders
 * `<div class="switch"><input type="checkbox" id="pim_enrich_form_{fieldId}"></div>`, which boolean.js:40-42 hands to
 * bootstrapSwitch (lib/bootstrap-switch/bootstrap.switch.js:47-66): the checkbox gets wrapped next to a "Yes" span,
 * an empty label and a "No" span (pim_common.yes / pim_common.no), and only the half matching the state shows. While
 * off, the visible "No" span toggles the switch on click (bootstrap.switch.js:70-88, 138-153). That is the click
 * Behat's Base::toggleSwitch made on the `.switch` element. The checkbox `change` updates the model and re-renders
 * the whole form (boolean.js:9-15), so the check runs on the REBUILT checkbox, which renders the model value.
 */
async function switchAxisOn(field: Locator): Promise<void> {
  const checkbox = field.locator('input[type="checkbox"]');
  await expect(checkbox).not.toBeChecked({timeout: 15_000});
  await field.getByText('No', {exact: true}).click({timeout: 10_000});
  await expect(checkbox).toBeChecked({timeout: 15_000});
}

/**
 * Fill a metric axis ("| Weight (variant axis) | 800 GRAM |"), the way Behat's Form::fillMetricField splits it: the
 * amount goes in `input.amount`, the unit in the native `select.unit` (templates/form/common/fields/metric.html).
 * The label's for= does not match the input id there (metric.html omits the pim_enrich_form_ prefix of field.html),
 * so both controls are located inside the field container. The unit goes first: its `change` re-renders the whole
 * form (metric.js:16-20). The amount only writes the model on `keyup` (metric.js:12-15), so it is typed key by key;
 * a re-render landing mid-typing would repaint the partial model value, hence the retry until the field keeps it.
 */
async function fillMetricAxis(field: Locator, amount: string, unit: string): Promise<void> {
  await field.locator('select.unit').selectOption(unit, {timeout: 10_000});
  await expect(field.locator('select.unit')).toHaveValue(unit, {timeout: 15_000});
  const amountInput = field.locator('input.amount');
  await expect(async () => {
    await amountInput.fill('', {timeout: 5_000});
    await amountInput.pressSequentially(amount, {timeout: 5_000});
    await expect(amountInput).toHaveValue(amount, {timeout: 3_000});
  }).toPass({timeout: 30_000});
}

type ChildCreation = {response: PageResponse; text: string; body: any; payload: any};

/**
 * "I confirm the child creation": click the inner `.modal-body .ok` (see the header block above) and capture the
 * create request. variant-navigation.js:253-261 posts a variant product to pim_enrich_product_rest_create
 * (/enrich/product/rest) and a sub product model to pim_enrich_product_model_rest_create
 * (/enrich/product-model/rest/create). The posted payload is returned too, so a test can prove what the form sent.
 */
async function confirmChildCreation(
  page: Page,
  modal: Locator,
  kind: 'product' | 'product_model'
): Promise<ChildCreation> {
  const urlPattern =
    kind === 'product' ? /\/enrich\/product\/rest(\?|$)/ : /\/enrich\/product-model\/rest\/create(\?|$)/;
  const responsePromise = page.waitForResponse(
    response => urlPattern.test(response.url()) && response.request().method() === 'POST',
    {timeout: 30_000}
  );
  await modal.locator('.modal-body .ok').click({timeout: 10_000});
  const response = await responsePromise;
  const text = await response.text().catch(() => '');
  let body: any = null;
  try {
    body = JSON.parse(text);
  } catch {
    body = null;
  }
  return {response, text, body, payload: response.request().postDataJSON()};
}

/**
 * "Then I should see the text "<error>"" for a refused child: the create answers 400 with {values: [normalized
 * violations]} (ProductModelController::normalizeCreateViolations, ProductController::createAction). The violations
 * these tests trigger have no `values[...]` property path (code NotBlank on `code`, NotEmptyVariantAxes and
 * UniqueVariantAxis on `attribute`), so ProductViolationNormalizer makes them {global: true, message}. Only
 * add-child/fields-container.js:32-36 shows those, as `<li class="global-error">` (fields-container.html:3-11); no
 * field claims them (field.js:80-97 needs a matching `attribute` or `path`), so the text appears once in the modal.
 * The body is parsed rather than searched, because JsonResponse escapes the double quotes of the messages.
 */
async function expectChildRefused(created: ChildCreation, modal: Locator, message: string): Promise<void> {
  expect(
    created.response.status(),
    `The child should be refused with a 400: ${created.response.status()} ${created.text}`
  ).toBe(400);
  const messages = ((created.body?.values ?? []) as Array<{message?: string}>).map(violation => violation.message);
  expect(messages, `The 400 does not carry the expected error: ${created.text}`).toContain(message);
  await expect(modal.getByText(message, {exact: true})).toBeVisible({timeout: 15_000});
  await expect(modal).toBeVisible({timeout: 5_000});
}

/**
 * "Then I should be on the product model "<code>" edit page": the create answered 2xx with meta.id and model_type
 * product_model (ProductModelNormalizer), variant-navigation.js:426-439 redirects to #/enrich/product-model/{id}, and
 * the new page's h1 shows the code (product-model-label.js:9-17, ProductModel::getLabel falls back to the code). The
 * heading role excludes the same-class drop zone div (see the header block above). Returns the new id.
 */
async function expectOnNewProductModelPage(page: Page, created: ChildCreation, code: string): Promise<string> {
  expect(
    created.response.ok(),
    `Create sub product model failed: ${created.response.status()} ${created.text}`
  ).toBeTruthy();
  const id = created.body?.meta?.id;
  expect(id, `Create response had no meta.id: ${created.text}`).toBeTruthy();
  expect(created.body?.meta?.model_type, created.text).toBe('product_model');
  await expect(page).toHaveURL(new RegExp(`#/enrich/product-model/${id}$`), {timeout: 30_000});
  await expect(page.getByRole('heading', {level: 1, name: code, exact: true})).toBeVisible({timeout: 30_000});
  return String(id);
}

/**
 * "Then I should be on the product "<sku>" edit page": the create answered 2xx with meta.uuid,
 * variant-navigation.js:426-439 redirects to #/enrich/product/{uuid}, and the new page's h1 shows the SKU
 * (product-label.js:20-29 falls back to the identifier when the family's attribute as label has no value).
 * Returns the uuid.
 */
async function expectOnNewProductPage(page: Page, created: ChildCreation, sku: string): Promise<string> {
  expect(
    created.response.ok(),
    `Create variant product failed: ${created.response.status()} ${created.text}`
  ).toBeTruthy();
  const uuid = created.body?.meta?.uuid;
  expect(uuid, `Create response had no meta.uuid: ${created.text}`).toBeTruthy();
  await expect(page).toHaveURL(new RegExp(`#/enrich/product/${uuid}$`), {timeout: 30_000});
  await expect(page.getByRole('heading', {level: 1, name: sku, exact: true})).toBeVisible({timeout: 30_000});
  return String(uuid);
}

/** GET /enrich/product-model/rest/{id} or /enrich/product/rest/{uuid}, failing with the status and body. */
async function readEntity(
  page: Page,
  kind: 'product' | 'product_model',
  id: string
): Promise<{entity: any; text: string}> {
  const url = kind === 'product' ? `/enrich/product/rest/${id}` : `/enrich/product-model/rest/${id}`;
  const response = await page.request.get(url, {headers: XHR_HEADER, timeout: 30_000});
  const text = await response.text().catch(() => '');
  expect(response.ok(), `Get ${kind} ${id} failed: ${response.status()} ${text}`).toBeTruthy();
  return {entity: JSON.parse(text), text};
}

/** The scalar data of a non-localizable, non-scopable value of an internal API normalized entity. */
function valueData(entity: any, attributeCode: string): unknown {
  return entity?.values?.[attributeCode]?.[0]?.data;
}

/** Parse the JSON body of a setup call's response, or fail with its status and body. */
async function expectCreated(response: APIResponse, what: string): Promise<any> {
  const text = await response.text().catch(() => '');
  expect(response.ok(), `Create ${what} failed: ${response.status()} ${text}`).toBeTruthy();
  try {
    return JSON.parse(text);
  } catch {
    return null;
  }
}

/** A disposable root product model (level 0) of `familyVariantCode`. Returns its numeric id. */
async function createRootProductModel(page: Page, code: string, familyVariantCode: string): Promise<string> {
  const body = await expectCreated(
    await createProductModelViaApi(page, code, familyVariantCode),
    `root product model ${code}`
  );
  expect(body?.meta?.level, `Root product model is not at variation level 0: ${JSON.stringify(body)}`).toBe(0);
  const id = body?.meta?.id;
  expect(id, `Create response had no meta.id: ${JSON.stringify(body)}`).toBeTruthy();
  return String(id);
}

/** A disposable level-1 sub product model with color=blue under a clothing_color_size root. Returns its id. */
async function createBlueSubProductModel(page: Page, code: string, rootCode: string): Promise<string> {
  const body = await expectCreated(
    await createSubProductModelViaApi(page, code, rootCode, FAMILY_VARIANT, {color: 'blue'}),
    `sub product model ${code}`
  );
  expect(body?.meta?.level, `Sub product model is not at variation level 1: ${JSON.stringify(body)}`).toBe(1);
  const id = body?.meta?.id;
  expect(id, `Create response had no meta.id: ${JSON.stringify(body)}`).toBeTruthy();
  return String(id);
}

/**
 * A disposable clothing variant product under `parentCode` with the given simple select axis values (option codes),
 * through the endpoint the modal posts to (pim_enrich_product_rest_create). Returns its uuid (meta.id).
 */
async function createVariantProduct(
  page: Page,
  sku: string,
  parentCode: string,
  axisValues: Record<string, string>
): Promise<string> {
  const values: Record<string, Array<{locale: null; scope: null; data: string}>> = {};
  for (const [attributeCode, optionCode] of Object.entries(axisValues)) {
    values[attributeCode] = [{locale: null, scope: null, data: optionCode}];
  }
  const body = await expectCreated(
    await createProductViaApi(page, sku, 'clothing', {parent: parentCode, values}),
    `variant product ${sku}`
  );
  const uuid = body?.meta?.id;
  expect(uuid, `Create variant product response had no meta.id: ${JSON.stringify(body)}`).toBeTruthy();
  return String(uuid);
}

/** "Nothing was created": the parent's children, read through the API, are exactly `expectedIdentifiers`. */
async function expectChildren(page: Page, parentId: string, expectedIdentifiers: string[]): Promise<void> {
  const children = await getProductModelChildrenViaApi(page, parentId);
  expect(
    children.map(child => child.identifier).sort(),
    `Unexpected children of product model ${parentId}: ${JSON.stringify(children)}`
  ).toEqual([...expectedIdentifiers].sort());
}

async function attemptCleanup(what: string, action: () => Promise<APIResponse>): Promise<void> {
  const response = await action().catch(error => {
    console.warn(`[cleanup] ${what} threw: ${error}`);
    return null;
  });
  if (response && !response.ok() && response.status() !== 404) {
    console.warn(`[cleanup] ${what} refused: ${await describeResponse(response)}`);
  }
}

/**
 * Delete a disposable root product model and everything under it, children first
 * (ProductModelController::removeAction refuses a model that still has children). The tree is read from the children
 * endpoint rather than from ids the test collected, so a child the UI created is deleted even when the test failed
 * before reading the create response.
 */
async function cleanUpProductModelTree(page: Page, rootId: string | null): Promise<void> {
  if (!rootId) {
    return;
  }
  const childrenOf = async (id: string) =>
    getProductModelChildrenViaApi(page, id).catch(error => {
      console.warn(`[cleanup] children of product model ${id} unreadable: ${error}`);
      return [];
    });
  for (const child of await childrenOf(rootId)) {
    if (child.model_type === 'product_model') {
      for (const grandChild of await childrenOf(String(child.id))) {
        await attemptCleanup(`product ${grandChild.identifier}`, () =>
          deleteProductViaApi(page, String(grandChild.id))
        );
      }
      await attemptCleanup(`product model ${child.identifier}`, () => deleteProductModelViaApi(page, child.id));
    } else {
      await attemptCleanup(`product ${child.identifier}`, () => deleteProductViaApi(page, String(child.id)));
    }
  }
  await attemptCleanup(`product model ${rootId}`, () => deleteProductModelViaApi(page, rootId));
}

/** Codes of the disposable structure of the many-axes tests, each set as soon as it exists. */
type ManyAxesStructure = {attributeCode?: string; familyCode?: string; familyVariantCode?: string};

/**
 * Structure of the two many-axes tests. The Behat scenarios add a new boolean attribute "handmade" and the
 * Background's "reference_color" to catalog_modeling's clothing family through the family edit page, then create a
 * five-axes family variant on it. Here: a disposable boolean attribute labelled "Handmade" (group "other", icecat
 * attribute_groups.csv), a disposable family holding it with icecat's color, material, size (simple selects) and
 * weight (metric, family Weight, attributes.csv:8), and a disposable family variant built by `buildSets`. Editing the
 * shared clothing family would leak into every other spec; the family page itself is covered by the family specs.
 */
async function createManyAxesStructure(
  page: Page,
  ts: number,
  structure: ManyAxesStructure,
  buildSets: (handmadeCode: string) => Array<{level: number; axes: string[]; attributes: string[]}>
): Promise<void> {
  const attributeCode = `pw_handmade_${ts}`;
  await expectCreated(
    await createAttributeViaApi(page, {
      code: attributeCode,
      type: 'pim_catalog_boolean',
      group: 'other',
      labels: {en_US: 'Handmade'},
    }),
    `attribute ${attributeCode}`
  );
  structure.attributeCode = attributeCode;

  const familyCode = `pw_addchild_family_${ts}`;
  await expectCreated(
    await createFamilyViaApi(page, familyCode, ['color', 'material', 'size', 'weight', attributeCode]),
    `family ${familyCode}`
  );
  structure.familyCode = familyCode;

  const familyVariantCode = `pw_addchild_fv_${ts}`;
  await expectCreated(
    await createFamilyVariantViaApi(page, familyVariantCode, familyCode, buildSets(attributeCode)),
    `family variant ${familyVariantCode}`
  );
  structure.familyVariantCode = familyVariantCode;
}

async function cleanUpManyAxesStructure(page: Page, structure: ManyAxesStructure): Promise<void> {
  const {familyVariantCode, familyCode, attributeCode} = structure;
  if (familyVariantCode) {
    await attemptCleanup(`family variant ${familyVariantCode}`, () =>
      deleteFamilyVariantViaApi(page, familyVariantCode)
    );
  }
  if (familyCode) {
    await attemptCleanup(`family ${familyCode}`, () => deleteFamilyViaApi(page, familyCode));
  }
  if (attributeCode) {
    await attemptCleanup(`attribute ${attributeCode}`, () => deleteAttributeViaApi(page, attributeCode));
  }
}

/** Assert a persisted metric value is 800 GRAM, whatever the amount's decimal formatting. */
function expectWeight(entity: any, text: string): void {
  const weight = valueData(entity, 'weight') as {amount?: unknown; unit?: unknown} | undefined;
  expect(parseFloat(String(weight?.amount ?? '').replace(',', '.')), text).toBe(800);
  expect(weight?.unit, text).toBe('GRAM');
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
    const createBody = (await createResp.json().catch(() => null)) as {meta?: {uuid?: string}} | null;
    const uuid = createBody?.meta?.uuid;
    expect(uuid, `Create response had no meta.uuid: ${createText}`).toBeTruthy();
    await expect(modal).toBeHidden({timeout: 15_000});

    // Then I should be on the product "apollon_blue_xl" edit page
    await expect(page).toHaveURL(new RegExp(`#/enrich/product/${uuid}$`), {timeout: 30_000});
    await expect(page.getByRole('heading', {level: 1, name: sku, exact: true})).toBeVisible({timeout: 30_000});

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

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:174
   *   "I cannot add a variant product with an already existing axis value combination"
   *
   * Adaptations:
   * - Catalog: the Behat scenario opens the catalog_modeling root product model "amor" (family variant
   *   clothing_colorsize, one level, axes color + size) whose variant 1111111113 already has color=blue,
   *   size=m. icecat_demo_dev ships a family variant with the same code and the same layout
   *   (family_variants.csv:4: family clothing, level 1 axes color,size, sku among the level-1 attributes),
   *   with the options color "blue" (label Blue, attribute_options.csv:87) and size "m" (label M, :99), and
   *   the attribute labels Color and Size (attributes.csv:70-71). The test reuses that family variant
   *   read-only, like the test above reuses clothing_color_size, and creates the disposable part through
   *   the internal API: a root product model and a variant product under it with color=blue, size=m. Both
   *   carry a Date.now() suffix and are deleted afterwards (variant products first, since
   *   ProductModelController::removeAction refuses a model that still has children). Uniqueness of an axis
   *   combination is checked among SIBLINGS only (UniqueVariantAxisValidator, siblings read in SQL by
   *   SqlGetValuesOfSiblings), so a fresh root isolates the test from any other variant of the catalog.
   * - The expected message therefore names the disposable variant's SKU instead of "1111111113", and the
   *   new SKU instead of "apollon_new_blue_m" (UniqueVariantAxisValidator::addViolation: %validated_entity%
   *   is the identifier of the product being created, %sibling_with_same_value% the sibling's).
   * - "I am logged in as Julia": admin/admin, like the test above.
   * - Field order: Color, then Size, then SKU, for the reason given in the header of this file (every
   *   select change rebuilds the whole modal form). Each select is synchronised on its REBUILT field
   *   repainting the chosen label, and both are checked again after the second rebuild.
   * - Not in Behat: the create request must answer 400 with the message in its body, the modal must stay
   *   open, and if the duplicate was created after all it is deleted with the rest.
   *
   * Selectors: the same as the test above (variant navigation container, #select2-drop "Add new" button,
   * `.modal.add-product-model-child`, `.AknFieldContainer` + label for the axis fields, getByLabel('SKU'),
   * `.modal-body .ok`), with one container on the page: navigation.html renders the level-1 selector of a
   * root product model because level 0 has a selected entity, and no level-2 selector exists for a one-level
   * family variant. The title "Add a new Color, Size" is header.js's sorted, comma-joined axis labels.
   * The error: the violation is raised atPath('attribute'), which ProductViolationNormalizer does not map to
   * an attribute value, so it is normalized as {global: true, message}. add-child/fields-container.js keeps
   * the errors without an `attribute` key as global errors and fields-container.html renders each one as
   * `<li class="global-error">` in an `.AknMessageBox--danger` list inside the modal. A listitem takes no
   * accessible name from its content, so the item is located by its exact text within the modal.
   */
  test('refuses a variant product whose axis value combination already exists', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_amor_${ts}`;
    const existingSku = `pw_addchild_blue_m_${ts}`;
    const sku = `pw_addchild_new_blue_m_${ts}`;
    const productUuids: string[] = [];
    let rootId: string | null = null;

    try {
      // Setup: root product model -> variant product with color=blue, size=m.
      const rootResp = await createProductModelViaApi(page, rootCode, ONE_LEVEL_FAMILY_VARIANT);
      expect(
        rootResp.ok(),
        `Create root product model ${rootCode} failed: ${await describeResponse(rootResp)}`
      ).toBeTruthy();
      const rootBody = await rootResp.json();
      expect(rootBody?.meta?.level, `Root product model is not at variation level 0: ${JSON.stringify(rootBody)}`).toBe(
        0
      );
      rootId = rootBody?.meta?.id != null ? String(rootBody.meta.id) : null;
      expect(rootId, `Create response had no meta.id: ${JSON.stringify(rootBody)}`).toBeTruthy();

      const existingResp = await createProductViaApi(page, existingSku, 'clothing', {
        parent: rootCode,
        values: {
          color: [{locale: null, scope: null, data: 'blue'}],
          size: [{locale: null, scope: null, data: 'm'}],
        },
      });
      const existingText = await existingResp.text().catch(() => '');
      expect(
        existingResp.ok(),
        `Create variant product ${existingSku} failed: ${existingResp.status()} ${existingText}`
      ).toBeTruthy();
      const existingUuid = JSON.parse(existingText)?.meta?.id;
      expect(existingUuid, `Create variant product response had no meta.id: ${existingText}`).toBeTruthy();
      productUuids.push(String(existingUuid));

      // Given I am on the "amor" product model page
      await nav.goToEntityPage('product model', rootId!);
      const childSelectors = page.locator('.variant-navigation.select2-container');
      await expect(childSelectors).toHaveCount(1, {timeout: 30_000});

      // When I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const levelOneSelector = childSelectors.first();
      const dropdown = page.locator('#select2-drop');
      const addNewButton = dropdown.getByRole('button', {name: 'Add new', exact: true});
      await expect(async () => {
        const isOpen = await levelOneSelector.evaluate(element => element.classList.contains('select2-dropdown-open'));
        if (!isOpen) {
          await levelOneSelector.locator('.select2-arrow').click({timeout: 5_000});
        }
        await expect(dropdown).toBeVisible({timeout: 5_000});
        await expect(addNewButton).toBeVisible({timeout: 10_000});
      }).toPass({timeout: 60_000});
      await addNewButton.click({timeout: 10_000});

      const modal = page.locator('.modal.add-product-model-child');
      await expect(modal).toBeVisible({timeout: 15_000});

      // Then I should see the text "Add a new Color, Size"
      await expect(modal.getByText('Add a new Color, Size', {exact: true})).toBeVisible({timeout: 15_000});
      const axisField = (label: string) =>
        modal
          .locator('.AknFieldContainer')
          .filter({has: page.locator('.AknFieldContainer-label', {hasText: `${label} (variant axis)`})});
      const colorField = axisField('Color');
      const sizeField = axisField('Size');
      await expect(colorField).toBeVisible({timeout: 15_000});
      await expect(sizeField).toBeVisible({timeout: 15_000});
      const skuInput = modal.getByLabel('SKU', {exact: true});
      await expect(skuInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | Color (variant axis) | Blue | Size (variant axis) | M |
      const pick = async (field: Locator, optionLabel: string) => {
        await field.locator('.select2-choice').click({timeout: 15_000});
        const option = dropdown
          .locator('.select2-result-selectable')
          .filter({hasText: new RegExp(`^\\s*${optionLabel}\\s*$`)});
        await expect(option).toBeVisible({timeout: 15_000});
        await option.click({timeout: 10_000});
        // The change rebuilds the whole modal form: wait for the REBUILT field to show the value.
        await expect(field.locator('.select2-chosen')).toHaveText(optionLabel, {timeout: 15_000});
      };
      await pick(colorField, 'Blue');
      await pick(sizeField, 'M');
      await expect(colorField.locator('.select2-chosen')).toHaveText('Blue', {timeout: 15_000});

      // When I fill in the following child information: | SKU | apollon_new_blue_m |
      await skuInput.fill(sku, {timeout: 10_000});
      await expect(skuInput).toHaveValue(sku);

      // And I confirm the child creation
      const createRespPromise = page.waitForResponse(
        response => /\/enrich\/product\/rest(\?|$)/.test(response.url()) && response.request().method() === 'POST',
        {timeout: 30_000}
      );
      await modal.locator('.modal-body .ok').click({timeout: 10_000});
      const createResp = await createRespPromise;
      const createText = await createResp.text().catch(() => '');
      if (createResp.ok()) {
        const createdUuid = JSON.parse(createText)?.meta?.uuid;
        if (createdUuid) {
          productUuids.push(String(createdUuid));
        }
      }
      expect(
        createResp.status(),
        `The duplicate should be refused with a 400: ${createResp.status()} ${createText}`
      ).toBe(400);

      // Then I should see the text "Cannot set value "[blue],[m]" for the attribute axis "color,size" ..."
      const message =
        `Cannot set value "[blue],[m]" for the attribute axis "color,size" on variant product "${sku}", ` +
        `as the variant product "${existingSku}" already has this value`;
      // ProductController::createAction answers {values: [normalized violations]}; the body is parsed rather
      // than searched, because JsonResponse escapes the double quotes of the message.
      const violations = (JSON.parse(createText)?.values ?? []) as Array<{message?: string}>;
      expect(
        violations.map(violation => violation.message),
        `The 400 does not carry the axis uniqueness error: ${createText}`
      ).toContain(message);
      await expect(modal.getByText(message, {exact: true})).toBeVisible({timeout: 15_000});
      await expect(modal).toBeVisible();
    } finally {
      for (const uuid of productUuids) {
        const resp = await deleteProductViaApi(page, uuid).catch(() => null);
        if (resp && !resp.ok() && resp.status() !== 404) {
          console.warn(`[cleanup] product ${uuid} refused: ${await describeResponse(resp)}`);
        }
      }
      if (rootId) {
        const resp = await deleteProductModelViaApi(page, rootId).catch(() => null);
        if (resp && !resp.ok() && resp.status() !== 404) {
          console.warn(`[cleanup] product model ${rootId} refused: ${await describeResponse(resp)}`);
        }
      }
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:56
   *   "Successfully add a new sub product model when I already am on a sub product product model"
   *
   * Adaptations:
   * - Catalog: the Behat scenario opens the catalog_modeling sub product model "apollon_blue" (child of the
   *   root "apollon"). The test builds the same 2-tier start through the internal API, like the first test of
   *   this file: a root product model and a level-1 sub product model with color=blue, both on the
   *   icecat_demo_dev family variant clothing_color_size (family_variants.csv:2, level 1 axis color). The new
   *   sibling takes color "black" (label Black, attribute_options.csv:86), so the axis uniqueness rule, which
   *   only compares siblings, cannot refuse it. Every code carries a Date.now() suffix, and the three product
   *   models are deleted afterwards, children before the root.
   * - "I am logged in as Julia": admin/admin, like the tests above. ROLE_ADMINISTRATOR holds
   *   pim_enrich_product_model_create, which variant-navigation.js::isCreationGranted (175-180) checks before
   *   appending the "Add new" footer for a product model child.
   * - Field order: Color is picked BEFORE the code is typed, the reverse of the Behat table, for the reason
   *   given in the header of this file (a select change rebuilds every field of the modal). The text field
   *   writes the model on `input` without re-rendering the form (form/common/fields/text.js:15-22), so the code
   *   typed after the rebuild is the one posted.
   * - Not in Behat: the create request must answer 2xx with meta.model_type "product_model", and the new sub
   *   product model is re-read through GET /enrich/product-model/rest/{id}: same code, parent = the ROOT (not
   *   the sub product model the user started from), same family variant, level 1, color black.
   *
   * What this scenario covers that feature:15 (same flow from the root) does not: on a level-1 page, opening
   * the level-1 selector makes variant-navigation.js::getEntityParentCode (189-206) take the "same level"
   * branch and return entity.parent, so the modal is opened for the ROOT (openModal 213-245, initial state
   * {parent, values, family_variant}) and the child is a sibling of the current page.
   *
   * Selectors (beyond those of the first test, which they share):
   * - Level-1 selector: on a level-1 sub product model navigation.html:16-18 renders the hidden input of
   *   levels 1 and 2, so there are 2 `.variant-navigation.select2-container` and level 1 is index 0.
   * - "Add a new Color": header.js:16-41 lists the axes of level parent.meta.level + 1 = 1 of the root.
   * - "Code (required)": add-child/fields-container.js:91 appends createProductModelCodeField (212-223) after
   *   the axis fields because the child is not a variant product. That field is pim/form/common/fields/text
   *   configured required with label pim_common.code (add_child.yml:54-58) = "Code"
   *   (UIBundle jsmessages.en_US.yml:194), required marker pim_common.required_label = "(required)" (:239).
   *   field.html:2-3 renders `<label for="pim_enrich_form_{fieldId}">Code <em>(required)</em></label>` and
   *   text.html:1-2 the `<input id="pim_enrich_form_{fieldId}">` with the SAME per-render fieldId
   *   (field.js:145-168), so getByLabel('Code (required)') resolves the input. No Select2 rewrites this label.
   * - Confirm: `.modal-body .ok`, as in the first test. submitForm (variant-navigation.js:253-261) posts to
   *   pim_enrich_product_model_rest_create = /enrich/product-model/rest/create (internal_api/product_model.yml:27-30,
   *   prefix /enrich/product-model in Enrichment routing.yml:19-21).
   * - "I should be on the product model "apollon_black" edit page": the Behat step asserts the URL of
   *   pim_enrich_product_model_edit {id} and waits for `.AknTitleContainer-title`. redirectToEntity
   *   (variant-navigation.js:426-439) builds that route from the create response meta (id, model_type
   *   "product_model", ProductModelNormalizer.php:103,106) -> #/enrich/product-model/{id} (ui/product_model.yml:1-2).
   *   The URL can change before the new page renders, so the sync is the h1: product-model-label.js:9-17 shows
   *   meta.label[catalogLocale], which ProductModel::getLabel (ProductModel.php:517-538) falls back to the code
   *   because the new model has no variation_name value. label.js:7-8 makes it an `h1.AknTitleContainer-title`;
   *   the heading role excludes the same-class drop zone div. The page the user started from shows the blue
   *   sub product model's code, never the new one.
   */
  test('adds a sub product model from a sub product model page', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_pm_root_${ts}`;
    const blueCode = `pw_addchild_pm_blue_${ts}`;
    const newCode = `pw_addchild_pm_black_${ts}`;
    // Deleted in reverse order of creation: children before the root.
    const productModelIds: string[] = [];

    try {
      // Setup: root product model -> sub product model (color=blue), sequential because the child needs its parent.
      const rootResp = await createProductModelViaApi(page, rootCode, FAMILY_VARIANT);
      expect(
        rootResp.ok(),
        `Create root product model ${rootCode} failed: ${await describeResponse(rootResp)}`
      ).toBeTruthy();
      const rootBody = await rootResp.json();
      const rootId = rootBody?.meta?.id;
      expect(rootId, `Create response had no meta.id: ${JSON.stringify(rootBody)}`).toBeTruthy();
      productModelIds.push(String(rootId));

      const blueResp = await createSubProductModelViaApi(page, blueCode, rootCode, FAMILY_VARIANT, {color: 'blue'});
      expect(
        blueResp.ok(),
        `Create sub product model ${blueCode} failed: ${await describeResponse(blueResp)}`
      ).toBeTruthy();
      const blueBody = await blueResp.json();
      expect(blueBody?.meta?.level, `Sub product model is not at variation level 1: ${JSON.stringify(blueBody)}`).toBe(
        1
      );
      const blueId = blueBody?.meta?.id;
      expect(blueId, `Create response had no meta.id: ${JSON.stringify(blueBody)}`).toBeTruthy();
      productModelIds.push(String(blueId));

      // Given I am on the "apollon_blue" product model page
      await nav.goToEntityPage('product model', String(blueId));
      await expect(page.getByRole('heading', {level: 1, name: blueCode, exact: true})).toBeVisible({timeout: 30_000});
      const childSelectors = page.locator('.variant-navigation.select2-container');
      await expect(childSelectors).toHaveCount(2, {timeout: 30_000});

      // When I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const levelOneSelector = childSelectors.nth(0);
      const dropdown = page.locator('#select2-drop');
      const addNewButton = dropdown.getByRole('button', {name: 'Add new', exact: true});
      await expect(async () => {
        const isOpen = await levelOneSelector.evaluate(element => element.classList.contains('select2-dropdown-open'));
        if (!isOpen) {
          await levelOneSelector.locator('.select2-arrow').click({timeout: 5_000});
        }
        await expect(dropdown).toBeVisible({timeout: 5_000});
        await expect(addNewButton).toBeVisible({timeout: 10_000});
      }).toPass({timeout: 60_000});
      await addNewButton.click({timeout: 10_000});

      const modal = page.locator('.modal.add-product-model-child');
      await expect(modal).toBeVisible({timeout: 15_000});

      // Then I should see the text "Add a new Color"
      await expect(modal.getByText('Add a new Color', {exact: true})).toBeVisible({timeout: 15_000});
      const colorField = modal
        .locator('.AknFieldContainer')
        .filter({has: page.locator('.AknFieldContainer-label', {hasText: 'Color (variant axis)'})});
      await expect(colorField).toBeVisible({timeout: 15_000});
      const codeInput = modal.getByLabel('Code (required)', {exact: true});
      await expect(codeInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | Color (variant axis) | Black |
      await colorField.locator('.select2-choice').click({timeout: 15_000});
      const blackOption = dropdown.locator('.select2-result-selectable').filter({hasText: /^\s*Black\s*$/});
      await expect(blackOption).toBeVisible({timeout: 15_000});
      await blackOption.click({timeout: 10_000});
      // The change rebuilds the whole modal form: wait for the REBUILT field to show the value.
      await expect(colorField.locator('.select2-chosen')).toHaveText('Black', {timeout: 15_000});

      // When I fill in the following child information: | Code (required) | apollon_black |
      await codeInput.fill(newCode, {timeout: 10_000});
      await expect(codeInput).toHaveValue(newCode, {timeout: 5_000});

      // And I confirm the child creation
      const createRespPromise = page.waitForResponse(
        response =>
          /\/enrich\/product-model\/rest\/create(\?|$)/.test(response.url()) && response.request().method() === 'POST',
        {timeout: 30_000}
      );
      await modal.locator('.modal-body .ok').click({timeout: 10_000});
      const createResp = await createRespPromise;
      const createText = await createResp.text().catch(() => '');
      const createBody = (() => {
        try {
          return JSON.parse(createText) as {meta?: {id?: number; model_type?: string}};
        } catch {
          return null;
        }
      })();
      if (createResp.ok() && createBody?.meta?.id != null) {
        productModelIds.push(String(createBody.meta.id));
      }
      expect(createResp.ok(), `Create sub product model failed: ${createResp.status()} ${createText}`).toBeTruthy();
      const newId = createBody?.meta?.id;
      expect(newId, `Create response had no meta.id: ${createText}`).toBeTruthy();
      expect(createBody?.meta?.model_type, createText).toBe('product_model');
      await expect(modal).toBeHidden({timeout: 15_000});

      // Then I should be on the product model "apollon_black" edit page
      await expect(page).toHaveURL(new RegExp(`#/enrich/product-model/${newId}$`), {timeout: 30_000});
      await expect(page.getByRole('heading', {level: 1, name: newCode, exact: true})).toBeVisible({timeout: 30_000});

      // Persistence: the new sub product model is a sibling of the starting one, under the root, with color black.
      const getResp = await page.request.get(`/enrich/product-model/rest/${newId}`, {
        headers: XHR_HEADER,
        timeout: 30_000,
      });
      const getText = await getResp.text().catch(() => '');
      expect(getResp.ok(), `Get product model ${newId} failed: ${getResp.status()} ${getText}`).toBeTruthy();
      const productModel = JSON.parse(getText);
      expect(productModel.code, getText).toBe(newCode);
      expect(productModel.parent, getText).toBe(rootCode);
      expect(productModel.family_variant, getText).toBe(FAMILY_VARIANT);
      expect(productModel.meta?.level, getText).toBe(1);
      expect(
        String(productModel.values?.color?.[0]?.data ?? '').toLowerCase(),
        JSON.stringify(productModel.values?.color)
      ).toBe('black');
    } finally {
      for (const id of [...productModelIds].reverse()) {
        const resp = await deleteProductModelViaApi(page, id).catch(() => null);
        if (resp && !resp.ok() && resp.status() !== 404) {
          console.warn(`[cleanup] product model ${id} refused: ${await describeResponse(resp)}`);
        }
      }
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:15 (@critical)
   *   "Successfully add a sub product model with one axis to a root product model"
   *
   * Adaptations:
   * - "apollon" (catalog_modeling root of clothing_color_size) becomes a disposable root product model on icecat's
   *   clothing_color_size (family_variants.csv:2: level 1 axis color, level 2 axis size). The new child takes color
   *   "black" (label Black, attribute_options.csv:86) and a code with a Date.now() suffix.
   * - Field order: Color before the code, as in the feature:56 test above (a select change rebuilds the form, the
   *   code input does not).
   * - Not in Behat: the new sub product model is re-read through the API: code, parent = the root, family variant,
   *   level 1, color black.
   *
   * Selectors: openAddChildModal with ONE container (a root page renders only the level-1 selector,
   * navigation.html:16-18), the title header.js:16-41 builds from the level-1 axis labels, axisField/pickAxisOption,
   * getByLabel('Code (required)') (traced in the feature:56 test), confirmChildCreation, expectOnNewProductModelPage.
   */
  test('adds a sub product model with one axis to a root product model', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_one_root_${ts}`;
    const newCode = `pw_addchild_one_black_${ts}`;
    let rootId: string | null = null;

    try {
      rootId = await createRootProductModel(page, rootCode, FAMILY_VARIANT);

      // Given I am on the "apollon" product model page
      await nav.goToEntityPage('product model', rootId);
      await expect(page.getByRole('heading', {level: 1, name: rootCode, exact: true})).toBeVisible({timeout: 30_000});

      // When I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 1, 1);

      // Then I should see the text "Add a new Color"
      await expect(modal.getByText('Add a new Color', {exact: true})).toBeVisible({timeout: 15_000});
      const colorField = axisField(modal, 'Color');
      await expect(colorField).toBeVisible({timeout: 15_000});
      const codeInput = modal.getByLabel('Code (required)', {exact: true});
      await expect(codeInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | Code (required) | apollon_black | Color | Black |
      await pickAxisOption(colorField, 'Black');
      await codeInput.fill(newCode, {timeout: 10_000});
      await expect(codeInput).toHaveValue(newCode, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product_model');

      // Then I should be on the product model "apollon_black" edit page
      const newId = await expectOnNewProductModelPage(page, created, newCode);
      await expect(modal).toBeHidden({timeout: 15_000});

      // Persistence: a level-1 sub product model of the root, with color black.
      const {entity, text} = await readEntity(page, 'product_model', newId);
      expect(entity.code, text).toBe(newCode);
      expect(entity.parent, text).toBe(rootCode);
      expect(entity.family_variant, text).toBe(FAMILY_VARIANT);
      expect(entity.meta?.level, text).toBe(1);
      expect(String(valueData(entity, 'color') ?? '').toLowerCase(), text).toBe('black');
    } finally {
      await cleanUpProductModelTree(page, rootId);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:26
   *   "Successfully add a sub product model with many axes to a root product model"
   *
   * Adaptations:
   * - Structure: see createManyAxesStructure. The Behat setup steps on the family page ("I visit the Attributes
   *   tab", "I add available attributes", "I save the family", no "unsaved changes") become API calls; they prepare
   *   data, the family edit page is not what this scenario is about.
   * - Axes: the Behat family variant has 5 level-1 axes (color, material, weight, handmade, reference_color) and size
   *   at level 2. reference_color is dropped: a pim_reference_data_simpleselect attribute needs the "color"
   *   reference data, which only the dev/test/behat configs declare (config/packages/{dev,test,behat}/
   *   akeneo_pim_structure.yml), and the Playwright job runs APP_ENV=prod (ci.yml, test-playwright). Its four
   *   siblings still cover one axis of each other kind the modal maps (add_child.yml:15-21): two simple selects, a
   *   metric and a boolean. The title loses ", Reference color" accordingly.
   * - Not in Behat: the posted payload and the re-read sub product model carry the four axis values.
   *
   * Selectors: the same as the test above, plus switchAxisOn (Handmade) and fillMetricAxis (Weight), whose doc blocks
   * trace them.
   */
  test('adds a sub product model with many axes to a root product model', async ({page}) => {
    const ts = Date.now();
    const structure: ManyAxesStructure = {};
    const rootCode = `pw_addchild_many_root_${ts}`;
    const newCode = `pw_addchild_many_pm_${ts}`;
    let rootId: string | null = null;

    try {
      // Given the following attribute: handmade, the clothing family holding it, and the following family variant
      await createManyAxesStructure(page, ts, structure, handmadeCode => [
        {
          level: 1,
          axes: ['color', 'material', 'weight', handmadeCode],
          attributes: ['color', 'material', 'weight', handmadeCode],
        },
        {level: 2, axes: ['size'], attributes: ['size', 'sku']},
      ]);
      // And the following root product model: root_model
      rootId = await createRootProductModel(page, rootCode, structure.familyVariantCode!);

      // When I am on the "root_model" product model page
      await nav.goToEntityPage('product model', rootId);
      await expect(page.getByRole('heading', {level: 1, name: rootCode, exact: true})).toBeVisible({timeout: 30_000});

      // And I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 1, 1);

      // Then I should see the text "Add a new Color, Handmade, Material, Reference color, Weight"
      await expect(modal.getByText('Add a new Color, Handmade, Material, Weight', {exact: true})).toBeVisible({
        timeout: 15_000,
      });
      const colorField = axisField(modal, 'Color');
      const materialField = axisField(modal, 'Material');
      const handmadeField = axisField(modal, 'Handmade');
      const weightField = axisField(modal, 'Weight');
      for (const field of [colorField, materialField, handmadeField, weightField]) {
        await expect(field).toBeVisible({timeout: 15_000});
      }
      const codeInput = modal.getByLabel('Code (required)', {exact: true});
      await expect(codeInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: Color Blue, Material leather, Weight 800 GRAM, Handmade Yes
      await pickAxisOption(colorField, 'Blue');
      await pickAxisOption(materialField, 'leather');
      await switchAxisOn(handmadeField);
      await fillMetricAxis(weightField, '800', 'GRAM');
      // Every value survived the rebuilds of the later changes.
      await expect(colorField.locator('.select2-chosen')).toHaveText('Blue', {timeout: 15_000});
      await expect(materialField.locator('.select2-chosen')).toHaveText('leather', {timeout: 15_000});
      await expect(handmadeField.locator('input[type="checkbox"]')).toBeChecked({timeout: 15_000});
      // ... and | Code (required) | model_with_five_axes |
      await codeInput.fill(newCode, {timeout: 10_000});
      await expect(codeInput).toHaveValue(newCode, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product_model');
      expect(valueData(created.payload, 'weight'), JSON.stringify(created.payload)).toEqual({
        amount: '800',
        unit: 'GRAM',
      });

      // Then I should be on the product model "model_with_five_axes" edit page
      const newId = await expectOnNewProductModelPage(page, created, newCode);

      // Persistence: the four axis values are stored on the new level-1 sub product model.
      const {entity, text} = await readEntity(page, 'product_model', newId);
      expect(entity.code, text).toBe(newCode);
      expect(entity.parent, text).toBe(rootCode);
      expect(entity.family_variant, text).toBe(structure.familyVariantCode);
      expect(entity.meta?.level, text).toBe(1);
      expect(String(valueData(entity, 'color') ?? '').toLowerCase(), text).toBe('blue');
      expect(String(valueData(entity, 'material') ?? '').toLowerCase(), text).toBe('leather');
      expect(valueData(entity, structure.attributeCode!), text).toBe(true);
      expectWeight(entity, text);
    } finally {
      await cleanUpProductModelTree(page, rootId);
      await cleanUpManyAxesStructure(page, structure);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:56
   *   "Successfully add a sub product model when I am on a variant product"
   *
   * Adaptations:
   * - "1111111121" (catalog_modeling variant product under apollon > apollon_blue) becomes a disposable 3-tier tree
   *   on icecat's clothing_color_size: a root, a level-1 sub product model with color=blue, and a variant product
   *   under it with size=m (attribute_options.csv:99). The new sub product model takes color "black".
   * - Field order: Color before the code (see the feature:56 test above).
   * - Not in Behat: the new sub product model is re-read through the API, and its parent is the ROOT.
   *
   * What it covers: on a level-2 variant product, opening the level-1 selector makes
   * variant-navigation.js::getEntityParentCode (189-206) take the `targetLevel < entityLevel` branch, which fetches
   * the product's parent and answers ITS parent, the root. openModal (213-245) then builds a product model child of
   * the root, so the new model is a sibling of the product's parent.
   *
   * Selectors: openAddChildModal with TWO containers (a level-2 product page renders the level-1 and level-2
   * selectors) and level 1 = the first. The starting page's h1 shows the SKU: product-label.js:20-29 falls back to
   * the identifier because clothing's attribute as label (variation_name, families.csv:17) has no value.
   */
  test('adds a sub product model from a variant product page', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_vp_root_${ts}`;
    const blueCode = `pw_addchild_vp_blue_${ts}`;
    const existingSku = `pw_addchild_vp_blue_m_${ts}`;
    const newCode = `pw_addchild_vp_black_${ts}`;
    let rootId: string | null = null;

    try {
      rootId = await createRootProductModel(page, rootCode, FAMILY_VARIANT);
      await createBlueSubProductModel(page, blueCode, rootCode);
      const productUuid = await createVariantProduct(page, existingSku, blueCode, {size: 'm'});

      // Given I am on the "1111111121" product page
      await nav.goToEntityPage('product', productUuid);
      await expect(page.getByRole('heading', {level: 1, name: existingSku, exact: true})).toBeVisible({
        timeout: 30_000,
      });

      // When I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 1, 2);

      // Then I should see the text "Add a new Color"
      await expect(modal.getByText('Add a new Color', {exact: true})).toBeVisible({timeout: 15_000});
      const colorField = axisField(modal, 'Color');
      await expect(colorField).toBeVisible({timeout: 15_000});
      const codeInput = modal.getByLabel('Code (required)', {exact: true});
      await expect(codeInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | Code (required) | apollon_black | Color | Black |
      await pickAxisOption(colorField, 'Black');
      await codeInput.fill(newCode, {timeout: 10_000});
      await expect(codeInput).toHaveValue(newCode, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product_model');

      // Then I should be on the product model "apollon_black" edit page
      const newId = await expectOnNewProductModelPage(page, created, newCode);

      // Persistence: a sibling of the product's parent, under the root, with color black.
      const {entity, text} = await readEntity(page, 'product_model', newId);
      expect(entity.code, text).toBe(newCode);
      expect(entity.parent, text).toBe(rootCode);
      expect(entity.family_variant, text).toBe(FAMILY_VARIANT);
      expect(entity.meta?.level, text).toBe(1);
      expect(String(valueData(entity, 'color') ?? '').toLowerCase(), text).toBe('black');
    } finally {
      await cleanUpProductModelTree(page, rootId);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:68 (@critical
   * @purge-messenger)
   *   "Successfully add a variant product to a root product model"
   *
   * Adaptations:
   * - "amor" (catalog_modeling root of clothing_colorsize) becomes a disposable root on icecat's clothing_colorsize
   *   (family_variants.csv:4: ONE level, axes color and size, sku among its attributes), like the feature:174 test.
   * - Field order: Color, then Size, then the SKU (every select change rebuilds the form).
   * - "And 1 event of type "product.created" should have been raised": not portable. ConnectivityContext reads the
   *   messenger transport in the Behat process; a browser test has no access to it, and the PIM exposes no read of
   *   it. The event is guarded at the backend on the same endpoint the modal posts to:
   *   tests/back/Pim/Enrichment/Integration/Controller/InternalApi/CreateProductControllerIntegration.php
   *   (test_it_creates_a_product_in_a_family_and_raises_one_product_created_event and
   *   test_it_creates_a_variant_product_under_a_sub_product_model, both assertEventCount(1, ProductCreated::class)),
   *   which runs in test-phpunit-integration. What stays here is the user-visible outcome, plus a re-read.
   * - Not in Behat: the new variant product is re-read through the API: SKU, family, parent = the root, color black,
   *   size xl.
   *
   * Selectors: openAddChildModal with one container, axisField/pickAxisOption for Color and Size, getByLabel('SKU')
   * (traced in the header block above), confirmChildCreation, expectOnNewProductPage.
   */
  test('adds a variant product to a root product model', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_amor_root_${ts}`;
    const sku = `pw_addchild_amor_black_xl_${ts}`;
    let rootId: string | null = null;

    try {
      rootId = await createRootProductModel(page, rootCode, ONE_LEVEL_FAMILY_VARIANT);

      // Given I am on the "amor" product model page
      await nav.goToEntityPage('product model', rootId);
      await expect(page.getByRole('heading', {level: 1, name: rootCode, exact: true})).toBeVisible({timeout: 30_000});

      // When I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 1, 1);

      // Then I should see the text "Add a new Color, Size"
      await expect(modal.getByText('Add a new Color, Size', {exact: true})).toBeVisible({timeout: 15_000});
      const colorField = axisField(modal, 'Color');
      const sizeField = axisField(modal, 'Size');
      await expect(colorField).toBeVisible({timeout: 15_000});
      await expect(sizeField).toBeVisible({timeout: 15_000});
      const skuInput = modal.getByLabel('SKU', {exact: true});
      await expect(skuInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | SKU | amor_black_xl | Color | Black | Size | XL |
      await pickAxisOption(colorField, 'Black');
      await pickAxisOption(sizeField, 'XL');
      await expect(colorField.locator('.select2-chosen')).toHaveText('Black', {timeout: 15_000});
      await skuInput.fill(sku, {timeout: 10_000});
      await expect(skuInput).toHaveValue(sku, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product');

      // Then I should be on the product "amor_black_xl" edit page
      const uuid = await expectOnNewProductPage(page, created, sku);

      // Persistence: a variant product of the root, with the two axis values.
      const {entity, text} = await readEntity(page, 'product', uuid);
      expect(entity.identifier, text).toBe(sku);
      expect(entity.family, text).toBe('clothing');
      expect(entity.parent, text).toBe(rootCode);
      expect(String(valueData(entity, 'color') ?? '').toLowerCase(), text).toBe('black');
      expect(String(valueData(entity, 'size') ?? '').toLowerCase(), text).toBe('xl');
    } finally {
      await cleanUpProductModelTree(page, rootId);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:81
   *   "Successfully add a new variant product when I already am on a variant product"
   *
   * Adaptations:
   * - "1111111121" becomes the disposable 3-tier tree of the feature:56 test above (root, sub product model
   *   color=blue, variant product size=m). The new variant product takes size "xl" (label XL,
   *   attribute_options.csv:101), free among the siblings.
   * - Field order: Size before the SKU.
   * - Not in Behat: the new variant product is re-read through the API, and its parent is the blue sub product model.
   *
   * What it covers: on a level-2 variant product, opening the level-2 selector makes getEntityParentCode take the
   * `same level` branch and answer the product's parent, so the new product is a sibling of the current page.
   *
   * Selectors: openAddChildModal with two containers and level 2 = the second, then those of the first test.
   */
  test('adds a variant product from a variant product page', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_vp2_root_${ts}`;
    const blueCode = `pw_addchild_vp2_blue_${ts}`;
    const existingSku = `pw_addchild_vp2_blue_m_${ts}`;
    const sku = `pw_addchild_vp2_blue_xl_${ts}`;
    let rootId: string | null = null;

    try {
      rootId = await createRootProductModel(page, rootCode, FAMILY_VARIANT);
      await createBlueSubProductModel(page, blueCode, rootCode);
      const productUuid = await createVariantProduct(page, existingSku, blueCode, {size: 'm'});

      // When I am on the "1111111121" product page
      await nav.goToEntityPage('product', productUuid);
      await expect(page.getByRole('heading', {level: 1, name: existingSku, exact: true})).toBeVisible({
        timeout: 30_000,
      });

      // And I open the variant navigation children selector for level 2
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 2, 2);

      // Then I should see the text "Add a new Size"
      await expect(modal.getByText('Add a new Size', {exact: true})).toBeVisible({timeout: 15_000});
      const sizeField = axisField(modal, 'Size');
      await expect(sizeField).toBeVisible({timeout: 15_000});
      const skuInput = modal.getByLabel('SKU', {exact: true});
      await expect(skuInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | SKU | apollon_blue_xl | Size (variant axis) | XL |
      await pickAxisOption(sizeField, 'XL');
      await skuInput.fill(sku, {timeout: 10_000});
      await expect(skuInput).toHaveValue(sku, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product');

      // Then I should be on the product "apollon_blue_xl" edit page
      const uuid = await expectOnNewProductPage(page, created, sku);

      // Persistence: a sibling of the starting product, under the blue sub product model, with size xl.
      const {entity, text} = await readEntity(page, 'product', uuid);
      expect(entity.identifier, text).toBe(sku);
      expect(entity.family, text).toBe('clothing');
      expect(entity.parent, text).toBe(blueCode);
      expect(String(valueData(entity, 'size') ?? '').toLowerCase(), text).toBe('xl');
    } finally {
      await cleanUpProductModelTree(page, rootId);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:92
   *   "Successfully add a variant product with many axes to a root product model"
   *
   * Adaptations:
   * - Structure: createManyAxesStructure with a ONE-level family variant whose axes are size, material, weight and
   *   handmade, sku among its attributes (the modal only offers a SKU field when the identifier belongs to the
   *   family variant's attributes, add-child/fields-container.js:72-88).
   * - Axes: reference_color dropped, for the reason given in the feature:26 test. The title loses
   *   ", Reference color".
   * - Not in Behat: the posted payload and the re-read variant product carry the four axis values.
   *
   * Selectors: those of the feature:26 test, plus getByLabel('SKU').
   */
  test('adds a variant product with many axes to a root product model', async ({page}) => {
    const ts = Date.now();
    const structure: ManyAxesStructure = {};
    const rootCode = `pw_addchild_many_vp_root_${ts}`;
    const sku = `pw_addchild_many_vp_${ts}`;
    let rootId: string | null = null;

    try {
      // Given the following attribute: handmade, the clothing family holding it, and the following family variant
      await createManyAxesStructure(page, ts, structure, handmadeCode => [
        {
          level: 1,
          axes: ['size', 'material', 'weight', handmadeCode],
          attributes: ['size', 'material', 'weight', handmadeCode, 'sku'],
        },
      ]);
      // And the following root product model: root_model
      rootId = await createRootProductModel(page, rootCode, structure.familyVariantCode!);

      // When I am on the "root_model" product model page
      await nav.goToEntityPage('product model', rootId);
      await expect(page.getByRole('heading', {level: 1, name: rootCode, exact: true})).toBeVisible({timeout: 30_000});

      // And I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 1, 1);

      // Then I should see the text "Add a new Handmade, Material, Reference color, Size, Weight"
      await expect(modal.getByText('Add a new Handmade, Material, Size, Weight', {exact: true})).toBeVisible({
        timeout: 15_000,
      });
      const materialField = axisField(modal, 'Material');
      const sizeField = axisField(modal, 'Size');
      const handmadeField = axisField(modal, 'Handmade');
      const weightField = axisField(modal, 'Weight');
      for (const field of [materialField, sizeField, handmadeField, weightField]) {
        await expect(field).toBeVisible({timeout: 15_000});
      }
      const skuInput = modal.getByLabel('SKU', {exact: true});
      await expect(skuInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: Material leather, Size XL, Weight 800 GRAM, Handmade Yes
      await pickAxisOption(materialField, 'leather');
      await pickAxisOption(sizeField, 'XL');
      await switchAxisOn(handmadeField);
      await fillMetricAxis(weightField, '800', 'GRAM');
      // Every value survived the rebuilds of the later changes.
      await expect(materialField.locator('.select2-chosen')).toHaveText('leather', {timeout: 15_000});
      await expect(sizeField.locator('.select2-chosen')).toHaveText('XL', {timeout: 15_000});
      await expect(handmadeField.locator('input[type="checkbox"]')).toBeChecked({timeout: 15_000});
      // ... and | SKU | tshirt_with_five_axes |
      await skuInput.fill(sku, {timeout: 10_000});
      await expect(skuInput).toHaveValue(sku, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product');
      expect(valueData(created.payload, 'weight'), JSON.stringify(created.payload)).toEqual({
        amount: '800',
        unit: 'GRAM',
      });

      // Then I should be on the product "tshirt_with_five_axes" edit page
      const uuid = await expectOnNewProductPage(page, created, sku);

      // Persistence: a variant product of the root with the four axis values.
      const {entity, text} = await readEntity(page, 'product', uuid);
      expect(entity.identifier, text).toBe(sku);
      expect(entity.family, text).toBe(structure.familyCode);
      expect(entity.parent, text).toBe(rootCode);
      expect(String(valueData(entity, 'size') ?? '').toLowerCase(), text).toBe('xl');
      expect(String(valueData(entity, 'material') ?? '').toLowerCase(), text).toBe('leather');
      expect(valueData(entity, structure.attributeCode!), text).toBe(true);
      expectWeight(entity, text);
    } finally {
      await cleanUpProductModelTree(page, rootId);
      await cleanUpManyAxesStructure(page, structure);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:122
   *   "I cannot add a sub product model without code"
   *
   * Adaptations: "apollon" becomes a disposable clothing_color_size root, as in the feature:15 test. Not in Behat:
   * the create answers 400 with the message, the modal stays open, and the root still has no child.
   *
   * The message is product_model.code.not_blank.message (Enrichment validators.en_US.yml:34), raised on `code`;
   * expectChildRefused traces where the modal shows it.
   */
  test('refuses a sub product model without code', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_nocode_root_${ts}`;
    let rootId: string | null = null;

    try {
      rootId = await createRootProductModel(page, rootCode, FAMILY_VARIANT);

      // Given I am on the "apollon" product model page
      await nav.goToEntityPage('product model', rootId);
      await expect(page.getByRole('heading', {level: 1, name: rootCode, exact: true})).toBeVisible({timeout: 30_000});

      // When I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 1, 1);

      // Then I should see the text "Add a new Color"
      await expect(modal.getByText('Add a new Color', {exact: true})).toBeVisible({timeout: 15_000});
      const colorField = axisField(modal, 'Color');
      await expect(colorField).toBeVisible({timeout: 15_000});
      const codeInput = modal.getByLabel('Code (required)', {exact: true});
      await expect(codeInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | Color (variant axis) | Black |
      await pickAxisOption(colorField, 'Black');
      await expect(codeInput).toHaveValue('', {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product_model');

      // Then I should see the text "The product model code must not be empty."
      await expectChildRefused(created, modal, 'The product model code must not be empty.');
      await expectChildren(page, rootId, []);
    } finally {
      await cleanUpProductModelTree(page, rootId);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:132
   *   "I cannot add a sub product model without axis value"
   *
   * Adaptations: as in the feature:122 test; the Behat title check "Add a new color" was case-insensitive (Mink's
   * pageTextContains), the rendered title is "Add a new Color". Not in Behat: the 400 body, the open modal, the root
   * still childless.
   *
   * The message is pim_catalog.constraint.can_have_family_variant_empty_axis_value (validators.en_US.yml:60), raised
   * by NotEmptyVariantAxesValidator:57-59 at path `attribute`.
   */
  test('refuses a sub product model without axis value', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_noaxis_root_${ts}`;
    const newCode = `pw_addchild_noaxis_pm_${ts}`;
    let rootId: string | null = null;

    try {
      rootId = await createRootProductModel(page, rootCode, FAMILY_VARIANT);

      // Given I am on the "apollon" product model page
      await nav.goToEntityPage('product model', rootId);
      await expect(page.getByRole('heading', {level: 1, name: rootCode, exact: true})).toBeVisible({timeout: 30_000});

      // When I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 1, 1);

      // Then I should see the text "Add a new color"
      await expect(modal.getByText('Add a new Color', {exact: true})).toBeVisible({timeout: 15_000});
      const codeInput = modal.getByLabel('Code (required)', {exact: true});
      await expect(codeInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | Code (required) | apollon_black |
      await codeInput.fill(newCode, {timeout: 10_000});
      await expect(codeInput).toHaveValue(newCode, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product_model');

      // Then I should see the text "Attribute "color" cannot be empty, as it is defined as an axis for this entity"
      await expectChildRefused(
        created,
        modal,
        'Attribute "color" cannot be empty, as it is defined as an axis for this entity'
      );
      await expectChildren(page, rootId, []);
    } finally {
      await cleanUpProductModelTree(page, rootId);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:142
   *   "I cannot add a variant product without axis value"
   *
   * Adaptations: "apollon_blue" becomes a disposable clothing_color_size root and its level-1 sub product model with
   * color=blue, as in the first test of this file. The Behat title check "Add a new size" was case-insensitive.
   * Not in Behat: the 400 body, the open modal, and the sub product model still has no variant product.
   *
   * Selectors: openAddChildModal with two containers (sub product model page) and level 2 = the second.
   */
  test('refuses a variant product without axis value', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_vpnoaxis_root_${ts}`;
    const blueCode = `pw_addchild_vpnoaxis_blue_${ts}`;
    const sku = `pw_addchild_vpnoaxis_${ts}`;
    let rootId: string | null = null;

    try {
      rootId = await createRootProductModel(page, rootCode, FAMILY_VARIANT);
      const blueId = await createBlueSubProductModel(page, blueCode, rootCode);

      // Given I am on the "apollon_blue" product model page
      await nav.goToEntityPage('product model', blueId);
      await expect(page.getByRole('heading', {level: 1, name: blueCode, exact: true})).toBeVisible({timeout: 30_000});

      // When I open the variant navigation children selector for level 2
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 2, 2);

      // Then I should see the text "Add a new size"
      await expect(modal.getByText('Add a new Size', {exact: true})).toBeVisible({timeout: 15_000});
      const skuInput = modal.getByLabel('SKU', {exact: true});
      await expect(skuInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | SKU | apollon_black_xl |
      await skuInput.fill(sku, {timeout: 10_000});
      await expect(skuInput).toHaveValue(sku, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product');

      // Then I should see the text "Attribute "size" cannot be empty, as it is defined as an axis for this entity"
      await expectChildRefused(
        created,
        modal,
        'Attribute "size" cannot be empty, as it is defined as an axis for this entity'
      );
      await expectChildren(page, blueId, []);
    } finally {
      await cleanUpProductModelTree(page, rootId);
    }
  });

  /**
   * Replaced Behat scenario (deleted with this test): add_product_model_children.feature:152
   *   "I cannot add a sub product model with an already existing axis value combination"
   *
   * Adaptations: "apollon" and its child "apollon_blue" become a disposable clothing_color_size root and its
   * level-1 sub product model with color=blue. The expected message therefore names the disposable codes instead of
   * "apollon_new_blue" and "apollon_blue" (UniqueVariantAxisValidator::addViolation: %validated_entity% is the code
   * being created, %sibling_with_same_value% the sibling's). Uniqueness is checked among siblings only, so the fresh
   * root isolates the test. Not in Behat: the 400 body, the open modal, and the root still has only its blue child.
   *
   * The message is pim_catalog.constraint.product_model_with_same_axis_value_already_exists
   * (validators.en_US.yml:61), raised at path `attribute` (UniqueVariantAxisValidator:186-191).
   */
  test('refuses a sub product model whose axis value combination already exists', async ({page}) => {
    const ts = Date.now();
    const rootCode = `pw_addchild_dup_root_${ts}`;
    const blueCode = `pw_addchild_dup_blue_${ts}`;
    const newCode = `pw_addchild_dup_new_blue_${ts}`;
    let rootId: string | null = null;

    try {
      rootId = await createRootProductModel(page, rootCode, FAMILY_VARIANT);
      await createBlueSubProductModel(page, blueCode, rootCode);

      // Given I am on the "apollon" product model page
      await nav.goToEntityPage('product model', rootId);
      await expect(page.getByRole('heading', {level: 1, name: rootCode, exact: true})).toBeVisible({timeout: 30_000});

      // When I open the variant navigation children selector for level 1
      // And I press the "Add new" button and wait for modal
      const modal = await openAddChildModal(page, 1, 1);

      // Then I should see the text "Add a new color"
      await expect(modal.getByText('Add a new Color', {exact: true})).toBeVisible({timeout: 15_000});
      const colorField = axisField(modal, 'Color');
      await expect(colorField).toBeVisible({timeout: 15_000});
      const codeInput = modal.getByLabel('Code (required)', {exact: true});
      await expect(codeInput).toBeVisible({timeout: 15_000});

      // When I fill in the following child information: | Code (required) | apollon_new_blue | Color | Blue |
      await pickAxisOption(colorField, 'Blue');
      await codeInput.fill(newCode, {timeout: 10_000});
      await expect(codeInput).toHaveValue(newCode, {timeout: 5_000});

      // And I confirm the child creation
      const created = await confirmChildCreation(page, modal, 'product_model');

      // Then I should see the text "Cannot set value "[blue]" for the attribute axis "color" on product model ..."
      await expectChildRefused(
        created,
        modal,
        `Cannot set value "[blue]" for the attribute axis "color" on product model "${newCode}", ` +
          `as the product model "${blueCode}" already has this value`
      );
      await expectChildren(page, rootId, [blueCode]);
    } finally {
      await cleanUpProductModelTree(page, rootId);
    }
  });
});
