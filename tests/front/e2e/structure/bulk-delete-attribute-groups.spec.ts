import type {Locator} from '@playwright/test';
import {test, expect} from '../fixtures/coverage-fixture';
import type {Page} from '../fixtures/coverage-fixture';
import {
  login,
  createAttributeGroupViaApi,
  createAttributeViaApi,
  getJobExecutionIdsViaApi,
  getJobNotificationViaApi,
  getStepSummaryValue,
  waitForJobExecutionViaApi,
  waitForNewJobExecutionIds,
} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/attribute-group/bulk_delete_attribute_groups.feature:6
 *   "Successfully bulk delete attribute groups"
 *
 * Drives the React attribute-groups grid (settings-ui AttributeGroupsIndex), its mass-delete modal
 * (MassDeleteAttributeGroupsModal on top of the shared DoubleCheckDeleteModal) and the JSON POST sent by
 * the useMassDeleteAttributeGroups hook. Then it checks the delete_attribute_groups job result over REST,
 * its report page, its notification (REST and panel) and the grid afterwards.
 *
 * Adaptations:
 * - Logs in as admin instead of Julia. Accepted gap: the grid only renders row checkboxes when
 *   pim_enrich_attributegroup_mass_delete is granted (AttributeGroupList.tsx:37), and the grant for Julia's
 *   ROLE_CATALOG_MANAGER is not checked here. The controller's 403 branch is covered by
 *   tests/back/Pim/Structure/Unit/Bundle/Infrastructure/Controller/MassDeleteAttributeGroupsControllerTest.php.
 * - Two labelled disposable groups, the first holding one disposable text attribute so the
 *   move_child_attributes step has work, plus the real 'other' group (icecat label "Other"), instead of the
 *   footwear catalog's Sizes and Colors.
 * - "I wait for the job to finish" / "the last executed job resume": the POST answers an empty 200
 *   (MassDeleteAttributeGroupsController.php:63), so the spec snapshots the highest delete_attribute_groups
 *   execution id in the process tracker before confirming, takes the new id, and asserts exactly one
 *   execution was launched. Older executions of the same job code (e.g. the afterAll of
 *   product/compare-and-copy-localized-fields.spec.ts calls DELETE /rest/attribute-group/{code}, and
 *   AttributeGroupController::removeAction launches delete_attribute_groups) have lower ids, because
 *   QueueJobLauncher::launch (:47) inserts the row before the launching request answers.
 * - "COMPLETED": the badge text is translate('akeneo_job.job_status.COMPLETED') = "Completed"; Behat saw
 *   upper case because Badge.tsx:13 applies text-transform: uppercase. Matched case-insensitively.
 * - "I should have 1 new notification" is dropped: earlier specs in the same shard leave unread admin
 *   notifications behind. The notification is matched by its /job/show/{id} url instead.
 * - The warning reason for 'other' is matched on its common wording only (see the comment at that check).
 * - Second test, not in Behat: the replacement group. The controller falls back to 'other' when
 *   `replacement_attribute_group` is missing (MassDeleteAttributeGroupsController.php:50) and the modal also
 *   defaults to 'other' (MassDeleteAttributeGroupsModal.tsx:27-29), so the first test cannot catch a key
 *   renamed on the controller side. The second test picks a disposable group in the modal and checks that the
 *   attribute lands in it. It never selects 'other' with a non-default replacement:
 *   AttributeRepository::getAttributesByGroups (:333-350) would move every attribute of the shard's 'other'
 *   group into the disposable one.
 *
 * Selectors traced from:
 * - Grid route: NavigationHelper 'attribute groups' = '#/configuration/attribute-group/'. The groups are
 *   fetched once on mount (useAttributeGroups.ts:17-21: fetch, then response.json(), then setState), so a
 *   rendered row is awaited after the /rest/attribute-group/list response before asserting absent rows.
 * - Row title cell: AttributeGroupRow.tsx:53 renders getLabel(labels, catalogLocale, code); rows are matched by
 *   that cell's exact accessible name.
 * - Row checkbox: TableRow.tsx:240-251 puts it in a td that stays aria-hidden (and opacity 0, :101) until the row
 *   is selected, hence includeHidden. Checkbox.tsx:172-174 gives role="checkbox" and aria-checked. Both the
 *   checkbox and its td stop propagation, so the row's edit redirect does not fire.
 * - Selection label: AttributeGroupsIndex.tsx:157 ('{{ count }} attribute groups selected'). Toolbar "Delete":
 *   MassDeleteAttributeGroupsModal.tsx:58-60, rendered only when selectedCount > 0 (AttributeGroupsIndex.tsx:160).
 * - Modal: Modal.tsx:147 role="dialog"; title "Confirm deletion" (DeleteModal.tsx:40). Confirmation texts:
 *   MassDeleteAttributeGroupsModal.tsx:74-92. Typed phrase: DoubleCheckDeleteModal.tsx:34 TextField, labelled by
 *   Field.tsx:118; the confirm Button is disabled until the phrase matches (DoubleCheckDeleteModal.tsx:20).
 * - Replacement SelectInput: rendered only when the impacted attribute count is > 0
 *   (MassDeleteAttributeGroupsModal.tsx:82-118). SelectInput.tsx:363-385 renders the selected option next to the
 *   search textbox in the same container; typing filters the options (:246-255, :264); each option carries
 *   data-testid=<group code> (:435) in a portal overlay.
 * - Flash: useNotify resolves to messenger.notify (legacy-bridge dependencies.ts:18), which renders a
 *   MessageBar with role="status" for the info level (MessageBar.tsx:291) that closes after 5s.
 * - Job page: JobExecutionDetail.tsx:262-263 data-testid="job-status"; summary rows are InnerTable.tsx:31-33
 *   key/value rows, whose keys are translated (StepExecutionNormalizer.php:89); warnings are list items
 *   (WarningHelper.tsx:27-30).
 * - Notification panel: notification.html:1 `.notification-link` opens it (notifications.js:30, list loaded only
 *   while the collection is empty, :123). Each item is notification-list.html:1-12:
 *   a.AknNotification-link[href="#<url>"] holding .AknNotification-status--<type>, .AknNotification-title
 *   ("Deletion", pim_notification.types.attribute_group_mass_delete) and .AknNotification-message. Every render of
 *   the user menu builds a new notifications view and calls refresh() (user-navigation.js:45-52); when that
 *   count_unread answer differs from the indicator, the collection is reset (notifications.js:73), so the answer
 *   is awaited before the panel is opened. A job notification that lands while the panel is open can still
 *   reset it; nothing in this spec launches one.
 */

