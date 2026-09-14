import {test, expect} from '../fixtures/coverage-fixture';
import {login, XHR_HEADER} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaced Behat scenario (deleted in #420): tests/legacy/features/pim/enrichment/product/datagrid/datagrid_views.feature:17
 *   "Successfully display the default view"
 *
 * Read-only scenario: it only checks the view-selector label and the ordered visible column
 * headers of the product grid, so nothing is created and nothing needs cleaning up.
 *
 * Adaptations:
 * - Background "a footwear catalog configuration" + the 3 footwear products (purple-sneakers,
 *   black-sneakers, black-boots) are dropped. Playwright CI runs against the icecat_demo_dev
 *   DB seed (ci.yml infra-db-seed-playwright: castor back:database --catalog .../icecat_demo_dev),
 *   and this scenario never reads those products: it only needs the grid table to render
 *   (an empty result hides it). icecat_demo_dev/products.csv ships ~1559 products, indexed by
 *   the CI step `pim:product:index --all`. The body-row wait below makes an empty grid fail
 *   loudly instead of silently producing zero headers.
 * - "I am logged in as Mary": kept as icecat_demo_dev's `mary` (users.csv: password `mary`,
 *   ROLE_USER, user_default_locale en_US, so the English labels below apply). Proof that
 *   ROLE_USER can open the product grid although icecat user_roles.csv has no permissions
 *   column: `castor back:database` runs `pim:installer:db` (castor/back.php), whose
 *   DatabaseCommand dispatches InstallerEvents::POST_LOAD_FIXTURE with the job name; for
 *   `fixtures_user_role_csv`, AddDefaultPrivilegesSubscriber grants every non-anonymous role the
 *   full-access root ACL mask and only zeroes `enabled_at_creation: false` classes (Connectivity
 *   apps, job tracker "view all jobs"). The product grid's acl_resource pim_enrich_product_index
 *   is not one of them. Using mary rather than admin also keeps this spec away from the account
 *   most other specs use, so it cannot inherit a default grid view saved by one of them.
 * - Scenario tag @data-quality-insights-feature-enabled needs no code: under Behat it toggles
 *   FilePersistedFeatureFlags (FeatureFlagContext.php), which only exists in the behat/test
 *   service configs. Playwright CI runs APP_ENV=prod, where data_quality_insights is an
 *   EnvVarFeatureFlag on FLAG_DATA_QUALITY_INSIGHTS_ENABLED (DataQualityInsights
 *   feature_flags.yml defaults it to 'true', .env sets 1), so FeatureFlagDatagridFilterListener
 *   keeps the "Quality score" column.
 * - Added precondition: the synthetic "Default view" is only selected when the user has no
 *   default product-grid view (grid/view-selector.js initializeSelection resolves
 *   getDefaultView() only without a user default and without a stored view id; the combobox
 *   gets showDefaultView = null === defaultUserView). It is asserted through
 *   GET /datagrid_view/rest/product-grid/default (route pim_datagrid_view_rest_default_user_view,
 *   GET, no trailing slash, checked in the compiled route dump), which returns
 *   {view: null | normalized view} (DatagridViewController::getUserDefaultDatagridViewAction).
 *   A polluted user or an unexpected (e.g. login-page HTML) response fails with the body in the
 *   message instead of a confusing "Default view not found".
 * - No stored client state to reset: a fresh Playwright browser context per test (and per
 *   retry) has an empty sessionStorage, so no stored view id / columns / filters
 *   (pimdatagrid js/datagrid/state.js), and table.js applies the default columns
 *   (applyColumns(state.columns || defaultColumns)), which DatagridViewManager::getDefaultColumns
 *   takes from the product-grid `columns` config.
 *
 * Navigation: "I am on the products grid" is NavigationHelper.goTo('products')
 * (#/enrich/product/). goToProductsGrid from pim.ts is NOT used because it switches the variant
 * selector to "product", which writes a filter into the datagrid state and diverges from the
 * untouched default state Behat checks. goTo's waitForPageReady is not a real sync here (after
 * login the hash change is a same-document Backbone navigation and the masks can be hidden
 * before the route starts); the authoritative "product grid mounted" signal is the first
 * `tr.AknGrid-bodyRow` with cells, the same locator and timeout as pim.ts goToProductsGrid.
 *
 * Selectors traced from:
 * - "Default view": form_extensions/product/index.yml pim-product-index-view-selector ->
 *   requirejs.yml `pim/grid/view-selector` (js/product/grid/view-selector.js), which builds the
 *   `pim-grid-view-selector` form from form_extensions/grid/grid_view_selector.yml ->
 *   `pim/grid/view-selector/selector` -> requirejs.yml js/grid/view-selector.js, whose template
 *   templates/grid/view-selector.html renders `div.grid-view-selector > .view-selector-combobox`.
 *   The React ViewSelectorCombobox mounted there renders only the selected value while closed
 *   (DSM SelectInput currentValueElement; options render only in the open overlay), as
 *   ViewSelectorLine's `span.view-label` holding the text; the dirty `*` is a sibling span, so an
 *   exact getByText scoped to `.grid-view-selector` resolves to that label. The text is
 *   pim_datagrid.view_selector.default_view = "Default view" (PimDataGridBundle
 *   jsmessages.en_US.yml). Behat's page-wide pageTextContains is narrowed to the selector.
 * - Columns: DataGridContext::iShouldSeeTheColumns checks the column count and each column's
 *   position via Grid.php getColumnHeaders(false, false): `thead //th` of the grid minus
 *   `.action-column` (column/action-column.js), `.select-all-header-cell`
 *   (header-cell/select-all-header-cell.js) and th with a direct input child, visible only.
 *   The grid table is `table.grid` (templates/common/grid.html). The only th with an input child
 *   in the product grid is the select-all header cell, already excluded by class, so the locator
 *   below is equivalent for this grid. Non-renderable header cells start hidden (backgrid.js
 *   Row.render, `cell.$el.hide()`), hence `:visible`. An array toHaveText asserts both the count
 *   and each text in order. Sortable headers render `<a>LABEL <span class="caret"></span></a>`
 *   (header-cell/header-cell.js); whitespace is normalised. Expected labels, in order:
 *   Enrichment datagrid/product.yml `columns` (ID ... Variant products) then DataQualityInsights
 *   datagrid/custom_product_enrichment.yml (Quality score, behind feature_flag
 *   data_quality_insights).
 */

