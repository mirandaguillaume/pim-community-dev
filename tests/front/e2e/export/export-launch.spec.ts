import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  createProductViaApi,
  getProductViaApi,
  saveProduct,
  waitForJobExecutionViaApi,
  responseBody,
  updateProductViaApi,
  createProductExportJobViaApi,
  configureProductExportJobViaApi,
  launchExportFromJobPage,
  readExportedCsv,
  deleteExportJobViaApi,
} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product/export/export_products_by_specific_date.feature:7
 *   "Export only the products updated by the UI since the last export"
 *
 * Flow, as in Behat: configure a product export whose `updated` data filter is "SINCE LAST JOB" on the job
 * itself, launch it (both products exported), edit one product through the product edit form and save it,
 * launch again (only the edited product exported). Both runs are launched with the "Export now" button and
 * both exported files are read back.
 *
 * Backend behaviour under test:
 * - Elasticsearch DateTimeFilter (Pim/Enrichment/Bundle/Elasticsearch/Filter/Field/DateTimeFilter.php:146-171)
 *   resolves the job instance named by the filter value, takes its last COMPLETED execution
 *   (DoctrineJobRepository::getLastJobExecution, ordered by startTime DESC) and turns the filter into a strict
 *   `updated > start_time`. With no completed execution yet the filter is a no-op, which is why run 1 exports
 *   both products.
 * - The UI edit bumps `product.updated` through the version's loggedAt (VersioningBundle
 *   TimestampableSubscriber.php:56). A save of an unchanged product is skipped entirely
 *   (ProductSaver.php:39 isDirty), hence the "There are unsaved changes." precondition before saving.
 * - The ES `updated` field is GREATEST(product.updated, sub/root product model updated)
 *   (GetElasticsearchProductProjection.php:158, ElasticsearchProductProjection.php:97). For the simple
 *   products used here that is product.updated; a variant-product port (export_variant_products_by_specific_date
 *   .feature) would have to account for the ancestors' dates.
 *
 * Adaptations:
 * - Catalog "footwear" -> icecat_demo_dev (the catalog Playwright CI runs against). The SNKRS-1B / SNKRS-1R
 *   sneakers become two disposable "tshirts" products (icecat families.csv) created through the internal API
 *   with a Date.now() suffix, created sequentially. The internal create endpoint cannot set a client-chosen
 *   uuid, so the uuid column is compared with the meta.id each product actually got.
 * - Shared job csv_footwear_product_export -> a disposable job instance of job name csv_product_export
 *   (POST /job-instance/rest/export, then PUT its configuration, see createProductExportJobViaApi and
 *   configureProductExportJobViaApi in pim.ts). The filter keys on the job's OWN last completed execution,
 *   so a shared job would inherit other specs' runs. Its "SINCE LAST JOB" value is its own code, which is what
 *   the UI stores (filter/product/updated.js:119-120) and what Behat line 11 sets. A `sku IN [both skus]` data
 *   filter is added so the rest of the shared catalog stays out of both runs; the job defaults' completeness
 *   >= 100 filter is not sent (a sent `filters` replaces the defaults wholesale, JobParametersFactory.php:37).
 * - storage {"type": "local", ...} is dropped: local storage needs the import_export_local_storage feature
 *   flag, which only the Behat JobContext enables. The CSV is read from the job ARCHIVE
 *   (meta.archives.output.files + pim_enrich_job_tracker_download_file, readExportedCsv in pim.ts), which is
 *   also what Behat's ExportProfilesContext reads.
 * - Scope "mobile" -> "ecommerce" (icecat channels.csv); locales ["en_US"] kept; with_uuid kept (Behat line 12);
 *   with_media false.
 * - UI edit of the multi-select "Weather conditions" -> UI edit of the text attribute `name` (icecat
 *   pim_catalog_text, not localizable, not scopable). The only multi-select of the tshirts family,
 *   tshirt_style, is a Select2 v3 widget whose native <select> is hidden, and the delta filter reads only the
 *   product `updated` date, which does not depend on the attribute type.
 * - The byte-for-byte expected CSVs -> assertions by column name (uuid, sku, family, name) plus exact row
 *   counts (2, then 1) and the untouched sku absent from run 2: the icecat header differs from the footwear one.
 * - "Julia" -> julia/julia (icecat users.csv, ROLE_CATALOG_MANAGER).
 *
 * Timing:
 * - A 2s wait separates run 1 from the edit. job_execution.start_time (JobExecution.php:53) and
 *   product.updated are second-precision DATETIME columns and the comparison is a strict `>`, so an edit in the
 *   same wall-clock second as run 1's start would be excluded from run 2.
 *   ExportProductsBySpecificDateIntegration.php sleeps 2s for the same reason.
 * - Run 1 must be COMPLETED: getLastJobExecution filters on that status, so a failed run 1 would silently turn
 *   run 2's filter into a no-op. Run 2 is never relaunched on a mismatch: a relaunch would become the new
 *   "last job" and hide the bug.
 * - Elasticsearch lag: saves are indexed without refresh (ProductIndexer.php:49 `Refresh::disable()`) while
 *   the export reader queries ES. Products are created before the job setup, and the edit is followed by the
 *   navigation to the export page, so several round trips pass before each launch. A missed product fails the
 *   exact `written` / row-count assertions with the data in the message.
 *
 * Selectors traced from:
 * - "Export now", export job page and launch: launchExportFromJobPage in pim.ts (templates/export/common/edit/
 *   launch.html, job/common/edit/launch.js).
 * - Job status: JobExecutionDetail.tsx:263 data-testid="job-status"; the badge label is
 *   akeneo_job.job_status.COMPLETED = "Completed". The page polls the execution every second while it runs
 *   (useJobExecution.ts). waitForJobCompletion is not used because it also accepts "failed".
 * - Product edit form: route #/enrich/product/{uuid} (NavigationHelper.ts). Each attribute field is a
 *   `div[data-attribute="<code>"]` (product/field/field.js:18-21) whose template puts `.original-field` directly
 *   under it (templates/product/field/field.html); text.html renders `input[type="text"]` in `.field-input`.
 *   The child combinator follows compare-and-copy-localized-fields.spec.ts. The attribute group selector
 *   defaults to "All" (form/common/group-selector.js ensureDefault), so `name` is rendered without switching
 *   group. text-field.js:8 updates the model on `change`, hence Tab after fill.
 * - "There are unsaved changes.": form/common/state.js (pim_common.entity_updated), shown while the model
 *   differs from the fetched data; saveProduct in pim.ts waits for it to hide after a successful save.
 *
 * Cleanup (finally): products deleted with DELETE /enrich/product/rest/{uuid} sent as an XHR
 * (ProductController::removeAction redirects non-XHR requests without deleting), and the disposable job
 * instance deleted with deleteExportJobViaApi. That delete also protects edit-export.spec.ts, which picks the
 * first export grid row matching /csv.*product/i.
 */

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};

