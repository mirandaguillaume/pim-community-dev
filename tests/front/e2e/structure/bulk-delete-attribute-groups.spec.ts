import {test, expect} from '../fixtures/coverage-fixture';
import {login, createAttributeGroupViaApi, launchMassDeleteAttributeGroupsViaApi} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/attribute-group/bulk_delete_attribute_groups.feature:6
 *   "Successfully bulk delete attribute groups"
 *
 * The Behat scenario selects 3 rows in the grid (Sizes, Colors, Other) via checkboxes, opens the
 * mass-delete popin, types the "delete" confirmation phrase, waits for the async job via the job
 * tracker UI, and checks its exact final message text. The grid multi-select + confirmation-typing
 * UI is a generic bulk-action pattern (not specific to attribute groups), so this spec instead
 * launches the same "delete_attribute_groups" job directly via the internal REST API
 * (MassDeleteAttributeGroupsController — same API-first rationale as launchImportViaApi /
 * launchExportViaApi bypassing their own UI widgets) and verifies the outcome via the API,
 * covering the part of the flow that's actually specific to this feature: the mass-delete job
 * itself, and that "Other" survives because it's the protected default replacement group
 * (AttributeGroupInterface::DEFAULT_CODE = 'other' — confirmed in
 * src/Akeneo/Pim/Structure/Component/Model/AttributeGroupInterface.php, and it's exactly why the
 * MassDeleteAttributeGroupsController defaults replacement_attribute_group to that same constant).
 *
 * Uses 2 disposable attribute groups instead of the footwear catalog's "Sizes"/"Colors", plus the
 * real 'other' group (guaranteed to exist in any catalog, same convention already relied on by
 * product/image-attribute-validation.spec.ts and structure/edit-attribute-group.spec.ts).
 */

test.describe('Bulk delete attribute groups', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('deletes the selected groups but protects the default replacement group', async ({page}) => {
    const ts = Date.now();
    const codeA = `pw_bulk_a_${ts}`;
    const codeB = `pw_bulk_b_${ts}`;

    const [respA, respB] = await Promise.all([
      createAttributeGroupViaApi(page, codeA),
      createAttributeGroupViaApi(page, codeB),
    ]);
    expect(respA.ok(), `Create attribute group ${codeA} failed: ${respA.status()}`).toBeTruthy();
    expect(respB.ok(), `Create attribute group ${codeB} failed: ${respB.status()}`).toBeTruthy();

    const launchResp = await launchMassDeleteAttributeGroupsViaApi(page, [codeA, codeB, 'other']);
    expect(launchResp.ok(), `Launch mass-delete failed: ${launchResp.status()}`).toBeTruthy();

    // The job runs async — poll until both disposable groups are gone.
    await expect(async () => {
      const [getA, getB] = await Promise.all([
        page.request.get(`/rest/attribute-group/${codeA}`),
        page.request.get(`/rest/attribute-group/${codeB}`),
      ]);
      expect(getA.status()).toBe(404);
      expect(getB.status()).toBe(404);
    }).toPass({timeout: 60_000});

    // "Other" is the protected default replacement group — it survives the bulk delete.
    const getOther = await page.request.get('/rest/attribute-group/other');
    expect(getOther.ok(), `Expected 'other' attribute group to survive the bulk delete`).toBeTruthy();
  });
});
