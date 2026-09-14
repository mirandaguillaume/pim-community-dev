import {test, expect} from '../fixtures/coverage-fixture';
import type {Page} from '../fixtures/coverage-fixture';
import {login, goToProductsGrid} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/user-management/user/edit_user.feature:22
 *   "Successfully edit and apply user preferences"
 *
 * Adaptations:
 * - Fixture substitution (CI runs icecat_demo_dev, not apparel), all from
 *   src/Akeneo/Platform/Installer/back/src/Infrastructure/Symfony/Resources/fixtures/icecat_demo_dev:
 *   - "German (Germany)" = de_DE, activated on every channel (channels.csv:2-4).
 *   - "Print" = the `print` channel, de_DE label "Drucken" (channels.csv:3).
 *   - "2015 collection" = the root tree `sales` "Sales catalog" / "Katalog Umsatz" (categories.csv:3),
 *     with children `brands` "Marken" (:19) and `home_appliances` "Haushaltsgeräte" (:20). These
 *     replace "Kollektion", "2015 Männer-Kollektion" and "2015 Damenkollektion". An existing root is
 *     reused on purpose: a brand-new root tree is not reliably visible to a user.
 *   - SKU/Name are the `sku`/`name` attributes (attributes.csv:2-3, useable_as_grid_filter=1); Family is
 *     the system `family` filter (datagrid/product.yml).
 * - "Peter" is replaced by a disposable ROLE_ADMINISTRATOR user created by admin through
 *   POST /rest/user/ (UserController::createAction), mirroring icecat's peter (users.csv). That user logs in
 *   and edits ITSELF, as Behat's Peter does. Editing admin instead would switch the catalog locale of the
 *   account every other spec in the shard uses. Admin works in a separate browser context for setup, the
 *   persistence check and cleanup; it deletes the user because deleteAction forbids self-deletion.
 * - "Then I should see the flash message "User saved"" is DROPPED. The Behat step body is a disabled
 *   no-op (AssertionContext.php), and the flash cannot be observed here. controller/user.js (the
 *   pim_user_edit controller) calls `location.reload()` from the post_save handler when the catalog
 *   locale, scope or default tree changed, and save.js::postSave fires post_save BEFORE messenger.notify.
 *   The in-memory flash is lost with the reloaded document. It is replaced by:
 *   - the save response status;
 *   - a real `load` event, since this is a full document reload and not the soft Routing.reloadPage;
 *   - an API check that the four preferences were persisted.
 * - "I should see the text "Drucken"" is scoped to the grid scope switcher (`.scope-switcher .value`,
 *   product_scope-filter.js className + highlightScope, templates/filter/scope-filter.html). A page-wide
 *   match would be ambiguous: the sales grandchild print_scan_sales is "Drucken und Scannen"
 *   (categories.csv:24). It is asserted FIRST: it proves the grid loaded with dataLocale=de_DE.
 * - Tree labels: the backend clears the session dataLocale on save
 *   (UpdateUserCommandHandler.php `remove('dataLocale')`). Every later request without an explicit
 *   dataLocale therefore resolves to the user's saved catalog locale (UserContext::getCurrentLocale), and
 *   the reload re-initialises the client UserContext from the server. The only way to get English tree
 *   labels back is a stale request sending dataLocale=en_US. If the scope reads "Drucken" but the tree is
 *   English, look for that request.
 *
 * Selectors traced from:
 * - "I edit the user": Context/Page/User/Edit.php `#/user/{identifier}/edit` (compiled route pim_user_edit).
 * - "I visit the "Additional" tab": templates/form/form-tabs.html `li.AknHorizontalNavtab-item[data-tab]`,
 *   form-tabs.js `click header ul.nav-tabs li`; label from UserManagement jsmessages.en_US.yml.
 * - Fields: field.js className `AknFieldContainer` + templates/form/common/fields/field.html
 *   `label.AknFieldContainer-label` (form_extensions/edit.yml labels "Catalog locale", "Catalog scope",
 *   "Default tree", "Product grid filters").
 * - Catalog locale / scope / default tree: select.js subclasses (available-locales.ts, fields/channel.ts,
 *   fields/category-tree.ts). Their `<select class="select2">` is wrapped by Select2 3.4.1
 *   (lib/select2/select2.js: `.select2-choice`, `.select2-chosen`, `.select2-drop-active`,
 *   `.select2-result-label`). Each change runs getRoot().render(), which re-renders every field one
 *   macrotask later (field.js render inside a Deferred.then). Select2 has already updated the OLD widget,
 *   so the helper tags the old widget and waits for it to be replaced before continuing.
 * - Product grid filters: product-grid-filters.ts (multi-select-async -> simple-select-async, hidden
 *   `input.select2`). Results are rendered with templates/attribute/attribute-line.html `.attribute-label`,
 *   matched exactly: "Name" also returns "Model name", "ERP name" and "Variant Name". After each pick the
 *   re-render rebuilds the chips from an AJAX initSelection (GET /enrich/product-grid-filter/?identifiers=...).
 *   The helper waits for that response. Chips follow SERVER order (system filters first, then attributes
 *   by group sort order), not pick order, so they are asserted by membership, never by position.
 * - "I save the user": templates/form/save-buttons.html `button.AknButton--apply` (only one primary
 *   button on this form); save URL pim_user_user_rest_post = POST /rest/user/{id}.
 * - "I open the category tree": category-switcher.js className `category-switcher`; render() stays empty
 *   until the tree label is known, hence the text wait before the single click (a second click would close
 *   it). The open state is `AknDefault-thirdColumnContainer--open` (simple-view.js toggleThirdColumn,
 *   templates/common/default-template.html), as Behat WebUser::iToggleTheCategoryTree checks.
 * - Tree nodes: `#tree` (product/grid/category-tree.js id) -> DSM Tree.tsx `title={label}`. Labels carry a
 *   product count "<label> (N)" (RootCategory.php / ChildCategory.php), hence anchored regexes.
 * - "I should (not) see the filter <code>": Behat Grid::getFilter `.filter-item[data-name="<code>"]`
 *   (abstract-filter.js className + data-name) inside `.filter-box` (filters-selector.ts). The negative
 *   `enabled` check runs only after the three positive checks: those prove the filters have rendered, so a
 *   hidden `enabled` filter is meaningful and not a vacuous pass.
 */

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

