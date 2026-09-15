import type {Locator} from '@playwright/test';
import {test, expect} from '../fixtures/coverage-fixture';
import type {Page} from '../fixtures/coverage-fixture';
import {
  login,
  createAttributeViaApi,
  deleteAttributeViaApi,
  expectJobNotificationInPanel,
  getJobExecutionIdsViaApi,
  getJobNotificationViaApi,
  getLatestJobExecutionId,
  getStepSummaryValue,
  responseBody,
  searchProductGrid,
  selectProductsBySku,
  waitForJobExecutionViaApi,
  waitForLoadingMasks,
  waitForNewJobExecutionIds,
  XHR_HEADER,
} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/attribute/bulk_delete_attributes.feature:6
 *   "Successfully bulk delete attributes"
 *
 * Drives the legacy Backbone attribute grid (#/configuration/attribute/): its search box, the row selection, the
 * "Delete" launcher of the bulk actions panel, then AttributeMassDeleteAction (attribute-mass-delete-action.ts): the
 * get-filter request, the DoubleCheckDeleteModal typed confirmation and the launch POST. It then checks the
 * delete_attributes job over REST, the attributes, the notification (REST and panel), the job report page and the
 * grid afterwards.
 *
 * Backend guard: test-playwright does not run on backend-only PRs, so the PHP/YAML half of this flow (the grid
 * mass_actions entry, the get-filter adapter and counter, the launch controller, the job and its notification) is
 * also pinned by tests/back/Pim/Structure/Integration/Attribute/MassDeleteAttributesIntegration.php.
 *
 * Adaptations:
 * - icecat julia/julia instead of the footwear catalog's Julia, both ROLE_CATALOG_MANAGER. Julia holds
 *   pim_enrich_attribute_mass_delete: AddDefaultPrivilegesSubscriber grants every role the full root mask and only
 *   zeroes the ACL classes that are not enabled at creation, and acl.yml gives this one no enabled_at_creation key
 *   (the default is true). The Version_8_0_20230308152730 migration that revokes it never runs on a fresh install,
 *   which marks every migration as executed, and the Playwright seed is a fresh install. Should that change, the
 *   launcher or the POST fails loudly.
 * - 3 labelled disposable text attributes instead of Rating, Manufacturer and Description. icecat has 77 attributes
 *   and the grid sorts by label, so the rows are brought onto the page by searching the timestamp in their codes.
 * - "I wait for the job to finish" / "the last executed job resume": the launch POST answers an empty 200
 *   (MassDeleteAttributeController.php:54), so the spec snapshots the highest delete_attributes execution id before
 *   confirming, takes the new id, and asserts that exactly one execution was launched.
 * - "COMPLETED": the badge text is "Completed", upper-cased by CSS (Badge.tsx text-transform), so it is matched
 *   case-insensitively.
 * - "I should have 1 new notification" is dropped: other julia specs in the same shard leave unread notifications.
 *   The notification is matched by its /job/show/{id} url instead. Deleting the attributes also launches
 *   clean_removed_attribute_job (AttributeRemovalSubscriber), which has no users_to_notify and therefore adds no
 *   notification while the panel is open (JobExecutionNotifier).
 * - Not in Behat: the get-filter query and answer and the launch body are asserted, which pins the contract between
 *   the JS action and the two controllers.
 *
 * Selectors traced from:
 * - Grid: NavigationHelper 'attributes' = '#/configuration/attribute/'. The page loads its rows with
 *   GET /datagrid/attribute-grid/load (pim_datagrid_load); rows are backgrid rows (grid.js rowClassName
 *   AknGrid-bodyRow) whose label cell shows the en_US label.
 * - Search: searchProductGrid(page, term, 'attribute-grid'); its JSDoc explains the readonly input of search-filter.js.
 * - Selection: selectProductsBySku is not product-specific despite its name: it matches tr.AknGrid-bodyRow by text,
 *   checks td.select-row-cell input (select-row-cell.js) and waits for the mass-actions bottom panel, which the
 *   attribute index mounts (UIBundle form_extensions/attribute/index.yml pim-attribute-index-mass-actions).
 * - Launcher: actions-panel.js renders the mass action launchers into div.mass-actions-panel as action-launcher.js
 *   views (tagName 'a', action-launcher-button.html: <a href="javascript:void(0);" title="Delete">Delete</a>). It is
 *   clicked through element.click(), like in openMassEditOperation, because #overlay intercepts coordinate clicks.
 * - get-filter: POST /rest/mass_edit/get-filter with gridName, actionName (mass-action.js:21, the mass_actions key),
 *   inset and values in the query string (attribute-mass-delete-action.ts getMassActionData, mass-action.js
 *   getActionParameters, identifierFieldName 'code'), answered by MassEditController::getFilterAction through
 *   OroToPimGridFilterAdapter::adaptAttributeGrid and ItemsCounter::count.
 * - Modal: DSM Modal role="dialog". Title "Confirm deletion of 3 attributes" and text "Are you sure you want to
 *   delete these attributes?" (Structure jsmessages.en_US.yml, pim_enrich.entity.attribute.module.mass_delete.modal).
 *   The typed phrase is a TextField labelled 'Please type "delete"', and the confirm Button stays disabled until it
 *   matches (DoubleCheckDeleteModal.tsx:20, DeleteModal.tsx:46).
 * - Flash: Messenger.notify('success', ...) renders a MessageBar with role="status" (MessageBar.tsx:291) that closes
 *   after 5s.
 * - Job: StepExecutionNormalizer labels a step with its name, so the step is 'delete_attributes'. Job page:
 *   JobExecutionDetail.tsx:263 data-testid="job-status"; the summary rows are key/value table rows.
 * - Notification: title "Deletion" (Enrichment jsmessages.en_US.yml, pim_notification.types.attribute_mass_delete),
 *   message "Bulk delete of attributes finished" (Structure messages.en_US.yml,
 *   pim_import_export.notification.attribute_mass_delete.success, built by NotificationFactory).
 * - Grid afterwards: the page restores the saved filters into the /load URL (form/common/grid.js applyFilters,
 *   pageable-collection.js processFiltersParams), and the load merges them into its data request
 *   (MetadataParser::getGridData). An empty grid hides its table and shows the .no-data block (grid.js
 *   _updateNoDataBlock).
 */

const JOB_CODE = 'delete_attributes';
const GRID_LOAD_PATH = '/datagrid/attribute-grid/load';

type StepExecution = {
  label: string;
  summary: Record<string, unknown>;
  warnings: Array<{reason: string; item: Record<string, unknown>}>;
};

type GridLoad = {url: string; rows: Array<Record<string, unknown>>};

function gridRows(page: Page, text: string): Locator {
  return page.locator('tr.AknGrid-bodyRow').filter({hasText: text});
}

/** Open the attribute grid and return its /load request url and the rows it answered. */
async function openAttributeGrid(page: Page): Promise<GridLoad> {
  const load = page.waitForResponse(resp => new URL(resp.url()).pathname === GRID_LOAD_PATH, {timeout: 90_000});
  load.catch(() => {});
  await new NavigationHelper(page).goTo('attributes');
  const resp = await load;
  const text = await responseBody(resp);
  expect(resp.ok(), `GET ${GRID_LOAD_PATH} failed: ${resp.status()} ${text}`).toBe(true);
  let data: {data?: unknown};
  try {
    const body = JSON.parse(text);
    data = typeof body.data === 'string' ? JSON.parse(body.data) : body.data;
  } catch {
    throw new Error(`GET ${GRID_LOAD_PATH} did not answer the grid JSON: ${resp.status()} ${text}`);
  }
  await waitForLoadingMasks(page);

  return {url: resp.url(), rows: Array.isArray(data?.data) ? data.data : []};
}

/** Best-effort cleanup that never masks the test result: a 404 means the job already deleted the attribute. */
async function cleanUp(page: Page, codes: string[]): Promise<void> {
  for (const code of codes) {
    try {
      const resp = await deleteAttributeViaApi(page, code);
      if (!resp.ok() && 404 !== resp.status()) {
        console.warn(`[cleanup] DELETE /rest/attribute/${code} refused: ${resp.status()} ${await responseBody(resp)}`);
      }
    } catch (e) {
      console.warn(`[cleanup] attribute ${code}: ${(e as Error).message}`);
    }
  }
}

test.describe('Bulk delete attributes', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'julia', 'julia');
  });

  test('bulk deletes the attributes selected in the grid', async ({page}) => {
    // Grid, job (up to 180s), report page, notification panel and the grid again.
    test.setTimeout(420_000);

    const ts = Date.now();
    const attributes = ['a', 'b', 'c'].map(letter => ({
      code: `pw_bulk_attr_${letter}_${ts}`,
      label: `PW bulk attr ${letter.toUpperCase()} ${ts}`,
    }));
    const codes = attributes.map(attribute => attribute.code);
    const labels = attributes.map(attribute => attribute.label);
    const sortedCodes = [...codes].sort();

    try {
      // Sequential: concurrent creates in the same attribute group intermittently answer 500.
      for (const {code, label} of attributes) {
        const resp = await createAttributeViaApi(page, {
          code,
          type: 'pim_catalog_text',
          group: 'other',
          labels: {en_US: label},
        });
        expect(resp.ok(), `Create attribute ${code} failed: ${resp.status()} ${await responseBody(resp)}`).toBe(true);
      }

      // "When I am on the attributes page"
      await openAttributeGrid(page);
      await searchProductGrid(page, String(ts), 'attribute-grid');
      for (const label of labels) {
        await expect(gridRows(page, label), `grid row "${label}" not rendered`).toBeVisible({timeout: 30_000});
      }

      // "When I select rows Rating, Manufacturer and Description"
      await selectProductsBySku(page, labels);
      await expect(
        gridRows(page, String(ts)).locator('td.select-row-cell input:checked'),
        'the 3 disposable rows should be selected'
      ).toHaveCount(3, {timeout: 10_000});

      // "And I press the "Delete" button in the bulk actions panel"
      const getFilter = page.waitForResponse(
        resp => new URL(resp.url()).pathname === '/rest/mass_edit/get-filter' && resp.request().method() === 'POST',
        {timeout: 30_000}
      );
      getFilter.catch(() => {});
      const launcher = page.locator('.mass-actions-panel').getByRole('link', {name: 'Delete', exact: true});
      await expect(launcher, 'the bulk actions panel has no Delete launcher').toBeVisible({timeout: 15_000});
      await launcher.evaluate(element => (element as HTMLElement).click());
      const filterResp = await getFilter;
      const filterText = await responseBody(filterResp);
      expect(filterResp.status(), `POST /rest/mass_edit/get-filter: ${filterText}`).toBe(200);
      const query = new URL(filterResp.url()).searchParams;
      expect(query.get('gridName'), filterResp.url()).toBe('attribute-grid');
      expect(query.get('actionName'), filterResp.url()).toBe('attribute_delete');
      expect(query.get('inset'), filterResp.url()).toBe('1');
      expect((query.get('values') ?? '').split(',').sort(), filterResp.url()).toEqual(sortedCodes);
      const filterBody = JSON.parse(filterText);
      expect(filterBody.itemsCount, filterText).toBe(3);
      expect(filterBody.filters?.search, filterText).toBeNull();
      expect(Object.keys(filterBody.filters?.options ?? {}), filterText).toEqual(['identifiers']);
      expect([...filterBody.filters.options.identifiers].sort(), filterText).toEqual(sortedCodes);

      const dialog = page.getByRole('dialog').filter({hasText: 'Confirm deletion of 3 attributes'});
      await expect(dialog, 'mass-delete modal did not open').toBeVisible({timeout: 15_000});
      await expect(dialog).toContainText('Are you sure you want to delete these attributes?');
      const confirm = dialog.getByRole('button', {name: 'Delete', exact: true});
      await expect(confirm, 'confirm must stay disabled until "delete" is typed').toBeDisabled({timeout: 10_000});

      // "And I fill the input labelled 'Please type "delete"' with 'delete'"
      await dialog.getByRole('textbox', {name: 'Please type "delete"', exact: true}).fill('delete', {timeout: 10_000});
      await expect(confirm).toBeEnabled({timeout: 10_000});

      // "And I press the "Delete" button"
      const prevMaxId = await getLatestJobExecutionId(page, JOB_CODE);
      const launch = page.waitForResponse(
        resp => new URL(resp.url()).pathname === '/rest/attribute/mass-delete' && resp.request().method() === 'POST',
        {timeout: 30_000}
      );
      launch.catch(() => {});
      await confirm.click({timeout: 10_000});
      const launchResp = await launch;
      expect(launchResp.status(), `POST /rest/attribute/mass-delete: ${await responseBody(launchResp)}`).toBe(200);
      // The controller turns this body into the job's filters: it must be get-filter's answer, untouched.
      expect(launchResp.request().postDataJSON(), 'POST /rest/attribute/mass-delete body').toEqual({
        filters: filterBody.filters,
      });
      // The success flash closes after 5s, so it is checked right after the response.
      await expect(page.getByRole('status').filter({hasText: 'The deletion has started.'})).toBeVisible({
        timeout: 10_000,
      });
      await expect(dialog, 'mass-delete modal did not close after a successful launch').toHaveCount(0, {
        timeout: 15_000,
      });

      // "And I wait for the "delete_attributes" job to finish"
      const [jobId] = await waitForNewJobExecutionIds(page, JOB_CODE, prevMaxId);
      const execution = await waitForJobExecutionViaApi(page, String(jobId));
      const dump = JSON.stringify(execution);
      // Checked once the job is over, so a second launch (e.g. a double submit) has long been registered.
      const launched = (await getJobExecutionIdsViaApi(page, JOB_CODE))
        .filter(id => id > prevMaxId)
        .sort((a, b) => a - b);
      expect(launched, `expected exactly one ${JOB_CODE} execution newer than #${prevMaxId}`).toEqual([jobId]);
      expect(execution.jobInstance?.code, dump).toBe(JOB_CODE);
      expect(execution.status, dump).toBe('COMPLETED');
      const steps: StepExecution[] = Array.isArray(execution.stepExecutions) ? execution.stepExecutions : [];
      const step = steps.find(candidate => candidate.label === JOB_CODE);
      expect(step, `job ${jobId} has no ${JOB_CODE} step: ${dump}`).toBeTruthy();
      expect(getStepSummaryValue(step!, 'deleted_attributes', 'Deleted attributes'), dump).toBe(3);
      expect(getStepSummaryValue(step!, 'skipped_attributes', 'Skipped attributes'), dump).toBe(0);
      expect(step!.warnings, dump).toEqual([]);

      // Entity effects. The job is over; the short retry only absorbs cache timing.
      await expect(async () => {
        for (const code of codes) {
          const resp = await page.request.get(`/rest/attribute/${code}`, {headers: XHR_HEADER});
          expect(resp.status(), `GET /rest/attribute/${code}: ${resp.status()} ${await responseBody(resp)}`).toBe(404);
        }
      }).toPass({timeout: 15_000});

      // "And I should see notification: success | Deletion Bulk delete of attributes finished"
      // (REST first: JobExecutionNotifier creates it after the job ends; the controller notifies the launching user.)
      await expect(async () => {
        const notification = await getJobNotificationViaApi(page, jobId);
        expect(notification, `no notification links to /job/show/${jobId} yet`).toBeTruthy();
        expect(notification?.type, JSON.stringify(notification)).toBe('success');
        expect(notification?.message, JSON.stringify(notification)).toBe('Bulk delete of attributes finished');
        expect(notification?.actionType, JSON.stringify(notification)).toBe('attribute_mass_delete');
      }).toPass({timeout: 60_000});

      // "When I go on the last executed job resume of "delete_attributes""
      await page.evaluate(id => {
        window.location.hash = `#/job/show/${id}`;
      }, jobId);
      // "Then I should see the text "COMPLETED"" / "And I should see the text "Deleted attributes 3""
      await expect(page.locator('[data-testid="job-status"]')).toHaveText(/^completed$/i, {timeout: 30_000});
      // Anchored name: the outer step row's name also holds the whole inner summary table.
      await expect(page.getByRole('row', {name: /^Deleted attributes\s*3$/})).toBeVisible({timeout: 30_000});

      // Notification panel. The job notification is still unread, as the helper requires.
      await expectJobNotificationInPanel(page, jobId, {
        title: 'Deletion',
        message: 'Bulk delete of attributes finished',
        level: 'success',
      });

      // "And I am on the attributes page" / "And I should not see attributes Rating, Manufacturer and Description"
      const after = await openAttributeGrid(page);
      // The load carries the restored search, so its rows are the filtered ones and cannot be vacuously empty.
      expect(decodeURIComponent(after.url), 'the attribute grid did not restore its search').toContain(String(ts));
      const leftovers = after.rows.map(row => String(row.code)).filter(code => codes.includes(code));
      expect(leftovers, `attribute grid rows after the job: ${JSON.stringify(after.rows)}`).toEqual([]);
      // Positive anchors before the absence check: the restored term is in the box and the grid shows its empty state.
      await expect(page.locator('.search-filter input[name="value"]')).toHaveValue(String(ts), {timeout: 30_000});
      await expect(page.locator('.no-data'), 'the filtered attribute grid should be empty').toBeVisible({
        timeout: 30_000,
      });
      await expect(gridRows(page, String(ts))).toHaveCount(0);
    } finally {
      await cleanUp(page, codes);
    }
  });
});
