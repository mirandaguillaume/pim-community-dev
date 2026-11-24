import type {APIResponse, Page} from '@playwright/test';
import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  attachFileToProductAttribute,
  saveProduct,
  createAttributeViaApi,
  createFamilyViaApi,
  createProductViaApi,
  deleteProductViaApi,
  deleteFamilyViaApi,
  deleteAttributeViaApi,
} from '../fixtures/pim';

/**
 * Replaced Behat scenario (deleted with this spec):
 *   tests/legacy/features/pim/enrichment/product/validation/validate_file_attributes.feature:39
 *   "Validate the allowed extensions constraint of scopable file attribute"
 *
 * Adaptations:
 * - Data: the Behat Background builds, on the "default" catalog, a scopable pim_catalog_file attribute
 *   "attachment" (label Attachment, max_file_size 0.01, allowed_extensions txt, group other), a family "baz"
 *   holding it and a product "foo" of that family. The test builds the same three things through the internal
 *   API, with Date.now() suffixed codes, and deletes them afterwards (product, then family, then attribute).
 *   The non-scopable "datasheet" attribute is not created: only the other scenarios of the feature use it.
 * - The expected message names the attribute CODE (FileValidator::validateFileExtension passes
 *   $constraint->attributeCode as %attribute%), so it reads "... for the pw_attachment_<ts> attribute ..."
 *   instead of "... for the attachment attribute ...". The rest of the text is the Behat one.
 * - "I am logged in as Mary": admin/admin, like every other spec.
 * - "I switch the scope to ecommerce": icecat_demo_dev gives admin the catalog scope ecommerce
 *   (users.csv), so the switch may be a no-op; it is still performed through the UI, and the test then proves
 *   the field edits the ecommerce value (see the selectors below) before any file is picked.
 * - Fixture: fanatic-freewave-76.gif (952 bytes, under the 10 kB limit, so only the extension rule fires) is
 *   the Behat file itself, read from tests/legacy/features/Context/fixtures through fixtureFilePath.
 * - Not in Behat: the save request must answer 400 (its status and body are printed otherwise), and the error
 *   must be the one of the ecommerce value of the Attachment field, not any matching text on the page.
 *
 * Selectors traced from:
 * - Product page readiness: the product label heading. form/common/label.js renders `h1.AknTitleContainer-title`;
 *   it falls back to the identifier when the family has no label value (product-label.js), so the h1 shows
 *   the SKU. getByRole('heading', {level: 1}) matches only the h1, not its same-class drop zone wrapper.
 * - Scope switcher: default-template.html `.AknTitleContainer-context` (the "Main context selector" of
 *   Behat's Product/Edit page), scope-switcher.js className `scope-switcher`. scope-switcher.html renders the
 *   toggle as `<a data-toggle="dropdown">` and each channel as `<a data-scope="<code>">`; neither has an
 *   href, so they carry no link role and are located by those attributes (the same targets as
 *   ContextSwitcherDecorator::switchScope). A click on a channel runs scope-switcher.js::changeScope.
 * - Scope proof: product/field/media.html renders the empty media field's `input[type=file]` with
 *   data-scope="<%- scope %>", the scope of the value the field edits.
 * - Attachment field: product/field/field.html `.AknFieldContainer` holding the `.AknFieldContainer-label`
 *   "Attachment" (the same container attachFileToProductAttribute uses).
 * - Error: attributes/validation.js adds a ValidationError footer to the field whose attribute matches the
 *   violation; tab/attributes/validation-error.html renders each error as `.AknFieldContainer-validationError`
 *   with a `.field-context` showing the error's scope code and a `.error-message` with the text.
 */

const ALLOWED_EXTENSIONS = ['txt'];
const ATTACHMENT_LABEL = 'Attachment';

async function describeResponse(response: APIResponse): Promise<string> {
  return `${response.status()} ${await response.text().catch(() => '')}`;
}

async function expectOk(response: APIResponse, action: string): Promise<void> {
  expect(response.ok(), `${action} failed: ${await describeResponse(response)}`).toBeTruthy();
}

/** Best-effort cleanup in dependency order; a refusal is logged, never thrown over the test's own failure. */
async function cleanUp(page: Page, productUuid: string | null, familyCode: string, attributeCode: string) {
  const attempt = async (label: string, remove: () => Promise<APIResponse>) => {
    try {
      const response = await remove();
      if (!response.ok() && response.status() !== 404) {
        console.warn(`[cleanup] ${label} refused: ${await describeResponse(response)}`);
      }
    } catch (e) {
      console.warn(`[cleanup] ${label}: ${(e as Error).message}`);
    }
  };
  if (productUuid) {
    await attempt(`product ${productUuid}`, () => deleteProductViaApi(page, productUuid));
  }
  await attempt(`family ${familyCode}`, () => deleteFamilyViaApi(page, familyCode));
  await attempt(`attribute ${attributeCode}`, () => deleteAttributeViaApi(page, attributeCode));
}

