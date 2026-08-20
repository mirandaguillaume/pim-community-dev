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
 */

test.describe('@critical Login', () => {
  test('can log in through the UI and land on the dashboard', async ({page}) => {
    const loginPage = new LoginPage(page);
    await loginPage.login('admin', 'admin');

    // Then I am on the dashboard page.
    await expect(page).toHaveURL(/#\/?$/, {timeout: 15_000});
    await expect(page.locator('#completeness-widget')).toBeVisible({timeout: 15_000});
  });
});
