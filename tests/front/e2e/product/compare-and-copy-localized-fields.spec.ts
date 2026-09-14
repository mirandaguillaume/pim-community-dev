import {test, expect} from '../fixtures/coverage-fixture';
import type {Page} from '../fixtures/coverage-fixture';
import {
  login,
  waitForLoadingMasks,
  createAttributeViaApi,
  createProductViaApi,
  getProductViaApi,
  deleteAttributeViaApi,
} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product/pef/compare_and_copy_localized_fields.feature:37
 *   "Successfully copy current tab compared product localized values"
 *
 * Adaptations:
 *
 * - Catalog ("Given a "apparel" catalog configuration"): CI runs icecat_demo_dev, whose `name`
 *   attribute is not localizable, whose `description` is WYSIWYG-enabled (it renders a
 *   .note-editable, not a <textarea>) and which has no `legend` attribute
 *   (icecat_demo_dev/attributes.csv). The apparel structure is therefore mirrored with disposable
 *   entities created in beforeAll through the internal REST API (the image-upload-pef.spec.ts
 *   pattern), so the test page logs in afterwards with cold attribute/attribute-group fetchers:
 *     - two attribute groups standing in for apparel "General"/"Media"
 *       (PUT /rest/attribute-group/, AttributeGroupController::createAction). The pim.ts helper
 *       only sends {code}, so a local helper also sends labels.en_US for the group selector label;
 *     - three attributes mirroring apparel attributes.csv: name (text, localizable), description
 *       (textarea, localizable + scopable, wysiwyg_enabled left null so WysiwygFieldProvider does
 *       not apply and textarea-field.js renders a plain <textarea>), legend (text, localizable, in
 *       the "Media" stand-in group). Created SEQUENTIALLY (concurrent attribute creation into one
 *       group returns 500);
 *     - a family standing in for "tshirts" (POST /configuration/rest/family/). A family is needed:
 *       FillMissingProductValues::fromStandardFormat (called by the internal_api ProductNormalizer)
 *       only emits the null en_US `name` value that copy.js::copy() writes into when the product
 *       has a family. Localizable-only attributes get every activated locale
 *       (FillMissingProductValues.php:98-101); scopable+localizable attributes get each channel's
 *       own locales (:103-111), and icecat's ecommerce channel includes en_US
 *       (icecat_demo_dev/channels.csv:4). Without that entry AttributeManager.getValue returns
 *       undefined and copy() silently skips the field;
 *     - the product with the Background values: POST /enrich/product/rest (identifier + family),
 *       then POST /enrich/product/rest/{uuid} with the values (UpdateProductController, XHR only).
 *       name en_US is left unset, exactly as in Behat.
 * - "I am logged in as "Mary"": replaced by admin. CE has no locale/category permission on this
 *   flow and admin's context matches Mary's (catalog locale en_US, catalog scope ecommerce,
 *   icecat_demo_dev/users.csv). The main scope switcher is asserted to show "Ecommerce", which
 *   replaces the 'I switch the scope to "ecommerce"' sub-step of the Description assertion.
 * - Added at the end (not in Behat, which never saves): the product is saved and re-read through
 *   GET /enrich/product/rest/{uuid} to prove the copied values reached the form model and that
 *   Legend was NOT written.
 *
 * Selectors traced from source:
 * - Attribute fields: product/field/field.js:18-22 sets data-attribute=<code> on the field root.
 *   The main input lives under field.html:1 `.original-field` -> :20 `.field-input`. CopyField
 *   extends Field (copy-field.js:8) and is built from the same attribute (copy.js:129), so it also
 *   carries data-attribute; its root is appended by field.js renderElements into
 *   field.html:26 `.comparison-elements-container`, a SIBLING of `.original-field` under the outer
 *   root. `[data-attribute=X] > .original-field` (child combinator) is therefore the main value
 *   and `[data-attribute=X] .copy-container` (copy-field.html:1) the compared value.
 * - Group selector: attribute-group-selector.js:14 className `group-selector`, inside
 *   attributes.js:84 `object-attributes`; attribute-group-selector.html:1 [data-toggle=dropdown]
 *   toggle, :3 `.AknActionButton-highlight` current label, :11 li[data-element=<code>]
 *   (group-selector.js:18-20,88-90). Behat: Form.php::visitGroup/openGroupSelector, which spin.
 * - Collapse column: form/column.html:5 `.AknColumn-collapseButton`, column.js:95-106 adds
 *   `AknColumn--collapsed` (WebUser.php::iCollapseTheColumn clicks every visible button).
 * - Comparison panel: secondary-actions.html:1 `.AknSecondaryActions-button` (no text/role),
 *   start-copy.js:11 `.start-copying` with label "Compare / Translate"
 *   (Enrichment jsmessages.en_US.yml:58). Panel = copy.js:15 `.attribute-copy-actions`;
 *   copy.html:5-17 selection dropdown ("All visible") and "Copy" button; copy-field.html:2
 *   `.copy-field-selector` checkbox.
 * - Comparison locale: the copy panel's locale-switcher.tsx -> shared LocaleSelector.tsx ->
 *   DSM SwitcherButton (<button> labelled "Locale:"); the menu is portalled into #dropdown-root
 *   (Dropdown/Overlay.tsx:141) with role=listbox (ItemCollection.tsx:92). Same targets as
 *   ReactContextSwitcherDecorator::switchLocale. The copy panel's scope switcher is an <a>
 *   (scope-switcher.html:1), so the Locale button is the only button in the panel.
 * - Main scope: default-template.html:28 `.AknTitleContainer-context`, scope-switcher.js:11
 *   `.scope-switcher`, scope-switcher.html:3 `.AknActionButton-highlight`.
 *
 * Timing: every locale switch / selection / copy / group change re-renders attributes.js, which
 * empties the tab synchronously (attributes.js:153), refills FieldManager.visibleFields
 * asynchronously (:159, :263) and drops re-render requests while one is in flight (:145). Each
 * action is therefore gated on a retrying assertion of the RE-RENDERED DOM (values that only
 * exist after the render), never on the disappearance of old nodes.
 */

