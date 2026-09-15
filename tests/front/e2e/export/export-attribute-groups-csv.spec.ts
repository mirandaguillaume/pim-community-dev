import {test, expect, Page} from '../fixtures/coverage-fixture';
import {
  login,
  createAttributeGroupViaApi,
  createAttributeViaApi,
  deleteAttributeViaApi,
  launchExportFromJobPage,
  waitForJobExecutionViaApi,
  readExportedCsv,
  getStepSummaryValue,
  resolveJobCode,
  responseBody,
  XHR_HEADER,
} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/attribute-group/export_attribute_groups_csv.feature:7
 *   "Successfully export attribute groups"
 *
 * Behat: footwear catalog, local storage, Julia launches the export from the export job page, then "Read 6" and
 * "Written 6" on the job page, and the exported file compared with the 6 footwear groups. That step checked the
 * header as a set and that each expected row's values appear in a row.
 *
 * Adaptations:
 * - Catalog: footwear -> icecat. The job is csv_footwear_attribute_group_export when it exists, otherwise the icecat
 *   csv_attribute_group_export (icecat_demo_dev/jobs.yml). The 6 fixture groups become 2 disposable groups. The
 *   exact footwear CSV is pinned by ExportAttributeGroupIntegration.php, and StandardToFlat\AttributeGroup by
 *   AttributeGroupTest.php.
 * - User: Julia -> admin, who holds pim_enrich_attributegroup_create (AttributeGroupController::createAction).
 * - Launch: like Behat, the "Export now" button of the export job page (launchExportFromJobPage in pim.ts).
 * - "Read 6" / "Written 6" -> baseline + 2, both in the step summary returned by the API and in the summary cells of
 *   the job page (InnerTable.tsx renders each entry as a key cell followed by its value cell).
 * - File: storage `local` is dropped. It needs the import_export_local_storage feature flag, which only Behat and
 *   the PHPUnit JobLauncher enable; the icecat job has no storage configuration, so the CSV is read from the job
 *   archive (readExportedCsv). Checked: the column order (code, label-*, attributes, sort_order: DefaultColumnSorter),
 *   one row per group, and the columns of the 2 disposable rows by name.
 *
 * Data, created one at a time (createAction assigns the max sort_order + 1, so concurrent creates can collide):
 * group A with en_US and fr_FR labels and 2 text attributes, group B with an en_US label and 1 text attribute.
 * Every icecat channel activates en_US, fr_FR and de_DE (icecat_demo_dev/channels.csv) and the icecat groups carry
 * labels in all three, so the three label columns are exported and the labels A and B lack are empty.
 * Cleanup in `finally`, best-effort, in the order bulk-delete-attribute-groups.spec.ts uses: the attributes first,
 * then the groups (DELETE /rest/attribute-group/{code} launches a delete_attribute_groups job).
 */

async function getAttributeGroupCount(page: Page): Promise<number> {
  const resp = await page.request.get('/rest/attribute-group/', {headers: XHR_HEADER, timeout: 30_000});
  expect(resp.ok(), `List attribute groups failed: ${resp.status()} ${await responseBody(resp)}`).toBeTruthy();
  const groups = await resp.json();
  return Object.keys(groups).length;
}

/**
 * GET /rest/attribute-group/{code} (pim_enrich_attributegroup_rest_get). The internal_api normalizer wraps the
 * standard one, so the body has the stored sort_order.
 */
async function getAttributeGroupViaApi(page: Page, code: string): Promise<any> {
  const resp = await page.request.get(`/rest/attribute-group/${code}`, {headers: XHR_HEADER, timeout: 30_000});
  expect(resp.ok(), `Get attribute group ${code} failed: ${resp.status()} ${await responseBody(resp)}`).toBeTruthy();
  return resp.json();
}

function column(header: string[], row: string[], name: string): string | undefined {
  const index = header.indexOf(name);
  return -1 === index ? undefined : row[index];
}

/**
 * Best-effort cleanup that never masks the test result: every problem is only logged.
 */
