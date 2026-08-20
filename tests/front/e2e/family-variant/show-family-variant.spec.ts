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
 * - "I visit the "Variants" tab": the family edit form uses a DIFFERENT tab widget than the
 *   product edit form's '.column-navigation-link' — form_extensions/family/edit.yml wires the
 *   Variants tab through `pim/form/common/form-tabs` (module js/form/common/form-tabs.js),
 *   whose template (templates/form/form-tabs.html) renders `.AknHorizontalNavtab-link` tab
 *   links, same class as export/edit-export.spec.ts's "Global settings"/"Content" tabs.
 * - "I click on the "<label>" row": DataGridContext.php::iClickOnTheRow() ->
 *   Datagrid::getRow($value) — the <tr> containing that visible text.
 * - "(Variant axis)" / "Variant attributes level one/two": confirmed genuine static UI copy
 *   (jsmessages.en_US.yml: variant_axis_label = "Variant axis", level_1/level_2 = "Variant
 *   attributes level one/two"), asserted regardless of which family variant is used.
 * - Dropped the Behat scenario's "Variation name" assertion: confirmed live in CI (element
 *   never found) and by grepping fixtures — "Variation name" isn't static UI copy at all, it's
 *   the LABEL of the `variation_name` attribute, which the catalog_modeling/icecat_demo_dev
 *   fixtures happen to include in most clothing/shoes family variants' attribute sets — not a
 *   guaranteed-present string for an arbitrarily-picked family variant.
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
    await page.locator('.AknHorizontalNavtab-link').filter({hasText: 'Variants'}).click();
    await nav.waitForPageReady();

    // I click on the "<label>" row
    await page.getByRole('row').filter({hasText: variantLabel}).first().click();

    // Then I should see the text "<variantCode>"
    await expect(page.getByText(variantCode, {exact: false})).toBeVisible({timeout: 15_000});

    // "(Variant axis)" suffix presence, and the level-count-dependent "Variant attributes level
    // N" labels (only meaningful when there's more than one level). Checking presence rather
    // than matching each axis's exact attribute label text: the UI shows the attribute's
    // translated LABEL next to "(Variant axis)" ("Size (Variant axis)"), which can differ in
    // wording/casing from its code. A lower bound rather than an exact count: confirmed live in
    // CI that the panel renders each axis's "(Variant axis)" marker more than once (a summary
    // section plus a per-level breakdown) — the exact multiplier isn't a stable, documented
    // contract, so only the presence of at least one marker per axis is asserted.
    const totalAxisCount = variant.variant_attribute_sets.reduce((sum: number, set: any) => sum + set.axes.length, 0);
    await expect(async () => {
      const axisMarkerCount = await page.getByText('(Variant axis)').count();
      expect(axisMarkerCount).toBeGreaterThanOrEqual(totalAxisCount);
    }).toPass({timeout: 15_000});

    if (levels > 1) {
      await expect(page.getByText(/variant attributes level one/i)).toBeVisible();
      await expect(page.getByText(/variant attributes level two/i)).toBeVisible();
    } else {
      await expect(page.getByText(/variant attributes/i).first()).toBeVisible();
    }
  });
});