const JOB_CODE = 'delete_attribute_groups';
const OTHER = 'other';
const OTHER_LABEL = 'Other';
const XHR = {'X-Requested-With': 'XMLHttpRequest'};

type AttributeGroupListItem = {code: string; labels: Record<string, string>; attribute_count: number};
type StepExecution = {
  label: string;
  summary: Record<string, unknown>;
  warnings: Array<{reason: string; item: Record<string, unknown>}>;
};

type ResponseLike = {status(): number; text(): Promise<string>};
const describeResponse = async (resp: ResponseLike) => `${resp.status()} ${await resp.text().catch(() => '')}`;

async function createGroup(page: Page, code: string, label: string): Promise<void> {
  const resp = await createAttributeGroupViaApi(page, code, {en_US: label});
  expect(resp.ok(), `Create attribute group ${code} failed: ${await describeResponse(resp)}`).toBe(true);
}

async function createTextAttribute(page: Page, code: string, group: string): Promise<void> {
  const resp = await createAttributeViaApi(page, {code, type: 'pim_catalog_text', group});
  expect(resp.ok(), `Create attribute ${code} in ${group} failed: ${await describeResponse(resp)}`).toBe(true);
}

async function expectAttributeInGroup(page: Page, attributeCode: string, groupCode: string): Promise<void> {
  const resp = await page.request.get(`/rest/attribute/${attributeCode}`, {headers: XHR});
  const text = await resp.text();
  expect(resp.ok(), `GET /rest/attribute/${attributeCode} failed: ${resp.status()} ${text}`).toBe(true);
  // The internal API reuses the standard normalizer, which gives the group as a code (AttributeNormalizer.php:35).
  expect(JSON.parse(text).group, `attribute ${attributeCode}: ${text}`).toBe(groupCode);
}

