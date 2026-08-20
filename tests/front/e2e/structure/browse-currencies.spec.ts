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
 * specific to currencies), and the exact active-currency count is fixture-dependent (which
 * currencies ship pre-activated varies by catalog). This spec instead verifies the essential,
 * currency-specific behavior directly: toggling a currency's status in the grid actually
 * flips its `activated` flag, checked via the internal REST API rather than the filter UI.
 *
 * Selectors traced from:
 * - "I activate the "GBP" currency": WebUser.php::iToggleTheCurrencies() ->
 *   Grid.php::clickOnAction($currency, 'Change status') -> Grid.php::getRow($value):
 *   the <tr> containing a <td> with that text, then within it
 *   '.AknButtonList-item[title="Change status"]'.
 * - Currency data: GET /configuration/currency/rest (Channel/back/.../routing/internal_api/currency.yml,
 *   prefix /configuration/currency) — used both to pick a currently-inactive currency to toggle
 *   (GBP ships inactive by default, but we don't hardcode that assumption) and to verify the
 *   flip afterwards.
 */

test.describe('Browse currencies', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('can activate an inactive currency from the grid', async ({page}) => {
    const listResp = await page.request.get('/configuration/currency/rest');
    expect(listResp.ok(), `List currencies failed: ${listResp.status()}`).toBeTruthy();
    const currencies = await listResp.json();
    const list: any[] = Array.isArray(currencies) ? currencies : Object.values(currencies);

    const inactive = list.find(c => c.activated === false);
    expect(inactive, `expected at least one inactive currency, got: ${JSON.stringify(list)}`).toBeTruthy();
    const currencyCode: string = inactive.code;

    const nav = new NavigationHelper(page);
    await nav.goTo('currencies');

    const row = page.getByRole('row').filter({hasText: currencyCode});
    await expect(row).toBeVisible({timeout: 30_000});

    await row.locator('[title="Change status"]').click();

    // Verify the flip via the API rather than re-reading the grid's toggle widget state.
    await expect(async () => {
      const resp = await page.request.get('/configuration/currency/rest');
      const updated = await resp.json();
      const updatedList: any[] = Array.isArray(updated) ? updated : Object.values(updated);
      const currency = updatedList.find(c => c.code === currencyCode);
      expect(currency?.activated).toBe(true);
    }).toPass({timeout: 15_000});
  });
});