function escapeRegExp(value: string): string {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

async function createUserViaApi(page: Page, data: Record<string, unknown>): Promise<any> {
  const resp = await page.request.post('/rest/user/', {
    data,
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
  expect(
    resp.ok(),
    `Create user ${data.username} failed: ${resp.status()} ${JSON.stringify(await resp.json().catch(() => null))}`
  ).toBeTruthy();

  return resp.json();
}

async function getUserViaApi(page: Page, id: number): Promise<any> {
  const resp = await page.request.get(`/rest/user/${id}`, {headers: XHR_HEADER});
  expect(
    resp.ok(),
    `Get user ${id} failed: ${resp.status()} ${JSON.stringify(await resp.json().catch(() => null))}`
  ).toBeTruthy();

  return resp.json();
}

function fieldContainer(page: Page, label: string) {
  return page
    .locator('.AknFieldContainer')
    .filter({has: page.locator('label.AknFieldContainer-label', {hasText: label})});
}

/**
 * Picks an option in a select.js-based Select2 field, then waits for the full-form re-render to replace
 * the widget before asserting the chosen value (see header).
 */
async function pickSelect2Option(page: Page, label: string, optionText: string) {
  const field = fieldContainer(page, label);
  await expect(field.locator('.select2-container')).toHaveCount(1, {timeout: 15_000});
  await field.locator('.select2-container').evaluate(element => element.setAttribute('data-pw-stale', '1'));

  await field.locator('.select2-choice').click();
  await page.locator('.select2-drop-active .select2-result-label').filter({hasText: optionText}).click();

  await expect(field.locator('.select2-container[data-pw-stale]')).toHaveCount(0, {timeout: 15_000});
  await expect(field.locator('.select2-chosen')).toHaveText(optionText, {timeout: 15_000});
  await expect(page.locator('.select2-drop-active:visible')).toHaveCount(0, {timeout: 15_000});
}

/**
 * Adds one choice to the "Product grid filters" multi-select. It waits for the initSelection AJAX
 * issued by the re-rendered widget (which carries the new code) before asserting the chips by membership.
 */
async function addGridFilterChoice(page: Page, term: string, code: string, exactLabel: string, expectedCount: number) {
  const field = fieldContainer(page, 'Product grid filters');
  await field.locator('.select2-search-field input.select2-input').fill(term);

  const result = page
    .locator('.select2-drop-active .select2-result-label')
    .filter({has: page.locator('.attribute-label', {hasText: new RegExp(`^\\s*${escapeRegExp(exactLabel)}\\s*$`)})});

  await Promise.all([
    page.waitForResponse(
      resp => {
        const url = new URL(resp.url());
        return (
          url.pathname === '/enrich/product-grid-filter/' &&
          (url.searchParams.get('identifiers') ?? '').split(',').includes(code)
        );
      },
      {timeout: 30_000}
    ),
    result.click(),
  ]);

  const chips = field.locator('.select2-search-choice');
  await expect(chips).toHaveCount(expectedCount, {timeout: 15_000});
  await expect(chips.filter({hasText: new RegExp(`^\\s*${escapeRegExp(exactLabel)}\\s*$`)})).toHaveCount(1, {
    timeout: 15_000,
  });
}

test.describe('Edit a user', () => {
  test('Successfully edit and apply user preferences', async ({page, browser}, testInfo) => {
    const adminContext = await browser.newContext({baseURL: testInfo.project.use.baseURL});
    const adminPage = await adminContext.newPage();
    let userId: number | null = null;

    try {
      await login(adminPage, 'admin', 'admin');

      const ts = Date.now();
      const username = `pw_edit_user_${ts}`;
      const password = `PwEditUser${ts}`;
      const created = await createUserViaApi(adminPage, {
        username,
        password,
        password_repeat: password,
        first_name: 'Peter',
        last_name: 'Playwright',
        email: `pw_edit_user_${ts}@example.com`,
        enabled: true,
        roles: ['ROLE_ADMINISTRATOR'],
        groups: ['IT support'],
        catalog_default_locale: 'en_US',
        user_default_locale: 'en_US',
        catalog_default_scope: 'ecommerce',
        default_category_tree: 'master',
        timezone: 'UTC',
      });
      expect(typeof created.meta?.id, `Created user has no meta.id: ${JSON.stringify(created)}`).toBe('number');
      userId = created.meta.id as number;

      // Background: I am logged in as "Peter"
      await login(page, username, password);

      // When I edit the "Peter" user
      await page.goto(`/#/user/${userId}/edit`);
      const nav = new NavigationHelper(page);
      await nav.waitForPageReady();
      const additionalTab = page.locator('.AknHorizontalNavtab-item[data-tab]').filter({hasText: 'Additional'});
      await expect(additionalTab).toBeVisible({timeout: 30_000});

      // And I visit the "Additional" tab
      await additionalTab.click();
      await expect(page.locator('label.AknFieldContainer-label', {hasText: 'Catalog locale'})).toBeVisible({
        timeout: 15_000,
      });

      // And I fill in the following information
      await pickSelect2Option(page, 'Catalog locale', 'German (Germany)');
      await pickSelect2Option(page, 'Catalog scope', 'Print');
      await pickSelect2Option(page, 'Default tree', 'Sales catalog');
      await addGridFilterChoice(page, 'SKU', 'sku', 'SKU', 1);
      await addGridFilterChoice(page, 'Name', 'name', 'Name', 2);
      await addGridFilterChoice(page, 'Family', 'family', 'Family', 3);

      // And I save the user: controller/user.js reloads the whole document after this save (see header).
      const reloaded = page.waitForEvent('load', {timeout: 120_000});
      reloaded.catch(() => undefined);
      const saved = page.waitForResponse(
        resp => /^\/rest\/user\/\d+$/.test(new URL(resp.url()).pathname) && resp.request().method() === 'POST',
        {timeout: 60_000}
      );
      await page.locator('.AknButton--apply').click();
      const saveResp = await saved;
      expect(
        saveResp.ok(),
        `Save user ${userId} failed: ${saveResp.status()} ${saveResp.ok() ? '' : await saveResp.text().catch(() => '<body unavailable>')}`
      ).toBeTruthy();

      // Replaces the flash message step: the preferences were persisted.
      const persisted = await getUserViaApi(adminPage, userId);
      expect(persisted.catalog_default_locale).toBe('de_DE');
      expect(persisted.catalog_default_scope).toBe('print');
      expect(persisted.default_category_tree).toBe('sales');
      expect(persisted.product_grid_filters).toHaveLength(3);
      expect(persisted.product_grid_filters).toEqual(expect.arrayContaining(['sku', 'name', 'family']));

      await reloaded;
      await nav.waitForPageReady();
      await expect(additionalTab).toBeVisible({timeout: 60_000});

      // When I am on the products grid
      await goToProductsGrid(page);

      // And I should see the text "Drucken" (asserted first: proves the grid loaded in de_DE)
      await expect(page.locator('.scope-switcher .value')).toHaveText('Drucken', {timeout: 30_000});

      // And I open the category tree
      const switcher = page.locator('.category-switcher');
      await expect(switcher).toContainText('Katalog Umsatz', {timeout: 30_000});
      await switcher.click();
      await expect(page.locator('.AknDefault-thirdColumnContainer')).toHaveClass(
        /AknDefault-thirdColumnContainer--open/,
        {timeout: 15_000}
      );

      // Then I should see the text "Kollektion" / "2015 Männer-Kollektion" / "2015 Damenkollektion"
      const tree = page.locator('#tree');
      await expect(tree.getByTitle(/^Katalog Umsatz( \(\d+\))?$/)).toBeVisible({timeout: 30_000});
      await expect(tree.getByTitle(/^Marken \(\d+\)$/)).toBeVisible({timeout: 30_000});
      await expect(tree.getByTitle(/^Haushaltsgeräte \(\d+\)$/)).toBeVisible({timeout: 30_000});
      await expect(tree.getByTitle(/^Sales catalog/)).toHaveCount(0);

      // And I should see the filters name, family and sku
      for (const code of ['name', 'family', 'sku']) {
        await expect(page.locator(`.filter-box .filter-item[data-name="${code}"]`)).toBeVisible({timeout: 30_000});
      }

      // And I should not see the filter enabled (meaningful only after the positive checks above)
      await expect(page.locator('.filter-box .filter-item[data-name="enabled"]')).toBeHidden({timeout: 15_000});
    } finally {
      if (userId !== null) {
        const del = await adminPage.request.delete(`/rest/user/${userId}`, {headers: XHR_HEADER});
        expect.soft(del.ok(), `Delete user ${userId} failed: ${del.status()} ${await del.text()}`).toBeTruthy();
      }
      await adminContext.close();
    }
  });
});
