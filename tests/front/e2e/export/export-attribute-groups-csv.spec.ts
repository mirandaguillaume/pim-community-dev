import {test, expect, Page} from '../fixtures/coverage-fixture';
import {
  login,
  createAttributeGroupViaApi,
  createAttributeViaApi,
  launchExportViaApi,
  waitForJobExecutionViaApi,
  resolveJobCode,
  goToJobExecution,
} from '../fixtures/pim';

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

/**
 * Replaces Behat: tests/legacy/features/pim/structure/attribute-group/export_attribute_groups_csv.feature:7
 *   "Successfully export attribute groups"
 *
 * The Behat scenario depends on the "footwear" fixture's exact 6 attribute groups and asserts a
 * byte-for-byte CSV dump of all of them ("Read 6" / "Written 6" + the full CSV body). That's not
 * portable (catalog-specific group codes/labels/attribute lists, exact column ordering) and, per
 * this migration's established pattern for export scenarios (export-launch.spec.ts,
 * import-via-api.spec.ts), raw exported file content isn't read back at all — completion is
 * verified through the job execution REST API's step summary counters instead.
 *
 * This spec follows that same pattern, made precise rather than a bare `write > 0` check: it reads
 * the CURRENT total attribute-group count via the REST API first (GET /rest/attribute-group/,
 * AttributeGroupController::indexAction() -> `findAll()`, no pagination, keyed by code), creates 2
 * disposable groups (with a disposable attribute assigned to each, mirroring the original CSV's
 * "attributes" column), launches the export, and asserts the job's write/read counts equal
 * `baseline + 2` — proving both that the export ran to completion AND that it actually picked up
 * the newly created groups, without depending on what else happens to be in the catalog.
 *
 * Job code: 'csv_footwear_attribute_group_export' (footwear fixture) falls back to
 * 'csv_attribute_group_export' — confirmed as a real default-install job instance in
 * src/Akeneo/Platform/Installer/back/.../fixtures/icecat_demo_dev/jobs.yml ("Demo CSV attribute
 * group export"), the catalog this suite actually runs against. Same resolveJobCode candidate-
 * fallback pattern as export-launch.spec.ts.
 *
 * Launches via REST API (launchExportViaApi) rather than the "Launch" button in the UI, for the
 * same reason as export-launch.spec.ts: both paths share JobInstanceController::launchAction().
 */

async function getAttributeGroupCount(page: Page): Promise<number> {
  const resp = await page.request.get('/rest/attribute-group/', {headers: XHR_HEADER});
  expect(resp.ok(), `List attribute groups failed: ${resp.status()}`).toBeTruthy();
  const groups = await resp.json();
  return Object.keys(groups).length;
}

test.describe('Export attribute groups CSV', () => {
  let exportJobCode: string;

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    exportJobCode = await resolveJobCode(
      page,
      'export',
      'csv_footwear_attribute_group_export',
      'csv_attribute_group_export'
    );
    await page.close();
  });

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('Successfully export attribute groups', async ({page}) => {
    const ts = Date.now();
    const groupACode = `pw_group_a_${ts}`;
    const groupBCode = `pw_group_b_${ts}`;

    const baselineCount = await getAttributeGroupCount(page);

    const [groupAResp, groupBResp] = await Promise.all([
      createAttributeGroupViaApi(page, groupACode),
      createAttributeGroupViaApi(page, groupBCode),
    ]);
    expect(groupAResp.ok(), `Create attribute group ${groupACode} failed: ${groupAResp.status()}`).toBeTruthy();
    expect(groupBResp.ok(), `Create attribute group ${groupBCode} failed: ${groupBResp.status()}`).toBeTruthy();

    const [attrAResp, attrBResp] = await Promise.all([
      createAttributeViaApi(page, {code: `pw_attr_a_${ts}`, type: 'pim_catalog_text', group: groupACode}),
      createAttributeViaApi(page, {code: `pw_attr_b_${ts}`, type: 'pim_catalog_text', group: groupBCode}),
    ]);
    expect(attrAResp.ok(), `Create attribute for ${groupACode} failed: ${attrAResp.status()}`).toBeTruthy();
    expect(attrBResp.ok(), `Create attribute for ${groupBCode} failed: ${attrBResp.status()}`).toBeTruthy();

    const expectedCount = baselineCount + 2;

    const jobId = await launchExportViaApi(page, exportJobCode);
    const jobResult = await waitForJobExecutionViaApi(page, jobId);

    expect(jobResult.status, `Export job did not complete: ${JSON.stringify(jobResult)}`).toBe('COMPLETED');

    const exportStep = jobResult.stepExecutions?.find((s: any) => s.summary?.written > 0);
    expect(exportStep, `No step wrote any items: ${JSON.stringify(jobResult.stepExecutions)}`).toBeTruthy();
    expect(exportStep.summary.written).toBe(expectedCount);
    if (undefined !== exportStep.summary.read) {
      expect(exportStep.summary.read).toBe(expectedCount);
    }

    // Verify completion is also reflected in the job tracker UI (same check as export-launch.spec.ts).
    await goToJobExecution(page, jobId);
    await expect(page.getByText(/completed/i).first()).toBeVisible({timeout: 15_000});
  });
});