const ts = Date.now();
const GROUP_MAIN = `pw_cmp_general_${ts}`;
const GROUP_MAIN_LABEL = `PW General ${ts}`;
const GROUP_MEDIA = `pw_cmp_media_${ts}`;
const GROUP_MEDIA_LABEL = `PW Media ${ts}`;
const NAME = `pw_cmp_name_${ts}`;
const DESCRIPTION = `pw_cmp_description_${ts}`;
const LEGEND = `pw_cmp_legend_${ts}`;
const FAMILY = `pw_cmp_tshirts_${ts}`;
const SKU = `pw-cmp-${ts}`;

const XHR = {'X-Requested-With': 'XMLHttpRequest'};
const JSON_XHR = {'Content-Type': 'application/json', ...XHR};

let productUuid: string | null = null;

async function describeResponse(resp: {status(): number; json(): Promise<unknown>}): Promise<string> {
  return `${resp.status()} ${JSON.stringify(await resp.json().catch(() => null))}`;
}

async function createAttributeGroupWithLabelViaApi(page: Page, code: string, label: string) {
  return page.request.put('/rest/attribute-group/', {
    data: {code, labels: {en_US: label}},
    headers: JSON_XHR,
  });
}

async function createFamilyViaApi(page: Page, code: string, label: string, attributeCodes: string[]) {
  return page.request.post('/configuration/rest/family/', {
    data: {code, labels: {en_US: label}, attributes: attributeCodes},
    headers: JSON_XHR,
  });
}

type InternalValue = {locale: string | null; scope: string | null; data: unknown};

async function setProductValuesViaApi(page: Page, uuid: string, values: Record<string, InternalValue[]>) {
  return page.request.post(`/enrich/product/rest/${uuid}`, {
    data: {values},
    headers: JSON_XHR,
  });
}

function originalInput(page: Page, code: string, tag: 'input' | 'textarea' = 'input') {
  return page.locator(`[data-attribute="${code}"] > .original-field .field-input ${tag}`);
}

function copyInput(page: Page, code: string, tag: 'input' | 'textarea' = 'input') {
  return page.locator(`[data-attribute="${code}"] .copy-container .field-input ${tag}`);
}

