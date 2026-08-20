import {test, expect, Locator, Page} from '../fixtures/coverage-fixture';
import {LoginPage} from '../pages/LoginPage';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * @critical Channel creation.
 *
 * Translated from Behat feature file:
 *   - tests/legacy/features/channel/create_channel.feature:8
 *       @critical Scenario: Successfully create a channel
 *
 * Selectors and step semantics sourced from:
 *   - tests/legacy/features/Context/Page/Channel/Creation.php: path = '#/configuration/channel/create'
 *   - tests/legacy/features/Context/Page/Base/Form.php fillField(): dispatches by field type —
 *       Category tree / Currencies / Locales are 'multiSelect2'/'simpleSelect2' fields.
 *   - src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/channel/form/properties/general/
 *       {category-tree,currencies,locales}.js: each renders a plain `<select class="select2" ...>`
 *       via `this.$('.select2').select2()` — standard Select2 v3, so the generated widget
 *       container has Select2's default id convention `s2id_<select-id>`.
 *   - src/Akeneo/Platform/Bundle/UIBundle/Resources/public/templates/channel/tab/properties/general/
 *       {category-tree,currencies,locales}.html: field ids are
 *       pim_enrich_channel_form_category_tree / _currencies / _locales
 *   - Save button and the 'unsaved changes' warning follow the same generic pattern already
 *     verified in critical/category.spec.ts and connectivity/edit-connection.spec.ts:
 *     '.AknButton--apply' / text 'There are unsaved changes.'
 */

/**
 * Selects one or more options in a Select2 v3 multi/single-select field, scoped by the
 * underlying <select>'s id (Select2's default container id is `s2id_<select-id>`).
 */
async function selectViaSelect2(page: Page, selectId: string, optionText: string): Promise<void> {
  const container: Locator = page.locator(`#s2id_${selectId}`);
  await container.click();
  const dropdown = page.locator('#select2-drop');
  await expect(dropdown).toBeVisible({timeout: 10_000});
  await dropdown.getByText(optionText, {exact: true}).first().click();
}

test.describe('@critical Channel creation', () => {
  let loginPage: LoginPage;
  let nav: NavigationHelper;

  test.beforeEach(async ({page}) => {
    loginPage = new LoginPage(page);
    nav = new NavigationHelper(page);
    await loginPage.login('admin', 'admin');
  });

  /**
   * Based on: create_channel.feature:8 @critical Scenario: Successfully create a channel
   *
   * Behat steps:
   *   When I am on the channel creation page
   *   Then I should see the Code, English (United States), Currencies, Locales and Category
   *     tree fields
   *   And I should not see the "History" tab
   *   And I fill in the following information:
   *     | Code                    | foo             |
   *     | Category tree           | 2014 collection |
   *     | Currencies              | EUR             |
   *     | Locales                 | French          |
   *     | English (United States) | Bar Bar         |
   *   And I press the "Save" button
   *   Then I should not see the text "There are unsaved changes."
   *   And I should see the text "Bar Bar"
   *
   * Adapted: uses a uniquely-generated code so the test is self-contained, and picks a category
   * tree / currency / locale that ship with every default catalog instead of the Behat fixture's
   * "2014 collection" (which depends on the "footwear" catalog configuration).
   */
  test('can create a channel', async ({page}) => {
    const code = `pw_channel_${Date.now()}`;
    const englishLabel = `Bar Bar ${Date.now()}`;

    await nav.goTo('channel creation');

    // Then I should see the Code, English (United States), Currencies, Locales and Category
    // tree fields.
    const codeField = page.locator('input[name="code"]').or(page.getByLabel('Code'));
    await expect(codeField.first()).toBeVisible({timeout: 15_000});
    // Scoped to '.AknFieldContainer-label' (templates/create/tab/properties/general.html /
    // field.html): the main navigation sidebar also has "Currencies" and "Locales" menu items,
    // and a plain page.getByText() strict-mode-violates by matching both.
    await expect(page.locator('.AknFieldContainer-label').filter({hasText: 'Category tree'})).toBeVisible();
    await expect(page.locator('.AknFieldContainer-label').filter({hasText: 'Currencies'})).toBeVisible();
    await expect(page.locator('.AknFieldContainer-label').filter({hasText: 'Locales'})).toBeVisible();

    // And I should not see the "History" tab — the History tab only appears once an entity
    // exists (channels don't have history before being created).
    await expect(page.getByRole('tab', {name: 'History'})).not.toBeVisible();

    // And I fill in the following information.
    await codeField.first().fill(code);

    // Category tree: 'simpleSelect2', id pim_enrich_channel_form_category_tree.
    // "Master catalog" ships with every default catalog configuration.
    await selectViaSelect2(page, 'pim_enrich_channel_form_category_tree', 'Master catalog');

    // Currencies: 'multiSelect2', id pim_enrich_channel_form_currencies. EUR ships by default.
    await selectViaSelect2(page, 'pim_enrich_channel_form_currencies', 'EUR');

    // Locales: 'multiSelect2', id pim_enrich_channel_form_locales. en_US ships by default.
    await selectViaSelect2(page, 'pim_enrich_channel_form_locales', 'en_US');

    // English (United States): the translatable label field for the locale just activated.
    const englishLabelField = page.locator('input[name="labels-en_US"]').or(page.getByLabel('English (United States)'));
    await englishLabelField.first().fill(englishLabel);

    // And I press the "Save" button.
    await page.locator('.AknButton--apply').click();

    // Then I should not see the text "There are unsaved changes."
    await expect(page.getByText('There are unsaved changes.')).not.toBeVisible({timeout: 10_000});

    // And I should see the text "Bar Bar" (adapted: our generated English label).
    await expect(page.getByText(englishLabel)).toBeVisible({timeout: 15_000});
  });
});
