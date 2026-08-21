import {test, expect, Page} from '../fixtures/coverage-fixture';
import {login} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/attribute/display_available_field_options.feature:21
 *   Scenario Outline: "Successfully display available parameter fields for attribute types"
 *
 * This is a type -> visible-validation-fields conditional-rendering mapping test, covered here as
 * a data-driven test over all 9 Examples rows (not just the literal "Image" line at :21) — that
 * mapping logic, not any single row, is what's actually under test.
 *
 * How attribute creation really works (traced from source, not assumed):
 * - Clicking "Create attribute" opens a 2-step React wizard (CreateAttributeButtonApp.tsx):
 *   step 1 is a type picker (SelectAttributeType.tsx, DSM `Tile` cards with `title={typeLabel}` —
 *   the `div[title="X"]` selector the Behat page object already uses), step 2 is a code/label
 *   modal (CreateAttributeCodeAndLabel.tsx -> settings-ui's CreateAttributeModal.tsx). Confirming
 *   step 2 redirects (router.redirectToRoute) to `#/configuration/attribute/create` with
 *   `attribute_type`/`code`/`label`/... as URL QUERY PARAMS — no backend call happens in this
 *   whole wizard, it's pure client-side state handed off via the URL.
 * - The create page's controller (UIBundle/.../controller/attribute/create.js) reads those same
 *   query params back out of `location.href` and calls `form.setType(type)` before rendering.
 * - Which validation fields render for that type is a client-side-only lookup:
 *   type-specific-form.js asks type-specific-form-registry.js for a form name from a
 *   `attribute_type -> {create, edit}` map (requirejs.yml module config), then builds whichever
 *   `form_extensions/attribute/<type>.yml` extension that resolves to (number.yml, price.yml,
 *   date.yml, file.yml, image.yml, text.yml, textarea.yml, identifier.yml, metric/create.yml —
 *   each one read directly to build the field list below). None of this touches the backend, and
 *   no attribute is created until an explicit "Save" click on the create page, which this spec
 *   never performs — matching the original scenario, which only checks which fields render.
 *
 * Adaptation: since the field set depends only on the `attribute_type` query param (not on how the
 * user got there), this spec navigates straight to `#/configuration/attribute/create?attribute_type=...`
 * instead of clicking through the 2-step wizard modal — the wizard's own mechanics (Tile grid,
 * code/label modal) aren't part of what PIM-... this scenario tests, and skipping them removes a
 * chunk of flake surface for no loss of coverage of the actual mapping logic.
 *
 * Field-presence check: most validation fields render via the shared `pim/form/common/fields/*`
 * base (BaseField -> templates/form/common/fields/field.html), whose `<label
 * class="AknFieldContainer-label" for="pim_enrich_form_<fieldId>">` is what's asserted here — not
 * `getByLabel()`. Two fields per row can be a Select2-enhanced native `<select>`
 * (allowed_extensions, validation_rule — pim/form/common/fields/select.js calls
 * `.select2()` on it), and Select2 v3 sets the native `<select>` to `display:none` once
 * initialized, so `getByLabel(...).toBeVisible()` false-negatives on exactly those two fields even
 * though they're present — matching the label text directly reproduces the original Behat step's
 * own `findField()` semantics (DOM presence via label association, not visual visibility of the
 * underlying control).
 *
 * `metric_family` ("Measurement family") is included per the Behat table; `default_metric_unit`
 * is deliberately NOT asserted even though metric/create.yml also declares it — the original
 * scenario's own field list excludes it too, consistent with it being a field that only renders
 * once a measurement family has actually been picked.
 */

const TYPE_ROWS: Array<{name: string; type: string; fields: string[]}> = [
  {name: 'Identifier', type: 'pim_catalog_identifier', fields: ['Max characters', 'Validation rule']},
  {name: 'Date', type: 'pim_catalog_date', fields: ['Min date', 'Max date']},
  {name: 'File', type: 'pim_catalog_file', fields: ['Max file size (MB)', 'Allowed extensions']},
  {name: 'Image', type: 'pim_catalog_image', fields: ['Max file size (MB)', 'Allowed extensions']},
  {
    name: 'Measurement',
    type: 'pim_catalog_metric',
    fields: ['Min number', 'Max number', 'Decimal values allowed', 'Negative values allowed', 'Measurement family'],
  },
  {name: 'Price', type: 'pim_catalog_price_collection', fields: ['Min number', 'Max number', 'Decimal values allowed']},
  {
    name: 'Number',
    type: 'pim_catalog_number',
    fields: ['Min number', 'Max number', 'Decimal values allowed', 'Negative values allowed'],
  },
  {name: 'Text Area', type: 'pim_catalog_textarea', fields: ['Max characters', 'Rich text editor enabled']},
  {name: 'Text', type: 'pim_catalog_text', fields: ['Max characters', 'Validation rule']},
];

async function expectFieldVisible(page: Page, label: string) {
  await expect(page.locator('label.AknFieldContainer-label').filter({hasText: label})).toBeVisible({timeout: 15_000});
}

test.describe('Display available field options for attribute types', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  for (const {name, type, fields} of TYPE_ROWS) {
    test(`shows the expected validation fields for a new "${name}" attribute`, async ({page}) => {
      const code = `pw_${type}_${Date.now()}`;
      await page.goto(`/#/configuration/attribute/create?attribute_type=${type}&code=${code}`);

      for (const label of fields) {
        await expectFieldVisible(page, label);
      }
    });
  }
});
