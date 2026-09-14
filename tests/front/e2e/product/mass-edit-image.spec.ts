import type {Page} from '@playwright/test';
import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  goToProductsGrid,
  waitForLoadingMasks,
  waitForJobExecutionViaApi,
  ensureProductExists,
  selectProductsBySku,
  openBulkEditAttributeValues,
  addAttributeToMassEdit,
  attachFileToMassEditAttribute,
  confirmMassEdit,
  productHasAttributeValue,
  createAttributeViaApi,
  addAttributeToFamilyViaApi,
  removeAttributeFromFamilyViaApi,
  deleteAttributeViaApi,
  getFirstProductsFromGrid,
  getProductFamilyCode,
} from '../fixtures/pim';

/**
 * Replaces Behat (both features have since been deleted from master; line numbers refer to the
 * parent of the deleting commit):
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/edit-common-attribute/edit_common_attributes_images.feature:32
 *     deleted by #212 (6afd6d6ed8)
 *   - tests/legacy/features/pim/enrichment/product/mass-edit/validate/validate_editing_common_image_attributes.feature:43
 *     deleted by #216 (a55efeb9ce)
 *
 * Adaptations:
 *   - Uses Playwright setInputFiles() which handles hidden file inputs natively,
 *     unlike Selenium W3C which fails to locate non-visible elements.
 *   - Uses existing indexed catalog products to avoid Elasticsearch indexing lag in CI.
 *   - attachFileToMassEditAttribute waits until the upload is answered and the file is rendered. The
 *     wizard has no "fields not ready" guard, so clicking Next during the upload validates, and can
 *     launch, the value from before the upload. Seen in CI, both passing on retry: run 34831928439 (the
 *     first test's job completed without the image) and run 34846941289 (step 3 reached the Confirm
 *     step, so the extension error never showed).
 *   - Step 3 checks the /rest/value/validate response for the exact extension message on the
 *     attribute, then that message in the field footer, then that the wizard stayed on the configure
 *     step, instead of a page-wide regex. Behat checked the same message as a validation tooltip.
 *   - The last test is a regression guard for that race (delayed /image-media); it has no Behat
 *     counterpart.
 */

/**
 * Click the wizard's Next action (configure -> confirm) and assert that the .gif value was rejected.
 *
 * mass-edit/form/form.js runs edit-common-attributes.js validate(), which POSTs the operation's
 * current values to /rest/value/validate. When the response holds no violations, the wizard moves
 * to the Confirm step. When it holds violations, the wizard stays on the configure step and
 * product/form/attributes/validation.js renders each one in the field footer
 * (validation-error.html: .AknFieldContainer-validationError .error-message). The response is
 * checked first, so a validate call that carried no file (e.g. an upload that had not finished yet)
 * fails with the server's answer instead of a bare "element(s) not found" on the Confirm step.
 */
async function confirmExpectingGifRejected(page: Page, attributeCode: string, attributeLabel: string) {
  const expectedMessage = `The gif file extension is not allowed for the ${attributeCode} attribute. Allowed extensions are png, jpeg, jpg.`;
  const confirmAction = page.locator('.wizard-action[data-action-target="confirm"]');

  // Listen before clicking: the click handler sends the validate request synchronously.
  const validated = page.waitForResponse(
    resp => resp.request().method() === 'POST' && new URL(resp.url()).pathname === '/rest/value/validate',
    {timeout: 30_000}
  );
  validated.catch(() => {});
  await confirmAction.click({timeout: 30_000});
  const response = await validated;
  const body = await response.json().catch(() => null);
  expect(response.ok(), `POST /rest/value/validate failed: ${response.status()} ${JSON.stringify(body)}`).toBe(true);
  const violations: unknown[] = Array.isArray(body?.values) ? body.values : [];
  expect(
    violations.length,
    `validate returned no violations (wizard advanced to Confirm): ${JSON.stringify(body)}`
  ).toBeGreaterThan(0);
  expect(violations, `validate did not reject the gif for ${attributeCode}: ${JSON.stringify(body)}`).toContainEqual(
    expect.objectContaining({attribute: attributeCode, message: expectedMessage})
  );

  await waitForLoadingMasks(page);
  const field = page
    .locator('.AknComparableFields')
    .filter({has: page.locator('.AknFieldContainer-label', {hasText: attributeLabel})})
    .first();
  const error = field.locator('.AknFieldContainer-validationError .error-message').first();
  await expect(error, `"${attributeLabel}" shows no extension error`).toHaveText(expectedMessage, {timeout: 15_000});
  await expect(error).toBeVisible({timeout: 5_000});

  // Still on the configure step: the Confirm step replaces Next with the "validate" (launch) action.
  await expect(confirmAction, 'the wizard left the configure step').toBeVisible({timeout: 5_000});
  await expect(page.locator('.wizard-action[data-action-target="validate"]')).toHaveCount(0);
}