function copySelector(page: Page, code: string) {
  return page.locator(`[data-attribute="${code}"] .copy-field-selector`);
}

/**
 * Form.php::visitGroup + openGroupSelector: open the attribute group dropdown (only when it is not
 * already open, a second toggle click would close it) and pick the group by code. Retried because
 * the selector re-renders itself asynchronously (attribute-group-selector.js:84-113) after any
 * attributes.js render, which can swap the menu under a pending click.
 */
async function visitAttributeGroup(page: Page, groupCode: string, groupLabel: string) {
  await waitForLoadingMasks(page);
  const selector = page.locator('.object-attributes .group-selector');
  await expect(async () => {
    const isOpen = await selector.evaluate(el => el.classList.contains('open'), undefined, {timeout: 3_000});
    if (!isOpen) {
      await selector.locator('[data-toggle="dropdown"]').click({timeout: 3_000});
    }
    await selector.locator(`li[data-element="${groupCode}"]`).click({timeout: 3_000});
    await expect(selector.locator('.AknActionButton-highlight')).toHaveText(groupLabel, {timeout: 3_000});
  }).toPass({timeout: 30_000});
}

function findValue(values: InternalValue[] | undefined, locale: string, scope: string | null) {
  return (values ?? []).find(v => v.locale === locale && v.scope === scope);
}

test.beforeAll(async ({browser}) => {
  const page = await browser.newPage();
  await login(page, 'admin', 'admin');

  for (const [code, label] of [
    [GROUP_MAIN, GROUP_MAIN_LABEL],
    [GROUP_MEDIA, GROUP_MEDIA_LABEL],
  ]) {
    const resp = await createAttributeGroupWithLabelViaApi(page, code, label);
    expect(resp.ok(), `Create attribute group ${code} failed: ${await describeResponse(resp)}`).toBeTruthy();
  }

  const attributes = [
    {code: NAME, type: 'pim_catalog_text', group: GROUP_MAIN, localizable: true, scopable: false, label: 'Name'},
    {
      code: DESCRIPTION,
      type: 'pim_catalog_textarea',
      group: GROUP_MAIN,
      localizable: true,
      scopable: true,
      label: 'Description',
    },
    {code: LEGEND, type: 'pim_catalog_text', group: GROUP_MEDIA, localizable: true, scopable: false, label: 'Legend'},
  ];
  for (const {label, ...attribute} of attributes) {
    const resp = await createAttributeViaApi(page, {...attribute, labels: {en_US: `PW ${label} ${ts}`}});
    expect(resp.ok(), `Create attribute ${attribute.code} failed: ${await describeResponse(resp)}`).toBeTruthy();
  }

  const familyResp = await createFamilyViaApi(page, FAMILY, `PW T-shirts ${ts}`, [NAME, DESCRIPTION, LEGEND]);
  expect(familyResp.ok(), `Create family ${FAMILY} failed: ${await describeResponse(familyResp)}`).toBeTruthy();

  const productResp = await createProductViaApi(page, SKU, FAMILY);
  expect(productResp.ok(), `Create product ${SKU} failed: ${await describeResponse(productResp)}`).toBeTruthy();
  const createdProduct = await productResp.json();
  productUuid = createdProduct.meta?.id ?? null;
  expect(productUuid, `Create product response had no meta.id: ${JSON.stringify(createdProduct)}`).toBeTruthy();

  const valuesResp = await setProductValuesViaApi(page, productUuid!, {
    [NAME]: [{locale: 'fr_FR', scope: null, data: 'Floup'}],
    [LEGEND]: [
      {locale: 'en_US', scope: null, data: 'Front view'},
      {locale: 'fr_FR', scope: null, data: 'Vue de face'},
    ],
    [DESCRIPTION]: [
      {locale: 'en_US', scope: 'ecommerce', data: 'City shoes'},
      {locale: 'fr_FR', scope: 'ecommerce', data: 'Chaussures de ville'},
    ],
  });
  expect(valuesResp.ok(), `Set values on product ${SKU} failed: ${await describeResponse(valuesResp)}`).toBeTruthy();

  await page.close();
});

