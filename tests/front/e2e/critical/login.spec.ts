import {test, expect} from '../fixtures/coverage-fixture';
import {LoginPage} from '../pages/LoginPage';

/**
 * Replaces Behat: tests/legacy/features/platform/security/login.feature:4
 *   "Login as a user"
 *
 * Selectors and step semantics sourced from:
 * - LoginPage.ts (already used by every other @critical spec in this suite): fills
 *   input[name="_username"]/input[name="_password"], clicks the "Login" button, then waits
 *   for the URL to leave /user/login and for the app's loading masks to clear.
 * - "Then I am on the dashboard page": Context/Page/Dashboard/Index.php `$path = '#/'`. Its
 *   documented distinguishing content, the completeness widget
 *   (Context/Page/Element/CompletenessWidget.php `$selector = ['css' => '#completeness-widget']`),
 *   is STALE — confirmed live in CI (element never found) and by grepping the current frontend:
 *   no `completeness-widget` id exists anywhere. The dashboard is now a React page
 *   (js/controller/dashboard.tsx -> <DashboardIndex /> from @akeneo-pim-community/activity,
 *   workspaces/activity/src/components/DashboardIndex.tsx), whose Header
 *   (components/Header.tsx) renders a breadcrumb step with the stable, dashboard-specific
 *   translation key `pim_dashboard.title` = "Activity dashboard"
 *   (DashboardBundle/Resources/translations/jsmessages.en_US.yml) — used here instead.
 *
 * Scoped to the breadcrumb (getByLabel('Breadcrumb'), confirmed live in CI): the sidebar's
 * "Activity dashboard" menu item carries the identical text, so a page-wide getByText()
 * strict-mode-violates by matching both.
 *
 * No URL assertion: straight after login (not an explicit hash navigation), the app lands on
 * the bare origin ("http://host:port/", confirmed live in CI) with no "#/" ever written to the
 * URL bar — the SPA router treats "no hash" and "#/" as equivalent without rewriting the URL.
 */

test.describe('@critical Login', () => {
  test('can log in through the UI and land on the dashboard', async ({page}) => {
    const loginPage = new LoginPage(page);
    await loginPage.login('admin', 'admin');

    // Then I am on the dashboard page.
    await expect(page.getByLabel('Breadcrumb').getByText('Activity dashboard', {exact: true})).toBeVisible({
      timeout: 15_000,
    });
  });
});
