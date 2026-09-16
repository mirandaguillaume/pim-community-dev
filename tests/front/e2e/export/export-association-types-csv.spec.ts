import {test, expect, Page} from '../fixtures/coverage-fixture';
import {
  login,
  createAssociationTypeViaApi,
  deleteAssociationTypeViaApi,
  launchExportFromJobPage,
  waitForJobExecutionViaApi,
  waitForJobCompletion,
  readExportedCsv,
  getStepSummaryValue,
  resolveJobCode,
  responseBody,
  XHR_HEADER,
} from '../fixtures/pim';

/**
 * Replaced Behat scenario (deleted): tests/legacy/features/pim/structure/association-type/export_association_types_csv.feature:8
 *   "Successfully export association types"
 *
 * Behat: footwear catalog, the job's storage set to local, Julia opens the export job page, launches the export,
 * waits for the job, then counts the lines of the file on disk ("should contain 5 rows").
 *
 * Adaptations:
 * - Catalog: footwear -> icecat. The job is csv_footwear_association_type_export when it exists, otherwise the icecat
 *   csv_association_type_export (icecat_demo_dev/jobs.yml).
 * - User: Julia -> admin.
 * - Launch: like Behat, the "Export now" button of the export job page (launchExportFromJobPage in pim.ts). The job
 *   uses the pim-job-instance-csv-base-export form (Structure services.yml), which renders that button.
 * - File: local storage needs the import_export_local_storage feature flag, which only Behat and the PHPUnit
 *   JobLauncher enable. The icecat job has storage `none`, so the CSV is read from the job archive (readExportedCsv).
 *   ExportAssociationTypesIntegration.php exports with local storage: the job completes (UploadStep runs without
 *   error) and the local file holds the exact CSV.
 * - "should contain 5 rows" -> data rows === step read === step written === baseline + 2, where the baseline is the
 *   association type count before the 2 disposable types are created, plus the columns of those 2 rows by name.
 *   StandardToFlat\AssociationType is covered by AssociationTypeTest.php.
 *
 * Data: 2 disposable association types, created one after the other: A two-way, B quantified (a type cannot be
 * both). They are deleted in `finally`, best-effort. label-en_US is exported because every icecat channel activates
 * en_US (icecat_demo_dev/channels.csv).
 */

async function getAssociationTypeCount(page: Page): Promise<number> {
  const resp = await page.request.get('/configuration/rest/association-type/', {headers: XHR_HEADER, timeout: 30_000});
  expect(resp.ok(), `List association types failed: ${resp.status()} ${await responseBody(resp)}`).toBeTruthy();
  const types = await resp.json();
  return Array.isArray(types) ? types.length : Object.keys(types).length;
}

function column(header: string[], row: string[], name: string): string | undefined {
  const index = header.indexOf(name);
  return -1 === index ? undefined : row[index];
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
    const types = [
      {code: `pw_assoc_a_${ts}`, label: `PW assoc A ${ts}`, isTwoWay: true, isQuantified: false},
      {code: `pw_assoc_b_${ts}`, label: `PW assoc B ${ts}`, isTwoWay: false, isQuantified: true},
    ];
    const createdCodes: string[] = [];

    const baselineCount = await getAssociationTypeCount(page);

    try {
      for (const type of types) {
        const resp = await createAssociationTypeViaApi(page, type.code, {
          labels: {en_US: type.label},
          is_two_way: type.isTwoWay,
          is_quantified: type.isQuantified,
        });
        expect(
          resp.ok(),
          `Create association type ${type.code} failed: ${resp.status()} ${await responseBody(resp)}`
        ).toBeTruthy();
        createdCodes.push(type.code);
      }
      const expectedCount = baselineCount + 2;

      // Given I am on the export job page
      // When I launch the export job
      const jobId = await launchExportFromJobPage(page, exportJobCode);

      // And I wait for the job to finish
      const execution = await waitForJobExecutionViaApi(page, jobId);
      const dump = JSON.stringify(execution);
      expect(execution.status, `Export job did not complete: ${dump}`).toBe('COMPLETED');
      const exportStep = execution.stepExecutions?.find((step: any) => 'export' === step.label);
      expect(exportStep, `No "export" step: ${dump}`).toBeTruthy();
      expect(getStepSummaryValue(exportStep, 'read', 'read'), `Step summary: ${dump}`).toBe(expectedCount);
      expect(getStepSummaryValue(exportStep, 'write', 'written'), `Step summary: ${dump}`).toBe(expectedCount);

      // The launch left the page on #/job/show/{id} (re-assigning the same hash would fire no hashchange).
      await waitForJobCompletion(page);
      await expect(page.locator('[data-testid="job-status"]')).toContainText(/completed/i, {timeout: 15_000});

      // Then the file should contain one row per association type
      const {text, header, rows} = await readExportedCsv(page, jobId);
      const csv = `CSV body:\n${text}`;
      expect(new Set(header).size, `Duplicate CSV headers. ${csv}`).toBe(header.length);
      expect(header[0], csv).toBe('code');
      expect(header, csv).toEqual(expect.arrayContaining(['label-en_US', 'is_two_way', 'is_quantified']));
      expect(rows, csv).toHaveLength(expectedCount);
      for (const row of rows) {
        expect(row, `Malformed row. ${csv}`).toHaveLength(header.length);
      }

      for (const type of types) {
        const matching = rows.filter(row => column(header, row, 'code') === type.code);
        expect(matching, `Expected exactly one row for ${type.code}. ${csv}`).toHaveLength(1);
        const [row] = matching;
        expect(column(header, row, 'label-en_US'), `label-en_US of ${type.code}. ${csv}`).toBe(type.label);
        // StandardToFlat\AssociationType casts both flags to int.
        expect(column(header, row, 'is_two_way'), `is_two_way of ${type.code}. ${csv}`).toBe(type.isTwoWay ? '1' : '0');
        expect(column(header, row, 'is_quantified'), `is_quantified of ${type.code}. ${csv}`).toBe(
          type.isQuantified ? '1' : '0'
        );
      }
    } finally {
      // Best-effort: deleteAssociationTypeViaApi only warns, so a cleanup problem never hides the test's own error.
      for (const code of createdCodes) {
        await deleteAssociationTypeViaApi(page, code);
      }
    }
  });
});
