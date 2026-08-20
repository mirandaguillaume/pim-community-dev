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
 * - "Then I am on the dashboard page": Context/Page/Dashboard/Index.php `$path = '#/'`. The
 *   dashboard's distinguishing content is the completeness widget
 *   (Context/Page/Element/CompletenessWidget.php `$selector = ['css' => '#completeness-widget']`),
 *   which only renders on `#/` — a plain "app chrome loaded" check (e.g. the main nav) would
 *   pass on any authenticated page, not specifically the dashboard.
 *
 * No URL assertion: straight after login (not an explicit hash navigation), the app lands on
 * the bare origin ("http://host:port/", confirmed live in CI) with no "#/" ever written to the
 * URL bar — the SPA router treats "no hash" and "#/" as equivalent without rewriting the URL.
 * The completeness widget is the reliable, dashboard-specific signal instead.
 */

test.describe('@critical Login', () => {
  test('can log in through the UI and land on the dashboard', async ({page}) => {
    const loginPage = new LoginPage(page);
    await loginPage.login('admin', 'admin');

    // Then I am on the dashboard page.
    await expect(page.locator('#completeness-widget')).toBeVisible({timeout: 15_000});
  });
});
