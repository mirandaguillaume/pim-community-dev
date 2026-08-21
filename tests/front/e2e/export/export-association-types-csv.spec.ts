import {test, expect, Page} from '../fixtures/coverage-fixture';
import {
  login,
  createAssociationTypeViaApi,
  launchExportViaApi,
  waitForJobExecutionViaApi,
  resolveJobCode,
  goToJobExecution,
} from '../fixtures/pim';

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

/**
 * Replaces Behat: tests/legacy/features/pim/structure/association-type/export_association_types_csv.feature:8
 *   "Successfully export association types"
 *
 * Same shape as export-attribute-groups-csv.spec.ts (PR #402): the Behat scenario asserts the
 * exported file "should contain 5 rows", which is exactly as fixture-count-dependent as that
 * scenario's "Read 6"/"Written 6" — depends on the "footwear" catalog's exact association type
 * count. Following the same established pattern (export-launch.spec.ts, export-attribute-groups-
 * csv.spec.ts), completion and row count are verified through the job execution REST API's step
 * summary counters, not by reading the exported file back.
 *
 * Reads the current total association-type count via the REST API first (GET
 * /configuration/rest/association-type, AssociationTypeController::indexAction() ->
 * `findAll()`, normalized as a plain collection), creates 2 disposable association types, launches
 * the export, and asserts the job's write/read counts equal `baseline + 2` — proving the export
 * both completed and actually picked up the new association types.
 *
 * Job code: 'csv_footwear_association_type_export' (footwear fixture) falls back to
 * 'csv_association_type_export' — confirmed as a real default-install job instance in
 * src/Akeneo/Platform/Installer/back/.../fixtures/icecat_demo_dev/jobs.yml ("Demo CSV association
 * type export"), the catalog this suite actually runs against.
 *
 * Launches via REST API (launchExportViaApi) rather than the "Launch" button in the UI, same
 * rationale as export-launch.spec.ts / export-attribute-groups-csv.spec.ts.
 */

async function getAssociationTypeCount(page: Page): Promise<number> {
  const resp = await page.request.get('/configuration/rest/association-type', {headers: XHR_HEADER});
  expect(resp.ok(), `List association types failed: ${resp.status()}`).toBeTruthy();
  const types = await resp.json();
  return Array.isArray(types) ? types.length : Object.keys(types).length;
}

test.describe('Export association types CSV', () => {
  let exportJobCode: string;

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    exportJobCode = await resolveJobCode(
      page,
      'export',
      'csv_footwear_association_type_export',
      'csv_association_type_export'
    );
    await page.close();
  });

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('Successfully export association types', async ({page}) => {
    const ts = Date.now();
    const typeACode = `pw_assoc_a_${ts}`;
    const typeBCode = `pw_assoc_b_${ts}`;

    const baselineCount = await getAssociationTypeCount(page);

    const [typeAResp, typeBResp] = await Promise.all([
      createAssociationTypeViaApi(page, typeACode),
      createAssociationTypeViaApi(page, typeBCode),
    ]);
    expect(typeAResp.ok(), `Create association type ${typeACode} failed: ${typeAResp.status()}`).toBeTruthy();
    expect(typeBResp.ok(), `Create association type ${typeBCode} failed: ${typeBResp.status()}`).toBeTruthy();

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

    // Verify completion is also reflected in the job tracker UI (same check as the sibling specs).
    await goToJobExecution(page, jobId);
    await expect(page.getByText(/completed/i).first()).toBeVisible({timeout: 15_000});
  });
});
