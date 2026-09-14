import type {Request} from '@playwright/test';
import {test, expect, Page} from '../fixtures/coverage-fixture';
import {login, createCategoryViaApi} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/category/edit_a_category.feature:11
 *   "Successfully edit a category"
 *
 * Adaptations:
 * - Catalog: Behat edits the footwear fixture category "Sandals". CI runs icecat_demo_dev, so this
 *   spec creates a disposable sub-category `pw_edit_cat_<ts>` under the icecat root tree `master`
 *   (icecat_demo_dev/categories.csv:2 `master;;Master catalog`, empty parent = root). It is created
 *   through POST /enrich/product-category-tree/create (createCategoryViaApi). `master` is
 *   hard-coded instead of getFirstRootCategoryCode(): GET /enrich/category/rest lists roots in no
 *   guaranteed order, so list[0] can be `sales` or a disposable root tree left by another spec.
 *   No new root tree is created either: API-created roots are not reliably granted to users.
 *   The create call answers 201 with an empty JSON object (JsonResponse(null) -> `{}`) and no id,
 *   so the numeric id the edit route needs is read from GET /enrich/category/rest/{code}
 *   (CategoryController::getAction -> InternalApi CategoryNormalizer adds `id`). A missing parent
 *   makes the create call fail with a 500 (uncaught InvalidPropertyException from the updater),
 *   and validator failures return a 400 violation map; either way the 201 check fails loudly.
 * - User: Behat logs in as Julia (catalog manager). This spec uses admin, like the rest of the
 *   suite: it holds every ACL the page checks (pim_enrich_product_category_edit for the page, the
 *   GET/POST /category/rest/{id} controllers and the editable label inputs). The scenario asserts
 *   no permission behaviour, so no assertion is lost.
 * - Label: 'My sandals' becomes `My sandals <ts>` so the text is unique per run, retry and shard.
 * - Added checks: the save response body and an independent API re-read must carry the new
 *   en_US label, and the unsaved-changes indicator must be gone. The new label already renders in
 *   the breadcrumb while typing (categoryLabel follows the edited state), so on its own the Behat
 *   text assertion does not prove the save.
 *
 * Selectors and flow traced from:
 * - "I edit the "Sandals" category": NavigationContext.php iAmOnTheEntityEditPage ->
 *   Page/Category/Edit.php `#/enrich/product-category-tree/{id}/edit` (NavigationHelper
 *   'category edit'). CategoriesApp.tsx routes `/:categoryId/edit` to CategoryEditPage wrapped in
 *   EditCategoryProvider. The page's default tab is Attributes when the user holds
 *   pim_enrich_product_category_edit_attributes (CategoryEditPage.tsx useSessionStorageState), so
 *   the Properties tab (pim_common.properties = "Properties", DSM TabBar role="tab") is clicked
 *   explicitly.
 * - Code field: EditPropertiesForm.tsx `<Field label="Code" requiredLabel="(required)"><TextInput
 *   name="code" readOnly>`. DSM Field wires the input with aria-labelledby, so its accessible
 *   name is "Code (required)"; the non-exact 'Code' name is pinned with name="code". "Disabled":
 *   Behat (AssertionContext.php) accepts disabled or readonly, and DSM TextInput renders
 *   `disabled={readOnly}`.
 * - "the input labelled 'English'": each label input's Field label is the locale label from
 *   GET /configuration/locale/rest?activated=true, which InternalApi LocaleNormalizer builds with
 *   \Locale::getDisplayName(code, uiLocale) = "English (United States)" for admin (en_US UI).
 *   icecat channels.csv activates fr_FR, de_DE and en_US only, so exactly one field matches.
 * - Save: CategoryEditPage.tsx `<Button level="primary">Save</Button>` -> saveEditCategoryForm.ts
 *   POST /category/rest/{id} -> UpdateCategoryController `{success: true, category}`.
 * - "Category successfully updated": useEditCategoryForm.ts notify(SUCCESS,
 *   pim_enrich.entity.category.content.edit.success) -> shared Notifications -> DSM MessageBar
 *   role="status", which closes itself after a few seconds. It is asserted right after the save.
 * - "My sandals": the label renders both in PageHeader.Title and in the last breadcrumb step, so
 *   the check is scoped to the DSM Breadcrumb `<nav aria-label="Breadcrumb">` to stay strict.
 *
 * Form reset race: EditCategoryProvider.tsx builds a new `locales` object on every render, and
 * useEditCategoryForm.ts re-initializes the edited category whenever `locales` changes identity.
 * It also re-initializes from the fetched category, which a save does not update. If a locales,
 * channels or category GET commits after the label was typed, the typed value is wiped. CI builds
 * the front in development mode and CategoriesApp.tsx wraps the app in <StrictMode>, so React runs
 * the mount effects twice and each of those GETs is sent twice (the Backbone shell can also request
 * the locale and channel lists). Waiting for one response per URL is therefore not a sync point.
 * Instead the spec tracks every in-flight GET to those three URLs and waits until none is pending
 * before touching the form. That wait is best-effort: the real guard is the toPass around
 * fill -> Save -> save-response label check. A wiped value shows up as the old label in the save
 * response and triggers a retry, and saving the same label twice is harmless. A non-ok save
 * response (400 violations, 500) is not retried: it fails the test at once with its body.
 */