async function openAttributeGroupsGrid(page: Page): Promise<AttributeGroupListItem[]> {
  const listResponse = page.waitForResponse(
    resp => new URL(resp.url()).pathname === '/rest/attribute-group/list' && resp.request().method() === 'GET',
    {timeout: 90_000}
  );
  listResponse.catch(() => {});
  await new NavigationHelper(page).goTo('attribute groups');
  const resp = await listResponse;
  const text = await resp.text();
  expect(resp.ok(), `GET /rest/attribute-group/list failed: ${resp.status()} ${text}`).toBe(true);
  const body = JSON.parse(text);
  return Array.isArray(body) ? body : Object.values(body);
}

function rowFor(page: Page, label: string): Locator {
  return page.getByRole('row').filter({has: page.getByRole('cell', {name: label, exact: true})});
}

async function selectRow(page: Page, label: string): Promise<void> {
  const checkbox = rowFor(page, label).getByRole('checkbox', {includeHidden: true});
  await checkbox.click({timeout: 15_000});
  await expect(checkbox, `grid row "${label}" was not selected`).toHaveAttribute('aria-checked', 'true', {
    timeout: 10_000,
  });
}

async function openMassDeleteModal(page: Page): Promise<Locator> {
  await page.getByRole('button', {name: 'Delete', exact: true}).click({timeout: 15_000});
  const dialog = page.getByRole('dialog').filter({hasText: 'Confirm deletion'});
  await expect(dialog, 'mass-delete modal did not open').toBeVisible({timeout: 15_000});
  return dialog;
}

function replacementInput(dialog: Locator): Locator {
  return dialog.getByRole('textbox', {name: /^Please select the attribute group to move /});
}

// The SelectInput container that holds both the search textbox and the selected option's label.
function replacementControl(dialog: Locator): Locator {
  return replacementInput(dialog).locator('xpath=..');
}

/**
 * Type the confirmation phrase, confirm, and return the JSON body the hook really sent, plus the highest
 * delete_attribute_groups execution id seen just before confirming.
 */
async function confirmMassDelete(
  page: Page,
  dialog: Locator
): Promise<{body: {codes: string[]; replacement_attribute_group: string | null}; prevMaxId: number}> {
  const confirm = dialog.getByRole('button', {name: 'Delete', exact: true});
  await expect(confirm, 'confirm must stay disabled until "delete" is typed').toBeDisabled({timeout: 10_000});
  await dialog.getByRole('textbox', {name: 'Please type "delete"', exact: true}).fill('delete', {timeout: 10_000});
  await expect(confirm).toBeEnabled({timeout: 10_000});

  const prevMaxId = Math.max(0, ...(await getJobExecutionIdsViaApi(page, JOB_CODE)));

  const launch = page.waitForResponse(
    resp => new URL(resp.url()).pathname === '/rest/attribute-group/mass-delete' && resp.request().method() === 'POST',
    {timeout: 30_000}
  );
  launch.catch(() => {});
  await confirm.click({timeout: 10_000});
  const launchResp = await launch;
  expect(launchResp.status(), `POST /rest/attribute-group/mass-delete: ${await describeResponse(launchResp)}`).toBe(
    200
  );
  const body = launchResp.request().postDataJSON();

  // The info flash closes after 5s, so it is checked right after the response.
  await expect(page.getByRole('status').filter({hasText: 'The deletion has started.'})).toBeVisible({
    timeout: 10_000,
  });
  await expect(dialog, 'mass-delete modal did not close after a successful launch').toHaveCount(0, {
    timeout: 15_000,
  });

  return {body, prevMaxId};
}

