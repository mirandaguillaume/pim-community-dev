import {test, expect, Page} from '../fixtures/coverage-fixture';
import {login} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

// pim.ts declares the same constant but does not export it.
const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

/**
 * Replaces Behat: tests/legacy/features/user-management/user/browse_users.feature:12
 *   "Successfully display users"
 *
 * Background adaptations:
 *
 * - `the "default" catalog configuration`: never loaded in Playwright CI, which seeds
 *   icecat_demo_dev (.github/workflows/ci.yml, `castor back:database --catalog .../icecat_demo_dev`).
 *   icecat's users.csv
 *   (src/Akeneo/Platform/Installer/back/src/Infrastructure/Symfony/Resources/fixtures/icecat_demo_dev/users.csv)
 *   has the same people, but its USERNAMES are lowercase (admin, julia, peter, mary, sandra, julien)
 *   while the Behat default catalog's are capitalised ("Peter", "Julia", ...). In icecat the
 *   capitalised strings are FIRST NAMES. The assertions therefore target the lowercase usernames
 *   with an exact (case-sensitive, whole-string) cell-name match. A case-insensitive text match on
 *   "Peter" would pass on the first-name cell and would not port the step faithfully.
 *
 * - `there is a "magento" connection`: ported twice.
 *   1. icecat already has a `magento` connection whose API user is `magento_0000`: Connectivity's
 *      Connections/Install/InstallSubscriber listens on POST_LOAD_FIXTURES and, for a catalog
 *      ending in icecat_demo_dev, runs FixturesLoader, which creates that user through
 *      defineAsApiUser() and a type 'default' connection. The test proves it exists with
 *      GET /rest/connections/magento (akeneo_connectivity_connection_rest_get) before asserting it
 *      is hidden, so the negative check can never be vacuous.
 *   2. A disposable connection `pw_browse_users_<ts>` is created with POST /rest/connections
 *      (akeneo_connectivity_connection_rest_create, compiled route has no trailing slash).
 *      CreateConnectionAction runs the same CreateConnectionHandler the Behat step calls
 *      (FixturesContext::thereIsAConnection -> CreateConnectionCommand($code, $code, DATA_SOURCE)),
 *      so label = code and flow_type = data_source mirror it. CreateUser names the API user
 *      `<code>_NNNN`. The response `username` field (ConnectionWithCredentials::normalize(), read
 *      through an INNER JOIN on oro_user) proves the user row was persisted. It is deleted in
 *      `finally` via DELETE /rest/connections/{code} (DeleteConnectionHandler removes the
 *      connection, its user and its OAuth client), so retries never pile up against the
 *      connection-count limit.
 *   Failure codes: 403 = peter's role lacks akeneo_connectivity_connection_manage_settings,
 *   404 (GET) = connection missing or its type is not 'default', 422 = validation errors,
 *   400 = CreateConnectionAction caught a handler exception, message in the body (e.g. CreateUser's
 *   "The user creation failed : ..." before anything is persisted). A 400 with an EMPTY message
 *   specifically means CreateConnectionHandler saved the connection but could not read it back.
 *   Because a failed or thrown create may still have saved rows, every non-201 outcome gets a
 *   best-effort DELETE (a DELETE on an unknown code just returns 400).
 *
 * - `I am logged in as "Peter"`: Behat injects a session token (NavigationContext::iAmLoggedInAs).
 *   This spec does a real UI login as icecat's `peter` / `peter` (ROLE_ADMINISTRATOR, "IT support":
 *   the same role and group as admin, so the same ACLs, including pim_user_user_index for the
 *   grid). The server-side interactive-login event Behat dispatches by hand fires naturally.
 *
 * Steps and selectors traced from:
 *
 * - `I am on the users page`: NavigationContext 'users' => 'User index' ->
 *   Context/Page/User/Index.php `$path = '#/user/'` (NavigationHelper 'users'). The page is
 *   form_extensions/index.yml `pim-user-index-grid` (pim/form/common/index/grid, alias
 *   pim-user-grid). UIBundle form/common/grid.js loads rows with
 *   `$.get(Routing.generate('pim_datagrid_load', ...))`, whose compiled route is
 *   `/datagrid/{alias}/load`. The spec waits for `/datagrid/pim-user-grid/load` and asserts its
 *   status, so a 403/500 fails with the body instead of an opaque timeout.
 * - `I should see users ...`: DataGridContext::iShouldSeeEntities -> Grid::getRow
 *   (`tr td:contains(value)`). The username column is `frontend_type: label`
 *   (datagrid/user.yml) -> PimDataGridBundle datagrid/cell/label-cell.js, a StringCell whose text
 *   is the raw username, so the cell's accessible name is exactly the username. Row/cell roles are
 *   the same pattern browse-currencies.spec.ts uses on the same grid module.
 * - `I should not see users "magento"`: DataGridContext::iShouldNotSeeEntities -> Grid::hasRow (any
 *   cell containing the value). `rows.filter({hasText})` is a case-insensitive substring match over
 *   the whole row, which is at least as strict. The grid hides API users through
 *   datagrid/user.yml `where: u.type = TYPE_USER` (User::TYPE_USER = 'user'; it also hides
 *   TYPE_API = 'api' and TYPE_JOB = 'job' users). Without that filter both asserted API users would
 *   be on page 1: sorted by username ASC at 25 per page, icecat has 6 human users plus 4 connection
 *   API users (magento_0000, sap_0000, alkemics_0000, translations_com_0000 in FixturesLoader) plus
 *   the disposable one, 11 rows in all. The negatives run only after the five positive checks have proven the grid
 *   body rendered, and a closing positive check on `sandra` (the disposable user would sort
 *   between peter and sandra) brackets them against a re-render.
 *
 * Deliberately NOT modelled on user-group-assignations.spec.ts: its `isVisible({timeout})` guards
 * do not retry.
 */

