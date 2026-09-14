import * as fs from 'node:fs';
import type {APIResponse} from '@playwright/test';
import {test, expect, Page} from '../fixtures/coverage-fixture';
import {
  login,
  createProductViaApi,
  getProductViaApi,
  createCategoryViaApi,
  waitForJobExecutionViaApi,
  waitForJobCompletion,
  fixtureFilePath,
} from '../fixtures/pim';

/**
 * Replaced Behat scenario (deleted in #420): tests/legacy/features/pim/enrichment/product/export/export_products_and_download_exported_file.feature:7
 *   "Successfully export products and be able to download exported file"
 *
 * Catalog ("Given an "apparel" catalog configuration"): Playwright CI runs against icecat_demo_dev, never
 * the Behat apparel catalog. Every entity is either reused from the icecat_demo_dev installer fixtures
 * (src/Akeneo/Platform/Installer/back/src/Infrastructure/Symfony/Resources/fixtures/icecat_demo_dev/) or
 * created disposably with a Date.now() suffix:
 * - family "tshirts" exists in icecat too (families.csv: clothing_size, description, main_color, name,
 *   picture, price, secondary_color, sku, tshirt_materials, tshirt_style).
 * - Apparel attributes are mapped onto their icecat equivalents (attributes.csv / attribute_options.csv):
 *   color -> main_color (options white/black), material -> tshirt_materials (option cotton),
 *   size -> clothing_size (size_M/size_L -> options m/l), thumbnail -> picture (pim_catalog_image).
 *   name is NOT localizable in icecat (localizable 0), so the 4 per-locale name values become one "name".
 *   description is localizable + scopable, exactly like apparel.
 * - Categories men_2013/men_2014/men_2015 -> a disposable parent under the icecat root tree "master"
 *   (categories.csv) with two disposable children. Never a new ROOT tree (a fresh root is not reliably
 *   granted to the user, see classify-product.spec.ts for the same sub-category pattern).
 * - The Behat fixture images SNKRS-1C-s.png / SNKRS-1C-t.png are uploaded through POST /media/ (route
 *   pim_enrich_media_rest_post, MediaController::postAction, the PEF media field's upload route) and set as
 *   the picture value. Two exported media files + the CSV are what make the job archive "at least 2 files"
 *   (StepExecutionArchivist::hasAtLeastTwoArchives), which is what makes the "Download generated archive"
 *   dropdown item render at all (JobExecutionDetail.tsx, meta.generateZipArchive).
 *
 * Job ("the following job "ecommerce_product_export" configuration"): instead of mutating a shared job
 * instance (csv_product_export is used by export-launch.spec.ts and edit-export.spec.ts), a disposable job
 * instance of job name csv_product_export (connector "Akeneo CSV Connector", icecat jobs.yml) is created
 * via POST /job-instance/rest/export. JobInstanceController::createAction resets the raw parameters to the
 * job defaults, so its configuration is then set with PUT /job-instance/rest/export/{code} (putAction ->
 * JobInstanceUpdater "configuration" -> JobParametersFactory: defaults merged with what is sent):
 * with_uuid true, with_media true, scope ecommerce, locales de_DE/en_US/fr_FR, and the data filters
 * enabled = true, categories IN CHILDREN [disposable parent], sku IN [the 2 skus] (the sku filter keeps the
 * row count deterministic in a shared catalog).
 * - storage {"type": "local", ...} is dropped: local storage needs the import_export_local_storage feature
 *   flag, which only the Behat JobContext enables server-side. The default storage type "none" is kept and
 *   the CSV is read back from the job ARCHIVE through the download route, which is also what Behat's
 *   ExportProfilesContext reads (getExportedArchivedFile).
 * - The apparel job profile's completeness >= 100 filter is dropped: it is fixture configuration, not the
 *   subject of this scenario, and would require filling every tshirts requirement for ecommerce.
 *
 * The mutating internal controllers used here (MediaController::postAction, UpdateProductController,
 * ProductController::removeAction, JobInstanceController create/put/launch/delete) return
 * `new RedirectResponse('/')` when the request is not an XHR, and APIRequestContext follows that redirect
 * to a 200. So those calls send X-Requested-With and assert the response BODY shape (job code, product
 * meta.id, media filePath), otherwise a missing header would pass resp.ok() while doing nothing. The
 * read-only job-execution GET and the archive download route have no such guard.
 *
 * Elasticsearch lag: product saves are indexed without refresh (OnSave ComputeProductsAndAncestorsSubscriber
 * -> ProductAndAncestorsIndexer.php:38 -> ProductIndexer.php:49 `Refresh::disable()`), while the export
 * reader queries through the PQB/ES (ProductReader::getProductsCursor). Products are therefore created and
 * updated BEFORE the job instance setup and the UI navigation, so several round trips pass before launch.
 * If a product is still missing, `written === 2` and the 2-row CSV assertion fail loudly with the data.
 *
 * "I am logged in as "Julia"": icecat users.csv has julia/julia (ROLE_CATALOG_MANAGER). Fixture roles get
 * full access (AddDefaultPrivilegesSubscriber::loadDefaultPrivilegesForRole) except ACLs flagged
 * enabled_at_creation: false (e.g. pim_enrich_job_tracker_view_all_jobs); none of those is needed here:
 * julia launches the job herself, and the job execution endpoint checks the export execution ACL.
 *
 * Selectors traced from:
 * - Export job page: Behat Page/Export/Show.php path '#/spread/export/{code}' (ImportExportBundle routing
 *   prefix /spread/export). Navigation by hash assignment, same pattern as goToJobExecution in pim.ts.
 * - "Export now": form_extensions/job_instance/csv_product_export_show.yml (pim/job/common/edit/launch,
 *   label pim_import_export.form.job_instance.button.export.title = "Export now") renders
 *   templates/export/common/edit/launch.html `<button class="AknButton AknButton--apply ...">`. Behat's
 *   "I launch the export job" clicks that same button (Show.php '.AknTitleContainer-meta .AknButton--apply'),
 *   so the UI click is kept rather than launchExportViaApi. launch.js POSTs
 *   /job-instance/rest/export/{code}/launch and redirects to response.redirectUrl (#/job/show/{id}).
 * - Job status: JobExecutionDetail.tsx data-testid="job-status" (waitForJobCompletion in pim.ts).
 * - "Download generated files": JobExecutionDetail.tsx renders a DSM <Button data-toggle="dropdown"> with
 *   pim_enrich.entity.job_execution.module.download.dropdown_title (UIBundle jsmessages.en_US.yml) when there
 *   is more than one download link OR generateZipArchive is true. Its overlay (portal) lists one DSM <Link>
 *   per archive link, labelled translate(link.label) where the server sends the archiver label key
 *   pim_enrich.entity.job_execution.module.download.output ("Download generated file"), plus, only when
 *   generateZipArchive, a <Link> to pim_enrich_job_tracker_download_zip_archive labelled
 *   pim_import_export.form.job_execution.button.download_archive.title ("Download generated archive").
 *   Behat checks it with Base.php getDropdownButton('*[data-toggle="dropdown"]:contains(...)').
 *
 * "exported file of ... should contain": Behat compareFile (ImportExportContext.php) requires the same row
 * count, the same header set and each expected line matched by exactly one actual line. Here the archived
 * CSV (meta.archives.output.files, route pim_enrich_job_tracker_download_file) is parsed and checked for:
 * header + exactly 2 data rows, no duplicate header, the expected columns present, no "-print" column, and
 * every expected value per row by column name. Adaptations:
 * - uuid column: the internal create endpoint cannot set a client-chosen uuid, so the column is compared
 *   with the uuid the product actually got (meta.id) instead of daa86948-... / 095b3808-....
 * - en_GB values/columns and price-GBP are dropped: en_GB is not a locale and GBP not a currency of the
 *   icecat ecommerce channel (channels.csv), so those columns cannot exist.
 * - manufacturer and country_of_manufacture are dropped: they are not attributes in icecat.
 * - Exact header set equality is relaxed to "contains the expected columns": the literal Behat header is
 *   the apparel family's attribute list, and the exhaustive icecat header was not proven from source.
 * - print-scope descriptions are set on the products (as in Behat) and asserted absent from the file.
 *
 * Cleanup: the disposable job instance is deleted in `finally` (DELETE /job-instance/rest/export/{code}).
 * That delete is the only protection for edit-export.spec.ts, which picks the first export grid row whose
 * text matches /csv.*product/i (a row text includes the job name column, so the label does not matter).
 * Products are deleted best-effort with DELETE /enrich/product/rest/{uuid} sent as an XHR (the pim.ts
 * deleteProductViaApi helper sends no X-Requested-With, and ProductController::removeAction redirects
 * non-XHR requests without deleting). The disposable categories stay under "master", as in
 * classify-product.spec.ts.
 */