async function getCategoryIdByCode(page: Page, code: string): Promise<number> {
  const resp = await page.request.get(`/enrich/category/rest/${code}`, {headers: XHR_HEADER});
  const body = await resp.json().catch(() => null);
  expect(resp.ok(), `Get category ${code} failed: ${resp.status()} ${JSON.stringify(body)}`).toBeTruthy();
  expect(body?.code, `Unexpected category payload: ${JSON.stringify(body)}`).toBe(code);
  expect(typeof body?.id, `No numeric id in ${JSON.stringify(body)}`).toBe('number');
  return body.id;
}

async function getEnrichedCategoryViaApi(page: Page, id: number): Promise<any> {
  const resp = await page.request.get(`/category/rest/${id}`, {headers: XHR_HEADER});
  const body = await resp.json().catch(() => null);
  expect(resp.ok(), `Get enriched category ${id} failed: ${resp.status()} ${JSON.stringify(body)}`).toBeTruthy();
  return body;
}

test.describe('Edit a category', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('successfully edits a category label from the Properties tab', async ({page}) => {
    const ts = Date.now();
    const code = `pw_edit_cat_${ts}`;
    const initialLabel = `PW edit category ${ts}`;
    const newLabel = `My sandals ${ts}`;

    const createResp = await createCategoryViaApi(page, code, 'master', initialLabel);
    expect(
      createResp.status(),
      `Create category ${code} failed: ${createResp.status()} ${await createResp.text()}`
    ).toBe(201);
    const id = await getCategoryIdByCode(page, code);

    // Register before navigating so no form-initializing GET is missed (see "Form reset race").
    const isFormInitGet = (r: Request): boolean => {
      if (r.method() !== 'GET') return false;
      const path = new URL(r.url()).pathname;
      return (
        path.endsWith('/configuration/locale/rest') ||
        path.endsWith('/configuration/channel/rest') ||
        path.endsWith(`/category/rest/${id}`)
      );
    };
    const pendingFormInitGets = new Set<Request>();
    const settle = (r: Request) => pendingFormInitGets.delete(r);
    page.on('request', r => {
      if (isFormInitGet(r)) pendingFormInitGets.add(r);
    });
    page.on('requestfinished', settle);
    page.on('requestfailed', settle);

    const nav = new NavigationHelper(page);
    await nav.goToEntityPage('category edit', String(id));

    const propertiesTab = page.getByRole('tab', {name: 'Properties', exact: true});
    await expect(propertiesTab).toBeVisible({timeout: 30_000});
    await propertiesTab.click();

    // Then I should see the Code field / And the field Code should be disabled
    const codeField = page.getByRole('textbox', {name: 'Code'});
    await expect(codeField).toBeVisible({timeout: 30_000});
    await expect(codeField).toHaveAttribute('name', 'code');
    await expect(codeField).toHaveValue(code);
    await expect(codeField).toBeDisabled();

    // When I fill the input labelled 'English' with 'My sandals' / And I press the "Save" button
    const englishField = page.getByRole('textbox', {name: 'English (United States)', exact: true});
    await expect(englishField).toBeEditable({timeout: 30_000});
    await expect(englishField).toHaveValue(initialLabel);
    // Best-effort: let every duplicated locales/channels/category GET commit before typing.
    await expect.poll(() => pendingFormInitGets.size, {timeout: 30_000}).toBe(0);

    const unsavedChanges = page.getByText('There are unsaved changes.');
    const saveButton = page.getByRole('button', {name: 'Save', exact: true});

    // Only a stale label in the save response is retried; a non-ok response is recorded and ends the loop.
    const saveOutcome: {failure?: string} = {};
    await expect(async () => {
      if (saveOutcome.failure) return;
      await englishField.fill(newLabel, {timeout: 5_000});
      await expect(englishField).toHaveValue(newLabel, {timeout: 2_000});
      await expect(unsavedChanges).toBeVisible({timeout: 2_000});

      // Promise.all keeps the waiter handled even if the click throws. The waiter gets most of the
      // loop budget so a slow but successful save is awaited instead of retried.
      const [resp] = await Promise.all([
        page.waitForResponse(
          r => r.request().method() === 'POST' && new URL(r.url()).pathname.endsWith(`/category/rest/${id}`),
          {timeout: 90_000}
        ),
        saveButton.click({timeout: 10_000}),
      ]);
      const body = await resp.json().catch(() => null);
      if (!resp.ok()) {
        saveOutcome.failure = `Save category ${code} failed: ${resp.status()} ${JSON.stringify(body)}`;
        return;
      }
      expect(
        body?.category?.properties?.labels?.en_US,
        `Save response did not carry the new label: ${JSON.stringify(body)}`
      ).toBe(newLabel);
    }).toPass({timeout: 150_000});
    expect(saveOutcome.failure, saveOutcome.failure).toBeUndefined();

    // Then I should see the text "Category successfully updated" (the flash closes after a few seconds).
    // .last(): a retried save can leave an earlier success flash on screen for a few seconds.
    await expect(page.getByRole('status').filter({hasText: 'Category successfully updated'}).last()).toBeVisible({
      timeout: 10_000,
    });

    // And I should see the text "My sandals"
    await expect(page.getByRole('navigation', {name: 'Breadcrumb'}).getByText(newLabel, {exact: true})).toBeVisible({
      timeout: 15_000,
    });
    await expect(unsavedChanges).toBeHidden({timeout: 15_000});

    const persisted = await getEnrichedCategoryViaApi(page, id);
    expect(persisted?.properties?.code, `Unexpected category payload: ${JSON.stringify(persisted)}`).toBe(code);
    expect(persisted?.properties?.labels?.en_US, `Label not persisted: ${JSON.stringify(persisted)}`).toBe(newLabel);
  });
});
