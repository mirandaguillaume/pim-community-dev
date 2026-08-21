import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  createFamilyViaApi,
  createAttributeViaApi,
  createProductViaApi,
  goToProductBySearch,
  saveProduct,
  reloadProduct,
} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product/create_product_and_save_added_attributes.feature:9
 *   "Successfully create a product, fill in product values with 0 and save"
 *
 * This is a regression test for PIM-5666 (cited in the Behat feature file): attribute value
 * fields must persist the literal value "0" as a real zero, not treat it as empty/falsy and
 * silently drop it. The Behat scenario proves this for 3 attribute types that each store/serialize
 * "0" differently: a plain number (rate_sale), a price collection (tmp_price), and a metric
 * (weight) — a naive falsy check anywhere in that pipeline would treat 0 the same as empty.
 *
 * Adaptations from the Behat scenario:
 * - Doesn't depend on the "footwear" fixture's `super_sandals` family or its specific
 *   `rate_sale`/`weight` attributes. Builds a disposable family with 3 disposable attributes (one
 *   of each type under test) via the internal REST API instead, so this works against any seeded
 *   catalog.
 * - Creates the product via API (createProductViaApi) rather than through the grid's "create
 *   product" popin. The popin's own mechanics (family Select2, SKU field —
 *   pimui/js/product/form/creation/modal.js) aren't what PIM-5666 is about — the regression lives
 *   in the attribute VALUE fields on the product edit form
 *   (UIBundle/Resources/public/js/product/field/{number,price-collection,metric}-field.js), so
 *   the UI part of this test is scoped to exactly that: filling "0" into those 3 fields and
 *   verifying it survives a save + page reload.
 * - Every attribute value field's root element carries `data-attribute="<code>"`
 *   (product/field/field.js: `attributes: () => ({'data-attribute': this.options.code})`), used
 *   here to locate each field precisely, regardless of label/i18n and regardless of which
 *   attribute group a field lives in. All 3 disposable attributes are put in the "other" group;
 *   the PEF's default "Attribute group: All" view (attribute-groups.spec.ts) renders every
 *   group's fields on one page, so no group-tab navigation is needed.
 * - Drops "1 event of type product.created should have been raised" — an internal PHP event-bus
 *   assertion with no UI-observable signal, consistent with other internal-only assertions
 *   dropped elsewhere in this migration.
 * - Verifies persistence via reload + reading the actual field values back (not just the "saved"
 *   toast) — a toast only proves the POST succeeded, not what got stored. Values are matched with
 *   a numeric-zero regex rather than an exact formatted string, since the precise decimal
 *   formatting of a fresh disposable attribute isn't the point of the regression check.
 *
 * @jira https://akeneo.atlassian.net/browse/PIM-5666
 */

test.describe('Create product and save added attributes', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('a value of 0 in number, price and metric fields survives save and reload', async ({page}) => {
    const ts = Date.now();
    const sku = `pw-zero-${ts}`;
    const familyCode = `pw_family_${ts}`;
    const numberCode = `pw_number_${ts}`;
    const priceCode = `pw_price_${ts}`;
    const metricCode = `pw_metric_${ts}`;

    const [numberResp, priceResp, metricResp] = await Promise.all([
      createAttributeViaApi(page, {
        code: numberCode,
        type: 'pim_catalog_number',
        group: 'other',
        labels: {en_US: 'PW Number'},
        decimals_allowed: false,
        negative_allowed: false,
      }),
      createAttributeViaApi(page, {
        code: priceCode,
        type: 'pim_catalog_price_collection',
        group: 'other',
        labels: {en_US: 'PW Price'},
        decimals_allowed: true,
      }),
      createAttributeViaApi(page, {
        code: metricCode,
        type: 'pim_catalog_metric',
        group: 'other',
        labels: {en_US: 'PW Metric'},
        metric_family: 'Weight',
        default_metric_unit: 'GRAM',
        decimals_allowed: true,
        negative_allowed: false,
      }),
    ]);
    expect(numberResp.ok(), `Create attribute ${numberCode} failed: ${numberResp.status()}`).toBeTruthy();
    expect(priceResp.ok(), `Create attribute ${priceCode} failed: ${priceResp.status()}`).toBeTruthy();
    expect(metricResp.ok(), `Create attribute ${metricCode} failed: ${metricResp.status()}`).toBeTruthy();

    const familyResp = await createFamilyViaApi(page, familyCode, [numberCode, priceCode, metricCode]);
    expect(familyResp.ok(), `Create family ${familyCode} failed: ${familyResp.status()}`).toBeTruthy();

    const productResp = await createProductViaApi(page, sku, familyCode);
    expect(productResp.ok(), `Create product ${sku} failed: ${productResp.status()}`).toBeTruthy();

    await goToProductBySearch(page, sku);

    const numberField = page.locator(`[data-attribute="${numberCode}"] input[type="text"]`);
    const priceField = page.locator(`[data-attribute="${priceCode}"] input[type="text"]`).first();
    const metricField = page.locator(`[data-attribute="${metricCode}"] input.data`);

    await expect(numberField).toBeVisible({timeout: 15_000});
    await expect(priceField).toBeVisible({timeout: 15_000});
    await expect(metricField).toBeVisible({timeout: 15_000});

    await numberField.fill('0');
    await priceField.fill('0');
    await metricField.fill('0');

    await saveProduct(page);
    await reloadProduct(page);

    // A regex (not an exact string) tolerates whatever decimal formatting the backend applies —
    // the point being verified is that the field is a real zero, not empty.
    const zeroPattern = /^0(\.0*)?$/;
    await expect(page.locator(`[data-attribute="${numberCode}"] input[type="text"]`)).toHaveValue(zeroPattern, {
      timeout: 15_000,
    });
    await expect(page.locator(`[data-attribute="${priceCode}"] input[type="text"]`).first()).toHaveValue(zeroPattern, {
      timeout: 15_000,
    });
    await expect(page.locator(`[data-attribute="${metricCode}"] input.data`)).toHaveValue(zeroPattern, {
      timeout: 15_000,
    });
  });
});