test.describe('Datagrid views', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'mary', 'mary');
  });

  test('displays the default view with the default product grid columns', async ({page}) => {
    // Precondition: mary has no saved default product-grid view.
    const defaultViewResp = await page.request.get('/datagrid_view/rest/product-grid/default', {
      headers: XHR_HEADER,
    });
    const defaultViewBody = await defaultViewResp.json().catch(() => null);
    expect(
      defaultViewResp.ok(),
      `Get default product-grid view failed: ${defaultViewResp.status()} ${JSON.stringify(defaultViewBody)}`
    ).toBeTruthy();
    expect(
      defaultViewBody,
      `mary must have NO default product-grid view for "Default view" to be shown, got: ${defaultViewResp.status()} ${JSON.stringify(defaultViewBody)}`
    ).toHaveProperty('view', null);

    // Given I am on the products grid
    const nav = new NavigationHelper(page);
    await nav.goTo('products');
    // mary is the only non-admin login in the suite: a timeout here most likely means ROLE_USER
    // lacks the pim_enrich_product_index ACL (see header), not an infra flake.
    await expect(
      page.locator('tr.AknGrid-bodyRow:has(td)').first(),
      'No product grid row rendered for mary: check the pim_enrich_product_index ACL of ROLE_USER, then the product index'
    ).toBeVisible({timeout: 120_000});

    // Then I should see the text "Default view"
    await expect(page.locator('.grid-view-selector').getByText('Default view', {exact: true})).toBeVisible({
      timeout: 30_000,
    });

    // Then I should see the columns ID, Image, Label, Family, Status, Complete, Created, Updated,
    // Variant products, Quality score
    const headers = page.locator('table.grid thead th:not(.action-column):not(.select-all-header-cell):visible');
    await expect(headers).toHaveText(
      [
        'ID',
        'Image',
        'Label',
        'Family',
        'Status',
        'Complete',
        'Created',
        'Updated',
        'Variant products',
        'Quality score',
      ],
      {timeout: 30_000}
    );
  });
});