const XHR_HEADER = {'X-Requested-With': 'XMLHttpRequest'};
const JSON_XHR_HEADERS = {'Content-Type': 'application/json', ...XHR_HEADER};

type MediaFile = {filePath: string; originalFilename: string};

type ExportedTShirt = {
  sku: string;
  image: string;
  color: string;
  size: string;
  name: string;
  ecommerceDescriptions: {en_US: string; fr_FR: string; de_DE: string};
  printDescriptions: {en_US: string; de_DE: string};
  uuid?: string;
};

async function responseBody(resp: APIResponse): Promise<string> {
  return resp.text().catch(() => '<no body>');
}

async function createCategoryOrFail(page: Page, code: string, parent: string, label: string): Promise<void> {
  const resp = await createCategoryViaApi(page, code, parent, label);
  expect(resp.ok(), `Create category ${code} failed: ${resp.status()} ${await responseBody(resp)}`).toBeTruthy();
}

/**
 * POST /media/ (pim_enrich_media_rest_post, trailing slash in the route dump), multipart field "file".
 * MediaController::postAction returns {originalFilename, filePath}.
 */
async function uploadMediaViaApi(page: Page, fileName: string): Promise<MediaFile> {
  const resp = await page.request.post('/media/', {
    headers: XHR_HEADER,
    multipart: {
      file: {name: fileName, mimeType: 'image/png', buffer: fs.readFileSync(fixtureFilePath(fileName))},
    },
  });
  const body = await resp.json().catch(() => null);
  expect(resp.ok(), `Upload media ${fileName} failed: ${resp.status()} ${JSON.stringify(body)}`).toBeTruthy();
  expect(body?.filePath, `Upload media ${fileName} returned no filePath: ${JSON.stringify(body)}`).toBeTruthy();

  return body as MediaFile;
}