test.describe('Mass edit image attributes', () => {
  const ts = Date.now();
  const ATTR_CODE = `pw_side_view_${ts}`;
  const ATTR_LABEL = 'Pw Side View';

  let sku1: string | null = null;
  let sku2: string | null = null;
  let uuid1: string | null = null;
  let uuid2: string | null = null;
  let families: string[] = [];

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');

    const products = await getFirstProductsFromGrid(page, 2);
    expect(
      products.length,
      'Need at least 2 indexed products in the catalog — icecat_demo_dev must be loaded'
    ).toBeGreaterThanOrEqual(2);

    sku1 = products[0].sku;
    sku2 = products[1].sku;
    uuid1 = products[0].uuid;
    uuid2 = products[1].uuid;

    // Resolve the family CODE from the product API (the grid's `.family` is the localized
    // LABEL, which 404s against the code-keyed family endpoint — and which family lands
    // "first" in the ES-ordered grid varies across shards). Deduplicate so we PUT each once.
    const familyCodes = await Promise.all([getProductFamilyCode(page, uuid1!), getProductFamilyCode(page, uuid2!)]);
    families = [...new Set(familyCodes.filter((code): code is string => Boolean(code)))];
    expect(families.length, 'Products must belong to at least one family').toBeGreaterThan(0);

    const r1 = await createAttributeViaApi(page, {
      code: ATTR_CODE,
      type: 'pim_catalog_image',
      group: 'other',
      allowed_extensions: ['png', 'jpeg', 'jpg'],
      scopable: false,
      localizable: false,
      labels: {en_US: ATTR_LABEL},
    });
    expect(r1.ok(), `Failed to create attribute ${ATTR_CODE}: ${r1.status()}`).toBe(true);

    for (const familyCode of families) {
      await addAttributeToFamilyViaApi(page, familyCode, ATTR_CODE);
    }

    await page.close();
  });

  test.afterAll(async ({browser}) => {
    const page = await browser.newPage();
    await login(page, 'admin', 'admin');
    for (const familyCode of families) {
      await removeAttributeFromFamilyViaApi(page, familyCode, ATTR_CODE);
    }
    await deleteAttributeViaApi(page, ATTR_CODE);
    await page.close();
  });

  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    await ensureProductExists(page);
  });

  /**
   * Replaces Behat: edit_common_attributes_images.feature:32 (deleted by #212)
   * Successfully update many images values at once
   */
  test('Successfully update many images values at once', async ({page}) => {
    // 720s covers beforeEach + UI flow + pollForNewMassEditJob (≤180s) + waitForJobExecutionViaApi (≤420s).
    // Set here: a `timeout` key in the test details object is not a TestDetails option and was ignored.
    test.setTimeout(720_000);
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1!, sku2!]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'SNKRS-1R.png');

    const jobId = await confirmMassEdit(page);
    expect(
      jobId,
      'Mass-edit job not registered in process-tracker within 60s — consumer may be dead or queue backlog too large'
    ).toBeTruthy();
    const result = await waitForJobExecutionViaApi(page, jobId!, 420_000);
    expect(['COMPLETED', 'completed']).toContain(result.status?.toUpperCase?.() ?? result.status);

    expect(await productHasAttributeValue(page, uuid1!, ATTR_CODE)).toBe(true);
    expect(await productHasAttributeValue(page, uuid2!, ATTR_CODE)).toBe(true);
  });

  /**
   * Replaces Behat: validate_editing_common_image_attributes.feature:43 (deleted by #216)
   * Mass edit image attribute — set, clear, and validate extension
   */
  test('Mass edit image attribute — set, clear, and validate extension', async ({page}) => {
    // 1380s covers 3 wizard flows × (≤180s poll + ≤360s job) on slow CI runners (set here for the same
    // reason as in the test above).
    test.setTimeout(1_380_000);
    await goToProductsGrid(page);

    // Step 1: set image on sku1 + sku2
    await selectProductsBySku(page, [sku1!, sku2!]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'SNKRS-1R.png');
    const jobId1 = await confirmMassEdit(page);
    expect(jobId1, 'Step 1: mass-edit job not registered in process-tracker within 60s').toBeTruthy();
    await waitForJobExecutionViaApi(page, jobId1!, 360_000);
    expect(await productHasAttributeValue(page, uuid1!, ATTR_CODE)).toBe(true);
    expect(await productHasAttributeValue(page, uuid2!, ATTR_CODE)).toBe(true);

    // Step 2: clear image by confirming without attaching a file
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1!, sku2!]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    const jobId2 = await confirmMassEdit(page);
    expect(jobId2, 'Step 2: mass-edit job not registered in process-tracker within 120s').toBeTruthy();
    await waitForJobExecutionViaApi(page, jobId2!, 360_000);
    expect(await productHasAttributeValue(page, uuid1!, ATTR_CODE)).toBe(false);
    expect(await productHasAttributeValue(page, uuid2!, ATTR_CODE)).toBe(false);

    // Step 3: attach invalid extension (.gif) and verify validation error
    await goToProductsGrid(page);
    await selectProductsBySku(page, [sku1!, sku2!]);
    await openBulkEditAttributeValues(page);
    await addAttributeToMassEdit(page, ATTR_LABEL);
    await attachFileToMassEditAttribute(page, ATTR_LABEL, 'bic-core-148.gif');
    await confirmExpectingGifRejected(page, ATTR_CODE, ATTR_LABEL);

    // Step 3 never reaches the launch action (form.js only POSTs the job on "validate"), so these can only
    // fail if step 2's clear did not hold; the wizard-step check above is what covers step 3.
    expect(await productHasAttributeValue(page, uuid1!, ATTR_CODE)).toBe(false);
    expect(await productHasAttributeValue(page, uuid2!, ATTR_CODE)).toBe(false);
  });

  test('Rejects an invalid image extension even when the upload is slow', async ({page}) => {
    // Regression guard for the confirm-during-upload race. Selecting a file only STARTS an async
    // upload: media-field.js POSTs it to /image-media and writes the uploaded file into the value
    // only in the ajax done() callback. Unlike the product edit form, the mass-edit wizard has no
    // "fields not ready" guard: clicking Next while the upload is in flight validates the value as it
    // was before the upload (still empty, which is valid), and the wizard moves on to the Confirm
    // step, so the extension error never appears. Delaying /image-media makes that window
    // deterministic: a helper that does not wait for the upload clicks Next inside it.
    // Seen in CI: run 34846941289 (step 3 of the test above reached the Confirm step, retry passed
    // with only 17-35ms between the upload response and the validate request).
    // The gif is rejected and no job is launched, so this test changes no product data.
    // Above the helpers' own bounds, so a failure reports their message rather than the test timeout.
    test.setTimeout(300_000);

    try {
      await page.route('**/image-media', async route => {
        await new Promise(resolve => setTimeout(resolve, 2_000));
        await route.continue();
      });

      await goToProductsGrid(page);
      await selectProductsBySku(page, [sku1!, sku2!]);
      await openBulkEditAttributeValues(page);
      await addAttributeToMassEdit(page, ATTR_LABEL);
      await attachFileToMassEditAttribute(page, ATTR_LABEL, 'bic-core-148.gif');
      await confirmExpectingGifRejected(page, ATTR_CODE, ATTR_LABEL);
    } finally {
      // A delayed handler can still be running when the test ends; its continue() must not fail cleanup.
      await page.unrouteAll({behavior: 'ignoreErrors'}).catch(() => {});
    }
  });
});