async function cleanUp(page: Page, attributeCodes: string[], groupCodes: string[]): Promise<void> {
  for (const code of attributeCodes) {
    try {
      const resp = await deleteAttributeViaApi(page, code);
      if (!resp.ok() && 404 !== resp.status()) {
        console.warn(`[cleanup] DELETE /rest/attribute/${code} refused: ${resp.status()} ${await responseBody(resp)}`);
      }
    } catch (e) {
      console.warn(`[cleanup] attribute ${code}: ${(e as Error).message}`);
    }
  }
  for (const code of groupCodes) {
    try {
      const existing = await page.request.get(`/rest/attribute-group/${code}`, {headers: XHR_HEADER, timeout: 30_000});
      if (404 === existing.status()) continue;
      const resp = await page.request.delete(`/rest/attribute-group/${code}`, {headers: XHR_HEADER, timeout: 30_000});
      if (!resp.ok()) {
        console.warn(
          `[cleanup] DELETE /rest/attribute-group/${code} refused: ${resp.status()} ${await responseBody(resp)}`
        );
      }
    } catch (e) {
      console.warn(`[cleanup] attribute group ${code}: ${(e as Error).message}`);
    }
  }
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
    const groupA = {
      code: `pw_group_a_${ts}`,
      labels: {en_US: `PW group A ${ts}`, fr_FR: `PW groupe A ${ts}`},
      attributes: [`pw_attr_a1_${ts}`, `pw_attr_a2_${ts}`],
    };
    const groupB = {
      code: `pw_group_b_${ts}`,
      labels: {en_US: `PW group B ${ts}`},
      attributes: [`pw_attr_b1_${ts}`],
    };
    const createdGroups: string[] = [];
    const createdAttributes: string[] = [];

    const baselineCount = await getAttributeGroupCount(page);

    try {
      for (const group of [groupA, groupB]) {
        const resp = await createAttributeGroupViaApi(page, group.code, group.labels);
        expect(
          resp.ok(),
          `Create attribute group ${group.code} failed: ${resp.status()} ${await responseBody(resp)}`
        ).toBeTruthy();
        createdGroups.push(group.code);
      }
      for (const group of [groupA, groupB]) {
        for (const code of group.attributes) {
          const resp = await createAttributeViaApi(page, {code, type: 'pim_catalog_text', group: group.code});
          expect(
            resp.ok(),
            `Create attribute ${code} in ${group.code} failed: ${resp.status()} ${await responseBody(resp)}`
          ).toBeTruthy();
          createdAttributes.push(code);
        }
      }

      const expectedSortOrder = new Map<string, string>();
      for (const group of [groupA, groupB]) {
        const body = await getAttributeGroupViaApi(page, group.code);
        expect(typeof body?.sort_order, `No sort_order for ${group.code}: ${JSON.stringify(body)}`).toBe('number');
        expectedSortOrder.set(group.code, String(body.sort_order));
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

      // Then I should see the text "Read N" and "Written N". The launch left the page on #/job/show/{id}.
      await expect(page.locator('[data-testid="job-status"]')).toContainText(/completed/i, {timeout: 60_000});
      for (const key of ['read', 'written']) {
        await expect(
          page.getByRole('cell', {name: key, exact: true}).locator('xpath=following-sibling::td[1]'),
          `"${key}" summary cell of job ${jobId}`
        ).toHaveText(String(expectedCount), {timeout: 30_000});
      }

      // And the exported file should contain the attribute groups
      const {text, header, rows} = await readExportedCsv(page, jobId);
      const csv = `CSV body:\n${text}`;
      expect(rows, csv).toHaveLength(expectedCount);
      expect(new Set(header).size, `Duplicate CSV headers. ${csv}`).toBe(header.length);
      expect(header, csv).toEqual([
        'code',
        ...header.filter(name => name.startsWith('label-')),
        'attributes',
        'sort_order',
      ]);
      expect(header, csv).toEqual(expect.arrayContaining(['label-en_US', 'label-fr_FR', 'label-de_DE']));
      for (const row of rows) {
        expect(row, `Malformed row. ${csv}`).toHaveLength(header.length);
      }

      const rowOf = (code: string): string[] => {
        const matching = rows.filter(row => column(header, row, 'code') === code);
        expect(matching, `Expected exactly one row for ${code}. ${csv}`).toHaveLength(1);
        return matching[0];
      };

      const rowA = rowOf(groupA.code);
      expect(column(header, rowA, 'label-en_US'), csv).toBe(groupA.labels.en_US);
      expect(column(header, rowA, 'label-fr_FR'), csv).toBe(groupA.labels.fr_FR);
      expect(column(header, rowA, 'label-de_DE'), csv).toBe('');
      // Attribute order inside the cell follows the database, not the creation order: compare as sorted lists.
      expect(column(header, rowA, 'attributes')?.split(',').sort(), csv).toEqual([...groupA.attributes].sort());
      expect(column(header, rowA, 'sort_order'), csv).toBe(expectedSortOrder.get(groupA.code));

      const rowB = rowOf(groupB.code);
      expect(column(header, rowB, 'label-en_US'), csv).toBe(groupB.labels.en_US);
      expect(column(header, rowB, 'label-fr_FR'), csv).toBe('');
      expect(column(header, rowB, 'label-de_DE'), csv).toBe('');
      expect(column(header, rowB, 'attributes'), csv).toBe(groupB.attributes[0]);
      expect(column(header, rowB, 'sort_order'), csv).toBe(expectedSortOrder.get(groupB.code));
    } finally {
      await cleanUp(page, createdAttributes, createdGroups);
    }
  });
});