/**
 * POST /enrich/product/rest/{uuid} (pim_enrich_product_rest_post, UpdateProductController). The payload is
 * the internal format: `values` is mandatory, media values are {filePath, originalFilename}
 * (InternalApiToStandard\ValueConverter).
 */
async function updateProductViaApi(page: Page, uuid: string, payload: Record<string, unknown>): Promise<void> {
  const resp = await page.request.post(`/enrich/product/rest/${uuid}`, {data: payload, headers: JSON_XHR_HEADERS});
  const body = await resp.json().catch(() => null);
  expect(resp.ok(), `Update product ${uuid} failed: ${resp.status()} ${JSON.stringify(body)}`).toBeTruthy();
  expect(body?.meta?.id, `Update product ${uuid} returned an unexpected body: ${JSON.stringify(body)}`).toBe(uuid);
}

/**
 * POST /job-instance/rest/export (pim_enrich_job_instance_rest_export_create, no trailing slash).
 * JobInstanceUpdater maps alias -> job name; the UI creation modal sends the same keys.
 */
async function createProductExportJobViaApi(page: Page, code: string, label: string): Promise<void> {
  const resp = await page.request.post('/job-instance/rest/export', {
    data: {code, label, alias: 'csv_product_export', connector: 'Akeneo CSV Connector'},
    headers: JSON_XHR_HEADERS,
  });
  const body = await resp.json().catch(() => null);
  expect(resp.ok(), `Create export job ${code} failed: ${resp.status()} ${JSON.stringify(body)}`).toBeTruthy();
  expect(body?.code, `Create export job ${code} returned an unexpected body: ${JSON.stringify(body)}`).toBe(code);
}

