import {test, expect} from '../fixtures/coverage-fixture';
import {login, createAttributeGroupViaApi} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/attribute-group/edit_attribute_group.feature:11
 *   "Successfully edit an attribute group"
 *
 * Creates its own disposable attribute group via the internal REST API (PUT /rest/attribute-group/,
 * AttributeGroupController::createAction()) instead of editing the footwear catalog's "sizes" —
 * self-contained, and avoids mutating a shared fixture group other specs in this suite reference
 * by code (e.g. `group: 'other'` in image-attribute-validation.spec.ts / mass-edit-image.spec.ts).
 *
 * Selectors traced from:
 * - "I am on the "<code>" attribute group page": Context/Page/AttributeGroup/Edit.php
 *   `$path = '#/configuration/attribute-group/{identifier}/edit'` — matches
 *   NavigationHelper.goToEntityPage's existing 'attribute group' route.
 * - "the field Code should be disabled": generic Backbone entity-edit form field, same
 *   'input[name="code"]' convention as channel/create-channel.spec.ts's Code field.
 * - "I fill in ... English (United States) | My sizes": translatable label input, same
 *   'input[name="labels-en_US"]' convention as channel/create-channel.spec.ts.
 * - "I press the "Save" button": '.AknButton--apply', same convention used throughout this suite.
 */

test.describe('Edit an attribute group', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('can edit an attribute group label', async ({page}) => {
    const code = `pw_attr_group_${Date.now()}`;
    const createResp = await createAttributeGroupViaApi(page, code);
    expect(createResp.ok(), `Create attribute group ${code} failed: ${createResp.status()}`).toBeTruthy();

    const nav = new NavigationHelper(page);
    await nav.goToEntityPage('attribute group', code);

    const codeField = page.locator('input[name="code"]').or(page.getByLabel('Code'));
    await expect(codeField.first()).toBeVisible({timeout: 15_000});
    await expect(codeField.first()).toBeDisabled();

    const newLabel = `PW Attribute Group ${Date.now()}`;
    const labelField = page.locator('input[name="labels-en_US"]').or(page.getByLabel('English (United States)'));
    await labelField.first().fill(newLabel);

    await page.locator('.AknButton--apply').click();

    await expect(page.getByText('There are unsaved changes.')).not.toBeVisible({timeout: 15_000});
    await expect(page.getByText(newLabel)).toBeVisible({timeout: 15_000});
  });
});