/**
 * Find the single execution launched after `prevMaxId`, wait for it to finish, and return it with its two steps.
 */
async function waitForLaunchedJob(
  page: Page,
  prevMaxId: number
): Promise<{jobId: number; dump: string; moveStep: StepExecution; deleteStep: StepExecution}> {
  const [jobId] = await waitForNewJobExecutionIds(page, JOB_CODE, prevMaxId);
  const execution = await waitForJobExecutionViaApi(page, String(jobId));
  const dump = JSON.stringify(execution);

  // Checked once the job is over, so a second launch (e.g. a double submit) has long been registered.
  const launched = (await getJobExecutionIdsViaApi(page, JOB_CODE)).filter(id => id > prevMaxId).sort((a, b) => a - b);
  expect(launched, `expected exactly one ${JOB_CODE} execution newer than #${prevMaxId}`).toEqual([jobId]);

  expect(execution.jobInstance?.code, dump).toBe(JOB_CODE);
  expect(execution.status, dump).toBe('COMPLETED');

  const steps: StepExecution[] = Array.isArray(execution.stepExecutions) ? execution.stepExecutions : [];
  const findStep = (name: string): StepExecution => {
    const step = steps.find(candidate => candidate.label === name);
    expect(step, `job ${jobId} has no ${name} step: ${dump}`).toBeTruthy();
    return step!;
  };

  return {jobId, dump, moveStep: findStep('move_child_attributes'), deleteStep: findStep('delete_attribute_groups')};
}

/**
 * Best-effort cleanup that never masks the test result. Attributes go first: a group still holding one would be
 * skipped by the delete job. DELETE /rest/attribute-group/{code} only LAUNCHES another delete_attribute_groups job
 * (AttributeGroupController::removeAction), so it is sent only for groups that still exist, after every job check.
 * 'other' is never deleted.
 */
async function cleanUp(page: Page, attributeCodes: string[], groupCodes: string[]): Promise<void> {
  const attempt = async (label: string, action: () => Promise<void>) => {
    try {
      await action();
    } catch (e) {
      console.warn(`[cleanup] ${label}: ${(e as Error).message}`);
    }
  };

  for (const code of attributeCodes) {
    await attempt(`attribute ${code}`, async () => {
      const resp = await page.request.delete(`/rest/attribute/${code}`, {headers: XHR, timeout: 30_000});
      if (!resp.ok() && 404 !== resp.status()) {
        console.warn(`[cleanup] DELETE /rest/attribute/${code} refused: ${await describeResponse(resp)}`);
      }
    });
  }
  for (const code of groupCodes.filter(groupCode => OTHER !== groupCode)) {
    await attempt(`attribute group ${code}`, async () => {
      const existing = await page.request.get(`/rest/attribute-group/${code}`, {headers: XHR, timeout: 30_000});
      if (404 === existing.status()) return;
      const resp = await page.request.delete(`/rest/attribute-group/${code}`, {headers: XHR, timeout: 30_000});
      if (!resp.ok()) {
        console.warn(`[cleanup] DELETE /rest/attribute-group/${code} refused: ${await describeResponse(resp)}`);
      }
    });
  }
}

