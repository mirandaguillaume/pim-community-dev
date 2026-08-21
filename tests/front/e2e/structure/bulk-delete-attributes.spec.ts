import {test, expect} from '../fixtures/coverage-fixture';
import {login, createAttributeViaApi, launchMassDeleteAttributesViaApi} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/attribute/bulk_delete_attributes.feature:6
 *   "Successfully bulk delete attributes"
 *
 * Same API-first rationale as structure/bulk-delete-attribute-groups.spec.ts: the grid
 * multi-select + "delete" confirmation-typing UI is a generic bulk-action pattern, not specific
 * to attributes, so this launches the "delete_attributes" job directly via the internal REST API
 * (MassDeleteAttributeController — see pim.ts for the JSON shape, which differs from the
 * attribute-group mass-delete: this one takes a JSON `filters.options.identifiers` job
 * configuration directly, not form-encoded `codes[]`) and verifies the outcome via the API.
 *
 * Uses 3 disposable text attributes instead of the footwear catalog's "Rating", "Manufacturer",
 * "Description" — self-contained, and unlike the attribute-group version there's no protected
 * "other"-style survivor here, every selected attribute is expected to be deleted.
 */

test.describe('Bulk delete attributes', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('deletes all selected attributes', async ({page}) => {
    const ts = Date.now();
    const codes = [`pw_bulk_attr_a_${ts}`, `pw_bulk_attr_b_${ts}`, `pw_bulk_attr_c_${ts}`];

    for (const code of codes) {
      const resp = await createAttributeViaApi(page, {code, type: 'pim_catalog_text', group: 'other'});
      expect(resp.ok(), `Create attribute ${code} failed: ${resp.status()}`).toBeTruthy();
    }

    const launchResp = await launchMassDeleteAttributesViaApi(page, codes);
    expect(launchResp.ok(), `Launch mass-delete failed: ${launchResp.status()}`).toBeTruthy();

    // The job runs async — poll until all 3 attributes are gone.
    await expect(async () => {
      const responses = await Promise.all(codes.map(code => page.request.get(`/rest/attribute/${code}`)));
      for (const resp of responses) {
        expect(resp.status()).toBe(404);
      }
    }).toPass({timeout: 60_000});
  });
});