test.afterAll(async ({browser}) => {
  // Best-effort cleanup, in dependency order. Every call is isolated so one failure does not
  // stop the others. The product delete needs the XHR header (ProductController::removeAction
  // redirects otherwise, and pim.ts deleteProductViaApi does not send it); the family delete is
  // refused while a product still uses it. The attribute-group delete only LAUNCHES the
  // delete_attribute_groups job (AttributeGroupController::removeAction returns 204 at once), so
  // its effect depends on the job consumer and is never asserted.
  const page = await browser.newPage();
  const attempt = async (action: () => Promise<unknown>) => {
    try {
      await action();
    } catch (e) {
      console.warn(`[cleanup] ${(e as Error).message}`);
    }
  };

  await attempt(() => login(page, 'admin', 'admin'));
  if (productUuid) {
    await attempt(() => page.request.delete(`/enrich/product/rest/${productUuid}`, {headers: XHR}));
  }
  await attempt(() => page.request.delete(`/configuration/rest/family/${FAMILY}`, {headers: XHR}));
  for (const code of [LEGEND, DESCRIPTION, NAME]) {
    await attempt(() => deleteAttributeViaApi(page, code));
  }
  for (const code of [GROUP_MAIN, GROUP_MEDIA]) {
    await attempt(() => page.request.delete(`/rest/attribute-group/${code}`, {headers: XHR}));
  }
  await page.close();
});