test.describe('Bulk delete attribute groups', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('deletes the selected groups, moves their attributes to "Other" and keeps "Other"', async ({page}) => {
    // Grid, job (up to 180s), report page and notification.
    test.setTimeout(420_000);

    const ts = Date.now();
    const codeA = `pw_bulk_grp_a_${ts}`;
    const labelA = `PW bulk A ${ts}`;
    const codeB = `pw_bulk_grp_b_${ts}`;
    const labelB = `PW bulk B ${ts}`;
    const attribute = `pw_bulk_grp_attr_${ts}`;

    try {
      // Sequential: concurrent creates sharing a parent collection intermittently fail.
      await createGroup(page, codeA, labelA);
      await createGroup(page, codeB, labelB);
      await createTextAttribute(page, attribute, codeA);

      // "When I am on the attribute groups page" / "I select rows Sizes, Colors and Other"
      await openAttributeGroupsGrid(page);
      for (const label of [labelA, labelB, OTHER_LABEL]) {
        await expect(rowFor(page, label), `grid row "${label}" not rendered`).toBeVisible({timeout: 30_000});
      }
      for (const label of [labelA, labelB, OTHER_LABEL]) {
        await selectRow(page, label);
      }
      await expect(page.getByText('3 attribute groups selected', {exact: true})).toBeVisible({timeout: 10_000});

      // "I press the "Delete" button"
      const dialog = await openMassDeleteModal(page);
      await expect(dialog).toContainText('Are you sure you want to delete these 3 attribute groups?');
      // No exact count: the impacted attribute count also adds Other's attribute_count, which earlier specs change.
      await expect(dialog).toContainText(/Deleting these attribute groups will impact \d+ related attributes?\./);
      await expect(
        replacementControl(dialog).getByText(OTHER_LABEL, {exact: true}),
        'the replacement group should default to Other'
      ).toBeVisible({timeout: 10_000});

      // "I fill the input labelled 'Please type "delete"' with 'delete'" / "I press the "Delete" button in the popin"
      const {body, prevMaxId} = await confirmMassDelete(page, dialog);
      expect([...body.codes].sort(), `mass-delete body: ${JSON.stringify(body)}`).toEqual([codeA, codeB, OTHER].sort());
      expect(body.replacement_attribute_group, `mass-delete body: ${JSON.stringify(body)}`).toBe(OTHER);

      // "I wait for the "delete_attribute_groups" job to finish"
      const {jobId, dump, moveStep, deleteStep} = await waitForLaunchedJob(page, prevMaxId);
      expect(getStepSummaryValue(deleteStep, 'deleted_attribute_groups', 'Deleted attribute groups'), dump).toBe(2);
      expect(getStepSummaryValue(deleteStep, 'skipped_attribute_groups', 'Skipped attribute groups'), dump).toBe(1);
      expect(
        Number(getStepSummaryValue(moveStep, 'moved_attributes', 'Moved attributes')),
        dump
      ).toBeGreaterThanOrEqual(1);
      // Two PRE_REMOVE listeners refuse 'other' at this point: CheckAttributeGroupOtherCannotBeRemovedSubscriber
      // ('Attribute group "other" cannot be removed.') and, since the attribute was just moved into it,
      // CheckAttributeGroupWithAttributeCannotBeRemovedSubscriber ('Attribute group containing attributes cannot be
      // removed...'). Both run at the default priority, so their order in Structure event_subscribers.yml (:97, then
      // :100) decides which reason DeleteAttributeGroupsTasklet records. Only the wording they share is asserted.
      expect(
        deleteStep.warnings.map(warning => warning.item),
        dump
      ).toEqual([{code: OTHER}]);
      expect(deleteStep.warnings[0].reason, dump).toMatch(/cannot be removed/);

      // Entity effects. The job is over; the short retry only absorbs cache timing.
      await expect(async () => {
        for (const code of [codeA, codeB]) {
          const resp = await page.request.get(`/rest/attribute-group/${code}`, {headers: XHR});
          expect(resp.status(), `GET /rest/attribute-group/${code}: ${await describeResponse(resp)}`).toBe(404);
        }
      }).toPass({timeout: 15_000});
      const otherResp = await page.request.get(`/rest/attribute-group/${OTHER}`, {headers: XHR});
      expect(otherResp.ok(), `'other' should survive: ${await describeResponse(otherResp)}`).toBe(true);
      await expectAttributeInGroup(page, attribute, OTHER);

      // "I should see notification: warning | Deletion Bulk delete of attribute groups finished with some warnings"
      // (REST first: JobExecutionNotifier creates it after the job ends.)
      await expect(async () => {
        const notification = await getJobNotificationViaApi(page, jobId);
        expect(notification, `no notification links to /job/show/${jobId} yet`).toBeTruthy();
        expect(notification?.type, JSON.stringify(notification)).toBe('warning');
        expect(notification?.message, JSON.stringify(notification)).toBe(
          'Bulk delete of attribute groups finished with some warnings'
        );
        expect(notification?.actionType, JSON.stringify(notification)).toBe('attribute_group_mass_delete');
      }).toPass({timeout: 60_000});

      // "When I go on the last executed job resume of "delete_attribute_groups""
      const countUnread = page.waitForResponse(resp => new URL(resp.url()).pathname === '/notification/count_unread', {
        timeout: 60_000,
      });
      countUnread.catch(() => {});
      await page.evaluate(id => {
        window.location.hash = `#/job/show/${id}`;
      }, jobId);
      // "Then I should see the text "COMPLETED"" / "And I should see the text "Deleted attribute groups 2""
      await expect(page.locator('[data-testid="job-status"]')).toHaveText(/^completed$/i, {timeout: 30_000});
      // Anchored names: the outer step row's name also holds the whole inner summary table.
      await expect(page.getByRole('row', {name: /^Deleted attribute groups\s*2$/})).toBeVisible({timeout: 30_000});
      await expect(page.getByRole('row', {name: /^Skipped attribute groups\s*1$/})).toBeVisible({timeout: 10_000});
      await expect(page.getByRole('listitem').filter({hasText: /cannot be removed/})).toBeVisible({timeout: 10_000});

      // Notification panel (legacy Backbone template, no ARIA roles: CSS classes are the only hooks).
      const unread = await countUnread;
      expect(unread.ok(), `GET /notification/count_unread: ${await describeResponse(unread)}`).toBe(true);
      const listResponse = page.waitForResponse(resp => new URL(resp.url()).pathname === '/notification/list', {
        timeout: 30_000,
      });
      listResponse.catch(() => {});
      await page.locator('.notification-link').click({timeout: 15_000});
      const list = await listResponse;
      expect(list.ok(), `GET /notification/list: ${await describeResponse(list)}`).toBe(true);
      const item = page.locator(`.AknNotification-link[href="#/job/show/${jobId}"]`);
      await expect(item, `notification panel has no entry for /job/show/${jobId}`).toBeVisible({timeout: 15_000});
      await expect(item.locator('.AknNotification-title')).toHaveText('Deletion');
      await expect(item.locator('.AknNotification-message')).toHaveText(
        'Bulk delete of attribute groups finished with some warnings'
      );
      await expect(item.locator('.AknNotification-status')).toHaveClass(/AknNotification-status--warning/);

      // "And I am on the attribute groups page" / "I should not see Sizes, Colors" / "I should see Other"
      const groupsAfter = await openAttributeGroupsGrid(page);
      const codesAfter = groupsAfter.map(group => group.code);
      expect(codesAfter, `list after the job: ${JSON.stringify(codesAfter)}`).not.toContain(codeA);
      expect(codesAfter, `list after the job: ${JSON.stringify(codesAfter)}`).not.toContain(codeB);
      const otherAfter = groupsAfter.find(group => OTHER === group.code);
      expect(
        otherAfter?.attribute_count ?? 0,
        `'other' after the job: ${JSON.stringify(otherAfter)}`
      ).toBeGreaterThanOrEqual(1);
      // Positive anchor first: the list renders after response.json(), so absent rows are only meaningful once
      // a row is on screen.
      await expect(rowFor(page, OTHER_LABEL)).toBeVisible({timeout: 30_000});
      await expect(rowFor(page, labelA)).toHaveCount(0);
      await expect(rowFor(page, labelB)).toHaveCount(0);
    } finally {
      await cleanUp(page, [attribute], [codeA, codeB]);
    }
  });

  test('moves the related attributes to the replacement group picked in the modal', async ({page}) => {
    test.setTimeout(300_000);

    const ts = Date.now();
    const sourceCode = `pw_bulk_grp_src_${ts}`;
    const sourceLabel = `PW bulk source ${ts}`;
    const targetCode = `pw_bulk_grp_dst_${ts}`;
    const targetLabel = `PW bulk target ${ts}`;
    const attribute = `pw_bulk_grp_moved_${ts}`;

    try {
      await createGroup(page, sourceCode, sourceLabel);
      await createGroup(page, targetCode, targetLabel);
      await createTextAttribute(page, attribute, sourceCode);

      await openAttributeGroupsGrid(page);
      for (const label of [sourceLabel, targetLabel]) {
        await expect(rowFor(page, label), `grid row "${label}" not rendered`).toBeVisible({timeout: 30_000});
      }
      await selectRow(page, sourceLabel);
      await expect(page.getByText('1 attribute group selected', {exact: true})).toBeVisible({timeout: 10_000});

      const dialog = await openMassDeleteModal(page);
      await expect(dialog).toContainText('Are you sure you want to delete this attribute group?');
      await expect(dialog).toContainText('Deleting these attribute groups will impact 1 related attribute.');
      await expect(replacementControl(dialog).getByText(OTHER_LABEL, {exact: true})).toBeVisible({timeout: 10_000});

      // Typing filters the options on code + label; the options overlay is a portal outside the dialog.
      await replacementInput(dialog).fill(targetCode, {timeout: 10_000});
      await page.getByTestId(targetCode).click({timeout: 10_000});
      await expect(
        replacementControl(dialog).getByText(targetLabel, {exact: true}),
        `the replacement group should now be "${targetLabel}"`
      ).toBeVisible({timeout: 10_000});

      const {body, prevMaxId} = await confirmMassDelete(page, dialog);
      expect(body.codes, `mass-delete body: ${JSON.stringify(body)}`).toEqual([sourceCode]);
      expect(body.replacement_attribute_group, `mass-delete body: ${JSON.stringify(body)}`).toBe(targetCode);

      const {dump, moveStep, deleteStep} = await waitForLaunchedJob(page, prevMaxId);
      expect(getStepSummaryValue(moveStep, 'moved_attributes', 'Moved attributes'), dump).toBe(1);
      expect(getStepSummaryValue(deleteStep, 'deleted_attribute_groups', 'Deleted attribute groups'), dump).toBe(1);
      expect(getStepSummaryValue(deleteStep, 'skipped_attribute_groups', 'Skipped attribute groups'), dump).toBe(0);
      expect(deleteStep.warnings, dump).toEqual([]);

      const sourceResp = await page.request.get(`/rest/attribute-group/${sourceCode}`, {headers: XHR});
      expect(
        sourceResp.status(),
        `GET /rest/attribute-group/${sourceCode}: ${await describeResponse(sourceResp)}`
      ).toBe(404);
      const targetResp = await page.request.get(`/rest/attribute-group/${targetCode}`, {headers: XHR});
      expect(targetResp.ok(), `GET /rest/attribute-group/${targetCode}: ${await describeResponse(targetResp)}`).toBe(
        true
      );
      // Only a replacement key that reached the job moves the attribute here: without it the controller uses 'other'.
      await expectAttributeInGroup(page, attribute, targetCode);
    } finally {
      await cleanUp(page, [attribute], [sourceCode, targetCode]);
    }
  });
});