type DeltaProduct = {sku: string; name: string; uuid?: string};

test.describe('Export products according to a date', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'julia', 'julia');
  });

  test('Export only the products updated by the UI since the last export', async ({page}) => {
    const ts = Date.now();
    const jobCode = `pw_delta_export_${ts}`;
    const products: DeltaProduct[] = [
      {sku: `pw-delta-a-${ts}`, name: `PW delta A ${ts}`},
      {sku: `pw-delta-b-${ts}`, name: `PW delta B ${ts}`},
    ];
    const [edited, untouched] = products;
    const editedName = `PW delta A edited ${ts}`;
    let jobCreated = false;

    const column = (header: string[], row: string[], name: string): string | undefined => {
      const index = header.indexOf(name);
      return index === -1 ? undefined : row[index];
    };
    const jobStatus = page.locator('[data-testid="job-status"]');

    try {
      // Given the following products (sequentially, before any job setup, to give Elasticsearch time).
      for (const product of products) {
        const createResp = await createProductViaApi(page, product.sku, 'tshirts');
        const created = await createResp.json().catch(() => null);
        expect(
          createResp.ok(),
          `Create product ${product.sku} failed: ${createResp.status()} ${JSON.stringify(created)}`
        ).toBeTruthy();
        const uuid: string | undefined = created?.meta?.id;
        expect(uuid, `Create product ${product.sku} returned no meta.id: ${JSON.stringify(created)}`).toBeTruthy();
        product.uuid = uuid;

        await updateProductViaApi(page, uuid!, {
          values: {
            sku: [{locale: null, scope: null, data: product.sku}],
            name: [{locale: null, scope: null, data: product.name}],
          },
        });
      }

      // And the following job configuration (disposable job, see header).
      jobCreated = true;
      await createProductExportJobViaApi(page, jobCode, `PW delta export ${ts}`);
      await configureProductExportJobViaApi(page, jobCode, {
        with_uuid: true,
        with_media: false,
        filters: {
          data: [
            {field: 'updated', operator: 'SINCE LAST JOB', value: jobCode},
            {field: 'sku', operator: 'IN', value: products.map(product => product.sku)},
          ],
          structure: {scope: 'ecommerce', locales: ['en_US']},
        },
      });

      // When I am on the export job page, And I launch the export job
      const jobId1 = await launchExportFromJobPage(page, jobCode);

      // And I wait for the job to finish
      const run1 = await waitForJobExecutionViaApi(page, jobId1);
      expect(run1.status, `Run 1 did not complete: ${JSON.stringify(run1)}`).toBe('COMPLETED');
      const run1Step = run1.stepExecutions?.find((step: any) => step.summary?.written > 0);
      expect(run1Step, `Run 1: no step wrote any items: ${JSON.stringify(run1.stepExecutions)}`).toBeTruthy();
      expect(run1Step.summary.written, `Run 1 step summaries: ${JSON.stringify(run1.stepExecutions)}`).toBe(2);
      await expect(jobStatus, `Run 1 (job ${jobId1}) status badge`).toContainText(/completed/i, {timeout: 30_000});

      // Then exported file should contain both products
      const csv1 = await readExportedCsv(page, jobId1);
      expect(csv1.header, `Run 1 CSV:\n${csv1.text}`).toEqual(
        expect.arrayContaining(['uuid', 'sku', 'family', 'name'])
      );
      expect(csv1.rows, `Run 1 CSV:\n${csv1.text}`).toHaveLength(2);
      for (const product of products) {
        const rows = csv1.rows.filter(row => column(csv1.header, row, 'sku') === product.sku);
        expect(rows, `Run 1: expected exactly one row for ${product.sku}:\n${csv1.text}`).toHaveLength(1);
        expect(column(csv1.header, rows[0], 'uuid'), `Run 1 CSV:\n${csv1.text}`).toBe(product.uuid);
        expect(column(csv1.header, rows[0], 'family'), `Run 1 CSV:\n${csv1.text}`).toBe('tshirts');
        expect(column(csv1.header, rows[0], 'name'), `Run 1 CSV:\n${csv1.text}`).toBe(product.name);
      }

      // Second-precision boundary with a strict `>` (see header): the edit must land in a later second than
      // run 1's start_time.
      await page.waitForTimeout(2_000);

      // When I edit the product
      await page.evaluate(uuid => {
        window.location.hash = `#/enrich/product/${uuid}`;
      }, edited.uuid!);
      const nameInput = page.locator('[data-attribute="name"] > .original-field .field-input input[type="text"]');
      // The fetched product data is applied to the field before it is edited.
      await expect(nameInput, `${edited.sku}: the "name" field never showed the saved value`).toHaveValue(edited.name, {
        timeout: 60_000,
      });

      // And I change the "name" (Behat: "Weather conditions") and the form registers the change
      await nameInput.fill(editedName, {timeout: 10_000});
      await nameInput.press('Tab', {timeout: 10_000});
      const unsavedChanges = page.getByText('There are unsaved changes.', {exact: true});
      await expect(unsavedChanges, `${edited.sku}: the form did not register the "name" change`).toBeVisible({
        timeout: 10_000,
      });

      // And I save the product
      const saveResponsePromise = page.waitForResponse(
        r => r.url().endsWith(`/enrich/product/rest/${edited.uuid}`) && r.request().method() === 'POST',
        {timeout: 150_000}
      );
      saveResponsePromise.catch(() => {});
      await saveProduct(page);
      const saveResponse = await saveResponsePromise;
      expect(
        saveResponse.ok(),
        `Save of ${edited.sku} failed: ${saveResponse.status()} ${await responseBody(saveResponse)}`
      ).toBeTruthy();

      // And I should not see the text "There are unsaved changes"
      await expect(unsavedChanges).toBeHidden({timeout: 30_000});
      const saved = await getProductViaApi(page, edited.uuid!);
      expect(saved.values?.name?.[0]?.data, `${edited.sku} after the UI save: ${JSON.stringify(saved)}`).toBe(
        editedName
      );

      // And I am on the export job page, And I launch the export job
      const jobId2 = await launchExportFromJobPage(page, jobCode);
      expect(jobId2, 'Run 2 must be a new job execution').not.toBe(jobId1);

      // And I wait for the job to finish
      const run2 = await waitForJobExecutionViaApi(page, jobId2);
      expect(run2.status, `Run 2 did not complete: ${JSON.stringify(run2)}`).toBe('COMPLETED');
      const run2Step = run2.stepExecutions?.find((step: any) => step.summary?.written > 0);
      expect(
        run2Step,
        `Run 2 wrote nothing: the edited product was not seen as updated since run 1 (job ${jobId1}). ` +
          `Step summaries: ${JSON.stringify(run2.stepExecutions)}`
      ).toBeTruthy();
      expect(run2Step.summary.written, `Run 2 step summaries: ${JSON.stringify(run2.stepExecutions)}`).toBe(1);
      await expect(jobStatus, `Run 2 (job ${jobId2}) status badge`).toContainText(/completed/i, {timeout: 30_000});

      // Then exported file should contain only the edited product
      const csv2 = await readExportedCsv(page, jobId2);
      expect(csv2.rows, `Run 2 CSV:\n${csv2.text}`).toHaveLength(1);
      const [row] = csv2.rows;
      expect(column(csv2.header, row, 'sku'), `Run 2 CSV:\n${csv2.text}`).toBe(edited.sku);
      expect(column(csv2.header, row, 'uuid'), `Run 2 CSV:\n${csv2.text}`).toBe(edited.uuid);
      expect(column(csv2.header, row, 'family'), `Run 2 CSV:\n${csv2.text}`).toBe('tshirts');
      expect(column(csv2.header, row, 'name'), `Run 2 CSV:\n${csv2.text}`).toBe(editedName);
      expect(csv2.text, `Run 2 must not export the untouched ${untouched.sku}`).not.toContain(untouched.sku);
    } finally {
      for (const product of products) {
        if (product.uuid) {
          await page.request.delete(`/enrich/product/rest/${product.uuid}`, {headers: XHR_HEADER}).catch(() => null);
        }
      }
      if (jobCreated) {
        await deleteExportJobViaApi(page, jobCode);
      }
    }
  });
});