/**
 * PUT /job-instance/rest/export/{code} (pim_enrich_job_instance_rest_export_put), then read it back with
 * GET /job-instance/rest/export/{code} to prove the configuration was stored.
 */
async function configureProductExportJobViaApi(
  page: Page,
  code: string,
  configuration: Record<string, unknown>
): Promise<void> {
  const putResp = await page.request.put(`/job-instance/rest/export/${code}`, {
    data: {configuration},
    headers: JSON_XHR_HEADERS,
  });
  const putBody = await putResp.json().catch(() => null);
  expect(
    putResp.ok(),
    `Configure export job ${code} failed: ${putResp.status()} ${JSON.stringify(putBody)}`
  ).toBeTruthy();
  expect(putBody?.code, `Configure export job ${code} returned an unexpected body: ${JSON.stringify(putBody)}`).toBe(
    code
  );

  const getResp = await page.request.get(`/job-instance/rest/export/${code}`, {headers: XHR_HEADER});
  const job = await getResp.json().catch(() => null);
  expect(getResp.ok(), `Get export job ${code} failed: ${getResp.status()} ${JSON.stringify(job)}`).toBeTruthy();
  expect(job?.configuration?.with_uuid, JSON.stringify(job?.configuration)).toBe(true);
  expect(job?.configuration?.with_media, JSON.stringify(job?.configuration)).toBe(true);
  expect(job?.configuration?.filters?.structure?.scope, JSON.stringify(job?.configuration)).toBe('ecommerce');
}

/**
 * GET /job-execution/rest/{id} (pim_enrich_job_execution_rest_get). The InternalApi JobExecutionController
 * adds meta.archives ({archiver: {label, files}}) and meta.generateZipArchive.
 */
async function getJobExecutionViaApi(page: Page, jobId: string): Promise<any> {
  const resp = await page.request.get(`/job-execution/rest/${jobId}`, {headers: XHR_HEADER});
  expect(resp.ok(), `Get job execution ${jobId} failed: ${resp.status()} ${await responseBody(resp)}`).toBeTruthy();

  return resp.json();
}

/**
 * GET /job/{id}/download/{archiver}/{key} (pim_enrich_job_tracker_download_file), the URL the
 * "Download generated file" link points to.
 */
async function downloadArchivedFile(page: Page, jobId: string, archiver: string, key: string): Promise<string> {
  const resp = await page.request.get(`/job/${jobId}/download/${archiver}/${encodeURIComponent(key)}`);
  expect(resp.ok(), `Download ${archiver}/${key} failed: ${resp.status()} ${await responseBody(resp)}`).toBeTruthy();

  return resp.text();
}

/**
 * Quote-aware CSV parser: enclosed fields, doubled enclosures, CRLF or LF line endings. Blank lines dropped.
 */
function parseCsv(text: string, delimiter = ';', enclosure = '"'): string[][] {
  const rows: string[][] = [];
  let row: string[] = [];
  let field = '';
  let inEnclosure = false;
  const input = text.replace(/^﻿/, '');

  for (let i = 0; i < input.length; i++) {
    const char = input[i];
    if (inEnclosure) {
      if (char === enclosure && input[i + 1] === enclosure) {
        field += enclosure;
        i++;
      } else if (char === enclosure) {
        inEnclosure = false;
      } else {
        field += char;
      }
    } else if (char === enclosure) {
      inEnclosure = true;
    } else if (char === delimiter) {
      row.push(field);
      field = '';
    } else if (char === '\n' || char === '\r') {
      if (char === '\r' && input[i + 1] === '\n') {
        i++;
      }
      row.push(field);
      rows.push(row);
      row = [];
      field = '';
    } else {
      field += char;
    }
  }
  if (field !== '' || row.length > 0) {
    row.push(field);
    rows.push(row);
  }

  return rows.filter(r => !(r.length === 1 && r[0] === ''));
}

