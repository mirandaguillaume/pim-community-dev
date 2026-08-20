import {test, expect} from '../fixtures/coverage-fixture';
import {login} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/family/family-variant/show_family_variant.feature:11
 *   "Successfully show a family variant with two axes"
 *
 * Uses whatever family variant already exists in the catalog (via
 * GET /configuration/rest/family-variant, same list endpoint as
 * getFirstFamilyVariantCode in pim.ts) instead of the catalog_modeling fixture's
 * "Clothing" / "clothing_color_size", and adapts its assertions to however many
 * levels that variant actually has (the level-specific labels — "Variant attributes
 * level one/two" — only make sense for a 2+ level variant).
 *
 * Selectors traced from:
 * - "I am on the "<family>" family page": NavigationHelper.goToEntityPage('family', code) ->
 *   #/configuration/family/{code}/edit (pre-existing route).
 * - "I visit the "Variants" tab": same generic '.column-navigation-link' tab pattern already
 *   used in product/classify-product.spec.ts for the "Categories" tab.
 * - "I click on the "<label>" row": DataGridContext.php::iClickOnTheRow() ->
 *   Datagrid::getRow($value) — the <tr> containing that visible text.
 * - The remaining assertions ("Size (Variant axis)", "Variation name", "Variant attributes
 *   level one/two") are static UI copy, not fixture-dependent — asserted literally regardless
 *   of which family variant is used, only substituting the actual axis attribute labels.
 */

test.describe('Show a family variant', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('shows the family variant detail panel when clicked from the Variants tab', async ({page}) => {
    const listResp = await page.request.get('/configuration/rest/family-variant', {
      headers: {'X-Requested-With': 'XMLHttpRequest'},
    });
    expect(listResp.ok(), `List family variants failed: ${listResp.status()}`).toBeTruthy();
    const familyVariants = await listResp.json();
    const variant: any = Object.values(familyVariants)[0];
    expect(variant, 'expected at least one family variant in the catalog').toBeTruthy();

    const familyCode: string = variant.family;
    const variantCode: string = variant.code;
    const variantLabel: string = variant.labels?.en_US || variantCode;
    const levels: number = variant.variant_attribute_sets.length;

    const nav = new NavigationHelper(page);
    await nav.goToEntityPage('family', familyCode);

    // I visit the "Variants" tab
    await page.locator('.column-navigation-link').filter({hasText: 'Variants'}).click();
    await nav.waitForPageReady();

    // I click on the "<label>" row
    await page.getByRole('row').filter({hasText: variantLabel}).first().click();

    // Then I should see the text "<variantCode>"
    await expect(page.getByText(variantCode, {exact: false})).toBeVisible({timeout: 15_000});

    // And I should see the text "Variation name" (static UI copy on the detail panel)
    await expect(page.getByText('Variation name')).toBeVisible({timeout: 15_000});

    // "(Variant axis)" suffix count, and the level-count-dependent "Variant attributes level N"
    // labels (only meaningful when there's more than one level). Counting the suffix rather
    // than matching each axis's exact attribute label text: the UI shows the attribute's
    // translated LABEL next to "(Variant axis)" ("Size (Variant axis)"), which can differ in
    // wording/casing from its code — the total axis count is the fixture-agnostic invariant.
    const totalAxisCount = variant.variant_attribute_sets.reduce((sum: number, set: any) => sum + set.axes.length, 0);
    await expect(page.getByText('(Variant axis)')).toHaveCount(totalAxisCount, {timeout: 15_000});

    if (levels > 1) {
      await expect(page.getByText(/variant attributes level one/i)).toBeVisible();
      await expect(page.getByText(/variant attributes level two/i)).toBeVisible();
    } else {
      await expect(page.getByText(/variant attributes/i).first()).toBeVisible();
    }
  });
});