test('Successfully copy current tab compared product localized values', async ({page}) => {
  expect(productUuid, 'beforeAll did not create the product').toBeTruthy();
  await login(page, 'admin', 'admin');

  // Given I am on the "tshirt" product page
  const productLoaded = page.waitForResponse(
    r => /\/enrich\/product(-model)?\/rest\//.test(r.url()) && r.status() === 200
  );
  await page.goto(`/#/enrich/product/${productUuid}`);
  await productLoaded;
  await waitForLoadingMasks(page);
  await expect(originalInput(page, NAME)).toBeVisible({timeout: 30_000});
  await expect(page.locator('.AknTitleContainer-context .scope-switcher .AknActionButton-highlight')).toHaveText(
    'Ecommerce'
  );

  // And I visit the "General" group
  await visitAttributeGroup(page, GROUP_MAIN, GROUP_MAIN_LABEL);
  await expect(originalInput(page, NAME)).toBeVisible({timeout: 15_000});
  await expect(originalInput(page, DESCRIPTION, 'textarea')).toBeVisible({timeout: 15_000});
  await expect(page.locator(`[data-attribute="${LEGEND}"]`)).toHaveCount(0, {timeout: 15_000});

  // Baseline: prove the copy is what changes the en_US / ecommerce values below.
  await expect(originalInput(page, NAME)).toHaveValue('');
  await expect(originalInput(page, DESCRIPTION, 'textarea')).toHaveValue('City shoes');

  // And I collapse the column
  const collapseButtons = page.locator('.AknColumn-collapseButton:visible');
  await expect(collapseButtons.first()).toBeVisible({timeout: 15_000});
  // Element handles, not locator.all(): all() yields nth(i) locators re-resolved against
  // `:visible` at click time, which would shift if a click changed which buttons are visible.
  for (const button of await collapseButtons.elementHandles()) {
    await button.click({timeout: 10_000});
  }
  await expect(page.locator('.AknColumn.AknColumn--collapsed').first()).toBeAttached({timeout: 15_000});

  // When I open the comparison panel
  const secondaryActions = page.locator('.secondary-actions').filter({has: page.locator('.start-copying')});
  await secondaryActions.locator('.AknSecondaryActions-button').click();
  await secondaryActions.getByText('Compare / Translate', {exact: true}).click();
  const copyPanel = page.locator('.attribute-copy-actions');
  await expect(copyPanel.getByText('Copy', {exact: true})).toBeVisible({timeout: 15_000});
  await expect(page.locator(`[data-attribute="${NAME}"] .copy-container`)).toBeVisible({timeout: 15_000});

  // And I switch the comparison locale to "fr_FR"
  await copyPanel.getByRole('button', {name: /Locale/}).click();
  const localeList = page.locator('#dropdown-root').getByRole('listbox');
  await expect(localeList).toBeVisible({timeout: 10_000});
  await localeList.getByText('French (France)').click();
  // Sync on the re-render: the compared fields now show the fr_FR values.
  await expect(copyInput(page, NAME)).toHaveValue('Floup', {timeout: 15_000});
  await expect(copyInput(page, DESCRIPTION, 'textarea')).toHaveValue('Chaussures de ville', {timeout: 15_000});

  // And I select all visible translations
  // ComparisonPanelDecorator::selectElements: open the selection dropdown only when it is not
  // already open (the AknDropdown-menuTitle intercepts a second toggle click during its fadeIn),
  // click "All visible", retry until the selection shows. Both checkboxes are asserted inside the
  // retry: a click landing while visibleFields is still refilling selects only part of the group,
  // and re-clicking is safe because selectFields() starts with unselectAll().
  const selection = copyPanel.locator('.selection-dropdown');
  await expect(async () => {
    const isOpen = await selection.evaluate(el => el.classList.contains('open'), undefined, {timeout: 3_000});
    if (!isOpen) {
      await selection.locator('[data-toggle="dropdown"]').click({timeout: 3_000});
    }
    await selection.getByText('All visible', {exact: true}).click({timeout: 3_000});
    await expect(copySelector(page, NAME)).toBeChecked({timeout: 5_000});
    await expect(copySelector(page, DESCRIPTION)).toBeChecked({timeout: 5_000});
  }).toPass({timeout: 45_000});

  // And I copy selected translations
  await copyPanel.getByText('Copy', {exact: true}).click();
  // Cheap check mirroring ComparisonPanelDecorator::copySelectedElements. It is NOT a render gate:
  // attributes.js:153 removes every checkbox synchronously. The retrying value assertions below
  // are the real post-copy sync (those values only exist once the re-render has finished).
  await expect(page.locator('.copy-field-selector:checked')).toHaveCount(0, {timeout: 15_000});

  // Then the product Name should be "Floup"
  await expect(originalInput(page, NAME)).toHaveValue('Floup', {timeout: 15_000});
  // And the product Description for scope "ecommerce" should be "Chaussures de ville"
  await expect(originalInput(page, DESCRIPTION, 'textarea')).toHaveValue('Chaussures de ville', {timeout: 15_000});

  // And I visit the "Media" group
  await visitAttributeGroup(page, GROUP_MEDIA, GROUP_MEDIA_LABEL);
  await expect(originalInput(page, LEGEND)).toBeVisible({timeout: 15_000});
  await expect(page.locator(`[data-attribute="${NAME}"]`)).toHaveCount(0, {timeout: 15_000});

  // And the product Legend should be "Front view"
  // "All visible" only selected the General group's fields (attributes.js:462-471 filterValues +
  // :263 addVisibleField), so Legend keeps its en_US value while its fr_FR value is still compared.
  await expect(originalInput(page, LEGEND)).toHaveValue('Front view', {timeout: 15_000});
  await expect(copyInput(page, LEGEND)).toHaveValue('Vue de face', {timeout: 15_000});

  // Added: the copied values reach the saved product, and Legend was not written.
  const saveResponse = page.waitForResponse(
    r => r.url().includes(`/enrich/product/rest/${productUuid}`) && r.request().method() === 'POST'
  );
  await page.getByRole('button', {name: 'Save', exact: true}).click();
  const saved = await saveResponse;
  expect(saved.ok(), `Save failed: ${saved.status()} ${await saved.text().catch(() => '')}`).toBeTruthy();

  const product = await getProductViaApi(page, productUuid!);
  const values = product.values ?? {};
  expect(
    findValue(values[NAME], 'en_US', null)?.data,
    `Unexpected ${NAME} values: ${JSON.stringify(values[NAME])}`
  ).toBe('Floup');
  expect(
    findValue(values[DESCRIPTION], 'en_US', 'ecommerce')?.data,
    `Unexpected ${DESCRIPTION} values: ${JSON.stringify(values[DESCRIPTION])}`
  ).toBe('Chaussures de ville');
  expect(
    findValue(values[LEGEND], 'en_US', null)?.data,
    `Unexpected ${LEGEND} values: ${JSON.stringify(values[LEGEND])}`
  ).toBe('Front view');
});