async function createConnectionViaApi(page: Page, code: string) {
  return page.request.post('/rest/connections', {
    data: {code, label: code, flow_type: 'data_source'},
    headers: {'Content-Type': 'application/json', ...XHR_HEADER},
  });
}

async function getConnectionViaApi(page: Page, code: string) {
  return page.request.get(`/rest/connections/${code}`, {headers: XHR_HEADER});
}

async function deleteConnectionViaApi(page: Page, code: string) {
  return page.request.delete(`/rest/connections/${code}`, {headers: XHR_HEADER});
}

test.describe('Browse users', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'peter', 'peter');
  });

  test('Successfully display users', async ({page}) => {
    // Background: there is a "magento" connection (icecat seed).
    const magento = await getConnectionViaApi(page, 'magento');
    const magentoBody = await magento.json().catch(() => null);
    expect(
      magento.ok(),
      `GET connection magento failed: ${magento.status()} ${JSON.stringify(magentoBody)}`
    ).toBeTruthy();
    expect(magentoBody?.username, `unexpected magento connection user: ${JSON.stringify(magentoBody)}`).toMatch(
      /^magento_\d{4}$/
    );

    // Background: a connection created through the same handler as the Behat step.
    const code = `pw_browse_users_${Date.now()}`;
    let createStatus: number | null = null;
    try {
      const created = await createConnectionViaApi(page, code);
      createStatus = created.status();
      const createdBody = await created.json().catch(() => null);
      expect(createStatus, `Create connection ${code} failed: ${createStatus} ${JSON.stringify(createdBody)}`).toBe(
        201
      );
      expect(createdBody?.username, `unexpected connection user: ${JSON.stringify(createdBody)}`).toMatch(
        new RegExp(`^${code}_\\d{4}$`)
      );

      // Given I am on the users page
      const [gridLoad] = await Promise.all([
        page.waitForResponse(r => r.url().includes('/datagrid/pim-user-grid/load'), {timeout: 60_000}),
        new NavigationHelper(page).goTo('users'),
      ]);
      expect(
        gridLoad.ok(),
        `pim-user-grid load failed: ${gridLoad.status()} ${(await gridLoad.text().catch(() => '')).slice(0, 2000)}`
      ).toBeTruthy();

      const rows = page.getByRole('row').filter({has: page.getByRole('cell')});

      // Then I should see users "admin", "Peter", "Julia", "Mary" and "Sandra"
      for (const username of ['admin', 'peter', 'julia', 'mary', 'sandra']) {
        await expect(
          rows.filter({has: page.getByRole('cell', {name: username, exact: true})}),
          `user ${username} row missing from the users grid`
        ).toHaveCount(1, {timeout: 30_000});
      }

      // But I should not see users "magento"
      await expect(
        rows.filter({hasText: 'magento'}),
        'API user of the icecat magento connection must not be listed'
      ).toHaveCount(0);
      await expect(rows.filter({hasText: code}), `API user of connection ${code} must not be listed`).toHaveCount(0);

      // The grid is still rendered after the negative checks.
      await expect(
        rows.filter({has: page.getByRole('cell', {name: 'sandra', exact: true})}),
        'users grid re-rendered empty during the negative checks'
      ).toHaveCount(1);
    } finally {
      if (createStatus === 201) {
        const del = await deleteConnectionViaApi(page, code);
        expect.soft(del.status(), `Delete connection ${code} failed: ${del.status()} ${await del.text()}`).toBe(204);
      } else {
        // Best effort: a 400 or a thrown request can leave the connection persisted.
        await deleteConnectionViaApi(page, code).catch(() => null);
      }
    }
  });
});
