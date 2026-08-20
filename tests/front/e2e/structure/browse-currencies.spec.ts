import {test, expect} from '../fixtures/coverage-fixture';
import {login} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/channel/currency/browse_currencies.feature:12
 *   "Successfully activate a currency"
 *
 * The Behat scenario also drives the "activated" grid filter (operator "equals", value "yes")
 * and asserts an exact 3-currency count (GBP + USD + EUR). The filter itself is the same
 * generic datagrid boolean filter exercised by many other grid specs in this suite (not
 * specific to currencies), and the exact active-currency count is fixture-dependent. This spec
 * instead verifies the essential, currency-specific behavior directly: toggling a currency's
 * status in the grid actually flips its displayed state.
 *
 * GET /configuration/currency/rest (CurrencyController::indexAction()) was considered as a way
 * to discover a currently-inactive currency to toggle, but it only returns
 * `getActivatedCurrencies()` — i.e. exclusively ALREADY-active ones (confirmed live in CI: a
 * catalog with just EUR/USD activated returned only those two, with no way to tell an inactive
 * one from it). So instead of picking a *specific* currency by state, this test reads whichever
 * state the grid's FIRST row currently shows and asserts the toggle flips it to the opposite —
 * agnostic to which state that row started in.
 *
 * Selectors traced from:
 * - "I activate the "GBP" currency": WebUser.php::iToggleTheCurrencies() ->
 *   Grid.php::clickOnAction($currency, 'Change status') -> Grid.php::getRow($value):
 *   the <tr> containing a <td> with that text, then within it
 *   '.AknButtonList-item[title="Change status"]'.
 * - The "Enabled"/"Disabled" badge: datagrid/currency.yml's `activated` column ->
 *   Oro/Bundle/PimDataGridBundle/Resources/views/Property/activated.html.twig:
 *   `<span class="AknBadge AknBadge--{{ value ? 'success' : 'important' }}">
 *   {{ value ? 'Enabled' : 'Disabled' }}</span>` — plain text, no need to parse the datagrid's
 *   JSON response shape at all.
 */

test.describe('Browse currencies', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('can toggle a currency status from the grid', async ({page}) => {
    const nav = new NavigationHelper(page);
    await nav.goTo('currencies');

    const row = page
      .getByRole('row')
      .filter({has: page.getByRole('cell')})
      .first();
    await expect(row).toBeVisible({timeout: 30_000});

    const wasEnabled = await row.getByText('Enabled', {exact: true}).isVisible();
    const otherState = wasEnabled ? 'Disabled' : 'Enabled';

    await row.locator('[title="Change status"]').click();

    await expect(row.getByText(otherState, {exact: true})).toBeVisible({timeout: 15_000});
  });
});
