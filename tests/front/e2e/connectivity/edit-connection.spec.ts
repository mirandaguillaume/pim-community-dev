import {test, expect} from '../fixtures/coverage-fixture';
import {LoginPage} from '../pages/LoginPage';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * @critical Connectivity connection settings.
 *
 * Translated from Behat feature file:
 *   - src/Akeneo/Connectivity/Connection/tests/features/edit_connection.feature:7
 *       Scenario: Peter can edit connection settings
 *
 * Selectors and step semantics sourced from:
 *   - src/Akeneo/Connectivity/Connection/tests/Context/SettingsContext.php:
 *       iHaveFollowingConnections(): calls CreateConnectionHandler directly (backend,
 *         not through the UI) — we instead create our connection through the "Create
 *         connection" UI form, reusing the exact same flow this file already exercises,
 *         so the test is self-contained and stays a true UI test end-to-end.
 *       iClickOnConnectionInTheList() / iShouldSeeTheConnectionInTheList():
 *         list container -> find('css', '[title="<label>"]')
 *       iAmConnectionEditPage(): expects url .../#/connect/connection-settings/<code>/edit
 *   - src/Akeneo/Connectivity/Connection/tests/Context/Page/Connections/Index.php:
 *       path = '#/connect/connection-settings/'
 *       'Data source connections list' => '[data-testid="data_source"]'
 *   - src/Akeneo/Connectivity/Connection/tests/Context/Page/Connections/Create.php:
 *       path = '#/connections/create' — STALE: this no longer matches the app. The real
 *       route, confirmed against the current React Router config
 *       (front/src/settings/pages/Index.tsx), is '#/connect/connection-settings/create'.
 *       'Creation form' => '[data-testid="create-connection"]' — this one is still accurate.
 *   - src/Akeneo/Connectivity/Connection/tests/Decorator/Settings/CreationForm.php:
 *       setLabel(): input[name="label"]
 *       setFlowType(): Select2 v3 widget scoped to '.select2-container.flowType' — STALE, see
 *       below.
 *       save(): '.AknButton--apply'
 *   - src/Akeneo/Connectivity/Connection/front/src/settings/components/ConnectionCreateForm.tsx
 *     and FlowTypeSelect.tsx: the whole creation form is React, not the legacy Select2 widget
 *     the Behat decorator drives. `flow_type` renders through a DSM `SelectInput`
 *     (FlowTypeSelect.tsx), and the form's initial state already defaults it to
 *     `FlowType.DATA_SOURCE` (ConnectionCreateForm.tsx's `initialState`) — "Data source" is
 *     pre-selected, so this test never needs to open or interact with that field at all.
 *   - src/Akeneo/Connectivity/Connection/tests/Decorator/Settings/EditForm.php:
 *       setLabel(): input[name="label"]
 *       save(): '.AknButton--apply:not([disabled])'
 */

test.describe('@critical Connectivity connection settings', () => {
  let loginPage: LoginPage;
  let nav: NavigationHelper;

  test.beforeEach(async ({page}) => {
    loginPage = new LoginPage(page);
    nav = new NavigationHelper(page);
    await loginPage.login('admin', 'admin');
  });

  /**
   * Based on: edit_connection.feature:7 Scenario: Peter can edit connection settings
   *
   * Behat steps:
   *   And I have the following connections:
   *     | label   | flow type   |
   *     | Magento | Data source |
   *   And I should see the "Magento" connection in the "Data source" list
   *   When I click on the "Magento" connection in the "Data source" list
   *   Then I am on the "Magento" connection edit page
   *   When I update the connection label with "NEWLABEL"
   *   Then I should not see the text "There are unsaved changes."
   *   And I am on the Connections index page
   *   Then I should see the "NEWLABEL" connection in the "Data source" list
   *
   * Adapted: we create our own uniquely-labelled "data source" connection through the UI
   * (see the file header) instead of relying on the Behat fixture's "Magento"/"BigCommerce",
   * so the test doesn't depend on pre-seeded data.
   */
  test('can edit a connection label', async ({page}) => {
    const label = `pw_connection_${Date.now()}`;
    const newLabel = `${label}_edited`;

    // Create the connection through the UI ('#/connect/connection-settings/create' —
    // see the file header note on Create.php's stale $path).
    await nav.goTo('connection creation');
    const creationForm = page.locator('[data-testid="create-connection"]');
    await expect(creationForm).toBeVisible({timeout: 15_000});
    await creationForm.locator('input[name="label"]').fill(label);

    // Flow type is left untouched: ConnectionCreateForm.tsx's initialState already defaults
    // it to FlowType.DATA_SOURCE ("Data source"), see the file header note.

    // From CreationForm.php save(): '.AknButton--apply'.
    await creationForm.locator('.AknButton--apply').click();

    // Creating a connection redirects straight to its edit page.
    await nav.waitForPageReady();
    await expect(page).toHaveURL(/#\/connect\/connection-settings\/.+\/edit/, {timeout: 15_000});

    // Then I should see the "<label>" connection in the "Data source" list.
    // From Index.php: 'Data source connections list' => '[data-testid="data_source"]'.
    await nav.goTo('connections');
    const dataSourceList = page.locator('[data-testid="data_source"]');
    await expect(dataSourceList.locator(`[title="${label}"]`)).toBeVisible({timeout: 15_000});

    // When I click on the "<label>" connection in the "Data source" list.
    await dataSourceList.locator(`[title="${label}"]`).click();

    // Then I am on the "<label>" connection edit page.
    await nav.waitForPageReady();
    await expect(page).toHaveURL(/#\/connect\/connection-settings\/.+\/edit/, {timeout: 15_000});

    // When I update the connection label with "<newLabel>".
    // From EditForm.php: input[name="label"], then '.AknButton--apply:not([disabled])'.
    const editForm = page.locator('.AknConnectivityConnection-view');
    await expect(editForm).toBeVisible({timeout: 15_000});
    const labelField = editForm.locator('input[name="label"]');
    await labelField.fill(newLabel);
    await editForm.locator('.AknButton--apply:not([disabled])').click();

    // Then I should not see the text "There are unsaved changes."
    await expect(page.getByText('There are unsaved changes.')).not.toBeVisible({timeout: 10_000});

    // And I am on the Connections index page / Then I should see the "<newLabel>" connection
    // in the "Data source" list.
    await nav.goTo('connections');
    await expect(page.locator('[data-testid="data_source"]').locator(`[title="${newLabel}"]`)).toBeVisible({
      timeout: 15_000,
    });
  });
});