test.describe('Validate file attributes of a product', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('refuses a file with a forbidden extension on a scopable file attribute', async ({page}) => {
    const ts = Date.now();
    const attributeCode = `pw_attachment_${ts}`;
    const familyCode = `pw_file_family_${ts}`;
    const sku = `pw_file_${ts}`;
    let productUuid: string | null = null;

    try {
      // Background: a scopable file attribute, a family holding it, and a product of that family.
      await expectOk(
        await createAttributeViaApi(page, {
          code: attributeCode,
          type: 'pim_catalog_file',
          group: 'other',
          scopable: true,
          localizable: false,
          max_file_size: '0.01',
          allowed_extensions: ALLOWED_EXTENSIONS,
          labels: {en_US: ATTACHMENT_LABEL},
        }),
        `Create attribute ${attributeCode}`
      );
      await expectOk(await createFamilyViaApi(page, familyCode, [attributeCode]), `Create family ${familyCode}`);
      const productResp = await createProductViaApi(page, sku, familyCode);
      await expectOk(productResp, `Create product ${sku}`);
      const productText = await productResp.text();
      productUuid = JSON.parse(productText)?.meta?.id ?? null;
      expect(productUuid, `Create product ${sku} returned no meta.id: ${productText}`).toBeTruthy();

      // And I am on the "foo" product page
      await page.goto(`/#/enrich/product/${productUuid}`);
      await expect(page.getByRole('heading', {level: 1, name: sku, exact: true})).toBeVisible({timeout: 60_000});
      const attachmentField = page
        .locator('.AknFieldContainer')
        .filter({has: page.locator('.AknFieldContainer-label', {hasText: ATTACHMENT_LABEL})})
        .first();
      await expect(attachmentField).toBeVisible({timeout: 30_000});

      // Given I switch the scope to "ecommerce"
      // The context bar re-renders on the product's post_fetch, which can close an early opening: the toggle and
      // the channel click are retried together.
      const scopeSwitcher = page.locator('.AknTitleContainer-context .scope-switcher');
      await expect(async () => {
        await scopeSwitcher.locator('[data-toggle="dropdown"]').click({timeout: 5_000});
        await scopeSwitcher.locator('a[data-scope="ecommerce"]').click({timeout: 5_000});
      }).toPass({timeout: 60_000});
      await expect(scopeSwitcher.locator('.AknActionButton-highlight')).toHaveText('Ecommerce', {timeout: 15_000});
      await expect(
        attachmentField.locator('input[type="file"][data-scope="ecommerce"]'),
        'The Attachment field does not edit the ecommerce value after the scope switch'
      ).toBeAttached({timeout: 30_000});

      // Given I attach file "fanatic-freewave-76.gif" to "Attachment"
      await attachFileToProductAttribute(page, ATTACHMENT_LABEL, 'fanatic-freewave-76.gif');

      // And I save the product
      const saveResponse = page.waitForResponse(
        response =>
          response.request().method() === 'POST' &&
          new URL(response.url()).pathname === `/enrich/product/rest/${productUuid}`,
        {timeout: 150_000}
      );
      saveResponse.catch(() => {});
      await saveProduct(page);
      const saved = await saveResponse;
      const savedText = await saved.text().catch(() => '');
      expect(saved.status(), `The save should be refused with a 400: ${saved.status()} ${savedText}`).toBe(400);

      // Then I should see the text "The gif file extension is not allowed for the attachment attribute. ..."
      const message = `The gif file extension is not allowed for the ${attributeCode} attribute. Allowed extensions are ${ALLOWED_EXTENSIONS.join(', ')}.`;
      expect(savedText, `The 400 does not carry the extension error: ${savedText}`).toContain(message);
      const fieldError = attachmentField.locator('.AknFieldContainer-validationError').filter({hasText: message});
      await expect(fieldError.getByText(message, {exact: true})).toBeVisible({timeout: 30_000});
      await expect(fieldError.locator('.field-context')).toHaveText(/^\s*ecommerce\s*$/, {timeout: 15_000});
    } finally {
      await cleanUp(page, productUuid, familyCode, attributeCode);
    }
  });
});