test.describe('Export and download exported products file', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'julia', 'julia');
  });

  test('Successfully export products and be able to download exported file', async ({page}) => {
    const ts = Date.now();
    const parentCategoryCode = `pw_export_${ts}`;
    const categoryA = `${parentCategoryCode}_a`;
    const categoryB = `${parentCategoryCode}_b`;
    const jobCode = `pw_product_export_${ts}`;
    const jobLabel = `PW product export ${ts}`;

    const tshirts: ExportedTShirt[] = [
      {
        sku: `pw-export-white-${ts}`,
        image: 'SNKRS-1C-s.png',
        color: 'white',
        size: 'm',
        name: 'White t-shirt',
        ecommerceDescriptions: {
          en_US: 'A stylish white t-shirt',
          fr_FR: 'Un T-shirt blanc élégant',
          de_DE: 'Ein elegantes weißes T-Shirt',
        },
        printDescriptions: {en_US: 'A really stylish white t-shirt', de_DE: 'Ein sehr elegantes weißes T-Shirt'},
      },
      {
        sku: `pw-export-black-${ts}`,
        image: 'SNKRS-1C-t.png',
        color: 'black',
        size: 'l',
        name: 'Black t-shirt',
        ecommerceDescriptions: {
          en_US: 'A stylish black t-shirt',
          fr_FR: 'Un T-shirt noir élégant',
          de_DE: 'Ein elegantes schwarzes T-Shirt',
        },
        printDescriptions: {en_US: 'A really stylish black t-shirt', de_DE: 'Ein sehr elegantes schwarzes T-Shirt'},
      },
    ];

    let jobCreated = false;

    try {
      // Categories: parent under the existing root "master" first, then its children (sequentially).
      await createCategoryOrFail(page, parentCategoryCode, 'master', `PW export ${ts}`);
      await createCategoryOrFail(page, categoryA, parentCategoryCode, `PW export ${ts} A`);
      await createCategoryOrFail(page, categoryB, parentCategoryCode, `PW export ${ts} B`);

      // Products FIRST (before any job setup), to give Elasticsearch time to refresh before the launch.
      for (const tshirt of tshirts) {
        const media = await uploadMediaViaApi(page, tshirt.image);

        const createResp = await createProductViaApi(page, tshirt.sku, 'tshirts');
        const created = await createResp.json().catch(() => null);
        expect(
          createResp.ok(),
          `Create product ${tshirt.sku} failed: ${createResp.status()} ${JSON.stringify(created)}`
        ).toBeTruthy();
        const uuid: string | undefined = created?.meta?.id;
        expect(uuid, `Create product ${tshirt.sku} returned no meta.id: ${JSON.stringify(created)}`).toBeTruthy();
        tshirt.uuid = uuid;

        await updateProductViaApi(page, uuid!, {
          categories: [categoryA, categoryB],
          values: {
            sku: [{locale: null, scope: null, data: tshirt.sku}],
            name: [{locale: null, scope: null, data: tshirt.name}],
            description: [
              {locale: 'en_US', scope: 'ecommerce', data: tshirt.ecommerceDescriptions.en_US},
              {locale: 'fr_FR', scope: 'ecommerce', data: tshirt.ecommerceDescriptions.fr_FR},
              {locale: 'de_DE', scope: 'ecommerce', data: tshirt.ecommerceDescriptions.de_DE},
              {locale: 'en_US', scope: 'print', data: tshirt.printDescriptions.en_US},
              {locale: 'de_DE', scope: 'print', data: tshirt.printDescriptions.de_DE},
            ],
            price: [
              {
                locale: null,
                scope: null,
                data: [
                  {amount: '10.00', currency: 'EUR'},
                  {amount: '15.00', currency: 'USD'},
                ],
              },
            ],
            clothing_size: [{locale: null, scope: null, data: tshirt.size}],
            main_color: [{locale: null, scope: null, data: tshirt.color}],
            tshirt_materials: [{locale: null, scope: null, data: 'cotton'}],
            picture: [
              {locale: null, scope: null, data: {filePath: media.filePath, originalFilename: media.originalFilename}},
            ],
          },
        });

        const product = await getProductViaApi(page, uuid!);
        expect(product.family, `Product ${tshirt.sku}: ${JSON.stringify(product)}`).toBe('tshirts');
        expect(
          product.categories ?? [],
          `Product ${tshirt.sku} categories: ${JSON.stringify(product.categories)}`
        ).toEqual(expect.arrayContaining([categoryA, categoryB]));
      }

      // Job configuration (disposable job instance, see header).
      await createProductExportJobViaApi(page, jobCode, jobLabel);
      jobCreated = true;
      await configureProductExportJobViaApi(page, jobCode, {
        with_media: true,
        with_uuid: true,
        filters: {
          data: [
            {field: 'enabled', operator: '=', value: true},
            {field: 'categories', operator: 'IN CHILDREN', value: [parentCategoryCode]},
            {field: 'sku', operator: 'IN', value: tshirts.map(t => t.sku)},
          ],
          structure: {scope: 'ecommerce', locales: ['de_DE', 'en_US', 'fr_FR']},
        },
      });

      // When I am on the export job page
      await page.evaluate(code => {
        window.location.hash = `#/spread/export/${code}`;
      }, jobCode);
      const exportNow = page.getByRole('button', {name: 'Export now', exact: true});
      await expect(exportNow).toBeVisible({timeout: 30_000});

      // And I launch the export job
      const launchResponsePromise = page.waitForResponse(
        r => r.url().endsWith(`/job-instance/rest/export/${jobCode}/launch`) && r.request().method() === 'POST',
        {timeout: 60_000}
      );
      await exportNow.click();
      const launchResponse = await launchResponsePromise;
      expect(
        launchResponse.ok(),
        `Launch ${jobCode} failed: ${launchResponse.status()} ${await launchResponse.text()}`
      ).toBeTruthy();
      const launchBody = await launchResponse.json();
      const jobId: string | undefined = launchBody?.redirectUrl?.match(/\/job\/show\/(\d+)/)?.[1];
      expect(jobId, `No job execution id in launch response: ${JSON.stringify(launchBody)}`).toBeTruthy();
      await expect(page).toHaveURL(new RegExp(`#/job/show/${jobId}$`), {timeout: 30_000});

      // And I wait for the job to finish
      const execution = await waitForJobExecutionViaApi(page, jobId!);
      expect(execution.status, `Export did not complete: ${JSON.stringify(execution)}`).toBe('COMPLETED');
      const exportStep = execution.stepExecutions?.find((s: any) => s.summary?.written > 0);
      expect(exportStep, `No step wrote any items: ${JSON.stringify(execution.stepExecutions)}`).toBeTruthy();
      expect(exportStep.summary.written, `Step summary: ${JSON.stringify(exportStep.summary)}`).toBe(2);

      // Stay on the page the launch redirected to (re-assigning the same hash fires no hashchange).
      await waitForJobCompletion(page);
      await expect(page.locator('[data-testid="job-status"]')).toContainText(/completed/i);

      // Then exported file should contain ... (read from the job archive)
      const detail = await getJobExecutionViaApi(page, jobId!);
      expect(detail.meta?.generateZipArchive, `Job meta: ${JSON.stringify(detail.meta)}`).toBe(true);
      const outputFiles = detail.meta?.archives?.output?.files ?? {};
      const csvKeys = Object.keys(outputFiles).filter(key => key.endsWith('.csv'));
      expect(csvKeys, `Unexpected output archives: ${JSON.stringify(detail.meta)}`).toHaveLength(1);

      const csvText = await downloadArchivedFile(page, jobId!, 'output', csvKeys[0]);
      const [header, ...dataRows] = parseCsv(csvText);
      expect(header, `CSV body:\n${csvText}`).toBeTruthy();
      expect(dataRows, `CSV body:\n${csvText}`).toHaveLength(2);
      expect(new Set(header).size, `Duplicate CSV headers: ${header.join(';')}`).toBe(header.length);
      expect(header).toEqual(
        expect.arrayContaining([
          'uuid',
          'sku',
          'categories',
          'clothing_size',
          'description-de_DE-ecommerce',
          'description-en_US-ecommerce',
          'description-fr_FR-ecommerce',
          'enabled',
          'family',
          'groups',
          'main_color',
          'name',
          'picture',
          'price-EUR',
          'price-USD',
          'secondary_color',
          'tshirt_materials',
          'tshirt_style',
        ])
      );
      expect(header.filter(column => column.endsWith('-print'))).toEqual([]);

      const col = (row: string[], column: string): string | undefined => row[header.indexOf(column)];
      for (const tshirt of tshirts) {
        const rows = dataRows.filter(row => col(row, 'sku') === tshirt.sku);
        expect(rows, `Expected exactly one CSV row for ${tshirt.sku}:\n${csvText}`).toHaveLength(1);
        const row = rows[0];

        expect(col(row, 'uuid')).toBe(tshirt.uuid);
        expect((col(row, 'categories') ?? '').split(',').sort()).toEqual([categoryA, categoryB].sort());
        expect(col(row, 'enabled')).toBe('1');
        expect(col(row, 'family')).toBe('tshirts');
        expect(col(row, 'main_color')).toBe(tshirt.color);
        expect(col(row, 'clothing_size')).toBe(tshirt.size);
        expect(col(row, 'tshirt_materials')).toBe('cotton');
        expect(col(row, 'name')).toBe(tshirt.name);
        expect(col(row, 'description-en_US-ecommerce')).toBe(tshirt.ecommerceDescriptions.en_US);
        expect(col(row, 'description-fr_FR-ecommerce')).toBe(tshirt.ecommerceDescriptions.fr_FR);
        expect(col(row, 'description-de_DE-ecommerce')).toBe(tshirt.ecommerceDescriptions.de_DE);
        // icecat price has decimals_allowed=1 (attributes.csv), so PriceNormalizer number_formats to 2 decimals
        // and StandardToFlat PriceConverter casts it to string: exactly the Behat "10.00" / "15.00".
        expect(col(row, 'price-EUR')).toBe('10.00');
        expect(col(row, 'price-USD')).toBe('15.00');
        // MediaExporterPathGenerator: files/<identifier>/<attribute code>/ + original filename.
        expect(col(row, 'picture')).toBe(`files/${tshirt.sku}/picture/${tshirt.image}`);

        expect(csvText).not.toContain(tshirt.printDescriptions.en_US);
        expect(csvText).not.toContain(tshirt.printDescriptions.de_DE);
      }

      // Then I should see the text "Download generated files"
      const downloadToggle = page.getByRole('button', {name: 'Download generated files', exact: true});
      await expect(downloadToggle).toBeVisible({timeout: 30_000});

      // Then I should see "Download generated archive" on the "Download generated files" dropdown button
      await downloadToggle.click();
      const archiveLink = page.getByRole('link', {name: 'Download generated archive', exact: true});
      await expect(archiveLink).toBeVisible({timeout: 10_000});
      await expect(archiveLink).toHaveAttribute('href', new RegExp(`/job/${jobId}/download/zip$`));
      // The UI link must point at the very CSV checked above. Without $deep, AbstractFilesystemArchiver::
      // getArchives lists only the top-level CSV (media live under files/), so the output archive yields one
      // link labelled with the archiver label. FOS router.generate encodes the key (encodeURIComponent with a
      // few characters restored), so the key part of the href is decoded before comparing.
      const fileLink = page.getByRole('link', {name: 'Download generated file', exact: true});
      await expect(fileLink).toBeVisible();
      const fileHref = (await fileLink.getAttribute('href')) ?? '';
      const fileHrefPrefix = `/job/${jobId}/download/output/`;
      expect(fileHref, `Unexpected "Download generated file" href`).toContain(fileHrefPrefix);
      expect(decodeURIComponent(fileHref.slice(fileHref.indexOf(fileHrefPrefix) + fileHrefPrefix.length))).toBe(
        csvKeys[0]
      );
    } finally {
      for (const tshirt of tshirts) {
        if (tshirt.uuid) {
          await page.request.delete(`/enrich/product/rest/${tshirt.uuid}`, {headers: XHR_HEADER}).catch(() => null);
        }
      }
      if (jobCreated) {
        const deleteResp = await page.request
          .delete(`/job-instance/rest/export/${jobCode}`, {headers: XHR_HEADER})
          .catch(() => null);
        if (deleteResp && !deleteResp.ok()) {
          console.warn(`Cleanup: delete export job ${jobCode} returned ${deleteResp.status()}`);
        }
      }
    }
  });
});
