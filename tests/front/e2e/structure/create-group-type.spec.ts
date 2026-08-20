import {test, expect} from '../fixtures/coverage-fixture';
import {login} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/group-type/create_group_type.feature:13
 *   "Successfully create a group type"
 *
 * Legacy Backbone area (form_extensions/group_type/create.yml), not React — no stale-route risk.
 *
 * Selectors traced from:
 * - "I am on the group types page": NavigationHelper.goTo('group types') -> #/configuration/group-type.
 * - "I create a new group type": WebUser.php::iCreateANew() -> Base/Index.php::clickCreationLink()
 *   -> 'Creation link' element, base selector '.AknTitleContainer .AknButton--apply' (GroupType/Index.php
 *   doesn't override it), opens the '.modal, .ui-dialog, [role=dialog]' popin.
 * - "I should see the Code field" / "I fill in ... Code | special": form_extensions/group_type/create.yml
 *   wires a single 'pim/form/common/creation/field' with identifier "code" -> input '#creation_code'
 *   (templates/form/creation/field.html — same pattern as product-model/create-product-model.spec.ts).
 * - "I press the "Save" button": Backbone.BootstrapModal's '.ok' button (lib/bootstrap-modal/bootstrap-modal.js
 *   — same pattern as critical/category.spec.ts and product-model/create-product-model.spec.ts).
 * - "Group type successfully created" / "I should be on the "<code>" group type page": create.yml's
 *   successMessage + editRoute (pim_enrich_grouptype_edit), routerKey: code -> redirects to
 *   #/configuration/group-type/{code}/edit (NavigationHelper's existing 'group type' entity route).
 */

test.describe('Group type creation', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('can create a group type', async ({page}) => {
    const nav = new NavigationHelper(page);
    await nav.goTo('group types');

    await page.locator('.AknTitleContainer .AknButton--apply').click();

    const modal = page.locator('div.modal, div[role="dialog"]');
    await expect(modal).toBeVisible({timeout: 15_000});
    await expect(modal.locator('#creation_code')).toBeVisible({timeout: 10_000});

    const code = `pw_group_type_${Date.now()}`;
    await modal.locator('#creation_code').fill(code);
    await modal.locator('.ok').click();

    await expect(page.getByText('Group type successfully created')).toBeVisible({timeout: 15_000});
    await expect(page).toHaveURL(new RegExp(`#/configuration/group-type/${code}/edit`), {timeout: 15_000});
    await expect(page.getByText(code).first()).toBeVisible({timeout: 15_000});
  });
});
